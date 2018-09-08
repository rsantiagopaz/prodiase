// ActionScript file
	import clases.HTTPServices;
	import antecedentes.nuevo_antecedente;
	
	import flash.events.Event;
	import mx.rpc.events.ResultEvent;
	
	import mx.controls.Alert;
	import mx.events.CloseEvent;
	import mx.managers.PopUpManager;
	
	include "../control_acceso.as";	
	
	[Bindable] private var _xmlAntecedentes:XML = <antecedentes></antecedentes>;
	private var _twNuevosAntecedentes:nuevo_antecedente;
	private var _idIngresoMovimiento:String;
	private var httpAntecedentes:HTTPServices = new HTTPServices;
		
	public function fncInit():void
	{
		_xmlAntecedentes = <antecedentes></antecedentes>;
		_idIngresoMovimiento = parentApplication.CONSULTA.idIngresoMovimiento;
		_xmlAntecedentes.appendChild(parentApplication.CONSULTA.xmlDatosPaciente.antecedente);
		httpAntecedentes.url = "antecedentes/antecedentes.php";
		httpAntecedentes.addEventListener("acceso",acceso);
		btnNuevoAntecedente.addEventListener("click" ,fncAgregarAntecedente);
		btnCerrar.addEventListener("click" ,fncCerrar);
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
	
	private function fncAgregarAntecedente(e:Event):void
	{
		_twNuevosAntecedentes = new nuevo_antecedente;
		_twNuevosAntecedentes.addEventListener("EventConfirmarAntecedente",fncGrabarNuevoAntecedente);
		PopUpManager.addPopUp(_twNuevosAntecedentes,this,true);
		PopUpManager.centerPopUp(_twNuevosAntecedentes);
	}
	
	private function fncGrabarNuevoAntecedente(e:Event):void
	{
		var xmlP : XML = _twNuevosAntecedentes.xmlNuevoAntecedente;
		xmlP.@id_ingreso_movimiento = _idIngresoMovimiento;
		httpAntecedentes.send({rutina:"insert", xmlAntecedente:xmlP.toXMLString()});
		httpAntecedentes.addEventListener(ResultEvent.RESULT,fncCargarID);
		PopUpManager.removePopUp(e.target as nuevo_antecedente);	
	}
	
	private function fncCargarID(e:Event):void
	{
		var xmlP : XML = _twNuevosAntecedentes.xmlNuevoAntecedente;
		xmlP.@id_antecedente = httpAntecedentes.lastResult.insert_id;		
		_xmlAntecedentes.appendChild(xmlP);		
		httpAntecedentes.removeEventListener(ResultEvent.RESULT,fncCargarID);
	}
	
	private function fncEditarNuevoAntecedente(e:Event):void
	{		
		_xmlAntecedentes.appendChild(_twNuevosAntecedentes.xmlNuevoAntecedente);
		//_xmlAntecedentes.antecedente[(gridAntecedentes.selectedItem as XML).childIndex()] = _twNuevosAntecedentes.xmlNuevoAntecedente;
		PopUpManager.removePopUp(e.target as nuevo_antecedente);	
	}
	
	public function fncEditarAntecedente():void
	{
		_twNuevosAntecedentes = new nuevo_antecedente;
		_twNuevosAntecedentes.xmlNuevoAntecedente =  (gridAntecedentes.selectedItem as XML).copy();
		_twNuevosAntecedentes.addEventListener("EventConfirmarAntecedente",fncGrabarNuevoAntecedente);
		PopUpManager.addPopUp(_twNuevosAntecedentes,this,true);
		PopUpManager.centerPopUp(_twNuevosAntecedentes);
	}
	
	public function fncEliminarAntecedente2():void
	{
		_twNuevosAntecedentes = new nuevo_antecedente;
		_twNuevosAntecedentes.xmlNuevoAntecedente2 =  (gridAntecedentes.selectedItem as XML).copy();
		_twNuevosAntecedentes.addEventListener("EventConfirmarAntecedente",fncGrabarNuevoAntecedente);
		PopUpManager.addPopUp(_twNuevosAntecedentes,this,true);
		PopUpManager.centerPopUp(_twNuevosAntecedentes);
	}
	
	public function fncEliminarAntecedente():void
	{
		Alert.show("¿Realmente desea Eliminar el Antecedente "+ gridAntecedentes.selectedItem.@antecedente+"?", "Confirmar", Alert.OK | Alert.CANCEL, this, fncConfirmEliminarAntecedente, null, Alert.OK);
	}
	
	private function fncConfirmEliminarAntecedente(e:CloseEvent):void
	{
		if (e.detail==Alert.OK){
			/*var _xmlTipoAntecedente:XML = (gridAntecedentes.selectedItem as XML).copy();
			_xmlTipoAntecedente.@accion = 'Baja';
			_xmlAntecedentes.appendChild(_xmlTipoAntecedente);*/			
			//delete _xmlAntecedentes.antecedente[(gridAntecedentes.selectedItem as XML).childIndex()]; 
			fncEliminarAntecedente2();
		}
	}