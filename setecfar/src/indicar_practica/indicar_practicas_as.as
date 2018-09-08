// ActionScript file
	import clases.HTTPServices;
	
	import flash.events.Event;
	
	import indicar_practica.practica;
	
	import mx.controls.Alert;
	import mx.events.CloseEvent;
	import mx.managers.PopUpManager;
	import mx.rpc.events.ResultEvent;
	
	include "../control_acceso.as";
	
	[Bindable] private var _xmlPracticas:XML = <practicas></practicas>;
	private var _twPractica:practica;
	private var _idIngresoMovimiento:String;
	private var httpPracticas:HTTPServices = new HTTPServices;
	
	public function fncInit():void
	{
		_xmlPracticas = <practicas></practicas>;
		_idIngresoMovimiento = this.parentDocument.idIngresoMovimiento;
		_xmlPracticas.appendChild(this.parentDocument.xmlDatosPaciente.practica);
		
		httpPracticas.url = "indicar_practica/indicar_practicas.php";
		httpPracticas.addEventListener("acceso",acceso);
		
		btnNuevaPractica.addEventListener("click" ,fncAgregarPractica);
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
	
	private function fncAgregarPractica(e:Event):void
	{
		_twPractica = new practica;
		_twPractica.addEventListener("EventConfirmarPractica",fncGrabarPractica);
		PopUpManager.addPopUp(_twPractica,this,true);
		PopUpManager.centerPopUp(_twPractica);
	}
	
	private function fncGrabarPractica(e:Event):void
	{
		var xmlPractica : XML = _twPractica.xmlPractica;
		xmlPractica.@id_ingreso_movimiento_solicitud = _idIngresoMovimiento;
		httpPracticas.send({rutina:"insert", xmlPractica:xmlPractica.toXMLString()});
		httpPracticas.addEventListener(ResultEvent.RESULT,fncCargarID);
		PopUpManager.removePopUp(e.target as practica);			
	}
	
	private function fncCargarID(e:Event):void
	{
		var xmlPractica : XML = _twPractica.xmlPractica;
		xmlPractica.@id_solicitudes = httpPracticas.lastResult.insert_id; 
		_xmlPracticas.appendChild(xmlPractica);
		httpPracticas.removeEventListener(ResultEvent.RESULT,fncCargarID);
	}
	
	public function fncEditarPractica():void
	{
		_twPractica = new practica;
		_twPractica.xmlPractica =  (gridPracticas.selectedItem as XML).copy();
		_twPractica.addEventListener("EventConfirmarPractica",fncGuardarEditarPractica);
		PopUpManager.addPopUp(_twPractica,this,true);
		PopUpManager.centerPopUp(_twPractica);
	}
	
	private function fncGuardarEditarPractica(e:Event):void
	{
		var xmlPractica : XML = _twPractica.xmlPractica;
		httpPracticas.send({rutina:"update", xmlPractica:xmlPractica.toXMLString()});
		
		_xmlPracticas.practica[(gridPracticas.selectedItem as XML).childIndex()] = _twPractica.xmlPractica;
		PopUpManager.removePopUp(e.target as practica);
	}
	
	public function fncEliminarPractica():void
	{
		Alert.show("¿Realmente desea Eliminar la Practica "+ gridPracticas.selectedItem.@descripcion+"?", "Confirmar", Alert.OK | Alert.CANCEL, this, fncConfirmEliminarPractica, null, Alert.OK);
	}
	
	private function fncConfirmEliminarPractica(e:CloseEvent):void
	{
		var xmlPractica : XML = _xmlPracticas.practica[(gridPracticas.selectedItem as XML).childIndex()];
		if (e.detail==Alert.OK){
			httpPracticas.send({rutina:"delete", xmlPractica:xmlPractica.toXMLString()});
			delete _xmlPracticas.practica[(gridPracticas.selectedItem as XML).childIndex()]; 
		}
	}
	