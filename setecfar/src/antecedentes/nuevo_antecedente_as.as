// ActionScript file
	import clases.HTTPServices;
	
	import flash.events.Event;
	
	import mx.core.UIComponent;
	import mx.events.ListEvent;
	import mx.events.ValidationResultEvent;
	import mx.managers.PopUpManager;
	import mx.rpc.events.ResultEvent;
	import mx.validators.Validator;	
	
	include "../control_acceso.as";
	
	import mx.controls.Alert;	
	[Bindable] private var httpAcTiposAntecedentes : HTTPServices = new HTTPServices;
	[Bindable] private var _xmlTipoAntecedente : XML = <antecedente id_antecedente_ingresos="0" estado ="" id_antecedente="0" antecedente="" observaciones="" accion="" fecha="" />;
	private var _accion : String;
	[Bindable] private var id_tipo_antec:String;
	
	public function get xmlNuevoAntecedente():XML
	{
		return _xmlTipoAntecedente.copy();
	}
	
	public function set xmlNuevoAntecedente(ant:XML):void
	{
		_xmlTipoAntecedente = ant;
		_accion = "editar";
	}
	
	public function set xmlNuevoAntecedente2(ant:XML):void
	{
		_xmlTipoAntecedente = ant;
		_accion = "eliminar";
	}
		
	//inicializa las variales necesarias para el modulo
	public function fncInit():void
	{	
		//preparo el PopUp Para que se cierre con esc y marco el default button
		this.defaultButton = btnGrabar;
		this.addEventListener(KeyboardEvent.KEY_UP,function(e:KeyboardEvent):void{if (e.keyCode==27) btnCancel.dispatchEvent(new MouseEvent(MouseEvent.CLICK))});		
		// escucho evento de los botones
		btnCancel.addEventListener("click",fncCerrar);
		btnGrabar.addEventListener("click",fncConfirmar);
		// si se trata de una edicion cargo el valor a editar
		if (_accion == "editar"){
			
			txaObservaciones.text = _xmlTipoAntecedente.@observaciones;
			_xmlTipoAntecedente.@accion = 'Modificó';		
		} else if (_accion == "eliminar") {
			_xmlTipoAntecedente.@accion = 'Quitó';
		} else {
			_xmlTipoAntecedente.@accion = 'Agregó';
		}
		httpsArmarXmlTipoAntec.send();
		cmbAntecedente.selectedIndex = 0;	
	}
			
	private function fncCerrar(e:Event):void
	{
		PopUpManager.removePopUp(this)	
	}
	
	private function fncHttpsArmarXmlTipoAntecFault(error:String) : void	
	{
	 	Alert.show("Error en la comunicación con el servidor: " + error,"E R R O R");	 	
	}						

	private function fncHttpsArmarXmlTipoAntecResult(e:Event) : void 			
	{  
	 	//Esto poner siempre -----------------------------------
	 	// Control de Acceso > > > > > > > > > > > > > > > >
	 	_acceso = (e.target as HTTPService).lastResult._acceso;
	    dispatchEvent(new Event("eveModulosHttpsResult"));
	    //Alert.show('se dispara evento eveModulosHttpsResult'); 
	    // Control de Acceso < < < < < < < < < < < < < < < <
	    //------------------------------------------------------
	    
	    // Aquí analizo y manipulo el resultado obtenido:
	 	var error : String = httpsArmarXmlTipoAntec.lastResult.error;
	 	if (error.length>0) {
	 	  	Alert.show(error,"E R R O R");
	 	} else {
	 		cmbTipoAntec.enabled = true;
	 	  	cmbTipoAntec.dataProvider = httpsArmarXmlTipoAntec.lastResult.tiposantecedentes.tipoantecedente.descripcion;
	 	  	if (_accion == "editar" || _accion == "eliminar") {
	 	  		for(var i:uint = 0; i<httpsArmarXmlTipoAntec.lastResult.tiposantecedentes.tipoantecedente.length(); i++)
			    {
			    	if(httpsArmarXmlTipoAntec.lastResult.tiposantecedentes.tipoantecedente[i].descripcion == _xmlTipoAntecedente.@descripcion) {
			     	  	cmbTipoAntec.selectedIndex  = i;
			     	}
			    }
			    cmbAntecedente.enabled = false;
				id_tipo_antec = httpsArmarXmlTipoAntec.lastResult.tiposantecedentes.tipoantecedente[cmbTipoAntec.selectedIndex].id_tipo_antec;
				httpsArmarXmlAntec.send();
	 	  	}	 	  		 	  		  	
	    }	 	
	}
	
	private function fncHttpsArmarXmlAntecFault(error:String) : void	
	{
	 	Alert.show("Error en la comunicación con el servidor: " + error,"E R R O R");	 	
	}
	 
	private function fncHttpsArmarXmlAntecResult(e:Event) : void 
	{  
	 	//Esto poner siempre -----------------------------------
	 	// Control de Acceso > > > > > > > > > > > > > > > >
	 	_acceso = (e.target as HTTPService).lastResult._acceso;
	    dispatchEvent(new Event("eveModulosHttpsResult"));
	    //Alert.show('se dispara evento eveModulosHttpsResult'); 
	    // Control de Acceso < < < < < < < < < < < < < < < <
	    //------------------------------------------------------
	    
	    // Aquí analizo y manipulo el resultado obtenido:
	 	var error : String = httpsArmarXmlAntec.lastResult.error;
	 	if (error.length>0) {
	 	  	Alert.show(error,"E R R O R");
	 	} else { 
			cmbAntecedente.enabled = true;
			cmbAntecedente.dataProvider = httpsArmarXmlAntec.lastResult.antecedentes.antecedente.antecedente;			
			if (_accion == "editar" || _accion == "eliminar") {
				for(var i:uint = 0; i<httpsArmarXmlAntec.lastResult.antecedentes.antecedente.length(); i++)
			    {
			    	if(httpsArmarXmlAntec.lastResult.antecedentes.antecedente[i].antecedente == _xmlTipoAntecedente.@antecedente) {
			     	  	cmbAntecedente.selectedIndex  = i;
			     	}
			    }
			    cmbAntecedente.close();
			} else {
				cmbAntecedente.open();
				cmbAntecedente.setFocus();	
			}			
		} 	 		 	
	}
	
	private function fncConfirmar(e:Event):void
	{
		var error:Array = Validator.validateAll([validObservaciones]);
		if (error.length>0) {
			((error[0] as ValidationResultEvent).target.source as UIComponent).setFocus();
		} else if (cmbAntecedente.selectedIndex==0) {
			Alert.show('Debe seleccionar un antecedente válido');
			cmbAntecedente.setFocus();
		} else {
			_xmlTipoAntecedente.@descripcion = cmbTipoAntec.text;
			_xmlTipoAntecedente.@antecedente = cmbAntecedente.text;
			_xmlTipoAntecedente.@id_antecedente = httpsArmarXmlAntec.lastResult.antecedentes.antecedente[cmbAntecedente.selectedIndex].id_antecedente;			
						
			var fechaActual:Date = new Date();
			var dia:int = fechaActual.getDate();
			var mes:int = fechaActual.getMonth() + 1;
			var strDia:String;
			var strMes:String;			
			if (dia < 10) {
				strDia = '0' + dia.toString();
			} else {
				strDia = dia.toString();
			}
			if (mes < 10) {
				strMes = '0' + mes.toString();
			} else {
				strMes = mes.toString();
			}			
			_xmlTipoAntecedente.@observaciones = txaObservaciones.text;
			_xmlTipoAntecedente.@fecha = strDia + '/' + strMes + '/' + fechaActual.getFullYear();
			_xmlTipoAntecedente.@medico = parentApplication.controlAcceso.getUsuarioNombre;			
			dispatchEvent(new Event("EventConfirmarAntecedente"));
		}	
	}