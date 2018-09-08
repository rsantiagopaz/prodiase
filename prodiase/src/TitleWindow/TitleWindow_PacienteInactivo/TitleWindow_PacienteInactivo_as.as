// ActionScript file
import TitleWindow.TitleWindow_PacienteInactivo.TitleWindow_PacienteInactivo;

import clases.HTTPServices;

import flash.events.Event;
import flash.events.MouseEvent;

import mx.collections.ArrayCollection;
import mx.controls.Alert;
import mx.events.CloseEvent;
import mx.formatters.NumberFormatter;
import mx.rpc.events.ResultEvent;

public var id_paciente : String;
[Bindable] public var xmlDepositos : XML;
[Bindable] public var xmlObrasSociales : XML;

private var httpService : HTTPServices;
private var nmf : NumberFormatter = new NumberFormatter;
private var gdp: Array = new Array;


private function TitleWindow_creationComplete() : void {
	dtfFecha.addEventListener("change", dtfFecha_change);
	btnBuscar.addEventListener("click", btnBuscar_click);
	btnTodos.addEventListener("click", btnTodos_click);
	btnNinguno.addEventListener("click", btnNinguno_click);
	btnImprimir.addEventListener("click", btnImprimir_click);
	btnInactivo.addEventListener("click", btnInactivo_click);

	var aux : Date = new Date;
	aux.setMonth(aux.getMonth() - 6);
	dtfFecha.selectedDate = aux;
	
	dtgTratamiento.dataProvider = new ArrayCollection(gdp);
}


private function btnBuscar_click(e: MouseEvent): void {
	var contexto: TitleWindow_PacienteInactivo = this;
	contexto.enabled = false;
	
  	httpService = new HTTPServices;
	httpService.url = "TitleWindow/TitleWindow_PacienteInactivo/TitleWindow_PacienteInactivo.php";
	httpService.addEventListener(ResultEvent.RESULT, function():void{
		gdp = new Array;
		
	 	xmlModel.pacientes = (httpService.lastResult as XML);
	 	
		for each (var item: XML in xmlModel.pacientes.paciente) {
			gdp.push({seleccionar: false, id_paciente: item.@id_paciente, persona_nombre: item.@persona_nombre, persona_dni: item.@persona_dni, fecha_ingreso: item.@fecha_ingreso, fecha: item.@fecha, estado: item.@estado});
		}
		
		lblCantidad.text = "Cantidad buscada: " + gdp.length;
		dtgTratamiento.dataProvider = new ArrayCollection(gdp);
		
	 	contexto.enabled = true;
	});
	httpService.send({rutina: "leer_paciente", fecha: dtfFecha.fechaSeleccionada});
}


private function dtfFecha_change(e: Event): void {
	lblCantidad.text = "Cantidad buscada: 0";

	gdp = new Array;
	dtgTratamiento.dataProvider = new ArrayCollection(gdp);
}


private function btnTodos_click(e: MouseEvent): void {
	for each (var item: Object in dtgTratamiento.dataProvider) {
		item.seleccionar = true;
	}
	
	(dtgTratamiento.dataProvider as ArrayCollection).refresh();
}


private function btnNinguno_click(e: MouseEvent): void {
	for each (var item: Object in dtgTratamiento.dataProvider) {
		item.seleccionar = false;
	}
	
	(dtgTratamiento.dataProvider as ArrayCollection).refresh();
}


private function btnImprimir_click(e: MouseEvent): void {
	var aux : XML = <root/>;
	var ac : ArrayCollection = dtgTratamiento.dataProvider as ArrayCollection;
	for (var i : int = 0; i < ac.length; i++) {
		if (ac[i].seleccionar) {
			aux.item[i] = <item/>;
			aux.item[i].@id_paciente = ac[i].id_paciente;
			aux.item[i].@persona_nombre = ac[i].persona_nombre;
			aux.item[i].@persona_dni = ac[i].persona_dni;
			aux.item[i].@fecha_ingreso = ac[i].fecha_ingreso;
			aux.item[i].@fecha = ac[i].fecha;
		}
	}
	
  	httpService = new HTTPServices;
	httpService.url = "TitleWindow/TitleWindow_PacienteInactivo/TitleWindow_PacienteInactivo.php";
	httpService.addEventListener(ResultEvent.RESULT, function():void{
		navigateToURL(new URLRequest("TitleWindow/TitleWindow_PacienteInactivo/TitleWindow_PacienteInactivo.php?rutina=imprimir&fecha=" + dtfFecha.fechaSeleccionada));
	});
	httpService.send({rutina: "guardar_inactivo", model: aux.toXMLString()});
}


private function btnInactivo_click(e: MouseEvent): void {
	var contexto: TitleWindow_PacienteInactivo = this;
	
	Alert.show("Desea pasar a inactivo los pacientes seleccionados?", "Atención", Alert.OK | Alert.CANCEL, null, function(e: CloseEvent):void{
		if (e.detail==Alert.OK) {
			contexto.enabled = false;
			
			var aux : XML = <root/>;
			var ac : ArrayCollection = dtgTratamiento.dataProvider as ArrayCollection;
			for (var i : int = 0; i < ac.length; i++) {
				if (ac[i].seleccionar) {
					aux.item[i] = <item/>;
					aux.item[i].@id_paciente = ac[i].id_paciente;
					aux.item[i].@persona_nombre = ac[i].persona_nombre;
					aux.item[i].@persona_dni = ac[i].persona_dni;
					aux.item[i].@fecha_ingreso = ac[i].fecha_ingreso;
					aux.item[i].@fecha = ac[i].fecha;
				}
			}
			
		  	httpService = new HTTPServices;
			httpService.url = "TitleWindow/TitleWindow_PacienteInactivo/TitleWindow_PacienteInactivo.php";
			httpService.addEventListener(ResultEvent.RESULT, function():void{
			  	httpService = new HTTPServices;
				httpService.url = "TitleWindow/TitleWindow_PacienteInactivo/TitleWindow_PacienteInactivo.php";
				httpService.addEventListener(ResultEvent.RESULT, function():void{
					lblCantidad.text = "Cantidad buscada: 0";
				
					gdp = new Array;
					dtgTratamiento.dataProvider = new ArrayCollection(gdp);
					
					contexto.enabled = true;
					
					Alert.show("Proceso de pacientes inactivos terminó correctamente");
				});
				
				httpService.send({rutina: "pasar_inactivo"});
			});
			httpService.send({rutina: "guardar_inactivo", model: aux.toXMLString()});
		}
	}, null, Alert.CANCEL);
}
