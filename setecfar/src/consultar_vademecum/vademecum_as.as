// ActionScript file
	import clases.HTTPServices;
	import flash.events.Event;
	import flash.events.KeyboardEvent;
	import mx.events.ListEvent;
	import mx.managers.PopUpManager;
	import mx.rpc.events.ResultEvent;

	include "../control_acceso.as";
	
	[Bindable] private var httpVademecum : HTTPServices = new HTTPServices;
	
	private function fncInit():void
	{
		httpVademecum.url = "consultar_vademecum/vademecum.php";
		httpVademecum.addEventListener("acceso",acceso);
		// escucho evento de los botones
		btnCerrar.addEventListener("click",fncCerrar);
		txtDroga.addEventListener("change",fncTraerVademecum);
		btnBuscar.addEventListener("click",fncTraerVademecumBoton);
	}
	
	private function fncCerrar(e:Event):void{
		dispatchEvent(new Event("SelectPrincipal"));
	}
		
	private function fncTraerVademecum(e:Event):void{
		if (txtDroga.text.length==3){
			httpVademecum.send({rutina:"traer_vademecum",filter:txtDroga.text});
		}
		if(txtDroga.text.length>3 && gridVademecum.dataProvider){
	  		gridVademecum.dataProvider.filterFunction = filtroTexto;
            gridVademecum.dataProvider.refresh();			
		}
	}
	
	private function filtroTexto (item : Object) : Boolean
	{
		//return item.@monodroga.toString().substr(0, txtDroga.text.length).toLowerCase() == txtDroga.text.toLowerCase();   
		var isMatch:Boolean = false;
		if(item.@monodroga.toString().toLowerCase().search(txtDroga.text.toLowerCase()) != -1)
		{
			isMatch = true;
		}		
		return isMatch;	
	}
	
	private function fncTraerVademecumBoton(e:Event):void{
		httpVademecum.send({rutina:"traer_vademecum",filter:txtDroga.text});
	}
	
	
