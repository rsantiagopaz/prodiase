// ActionScript file
import Report.Report_Historial;

import TitleWindow.TitleWindow_Impresion.TitleWindow_Impresion;

import clases.HTTPServices;

import com.adobe.flex.extras.controls.AutoComplete;

import mx.controls.Alert;
import mx.rpc.events.ResultEvent;

import org.doc.Document;


public var xmlDepositos : XML;
public var id_deposito : String;


private var httpService : HTTPServices;
private var _varAutoComplete : AutoComplete;



private function TitleWindow_creationComplete() : void {
	dflDesde.selectedDate = new Date;
	dflDesde.selectedDate.setMonth(dflDesde.selectedDate.getMonth() - 1);
	
	dflHasta.selectedDate = new Date;
	btnVer.addEventListener("click", btnVer_click);
}

private function btnVer_click(e:MouseEvent): void {
	var h : HTTPService = new HTTPService;
	var url : String = h.rootURL.substring(0, h.rootURL.lastIndexOf("/")) + "/impresion.php";
	autocompleteHospital.errorString = "";
	autocompletePaciente.errorString = "";
	if (rdb0.selected && rdb3.selected && typeof autocompleteHospital.selectedItem!="xml") {
		autocompleteHospital.errorString = "Debe seleccionar un hospital/area válido.";
		autocompleteHospital.setFocus();
	} else if (rdb4.selected && rdb7.selected && typeof autocompletePaciente.selectedItem!="xml") {
		autocompletePaciente.errorString = "Debe seleccionar un paciente.";
		autocompletePaciente.setFocus();
	} else if (rdb1.selected){
		navigateToURL(new URLRequest("impresion.php?rutina=historial_entregas" + "&a_depositos=true" + "&id_deposito=" + id_deposito + "&deposito=" + xmlDepositos.deposito.(@id_deposito==id_deposito).@denominacion + "&desde=" + dflDesde.fechaSeleccionada + "&hasta=" + dflHasta.fechaSeleccionada));
	} else if (rdb5.selected){
		navigateToURL(new URLRequest(url + "?rutina=entregas_por_producto&desde=" + dflDesde.fechaSeleccionada + "&hasta=" + dflHasta.fechaSeleccionada));
	} else if (rdb0.selected && rdb2.selected){
		navigateToURL(new URLRequest("impresion.php?rutina=historial_entregas" + "&en_gral=true" + "&id_deposito=" + id_deposito + "&deposito=" + xmlDepositos.deposito.(@id_deposito==id_deposito).@denominacion + "&desde=" + dflDesde.fechaSeleccionada + "&hasta=" + dflHasta.fechaSeleccionada));
	} else if (rdb0.selected && rdb3.selected){
		navigateToURL(new URLRequest("impresion.php?rutina=historial_entregas" + "&en_particular=true" + "&organismo_area_id=" + autocompleteHospital.selectedItem.@id + "&id_deposito=" + id_deposito + "&deposito=" + xmlDepositos.deposito.(@id_deposito==id_deposito).@denominacion + "&desde=" + dflDesde.fechaSeleccionada + "&hasta=" + dflHasta.fechaSeleccionada));
	} else if (rdb4.selected && rdb6.selected){
		if (chk9.selected) {
			navigateToURL(new URLRequest("impresion.php?rutina=entregas_a_pacientes" + "&en_gral=true" + "&id_deposito=" + id_deposito + "&diarios=" + ((chk10.selected) ? "true" : "false") + "&deposito=" + xmlDepositos.deposito.(@id_deposito==id_deposito).@denominacion + "&desde=" + dflDesde.fechaSeleccionada + "&hasta=" + dflHasta.fechaSeleccionada + "&id_financiador=" + ((typeof autocompleteObraSocial.selectedItem!="xml") ? "NULL" : autocompleteObraSocial.selectedItem.@id)));
		} else {
			navigateToURL(new URLRequest("impresion.php?rutina=entregas_a_pacientes" + "&en_gral=true" + "&id_deposito=" + id_deposito + "&diarios=" + ((chk10.selected) ? "true" : "false") + "&deposito=" + xmlDepositos.deposito.(@id_deposito==id_deposito).@denominacion + "&desde=" + dflDesde.fechaSeleccionada + "&hasta=" + dflHasta.fechaSeleccionada));
		}
	} else if (rdb4.selected && rdb7.selected){
		if (chk9.selected) {
			navigateToURL(new URLRequest("impresion.php?rutina=entregas_a_pacientes" + "&en_particular=true" + "&id_paciente=" + autocompletePaciente.selectedItem.@id + "&id_deposito=" + id_deposito + "&deposito=" + xmlDepositos.deposito.(@id_deposito==id_deposito).@denominacion + "&desde=" + dflDesde.fechaSeleccionada + "&hasta=" + dflHasta.fechaSeleccionada + "&id_financiador=" + ((typeof autocompleteObraSocial.selectedItem!="xml") ? "NULL" : autocompleteObraSocial.selectedItem.@id)));
		} else {
			navigateToURL(new URLRequest("impresion.php?rutina=entregas_a_pacientes" + "&en_particular=true" + "&id_paciente=" + autocompletePaciente.selectedItem.@id + "&id_deposito=" + id_deposito + "&deposito=" + xmlDepositos.deposito.(@id_deposito==id_deposito).@denominacion + "&desde=" + dflDesde.fechaSeleccionada + "&hasta=" + dflHasta.fechaSeleccionada));
		}
	} else if (rdb4.selected && rdb8.selected){
		if (chk9.selected) {
			navigateToURL(new URLRequest(url + "?rutina=entregas_pacientes_por_producto&desde=" + dflDesde.fechaSeleccionada + "&hasta=" + dflHasta.fechaSeleccionada + "&id_financiador=" + ((typeof autocompleteObraSocial.selectedItem!="xml") ? "NULL" : autocompleteObraSocial.selectedItem.@id)));
		} else {
			navigateToURL(new URLRequest(url + "?rutina=entregas_pacientes_por_producto&desde=" + dflDesde.fechaSeleccionada + "&hasta=" + dflHasta.fechaSeleccionada));
		}
	} else {

		var twnImpresion : TitleWindow_Impresion = new TitleWindow_Impresion;
		var reportHistorial : Report_Historial = new Report_Historial;
		
		twnImpresion.width = this.parentApplication.width;
		twnImpresion.height = this.parentApplication.height;
		PopUpManager.addPopUp(twnImpresion, this.parentApplication as DisplayObject, true);
		PopUpManager.centerPopUp(twnImpresion);
		
	  	httpService = new HTTPServices;
		httpService.url = "TitleWindow/TitleWindow_Historial/TitleWindow_Historial.php";
		//httpService.resultFormat="text";
		httpService.addEventListener(ResultEvent.RESULT, function():void{
			//Alert.show(httpService.lastResult as String);
			var xmlModel : XML = httpService.lastResult as XML; 
			xmlModel.@titulo = "Historial de entregas: " + xmlDepositos.deposito.(@id_deposito==id_deposito).@denominacion;
			xmlModel.@intervalo = "Desde " + dflDesde.fechaSeleccionada + " hasta " + dflHasta.fechaSeleccionada;
			reportHistorial.xmlModel = xmlModel;
			twnImpresion.preview.doc = new Document(reportHistorial, null, org.doc.PaperFormat.A4);
		});
		if (rdb1.selected) {
			httpService.send({rutina:"leer_historial", a_depositos: true, id_deposito: id_deposito, desde: dflDesde.fechaSeleccionada, hasta: dflHasta.fechaSeleccionada});
		} else if (rdb2.selected) {
			httpService.send({rutina:"leer_historial", en_gral: true, id_deposito: id_deposito, desde: dflDesde.fechaSeleccionada, hasta: dflHasta.fechaSeleccionada});
		} else if (rdb4.selected) {
			//httpService.send({rutina:"leer_historial", a_pacientes: true, desde: dflDesde.fechaSeleccionada, hasta: dflHasta.fechaSeleccionada});
			navigateToURL(new URLRequest("impresion.php?rutina=entregas_a_pacientes&desde=" + dflDesde.fechaSeleccionada + "&hasta=" + dflHasta.fechaSeleccionada));
		} else {
			httpService.send({rutina:"leer_historial", en_particular: true, organismo_area_id: autocompleteHospital.selectedItem.@id, id_deposito: id_deposito, desde: dflDesde.fechaSeleccionada, hasta: dflHasta.fechaSeleccionada});
		}
	}
}

private function _varAutoComplete_change(e:Event) : void {
	_varAutoComplete=(e.target as AutoComplete);
	if (_varAutoComplete.text.length==3) {
		httpsAutoComplete.send({rutina: _varAutoComplete.id, descrip: _varAutoComplete.text});
	}
}

private function httpsAutoComplete_result() : void {
	_varAutoComplete.typedText = _varAutoComplete.text;
	_varAutoComplete.dataProvider = httpsAutoComplete.lastResult.row;
}