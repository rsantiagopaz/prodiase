// ActionScript file
import TitleWindow.TitleWindow_Entregas.TitleWindow_Entregas;

import clases.HTTPServices;

import mx.events.CloseEvent;
import mx.managers.PopUpManager;
import mx.rpc.events.ResultEvent;

public var xmlDepositos: XML;
public var id_deposito: String;
private var twnIngreso : TitleWindow_Entregas;
private var httpIngreso : HTTPServices;

private function Module_creationComplete() : void {
	btnNuevoIngreso.addEventListener("click", btnNuevoIngreso_click);
	btnAnularEntrega.addEventListener("click", btnAnularEntrega_click);
	dtfHasta.selectedDate = new Date;
	dtfDesde.selectedDate = new Date(dtfHasta.selectedDate.getTime() - (1000 * 60 * 60 * 24 * 60));
	Actualizar();
}

private function btnNuevoIngreso_click(e:MouseEvent) : void {
	twnIngreso = new TitleWindow_Entregas;
	twnIngreso.addEventListener("eventAceptar", function():void{
		Actualizar();
	});
	
	twnIngreso.xmlDepositos = xmlDepositos;
	twnIngreso.id_deposito = id_deposito;
	
	PopUpManager.addPopUp(twnIngreso, this.parent.parent.parent, true);
	PopUpManager.centerPopUp(twnIngreso);
}


public function btnAnularEntrega_click(e:MouseEvent) : void {
	Alert.show("Desea anular la entrega seleccionada?", "Atención", Alert.OK | Alert.CANCEL, null, function(e: CloseEvent):void{
		if (e.detail==Alert.OK) {
			httpIngreso = new HTTPServices;
			httpIngreso.resultFormat = "text";
			httpIngreso.url = "Module/Module_Entregas/Module_Entregas.php";
			httpIngreso.addEventListener(ResultEvent.RESULT, function():void{
				if ((httpIngreso.lastResult as String) == "error") {
					Alert.show("Imposible anular entrega, yá figura como entregada o anulada previamente.", "Atención");
				}
				xmlModel = null;
				btnActualizarDatos.dispatchEvent(new MouseEvent("click"));
			});
			httpIngreso.send({rutina:"anular_entrega", id_entrega: dtgIngresos.selectedItem.@id_entrega});
		}
	}, null, Alert.CANCEL);
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