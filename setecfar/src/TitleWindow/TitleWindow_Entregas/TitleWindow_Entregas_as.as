// ActionScript file

	import clases.HTTPServices;
	
	import com.adobe.flex.extras.controls.AutoComplete;
	
	import mx.controls.Alert;
	import mx.rpc.events.ResultEvent;
	
	import paquete01.ControlAcceso;

public var aceptar : Boolean = false;
public var controlAcceso : ControlAcceso;
[Bindable] public var xmlDepositos : XML;
public var id_deposito : String;

private var _varAutoComplete : AutoComplete;



private function btnAceptar_click() : void {
	if (rdb0.selected && typeof autocompleteHospital.selectedItem != "xml") {
		Alert.show("Debe seleccionar un hospital.", "Atención");
		autocompleteHospital.setFocus();
	} else if (rdb1.selected && cboDeposito.selectedItem.@id_deposito==id_deposito) {
		Alert.show("Debe seleccionar un depósito distinto al de origen.", "Atención");
		cboDeposito.setFocus();
	} else if ((xmlModel.items_entregas as XMLList).length()==0) {
		 dtgItems_ingresos.errorString='Debe agregar algun item a la entrega.';
		 autocompleteProductoStock.setFocus();
	} else {
		xmlModel.@fecha = dflFecha.fechaSeleccionada;
		
	  	var httpService : HTTPServices = new HTTPServices;
		httpService.url = "TitleWindow/TitleWindow_Entregas/TitleWindow_Entregas.php";
		httpService.addEventListener(ResultEvent.RESULT, function():void{
			btnCancelar.dispatchEvent(new MouseEvent("click"));
		});
		httpService.send({rutina:"agregar_entrega", a_hospital: rdb0.selected, model: xmlModel.toXMLString()});
	}
}

private function btnAgregar_click() : void {
	btnAgregar.enabled=false;
	if (typeof autocompleteProductoStock.selectedItem == "xml") {
		var aux : XMLList = xmlModel.items_entregas.(@id_producto==autocompleteProductoStock.selectedItem.@id);
		var disponible_virtual : int = int(autocompleteProductoStock.selectedItem.@disponible_virtual);
		if (aux.length()==1) {
			if (int(aux.@cantidad) + nstCantidad.value > disponible_virtual) {
				Alert.show("La cantidad total ingresada excede el disponible virtual de " + disponible_virtual);
			} else {
				aux.@cantidad = int(aux.@cantidad) + nstCantidad.value;
				dtgItems_ingresos.selectedItem = aux;
			}
		} else {
			
			if (nstCantidad.value > disponible_virtual) {
				
				Alert.show("La cantidad total ingresada excede el disponible virtual de " + disponible_virtual);
			} else {
				var x : XML = new XML('<items_entregas id_producto_deposito="' + autocompleteProductoStock.selectedItem.@id_producto_deposito + '" id_producto="' + autocompleteProductoStock.selectedItem.@id + '" producto="' + autocompleteProductoStock.selectedItem.descrip + '" cantidad="' + nstCantidad.value + '"/>');
				xmlModel.appendChild(x);
				dtgItems_ingresos.selectedItem = x;
			}
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
	autocompleteHospital.setFocus();
}

private function _varAutoComplete_change(e:Event) : void {
	_varAutoComplete=(e.target as AutoComplete);
	if (_varAutoComplete.text.length==3) {
		httpsAutoComplete.send({rutina: _varAutoComplete.id, descrip: _varAutoComplete.text, id_deposito: id_deposito});
	}
}

private function httpsAutoComplete_result() : void {
	//Alert.show(httpsAutoComplete.lastResult.toString());
	_varAutoComplete.typedText = _varAutoComplete.text;
	_varAutoComplete.dataProvider = httpsAutoComplete.lastResult.row;
}