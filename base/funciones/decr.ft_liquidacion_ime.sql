CREATE OR REPLACE FUNCTION "decr"."ft_liquidacion_ime" (	
				p_administrador integer, p_id_usuario integer, p_tabla character varying, p_transaccion character varying)
RETURNS character varying AS
$BODY$

/**************************************************************************
 SISTEMA:		devoluciones
 FUNCION: 		decr.ft_liquidacion_ime
 DESCRIPCION:   Funcion que gestiona las operaciones basicas (inserciones, modificaciones, eliminaciones de la tabla 'decr.tliquidacion'
 AUTOR: 		 (admin)
 FECHA:	        17-04-2020 01:54:37
 COMENTARIOS:	
***************************************************************************
 HISTORIAL DE MODIFICACIONES:
#ISSUE				FECHA				AUTOR				DESCRIPCION
 #0				17-04-2020 01:54:37								Funcion que gestiona las operaciones basicas (inserciones, modificaciones, eliminaciones de la tabla 'decr.tliquidacion'	
 #
 ***************************************************************************/

DECLARE

	v_nro_requerimiento    	integer;
	v_parametros           	record;
	v_id_requerimiento     	integer;
	v_resp		            varchar;
	v_nombre_funcion        text;
	v_mensaje_error         text;
	v_id_liquidacion	integer;
    v_json	varchar;

    v_id_concepto_ingas varchar[];
    v_i integer;
    v_tamano integer;
    v_num_tramite  		varchar;
    v_id_proceso_wf 	integer;
    v_id_estado_wf 		integer;
    v_codigo_estado 	varchar;
    v_rec                         RECORD;
    v_codigo_estado_siguiente     varchar;
    v_acceso_directo              varchar;
    v_clase                       varchar;
    v_parametros_ad               varchar;
    v_tipo_noti                   varchar;
    v_titulo                      varchar;
    v_id_estado_actual            integer;
    v_conceptos_json            record;
BEGIN

    v_nombre_funcion = 'decr.ft_liquidacion_ime';
    v_parametros = pxp.f_get_record(p_tabla);

	/*********************************    
 	#TRANSACCION:  'DECR_LIQUI_INS'
 	#DESCRIPCION:	Insercion de registros
 	#AUTOR:		admin	
 	#FECHA:		17-04-2020 01:54:37
	***********************************/

	if(p_transaccion='DECR_LIQUI_INS')then
					
        begin

            v_rec = param.f_get_periodo_gestion(to_char(now(), 'YYYY-mm-dd')::DATE);
            -- inciar el tramite en el sistema de WF
            SELECT
                ps_num_tramite ,
                ps_id_proceso_wf ,
                ps_id_estado_wf ,
                ps_codigo_estado
            INTO
                v_num_tramite,
                v_id_proceso_wf,
                v_id_estado_wf,
                v_codigo_estado

            FROM wf.f_inicia_tramite(
                    p_id_usuario,
                    NULL, --(p_hstore->'_id_usuario_ai')::integer,
                    NULL, --(p_hstore->'_nombre_usuario_ai')::varchar,
                    v_rec.po_id_gestion::INTEGER,
                    'LIQDEVOLU',
                    NULL::integer,
                    NULL,
                    NULL,
                    v_parametros.nro_liquidacion );





            --Sentencia de la insercion
        	insert into decr.tliquidacion(
			estacion,
			nro_liquidacion,
			estado_reg,
			tipo_de_cambio,
			descripcion,
			nombre_cheque,
			fecha_liqui,
			tramo_devolucion,
			util,
			--fecha_pago,
			id_tipo_doc_liquidacion,
			pv_agt,
			noiata,
			id_tipo_liquidacion,
			id_forma_pago,
			tramo,
			nombre,
			moneda_liq,
			estado,
			cheque,
			id_usuario_reg,
			fecha_reg,
			usuario_ai,
			id_usuario_ai,
			id_usuario_mod,
			fecha_mod,
        	                              id_boleto,
            punto_venta,
            moneda_emision,
            importe_neto,
            tasas,
            importe_total,
            id_punto_venta,
        	                              id_estado_wf,
        	                              id_proceso_wf,
        	                              num_tramite
          	) values(
			v_parametros.estacion,
			v_parametros.nro_liquidacion,
			'activo',
			v_parametros.tipo_de_cambio,
			v_parametros.descripcion,
			v_parametros.nombre_cheque,
			v_parametros.fecha_liqui,
			v_parametros.tramo_devolucion,
			v_parametros.util,
			--v_parametros.fecha_pago,
			v_parametros.id_tipo_doc_liquidacion,
			v_parametros.pv_agt,
			v_parametros.noiata,
			v_parametros.id_tipo_liquidacion,
			v_parametros.id_forma_pago,
			v_parametros.tramo,
			v_parametros.nombre,
			v_parametros.moneda_liq,
			--v_parametros.estado,
            v_codigo_estado,
			v_parametros.cheque,
			p_id_usuario,
			now(),
			v_parametros._nombre_usuario_ai,
			v_parametros._id_usuario_ai,
			null,
			null,
          	         v_parametros.id_boleto,
          	         v_parametros.punto_venta,
          	         v_parametros.moneda_emision,
          	         v_parametros.importe_neto,
          	         v_parametros.tasas,
          	         v_parametros.importe_total,
          	         v_parametros.id_punto_venta,

            v_id_estado_wf,
            v_id_proceso_wf,
            v_num_tramite

			
			
			)RETURNING id_liquidacion into v_id_liquidacion;



            FOR v_conceptos_json
                IN (
                    SELECT *
                    FROM json_populate_recordset(NULL::record, v_parametros.json::json)
                             AS
                             (
                              id_concepto_ingas varchar, descripcion varchar, contabilizar varchar, importe varchar
                                 )

                )
                LOOP

                    insert into decr.tdescuento_liquidacion(
                        contabilizar,
                        importe,
                        estado_reg,
                        id_concepto_ingas,
                        id_liquidacion,
                        sobre,
                        fecha_reg,
                        usuario_ai,
                        id_usuario_reg,
                        id_usuario_ai,
                        fecha_mod,
                        id_usuario_mod
                    ) values(
                                                    v_conceptos_json.contabilizar,
                                                    v_conceptos_json.importe::numeric, --todo
                                'activo',
                                v_conceptos_json.id_concepto_ingas::integer,
                                v_id_liquidacion,
                                null,
                                now(),
                                v_parametros._nombre_usuario_ai,
                                p_id_usuario,
                                v_parametros._id_usuario_ai,
                                null,
                                null
                            );

            END LOOP;

            /*v_id_concepto_ingas = string_to_array(v_parametros.id_concepto_ingas,',');
            v_tamano = coalesce(array_length(v_id_concepto_ingas, 1),0);
            FOR v_i IN 1..v_tamano LOOP
                --insertamos  registro si no esta presente como activo

                --Sentencia de la insercion
                    insert into decr.tdescuento_liquidacion(
                        contabilizar,
                        importe,
                        estado_reg,
                        id_concepto_ingas,
                        id_liquidacion,
                        sobre,
                        fecha_reg,
                        usuario_ai,
                        id_usuario_reg,
                        id_usuario_ai,
                        fecha_mod,
                        id_usuario_mod
                    ) values(
                                null,
                                100, --todo
                                'activo',
                                v_id_concepto_ingas[v_i]::integer,
                                v_id_liquidacion,
                                null,
                                now(),
                                v_parametros._nombre_usuario_ai,
                                p_id_usuario,
                                v_parametros._id_usuario_ai,
                                null,
                                null
                            );
            END LOOP;*/

			
			--Definicion de la respuesta
			v_resp = pxp.f_agrega_clave(v_resp,'mensaje','Liquidacion almacenado(a) con exito (id_liquidacion'||v_id_liquidacion||')'); 
            v_resp = pxp.f_agrega_clave(v_resp,'id_liquidacion',v_id_liquidacion::varchar);

            --Devuelve la respuesta
            return v_resp;

		end;

	/*********************************    
 	#TRANSACCION:  'DECR_LIQUI_MOD'
 	#DESCRIPCION:	Modificacion de registros
 	#AUTOR:		admin	
 	#FECHA:		17-04-2020 01:54:37
	***********************************/

	elsif(p_transaccion='DECR_LIQUI_MOD')then

		begin
			--Sentencia de la modificacion
			update decr.tliquidacion set
			estacion = v_parametros.estacion,
			nro_liquidacion = v_parametros.nro_liquidacion,
			tipo_de_cambio = v_parametros.tipo_de_cambio,
			descripcion = v_parametros.descripcion,
			nombre_cheque = v_parametros.nombre_cheque,
			fecha_liqui = v_parametros.fecha_liqui,
			tramo_devolucion = v_parametros.tramo_devolucion,
			util = v_parametros.util,
			fecha_pago = v_parametros.fecha_pago,
			id_tipo_doc_liquidacion = v_parametros.id_tipo_doc_liquidacion,
			pv_agt = v_parametros.pv_agt,
			noiata = v_parametros.noiata,
			id_tipo_liquidacion = v_parametros.id_tipo_liquidacion,
			id_forma_pago = v_parametros.id_forma_pago,
			tramo = v_parametros.tramo,
			nombre = v_parametros.nombre,
			moneda_liq = v_parametros.moneda_liq,
			estado = v_parametros.estado,
			cheque = v_parametros.cheque,
			id_usuario_mod = p_id_usuario,
			fecha_mod = now(),
			id_usuario_ai = v_parametros._id_usuario_ai,
			usuario_ai = v_parametros._nombre_usuario_ai,
			id_boleto = v_parametros.id_boleto,
			punto_venta = v_parametros.punto_venta,
			moneda_emision = v_parametros.moneda_emision,
			importe_neto = v_parametros.importe_neto,
			tasas = v_parametros.tasas,
			importe_total = v_parametros.importe_total
			where id_liquidacion=v_parametros.id_liquidacion;
               
			--Definicion de la respuesta
            v_resp = pxp.f_agrega_clave(v_resp,'mensaje','Liquidacion modificado(a)'); 
            v_resp = pxp.f_agrega_clave(v_resp,'id_liquidacion',v_parametros.id_liquidacion::varchar);
               
            --Devuelve la respuesta
            return v_resp;
            
		end;

	/*********************************    
 	#TRANSACCION:  'DECR_LIQUI_ELI'
 	#DESCRIPCION:	Eliminacion de registros
 	#AUTOR:		admin	
 	#FECHA:		17-04-2020 01:54:37
	***********************************/

	elsif(p_transaccion='DECR_LIQUI_ELI')then

		begin
			--Sentencia de la eliminacion
			delete from decr.tliquidacion
            where id_liquidacion=v_parametros.id_liquidacion;
               
            --Definicion de la respuesta
            v_resp = pxp.f_agrega_clave(v_resp,'mensaje','Liquidacion eliminado(a)'); 
            v_resp = pxp.f_agrega_clave(v_resp,'id_liquidacion',v_parametros.id_liquidacion::varchar);
              
            --Devuelve la respuesta
            return v_resp;

		end;
         
	/*********************************
 	#TRANSACCION:  'DECR_LIQUI_VERJSON'
 	#DESCRIPCION:	Eliminacion de registros
 	#AUTOR:		admin
 	#FECHA:		17-04-2020 01:54:37
	***********************************/

	elsif(p_transaccion='DECR_LIQUI_VERJSON')then

		begin
			--Sentencia de la eliminacion

            WITH t_liqui AS
                     (
                         SELECT * FROM decr.tliquidacion tl
                         WHERE tl.id_liquidacion = v_parametros.id_liquidacion
                     ), t_descuentos AS (
                SELECT tdl.id_liquidacion,tdl.id_concepto_ingas, tdl.importe, tci.desc_ingas
                FROM decr.tdescuento_liquidacion tdl
                INNER JOIN param.tconcepto_ingas tci on tci.id_concepto_ingas = tdl.id_concepto_ingas
                WHERE tdl.id_liquidacion = v_parametros.id_liquidacion
            )SELECT TO_JSON(ROW_TO_JSON(jsonData) :: TEXT) #>> '{}' as json
            INTO v_json
            FROM
                (
                    SELECT
                        (
                            SELECT TO_JSON(liqui)
                            FROM
                                (
                                    SELECT tl.*,
                                           (
                                                   tl.importe_total - (SELECT sum(importe)
                                                                       FROM t_descuentos td
                                                                       WHERE td.id_liquidacion = tl.id_liquidacion )
                                               ) as total_liquidacion
                                    FROM t_liqui tl
                                ) liqui
                        ) as liquidacion,
                        (
                            SELECT ARRAY_TO_JSON(ARRAY_AGG(ROW_TO_JSON(descuentos)))
                            FROM
                                (
                                    SELECT * FROM t_descuentos
                                ) descuentos
                        ) as descuentos,
                        (
                            SELECT ARRAY_TO_JSON(ARRAY_AGG(ROW_TO_JSON(nota)))
                            FROM
                                (
                                    SELECT * FROM decr.tnota where id_liquidacion::integer = v_parametros.id_liquidacion
                                ) nota
                        ) as notas,
                        (
                            select sum(importe) from t_descuentos
                        ) as sum_descuentos

                ) jsonData;


            --Definicion de la respuesta
            v_resp = pxp.f_agrega_clave(v_resp,'json',v_json);
            v_resp = pxp.f_agrega_clave(v_resp,'mensaje',v_json);
            v_resp = pxp.f_agrega_clave(v_resp,'id_liquidacion',v_parametros.id_liquidacion::varchar);

            --Devuelve la respuesta
            return v_resp;

		end;

	/*********************************
 	#TRANSACCION:  'DECR_LIQUI_SIGWF'
 	#DESCRIPCION:	ACTUALIZAR SIGUIENTE ESTADO WORKFLOW
 	#AUTOR:		admin
 	#FECHA:		17-04-2020 01:54:37
	***********************************/

	elsif(p_transaccion='DECR_LIQUI_SIGWF')then

		begin

            select
                id_liquidacion
            into
                v_id_liquidacion
            from decr.tliquidacion mov
            where id_proceso_wf = v_parametros.id_proceso_wf_act;

            select
                codigo
            into
                v_codigo_estado_siguiente
            from wf.ttipo_estado tes
            where tes.id_tipo_estado =  v_parametros.id_tipo_estado;

            if v_codigo_estado_siguiente not in ('emitido') then
                v_acceso_directo = '../../../sis_devoluciones/vista/liquidacion/Liquidacion.php';
                v_clase = 'Liquidacion';
                v_parametros_ad = '{filtro_directo:{campo:"liqui.id_proceso_wf",valor:"'||v_parametros.id_proceso_wf_act::varchar||'"}}';
                v_tipo_noti = 'notificacion';
                v_titulo  = 'Notificacion';
            end if;

            --Obtención id del estaado actual
            v_id_estado_actual =  wf.f_registra_estado_wf(
                    v_parametros.id_tipo_estado,
                    v_parametros.id_funcionario_wf,
                    v_parametros.id_estado_wf_act,
                    v_parametros.id_proceso_wf_act,
                    p_id_usuario,
                    v_parametros._id_usuario_ai,
                    v_parametros._nombre_usuario_ai,
                    null,
                    '',
                    v_acceso_directo ,
                    v_clase,
                    v_parametros_ad,
                    v_tipo_noti,
                    v_titulo
                );

            --Actualiza el estado actual del movimiento
            update decr.tliquidacion set
                                       id_estado_wf = v_id_estado_actual,
                                       estado = v_codigo_estado_siguiente
            where id_liquidacion = v_id_liquidacion;



            --Definicion de la respuesta
            v_resp = pxp.f_agrega_clave(v_resp,'mensaje','estado cambiado');
            v_resp = pxp.f_agrega_clave(v_resp,'id_liquidacion',v_id_liquidacion::varchar);

            --Devuelve la respuesta
            return v_resp;

		end;

	else
     
    	raise exception 'Transaccion inexistente: %',p_transaccion;

	end if;

EXCEPTION
				
	WHEN OTHERS THEN
		v_resp='';
		v_resp = pxp.f_agrega_clave(v_resp,'mensaje',SQLERRM);
		v_resp = pxp.f_agrega_clave(v_resp,'codigo_error',SQLSTATE);
		v_resp = pxp.f_agrega_clave(v_resp,'procedimientos',v_nombre_funcion);
		raise exception '%',v_resp;
				        
END;
$BODY$
LANGUAGE 'plpgsql' VOLATILE
COST 100;
ALTER FUNCTION "decr"."ft_liquidacion_ime"(integer, integer, character varying, character varying) OWNER TO postgres;