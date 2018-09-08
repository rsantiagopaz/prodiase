// ActionScript file
import clases.HTTPServices;

import flash.events.KeyboardEvent;
import flash.events.MouseEvent;

import mx.controls.Alert;
import mx.controls.TextInput;
import mx.events.DataGridEvent;
import mx.events.ListEvent;
import mx.formatters.NumberFormatter;
import mx.rpc.events.ResultEvent;


public var id_paciente : String;
[Bindable] public var xmlDepositos : XML;
[Bindable] public var xmlObrasSociales : XML;

private var httpService : HTTPServices;
private var nmf : NumberFormatter = new NumberFormatter;


private function TitleWindow_creationComplete() : void {
	nmf.precision = 0;
	this.addEventListener(KeyboardEvent.KEY_UP, function(e:KeyboardEvent):void{
		//if (e.keyCode==27) btnCancelar.dispatchEvent(new MouseEvent(MouseEvent.CLICK));
	});
	
	btnAceptar.addEventListener("click", btnAceptar_click);
	cboDeposito.addEventListener("change", cboDeposito_change);
	
	dtgTratamiento.addEventListener(KeyboardEvent.KEY_UP, function(e:KeyboardEvent):void{
		if (e.keyCode==13) dtgTratamiento.editedItemPosition = {columnIndex:5, rowIndex:dtgTratamiento.selectedIndex};
	});
	dtgTratamiento.addEventListener("itemDoubleClick", function(e: ListEvent):void{
		dtgTratamiento.editedItemPosition = {columnIndex:5, rowIndex:dtgTratamiento.selectedIndex};
	});

	dtgTratamiento.addEventListener("itemEditEnd", function(e:DataGridEvent):void{
		var txt : TextInput = e.currentTarget.itemEditorInstance;
		nmf.useThousandsSeparator = false;
		if (int(nmf.format(txt.text)) <= 0) {
			txt.text = "0";
		} else {
			//nmf.useThousandsSeparator = true;
			txt.text = nmf.format(txt.text);			
		}
	});

	//dtgTratamiento.editedItemPosition = {columnIndex:5, rowIndex:0}
	cboDeposito.selectedItem = xmlDepositos.deposito.(@id_deposito=='1');
	cboDeposito_change(null);
}

private function cboDeposito_change(e: ListEvent): void {
  	httpService = new HTTPServices;
	httpService.url = "TitleWindow/TitleWindow_Entrega/TitleWindow_Entrega.php";
	httpService.addEventListener(ResultEvent.RESULT, function():void{
	 	xmlModel.tratamientos = (httpService.lastResult as XML);
	 	dtgTratamiento.editedItemPosition = {columnIndex:5, rowIndex:0};
	});
	httpService.send({rutina:"leer_stock", id_paciente: id_paciente, id_deposito: cboDeposito.selectedItem.@id_deposito});
}

private function btnAceptar_click(e:MouseEvent): void {
	var bandera1 : Boolean = true;
	var bandera2 : Boolean = false;
	var cantidad : int;
	nmf.useThousandsSeparator = false;
	for each (var item : XML in xmlModel.tratamientos.tratamiento) {
		cantidad = int(nmf.format(item.@cantidad));
		if (cantidad > int(nmf.format(item.@disponible_virtual))) {
			bandera1 = false;
			dtgTratamiento.selectedItem = item;
			break;
		}
		if (cantidad > 0) bandera2 = true;
	}
	if (bandera1) {
		if (bandera2) {
			dispatchEvent(new Event("eventAceptar"));
		} else {
			Alert.show("No hay ninguna cantidad a entregar asignada.", "Atención",4,null,function():void{
				dtgTratamiento.editedItemPosition = {columnIndex:5, rowIndex:0}
			});
		}
	} else {
		Alert.show("La cantidad a entregar" + String.fromCharCode(13) + " asignada excede la disp.virtual.", "Atención",4,null,function():void{
			dtgTratamiento.editedItemPosition = {columnIndex: 5, rowIndex: dtgTratamiento.selectedIndex};
		});
	}
}