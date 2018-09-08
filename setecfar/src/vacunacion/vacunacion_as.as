// ActionScript file
	import clases.HTTPServices;
	import flash.events.Event;	
	import mx.controls.Alert;
	import mx.events.CloseEvent;
	import mx.managers.PopUpManager;
	import mx.rpc.events.ResultEvent;
	
	import vacunacion.nueva_vacunacion;
	
	include "../control_acceso.as";
	
	[Bindable] private var _xmlVacunaciones:XML = <vacunaciones></vacunaciones>;
	private var _twNuevasVacunaciones:nueva_vacunacion;
	private var _idIngresoMovimiento:String;
	private var httpVacunas:HTTPServices = new HTTPServices;
		
	public function fncInit():void
	{
		_xmlVacunaciones = <vacunaciones></vacunaciones>;
		_idIngresoMovimiento = this.parentDocument.idIngresoMovimiento;		
		_xmlVacunaciones.appendChild(this.parentDocument.xmlDatosPaciente.vacunaciones);		
		httpVacunas.url = "vacunacion/vacunacion.php";
		httpVacunas.addEventListener("acceso",acceso);
		btnNuevaVacunacion.addEventListener("click" ,fncAgregarVacunacion);
		btnCerrar.addEventListener("click",fncCerrar);
		if (this.parentDocument.ReadOnly){
			this.currentState = 'historial';
			btnCerrarHistorial.addEventListener("click",fncCerrarPOP);
		}	
	}
	
	private function fncCerrarPOP(e:Event):void{
		parentDocument.fncCloseHistorial();
	}
	
	private function fncCerrar(e:Event):void{
		dispatchEvent(new Event("eventClose"));
	}
		
	private function fncAgregarVacunacion(e:Event):void
	{
		_twNuevasVacunaciones = new nueva_vacunacion;
		_twNuevasVacunaciones.addEventListener("EventConfirmarVacunacion",fncGrabarNuevaVacunacion);
		PopUpManager.addPopUp(_twNuevasVacunaciones,this,true);
		PopUpManager.centerPopUp(_twNuevasVacunaciones);
	}
	
	private function fncGrabarNuevaVacunacion(e:Event):void
	{				
		/*_xmlVacunaciones.appendChild(_twNuevasVacunaciones.xmlVacuna);		
		PopUpManager.removePopUp(e.target as nueva_vacunacion);*/
		var xmlP : XML = _twNuevasVacunaciones.xmlVacuna;
		xmlP.@id_ingreso_movimiento = _idIngresoMovimiento;
		httpVacunas.send({rutina:"insert", xmlVacunacion:xmlP.toXMLString()});
		httpVacunas.addEventListener(ResultEvent.RESULT,fncCargarID);
		PopUpManager.removePopUp(e.target as nueva_vacunacion);	
	}
	
	private function fncCargarID(e:Event):void
	{
		var xmlP : XML = _twNuevasVacunaciones.xmlVacuna;
		xmlP.@id_vacunacion = httpVacunas.lastResult.insert_id;		
		_xmlVacunaciones.appendChild(xmlP);		
		httpVacunas.removeEventListener(ResultEvent.RESULT,fncCargarID);
	}
	
	private function fncEditarNuevaVacunacion(e:Event):void
	{
		var xmlP : XML = _twNuevasVacunaciones.xmlVacuna;
		xmlP.@id_ingreso_movimiento = _idIngresoMovimiento;
		httpVacunas.send({rutina:"update", xmlVacunacion:xmlP.toXMLString()});
		_xmlVacunaciones.vacunaciones[(gridVacunaciones.selectedItem as XML).childIndex()] = _twNuevasVacunaciones.xmlVacuna;
		PopUpManager.removePopUp(e.target as nueva_vacunacion);	
	}
	
	public function fncEditarVacunacion():void
	{
		_twNuevasVacunaciones = new nueva_vacunacion;
		_twNuevasVacunaciones.xmlVacuna =  (gridVacunaciones.selectedItem as XML).copy();
		_twNuevasVacunaciones.addEventListener("EventConfirmarVacunacion",fncEditarNuevaVacunacion);
		PopUpManager.addPopUp(_twNuevasVacunaciones,this,true);
		PopUpManager.centerPopUp(_twNuevasVacunaciones);
	}
	
	public function fncEliminarVacunacion():void
	{
		Alert.show("¿Realmente desea Eliminar la Vacuna "+ gridVacunaciones.selectedItem.@nombre+"?", "Confirmar", Alert.OK | Alert.CANCEL, this, fncConfirmEliminarVacunacion, null, Alert.OK);
	}
	
	private function fncConfirmEliminarVacunacion(e:CloseEvent):void
	{		
		var xmlP : XML = _xmlVacunaciones.vacunaciones[(gridVacunaciones.selectedItem as XML).childIndex()];
		if (e.detail==Alert.OK){
			httpVacunas.send({rutina:"delete", xmlVacunacion:xmlP.toXMLString()});
			delete _xmlVacunaciones.vacunaciones[(gridVacunaciones.selectedItem as XML).childIndex()]; 
		}
	}