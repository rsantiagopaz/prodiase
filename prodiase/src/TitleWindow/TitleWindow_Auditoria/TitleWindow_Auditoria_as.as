// ActionScript file

import com.adobe.flex.extras.controls.AutoComplete;
import mx.utils.StringUtil;
import mx.events.ListEvent;

[Bindable] public var xmlDepositos : XML;

private var varAutoComplete : AutoComplete;

private function TitleWindow_creationComplete() : void {
	btnImprimir.addEventListener("click", btnImprimir_click);
	txtUsuario.addEventListener("focusOut", function():void{txtUsuario.text = mx.utils.StringUtil.trim(txtUsuario.text)});
	dtfDesde.selectedDate = new Date;
	dtfHasta.selectedDate = new Date;
}


private function btnImprimir_click(e:MouseEvent): void {
	var bandera : Boolean = true;
	xmlModel.fecha.@desde = dtfDesde.fechaSeleccionada;
	xmlModel.fecha.@hasta = dtfHasta.fechaSeleccionada;
	txtUsuario.errorString="";
	autocompleteProducto.errorString="";
	autocompletePaciente.errorString="";

	if (chkPaciente.selected && typeof autocompletePaciente.selectedItem != "xml") {
		bandera = false;
		autocompletePaciente.errorString="Debe seleccionar un paciente";
		autocompletePaciente.setFocus();
	}
	if (chkProducto.selected && typeof autocompleteProducto.selectedItem != "xml") {
		bandera = false;
		autocompleteProducto.errorString="Debe seleccionar un producto";
		autocompleteProducto.setFocus();
	}
	if (chkUsuario.selected && txtUsuario.text=="") {
		bandera = false;
		txtUsuario.errorString="Debe ingresar un nombre de usuario";
		txtUsuario.setFocus();
	}
	
	if (bandera) {
		var x : XML = xmlModel.copy();
		
		//var h : HTTPService = new HTTPService;
		//var url : String = h.rootURL.substring(0, h.rootURL.lastIndexOf("/")) + "/impresion.php";
		//navigateToURL(new URLRequest(url + "?rutina=consulta_combinada&model=" + encodeURIComponent(x.toXMLString())));
		//Alert.show(cboDeposito.selectedItem.toXMLString());
		navigateToURL(new URLRequest("TitleWindow/TitleWindow_Auditoria/TitleWindow_Auditoria.php?model=" + encodeURIComponent(x.toXMLString())));		
	}
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