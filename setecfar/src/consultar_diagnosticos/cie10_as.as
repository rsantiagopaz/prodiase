// ActionScript file
	import clases.HTTPServices;
	import flash.events.Event;
	import flash.events.KeyboardEvent;
	import mx.events.ListEvent;
	import mx.managers.PopUpManager;
	import mx.rpc.events.ResultEvent;

	include "../control_acceso.as";
	
	[Bindable] private var httpDiagnosticos : HTTPServices = new HTTPServices;
	
	private function fncInit():void
	{
		httpDiagnosticos.url = "consultar_diagnosticos/cie10.php";
		httpDiagnosticos.addEventListener("acceso",acceso);
		// escucho evento de los botones
		btnCerrar.addEventListener("click",fncCerrar);
		txtDiagnostico.addEventListener("change",fncTraerDiagnosticos);
		btnBuscar.addEventListener("click",fncTraerDiagnosticosBoton);
	}
	
	private function fncCerrar(e:Event):void{
		dispatchEvent(new Event("SelectPrincipal"));
	}
		
	private function fncTraerDiagnosticos(e:Event):void{
		if (txtDiagnostico.text.length==3){
			httpDiagnosticos.send({rutina:"traer_cie10",filter:txtDiagnostico.text});
		}
		if(txtDiagnostico.text.length>3 && gridCie10.dataProvider){
		
	  		gridCie10.dataProvider.filterFunction = filtroTexto;
            gridCie10.dataProvider.refresh();			
		}
	}
	
	private function filtroTexto (item : Object) : Boolean
	{
		//return item.@descripcion.toString().substr(0, txtDiagnostico.text.length).toLowerCase() == txtDiagnostico.text.toLowerCase();   
		var isMatch:Boolean = false;
		if(item.@descripcion.toString().toLowerCase().search(txtDiagnostico.text.toLowerCase()) != -1)
		{
			isMatch = true;
		}		
		return isMatch;
	}
	
	private function fncTraerDiagnosticosBoton(e:Event):void{
		httpDiagnosticos.send({rutina:"traer_cie10",filter:txtDiagnostico.text});
	}
	
