// ActionScript file
	import clases.HTTPServices;
	
	import flash.events.Event;
	import flash.events.KeyboardEvent;
	
	import mx.events.ListEvent;
	import mx.managers.PopUpManager;
	import mx.rpc.events.ResultEvent;
	
	import mx.core.UIComponent;
	import mx.events.ValidationResultEvent;
	import mx.validators.Validator;

	include "../control_acceso.as";
	
	[Bindable] private var httpAcPractica : HTTPServices = new HTTPServices;
	[Bindable] private var _xmlPractica : XML = <practica id_solitudes="0" id_practica="0" descripcion="" resultados="" fecha_solicitud="" id_ingreso_movimiento_solicitud="" />;
	private var _accion : String;
	
	public function get xmlPractica():XML
	{
		return _xmlPractica.copy();
	}
	
	public function set xmlPractica(prac:XML):void
	{
		_xmlPractica = prac;
		_accion = "editar";
	}
	
	private function fncInit():void
	{
		//preparo el PopUp Para que se cierre con esc y marco el default button
		this.defaultButton = btnGrabar;
		this.addEventListener(KeyboardEvent.KEY_UP,function(e:KeyboardEvent):void{if (e.keyCode==27) btnCancel.dispatchEvent(new MouseEvent(MouseEvent.CLICK))});
		//preparo el autocomplete
		acPractica.addEventListener(FocusEvent.FOCUS_OUT,function(e:Event):void{acPractica.errorString=(acPractica.selectedItem==null ? 'Debe seleccionar una practica' : '')});
		acPractica.addEventListener(ListEvent.CHANGE,ChangeAcPractica);
		acPractica.labelField = "@descripcion";
		acPractica.setFocus();
		//preparo el httpservice necesario para el autocomplete
		httpAcPractica.url = "indicar_practica/practica.php";
		httpAcPractica.addEventListener("acceso",acceso);
		httpAcPractica.addEventListener(ResultEvent.RESULT,fncCargarAcPractica);
		// escucho evento de los botones
		btnCancel.addEventListener("click",fncCerrar);
		btnGrabar.addEventListener("click",fncConfirmar);
		// si se trata de una edicion cargo el valor a editar
		if (_accion == "editar"){
			acPractica.typedText = _xmlPractica.@descripcion;
			acPractica.text = _xmlPractica.@descripcion;
			txaResultado.text = _xmlPractica.@resultados;
			dfSolicitud.text = _xmlPractica.@fecha_solicitud;
			httpAcPractica.send({rutina:"traer_practicas",descripcion:acPractica.text});
		}
	}
	
	private function ChangeAcPractica(e:Event):void{
		if (acPractica.text.length==3){
			httpAcPractica.send({rutina:"traer_practicas",descripcion:acPractica.text});
		}
	}
		
	private function fncCargarAcPractica(e:Event):void{
		acPractica.typedText = acPractica.text;
		acPractica.dataProvider = httpAcPractica.lastResult.practica;
	}
	
	private function fncCerrar(e:Event):void
	{
		PopUpManager.removePopUp(this)	
	}
	
	private function fncConfirmar(e:Event):void
	{
		var error:Array = Validator.validateAll([validFecha]);
		if (error.length>0) {
			((error[0] as ValidationResultEvent).target.source as UIComponent).setFocus();
		} else {
			if (acPractica.selectedItem==null) {
			acPractica.errorString='Debe seleccionar un medicamento válido';
			acPractica.setFocus();
		}else {
			_xmlPractica.@descripcion = acPractica.selectedItem.@descripcion;
			_xmlPractica.@id_practica = acPractica.selectedItem.@id_practica;
			_xmlPractica.@fecha_solicitud = dfSolicitud.text;	
			_xmlPractica.@resultados = txaResultado.text;
			dispatchEvent(new Event("EventConfirmarPractica"));
		}	
		}			
	}
	