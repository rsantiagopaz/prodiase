// ActionScript file


import Report.Report_Entrega;

import TitleWindow.TitleWindow_AgregaTratamiento.TitleWindow_AgregaTratamiento;
import TitleWindow.TitleWindow_Entrega.TitleWindow_Entrega;
import TitleWindow.TitleWindow_Impresion.TitleWindow_Impresion;
import TitleWindow.TitleWindow_NuevoPaciente.TitleWindow_NuevoPaciente;

import clases.HTTPServices;

import flash.display.DisplayObject;
import flash.events.MouseEvent;

import mx.controls.Alert;
import mx.events.CloseEvent;
import mx.events.ListEvent;
import mx.managers.PopUpManager;
import mx.rpc.events.ResultEvent;

import org.doc.Document;


private var boolImprimirComprobante : Boolean = false; 
private var varAutoComplete : AutoComplete;
[Bindable] private var httpPaciente : HTTPServices;
private var twnAgregaTratamiento : TitleWindow_AgregaTratamiento;
private var twnNuevoPaciente : TitleWindow_NuevoPaciente;
private var twnEntrega : TitleWindow_Entrega;
private var xmlDepositos : XML;


private function Module_creationComplete() : void {
	//autocompletePaciente.addEventListener(ListEvent.CHANGE, varAutoComplete_change);
	btnVerDatos.addEventListener("click", btnVerDatos_click);
	btnAgregarItem.addEventListener("click", btnAgregarItem_click);
	btnNuevoPaciente.addEventListener("click", btnNuevoPaciente_click);
	btnModificarDatos.addEventListener("click", btnModificarDatos_click);
	btnNuevaEntrega.addEventListener("click", btnNuevaEntrega_click);
	btnImprimirComprobante.addEventListener("click", btnImprimirComprobante_click);
	btnAnularEntrega.addEventListener("click", btnAnularEntrega_click);
	
 	var httpPaciente : HTTPServices = new HTTPServices;
	httpPaciente.url = "Module/Module_Principal/Module_Principal.php";
	httpPaciente.addEventListener(ResultEvent.RESULT, function():void{
	 	xmlDepositos = httpPaciente.lastResult as XML;
	});
	httpPaciente.send({rutina:"leer_depositos"});
	
	autocompletePaciente.setFocus();
}


private function btnImprimirComprobante_click(e:MouseEvent) : void {
	var twnImpresion : TitleWindow_Impresion = new TitleWindow_Impresion;
	twnImpresion.width = this.width;
	twnImpresion.height = this.height;
	var reportPacientes : Report_Entrega  = new Report_Entrega;
	
	twnImpresion.width = this.parentApplication.width;
	twnImpresion.height = this.parentApplication.height;
	PopUpManager.addPopUp(twnImpresion, this.parentApplication as DisplayObject, true);
	PopUpManager.centerPopUp(twnImpresion);

	reportPacientes.xmlModel.@entrega = "ORDEN DE ENTREGA";
	reportPacientes.xmlModel.@fecha ="Fecha: " + drgEntregas.selectedItem.@fecha;
	reportPacientes.xmlModel.@paciente = xmlModel.paciente.@persona_nombre;
	reportPacientes.xmlModel.@dni = xmlModel.paciente.@persona_dni;
	reportPacientes.xmlModel.@autorizado = "Impreso por: " + parentApplication.controlAcceso.getUsuario;
	reportPacientes.xmlModel.@medico = xmlModel.paciente.@medico;
	reportPacientes.xmlModel.@firma = "Firma Autorizada";
	reportPacientes.xmlModel.@deposito = drgEntregas.selectedItem.@deposito;
	reportPacientes.xmlModel.@obra_social_descrip = drgEntregas.selectedItem.@obra_social_descrip;
	reportPacientes.xmlModel.item_entrega = drgEntregas.selectedItem.item_entrega;


	//Alert.show(reportPacientes.rdg.columns.toString());
	//reportPacientes.rdg.columns[4].visible = false;
	//Alert.show(reportPacientes.body.toString());
	twnImpresion.preview.doc = new Document(reportPacientes, null, org.doc.PaperFormat.A4);
}


public function btnAnularEntrega_click(e:MouseEvent) : void {
	Alert.show("Desea anular la entrega seleccionada?", "Atención", Alert.OK | Alert.CANCEL, null, function(e:CloseEvent):void{
		if (e.detail==Alert.OK) {
			httpPaciente = new HTTPServices;
			httpPaciente.resultFormat = "text";
			httpPaciente.url = "Module/Module_Principal/Module_Principal.php";
			httpPaciente.addEventListener(ResultEvent.RESULT, function():void{
				if ((httpPaciente.lastResult as String) == "error") {
					Alert.show("Imposible anular entrega, yá figura como entregada o anulada previamente.", "Atención");
				}
				btnVerDatos.dispatchEvent(new MouseEvent("click"));
			});
			httpPaciente.send({rutina:"anular_entrega", id_entrega: drgEntregas.selectedItem.@id_entrega});
		}
	}, null, Alert.CANCEL);
}


private function btnNuevaEntrega_click(e:MouseEvent) : void {
	twnEntrega = new TitleWindow_Entrega;
	twnEntrega.addEventListener("eventAceptar", function():void{
 		httpPaciente = new HTTPServices;
		httpPaciente.url = "Module/Module_Principal/Module_Principal.php";
		httpPaciente.addEventListener(ResultEvent.RESULT, function():void{
			var xmlAux : XML = (httpPaciente.lastResult as XML);
			if (xmlAux.error_stock.toString() == "") {
				PopUpManager.removePopUp(twnEntrega);
				xmlModel = xmlAux;
				drgEntregas.selectedIndex = 0;
				btnImprimirComprobante.dispatchEvent(new MouseEvent("click"));
			} else {
				twnEntrega.cboDeposito.dispatchEvent(new ListEvent(ListEvent.CHANGE));
				
				Alert.show(xmlAux.error_stock.toString(), "Atención", 4, null, function():void{
					twnEntrega.dtgTratamiento.editedItemPosition = {columnIndex:5, rowIndex:0};
				});
			}
		});
		
		if (twnEntrega.cboObraSocial.selectedItem==null) {
			httpPaciente.send({rutina:"agregar_entrega", id_paciente: xmlModel.paciente.@id_paciente, id_financiador: null, model: twnEntrega.xmlModel.tratamientos});
		} else {
			httpPaciente.send({rutina:"agregar_entrega", id_paciente: xmlModel.paciente.@id_paciente, id_financiador: twnEntrega.cboObraSocial.selectedItem.@id_financiador.toString(), model: twnEntrega.xmlModel.tratamientos});
		}
	});
	
	twnEntrega.id_paciente = xmlModel.paciente.@id_paciente;
	
	twnEntrega.xmlObrasSociales = (xmlModel.paciente.obras_sociales[0] as XML);
	twnEntrega.xmlDepositos = xmlDepositos;
	
	PopUpManager.addPopUp(twnEntrega, this, true);
	PopUpManager.centerPopUp(twnEntrega);
	
}


private function btnModificarDatos_click(e:MouseEvent) : void {
	twnNuevoPaciente = new TitleWindow_NuevoPaciente;
	twnNuevoPaciente.id_paciente = xmlModel.paciente.@id_paciente;
	twnNuevoPaciente.addEventListener("eventAceptar", function():void{
		autocompletePaciente.setFocus();
		autocompletePaciente.selectedItem = null;
		autocompletePaciente.dataProvider = null;
		autocompletePaciente.text = "";
		btnVerDatos.dispatchEvent(new MouseEvent("click"));
	});
	PopUpManager.addPopUp(twnNuevoPaciente, this.parentDocument as DisplayObject, true);
	PopUpManager.centerPopUp(twnNuevoPaciente);
}

private function btnNuevoPaciente_click(e:MouseEvent) : void {
	twnNuevoPaciente = new TitleWindow_NuevoPaciente;
	twnNuevoPaciente.addEventListener("removedFromStage", function():void{
		autocompletePaciente.setFocus();
	});
	PopUpManager.addPopUp(twnNuevoPaciente, this.parentDocument as DisplayObject, true);
	PopUpManager.centerPopUp(twnNuevoPaciente);
}


private function btnAgregarItem_click(e:MouseEvent) : void {
	twnAgregaTratamiento = new TitleWindow_AgregaTratamiento;
	twnAgregaTratamiento.addEventListener("eventAceptar", function():void {
		var bandera : Boolean = true;
		for each (var item : XML in xmlModel.tratamientos.tratamiento) {
			if (item.@id_producto==twnAgregaTratamiento.xmlModel.tratamiento.@id_producto) {
				bandera = false;
				dtgTratamiento.selectedItem = item;
				Alert.show("El producto seleccionado ya está asignado a este tratamiento.", "Atención");
				break;
			}
		}
		if (bandera) {
			httpPaciente = new HTTPServices;
			httpPaciente.url = "Module/Module_Principal/Module_Principal.php";
			httpPaciente.addEventListener(ResultEvent.RESULT, function() : void {
				xmlModel = httpPaciente.lastResult as XML;
				for each (var item : XML in xmlModel.tratamientos.tratamiento) {
					if (item.@id_paciente_producto==xmlModel.@id_paciente_producto) {
						dtgTratamiento.selectedItem = item;
					}
				}
			});
			twnAgregaTratamiento.xmlModel.tratamiento.@id_paciente = autocompletePaciente.selectedItem.@id;
			httpPaciente.send({rutina:"agregar_tratamiento", model: twnAgregaTratamiento.xmlModel});
		}
	});
	PopUpManager.addPopUp(twnAgregaTratamiento, this, true);
	PopUpManager.centerPopUp(twnAgregaTratamiento);
}

private function btnVerDatos_click(e:MouseEvent) : void {
	if (typeof autocompletePaciente.selectedItem == "xml") {
		httpPaciente = new HTTPServices;
		httpPaciente.url = "Module/Module_Principal/Module_Principal.php";
		httpPaciente.addEventListener(ResultEvent.RESULT, function() : void {
			xmlModel = httpPaciente.lastResult as XML;
		});
		httpPaciente.send({rutina:"leer_datos_paciente", id_paciente: autocompletePaciente.selectedItem.@id});
		btnAgregarItem.enabled = true;
		btnNuevaEntrega.enabled = true;
		btnModificarDatos.enabled = true;
	} else {
		xmlModel = null;
		btnAgregarItem.enabled = false;
		btnNuevaEntrega.enabled = false;
		btnModificarDatos.enabled = false;
	}
}

public function btnDesactivaritem_click(e:CloseEvent) : void {
	if (e.detail==Alert.OK) {
		httpPaciente = new HTTPServices;
		httpPaciente.url = "Module/Module_Principal/Module_Principal.php";
		httpPaciente.addEventListener(ResultEvent.RESULT, function():void{
			delete xmlModel.tratamientos.tratamiento[(dtgTratamiento.selectedItem as XML).childIndex()];
		});
		httpPaciente.send({rutina:"desactivar_item", id_paciente_producto: dtgTratamiento.selectedItem.@id_paciente_producto});
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

