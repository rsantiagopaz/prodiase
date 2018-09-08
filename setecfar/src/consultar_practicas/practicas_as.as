// ActionScript file
	import clases.HTTPServices;
	import flash.events.Event;
	import flash.events.KeyboardEvent;
	import mx.events.ListEvent;
	import mx.managers.PopUpManager;
	import mx.rpc.events.ResultEvent;

	include "../control_acceso.as";
	
	[Bindable] private var httpPracticas : HTTPServices = new HTTPServices;
	
	private function fncInit():void
	{
		httpPracticas.url = "consultar_practicas/practicas.php";
		httpPracticas.addEventListener("acceso",acceso);
		// escucho evento de los botones
		btnCerrar.addEventListener("click",fncCerrar);
		txtPractica.addEventListener("change",fncTraerPractica);
		btnBuscar.addEventListener("click",fncTraerPracticaBoton);
	}
	
	private function fncCerrar(e:Event):void{
		dispatchEvent(new Event("SelectPrincipal"));
	}
		
	private function fncTraerPractica(e:Event):void{
		if (txtPractica.text.length==3){
			httpPracticas.send({rutina:"traer_practicas",filter:txtPractica.text});
		}
		if(txtPractica.text.length>3 && gridEstudios.dataProvider){
		
	  		gridEstudios.dataProvider.filterFunction = filtroTexto;
            gridEstudios.dataProvider.refresh();			
		}
	}
	
	private function filtroTexto (item : Object) : Boolean
	{
		var isMatch:Boolean = false;
		if(item.@descripcion.toString().toLowerCase().search(txtPractica.text.toLowerCase()) != -1)
		{
			isMatch = true;
		}		
		return isMatch;                        
		
	}
	
	private function fncTraerPracticaBoton(e:Event):void{
		httpPracticas.send({rutina:"traer_practicas",filter:txtPractica.text});
	}
	
