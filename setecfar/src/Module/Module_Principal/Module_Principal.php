<?php
include("../../control_acceso_flex.php");
//include("../../conexion.php");
include("../../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "entregar" : {
		$sql = "SELECT estado FROM entregas WHERE id_entrega = '" . $_REQUEST['id_entrega'] . "'";
		$rs = mysql_query($sql);
		$row = mysql_fetch_array($rs);
		if ($row['estado']=="A") {
			$auditoria_json = new stdClass;
			
			mysql_query("START TRANSACTION");
			
			$sql = "UPDATE entregas SET estado = 'E' WHERE id_entrega = '" . $_REQUEST['id_entrega'] . "'";
			mysql_query($sql);
			$auditoria_sql = $sql;
			
			$sql = "SELECT id_producto_deposito, cantidad, id_deposito, id_producto FROM items_entregas INNER JOIN productos_depositos USING(id_producto_deposito) WHERE id_entrega='" . $_REQUEST['id_entrega'] . "'";
			$rs = mysql_query($sql);
			while ($row = mysql_fetch_object($rs)) {
				$sql="UPDATE productos_depositos SET disponible_real = disponible_real - " . $row->cantidad . " WHERE id_producto_deposito=" . $row->id_producto_deposito;
				mysql_query($sql);
			}
			
			$auditoria_json = json_encode($auditoria_json);
			auditoria($auditoria_sql, "Entrega paciente", "entrega_paciente", $_REQUEST['id_entrega'], $auditoria_json);
			
			mysql_query("COMMIT");
		} else {
			echo "error";
		}
		
		break;
	}
	
	case "leer_datos_paciente" : {
		header('Content-Type: text/xml');
		echo leer_datos_paciente($_REQUEST['id_paciente'])->asXML();

		break;
	}
}



function leer_datos_paciente($id_paciente) {
	$xml = new SimpleXMLElement('<rows/>');
	
	$sql = "SELECT pacientes.id_paciente, pacientes.fecha_ingreso, pacientes.id_personal, salud1._personas.persona_nombre, salud1._personas.persona_tipodoc, salud1._personas.persona_dni, salud1._personas.persona_domicilio, salud1._personas.persona_fecha_nacimiento";
	$sql.= " FROM salud1._personas INNER JOIN pacientes USING(persona_id)";
	$sql.= " WHERE pacientes.id_paciente='" . $id_paciente . "'";
	
	$rs = mysql_query($sql);
	echo mysql_error();
	$row = mysql_fetch_array($rs);
	
	$a = array();
	$a['D'] = "D.N.I.";
	$a['C'] = "Lib.Cívica";
	$a['E'] = "Lib.Enrolamiento";
	$a['P'] = "Pasaporte";
	$a['F'] = "Cédula";
	
	$row['tipodoc'] = $a[$row['persona_tipodoc']];
	
	$sql = "SELECT apenom FROM salud1._personal WHERE id_personal='" . $row['id_personal'] . "'";
	$rsMedico = mysql_query($sql);
	$rowMedico = mysql_fetch_array($rsMedico);
	$row['medico'] = $rowMedico['apenom'];
	
	$nodoPaciente = toXML($xml, $row, "paciente");
	$nodoTratamientos = $nodoPaciente->addChild('tratamientos');
	
	$sql = "SELECT id_paciente_producto, productos.id_producto, productos.descripcion, tipos_productos.tipo_producto, dosis_diaria";
	$sql.= " FROM (pacientes_productos INNER JOIN productos USING(id_producto)) INNER JOIN tipos_productos USING(id_tipo_producto)";
	$sql.= " WHERE id_paciente='" . $id_paciente . "' AND pacientes_productos.estado='A'";

	toXML($nodoTratamientos, $sql, "tratamiento");
/*	$rs = mysql_query($sql);
	while ($row = mysql_fetch_array($rs)) {
		toXML($nodoTratamientos, $row, "tratamiento");
	}*/
	
	$sql = "SELECT DISTINCTROW entregas.id_entrega, entregas.fecha, entregas.estado, entregas.id_financiador, entregas.nro_afiliado, productos_depositos.id_deposito";
	$sql.= " FROM (entregas INNER JOIN items_entregas USING(id_entrega)) INNER JOIN productos_depositos USING(id_producto_deposito)";
	$sql.= " WHERE id_paciente='" . $id_paciente . "'";
	$sql.= " ORDER BY fecha DESC, id_entrega DESC";
	
	$rsEntregas = mysql_query($sql);
	while ($rowEntregas = mysql_fetch_array($rsEntregas)) {
		if (! is_null($rowEntregas['id_financiador'])) {
			$sql = "SELECT nombre, siglas FROM suram.financiadores WHERE id_financiador='" . $rowEntregas['id_financiador'] . "'";
			$rsFinanciadores = mysql_query($sql);
			$rowFinanciadores = mysql_fetch_array($rsFinanciadores);
			if (! is_null($rowFinanciadores['siglas'])) $rowFinanciadores['siglas'] = trim($rowFinanciadores['siglas']);
			$rowEntregas['obra_social_descrip'] = ((empty($rowFinanciadores['siglas'])) ? $rowFinanciadores['nombre'] : $rowFinanciadores['siglas'] . ", " . $rowFinanciadores['nombre']) . " (" . $rowEntregas['nro_afiliado'] . ")";
		}
		
		
		
		
		$sql = "SELECT denominacion FROM depositos WHERE id_deposito='" . $rowEntregas['id_deposito'] . "'";
		$rsDepositos = mysql_query($sql);
		$rowDepositos = mysql_fetch_array($rsDepositos);
		$rowEntregas['deposito'] = $rowDepositos['denominacion'];
		
		$nodoEntrega = toXML($nodoPaciente, $rowEntregas, "entrega");
		
		$sql = "SELECT items_entregas.*, productos.descripcion, tipos_productos.tipo_producto";
		$sql.= " FROM ((items_entregas INNER JOIN productos_depositos USING(id_producto_deposito)) INNER JOIN productos USING(id_producto)) INNER JOIN tipos_productos USING(id_tipo_producto)";
		$sql.= " WHERE id_entrega='" . $rowEntregas['id_entrega'] . "'";
		
		toXML($nodoEntrega, $sql, "item_entrega");
		
/*		$rsItems = mysql_query($sql);
		while ($rowItems = mysql_fetch_array($rsItems)) {
			toXML($nodoEntrega, $rowItems, "item_entrega");
		}*/
	}
		
	
	return $xml;
}
?>