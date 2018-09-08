<?php

include("control_acceso_flex.php");
//include("../../conexion.php");
include("rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "leer_depositos" : {
		$xml = new SimpleXMLElement('<root/>');
		
		$sql = "SELECT * FROM depositos ORDER BY denominacion";
		toXML($xml, $sql, "deposito");
		
		header('Content-Type: text/xml');
		echo $xml->asXML();
		
		break;
	}
	
	case "leer_stock" : {
		$xml = new SimpleXMLElement("<root/>");
		
		$sql = "SELECT productos.*, tipos_productos.tipo_producto";
		$sql.= " FROM productos INNER JOIN tipos_productos USING(id_tipo_producto)";
		$sql.= " ORDER BY descripcion, tipo_producto";
		
		$rs = mysql_query($sql);
		while ($row = mysql_fetch_array($rs)) {
			$nodo = toXML($xml, $row, "row");
			
			$sql = "SELECT depositos.denominacion AS deposito, disponible_virtual, disponible_real, pto_reposicion";
			$sql.= " FROM depositos INNER JOIN productos_depositos USING(id_deposito)";
			$sql.= " WHERE id_producto='" . $row['id_producto'] . "'";
			$sql.= " ORDER BY deposito";
			
			$rsDeposito = mysql_query($sql);
			toXML($nodo, $rsDeposito, "row");
		}
		
		
		header('Content-Type: text/xml');
		echo $xml->asXML();
		break;
	}
	
	
	case "leer_pacientes" : {
		$xml = new SimpleXMLElement("<root/>");
		
		//$sql = "SELECT _personas.persona_id, _personas.persona_tipodoc, _personas.persona_dni, _personas.persona_nombre, _personas.persona_domicilio, _personas.persona_fecha_nacimiento, _personas.persona_sexo, localidad, 024_personas_fisicas.apenom AS medico";
		//$sql.= " FROM ((salud1._personas LEFT JOIN salud1._localidades USING(localidad_id)) INNER JOIN pacientes USING (persona_id)) INNER JOIN salud1.024_personas_fisicas USING (id_persona_profesion)";
		
		//$sql = "SELECT _personas.persona_id, _personas.persona_tipodoc, _personas.persona_dni, _personas.persona_nombre, _personas.persona_domicilio, _personas.persona_fecha_nacimiento, _personas.persona_sexo, localidad";
		//$sql.= " FROM ((salud1._personas LEFT JOIN salud1._localidades USING(localidad_id)) INNER JOIN pacientes USING (persona_id))";
		
		$sql = "SELECT _personas.persona_id, _personas.persona_tipodoc, _personas.persona_dni, _personas.persona_nombre, _personas.persona_domicilio, _personas.persona_fecha_nacimiento, _personas.persona_sexo, pacientes.id_personal, localidad, pacientes.tratamientos_realizados, pacientes.estado";
		$sql.= " FROM ((salud1._personas LEFT JOIN salud1._localidades USING(localidad_id)) INNER JOIN pacientes USING (persona_id))";
		if (is_null($_REQUEST['activos'])) {

		} else if ($_REQUEST['activos']=="true") {
			$sql.= " WHERE pacientes.estado='A'";
		} else {
			$sql.= " WHERE pacientes.estado='I'";
		}
		$sql.= " ORDER BY _personas.persona_nombre";
		
		$a = array();
		$a['D'] = "D.N.I.";
		$a['C'] = "Lib.Cívica";
		$a['E'] = "Lib.Enrolamiento";
		$a['P'] = "Pasaporte";
		$a['F'] = "Cédula";
		
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