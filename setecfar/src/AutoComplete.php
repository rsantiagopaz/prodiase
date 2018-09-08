<?php
//session_start();

include("control_acceso_flex.php");
//require_once('conexion.php');

$xml="<?xml version='1.0' encoding='UTF-8' ?><rows>";

if ($_REQUEST['rutina']=='autocompletePaciente') {
	if (is_numeric($_REQUEST['descrip'])) {
		$sql = "SELECT " .
				"pacientes.id_paciente AS id" . 
				", CONCAT(salud1._personas.persona_dni, ' (', CASE salud1._personas.persona_tipodoc WHEN 'D' THEN 'D.N.I.' WHEN 'C' THEN 'Lib.Civica' WHEN 'E' THEN 'Lib.Enrolamiento' WHEN 'P' THEN 'Pasaporte' WHEN 'F' THEN 'Cedula' END, ') - ', salud1._personas.persona_nombre) AS descrip" . 
			" FROM salud1._personas INNER JOIN pacientes USING(persona_id) WHERE salud1._personas.persona_dni LIKE '%" . $_REQUEST['descrip'] . "%'" . 
			" ORDER BY descrip";
	} else {
		$sql = "SELECT " .
				"pacientes.id_paciente AS id" . 
				", CONCAT(salud1._personas.persona_nombre, ' - ', salud1._personas.persona_dni, ' (', CASE salud1._personas.persona_tipodoc WHEN 'D' THEN 'D.N.I.' WHEN 'C' THEN 'Lib.Civica' WHEN 'E' THEN 'Lib.Enrolamiento' WHEN 'P' THEN 'Pasaporte' WHEN 'F' THEN 'Cedula' END, ')') AS descrip" . 
			" FROM salud1._personas INNER JOIN pacientes USING(persona_id) WHERE salud1._personas.persona_nombre LIKE '%" . $_REQUEST['descrip'] . "%'" . 
			" ORDER BY descrip";
	}
	$rs = mysql_query($sql);
	//$rs = mysql_query("SELECT pacientes.id_paciente AS id, salud1._personas.persona_nombre AS descrip FROM salud1._personas INNER JOIN pacientes USING(persona_id) WHERE salud1._personas.persona_nombre LIKE '%" . $_REQUEST['descrip'] . "%'");	
}
if ($_REQUEST['rutina']=='autocompleteProducto') {
	$rs = mysql_query("SELECT id_producto AS id, CONCAT(descripcion, ' | ', tipo_producto) AS descrip FROM productos INNER JOIN tipos_productos USING (id_tipo_producto) WHERE descripcion LIKE '%" . $_REQUEST['descrip'] . "%'");	
}
if ($_REQUEST['rutina']=='autocompleteProductoStock') {
	$rs = mysql_query("SELECT id_producto AS id, CONCAT(descripcion, ' | ', tipo_producto) AS descrip, id_producto_deposito, disponible_virtual, disponible_real FROM (productos INNER JOIN tipos_productos USING (id_tipo_producto)) INNER JOIN productos_depositos USING(id_producto) WHERE descripcion LIKE '%" . $_REQUEST['descrip'] . "%' AND id_deposito='" . $_REQUEST['id_deposito'] . "'");	
}
if ($_REQUEST['rutina']=='autocompleteHospital') {
	//$rs = mysql_query("SELECT organismo_area_id AS id, organismo_area AS descrip FROM salud1._organismos_areas WHERE organismo_area LIKE '%" . $_REQUEST['descrip'] . "%' ORDER BY descrip");
	//$rs = mysql_query("SELECT _organismos_areas.organismo_area_id AS id, CONCAT(_organismos_areas.organismo_area, ' (', CASE WHEN _organismos_areas.organismo_area_tipo_id='E' THEN _departamentos.departamento ELSE _organismos.organismo END, ')') AS descrip FROM (salud1._organismos_areas INNER JOIN salud1._organismos USING(organismo_id)) LEFT JOIN salud1._departamentos ON _organismos_areas.organismo_areas_id_departamento=_departamentos.codigo_indec WHERE (_organismos_areas.organismo_id='33' OR _organismos_areas.organismo_id='54') AND _departamentos.provincia_id=21 AND _organismos_areas.organismo_area LIKE '%" . $_REQUEST['descrip'] . "%' ORDER BY descrip");
	$sql = "(SELECT _organismos_areas.organismo_area_id AS id, CONCAT(_organismos_areas.organismo_area, ' (', _departamentos.departamento, ')') AS descrip FROM salud1._organismos_areas INNER JOIN salud1._departamentos ON _organismos_areas.organismo_areas_id_departamento=_departamentos.codigo_indec WHERE _organismos_areas.organismo_area_tipo_id='E' AND _departamentos.provincia_id=21 AND _organismos_areas.organismo_area LIKE '%" . $_REQUEST['descrip'] . "%')";
	$sql.= " UNION DISTINCT";
	$sql.=" (SELECT _organismos_areas.organismo_area_id AS id, CONCAT(_organismos_areas.organismo_area, ' (', _organismos.organismo, ')') AS descrip FROM salud1._organismos_areas INNER JOIN salud1._organismos USING(organismo_id) WHERE _organismos_areas.organismo_area_tipo_id<>'E' AND (_organismos_areas.organismo_id='33' OR _organismos_areas.organismo_id='54') AND _organismos_areas.organismo_area LIKE '%" . $_REQUEST['descrip'] . "%')";
	$sql.= " ORDER BY descrip";
	$rs = mysql_query($sql);
}
if ($_REQUEST['rutina']=='autocompleteObraSocial') {
	$rs = mysql_query("SELECT id_financiador AS id, nombre, siglas FROM suram.financiadores ORDER BY siglas, nombre"); 
}
while ($row = mysql_fetch_array($rs)) {
	if ($_REQUEST['rutina']=='autocompleteProductoStock') {
		$xml.='<row id="'.$row['id'].'" id_producto_deposito="' . $row['id_producto_deposito'] . '" disponible_virtual="' . $row['disponible_virtual'] . '" disponible_real="' . $row['disponible_real'] . '"><descrip><![CDATA['.$row['descrip'].']]></descrip></row>';
	} else if ($_REQUEST['rutina']=='autocompleteObraSocial') {
		if (! is_null($row['siglas'])) $row['siglas'] = trim($row['siglas']);
		$xml.='<row id="'.$row['id'].'"><descrip><![CDATA[' . ((empty($row['siglas'])) ? $row['nombre'] : $row['siglas'] . ", " . $row['nombre']) . ']]></descrip></row>';
	} else {
		$xml.='<row id="'.$row['id'].'"><descrip><![CDATA['.$row['descrip'].']]></descrip></row>';
	}

	//$aux='<row id="' . $row['id'] . '"><descrip><![CDATA[' . $row['descrip'] . ']]></descrip></row>';
	//$nodo=new SimpleXMLElement($aux);
	//rs2XML($xml, $nodo);
}

$xml.="</rows>";
header('Content-Type: text/xml');
echo $xml;


?>
