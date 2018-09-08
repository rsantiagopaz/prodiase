<?php
include("../../control_acceso_flex.php");
//include("../../conexion.php");
include("../../rutinas.php");

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
	
	case "anular_entrega" : {
		$sql = "SELECT estado FROM entregas WHERE id_entrega = " . $_REQUEST['id_entrega'];
		$rs = mysql_query($sql);
		$row = mysql_fetch_object($rs);
		if ($row->estado=="A") {
			$auditoria_json = new stdClass;
			
			mysql_query("START TRANSACTION");
			
			$sql = "UPDATE entregas SET estado = 'X' WHERE id_entrega = " . $_REQUEST['id_entrega'];
			mysql_query($sql);
			$auditoria_sql = $sql;
			
			$sql = "SELECT id_producto_deposito, cantidad FROM items_entregas WHERE id_entrega=" . $_REQUEST['id_entrega'];
			$rs = mysql_query($sql);
			while ($row = mysql_fetch_object($rs)) {
				$sql="UPDATE productos_depositos SET disponible_virtual = disponible_virtual + " . $row->cantidad . " WHERE id_producto_deposito=" . $row->id_producto_deposito;
				mysql_query($sql);
			}
			
			$auditoria_json = json_encode($auditoria_json);
			auditoria($auditoria_sql, "Anula entrega paciente", "anula_entrega_paciente", $_REQUEST['id_entrega'], $auditoria_json);
			
			mysql_query("COMMIT");
		} else {
			echo "error";
		}
		
		break;
	}
	
	case "agregar_entrega" : {
		$xml = new SimpleXMLElement(stripslashes($_REQUEST['model']));

		$auditoria_json = new stdClass;

		mysql_query("START TRANSACTION");
		
		if (is_null($_REQUEST['id_financiador'])) {
			$sql = "INSERT entregas SET id_paciente='" . $_REQUEST['id_paciente'] . "', id_deposito='0', organismo_area_id='', fecha = NOW(), estado = 'A'";
		} else {
			$sql = "INSERT entregas SET id_paciente='" . $_REQUEST['id_paciente'] . "', id_deposito='0', organismo_area_id='', fecha = NOW(), estado = 'A', id_financiador='" . $_REQUEST['id_financiador'] . "', nro_afiliado=" . ((empty($_REQUEST['nro_afiliado'])) ? "NULL" : "'" . $_REQUEST['nro_afiliado'] . "'");
		}

		mysql_query($sql);
		$auditoria_sql = $sql;
		$id_entrega = mysql_insert_id();
		
		$bandera_stock = true;
		
		foreach ($xml->tratamiento as $tratamiento) {
			if ((int) $tratamiento['cantidad'] > 0) {
				$sql = "INSERT items_entregas SET id_entrega=" . $id_entrega . ", id_producto_deposito=" . $tratamiento['id_producto_deposito'] . ", cantidad=" . $tratamiento['cantidad'];
				mysql_query($sql);
				
				$sql="UPDATE productos_depositos SET disponible_virtual = disponible_virtual - " . $tratamiento['cantidad'] . " WHERE id_producto_deposito=" . $tratamiento['id_producto_deposito'] . " AND disponible_virtual >= " . $tratamiento['cantidad'];
				mysql_query($sql);
				if (mysql_affected_rows() < 1) {
					$bandera_stock = false;
					break;
				}
			}
		}
		
		if ($bandera_stock) {
			$auditoria_json = json_encode($auditoria_json);
			auditoria($auditoria_sql, "Autoriza entrega paciente", "autoriza_entrega_paciente", $id_entrega, $auditoria_json);
		
			mysql_query("COMMIT");

			$xml = leer_datos_paciente($_REQUEST['id_paciente']);
			header('Content-Type: text/xml');
			echo $xml->asXML();
		} else {
			mysql_query("ROLLBACK");
			
			header('Content-Type: text/xml');
			echo '<root><error_stock>El stock de alguno de los items cambió en el transcurso de esta autorización. Por favor verifique y asigne de nuevo las cantidades de acuerdo al stock actualizado.</error_stock></root>';
		}
		
		break;
	}
	
	
	
	case "desactivar_item" : {
		$auditoria_json = new stdClass;
		
		$sql = "UPDATE pacientes_productos SET estado = 'I' WHERE id_paciente_producto = '" . $_REQUEST['id_paciente_producto'] . "'";
		mysql_query($sql);
		$auditoria_sql = $sql;
		
		$auditoria_json = json_encode($auditoria_json);
		auditoria($auditoria_sql, "Desactiva tratamiento paciente", "desactiva_tratamiento_paciente", $_REQUEST['id_paciente_producto'], $auditoria_json);
		
		break;
	}
	
	case "agregar_tratamiento" : {
		$xml = new SimpleXMLElement(stripslashes($_REQUEST['model']));
		$tratamiento = $xml->tratamiento;
		
		$auditoria_json = new stdClass;
		
		$sql = "INSERT pacientes_productos SET id_paciente='" . $tratamiento['id_paciente'] . "', id_producto='" . $tratamiento['id_producto'] . "', dosis_diaria='" . $tratamiento['dosis_diaria'] . "', estado='A'";
		mysql_query($sql);
		$auditoria_sql = $sql;
		$id_paciente_producto = mysql_insert_id();
		
		$auditoria_json = json_encode($auditoria_json);
		auditoria($auditoria_sql, "Alta tratamiento paciente", "alta_tratamiento_paciente", $id_paciente_producto, $auditoria_json);
				
		$xml = leer_datos_paciente($tratamiento['id_paciente']);
		$xml->addAttribute('id_paciente_producto', (string) $id_paciente_producto);
		
		header('Content-Type: text/xml');
		echo $xml->asXML();
		
		break;
	}
	
	case "leer_datos_paciente" : {
		header('Content-Type: text/xml');
		echo leer_datos_paciente($_REQUEST['id_paciente'])->asXML();

		break;
	}
}



function leer_datos_paciente($id_paciente) {
	$xml = new SimpleXMLElement('<root/>');
	
	$sql = "SELECT pacientes.id_paciente, pacientes.fecha_ingreso, pacientes.id_personal, pacientes.estado, salud1._personas.persona_nombre, salud1._personas.persona_tipodoc, salud1._personas.persona_dni, salud1._personas.persona_domicilio, salud1._personas.persona_fecha_nacimiento";
	$sql.= " FROM salud1._personas INNER JOIN pacientes USING(persona_id)";
	$sql.= " WHERE pacientes.id_paciente='" . $id_paciente . "'";
	
	$rs = mysql_query($sql);
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

	$nodo = toXML($xml, $row, "paciente");
	$nodoTratamientos = $xml->addChild('tratamientos');
	$nodoEntregas = $xml->addChild('entregas');
	$nodoObras_sociales = $xml->addChild('obras_sociales');
	


	
	if ($row['persona_tipodoc']=="D") $td = "DNI";
	if ($row['persona_tipodoc']=="C") $td = "LC";
	if ($row['persona_tipodoc']=="E") $td = "LE";
	if ($row['persona_tipodoc']=="P") $td = "PAS";
	if ($row['persona_tipodoc']=="F") $td = "CI";
	
	$sql = "SELECT id_financiador, nombre, siglas, nro_afiliado FROM (suram.financiadores INNER JOIN suram.padrones USING(id_financiador)) LEFT JOIN prodiase.afiliado USING(id_padron) WHERE padrones.tipo_doc='" . $td . "' AND padrones.nrodoc='" . $row['persona_dni'] . "' ORDER BY siglas, nombre";
	$rsFinanciadores = mysql_query($sql);
	while ($rowFinanciadores = mysql_fetch_array($rsFinanciadores)) {
		if (! is_null($rowFinanciadores['siglas'])) $rowFinanciadores['siglas'] = trim($rowFinanciadores['siglas']);
		toXML($nodoObras_sociales, new SimpleXMLElement('<obra_social id_financiador="' . $rowFinanciadores['id_financiador'] . '" descrip="' . ((empty($rowFinanciadores['siglas'])) ? $rowFinanciadores['nombre'] : $rowFinanciadores['siglas'] . ", " . $rowFinanciadores['nombre']) . " (" . $rowFinanciadores['nro_afiliado'] . ')" nro_afiliado="' . $rowFinanciadores['nro_afiliado'] . '"/>'));
	}
	

	
	
	$sql = "SELECT id_paciente_producto, productos.id_producto, productos.descripcion, tipos_productos.tipo_producto, dosis_diaria";
	$sql.= " FROM (pacientes_productos INNER JOIN productos USING(id_producto)) INNER JOIN tipos_productos USING(id_tipo_producto)";
	$sql.= " WHERE id_paciente='" . $id_paciente . "' AND pacientes_productos.estado='A'";

	$rs = mysql_query($sql);
	while ($row = mysql_fetch_array($rs)) {
		toXML($nodoTratamientos, $row, "tratamiento");
	}
	
	
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
		
		//$nodoEntrega = toXML($nodo, $rowEntregas, "entrega");
		$nodoEntrega = toXML($nodoEntregas, $rowEntregas, "entrega");
		
		$sql = "SELECT items_entregas.*, productos.descripcion, tipos_productos.tipo_producto";
		$sql.= " FROM ((items_entregas INNER JOIN productos_depositos USING(id_producto_deposito)) INNER JOIN productos USING(id_producto)) INNER JOIN tipos_productos USING(id_tipo_producto)";
		$sql.= " WHERE id_entrega='" . $rowEntregas['id_entrega'] . "'";
		
		
/*		$sql = "SELECT items_entregas.*, productos.descripcion, tipos_productos.tipo_producto, depositos.id_deposito, depositos.denominacion AS deposito";
		$sql.= " FROM (((items_entregas INNER JOIN productos_depositos USING(id_producto_deposito)) INNER JOIN productos USING(id_producto)) INNER JOIN tipos_productos USING(id_tipo_producto)) INNER JOIN depositos USING(id_deposito)";
		$sql.= " WHERE id_entrega='" . $rowEntregas['id_entrega'] . "'";*/
		
		toXML($nodoEntrega, $sql, "item_entrega");
		
/*		$rsItems = mysql_query($sql);
		while ($rowItems = mysql_fetch_array($rsItems)) {
			toXML($nodoEntrega, $rowItems, "item_entrega");
		}*/
	}
	
	return $xml;
}
?>