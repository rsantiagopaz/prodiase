// ActionScript file
import Report.Report_Entrega;

import TitleWindow.TitleWindow_Impresion.TitleWindow_Impresion;

import clases.HTTPServices;

import flash.events.MouseEvent;

import mx.controls.Alert;
import mx.events.CloseEvent;
import mx.events.ListEvent;
import mx.managers.PopUpManager;
import mx.rpc.events.ResultEvent;

import org.doc.Document;



public var xmlDepositos: XML;
public var id_deposito: String;


private var varAutoComplete : AutoComplete;
[Bindable] private var httpPaciente : HTTPServices = new HTTPServices;


private function Module_creationComplete() : void {
	//autocompletePaciente.addEventListener(ListEvent.CHANGE, varAutoComplete_change);
	btnVerDatos.addEventListener("click", btnVerDatos_click);
	
	httpPaciente.url = "Module/Module_Principal/Module_Principal.php";
	//httpPaciente.method = "POST";
	//httpPaciente.addEventListener("acceso",acceso);

	autocompletePaciente.setFocus();
}


public function btnDesactivaritem_click(e:CloseEvent) : void {
	if (e.detail==Alert.OK) {
		if (drgEntregas.selectedItem.@id_deposito==id_deposito) {
			var twnImpresion : TitleWindow_Impresion = new TitleWindow_Impresion;
			twnImpresion.visible = false;
			twnImpresion.width = this.width;
			twnImpresion.height = this.height;
			var reportPacientes : Report_Entrega  = new Report_Entrega;
			
			twnImpresion.width = this.parentApplication.width;
			twnImpresion.height = this.parentApplication.height;
			PopUpManager.addPopUp(twnImpresion, this.parentApplication as DisplayObject, true);
			PopUpManager.centerPopUp(twnImpresion);
		
			reportPacientes.xmlModel.@entrega = "CONSTANCIA DE ENTREGA";
			reportPacientes.xmlModel.@fecha ="Fecha: " + drgEntregas.selectedItem.@fecha;
			reportPacientes.xmlModel.@paciente = xmlModel.paciente.@persona_nombre;
			reportPacientes.xmlModel.@dni = xmlModel.paciente.@persona_dni;
			reportPacientes.xmlModel.@autorizado = "Impreso por: " + parentApplication.controlAcceso.getUsuario;
			reportPacientes.xmlModel.@medico = xmlModel.paciente.@medico;
			reportPacientes.xmlModel.@firma = "Firma Paciente/Autorizado";
			reportPacientes.xmlModel.@deposito = drgEntregas.selectedItem.@deposito;
			reportPacientes.xmlModel.@obra_social_descrip = drgEntregas.selectedItem.@obra_social_descrip;
			reportPacientes.xmlModel.item_entrega = drgEntregas.selectedItem.item_entrega;
	
		//Alert.show(reportPacientes.rdg.columns.toString());
		//reportPacientes.rdg.columns[4].visible = false;
		//Alert.show(reportPacientes.body.toString());
		//twnImpresion.preview.doc = new Document(reportPacientes, null, org.doc.PaperFormat.A4);
			
			httpPaciente = new HTTPServices;
			httpPaciente.resultFormat = "text";
			httpPaciente.url = "Module/Module_Principal/Module_Principal.php";
			httpPaciente.addEventListener(ResultEvent.RESULT, function():void{
				if ((httpPaciente.lastResult as String) == "error") {
					PopUpManager.removePopUp(twnImpresion);
					Alert.show("Imposible realizar entrega, yá figura como entregada o anulada previamente.", "Atención");
				} else {
					twnImpresion.visible = true;
					twnImpresion.preview.doc = new Document(reportPacientes, null, org.doc.PaperFormat.A4);
					btnVerDatos.dispatchEvent(new MouseEvent("click"));
				}
			});
			httpPaciente.send({rutina:"entregar", id_entrega: drgEntregas.selectedItem.@id_entrega, id_paciente: xmlModel.paciente.@id_paciente});
		} else {
			Alert.show("No esta autorizado para hacer esta entrega.", "Atención");
		}
	}
}




private function btnVerDatos_click(e:MouseEvent) : void {
	if (typeof autocompletePaciente.selectedItem == "xml") {
		httpPaciente = new HTTPServices;
		httpPaciente.url = "Module/Module_Principal/Module_Principal.php";
		httpPaciente.addEventListener(ResultEvent.RESULT, function():void{
			//Alert.show(httpPaciente.lastResult.toString());
			xmlModel = httpPaciente.lastResult as XML;
			if (xmlModel.paciente.entrega.length() > 0) drgEntregas.selectedIndex = 0;
			btnNuevaEntrega.enabled = true;
		});
		//httpPaciente.resultFormat="text";
		httpPaciente.send({rutina:"leer_datos_paciente", id_paciente: autocompletePaciente.selectedItem.@id});
	} else {
		xmlModel = null;
		btnNuevaEntrega.enabled = false;
	}
}

public function varAutoComplete_change(e:ListEvent) : void {
	varAutoComplete=(e.target as AutoComplete);
	if (varAutoComplete.text.length==3) {
		httpsAutoComplete.send({rutina: varAutoComplete.id, descrip: varAutoComplete.text});
	}
}

private function httpsAutoComplete_result() : void {
	varAutoComplete.typedText = varAutoComplete.text;
	varAutoComplete.dataProvider = httpsAutoComplete.lastResult.row;
}
