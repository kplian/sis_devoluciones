<?php
/**
*@package pXP
*@file gen-SucursalUsuario.php
*@author  (admin)
*@date 23-09-2015 19:15:16
*@description Archivo con la interfaz de usuario que permite la ejecucion de todas las funcionalidades del sistema
*/

header("content-type: text/javascript; charset=UTF-8");
?>
<script>
Phx.vista.SucursalUsuario=Ext.extend(Phx.gridInterfaz,{

	constructor:function(config){
		this.maestro=config.maestro;
    	//llama al constructor de la clase padre
		Phx.vista.SucursalUsuario.superclass.constructor.call(this,config);
		this.init();



		this.load({params:{start:0, limit:this.tam_pag}})
	},
			
	Atributos:[
		{
			//configuracion del componente
			config:{
					labelSeparator:'',
					inputType:'hidden',
					name: 'id_sucursal_usuario'
			},
			type:'Field',
			form:true 
		},
		{
			config: {
				name: 'tipo',
				fieldLabel: 'tipo',
				allowBlank: true,
				emptyText: 'Tipo...',
				typeAhead: true,
				triggerAction: 'all',
				lazyRender: true,
				mode: 'local',
				store: ['RESPONSABLE', 'AUXILIAR'],
				width: 200
			},
			type: 'ComboBox',
			id_grupo: 1,
			form: true,
			grid:true,
		},
		{
			config:{
				name:'id_usuario',
				fieldLabel:'Usuario',
				allowBlank:false,
				emptyText:'Usuario...',
				store: new Ext.data.JsonStore({

					url: '../../sis_seguridad/control/Usuario/listarUsuario',
					id: 'id_persona',
					root: 'datos',
					sortInfo:{
						field: 'desc_person',
						direction: 'ASC'
					},
					totalProperty: 'total',
					fields: ['id_usuario','desc_person','cuenta'],
					// turn on remote sorting
					remoteSort: true,
					baseParams:{par_filtro:'PERSON.nombre_completo2#cuenta'}
				}),
				valueField: 'id_usuario',
				displayField: 'desc_person',
				gdisplayField:'desc_usuario',//dibuja el campo extra de la consulta al hacer un inner join con orra tabla
				tpl:'<tpl for="."><div class="x-combo-list-item"><p>{desc_person}</p><p>Cuenta:{cuenta}</p> </div></tpl>',
				hiddenName: 'id_usuario',
				forceSelection:true,
				typeAhead: true,
				triggerAction: 'all',
				lazyRender:true,
				mode:'remote',
				pageSize:10,
				queryDelay:1000,
				width:250,
				gwidth:280,
				minChars:2,
				turl:'../../../sis_seguridad/vista/usuario/Usuario.php',
				ttitle:'Usuarios',
				// tconfig:{width:1800,height:500},
				tdata:{},
				tcls:'usuario',
				pid:this.idContenedor,

				renderer:function (value, p, record){return String.format('{0}', record.data['desc_usuario']);}
			},
			type:'TrigguerCombo',
			//type:'ComboRec',
			id_grupo:0,
			filters:{
				pfiltro:'desc_person',
				type:'string'
			},

			grid:true,
			form:true
		},
		{
			config:{
				name: 'estado_reg',
				fieldLabel: 'Estado Reg.',
				allowBlank: true,
				anchor: '80%',
				gwidth: 100,
				maxLength:10
			},
				type:'TextField',
				filters:{pfiltro:'sucus.estado_reg',type:'string'},
				id_grupo:1,
				grid:true,
				form:false
		},
		{
			config: {
				name: 'id_sucursal',
				fieldLabel: 'Sucursal',
				allowBlank: false,
				emptyText: 'Elija una opción...',
				store: new Ext.data.JsonStore({
					url: '../../sis_devoluciones/control/Sucursal/listarSucursal',
					id: 'id_sucursal',
					root: 'datos',
					sortInfo: {
						field: 'estacion',
						direction: 'ASC'

					},
					totalProperty: 'total',
					fields: ['id_sucursal','sucursal_descriptivo', 'estacion', 'direccion','alcaldia','sucursal'],
					remoteSort: true,
					baseParams: {par_filtro: 'sucu.sucursal#sucu.estacion'}
				}),
				valueField: 'id_sucursal',
				displayField: 'estacion',
				gdisplayField: 'desc_sucursal',
				tpl:'<tpl for="."><div class="x-combo-list-item"><p>Sucursal : {sucursal} {estacion}</p><p>Direccion: {direccion}<p> <p>Alcaldia: {alcaldia}<p> </div></tpl>',

				hiddenName: 'id_sucursal',
				forceSelection: true,
				typeAhead: false,
				triggerAction: 'all',
				lazyRender: true,
				mode: 'remote',
				pageSize: 15,
				queryDelay: 1000,
				anchor: '60%',
				gwidth: 150,
				minChars: 2,
				renderer : function(value, p, record) {
					return String.format('{0}', record.data['desc_sucursal']);
				}
			},
			type: 'ComboBox',
			id_grupo: 0,
			filters: {pfiltro: 'sucu.estacion',type: 'string'},
			grid: true,
			form: true
		},
		{
			config:{
				name: 'id_usuario_ai',
				fieldLabel: '',
				allowBlank: true,
				anchor: '80%',
				gwidth: 100,
				maxLength:4
			},
				type:'Field',
				filters:{pfiltro:'sucus.id_usuario_ai',type:'numeric'},
				id_grupo:1,
				grid:false,
				form:false
		},
		{
			config:{
				name: 'usr_reg',
				fieldLabel: 'Creado por',
				allowBlank: true,
				anchor: '80%',
				gwidth: 100,
				maxLength:4
			},
				type:'Field',
				filters:{pfiltro:'usu1.cuenta',type:'string'},
				id_grupo:1,
				grid:true,
				form:false
		},
		{
			config:{
				name: 'fecha_reg',
				fieldLabel: 'Fecha creación',
				allowBlank: true,
				anchor: '80%',
				gwidth: 100,
							format: 'd/m/Y', 
							renderer:function (value,p,record){return value?value.dateFormat('d/m/Y H:i:s'):''}
			},
				type:'DateField',
				filters:{pfiltro:'sucus.fecha_reg',type:'date'},
				id_grupo:1,
				grid:true,
				form:false
		},
		{
			config:{
				name: 'usuario_ai',
				fieldLabel: 'Funcionaro AI',
				allowBlank: true,
				anchor: '80%',
				gwidth: 100,
				maxLength:300
			},
				type:'TextField',
				filters:{pfiltro:'sucus.usuario_ai',type:'string'},
				id_grupo:1,
				grid:true,
				form:false
		},
		{
			config:{
				name: 'usr_mod',
				fieldLabel: 'Modificado por',
				allowBlank: true,
				anchor: '80%',
				gwidth: 100,
				maxLength:4
			},
				type:'Field',
				filters:{pfiltro:'usu2.cuenta',type:'string'},
				id_grupo:1,
				grid:true,
				form:false
		},
		{
			config:{
				name: 'fecha_mod',
				fieldLabel: 'Fecha Modif.',
				allowBlank: true,
				anchor: '80%',
				gwidth: 100,
							format: 'd/m/Y', 
							renderer:function (value,p,record){return value?value.dateFormat('d/m/Y H:i:s'):''}
			},
				type:'DateField',
				filters:{pfiltro:'sucus.fecha_mod',type:'date'},
				id_grupo:1,
				grid:true,
				form:false
		}
	],
	tam_pag:50,	
	title:'Sucursal Usuario',
	ActSave:'../../sis_devoluciones/control/SucursalUsuario/insertarSucursalUsuario',
	ActDel:'../../sis_devoluciones/control/SucursalUsuario/eliminarSucursalUsuario',
	ActList:'../../sis_devoluciones/control/SucursalUsuario/listarSucursalUsuario',
	id_store:'id_sucursal_usuario',
	fields: [
		{name:'id_sucursal_usuario', type: 'numeric'},
		{name:'tipo', type: 'string'},
		{name:'id_usuario', type: 'numeric'},
		{name:'estado_reg', type: 'string'},
		{name:'id_sucursal', type: 'numeric'},
		{name:'id_usuario_ai', type: 'numeric'},
		{name:'id_usuario_reg', type: 'numeric'},
		{name:'fecha_reg', type: 'date',dateFormat:'Y-m-d H:i:s.u'},
		{name:'usuario_ai', type: 'string'},
		{name:'id_usuario_mod', type: 'numeric'},
		{name:'fecha_mod', type: 'date',dateFormat:'Y-m-d H:i:s.u'},
		{name:'usr_reg', type: 'string'},
		{name:'usr_mod', type: 'string'},
		{name:'desc_usuario', type: 'string'},
		{name:'desc_sucursal', type: 'string'},


		// aggregamos el sucursao
	],
	sortInfo:{
		field: 'id_sucursal_usuario',
		direction: 'ASC'
	},
	bdel:true,
	bsave:true
	}
)
</script>
		
		