// ActionScript file
	import clases.HTTPServices;
	
	import flash.events.Event;
	
	import mx.core.UIComponent;
	import mx.events.ValidationResultEvent;
	import mx.rpc.events.ResultEvent;
	import mx.validators.Validator;
	
	include "../control_acceso.as";

	private var _tipo_doc : String;
	private var _nro_doc: String;
	[Bindable] private var _xmlDatosPaciente : XML;
	[Bindable] private var httpDatos : HTTPServices = new HTTPServices;
	
	public function fncInit():void
	{				
		httpDatos.url = "buscar_persona/busquedapersona.php";
		httpDatos.addEventListener("acceso",acceso);
		httpDatos.addEventListener(ResultEvent.RESULT,fncCargarDatos);
		btnAceptar.addEventListener("click",fncIniciarBusqueda);		
	}		
		
	public function get xmlDatosPaciente():XML { return _xmlDatosPaciente }
	
	public function get tipo_doc():String { return _tipo_doc }
	
	private function fncValidar():Boolean
	{
		var error:Array = Validator.validateAll([validTDOC,validNDOC]);
		if (error.length>0) {
			((error[0] as ValidationResultEvent).target.source as UIComponent).setFocus();
			return false;
		}else{
			return true;	
		}	
	}
	
	private function fncIniciarBusqueda(e:Event):void
	{
		//if (fncValidar()){
			var idxdoc:int = cbxTipoDoc.selectedIndex;			
			_tipo_doc = xmlTiposDoc.tipodoc.id[idxdoc];
			_nro_doc = txiNroDoc.text;
			httpDatos.send({rutina:"buscar_persona", tipo_doc:_tipo_doc, nro_doc:_nro_doc});
		//}	
	}
	
	private function fncCargarDatos(e:ResultEvent):void
	{
		_xmlDatosPaciente = httpDatos.lastResult as XML;
		dispatchEvent(new Event("EventMostrarResultadoBusqueda"));
	}