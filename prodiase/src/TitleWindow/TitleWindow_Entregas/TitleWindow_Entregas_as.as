// ActionScript file

	import Report.Report_Hospital;
	
	import TitleWindow.TitleWindow_Impresion.TitleWindow_Impresion;
	
	import clases.HTTPServices;
	
	import mx.controls.Alert;
	import mx.rpc.events.ResultEvent;
	
	import org.doc.Document;
	
	import paquete01.ControlAcceso;

public var aceptar : Boolean = false;
public var controlAcceso : ControlAcceso;
[Bindable] public var xmlDepositos : XML;
public var id_deposito : String;

private var _varAutoComplete : AutoComplete;



private function btnAceptar_click() : void {
	autocompleteHospital.errorString="";
	cboDeposito.errorString="";
	if (typeof autocompleteHospital.selectedItem != "xml") {
		autocompleteHospital.errorString="Debe seleccionar un hospital/area";
		//Alert.show("Debe seleccionar un hospital.", "Atención");
		autocompleteHospital.setFocus();
		/*
	} else if (cboDeposito.selectedItem.@id_deposito==id_deposito) {
		cboDeposito.errorString="Debe seleccionar un depósito distinto al de origen";
		//Alert.show("Debe seleccionar un depósito distinto al de origen.", "Atención");
		autocompleteHospital.setFocus();
		cboDeposito.setFocus();
		*/
	} else if ((xmlModel.items_entregas as XMLList).length()==0) {
		 dtgItems_ingresos.errorString='Debe agregar algun item a la entrega.';
		 autocompleteProductoStock.setFocus();
	} else {
		var hora : Date = new Date();
		xmlModel.@fecha = dflFecha.fechaSeleccionada + " " + hora.toTimeString().substr(0, 8);
		
	  	var httpService : HTTPServices = new HTTPServices;
		httpService.url = "TitleWindow/TitleWindow_Entregas/TitleWindow_Entregas.php";
		httpService.addEventListener(ResultEvent.RESULT, function():void{
			imprimirComprobante();
			dispatchEvent(new Event("eventAceptar"));
			btnCancelar.dispatchEvent(new MouseEvent("click"));
		});
		httpService.send({rutina:"agregar_entrega", a_hospital: true, model: xmlModel.toXMLString()});
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

private function imprimirComprobante() : void {
	var twnImpresion : TitleWindow_Impresion = new TitleWindow_Impresion;
	twnImpresion.width = this.parent.width;
	twnImpresion.height = this.parent.height;
	var reportPacientes : Report_Hospital  = new Report_Hospital;
	
	twnImpresion.width = this.parentApplication.width;
	twnImpresion.height = this.parentApplication.height;
	PopUpManager.addPopUp(twnImpresion, this.parentApplication as DisplayObject, true);
	PopUpManager.centerPopUp(twnImpresion);

	reportPacientes.xmlModel.@entrega = "ORDEN DE ENTREGA";
	//reportPacientes.xmlModel.@fecha ="Fecha: " + dflFecha.fechaSeleccionada;
	reportPacientes.xmlModel.@fecha ="Fecha: " + xmlModel.@fecha;
	reportPacientes.xmlModel.@paciente = autocompleteHospital.text;
	reportPacientes.xmlModel.@autorizado = "Autorizado por:";
	reportPacientes.xmlModel.@firma = "Firma Autorizada";
	reportPacientes.xmlModel.@deposito = cboDeposito.selectedItem.@denominacion;
	reportPacientes.xmlModel.item_entrega = xmlModel.items_entregas;


	//Alert.show(reportPacientes.rdg.columns.toString());
	//reportPacientes.rdg.columns[4].visible = false;
	//Alert.show(reportPacientes.body.toString());
	twnImpresion.preview.doc = new Document(reportPacientes, null, org.doc.PaperFormat.A4);
}

private function TitleWindow_creationComplete() : void {
	dflFecha.selectedDate = new Date;
	btnAgregar.addEventListener("click", btnAgregar_click);
	autocompleteHospital.setFocus();
}

private function _varAutoComplete_change(e:Event) : void {
	_varAutoComplete=(e.target as AutoComplete);
	if (_varAutoComplete.text.length==3) {
		httpsAutoComplete.send({rutina: _varAutoComplete.id, descrip: _varAutoComplete.text, id_deposito: cboDeposito.selectedItem.@id_deposito});
	}
}

private function httpsAutoComplete_result() : void {
	//Alert.show((httpsAutoComplete.lastResult as String));
	_varAutoComplete.typedText = _varAutoComplete.text;
	_varAutoComplete.dataProvider = httpsAutoComplete.lastResult.row;
}