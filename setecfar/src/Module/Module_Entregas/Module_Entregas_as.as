// ActionScript file
import Report.Report_Hospital;

import TitleWindow.TitleWindow_Entregas.TitleWindow_Entregas;
import TitleWindow.TitleWindow_Impresion.TitleWindow_Impresion;

import clases.HTTPServices;

import mx.controls.Alert;
import mx.events.CloseEvent;
import mx.managers.PopUpManager;

public var xmlDepositos: XML;
public var id_deposito: String;
private var twnIngreso : TitleWindow_Entregas;
private var httpIngreso : HTTPServices;

import org.doc.Document;

private function Module_creationComplete() : void {
	dtfHasta.selectedDate = new Date;
	dtfDesde.selectedDate = new Date(dtfHasta.selectedDate.getTime() - (1000 * 60 * 60 * 24 * 60));
	Actualizar();
}
/*
public function btnDesactivaritem_click(e:CloseEvent) : void {
	if (e.detail==Alert.OK) {
		if (dtgIngresos.selectedItem.@id_deposito==id_deposito) {
			httpIngreso = new HTTPServices;
			httpIngreso.url = "Module/Module_Entregas/Module_Entregas.php";
			httpIngreso.addEventListener("result", function():void{
				xmlModel = httpIngreso.lastResult as XML;
				dtgIngresos.selectedItem = xmlModel.ingresos[0];
			});
			httpIngreso.send({rutina:"entregar", id_oas_usuario: parentApplication.controlAcceso.id_oas_usuario, id_entrega: dtgIngresos.selectedItem.@id_entrega});
		} else {
			Alert.show("No esta autorizado para hacer esta entrega.", "Atención");
		}
	}
}
*/

public function btnDesactivaritem_click(e:CloseEvent) : void {
	if (e.detail==Alert.OK) {
		if (dtgIngresos.selectedItem.@id_deposito==id_deposito) {
			var twnImpresion : TitleWindow_Impresion = new TitleWindow_Impresion;
			twnImpresion.visible = false;
			twnImpresion.width = this.width;
			twnImpresion.height = this.height;
			var report : Report_Hospital  = new Report_Hospital;
			
			twnImpresion.width = this.parentApplication.width;
			twnImpresion.height = this.parentApplication.height;
			PopUpManager.addPopUp(twnImpresion, this.parentApplication as DisplayObject, true);
			PopUpManager.centerPopUp(twnImpresion);
		
			report.xmlModel.@entrega = "CONSTANCIA DE ENTREGA";
			report.xmlModel.@fecha ="Fecha: " + dtgIngresos.selectedItem.@fecha;
			report.xmlModel.@paciente = String(dtgIngresos.selectedItem.@destino).substr(0, 70);
			report.xmlModel.@autorizado = "Retirado por:";
			report.xmlModel.@firma = "Firma Autorizado";
			report.xmlModel.@deposito = dtgIngresos.selectedItem.@deposito;
			report.xmlModel.row = dtgIngresos.selectedItem.row;
			
			
			
			
			httpIngreso = new HTTPServices;
			httpIngreso.resultFormat = "text";
			httpIngreso.url = "Module/Module_Entregas/Module_Entregas.php";
			httpIngreso.addEventListener("result", function():void{
				if ((httpIngreso.lastResult as String) == "error") {
					PopUpManager.removePopUp(twnImpresion);
					Alert.show("Imposible realizar entrega, yá figura como entregada o anulada previamente.", "Atención");
				} else {
					twnImpresion.visible = true;
					twnImpresion.preview.doc = new Document(report, null, org.doc.PaperFormat.A4);
					btnActualizarDatos.dispatchEvent(new MouseEvent("click"));
				}
			});
			httpIngreso.send({rutina:"entregar", id_entrega: dtgIngresos.selectedItem.@id_entrega});
		} else {
			Alert.show("No esta autorizado para hacer esta entrega.", "Atención");
		}
	}
}

private function Actualizar() : void {
	httpIngreso = new HTTPServices;
	httpIngreso.url = "Module/Module_Entregas/Module_Entregas.php";
	//httpIngreso.resultFormat="text";
	httpIngreso.addEventListener("result", function():void{
		//Alert.show(httpIngreso.lastResult.toString());
		//Alert.show((httpIngreso.lastResult as XML).toXMLString());
		xmlModel = httpIngreso.lastResult as XML;
		dtgIngresos.selectedItem = xmlModel.ingresos[0];
	});
	//httpIngreso.resultFormat="text";
	httpIngreso.send({rutina:"leer_datos_ingresos", desde: dtfDesde.fechaSeleccionada, hasta: dtfHasta.fechaSeleccionada});
}