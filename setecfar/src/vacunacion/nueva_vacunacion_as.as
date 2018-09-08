// ActionScript file
	
	import flash.events.Event;
	
	import mx.core.UIComponent;
	import mx.events.ValidationResultEvent;
	import mx.managers.PopUpManager;
	import mx.validators.Validator;
			
	[Bindable] private var _xmlVacuna : XML = <vacunaciones id_vacunacion="0" id_dosis="0" nombre="" enfermedades="" fecha="" />;
	[Bindable] private var _xmlVacunas:XML = <vacunas></vacunas>;
	private var _accion : String;
	
	public function get xmlVacuna():XML
	{
		return _xmlVacuna.copy();
	}
	
	public function set xmlVacuna(ant:XML):void
	{
		_xmlVacuna = ant;
		_accion = "editar";
	}
		
	//inicializa las variales necesarias para el modulo
	public function fncInit():void
	{	
		_xmlVacunas.appendChild(parentApplication.CONSULTA.xmlDatosPaciente.vacunas);
		//preparo el PopUp Para que se cierre con esc y marco el default button
		this.defaultButton = btnGrabar;
		this.addEventListener(KeyboardEvent.KEY_UP,function(e:KeyboardEvent):void{if (e.keyCode==27) btnCancel.dispatchEvent(new MouseEvent(MouseEvent.CLICK))});
		// escucho evento de los botones
		btnCancel.addEventListener("click",fncCerrar);
		btnGrabar.addEventListener("click",fncConfirmar);
		// si se trata de una edicion cargo el valor a editar
		if (_accion == "editar"){
			for(var i:uint = 0; i<cmbVacuna.dataProvider.length; i++)
		    {	
		    	var item:String = cmbVacuna.dataProvider[i].@nombre;					     	
		     	if(item == _xmlVacuna.@nombre)
		     	  {					     	  	
		     	  	cmbVacuna.selectedIndex  = i;
		     	  	break;
		     	  }
		    } 			
			dfVacunacion.text = _xmlVacuna.@fecha;						
		}			
	}
	
	
	private function fncCerrar(e:Event):void
	{
		PopUpManager.removePopUp(this)	
	}
	
	private function fncConfirmar(e:Event):void
	{	
		var error:Array = Validator.validateAll([validFecha,validVacuna]);	
		if (error.length>0) {
			((error[0] as ValidationResultEvent).target.source as UIComponent).setFocus();
		}else {			
			//_xmlVacuna.@nombre = acVacuna.text;
			_xmlVacuna.@id_dosis = cmbVacuna.selectedItem.@id_dosis;				
			_xmlVacuna.@nombre= cmbVacuna.selectedItem.@nombre;						
			_xmlVacuna.@enfermedades= cmbVacuna.selectedItem.@enfermedades;
			_xmlVacuna.@fecha = dfVacunacion.text;										
			dispatchEvent(new Event("EventConfirmarVacunacion"));
		}	
	}