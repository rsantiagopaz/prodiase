<?php

include("../../control_acceso_flex.php");
//include("../../conexion.php");
include("../../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "leer_pacientes" : {
		$xml = new SimpleXMLElement("<root/>");
		
		if ($_REQUEST['por']=="medico") {
			$sql = "SELECT _personas.persona_id, _personas.persona_tipodoc, _personas.persona_dni, _personas.persona_nombre, _personas.persona_domicilio, _personas.persona_fecha_nacimiento, _personas.persona_sexo, pacientes.id_personal, pacientes.estado, localidad";
			$sql.= " FROM ((salud1._personas LEFT JOIN salud1._localidades USING(localidad_id)) INNER JOIN pacientes USING (persona_id))";
			$sql.= " WHERE pacientes.id_personal='" . $_REQUEST['id'] . "'";
			$sql.= " ORDER BY _personas.persona_nombre";
			// INNER JOIN salud1.personas_profesion USING (id_persona_profesion)) INNER JOIN salud1._personas ON personas_profesion.id_persona=_personas.persona_id
		} else if ($_REQUEST['por']=="producto") {
			$sql = "SELECT _personas.persona_id, _personas.persona_tipodoc, _personas.persona_dni, _personas.persona_nombre, _personas.persona_domicilio, _personas.persona_fecha_nacimiento, _personas.persona_sexo, pacientes.id_personal, pacientes.estado, localidad";
			$sql.= " FROM ((salud1._personas LEFT JOIN salud1._localidades USING(localidad_id)) INNER JOIN pacientes USING (persona_id)) INNER JOIN pacientes_productos USING (id_paciente)";
			$sql.= " WHERE pacientes_productos.id_producto='" . $_REQUEST['id'] . "' AND pacientes_productos.estado = 'A'";
			$sql.= " ORDER BY _personas.persona_nombre";
		} else {
			$sql = "SELECT _personas.persona_id, _personas.persona_tipodoc, _personas.persona_dni, _personas.persona_nombre, _personas.persona_domicilio, _personas.persona_fecha_nacimiento, _personas.persona_sexo, pacientes.id_personal, pacientes.estado, localidad";
			$sql.= " FROM ((salud1._personas LEFT JOIN salud1._localidades USING(localidad_id)) INNER JOIN pacientes USING (persona_id)) INNER JOIN tipos_pacientes USING(id_tipo_paciente)";
			$sql.= " WHERE pacientes.id_tipo_paciente='" . $_REQUEST['id'] . "'";
			$sql.= " ORDER BY _personas.persona_nombre";
		}
		
		$a = array();
		$a['D'] = "D.N.I.";
		$a['C'] = "Lib.Cívica";
		$a['E'] = "Lib.Enrolamiento";
		$a['P'] = "Pasaporte";
		$a['F'] = "Cedula";
		
		$rs = mysql_query($sql);
		while ($row = mysql_fetch_array($rs)) {
			$row['tipodoc'] = $a[$row['persona_tipodoc']];

			$sql = "SELECT apenom FROM salud1._personal WHERE id_personal='" . $row['id_personal'] . "'";
			$rsMedico = mysql_query($sql);
			$rowMedico = mysql_fetch_array($rsMedico);
			$row['medico'] = $rowMedico['apenom'];
			$row['estado'] = ($row['estado'] == "A") ? "Activo" : "Inactivo";
			
			toXML($xml, $row, "row");
		}

		header('Content-Type: text/xml');
		echo $xml->asXML();
		
		break;
	}
}

?>