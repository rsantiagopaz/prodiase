// ActionScript file
import mx.events.ListEvent;
import mx.formatters.NumberFormatter;
import mx.managers.PopUpManager;
import mx.utils.StringUtil;

private var varAutoComplete : AutoComplete;
private var nmf : NumberFormatter = new NumberFormatter;

private function TitleWindow_creationComplete() : void {
	nmf.useThousandsSeparator = false;
	btnAceptar.addEventListener("click", btnAceptar_click);
	txtDosisDiaria.addEventListener("focusOut", function():void{txtDosisDiaria.text = mx.utils.StringUtil.trim(txtDosisDiaria.text)});
	autocompleteProducto.setFocus();
}

private function btnAceptar_click(e:MouseEvent) : void {
	if (autocompleteProducto.selectedItem==null) {
		txtDosisDiaria.errorString='';
		autocompleteProducto.errorString='Debe seleccionar un producto válido';
		autocompleteProducto.setFocus();
	} else if (int(nmf.format(txtDosisDiaria.text)) <= 0) {
		autocompleteProducto.errorString='';
		txtDosisDiaria.errorString='Debe agregar dosis diaria';
		txtDosisDiaria.setFocus();
	} else {
		PopUpManager.removePopUp(this);
		dispatchEvent(new Event("eventAceptar"));
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