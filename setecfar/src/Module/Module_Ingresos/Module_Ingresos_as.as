// ActionScript file
import TitleWindow.TitleWindow_Ingreso.TitleWindow_Ingreso;

import clases.HTTPServices;

import mx.controls.Alert;
import mx.managers.PopUpManager;

public var xmlDepositos: XML;
public var id_deposito: String;
private var twnIngreso : TitleWindow_Ingreso;
private var httpIngreso : HTTPServices;

private function Module_creationComplete() : void {
	btnNuevoIngreso.addEventListener("click", btnNuevoIngreso_click);
	
	httpIngreso = new HTTPServices;
	httpIngreso.url = "Module/Module_Ingresos/Module_Ingresos.php";
	httpIngreso.addEventListener("result", function():void{
		//Alert.show(httpIngreso.lastResult.toString());
		xmlModel = httpIngreso.lastResult as XML;
		dtgIngresos.selectedItem = xmlModel.ingresos[0];
	});
	//httpIngreso.resultFormat="text";
	httpIngreso.send({rutina:"leer_datos_ingresos", id_deposito: this.parentApplication.id_deposito});
}

private function btnNuevoIngreso_click(e:MouseEvent) : void {
	twnIngreso = new TitleWindow_Ingreso;
	twnIngreso.addEventListener("eventAceptar", function():void{
		httpIngreso = new HTTPServices;
		httpIngreso.url = "Module/Module_Ingresos/Module_Ingresos.php";
		//httpIngreso.resultFormat="text";
		httpIngreso.addEventListener("result", function():void{
			//Alert.show(httpIngreso.lastResult as String);
			xmlModel = httpIngreso.lastResult as XML;
			//dtgIngresos.selectedIndex = dtgIngresos.rowCount - 1;
			dtgIngresos.selectedItem = xmlModel.ingresos[0];
		});

		//Alert.show(twnIngreso.xmlModel.toString());
		httpIngreso.send({rutina:"nuevo_ingreso", id_deposito: id_deposito, de_proveedor: twnIngreso.rdb0.selected, model: twnIngreso.xmlModel});
	});
	
	twnIngreso.xmlDepositos = xmlDepositos;
	twnIngreso.id_deposito = id_deposito;
	
	PopUpManager.addPopUp(twnIngreso, this.parent.parent.parent, true);
	PopUpManager.centerPopUp(twnIngreso);
}