// ActionScript file
import TitleWindow.TitleWindow_NuevoPaciente.TitleWindow_NuevoPaciente;

import clases.HTTPServices;

import mx.controls.Alert;
import mx.controls.TextInput;
import mx.core.UIComponent;
import mx.events.DataGridEvent;
import mx.events.ListEvent;
import mx.events.ValidationResultEvent;
import mx.utils.StringUtil;
import mx.validators.Validator;


public var id_paciente : String = "0";

private var boolPersonaExistente: Boolean = false;
private var boolObraSocial: Boolean = false;
private var varAutoComplete : AutoComplete;
private var httpService : HTTPServices;
private var contexto : TitleWindow_NuevoPaciente;


private function TitleWindow_creationComplete() : void {
	contexto = this;
	btnVerificar.addEventListener("click", btnVerificar_click);
	btnAceptar.addEventListener("click", btnAceptar_click);
	autocompleteMedico.addEventListener(ListEvent.CHANGE, varAutoComplete_change);
	autocompleteLocalidad.addEventListener(ListEvent.CHANGE, varAutoComplete_change);
	
	dtgObraSocial.addEventListener("itemEditEnd", function(e: DataGridEvent):void{
		var txt : TextInput = e.currentTarget.itemEditorInstance;
		txt.text = mx.utils.StringUtil.trim(txt.text).substr(0, 20);
		dtgObraSocial.selectedItem.@modificado = "true";
	});
	
	//dtgObraSocial.editable = parentApplication.controlAcceso.tienePerfil("035002");
	
	var httpService: HTTPServices = new HTTPServices;
	httpService.method = "POST";
	httpService.url = "TitleWindow/TitleWindow_NuevoPaciente/TitleWindow_NuevoPaciente.php";
	httpService.addEventListener("result", function():void{
		cboTipo_paciente.dataProvider = httpService.lastResult.tipos_pacientes;
		
		if (id_paciente == "0") {
			dflFecha_ingreso.selectedDate = new Date;
			contexto.title = "Alta de paciente"
		} else {
			contexto.title = "Modificación de datos de paciente"
			btnVerificar.visible = false;
			btnVerificar.dispatchEvent(new MouseEvent("click"));
		}
		txtPersona_dni.setFocus();
		cboPersona_tipodoc.setFocus();
		txtPersona_dni.errorString = "";
	});
	httpService.send({rutina:"leer_tipos_pacientes"});
	/* 
	if (id_paciente == "0") {
		dflFecha_ingreso.selectedDate = new Date;
		this.title = "Alta de paciente"
	} else {
		this.title = "Modificación de datos de paciente"
		btnVerificar.visible = false;
		btnVerificar.dispatchEvent(new MouseEvent("click"));
	}
	txtPersona_dni.setFocus();
	cboPersona_tipodoc.setFocus();
	txtPersona_dni.errorString = "";
	 */
}

private function btnAceptar_click(e:MouseEvent): void {
	var arrayAux : Array;
	
	txtCuil.errorString = "";
	autocompleteMedico.errorString = "";
	autocompleteLocalidad.errorString = "";
	
	if (id_paciente == "0" && ! boolObraSocial) {
		Alert.show("Debe verificar los datos antes de dar alta.", "Atención", 4, null, function():void{
			btnVerificar.setFocus();
		});
	} else if (false && id_paciente == "0" && (xmlModel.obras_sociales.obra_social as XMLList).length() > 0 && ! parentApplication.controlAcceso.tienePerfil("035002")) {
		Alert.show("No tiene autorización para dar alta a pacientes con obra social.", "Atención");
	} else {
		
		xmlModel.persona[0].@persona_fecha_nacimiento = dflPersona_fecha_nacimiento.fechaSeleccionada;
		xmlModel.paciente[0].@fecha_ingreso = dflFecha_ingreso.fechaSeleccionada;
		xmlModel.paciente[0].@req_fecha = dflReq_fecha.fechaSeleccionada;
	
		arrayAux = Validator.validateAll([valPersona_dni, valPersona_nombre, valPersona_domicilio, valPersona_fecha_nacimiento, valFecha_ingreso, valReq_fecha]);
		
		if (arrayAux.length > 0) {
			((arrayAux[0] as ValidationResultEvent).target.source as UIComponent).setFocus();
		} else if (txtCuil.text.length > 0 && ! validar_CUIT(txtCuil.text)) {
			txtCuil.errorString = "El número ingresado no es un número CUIL válido.";
			txtCuil.setFocus();
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
				httpService.addEventListener("result", function():void{
					btnCancelar.dispatchEvent(new MouseEvent("click"));
					Alert.show("Se grabó correctamente los datos del paciente '" + txtPersona_nombre.text + "'", "", 4, null, function():void{
						dispatchEvent(new Event("eventAceptar"));
					});
				});
				
				if (id_paciente == "0") {
					httpService.send({rutina:(boolPersonaExistente) ? "nuevo_paciente" : "alta_persona", id_oas_usuario: parentApplication.controlAcceso.id_oas_usuario, model: xmlModel});
				} else {
					httpService.send({rutina: "modificar_paciente", id_oas_usuario: parentApplication.controlAcceso.id_oas_usuario, model: xmlModel});
				}
			}
		}
	}
}

private function validar_CUIT(cuit: String): Boolean {
	var multiplos : Array = new Array(9);
	multiplos[0] = 5;
	multiplos[1] = 4;
	multiplos[2] = 3;
	multiplos[3] = 2;
	multiplos[4] = 7;
	multiplos[5] = 6;
	multiplos[6] = 5;
	multiplos[7] = 4;
	multiplos[8] = 3;
	multiplos[9] = 2;
	var sumador : int = 0;
	   
	if (cuit.length == 11) {
	    for(var i : int = 0; i<((cuit.length)-1); i++) {
	        sumador = sumador + (int(cuit.charAt(i)) * multiplos[i]);
	    }
	   
	    sumador = (11 - (sumador % 11)) % 11;
	   
	    if (int(cuit.charAt(10)) != sumador) {
	        return false;
	    } else {
	        return true;
	    }
	} else {
	    return false;
	}
}

private function btnVerificar_click(e:MouseEvent): void {
	if (valPersona_dni.validate().type == "valid" || id_paciente!="0") {
		boolPersonaExistente = false;
		btnAceptar.enabled = true;

		httpService = new HTTPServices;
		httpService.method = "POST";
		httpService.url = "TitleWindow/TitleWindow_NuevoPaciente/TitleWindow_NuevoPaciente.php";
		httpService.addEventListener("result", function():void{
			var xmlAux : XML = httpService.lastResult as XML;
			
			xmlModel.obras_sociales = xmlAux.obras_sociales;
			boolObraSocial = true;
			
			if (int(xmlAux.persona.@persona_id) > 0) {
				boolPersonaExistente = true;
				xmlModel.persona = xmlAux.persona;
				dflPersona_fecha_nacimiento.fechaSeleccionada = xmlModel.persona.@persona_fecha_nacimiento;
				
				varAutoComplete = autocompleteLocalidad;
				varAutoComplete.typedText = xmlModel.persona.@localidad + " (" + xmlModel.persona.@departamento + ")";
				httpsAutoCompleteLocalidad.send({rutina: varAutoComplete.id, id: xmlModel.persona.@localidad_id});
				
				cboPersona_sexo.selectedIndex = (xmlModel.persona.@persona_sexo=="F") ? 0 : 1;
				autocompleteLocalidad.setFocus();
				autocompleteMedico.setFocus();
				
				if (int(xmlAux.paciente.@id_paciente) > 0) {
					xmlModel.paciente = xmlAux.paciente;
					dflReq_fecha.fechaSeleccionada = xmlModel.paciente.@req_fecha;
					dflFecha_ingreso.fechaSeleccionada = xmlModel.paciente.@fecha_ingreso;
					
					varAutoComplete=autocompleteMedico;
					varAutoComplete.typedText = xmlModel.paciente.@medico;
					httpsAutoCompleteMedico.send({rutina: varAutoComplete.id, id: xmlModel.paciente.@id_personal});
					autocompleteLocalidad.setFocus();
					
					var aux : XMLList = new XMLList(cboTipo_paciente.dataProvider);
					cboTipo_paciente.selectedItem = aux.(@id_tipo_paciente==xmlModel.paciente.@id_tipo_paciente);
					cboEstado.selectedIndex = (xmlModel.paciente.@estado=="A") ? 0 : 1;
					
					if (xmlModel.paciente.@inactivo == "S") cboInactivo.selectedIndex = 0;
					else if (xmlModel.paciente.@inactivo == "D") cboInactivo.selectedIndex = 1;
					else cboInactivo.selectedIndex = 2;
					
					
					if (id_paciente == "0") {
						cboPersona_tipodoc.setFocus();
						btnAceptar.enabled = false;
						var strAux : String;
						strAux = "Nombre: " + txtPersona_nombre.text + String.fromCharCode(13);
						strAux+= "Tipo doc.: " + cboPersona_tipodoc.text + String.fromCharCode(13);
						strAux+= "Nro.doc.: " + txtPersona_dni.text + String.fromCharCode(13);
						strAux+= String.fromCharCode(13);
						strAux+= "ya existe en la tabla de Pacientes."
						Alert.show(strAux, "Atención");
						
					} else {
						if (xmlModel.persona.@persona_tipodoc=="D") {
							cboPersona_tipodoc.selectedIndex = 0;
						} else if (xmlModel.persona.@persona_tipodoc=="C") {
							cboPersona_tipodoc.selectedIndex = 1;
						} else if (xmlModel.persona.@persona_tipodoc=="E") {
							cboPersona_tipodoc.selectedIndex = 2;
						} else if (xmlModel.persona.@persona_tipodoc=="P") {
							cboPersona_tipodoc.selectedIndex = 3;
						} else if (xmlModel.persona.@persona_tipodoc=="F") {
							cboPersona_tipodoc.selectedIndex = 4;
						}
						txtCuil.setFocus();
					}
				} else {
					autocompleteMedico.dataProvider = null;
					autocompleteMedico.text = "";
					dflFecha_ingreso.selectedDate = new Date;
					cboTipo_paciente.selectedIndex = 0;
					txtTratamientos_realizados.text = "";
					txtEstado_actual.text = "";
					txtEstudios_complementarios.text = "";
					chkReq_cobertura_social.selected = false;
					chkReq_declaracion_jurada.selected = false;
					chkReq_fotocopia_dni.selected = false;
					chkReq_certificado_residencia.selected = false;
					dflReq_fecha.fechaSeleccionada = "";
					cboEstado.selectedIndex = 0;
					cboInactivo.selectedIndex = 0;
				}
			} else {
				txtPersona_nombre.text = "";
				txtPersona_domicilio.text = "";
				autocompleteLocalidad.dataProvider = null;
				autocompleteLocalidad.text = "";
				dflPersona_fecha_nacimiento.fechaSeleccionada = "";
				
				autocompleteMedico.dataProvider = null;
				autocompleteMedico.text = "";
				dflFecha_ingreso.selectedDate = new Date;
				cboTipo_paciente.selectedIndex = 0;
				txtTratamientos_realizados.text = "";
				txtEstado_actual.text = "";
				txtEstudios_complementarios.text = "";
				chkReq_cobertura_social.selected = false;
				chkReq_declaracion_jurada.selected = false;
				chkReq_fotocopia_dni.selected = false;
				chkReq_certificado_residencia.selected = false;
				dflReq_fecha.fechaSeleccionada = "";
				cboEstado.selectedIndex = 0;
				cboInactivo.selectedIndex = 0;
				
				txtCuil.setFocus();
			}
		});

		httpService.send({rutina:"verificar_persona", id_paciente: id_paciente, persona_tipodoc: cboPersona_tipodoc.selectedItem.@persona_tipodoc, persona_dni: txtPersona_dni.text});
	} else txtPersona_dni.setFocus();
}

public function varAutoComplete_change(e:ListEvent) : void {
	varAutoComplete=(e.target as AutoComplete);
	if (varAutoComplete.text.length==3) {
		if (varAutoComplete==autocompleteLocalidad) {
			httpsAutoCompleteLocalidad.send({rutina: varAutoComplete.id, descrip: varAutoComplete.text});
		} else {
			httpsAutoCompleteMedico.send({rutina: varAutoComplete.id, descrip: varAutoComplete.text});
		}
	}
}

private function httpsAutoCompleteLocalidad_result() : void {
	autocompleteLocalidad.typedText = autocompleteLocalidad.text;
	autocompleteLocalidad.dataProvider = httpsAutoCompleteLocalidad.lastResult.row;
}

private function httpsAutoCompleteMedico_result() : void {
	autocompleteMedico.typedText = autocompleteMedico.text;
	autocompleteMedico.dataProvider = httpsAutoCompleteMedico.lastResult.row;
}