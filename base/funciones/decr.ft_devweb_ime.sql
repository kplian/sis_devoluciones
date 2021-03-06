CREATE OR REPLACE FUNCTION "decr"."ft_devweb_ime" (	
				p_administrador integer, p_id_usuario integer, p_tabla character varying, p_transaccion character varying)
RETURNS character varying AS
$BODY$

/**************************************************************************
 SISTEMA:		devoluciones
 FUNCION: 		decr.ft_devweb_ime
 DESCRIPCION:   Funcion que gestiona las operaciones basicas (inserciones, modificaciones, eliminaciones de la tabla 'decr.tdevweb'
 AUTOR: 		 (admin)
 FECHA:	        04-07-2016 15:19:06
 COMENTARIOS:	
***************************************************************************
 HISTORIAL DE MODIFICACIONES:

 DESCRIPCION:	
 AUTOR:			
 FECHA:		
***************************************************************************/

DECLARE

	v_nro_requerimiento    	integer;
	v_parametros           	record;
	v_id_requerimiento     	integer;
	v_resp		            varchar;
	v_nombre_funcion        text;
	v_mensaje_error         text;
	v_id_devweb	integer;
			    
BEGIN

    v_nombre_funcion = 'decr.ft_devweb_ime';
    v_parametros = pxp.f_get_record(p_tabla);

	/*********************************    
 	#TRANSACCION:  'DECR_DEVWEB_INS'
 	#DESCRIPCION:	Insercion de registros
 	#AUTOR:		admin	
 	#FECHA:		04-07-2016 15:19:06
	***********************************/

	if(p_transaccion='DECR_DEVWEB_INS')then
					
        begin
        	--Sentencia de la insercion
        	insert into decr.tdevweb(
			estado,
			estado_reg,
			id_usuario,
			id_usuario_ai,
			usuario_ai,
			fecha_reg,
			id_usuario_reg,
			id_usuario_mod,
			fecha_mod
          	) values(
			v_parametros.estado,
			'activo',
			v_parametros.id_usuario,
			v_parametros._id_usuario_ai,
			v_parametros._nombre_usuario_ai,
			now(),
			p_id_usuario,
			null,
			null
							
			
			
			)RETURNING id_devweb into v_id_devweb;
			
			--Definicion de la respuesta
			v_resp = pxp.f_agrega_clave(v_resp,'mensaje','devweb almacenado(a) con exito (id_devweb'||v_id_devweb||')'); 
            v_resp = pxp.f_agrega_clave(v_resp,'id_devweb',v_id_devweb::varchar);

            --Devuelve la respuesta
            return v_resp;

		end;

	/*********************************    
 	#TRANSACCION:  'DECR_DEVWEB_MOD'
 	#DESCRIPCION:	Modificacion de registros
 	#AUTOR:		admin	
 	#FECHA:		04-07-2016 15:19:06
	***********************************/

	elsif(p_transaccion='DECR_DEVWEB_MOD')then

		begin
			--Sentencia de la modificacion
			update decr.tdevweb set
			estado = v_parametros.estado,
			id_usuario = v_parametros.id_usuario,
			id_usuario_mod = p_id_usuario,
			fecha_mod = now(),
			id_usuario_ai = v_parametros._id_usuario_ai,
			usuario_ai = v_parametros._nombre_usuario_ai
			where id_devweb=v_parametros.id_devweb;
               
			--Definicion de la respuesta
            v_resp = pxp.f_agrega_clave(v_resp,'mensaje','devweb modificado(a)'); 
            v_resp = pxp.f_agrega_clave(v_resp,'id_devweb',v_parametros.id_devweb::varchar);
               
            --Devuelve la respuesta
            return v_resp;
            
		end;

	/*********************************    
 	#TRANSACCION:  'DECR_DEVWEB_ELI'
 	#DESCRIPCION:	Eliminacion de registros
 	#AUTOR:		admin	
 	#FECHA:		04-07-2016 15:19:06
	***********************************/

	elsif(p_transaccion='DECR_DEVWEB_ELI')then

		begin
			--Sentencia de la eliminacion
			delete from decr.tdevweb
            where id_devweb=v_parametros.id_devweb;
               
            --Definicion de la respuesta
            v_resp = pxp.f_agrega_clave(v_resp,'mensaje','devweb eliminado(a)'); 
            v_resp = pxp.f_agrega_clave(v_resp,'id_devweb',v_parametros.id_devweb::varchar);
              
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
ALTER FUNCTION "decr"."ft_devweb_ime"(integer, integer, character varying, character varying) OWNER TO postgres;
