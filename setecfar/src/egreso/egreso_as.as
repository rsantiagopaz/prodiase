// ActionScript file
	import clases.HTTPServices;
	
	import flash.events.Event;
	
	import mx.controls.Alert;
	import mx.events.ListEvent;
	import mx.rpc.events.ResultEvent;
	
	include "../control_acceso.as";
	
	[Bindable] private var httpDatos : HTTPServices = new HTTPServices;
	[Bindable] private var httpDatos2 : HTTPServices = new HTTPServices;
	[Bindable] private var httpAcEstablecimiento : HTTPServices = new HTTPServices;
	private var _idIngresoMovimiento:String;
	[Bindable] private var _xmlTipoEgresos : XML = <tegresos></tegresos>;
	[Bindable] private var _xmlMedicos : XML = <medicos></medicos>;
	
	private function fncCerrar(e:Event):void{
		dispatchEvent(new Event("SelectPrincipal"));
	}
	
	
	public function fncInit(ingMov:String):void
	{
		_xmlTipoEgresos = <tegresos></tegresos>;
		_xmlMedicos = <medicos></medicos>;
		btnGuardar.addEventListener("click",fncGuardar);
		
		cmbTipoEgreso.addEventListener(ListEvent.CHANGE,fncChangeTipoEgreso);
		_idIngresoMovimiento = ingMov;
		//preparo el autocomplete
		fncLimpiarAcEstablecimiento();
		acEstablecimiento.addEventListener(FocusEvent.FOCUS_OUT,fncCargarServicios);
		acEstablecimiento.addEventListener(ListEvent.CHANGE,ChangeAcEstablecimiento);
		acEstablecimiento.labelField = "@descripcion";
		//preparo el httpservice necesario para el autocomplete
		httpAcEstablecimiento.url = "egreso/egreso.php";
		httpAcEstablecimiento.addEventListener("acceso",acceso);
		httpAcEstablecimiento.addEventListener(ResultEvent.RESULT,fncCargarAcEstablecimiento);
		httpAcEstablecimiento.send({rutina:"traer_establecimientos",descripcion:acEstablecimiento.text});
		//preparo el httpservice que trae la info de los combos
		httpDatos.url = "egreso/egreso.php";
		httpDatos.addEventListener("acceso",acceso);
		httpDatos.addEventListener(ResultEvent.RESULT,fncCargarDatos);
		httpDatos.send({rutina:"traer_combos"});
		httpDatos2.url = "egreso/egreso.php";
		httpDatos2.addEventListener("acceso",acceso);
		httpDatos2.addEventListener(ResultEvent.RESULT,fncGuardarEgreso);
	}
	
	private function fncChangeTipoEgreso(e:Event):void
	{
		fncLimpiarAcEstablecimiento();
		acEstablecimiento.enabled =(cmbTipoEgreso.selectedItem.@id_tipo_egreso == 2);
	}
	
	private function fncLimpiarAcEstablecimiento():void{
		acEstablecimiento.typedText =""; 
		acEstablecimiento.text = "";
		acEstablecimiento.errorString= '';	
	}
	private function fncCargarAcEstablecimiento(e:Event):void{
		acEstablecimiento.typedText = acEstablecimiento.text;
		acEstablecimiento.dataProvider = httpAcEstablecimiento.lastResult.establecimiento;
	}
	
	private function ChangeAcEstablecimiento(e:Event):void{
		if (acEstablecimiento.text.length==3){
			httpAcEstablecimiento.send({rutina:"traer_establecimientos",descripcion:acEstablecimiento.text});
		}
	}
	
	private function fncCargarDatos(e:Event):void
	{
		_xmlTipoEgresos.appendChild(httpDatos.lastResult.tegreso);
		_xmlMedicos.appendChild(httpDatos.lastResult.medico);
	}
	
	private function fncCargarServicios(e:Event):void
	{
		if ((acEstablecimiento.selectedItem==null) || !(acEstablecimiento.selectedItem is XML)){
			acEstablecimiento.errorString= 'Debe seleccionar un Diagnostico';
		}
		else{
			acEstablecimiento.errorString= '';
			var establecimiento:String=acEstablecimiento.selectedItem.@id_establecimiento;
		}
	}
	
	private function fncGuardar(e:Event):void
	{
		var error : Boolean = false;
		//httpDatos.removeEventListener(ResultEvent.RESULT,fncCargarDatos);
		//httpDatos.addEventListener(ResultEvent.RESULT,fncGuardarEgreso);
			
		if (cmbTipoEgreso.selectedItem.@id_tipo_egreso == 2)
		{
			if ((acEstablecimiento.selectedItem==null) || !(acEstablecimiento.selectedItem is XML)){
				acEstablecimiento.errorString= 'Debe seleccionar un Diagnostico';
			}
			else{
				httpDatos2.send({rutina:"guardar",
							id_ingreso_movimiento:_idIngresoMovimiento,
							id_tipo_egreso:cmbTipoEgreso.selectedItem.@id_tipo_egreso,
							id_medico:cmbMedico.selectedItem.@id_personal,
							id_establecimiento:acEstablecimiento.selectedItem.@id_establecimiento,
							fecha_egreso:dfEgreso.text,
							observaciones:txaObservaciones.text
							});
			}
		}
		else{
			httpDatos2.send({rutina:"guardar",
							id_ingreso_movimiento:_idIngresoMovimiento,
							id_tipo_egreso:cmbTipoEgreso.selectedItem.@id_tipo_egreso,
							id_medico:cmbMedico.selectedItem.@id_personal,
							fecha_egreso:dfEgreso.text,
							observaciones:txaObservaciones.text
							});
		}
	}
	
	private function fncGuardarEgreso(e:Event):void
	{
		Alert.show("El Egreso fue Registrado Exitosamente!!","Egreso");
		dispatchEvent(new Event("SelectPrincipal"));
	}

