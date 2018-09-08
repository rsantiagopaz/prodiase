// ActionScript file
import Report.Report_Pacientes;

import TitleWindow.TitleWindow_Impresion.TitleWindow_Impresion;

import clases.HTTPServices;

import flash.display.DisplayObject;
import flash.events.MouseEvent;

import mx.controls.Alert;
import mx.events.ListEvent;
import mx.managers.PopUpManager;

import org.doc.Document;



private var varAutoComplete : AutoComplete;

private function TitleWindow_creationComplete() : void {
	cboVer.addEventListener("change", function():void{
		autocompleteMedico.dataProvider = null;
		autocompleteMedico.text = "";
		varAutoComplete.typedText = "";
		xmlModel = null;
	});
	btnCerrar.addEventListener("click", btnCerrar_click);
	btnImprimir.addEventListener("click", btnImprimir_click);
	btnMostrarDatos.addEventListener("click", function():void{
		if (typeof autocompleteMedico.selectedItem =="xml") {
			var httpService : HTTPServices = new HTTPServices;
			httpService.url = "TitleWindow/TitleWindow_PacientesX/TitleWindow_PacientesX.php";
			httpService.addEventListener("result", function():void{
				//Alert.show(httpService.lastResult.toString());
				xmlModel = httpService.lastResult as XML;
			});
			//httpService.resultFormat="text";
			if (cboVer.selectedIndex==0) {
				httpService.send({rutina:"leer_pacientes", id: autocompleteMedico.selectedItem.@id, por: "medico"});
			} else if (cboVer.selectedIndex==1) {
				httpService.send({rutina:"leer_pacientes", id: autocompleteMedico.selectedItem.@id, por: "producto"});
			} else {
				httpService.send({rutina:"leer_pacientes", id: autocompleteMedico.selectedItem.@id, por: "tipopaciente"});
			}
		} else {
			xmlModel = null;
		}
	});
	autocompleteMedico.addEventListener(ListEvent.CHANGE, varAutoComplete_change);
}

public function btnImprimir_click(e:MouseEvent) : void {
	if (xmlModel != null) {
		var twnImpresion : TitleWindow_Impresion = new TitleWindow_Impresion;
		twnImpresion.width = this.width;
		twnImpresion.height = this.height;
		var reportPacientes : Report_Pacientes = new Report_Pacientes;
		
		twnImpresion.width = this.parentApplication.width;
		twnImpresion.height = this.parentApplication.height;
		PopUpManager.addPopUp(twnImpresion, this.parentApplication as DisplayObject, true);
		PopUpManager.centerPopUp(twnImpresion);
	
		xmlModel.@titulo = "Listado pacientes " + cboVer.text + ": " + autocompleteMedico.selectedItem.descrip;
		reportPacientes.xmlModel = xmlModel;
		//Alert.show(reportPacientes.rdg.columns.toString());
		//reportPacientes.rdg.columns[4].visible = false;
		//Alert.show(reportPacientes.body.toString());
		twnImpresion.preview.doc = new Document(reportPacientes, null, org.doc.PaperFormat.A4);
		//if (cboVer.selectedIndex==0) reportPacientes.rdg.columns[4].visible = false;
	}
}

public function btnCerrar_click(e:MouseEvent) : void {
	PopUpManager.removePopUp(this);
}

public function varAutoComplete_change(e: ListEvent) : void {
	varAutoComplete=(e.target as AutoComplete);
	if (varAutoComplete.text.length==3) {
		if (cboVer.selectedIndex==0) {
			httpsAutoComplete.send({rutina: "autocompleteMedico", descrip: varAutoComplete.text});
		} else if (cboVer.selectedIndex==1) {
			httpsAutoComplete.send({rutina: "autocompleteProducto", descrip: varAutoComplete.text});
		} else {
			httpsAutoComplete.send({rutina: "autocompleteTipoPaciente", descrip: varAutoComplete.text});
		}
		
	}
}

private function httpsAutoComplete_result() : void {
	varAutoComplete.typedText = varAutoComplete.text;
	varAutoComplete.dataProvider = httpsAutoComplete.lastResult.row;
}