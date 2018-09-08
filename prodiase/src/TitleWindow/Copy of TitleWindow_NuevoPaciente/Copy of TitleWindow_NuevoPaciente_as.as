// ActionScript file
import clases.HTTPServices;

import flash.events.MouseEvent;

import mx.controls.Alert;
import mx.core.UIComponent;
import mx.events.ListEvent;
import mx.events.ValidationResultEvent;
import mx.rpc.events.ResultEvent;
import mx.validators.Validator;

private var varAutoComplete : AutoComplete;

private function TitleWindow_creationComplete() : void {
	btnBuscar.addEventListener("click", btnBuscar_click);
	btnAceptar.addEventListener("click", btnAceptar_click);
	autocompleteMedico.addEventListener(ListEvent.CHANGE, varAutoComplete_change);
	autocompleteLocalidad.addEventListener(ListEvent.CHANGE, varAutoComplete_change);
	dflFecha_ingreso.selectedDate = new Date;
	txtPersona_dni.setFocus();
	cboPersona_tipodoc.setFocus();
	txtPersona_dni.errorString = "";
	var httpService : HTTPServices = new HTTPServices;
	httpService.url = "TitleWindow/TitleWindow_NuevoPaciente/TitleWindow_NuevoPaciente.php";
	httpService.addEventListener(ResultEvent.RESULT, function():void{
		cboTipo_paciente.dataProvider = httpService.lastResult.tipos_pacientes;
	});
	httpService.send({rutina:"leer_tipos_pacientes"});
}

private function btnAceptar_click(e:MouseEvent): void {
	var arrayAux : Array;
	
	autocompleteMedico.errorString = "";
	autocompleteLocalidad.errorString = "";
	
	xmlModel.persona[0].@persona_fecha_nacimiento = dflPersona_fecha_nacimiento.fechaSeleccionada;
	xmlModel.paciente[0].@fecha_ingreso = dflFecha_ingreso.fechaSeleccionada;
	xmlModel.paciente[0].@req_fecha = dflReq_fecha.fechaSeleccionada;

	if (txtPersona_nombre.editable) {
		arrayAux = Validator.validateAll([valPersona_dni, valPersona_nombre, valPersona_domicilio, valPersona_fecha_nacimiento, valFecha_ingreso, valReq_fecha]);
	} else {
		arrayAux = Validator.validateAll([valPersona_dni, valFecha_ingreso, valReq_fecha]);
	}
	
	
	
	if (arrayAux.length > 0) {
		((arrayAux[0] as ValidationResultEvent).target.source as UIComponent).setFocus();
	} else if (txtPersona_nombre.editable && typeof autocompleteLocalidad.selectedItem!="xml") {
		autocompleteLocalidad.errorString = "Debe seleccionar una localidad válida.";
		autocompleteLocalidad.setFocus();
	} else if (typeof autocompleteMedico.selectedItem!="xml") {
		autocompleteMedico.errorString = "Debe seleccionar un profesional válido.";
		autocompleteMedico.setFocus();
	} else {
		if (txtPersona_nombre.editable || txtTratamientos_realizados.editable) {
			var httpService : HTTPServices = new HTTPServices;
			httpService.url = "TitleWindow/TitleWindow_NuevoPaciente/TitleWindow_NuevoPaciente.php";
		
			httpService.addEventListener(ResultEvent.RESULT, function():void{
				//var xmlAux : XML = httpService.lastResult as XML;
				//if (xmlAux.seleccionado != null) xmlModel.seleccionado = xmlAux.seleccionado;
				//Alert.show(httpService.lastResult.toString());
				//return;
				btnCancelar.dispatchEvent(new MouseEvent("click"));
				Alert.show("Se dió de alta correctamente al paciente '" + txtPersona_nombre.text + "'");
			});
			//Alert.show(xmlModel.toXMLString());
			//httpService.resultFormat="text";
			httpService.send({rutina:(txtPersona_nombre.editable) ? "alta_persona" : "nuevo_paciente", model: xmlModel});
		}
	}
}

private function btnBuscar_click(e:MouseEvent): void {
	if (valPersona_dni.validate().type == "valid") {
		var httpService : HTTPServices = new HTTPServices;
		httpService.url = "TitleWindow/TitleWindow_NuevoPaciente/TitleWindow_NuevoPaciente.php";
	
		httpService.addEventListener(ResultEvent.RESULT, function():void{
			var xmlAux : XML = httpService.lastResult as XML;
			if (int(xmlAux.persona.@persona_id) > 0) {
				xmlModel.persona = xmlAux.persona;
				dflPersona_fecha_nacimiento.fechaSeleccionada = xmlModel.persona.@persona_fecha_nacimiento;
				autocompleteLocalidad.text = xmlModel.persona.@localidad;
				cboPersona_sexo.selectedIndex = ((xmlModel.persona.@persona_sexo=="F") ? 0 : 1);
				/* 
				txtPersona_nombre.editable = false;
				txtPersona_domicilio.editable = false;
				autocompleteLocalidad.enabled = false;
				dflPersona_fecha_nacimiento.enabled = false;
				cboPersona_sexo.enabled = false;
				 */
				autocompleteMedico.setFocus();
				if (int(xmlAux.paciente.@id_paciente) > 0) {
					xmlModel.paciente = xmlAux.paciente;
					dflReq_fecha.fechaSeleccionada = xmlModel.paciente.@req_fecha;
					autocompleteMedico.text = xmlModel.paciente.@medico;
					var aux : XMLList = new XMLList(cboTipo_paciente.dataProvider);
					cboTipo_paciente.selectedItem = aux.(@id_tipo_paciente==xmlModel.paciente.@id_tipo_paciente);
					
					autocompleteMedico.enabled = false;
					dflFecha_ingreso.enabled = false;
					cboTipo_paciente.enabled = false;
					txtTratamientos_realizados.editable = false;
					txtEstado_actual.editable = false;
					txtEstudios_complementarios.editable = false;
					chkReq_cobertura_social.enabled = false;
					chkReq_declaracion_jurada.enabled = false;
					chkReq_fotocopia_dni.enabled = false;
					chkReq_certificado_residencia.enabled = false;
					dflReq_fecha.enabled = false;
					
					btnAceptar.enabled = false;
					
					var strAux : String;
					strAux = "Nombre: " + txtPersona_nombre.text + String.fromCharCode(13);
					strAux+= "Tipo doc.: " + cboPersona_tipodoc.text + String.fromCharCode(13);
					strAux+= "Nro.doc.: " + txtPersona_dni.text + String.fromCharCode(13);
					strAux+= String.fromCharCode(13);
					strAux+= "ya existe en la tabla de Pacientes."
					Alert.show(strAux, "Atención");
					
					cboPersona_tipodoc.setFocus();
				}
			} else {
				txtPersona_nombre.editable = true;
				txtPersona_domicilio.editable = true;
				autocompleteLocalidad.enabled = true;
				dflPersona_fecha_nacimiento.enabled = true;
				cboPersona_sexo.enabled = true;

				txtPersona_nombre.text = "";
				txtPersona_domicilio.text = "";
				autocompleteLocalidad.dataProvider = null;
				autocompleteLocalidad.text = "";
				dflPersona_fecha_nacimiento.fechaSeleccionada = "";
				
				autocompleteMedico.enabled = true;
				dflFecha_ingreso.enabled = true;
				cboTipo_paciente.enabled = true;
				txtTratamientos_realizados.editable = true;
				txtEstado_actual.editable = true;
				txtEstudios_complementarios.editable = true;
				chkReq_cobertura_social.enabled = true;
				chkReq_declaracion_jurada.enabled = true;
				chkReq_fotocopia_dni.enabled = true;
				chkReq_certificado_residencia.enabled = true;
				dflReq_fecha.enabled = true;
				
				autocompleteMedico.dataProvider = null;
				autocompleteMedico.text = "";
				dflFecha_ingreso.selectedDate = new Date;
				txtTratamientos_realizados.text = "";
				txtEstado_actual.text = "";
				txtEstudios_complementarios.text = "";
				chkReq_cobertura_social.selected = false;
				chkReq_declaracion_jurada.selected = false;
				chkReq_fotocopia_dni.selected = false;
				chkReq_certificado_residencia.selected = false;
				dflReq_fecha.fechaSeleccionada = "";
				
				btnAceptar.enabled = true;
				txtPersona_nombre.setFocus();
			}
		});
		httpService.send({rutina:"buscar_persona", persona_tipodoc: cboPersona_tipodoc.selectedItem.persona_tipodoc, persona_dni: txtPersona_dni.text});
	} else txtPersona_dni.setFocus();
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