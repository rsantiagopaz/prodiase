<?php

include("../../control_acceso_flex.php");
//include("../../conexion.php");
include("../../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "leer_tipo_producto" : {
		$xml = new SimpleXMLElement('<root/>');
		
		$sql = "SELECT * FROM tipos_productos ORDER BY tipo_producto";
		$rs = mysql_query($sql);
		toXML($xml, $sql, "row");
		
		header('Content-Type: text/xml');
		echo $xml->asXML();
		
		break;
	}


	case "alta_producto" : {
		mysql_query("START TRANSACTION");
		
		$sql = "INSERT productos SET descripcion='" . $_REQUEST['descrip'] . "', id_tipo_producto='" . $_REQUEST['id_tipo_producto'] . "'";
		mysql_query($sql);
		$insert_id = mysql_insert_id();
		
		auditoria($sql, "Alta producto", "alta_producto", $insert_id);
		
		$sql = "SELECT id_deposito FROM depositos";
		$rs = mysql_query($sql);
		while ($row = mysql_fetch_array($rs)) {
			$sql = "INSERT productos_depositos SET id_deposito='" . $row['id_deposito'] . "', id_producto='" . $insert_id . "', disponible_virtual=0, disponible_real=0, pto_reposicion=0";
			mysql_query($sql);
		}
		
		mysql_query("COMMIT");
		
		break;
	}
	
	
	case "alta_deposito" : {
		$sql = "INSERT depositos SET denominacion='" . $_REQUEST['descrip'] . "', id_organismo_area_servicio=0";
		mysql_query($sql);
		$insert_id = mysql_insert_id();
		
		auditoria($sql, "Alta deposito", "alta_deposito", $insert_id);
		
		$sql = "SELECT id_producto FROM productos";
		$rs = mysql_query($sql);
		while ($row = mysql_fetch_array($rs)) {
			$sql = "INSERT productos_depositos SET id_deposito='" . $insert_id . "', id_producto='" . $row['id_producto'] . "', disponible_virtual=0, disponible_real=0, pto_reposicion=0";
			mysql_query($sql);
		}
		
		break;
	}
}

?>