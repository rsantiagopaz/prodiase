<?php

include("../../control_acceso_flex.php");
//include("../../conexion.php");
include("../../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "alta_persona" : {
		$xml = new SimpleXMLElement(stripslashes($_REQUEST['model']));
		
		//mysql_select_db("salud1", $link);
		//mysql_query("SET NAMES 'utf8'");
		
		$sql = "SELECT persona_nombre, persona_tipodoc, persona_dni";
		$sql.= " FROM salud1._personas";
		$sql.= " WHERE persona_tipodoc='" . $xml->persona['persona_tipodoc'] . "' AND persona_dni='" . $xml->persona['persona_dni'] . "'";

		$rs = mysql_query($sql);
		if ($row = mysql_fetch_array($rs)) {
			$a = array();
			$a['D'] = "D.N.I.";
			$a['C'] = "Lib.Cívica";
			$a['E'] = "Lib.Enrolamiento";
			$a['P'] = "Pasaporte";
			$a['F'] = "F";
			
			$error = "<error>";
			$error.= "Nombre: " . $row['persona_nombre'] . "\n"; 
			$error.= "Tipo doc.: " . $a[$row['persona_tipodoc']] . "\n";
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
			$xml->persona['persona_id'] = mysql_insert_id();
		}
	}
	
	case "nuevo_paciente" : {
		if (!isset($xml)) $xml = new SimpleXMLElement(stripslashes($_REQUEST['model']));
		
		//mysql_select_db("$base", $link);
		//mysql_query("SET NAMES 'utf8'");
		
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
			$a['F'] = "F";
			
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
			$sql = "INSERT INTO pacientes";
			$sql.= " SET persona_id='" . $xml->persona['persona_id'] . "'";
			$sql.= ", id_tipo_paciente='" . $xml->paciente['id_tipo_paciente'] . "'";
			$sql.= ", id_persona_profesion='" . $xml->paciente['id_persona_profesion'] . "'";
			$sql.= ", fecha_ingreso='" . $xml->paciente['fecha_ingreso'] . "'";
			$sql.= ", tratamientos_realizados='" . $xml->paciente['tratamientos_realizados'] . "'";
			$sql.= ", estado_actual='" . $xml->paciente['estado_actual'] . "'";
			$sql.= ", estudios_complementarios='" . $xml->paciente['estudios_complementarios'] . "'";
			$sql.= ", req_cobertura_social='" . $xml->paciente['req_cobertura_social'] . "'";
			$sql.= ", req_declaracion_jurada='" . $xml->paciente['req_declaracion_jurada'] . "'";
			$sql.= ", req_fotocopia_dni='" . $xml->paciente['req_fotocopia_dni'] . "'";
			$sql.= ", req_certificado_residencia='" . $xml->paciente['req_certificado_residencia'] . "'";
			$sql.= ", req_fecha='" . $xml->paciente['req_fecha'] . "'";
			mysql_query($sql);
		}

		break;
	}
	
	case "buscar_persona" : {
		$xml = new SimpleXMLElement("<root/>");
		
		//mysql_select_db("salud1", $link);
		//mysql_query("SET NAMES 'utf8'");
		
		$sql = "SELECT _personas.persona_id, _personas.persona_tipodoc, _personas.persona_dni, _personas.persona_nombre, _personas.persona_domicilio, _personas.persona_fecha_nacimiento, _personas.persona_sexo, localidad";
		$sql.= " FROM salud1._personas LEFT JOIN salud1._localidades USING(localidad_id)";
		$sql.= " WHERE persona_tipodoc='" . $_REQUEST['persona_tipodoc'] . "' AND persona_dni='" . $_REQUEST['persona_dni'] . "'";

		$rs = mysql_query($sql);
		if (mysql_num_rows($rs)) {
			$row = mysql_fetch_array($rs);
			toXML($xml, $row, "persona");
		}
		
		$sql = "SELECT pacientes.*, 024_personas_fisicas.apenom AS medico";
		$sql.= " FROM ((pacientes INNER JOIN salud1._personas USING(persona_id)) INNER JOIN salud1.personas_profesion USING (id_persona_profesion)) INNER JOIN salud1.024_personas_fisicas USING (id_persona)";
		$sql.= " WHERE persona_tipodoc='" . $_REQUEST['persona_tipodoc'] . "' AND persona_dni='" . $_REQUEST['persona_dni'] . "'";

		$rs = mysql_query($sql);
		if (mysql_num_rows($rs)) {
			$row = mysql_fetch_array($rs);
			toXML($xml, $row, "paciente");
		}

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