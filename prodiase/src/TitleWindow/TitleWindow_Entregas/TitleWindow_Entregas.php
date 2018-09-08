<?php

include("../../control_acceso_flex.php");
//include("../../conexion.php");
include("../../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "agregar_entrega" : {
		$xml = new SimpleXMLElement(stripslashes($_REQUEST['model']));
		
		$auditoria_json = new stdClass;
		
		mysql_query("START TRANSACTION");
		
		if ($_REQUEST['a_hospital']=="true") {
			$sql = "INSERT entregas SET id_paciente='0', id_deposito='0', organismo_area_id='" . $xml['organismo_area_id'] . "', fecha='" . $xml['fecha'] . "', estado='A'";
		} else {
			$sql = "INSERT entregas SET id_paciente='0', id_deposito='" . $xml['id_deposito'] . "', organismo_area_id='', fecha='" . $xml['fecha'] . "', estado='E'";
		}
		mysql_query($sql);
		$auditoria_sql = $sql;
		$id_entrega = mysql_insert_id();
		
		foreach ($xml->items_entregas as $tratamiento) {
			if ((int) $tratamiento['cantidad'] > 0) {
				$sql = "INSERT items_entregas SET id_entrega=" . $id_entrega . ", id_producto_deposito=" . $tratamiento['id_producto_deposito'] . ", cantidad=" . $tratamiento['cantidad'];
				mysql_query($sql);
				
				$sql="UPDATE productos_depositos SET disponible_virtual = disponible_virtual - " . $tratamiento['cantidad'] . " WHERE id_producto_deposito=" . $tratamiento['id_producto_deposito'];
				mysql_query($sql);
			}
		}
		
		$auditoria_json = json_encode($auditoria_json);
		auditoria($auditoria_sql, "Autoriza entrega hospital/area", "autoriza_entrega_hospital/area", $id_entrega, $auditoria_json);
		
		mysql_query("COMMIT");
		
		break;
	}
}

?>