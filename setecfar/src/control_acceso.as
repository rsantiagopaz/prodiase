		import paquete01.ControlAcceso;
		import clases.HTTPServices;
		// ActionScript file
		// Defino la propiedad "_acceso" de esta clase (this),
	 	// con el objeto de poner, si corresponde, el valor obtenido 
	 	// en el xml resultante de la solicitud HTTPService previo
	 	// paso por ControlAcceso.php, script que genera un xml con
	 	// el tag _login y _acceso = SI|NO  
	 	[Bindable] private var _acceso : String;
	
	 	// A su ves, defino el getter para _acceso:
	 	public function get get_acceso() : String
	  	{
	  		return _acceso;
	  	}
	  	
		private function acceso(event:Event):void
		{
	 		// Control de Acceso > > > > > > > > > > > > > > > >
	    	_acceso = (event.target as HTTPServices).lastResult._acceso;
	    	dispatchEvent(new Event("eveModulosHttpsResult"));
	    	
	    	// Control de Acceso < < < < < < < < < < < < < < < <
		}
