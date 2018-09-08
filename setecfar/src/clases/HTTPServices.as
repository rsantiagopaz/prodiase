package clases
{
	import flash.events.Event;
	import flash.net.URLRequestMethod;
	
	import mx.controls.Alert;
	import mx.rpc.events.FaultEvent;
	import mx.rpc.events.ResultEvent;
	import mx.rpc.http.mxml.HTTPService;

	public class HTTPServices extends HTTPService
	{  
		public function HTTPServices(rootURL:String=null, destination:String=null)
		{
			//TODO: implement function
			super(rootURL, destination);
			this.method = URLRequestMethod.POST;
			this.addEventListener(FaultEvent.FAULT,HttpsFault);
			this.addEventListener(ResultEvent.RESULT,fncResult);
			this.useProxy = false;
			this.resultFormat = "e4x";
			this.showBusyCursor = true;
		}
		
		private function HttpsFault(event:FaultEvent):void
		{
			Alert.show(event.fault.faultString, "Error"); 
		}
		
		private function fncResult(event:Event):void
		{
	    	dispatchEvent(new Event("acceso"));
	    
	    	// Veo si no hay mensajes de errores en el xml
	 		var error : String = this.lastResult.error;
	 	
	 		if (error.length>0)
			{
	 	  		Alert.show(error,"E R R O R");
	 	  		event.stopImmediatePropagation(); 	
			}
		}
		
	}
}