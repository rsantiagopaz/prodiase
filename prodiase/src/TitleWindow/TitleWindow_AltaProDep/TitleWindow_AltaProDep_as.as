// ActionScript file
import TitleWindow.TitleWindow_AltaProDep.TitleWindow_AltaProDep;

import clases.HTTPServices;

import mx.controls.Alert;
import mx.formatters.NumberFormatter;
import mx.managers.PopUpManager;
import mx.rpc.events.ResultEvent;
import mx.utils.StringUtil;

private var httpService : HTTPServices
private var nmf : NumberFormatter = new NumberFormatter;
private var contexto : TitleWindow_AltaProDep;

private function TitleWindow_creationComplete() : void {
	nmf.useThousandsSeparator = false;
	btnAceptar.addEventListener("click", btnAceptar_click);
	txtProDescrip.addEventListener("focusOut", function():void{txtProDescrip.text = mx.utils.StringUtil.trim(txtProDescrip.text).substr(0, 50)});
	txtDepDescrip.addEventListener("focusOut", function():void{txtDepDescrip.text = mx.utils.StringUtil.trim(txtDepDescrip.text).substr(0, 50)});
	
  	httpService = new HTTPServices;
	httpService.url = "TitleWindow/TitleWindow_AltaProDep/TitleWindow_AltaProDep.php";
	httpService.addEventListener(ResultEvent.RESULT, function():void{
		cboTipo.dataProvider = httpService.lastResult.row;
	});
	httpService.send({rutina:"leer_tipo_producto"});
}

private function btnAceptar_click(e:MouseEvent) : void {
	txtProDescrip.errorString='';
	txtDepDescrip.errorString='';

	contexto = this;
  	httpService = new HTTPServices;
	httpService.url = "TitleWindow/TitleWindow_AltaProDep/TitleWindow_AltaProDep.php";
	httpService.addEventListener(ResultEvent.RESULT, function():void{
		PopUpManager.removePopUp(contexto);
	});
	if (rdb0.selected) {
		if (txtProDescrip.text == "") {
			txtProDescrip.errorString='Debe ingresar descripción de producto';
			txtProDescrip.setFocus();
		} else {
			httpService.send({rutina:"alta_producto", descrip: txtProDescrip.text, id_tipo_producto: cboTipo.selectedItem.@id_tipo_producto});
		}
	} else {
		if (txtDepDescrip.text == "") {
			txtDepDescrip.errorString='Debe ingresar descripción de depósito';
			txtDepDescrip.setFocus();
		} else {
			httpService.send({rutina:"alta_deposito", descrip: txtDepDescrip.text});
		}
	}
}