/*
 paquete01.ControlAcceso 
 Fecha de creación: 7/2/2009
 Fecha de última modificación: 4/3/2009
 Autor: Jorge Fabián Mitre  (jorgemitre@hotmail.com)
 Descripción: Contiene las clases propias creadas para el desarrollo de aplicaciones FX, 
 tales como ControlAcceso, para el control de acceso de usuarios y otras relacionadas.
 "SI TE SIRVE ESTE CODIGO, POR FAVOR, NO QUITES ESTAS LINEAS"
*/  
package paquete01
{
	 import flash.events.Event;
	 
	 import mx.controls.Alert;
	 import mx.rpc.events.FaultEvent;
	 import mx.rpc.events.ResultEvent;
	 import mx.rpc.http.HTTPService;
	 
	 
	
	/*
	
	********************* C o n t r o l A c c e s o 2 *************************
	
	Getters:
	--------
	SYSusuario_nombre            = mysql_result($result,0,SYSusuarionombre);
	SYSusuario_estado            = mysql_result($result,0,SYSusuario_estado);
    
    SYSusuario_sexo                  
    SYSusuario_dni                 
    SYSusuario_cuil                 
    SYSusuarioemail                  
    SYSusuario_estado                
    SYSusuario_comentario	
	
	SYSusuario_organismo_id      = mysql_result($result,0,organismo_id);
	SYSusuario_organismo         = mysql_result($result,0,organismo);
	SYSusuario_organismo_area_id = mysql_result($result,0,organismo_area_id);
	SYSusuario_organismo_area    = mysql_result($result,0,organismo_area);
	SYSusuario_organismo_area_mesa_entrada = mysql_result($result,0,organismo_area_mesa_entrada);
	
	SYSsistemas_perfiles_usuario (Arreglo)

	session_id : String
	autorizado : Boolean  

	  
	Setters:
	--------
	SYSusuario
	SYSpassword
	              
    
    Métodos:
    ========
    login: Dado el nombre de usuario, contraseña  e id del sistema (recibe estos 3 parámetros) 
    ------ (a través de los setters de la clase ControlAcceso), el método
           login de la clase ControlAcceso se conecta a la base de datos
           y chequea los datos para establecer la autorización del usuario.
           
           Puede suceder:
           
            1) Los datos son válidos, el usuario tiene autorización para trabajar con el sistema:
               Pone a la propiedad "autorizado" de la clase ControlAcceso el valor "true"
               También se cargan los valores de otras propiedades de la clase ControlAcceso tales como:
               usuario_nombre, usuario_estado, usuario_sexo, usuario_dni, 
               usuario_cuil, ... , usuario_estado,                 
               usuario_organismo_id, usuario_organismo, usuario_organismo_area_id, 
               usuario_organismo_area, usuario_organismo_area_mesa_entrada, 
               sistemas_perfiles_usuario (Arreglo), etc.
               La propiedad "sesion_id" toma el id de la sesión creada para el usuario 
               (la que es almacenada en la tabla _sesiones (_sesiones.sessionid) )               
               
            2) Los datos son invalidos, el usuario no tiene autorización para trabajar con el sistema:
               Pone a la propiedad "autorizado" de la clase ControlAcceso el valor "false".
               En la propiedad "error" de la clase ControlAcceso se almacena el mensaje del error por
               el cual el usuario no fue autorizado, ej: Nombre de usuario o contraseña inválida; El usuario
               está suspendido; El usuario no tiene atorización para el uso del sistema "ControlAcceso.sistema_id" 
                
    logout: Cierra sesión el usuario autorizado.
    ------- 

	
	*/
	
   // ------------------------------------------------------------
   // Clase:
   // ControlAcceso 
   // ------------------------------------------------------------
	public class ControlAcceso
	{
		
		[Bindable] public var httpsLogin : HTTPService = new HTTPService;  
		[Bindable] public var httpsLogout : HTTPService = new HTTPService;
		[Bindable] public var httpsServicios : HTTPService = new HTTPService;
		private var _xmlServicios : XML = <servicios></servicios>;
		private var _usuario : String;
		private var _usuario_id : String;
		private var _id_oas_usuario : String;
		private var _id_organismo_area_servicio : String;
		private var _usuario_servicio_id : String;
		private var _usuario_servicio : String;
		private var _password : String;
		private var _sistema_id : String;
		private var _usuario_nombre : String;
		private var _usuario_estado : uint;
		private var _sesion_id : String;
		private var _autorizado : Boolean;
		private var _usuario_organismo_id : String;
		private var _usuario_organismo : String;
		private var _usuario_organismo_area_id : String;
		private var _usuario_organismo_area : String;
		private var _usuario_sistemas_perfiles : XMLList;
		private var _usuario_organismo_area_mesa_entradas : Boolean;
		
		private var _mensaje_error : String;
		private var _mensaje_ok : String;
		
		
 
		// Constructor ------------------------
		public function ControlAcceso()
		 {
		  //Alert.show("Se instanció el objeto de la clase ControlAcceso");
		  //_autorizado = false; 
		 }

        // Métodos de la clase ControlAcceso: ---------------------
        
        // httpsLoginFault y httpsLoginResult no son métodos de la clase ControlAcceso,
        // sino una función q convoca el método login
        private function httpsLoginFault(e:Event):void
         {  _mensaje_error = "Falló la comunicación con el servidor (httpsLogin)";
         	Alert.show(_mensaje_error);
         	_autorizado = false;
         	dispatchEvent(new Event("eveLogueoFalloConexion"))
         }
        private function httpsLoginResult(e:Event):void
         {
         	//Alert.show("Resultado ok");
         	// Recorro el xml devuelto y si está todo ok recien pongo _autorizado = true
         	// Veamos que me devolvió HTTPservice
         	//Alert.show(httpsLogin.lastResult.toString());
         	var error : String = httpsLogin.lastResult.error; 
         	if (error.length > 0)
         	  {
         	   _mensaje_error = "ACCESO DENEGADO: " + error;	
               Alert.show(error, "ACCESO  D E N E G A D O  (Sistema "+_sistema_id+")");
               _autorizado = false;
              }
             else  
         	  {
         	  
         	   _autorizado             = true;
         	   _usuario_id             = httpsLogin.lastResult.ControlAcceso._usuario_id;
         	   _usuario                = httpsLogin.lastResult.ControlAcceso._usuario;
		       _sistema_id             = httpsLogin.lastResult.ControlAcceso._sistema_id;
	 	       _usuario_nombre         = httpsLogin.lastResult.ControlAcceso._usuario_nombre;
	 	       _usuario_estado         = httpsLogin.lastResult.ControlAcceso._usuario_estado;
	 	       _sesion_id              = httpsLogin.lastResult.ControlAcceso._sesion_id;
	 	       _usuario_organismo_id   = httpsLogin.lastResult.ControlAcceso._usuario_organismo_id;
	 	       
	 	       _usuario_organismo         = httpsLogin.lastResult.ControlAcceso._usuario_organismo;
	 	       _usuario_organismo_area    = httpsLogin.lastResult.ControlAcceso._usuario_organismo_area;
	 	       _usuario_organismo_area_id = httpsLogin.lastResult.ControlAcceso._usuario_organismo_area_id;
	 	       
	 	       _usuario_servicio_id = httpsLogin.lastResult.ControlAcceso.usuario_servicio_id;
	 	       _usuario_servicio = httpsLogin.lastResult.ControlAcceso.usuario_servicio;
	 	       _id_oas_usuario = httpsLogin.lastResult.ControlAcceso.id_oas_usuario;
	 	       _id_organismo_area_servicio = httpsLogin.lastResult.ControlAcceso.id_organismo_area_servicio;
	 	       
	 	       // Perfiles que posee el usuario	 	       
	 	       var total_perfiles : uint = httpsLogin.lastResult.ControlAcceso._usuario_sistemas_perfiles.perfil_id.length();
	 	       if (total_perfiles > 0)
	 	        {
	 	         _usuario_sistemas_perfiles = httpsLogin.lastResult.ControlAcceso._usuario_sistemas_perfiles;
	 	         //Alert.show(_usuario_sistemas_perfiles); 
	 	        } 
	 	       // -----------------------------
	 	       _mensaje_ok = "ACCESO PERMITIDO: " + httpsLogin.lastResult.ok;   
	 	       Alert.show(httpsLogin.lastResult.ok, "ACCESO  P E R M I T I D O  (Sistema "+_sistema_id+")");
         	  }
         	dispatchEvent(new Event("eveLogueoRealizado"));
         	  
         }
        public function httpsServicioResult(e:ResultEvent):void
        {
        	_xmlServicios = <servicios></servicios>;
        	_xmlServicios.appendChild(httpsServicios.lastResult.areaservicio);
        	dispatchEvent(new Event("eveServiciosTraidos"));
        }
        // Método "TraerServicios" de la clase ControlAcceso 
        public function TraerServicios(usuario:String) : void
         {
         	 // HTTPService
         	 var url : String;
         	 url = "paquete01/ControlAcceso.php?rutina=TraerServicios";
         	 url+= "&usuario=" + usuario;
         	 httpsServicios.url            = url;
         	 httpsServicios.method         = "GET";
         	 httpsServicios.resultFormat   = "e4x";
         	 httpsServicios.useProxy       = false;
         	 httpsServicios.addEventListener(FaultEvent.FAULT, httpsLoginFault);
         	 httpsServicios.addEventListener(ResultEvent.RESULT, httpsServicioResult);
         	 httpsServicios.send();
         	 
         }
        // Método "login" de la clase ControlAcceso 
        public function login(usuario:String, password:String, id_oas_usuario:String ,sistema_id:String) : void
         {
         	 _usuario    = usuario;
         	 _password   = password;
         	 _sistema_id = sistema_id;
         	 _id_oas_usuario =id_oas_usuario; 
         	 
         	 // Es importante poner en blanco las varialbes privadas 
         	 // cuando se realiza un nuevo login, de lo contrario 
         	 // pueden tener valores del anterior login  
	 	     _usuario_nombre  = "";
	 	     _usuario_estado  = 0;
	 	     _sesion_id       = "";
	 	     _usuario_organismo_id = "";
	 	     _mensaje_error = "";
	 	     _mensaje_ok = "";
         	 _autorizado=false;
         	 
			 _usuario_organismo_id : '';
			 _usuario_organismo = '';
			 _usuario_organismo_area_id = '';
			 _usuario_organismo_area = '';
			 _usuario_sistemas_perfiles = null;
			 _usuario_organismo_area_mesa_entradas = false;
         	 
         	 
         	 _usuario_servicio_id = '';
			 _usuario_servicio = '';
			 
         	 //Alert.show("Login: Usuario: " + _usuario + " Pass: " + _password + " Sistema: " + _sistema_id);
         	 
         	 // HTTPService
         	 //var httpsLogin : HTTPService = new HTTPService;
         	 var url : String;
         	 url = "paquete01/ControlAcceso.php?rutina=login";
         	 url+= "&usuario=" + usuario;
         	 url+= "&password=" + password;
         	 url+= "&sistema_id=" + sistema_id;
         	 url+= "&id_oas_usuario=" + id_oas_usuario;
         	 httpsLogin.url            = url;
         	 httpsLogin.method         = "GET";
         	 httpsLogin.resultFormat   = "e4x";
         	 httpsLogin.useProxy       = false;
         	 httpsLogin.addEventListener(FaultEvent.FAULT, httpsLoginFault);
         	 httpsLogin.addEventListener(ResultEvent.RESULT, httpsLoginResult);
         	// httpsLogin.showBusyCursor = true;
         	 httpsLogin.send();
         	 
         }
        
        // logout > > > > > > > > > > > > > > > > > > > > > > > > > > > > > > > > > > > > > > > >
        private function httpsLogoutResult(e:Event):void
         { 
           var ok : String = httpsLogout.lastResult.ok;
           var error : String = httpsLogout.lastResult.error;
           //Alert.show("ok: " + ok + " #" +ok.length.toString() + " Error: " + error + ' #' + error.length.toString());
           if (error.length>0)
             {
              Alert.show("Error: " + httpsLogout.lastResult.error,"CIERRE DE SESION")
             }
            else
             {
             	if(ok.length > 0)
             	 {
             	   Alert.show(httpsLogout.lastResult.ok,"CIERRE DE SESION: "+_usuario)
             	 }
             }
           dispatchEvent(new Event("eveLogoutRealizado"))
         	   
         }
         
         private function httpsLogoutFault(e:Event):void
         {
         	Alert.show("Falló la conexión para realizar el CIERRE DE SESION", "CIERRE DE SESION");
         }
        
        
        public function logout():void
         {
         	if(_autorizado)
         	  {
         	   //Cierro la sesión
         	   var url : String;
         	   url = "paquete01/ControlAcceso.php?rutina=logout";
         	   httpsLogout.url            = url;
         	   httpsLogout.method         = "GET";
         	   httpsLogout.resultFormat   = "e4x";
         	   httpsLogout.useProxy       = false;
         	   httpsLogout.addEventListener(FaultEvent.FAULT, httpsLogoutFault);
         	   httpsLogout.addEventListener(ResultEvent.RESULT, httpsLogoutResult);
         	   httpsLogout.send();
         	   //----------------	
         	  	
         	   _autorizado = false;
         	   
         	  }
         	 else
         	  {
         	  	Alert.show("No está AUTORIZADO aún", "CIERRE DE SESION");
         	  }  
         	
         }
        // Fin: logout < < < < < < < < < < < < < < < < < < < < < < < < < < < < < < < < < < < <  
        
        
        
        public function tienePerfil(perfil_id:String) : Boolean
         {
          var totalPerfiles : uint = _usuario_sistemas_perfiles.perfil_id.length().toString();
          var tienePerfil : Boolean = false;
          for(var i:uint=0; i<totalPerfiles;i++)
           {
           	if(_usuario_sistemas_perfiles.perfil_id[i]==perfil_id)
           	 {
           	  tienePerfil = true;
           	  i=totalPerfiles;
           	  //Alert.show("tiene perfil " + perfil_id); 	
           	 }
           }
         return tienePerfil; 	
         }
        


        public function cambiarPassword(e:Event) : Boolean
         {
         
         
          var resultado : Boolean = false;
          var cambiarPasswordPopUp : ControlAccesoCambiarPasswordPopUp = new ControlAccesoCambiarPasswordPopUp;
          dispatchEvent(new Event("eveCambiarPasswordPopUpAbrir"));
          //AddChild(cambiarPasswordPopUp);
          //PopUpManager.addPopUp(cambiarPasswordPopUp, e.target, false);
          
          return resultado; 	
         }



        // Definición de los setters: ------------------------------
        public function set setUsuario(usuario:String) : void
		 {
			_usuario = usuario;
		 }

        public function set setPassword(password:String) : void
		 {
			_password = password;
		 }

		public function set setSistema_id(sistema_id:String) : void
		 {
			_sistema_id = sistema_id;
		 }
		 
		 
		
		// Definición de los getters: ------------------------------
		 
		public function get getUsuario() : String
		 {
			return _usuario;
		 }
		
		public function get getUsuarioId() : String
		 {
			return _usuario_id;
		 } 
		public function get getPassword() : String
		 {
			return _password;
		 }
		 
		public function get getAutorizado() : Boolean
		 {
			return _autorizado;
		 }		 

		public function get getUsuarioNombre() : String
		 {
			return _usuario_nombre;
		 }
		 
		public function get getMensajeError() : String
		 {
			return _mensaje_error;
		 }
		
		public function get getMensajeOk() : String
		 {
			return _mensaje_ok;
		 }
		 
		public function get getUsuarioOrganismo() : String
		 {
		 	return _usuario_organismo;
		 }
		public function get getUsuarioOrganismoId() : String
		 {
		 	return _usuario_organismo_id;
		 } 
		 
		public function get getUsuarioOrganismoArea() : String
		 {
		 	return _usuario_organismo_area;
		 } 
		
		public function get getUsuarioOrganismoAreaId() : String
		 {
		 	return _usuario_organismo_area_id;
		 }		
		 public function get getSesionId() : String
		 {
		 	return _sesion_id;
		 } 

		public function get xmlServicios():XML
		{
			return _xmlServicios.copy();
		}
		
		public function get usuario_servicio():String
		{
			return _usuario_servicio;
		}
		
		public function get usuario_servicio_id():String
		{
			return _usuario_servicio_id;
		}
		
		public function get id_oas_usuario():String
		{
			return _id_oas_usuario;
		}
		
		public function get id_organismo_area_servicio():String
		{
			return _id_organismo_area_servicio;
		}
		
		 // Dato importante:
		 /* Se puede usar el mismo nombre de la función para definir un setter y un getter
		    Por ej. podemos tener:
		     public function set usuario(u:String):void
		     y
		     public function get usuario():void
		     
		 */
	}	
	
	
	
}