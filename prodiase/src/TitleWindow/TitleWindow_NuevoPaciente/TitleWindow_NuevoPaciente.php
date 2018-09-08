<?php

include("../../control_acceso_flex.php");
//include("../../conexion.php");
include("../../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "modificar_paciente" : {
		$xml = new SimpleXMLElement(stripslashes($_REQUEST['model']));
		
		$sql = "SELECT persona_id";
		$sql.= " FROM salud1._personas";
		$sql.= " WHERE persona_dni='" . $xml->persona['persona_dni'] . "' AND persona_id <> '" . $xml->persona['persona_id'] . "'";
		$rs = mysql_query($sql);
		if (mysql_num_rows($rs) > 0) {
			$a = array();
			$a['D'] = "D.N.I.";
			$a['C'] = "Lib.Cívica";
			$a['E'] = "Lib.Enrolamiento";
			$a['P'] = "Pasaporte";
			$a['F'] = "Cédula";
			
			$error = "<error>";
			//$error.= "Tipo doc.: " . $a[(string) $xml->persona['persona_tipodoc']] . "\n";
			$error.= "Nro.: " . $xml->persona['persona_dni'] . "\n";
			$error.= "\n";
			$error.= "ya existe en la tabla de Personas.\n\n";
			$error.= "</error>";
			$xml = new SimpleXMLElement("<root/>");
			toXML($xml, $error);
			header('Content-Type: text/xml');
			echo $xml->asXML();
		} else {
			mysql_query("START TRANSACTION");
			
			$sql = "UPDATE salud1._personas";
			$sql.= " SET persona_tipodoc='" . $xml->persona['persona_tipodoc'] . "'";
			$sql.= ", persona_dni='" . $xml->persona['persona_dni'] . "'";
			$sql.= ", persona_nombre='" . $xml->persona['persona_nombre'] . "'";
			$sql.= ", persona_domicilio='" . $xml->persona['persona_domicilio'] . "'";
			$sql.= ", persona_sexo='" . $xml->persona['persona_sexo'] . "'";
			$sql.= ", persona_fecha_nacimiento='" . $xml->persona['persona_fecha_nacimiento'] . "'";
			$sql.= ", localidad_id='" . $xml->persona['localidad_id'] . "'";
			$sql.= " WHERE persona_id='" . $xml->persona['persona_id'] . "'";
			mysql_query($sql);
			//auditoria($sql, "Actualiza datos persona", "actualiza_persona", $xml->persona['persona_id']);
			//print mysql_error();
		
		
			$sql = "UPDATE pacientes";
			$sql.= " SET id_tipo_paciente='" . $xml->paciente['id_tipo_paciente'] . "'";
			$sql.= ", cuil='" . $xml->paciente['cuil'] . "'";
			$sql.= ", id_personal='" . $xml->paciente['id_personal'] . "'";
			$sql.= ", fecha_ingreso='" . $xml->paciente['fecha_ingreso'] . "'";
			$sql.= ", tratamientos_realizados='" . $xml->paciente['tratamientos_realizados'] . "'";
			$sql.= ", estado_actual='" . $xml->paciente['estado_actual'] . "'";
			$sql.= ", estudios_complementarios='" . $xml->paciente['estudios_complementarios'] . "'";
			$sql.= ", req_cobertura_social='" . $xml->paciente['req_cobertura_social'] . "'";
			$sql.= ", req_declaracion_jurada='" . $xml->paciente['req_declaracion_jurada'] . "'";
			$sql.= ", req_fotocopia_dni='" . $xml->paciente['req_fotocopia_dni'] . "'";
			$sql.= ", req_certificado_residencia='" . $xml->paciente['req_certificado_residencia'] . "'";
			$sql.= ", req_fecha='" . $xml->paciente['req_fecha'] . "'";
			$sql.= ", sabe_leer='" . $xml->paciente['sabe_leer'] . "'";
			$sql.= ", sabe_escribir='" . $xml->paciente['sabe_escribir'] . "'";
			$sql.= ", telefonos='" . $xml->paciente['telefonos'] . "'";
			$sql.= ", peso='" . $xml->paciente['peso'] . "'";
			$sql.= ", altura='" . $xml->paciente['altura'] . "'";
			$sql.= ", imc='" . $xml->paciente['imc'] . "'";
			$sql.= ", ceguera='" . $xml->paciente['ceguera'] . "'";
			$sql.= ", msa_izq='" . $xml->paciente['msa_izq'] . "'";
			$sql.= ", msa_der='" . $xml->paciente['msa_der'] . "'";
			$sql.= ", mia_izq='" . $xml->paciente['mia_izq'] . "'";
			$sql.= ", mia_der='" . $xml->paciente['mia_der'] . "'";
			$sql.= ", observa='" . $xml->paciente['observa'] . "'";
			//$sql.= ", obra_social='" . $xml->paciente['obra_social'] . "'";
			//$sql.= ", nro_afiliado='" . $xml->paciente['nro_afiliado'] . "'";
			$sql.= ", estado='" . $xml->paciente['estado'] . "'";
			$sql.= ", inactivo='" . $xml->paciente['inactivo'] . "'";
			$sql.= ", id_oas_usuario='" . $_REQUEST['id_oas_usuario'] . "'";
			$sql.= " WHERE id_paciente='" . $xml->paciente['id_paciente'] . "'";
			mysql_query($sql);
			auditoria($sql, "Actualiza datos paciente", "actualiza_paciente", (string) $xml->paciente['id_paciente']);
			//print mysql_error();
			
			foreach ($xml->obras_sociales->obra_social as $item) {
				if ($item['modificado'] == "true") {
					$sql = "INSERT afiliado SET id_padron='" . $item['id_padron'] . "', nro_afiliado='" . $item['nro_afiliado'] . "' ON DUPLICATE KEY UPDATE nro_afiliado='" . $item['nro_afiliado'] . "'";
					mysql_query($sql);
				}
			}
			
			mysql_query("COMMIT");
		}
		
		break;
	}
	
	
	
	case "alta_persona" : {
		$xml = new SimpleXMLElement(stripslashes($_REQUEST['model']));
		
		$sql = "SELECT persona_nombre, persona_tipodoc, persona_dni";
		$sql.= " FROM salud1._personas";
		$sql.= " WHERE persona_dni='" . $xml->persona['persona_dni'] . "'";

		$rs = mysql_query($sql);
		if ($row = mysql_fetch_array($rs)) {
			$a = array();
			$a['D'] = "D.N.I.";
			$a['C'] = "Lib.Cívica";
			$a['E'] = "Lib.Enrolamiento";
			$a['P'] = "Pasaporte";
			$a['F'] = "Cédula";
			
			$error = "<error>";
			$error.= "Nombre: " . $row['persona_nombre'] . "\n"; 
			//$error.= "Tipo doc.: " . $a[$row['persona_tipodoc']] . "\n";
			$error.= "Nro.: " . $row['persona_dni'] . "\n";
			$error.= "\n";
			$error.= "ya existe en la tabla de Personas.\n\n";
			$error.= "Por favor, verifique si existe antes de intentar dar de alta.";
			$error.= "</error>";
			$xml = new SimpleXMLElement("<root/>");
			toXML($xml, $error);
			header('Content-Type: text/xml');
			echo $xml->asXML();
			break;
		} else {
			$sql = "INSERT INTO salud1._personas";
			$sql.= " SET persona_tipo='F'";
			$sql.= ", persona_tipodoc='" . $xml->persona['persona_tipodoc'] . "'";
			$sql.= ", persona_dni='" . $xml->persona['persona_dni'] . "'";
			$sql.= ", persona_nombre='" . $xml->persona['persona_nombre'] . "'";
			$sql.= ", persona_domicilio='" . $xml->persona['persona_domicilio'] . "'";
			$sql.= ", persona_sexo='" . $xml->persona['persona_sexo'] . "'";
			$sql.= ", persona_fecha_nacimiento='" . $xml->persona['persona_fecha_nacimiento'] . "'";
			$sql.= ", localidad_id='" . $xml->persona['localidad_id'] . "'";
			mysql_query($sql);
			$insert_id = mysql_insert_id();
			$xml->persona['persona_id'] = $insert_id;
			
			//auditoria($sql, "Alta persona", "alta_persona", $insert_id);
		}
	}
	
	case "nuevo_paciente" : {
		if (!isset($xml)) $xml = new SimpleXMLElement(stripslashes($_REQUEST['model']));
		
		$sql = "SELECT id_paciente";
		$sql.= " FROM pacientes";
		$sql.= " WHERE persona_id='" . $xml->persona['persona_id'] . "'";
		
		$rs = mysql_query($sql);
		if ($row = mysql_fetch_array($rs)) {
			$a = array();
			$a['D'] = "D.N.I.";
			$a['C'] = "Lib.Cívica";
			$a['E'] = "Lib.Enrolamiento";
			$a['P'] = "Pasaporte";
			$a['F'] = "Cédula";
			
			$error = "<error>";
			$error.= "Nombre: " . $xml->persona['persona_nombre'] . "\n"; 
			$error.= "Tipo doc.: " . $a[$xml->persona['persona_tipodoc']] . "\n";
			$error.= "Nro.: " . $xml->persona['persona_dni'] . "\n";
			$error.= "\n";
			$error.= "ya existe en la tabla de Pacientes.";
			$error.= "</error>";
			$xml = new SimpleXMLElement("<root/>");
			toXML($xml, $error);
			header('Content-Type: text/xml');
			echo $xml->asXML();
			break;
		} else {
			mysql_query("START TRANSACTION");
			
			$sql = "UPDATE salud1._personas";
			$sql.= " SET persona_nombre='" . $xml->persona['persona_nombre'] . "'";
			$sql.= ", persona_domicilio='" . $xml->persona['persona_domicilio'] . "'";
			$sql.= ", persona_sexo='" . $xml->persona['persona_sexo'] . "'";
			$sql.= ", persona_fecha_nacimiento='" . $xml->persona['persona_fecha_nacimiento'] . "'";
			$sql.= ", localidad_id='" . $xml->persona['localidad_id'] . "'";
			$sql.= " WHERE persona_id='" . $xml->persona['persona_id'] . "'";
			mysql_query($sql);
			//auditoria($sql, "Actualiza datos persona", "actualiza_persona", $xml->persona['persona_id']);
			
			$sql = "INSERT INTO pacientes";
			$sql.= " SET persona_id='" . $xml->persona['persona_id'] . "'";
			$sql.= ", id_tipo_paciente='" . $xml->paciente['id_tipo_paciente'] . "'";
			$sql.= ", cuil='" . $xml->paciente['cuil'] . "'";
			$sql.= ", id_personal='" . $xml->paciente['id_personal'] . "'";
			$sql.= ", fecha_ingreso='" . $xml->paciente['fecha_ingreso'] . "'";
			$sql.= ", tratamientos_realizados='" . $xml->paciente['tratamientos_realizados'] . "'";
			$sql.= ", estado_actual='" . $xml->paciente['estado_actual'] . "'";
			$sql.= ", estudios_complementarios='" . $xml->paciente['estudios_complementarios'] . "'";
			$sql.= ", req_cobertura_social='" . $xml->paciente['req_cobertura_social'] . "'";
			$sql.= ", req_declaracion_jurada='" . $xml->paciente['req_declaracion_jurada'] . "'";
			$sql.= ", req_fotocopia_dni='" . $xml->paciente['req_fotocopia_dni'] . "'";
			$sql.= ", req_certificado_residencia='" . $xml->paciente['req_certificado_residencia'] . "'";
			$sql.= ", req_fecha='" . $xml->paciente['req_fecha'] . "'";
			$sql.= ", sabe_leer='" . $xml->paciente['sabe_leer'] . "'";
			$sql.= ", sabe_escribir='" . $xml->paciente['sabe_escribir'] . "'";
			$sql.= ", telefonos='" . $xml->paciente['telefonos'] . "'";
			$sql.= ", peso='" . $xml->paciente['peso'] . "'";
			$sql.= ", altura='" . $xml->paciente['altura'] . "'";
			$sql.= ", imc='" . $xml->paciente['imc'] . "'";
			$sql.= ", ceguera='" . $xml->paciente['ceguera'] . "'";
			$sql.= ", msa_izq='" . $xml->paciente['msa_izq'] . "'";
			$sql.= ", msa_der='" . $xml->paciente['msa_der'] . "'";
			$sql.= ", mia_izq='" . $xml->paciente['mia_izq'] . "'";
			$sql.= ", mia_der='" . $xml->paciente['mia_der'] . "'";
			$sql.= ", observa='" . $xml->paciente['observa'] . "'";
			//$sql.= ", obra_social='" . $xml->paciente['obra_social'] . "'";
			//$sql.= ", nro_afiliado='" . $xml->paciente['nro_afiliado'] . "'";
			$sql.= ", estado='" . $xml->paciente['estado'] . "'";
			$sql.= ", inactivo='" . $xml->paciente['inactivo'] . "'";
			$sql.= ", id_oas_usuario='" . $_REQUEST['id_oas_usuario'] . "'";
			mysql_query($sql);
			$insert_id = mysql_insert_id();
			auditoria($sql, "Alta paciente", "alta_paciente", $insert_id);
			
			foreach ($xml->obras_sociales->obra_social as $item) {
				if ($item['modificado'] == "true") {
					$sql = "INSERT afiliado SET id_padron='" . $item['id_padron'] . "', nro_afiliado='" . $item['nro_afiliado'] . "' ON DUPLICATE KEY UPDATE nro_afiliado='" . $item['nro_afiliado'] . "'";
					mysql_query($sql);
				}
			}
			
			mysql_query("COMMIT");
		}

		break;
	}
	
	case "verificar_persona" : {
		$xml = new SimpleXMLElement("<root/>");
		

		
		
		
		
		$sql = "SELECT pacientes.*, _personal.apenom AS medico";
		$sql.= " FROM (pacientes INNER JOIN salud1._personas USING(persona_id)) INNER JOIN salud1._personal USING (id_personal)";
		if ($_REQUEST['id_paciente']=="0") {
			$sql.= " WHERE persona_tipodoc='" . $_REQUEST['persona_tipodoc'] . "' AND persona_dni='" . $_REQUEST['persona_dni'] . "'";
		} else {
			$sql.= " WHERE pacientes.id_paciente='" . $_REQUEST['id_paciente'] . "'";
		}
	
		$rs = mysql_query($sql);
		while ($row = mysql_fetch_array($rs)) {
			toXML($xml, $row, "paciente");
		}
		
		//$sql = "SELECT _personas.persona_id, _personas.persona_tipodoc, _personas.persona_dni, _personas.persona_nombre, _personas.persona_domicilio, _personas.persona_fecha_nacimiento, _personas.persona_sexo, _personas.localidad_id, localidad";
		//$sql.= " FROM salud1._personas LEFT JOIN salud1._localidades USING(localidad_id)";
		$sql = "SELECT _personas.persona_id, _personas.persona_tipodoc, _personas.persona_dni, _personas.persona_nombre, _personas.persona_domicilio, _personas.persona_fecha_nacimiento, _personas.persona_sexo, _personas.localidad_id, localidad, departamento";
		$sql.= " FROM (salud1._personas LEFT JOIN salud1._localidades USING(localidad_id)) LEFT JOIN salud1._departamentos USING(departamento_id)";
		if ($_REQUEST['id_paciente']=="0") {
			$sql.= " WHERE persona_tipodoc='" . $_REQUEST['persona_tipodoc'] . "' AND persona_dni='" . $_REQUEST['persona_dni'] . "'";
		} else {
			$sql.= " WHERE persona_id='" . $xml->paciente['persona_id'] . "'";
		}
	
		$rs = mysql_query($sql);
		while ($row = mysql_fetch_array($rs)) {
			$xmlPersona = toXML($xml, $row, "persona");
		}
		
		
		


		
		$xmlObrasSociales = new SimpleXMLElement("<obras_sociales/>");
		
		if ($_REQUEST['id_paciente']=="0") {
			if ($_REQUEST['persona_tipodoc']=="D") $td = "DNI";
			if ($_REQUEST['persona_tipodoc']=="C") $td = "LC";
			if ($_REQUEST['persona_tipodoc']=="E") $td = "LE";
			if ($_REQUEST['persona_tipodoc']=="P") $td = "PAS";
			if ($_REQUEST['persona_tipodoc']=="F") $td = "CI";
			
			$sql = "SELECT id_padron, nombre, siglas, nro_afiliado FROM (suram.financiadores INNER JOIN suram.padrones USING(id_financiador)) LEFT JOIN prodiase.afiliado USING(id_padron) WHERE padrones.tipo_doc='" . $td . "' AND padrones.nrodoc='" . $_REQUEST['persona_dni'] . "' ORDER BY siglas, nombre";
		} else {
			if ($xml->persona['persona_tipodoc']=="D") $td = "DNI";
			if ($xml->persona['persona_tipodoc']=="C") $td = "LC";
			if ($xml->persona['persona_tipodoc']=="E") $td = "LE";
			if ($xml->persona['persona_tipodoc']=="P") $td = "PAS";
			if ($xml->persona['persona_tipodoc']=="F") $td = "CI";
			
			$sql = "SELECT id_padron, nombre, siglas, nro_afiliado FROM (suram.financiadores INNER JOIN suram.padrones USING(id_financiador)) LEFT JOIN prodiase.afiliado USING(id_padron) WHERE padrones.tipo_doc='" . $td . "' AND padrones.nrodoc='" . $xml->persona['persona_dni'] . "' ORDER BY siglas, nombre";
		}
		
		$rsFinanciadores = mysql_query($sql);
		while ($rowFinanciadores = mysql_fetch_array($rsFinanciadores)) {
			if (! is_null($rowFinanciadores['siglas'])) $rowFinanciadores['siglas'] = trim($rowFinanciadores['siglas']);
			toXML($xmlObrasSociales, new SimpleXMLElement('<obra_social id_padron="' . $rowFinanciadores['id_padron'] . '" nombre="' . $rowFinanciadores['nombre'] . '" siglas="' . $rowFinanciadores['siglas'] . '" nro_afiliado="' . $rowFinanciadores['nro_afiliado'] . '" modificado="false"/>'));
		}
		toXML($xml, $xmlObrasSociales);
		
		


		

		header('Content-Type: text/xml');
		echo $xml->asXML();
		
		break;
	}
	
	case "leer_tipos_pacientes" : {
		$xml = new SimpleXMLElement("<root/>");
		
		$sql = "SELECT * FROM tipos_pacientes";
		$rs = mysql_query($sql);
		toXML($xml, $rs, "tipos_pacientes");

		header('Content-Type: text/xml');
		echo $xml->asXML();
		
		break;
	}
}

?>