<?php

include("../../control_acceso_flex.php");
//include("../../conexion.php");
include("../../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "leer_historial" : {
		$xml = new SimpleXMLElement('<root/>');
		
		if ($_REQUEST['a_depositos']=='true') {
			$sql = "SELECT DISTINCTROW entregas.id_entrega, fecha, depositos.denominacion AS destino";
			$sql.= " FROM ((entregas INNER JOIN depositos USING(id_deposito)) INNER JOIN items_entregas USING (id_entrega)) INNER JOIN productos_depositos USING(id_producto_deposito)";
			$sql.= " WHERE productos_depositos.id_deposito='" . $_REQUEST['id_deposito'] . "' AND (fecha BETWEEN '" . $_REQUEST['desde'] . "' AND '" . $_REQUEST['hasta'] . "')";
			$sql.= " ORDER BY fecha, entregas.id_entrega";
		} else if ($_REQUEST['en_gral']=='true') {
			//$sql = "SELECT DISTINCTROW entregas.id_entrega, fecha, CONCAT(_organismos_areas.organismo_area, ' (', _departamentos.departamento, ')') AS destino";
			//$sql.= " FROM (((entregas INNER JOIN salud1._organismos_areas USING(organismo_area_id)) LEFT JOIN salud1._departamentos ON _organismos_areas.organismo_areas_id_departamento=_departamentos.codigo_indec) INNER JOIN items_entregas USING (id_entrega)) INNER JOIN productos_depositos USING(id_producto_deposito)";
			//$sql.= " WHERE _departamentos.provincia_id=21 AND productos_depositos.id_deposito='" . $_REQUEST['id_deposito'] . "' AND entregas.organismo_area_id <> '' AND (fecha BETWEEN '" . $_REQUEST['desde'] . "' AND '" . $_REQUEST['hasta'] . "')";
			//$sql.= " ORDER BY fecha, entregas.id_entrega";
			
			
			$sql = "SELECT DISTINCTROW entregas.id_entrega, entregas.estado, fecha, CONCAT(_organismos_areas.organismo_area, ' (', CASE WHEN _organismos_areas.organismo_area_tipo_id='E' THEN _departamentos.departamento ELSE _organismos.organismo END, ')') AS destino, depositos.denominacion AS deposito, depositos.id_deposito";
			$sql.= " FROM (((((entregas INNER JOIN salud1._organismos_areas USING(organismo_area_id)) INNER JOIN salud1._organismos USING(organismo_id)) INNER JOIN items_entregas USING (id_entrega)) INNER JOIN productos_depositos USING(id_producto_deposito)) INNER JOIN depositos ON productos_depositos.id_deposito = depositos.id_deposito) LEFT JOIN salud1._departamentos ON _organismos_areas.organismo_areas_id_departamento=_departamentos.codigo_indec";
			$sql.= " WHERE ((_organismos_areas.organismo_area_tipo_id='E' AND _departamentos.provincia_id=21) OR _organismos_areas.organismo_area_tipo_id<>'E') AND entregas.organismo_area_id <> '' AND productos_depositos.id_deposito=" . $_REQUEST['id_deposito'];
			$sql.= " ORDER BY fecha, entregas.id_entrega";
		} else if ($_REQUEST['a_pacientes']=='true') {
			
		} else {
			//$sql = "SELECT DISTINCTROW entregas.id_entrega, fecha, CONCAT(_organismos_areas.organismo_area, ' (', _departamentos.departamento, ')') AS destino";
			//$sql.= " FROM (((entregas INNER JOIN salud1._organismos_areas USING(organismo_area_id)) LEFT JOIN salud1._departamentos ON _organismos_areas.organismo_areas_id_departamento=_departamentos.codigo_indec) INNER JOIN items_entregas USING (id_entrega)) INNER JOIN productos_depositos USING(id_producto_deposito)";
			//$sql.= " WHERE _departamentos.provincia_id=21 AND productos_depositos.id_deposito='" . $_REQUEST['id_deposito'] . "' AND entregas.organismo_area_id='" . $_REQUEST['organismo_area_id'] . "' AND (fecha BETWEEN '" . $_REQUEST['desde'] . "' AND '" . $_REQUEST['hasta'] . "')";
			//$sql.= " ORDER BY fecha, entregas.id_entrega";
			
	//$sql = "SELECT DISTINCTROW entregas.id_entrega, fecha, CONCAT(_organismos_areas.organismo_area, ' (', _departamentos.departamento, ')') AS destino";
	//$sql.= " FROM (((entregas INNER JOIN salud1._organismos_areas USING(organismo_area_id)) LEFT JOIN salud1._departamentos ON _organismos_areas.organismo_areas_id_departamento=_departamentos.codigo_indec) INNER JOIN items_entregas USING (id_entrega)) INNER JOIN productos_depositos USING(id_producto_deposito)";
	//$sql.= " WHERE _departamentos.provincia_id=21 AND productos_depositos.id_deposito='" . $_REQUEST['id_deposito'] . "' AND entregas.organismo_area_id='" . $_REQUEST['organismo_area_id'] . "' AND (fecha BETWEEN '" . $_REQUEST['desde'] . "' AND '" . $_REQUEST['hasta'] . "')";
	//$sql.= " ORDER BY fecha, entregas.id_entrega";
			
			
			
			$sql = "SELECT DISTINCTROW entregas.id_entrega, entregas.estado, fecha, CONCAT(_organismos_areas.organismo_area, ' (', CASE WHEN _organismos_areas.organismo_area_tipo_id='E' THEN _departamentos.departamento ELSE _organismos.organismo END, ')') AS destino, depositos.denominacion AS deposito, depositos.id_deposito";
			$sql.= " FROM (((((entregas INNER JOIN salud1._organismos_areas USING(organismo_area_id)) INNER JOIN salud1._organismos USING(organismo_id)) INNER JOIN items_entregas USING (id_entrega)) INNER JOIN productos_depositos USING(id_producto_deposito)) INNER JOIN depositos ON productos_depositos.id_deposito = depositos.id_deposito) LEFT JOIN salud1._departamentos ON _organismos_areas.organismo_areas_id_departamento=_departamentos.codigo_indec";
			$sql.= " WHERE ((_organismos_areas.organismo_area_tipo_id='E' AND _departamentos.provincia_id=21) OR _organismos_areas.organismo_area_tipo_id<>'E') AND entregas.organismo_area_id = '" . $_REQUEST['organismo_area_id'] . "' AND productos_depositos.id_deposito=" . $_REQUEST['id_deposito'] . " AND (fecha BETWEEN '" . $_REQUEST['desde'] . "' AND '" . $_REQUEST['hasta'] . "')";
			$sql.= " ORDER BY fecha, entregas.id_entrega";
		}
		
		$rsEntregas = mysql_query($sql);
		while ($rowEntregas = mysql_fetch_array($rsEntregas)) {
			$nodo = toXML($xml, $rowEntregas, "row");
			
			$sql = "SELECT descripcion, tipo_producto, cantidad";
			$sql.= " FROM ((productos INNER JOIN tipos_productos USING(id_tipo_producto)) INNER JOIN productos_depositos USING(id_producto)) INNER JOIN items_entregas USING(id_producto_deposito)";
			$sql.= " WHERE id_entrega='" . $nodo['id_entrega'] . "'";
			$sql.= " ORDER BY id_item_entrega";
			
			toXML($nodo, $sql, "row");
		}
		
		header('Content-Type: text/xml');
		echo $xml->asXML();
		
		break;
	}
}

?>