<?php
include("../../control_acceso_flex.php");
//include("../../conexion.php");
include("../../rutinas.php");

switch ($_REQUEST['rutina'])
{
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
			auditoria($auditoria_sql, "Anula entrega hospital/area", "anula_entrega_hospital/area", $_REQUEST['id_entrega'], $auditoria_json);
			
			mysql_query("COMMIT");
		} else {
			echo "error";
		}
		
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
	
	/*
	$sql = "SELECT DISTINCTROW entregas.id_entrega, entregas.estado, fecha, CONCAT(_organismos_areas.organismo_area, ' (', CASE WHEN _organismos_areas.organismo_area_tipo_id='E' THEN _departamentos.departamento ELSE _organismos.organismo END, ')') AS destino, depositos.denominacion AS deposito, depositos.id_deposito";
	$sql.= " FROM (((((entregas INNER JOIN salud1._organismos_areas USING(organismo_area_id)) INNER JOIN salud1._organismos USING(organismo_id)) INNER JOIN items_entregas USING (id_entrega)) INNER JOIN productos_depositos USING(id_producto_deposito)) INNER JOIN depositos ON productos_depositos.id_deposito = depositos.id_deposito) LEFT JOIN salud1._departamentos ON _organismos_areas.organismo_areas_id_departamento=_departamentos.codigo_indec";
	$sql.= " WHERE ((_organismos_areas.organismo_area_tipo_id='E' AND _departamentos.provincia_id=21) OR _organismos_areas.organismo_area_tipo_id<>'E') AND entregas.organismo_area_id <> '' AND (DATE(entregas.fecha) BETWEEN '" . $_REQUEST['desde'] . "' AND '" . $_REQUEST['hasta'] . "')";
	$sql.= " ORDER BY fecha DESC, entregas.id_entrega DESC";
	*/
	
	$sql = "SELECT id_entrega, estado, fecha, organismo_area_id";
	$sql.= " FROM entregas";
	$sql.= " WHERE organismo_area_id <> '' AND (DATE(fecha) BETWEEN '" . $_REQUEST['desde'] . "' AND '" . $_REQUEST['hasta'] . "')";
	$sql.= " ORDER BY fecha DESC, id_entrega DESC";
	
	$rsEntregas = mysql_query($sql);
	while ($rowEntregas = mysql_fetch_array($rsEntregas)) {
		$sql = "SELECT organismo_area_id, organismo_area, organismo_area_tipo_id, organismo_id, organismo_areas_id_departamento";
		$sql.= " FROM salud1._organismos_areas";
		$sql.= " WHERE organismo_area_id='" . $rowEntregas["organismo_area_id"] . "'";
		
		$rs_organismos_areas = mysql_query($sql);
		$row_organismos_areas = mysql_fetch_array($rs_organismos_areas);
		
		
		$sql = "SELECT organismo";
		$sql.= " FROM salud1._organismos";
		$sql.= " WHERE organismo_id='" . $row_organismos_areas["organismo_id"] . "'";
		
		$rs_organismos = mysql_query($sql);
		$row_organismos = mysql_fetch_array($rs_organismos);
		
		
		$sql = "SELECT productos_depositos.id_deposito";
		$sql.= " FROM items_entregas INNER JOIN productos_depositos USING(id_producto_deposito)";
		$sql.= " WHERE id_entrega='" . $rowEntregas["id_entrega"] . "'";
		
		$rs_productos_depositos = mysql_query($sql);
		$row_productos_depositos = mysql_fetch_array($rs_productos_depositos);
		
		
		$sql = "SELECT depositos.denominacion AS deposito, depositos.id_deposito";
		$sql.= " FROM depositos";
		$sql.= " WHERE id_deposito='" . $row_productos_depositos["id_deposito"] . "'";
		
		$rs_depositos = mysql_query($sql);
		$row_depositos = mysql_fetch_array($rs_depositos);
		
		
		$sql = "SELECT departamento";
		$sql.= " FROM salud1._departamentos";
		$sql.= " WHERE codigo_indec='" . $row_organismos_areas["organismo_areas_id_departamento"] . "' AND provincia_id=21";
		
		$rs_departamentos = mysql_query($sql);
		if (mysql_num_rows($rs_departamentos) > 0) $row_departamentos = mysql_fetch_array($rs_departamentos); else $row_departamentos = array();
		
		
		
		
		if ($row_organismos_areas["organismo_area_tipo_id"] == "E") {
			$rowEntregas["destino"] = $row_organismos_areas["organismo_area"] . " (" . $row_departamentos["departamento"] . ")";
		} else {
			$rowEntregas["destino"] = $row_organismos_areas["organismo_area"] . " (" . $row_organismos["organismo"] . ")";
		}
		
		$rowEntregas["deposito"] = $row_depositos["deposito"];
		$rowEntregas["id_deposito"] = $row_depositos["id_deposito"];		
		
		
		
		
		
		$nodo = toXML($xml, $rowEntregas, "row");
		
		$sql = "SELECT descripcion, tipo_producto, cantidad";
		$sql.= " FROM ((productos INNER JOIN tipos_productos USING(id_tipo_producto)) INNER JOIN productos_depositos USING(id_producto)) INNER JOIN items_entregas USING(id_producto_deposito)";
		$sql.= " WHERE id_entrega='" . $nodo['id_entrega'] . "'";
		$sql.= " ORDER BY id_item_entrega";
		
		toXML($nodo, $sql, "row");
	}
	
	return $xml;
}
?>