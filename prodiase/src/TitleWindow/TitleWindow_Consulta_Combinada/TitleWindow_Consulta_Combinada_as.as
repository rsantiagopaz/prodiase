// ActionScript file

import clases.HTTPServices;

import com.adobe.flex.extras.controls.AutoComplete;

import mx.controls.Alert;
import mx.events.ListEvent;

private var varAutoComplete : AutoComplete;

private function TitleWindow_creationComplete() : void {
	var httpService: HTTPServices = new HTTPServices;
	httpService.method = "POST";
	httpService.url = "TitleWindow/TitleWindow_NuevoPaciente/TitleWindow_NuevoPaciente.php";
	httpService.addEventListener("result", function():void{
		cboTipo_paciente.dataProvider = httpService.lastResult.tipos_pacientes;
	});
	httpService.send({rutina:"leer_tipos_pacientes"});
	
	btnImprimir.addEventListener("click", btnImprimir_click);
	dtfDesde.selectedDate = new Date;
	dtfHasta.selectedDate = new Date;
}


private function btnImprimir_click(e:MouseEvent): void {
	xmlModel.rbt0.fecha_ingreso.@desde = dtfDesde.fechaSeleccionada;
	xmlModel.rbt0.fecha_ingreso.@hasta = dtfHasta.fechaSeleccionada;
	if (typeof autocompleteObraSocial.selectedItem!="xml") {
		xmlModel.rbt0.obra_social.@id_financiador = "";
	} else {
		xmlModel.rbt0.obra_social.@id_financiador = autocompleteObraSocial.selectedItem.@id;
	}
	
	var x : XML = xmlModel.copy();
	delete x.rbt0.medico.row.descrip;
	delete x.rbt0.producto.row.descrip;
	
	var h : HTTPService = new HTTPService;
	var url : String = h.rootURL.substring(0, h.rootURL.lastIndexOf("/")) + "/impresion.php";
	navigateToURL(new URLRequest(url + "?rutina=consulta_combinada&model=" + encodeURIComponent(x.toXMLString())));
}


public function varAutoComplete_change(e: ListEvent) : void {
	varAutoComplete=(e.target as AutoComplete);
	if (varAutoComplete.text.length==3) {
		httpsAutoComplete.send({rutina: varAutoComplete.id, descrip: varAutoComplete.text});
	}
}


private function httpsAutoComplete_result() : void {
	varAutoComplete.typedText = varAutoComplete.text;
	varAutoComplete.dataProvider = httpsAutoComplete.lastResult.row;
}