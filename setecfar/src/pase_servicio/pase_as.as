// ActionScript file
	import clases.HTTPServices;
	
	import flash.events.Event;
	
	import mx.controls.Alert;
	import mx.events.ListEvent;
	import mx.rpc.events.ResultEvent;
	
	include "../control_acceso.as";
	
	[Bindable] private var httpDatos : HTTPServices = new HTTPServices;
	[Bindable] private var httpDatos2 : HTTPServices = new HTTPServices;
	private var _idIngresoMovimiento:String;
	[Bindable] private var _xmlServicios : XML = <servicios></servicios>;
	[Bindable] private var _xmlMedicos : XML = <medicos></medicos>;
	
	private function fncCerrar(e:Event):void{
		dispatchEvent(new Event("SelectPrincipal"));
	}
	
	public function fncInit(ingMov:String):void
	{		
		_idIngresoMovimiento = ingMov;
		_xmlServicios = <servicios></servicios>;
		_xmlMedicos = <medicos></medicos>;
		txaObservaciones.text = "";
		dfPase.text="";
		
		btnGuardar.addEventListener("click",fncGuardar);
		//preparo el httpservice que trae la info de los combos
		httpDatos.url = "pase_servicio/pase.php";
		httpDatos.addEventListener("acceso",acceso);
		httpDatos.addEventListener(ResultEvent.RESULT,fncCargarDatos);
		httpDatos.send({rutina:"traer_combos"});
		httpDatos2.url = "pase_servicio/pase.php";
		httpDatos2.addEventListener("acceso",acceso);
		httpDatos2.addEventListener(ResultEvent.RESULT,fncGuardarPase);
	}
	
	
	
	private function fncCargarDatos(e:Event):void
	{
		_xmlServicios.appendChild(httpDatos.lastResult.servicio);	
		_xmlMedicos.appendChild(httpDatos.lastResult.medico);
	}
	

	private function fncGuardar(e:Event):void
	{
		var error : Boolean = false;
		
		//httpDatos.removeEventListener(ResultEvent.RESULT,fncCargarDatos);
		//httpDatos.addEventListener(ResultEvent.RESULT,fncGuardarPase);
			
		httpDatos2.send({rutina:"guardar",
							id_ingreso_movimiento:_idIngresoMovimiento,
							id_servicio:cmbServicio.selectedItem.@id_area_servicio,
							id_medico:cmbMedico.selectedItem.@id_personal,
							fecha_pase:dfPase.text,
							observaciones:txaObservaciones.text
							});
	}

	
	private function fncGuardarPase(e:Event):void
	{		
		Alert.show("El Pase fue Registrado Exitosamente!!","Pase a otro Servicio");
		dispatchEvent(new Event("SelectPrincipal"));
	}

