<?php
include("../../control_acceso_flex.php");
//include("../../conexion.php");
include("../../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "nuevo_ingreso" : {
		$xml = new SimpleXMLElement(stripslashes($_REQUEST['model']));
		
		$auditoria_json = new stdClass;

		mysql_query("START TRANSACTION");

		if ($_REQUEST['de_proveedor']=="true") {
			$sql = "INSERT ingresos SET tipo_ingreso='P', id_deposito='0', nro_remito='" . $xml['nro_remito'] . "', fecha='" . $xml['fecha'] . "'";
		} else {
			$sql = "INSERT ingresos SET tipo_ingreso='D', id_deposito='" . $xml['id_deposito'] . "', fecha='" . $xml['fecha'] . "'";
		}
		mysql_query($sql);
		$auditoria_sql = $sql;
		$id_ingreso = mysql_insert_id();
		
		foreach ($xml->items_ingresos as $items_ingresos) {
			$sql = "INSERT items_ingresos SET id_ingreso=" . $id_ingreso . ", id_producto_deposito=" . $items_ingresos['id_producto_deposito'] . ", cantidad=" . $items_ingresos['cantidad'];
			mysql_query($sql);
			
			$sql="UPDATE productos_depositos SET disponible_real = disponible_real + " . $items_ingresos['cantidad'] . ", disponible_virtual = disponible_virtual + " . $items_ingresos['cantidad'] . " WHERE id_producto_deposito=" . $items_ingresos['id_producto_deposito'];
			mysql_query($sql);
		}
		
		$auditoria_json = json_encode($auditoria_json);
		auditoria($auditoria_sql, "Alta ingreso", "alta_ingreso", $id_ingreso, $auditoria_json);
		
		mysql_query("COMMIT");

		//$xml = leer_datos_ingresos();
		//header('Content-Type: text/xml');
		//echo $xml->asXML();
		
		header('Content-Type: text/xml');
		echo leer_datos_ingresos()->asXML();
		
		break;
	}
	
	case "leer_datos_ingresos" : {
		header('Content-Type: text/xml');
		echo leer_datos_ingresos()->asXML();

		break;
	}
}



function leer_datos_ingresos() {
	$xml = new SimpleXMLElement('<rows/>');
	
	$sql = "(SELECT id_ingreso, nro_remito, fecha, tipo_ingreso, id_deposito";
	$sql.= " FROM ingresos WHERE tipo_ingreso='P')";
	$sql.= " UNION ALL";
	$sql.= " (SELECT id_ingreso, depositos.denominacion AS nro_remito, fecha, tipo_ingreso, id_deposito";
	$sql.= " FROM ingresos LEFT JOIN depositos USING(id_deposito) WHERE tipo_ingreso='D')";
	$sql.= " ORDER BY fecha DESC, id_ingreso DESC";
	
	$rs = mysql_query($sql);
	while ($row = mysql_fetch_array($rs)) {
		$sql = "SELECT id_item_ingreso, descripcion, tipo_producto, items_ingresos.cantidad";
		$sql.= " FROM ((productos INNER JOIN tipos_productos USING(id_tipo_producto)) INNER JOIN productos_depositos USING(id_producto)) INNER JOIN items_ingresos USING(id_producto_deposito)";
		//$sql.= " WHERE id_ingreso='" . $row['id_ingreso'] . "'";
		$sql.= " WHERE id_ingreso='" . $row['id_ingreso'] . "' AND productos_depositos.id_deposito='" . $_REQUEST['id_deposito'] . "'";
		
		$rsItems_ingresos = mysql_query($sql);
		if (mysql_num_rows($rsItems_ingresos) > 0) {
			$nodo = toXML($xml, $row, "ingresos");
			toXML($nodo, $rsItems_ingresos, "items_ingresos");
		}
	}
	
	return $xml;
}
?>