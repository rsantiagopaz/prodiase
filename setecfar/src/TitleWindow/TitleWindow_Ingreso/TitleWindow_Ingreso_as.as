// ActionScript file

	import mx.controls.Alert;
	import mx.core.UIComponent;
	import mx.events.ValidationResultEvent;
	import mx.managers.PopUpManager;
	import mx.validators.Validator;
	import mx.utils.StringUtil;

public var aceptar : Boolean = false;
[Bindable] public var xmlDepositos: XML;
public var id_deposito: String;

private var _varAutoComplete : AutoComplete;



private function btnAceptar_click() : void {
	txtNro_remito.errorString="";
	cboDeposito.errorString="";
	if (rdb0.selected && mx.utils.StringUtil.trim(txtNro_remito.text)=="") {
		txtNro_remito.errorString="Debe ingresar número de remito o descripción";
		//Alert.show("Debe ingresar número de remito.", "Atención");
		txtNro_remito.setFocus();
	} else if (rdb1.selected && cboDeposito.selectedItem.@id_deposito==id_deposito) {
		cboDeposito.errorString="Debe seleccionar un depósito distinto al de destino";
		//Alert.show("Debe seleccionar un depósito distinto al de destino.", "Atención");
		txtNro_remito.setFocus();
		cboDeposito.setFocus();
	} else if ((xmlModel.items_ingresos as XMLList).length()==0) {
		 dtgItems_ingresos.errorString='Debe agregar algun item al ingreso.';
		 autocompleteProductoStock.setFocus();
	} else {
		xmlModel.@fecha = dflFecha.fechaSeleccionada;
		PopUpManager.removePopUp(this);
		dispatchEvent(new Event("eventAceptar"));
	} 
}

private function btnAgregar_click() : void {
	btnAgregar.enabled=false;
	if (typeof autocompleteProductoStock.selectedItem == "xml") {
		var aux : XMLList = xmlModel.items_ingresos.(@id_producto==autocompleteProductoStock.selectedItem.@id);
		if (aux.length()==1) {
			aux.@cantidad = Number(aux.@cantidad) + nstCantidad.value;
			dtgItems_ingresos.selectedItem = aux;
		} else {
			var x : XML = new XML('<items_ingresos id_producto="' + autocompleteProductoStock.selectedItem.@id + '" id_producto_deposito="' + autocompleteProductoStock.selectedItem.@id_producto_deposito + '" producto="' + autocompleteProductoStock.selectedItem.descrip + '" cantidad="' + nstCantidad.value + '"/>');
			xmlModel.appendChild(x);
			dtgItems_ingresos.selectedItem = x;
		}
		dtgItems_ingresos.errorString='';
		nstCantidad.value=1;
		autocompleteProductoStock.text="";
		autocompleteProductoStock.typedText="";
		autocompleteProductoStock.selectedItem = null;
		autocompleteProductoStock.setFocus();
	}
	btnAgregar.enabled=true;
}

private function TitleWindow_creationComplete() : void {
	dflFecha.selectedDate = new Date;
	btnAgregar.addEventListener("click", btnAgregar_click);
	txtNro_remito.setFocus();
}

private function _varAutoComplete_change(e:Event) : void {
	_varAutoComplete=(e.target as AutoComplete);
	if (_varAutoComplete.text.length==3) {
		httpsAutoComplete.send({rutina: _varAutoComplete.id, descrip: _varAutoComplete.text, id_deposito: id_deposito});
	}
}

private function httpsAutoComplete_result() : void {
	_varAutoComplete.typedText = _varAutoComplete.text;
	_varAutoComplete.dataProvider = httpsAutoComplete.lastResult.row;
}