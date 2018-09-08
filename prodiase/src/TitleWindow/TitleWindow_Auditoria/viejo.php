<?php

include("../../control_acceso_flex.php");
//include("../../conexion.php");
include("../../rutinas.php");


$xml = new SimpleXMLElement($_REQUEST['model']);

if ($xml->rbt0['seleccionado']=="true") {
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Auditoria entregas</title>
	</head>
	<body>
	<table border="0" cellpadding=0 cellspacing=0 width=750 height=1% align="center">
	<tr><td align="center" colspan="6"><big>AUDITORIA ENTREGAS</big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td colspan="10"><hr></td></tr>
	<?php
} else if ($xml->rbt1['seleccionado']=="true") {
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Auditoria ingresos</title>
	</head>
	<body>
	<table border="0" cellpadding=0 cellspacing=0 width=750 height=1% align="center">
	<tr><td align="center" colspan="6"><big>AUDITORIA INGRESOS</big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td colspan="10"><hr></td></tr>
	<?php
}

$sql = "SELECT * FROM _auditoria WHERE TRUE";
if ($xml->usuario['seleccionado']=="true") {
	$sql.= " AND SYSusuario='" . $xml->usuario['usuario'] . "'";
}
if ($xml->fecha['seleccionado']=="true") {
	$sql.= " AND (DATE(fecha_hora) BETWEEN '" . $xml->fecha['desde'] . "' AND '" . $xml->fecha['hasta'] . "')";
}
$sql.= " ORDER BY fecha_hora";
$rsAuditoria = mysql_query($sql);
while ($rowAuditoria = mysql_fetch_object($rsAuditoria)) {
	if ($xml->rbt0['seleccionado']=="true" && stripos(" 'autoriza_entrega_paciente', 'entrega_paciente', 'anula_entrega_paciente', 'autoriza_entrega_hospital/area', 'entrega_hospital/area', 'anula_entrega_hospital/area', 'entrega_deposito'", "'" . $rowAuditoria->tags . "'") > 0) {
		?>
		<tr><td><?php echo $rowAuditoria->mysql_descrip ?></td></tr>
		<tr><td><?php echo $rowAuditoria->SYSusuario ?></td><td><?php echo $rowAuditoria->fecha_hora ?></td></tr>
		<?php
		
		$sql = "SELECT * FROM entregas WHERE id_entrega=" . $rowAuditoria->id_registro;
		$rsEntregas = mysql_query($sql);
		$rowEntregas = mysql_fetch_object($rsEntregas);
		
		if ($rowEntregas->id_paciente != "0") {
			$sql = "SELECT salud1._personas.persona_nombre FROM pacientes INNER JOIN salud1._personas USING(persona_id) WHERE id_paciente=" . $rowEntregas->id_paciente;
			$rsPacientes = mysql_query($sql);
			$rowPacientes = mysql_fetch_object($rsPacientes);
			$paciente = $rowPacientes->persona_nombre;
		} else if ($rowEntregas->id_deposito != "0") {
			$sql = "SELECT denominacion FROM depositos WHERE id_deposito=" . $rowEntregas->id_deposito;
			$rsDepositos = mysql_query($sql);
			$rowDepositos = mysql_fetch_object($rsDepositos);
			$paciente = $rowDepositos->denominacion;
		} else {
			$sql = "SELECT DISTINCTROW CONCAT(_organismos_areas.organismo_area, ' (', CASE WHEN _organismos_areas.organismo_area_tipo_id='E' THEN _departamentos.departamento ELSE _organismos.organismo END, ')') AS destino";
			$sql.= " FROM (salud1._organismos_areas INNER JOIN salud1._organismos USING(organismo_id)) LEFT JOIN salud1._departamentos ON _organismos_areas.organismo_areas_id_departamento=_departamentos.codigo_indec";
			$sql.= " WHERE ((_organismos_areas.organismo_area_tipo_id='E' AND _departamentos.provincia_id=21) OR _organismos_areas.organismo_area_tipo_id<>'E') AND salud1._organismos_areas.organismo_area_id='" . $rowEntregas->organismo_area_id . "'";
			$rsOA = mysql_query($sql);
			$rowOA = mysql_fetch_object($rsOA);
			$paciente = $rowOA->destino;
		}
		
		$items_entregas = array();
		$sql = "SELECT CONCAT(productos.descripcion, ' - ', tipos_productos.tipo_producto) AS producto, cantidad, depositos.denominacion AS deposito FROM (((items_entregas INNER JOIN productos_depositos USING(id_producto_deposito)) LEFT JOIN productos USING(id_producto)) LEFT JOIN tipos_productos USING(id_tipo_producto)) LEFT JOIN depositos USING(id_deposito) WHERE id_entrega=" . $rowAuditoria->id_registro;
		$rsItems_entregas = mysql_query($sql);
		while ($rowItems_entregas = mysql_fetch_object($rsItems_entregas)) {
			$items_entregas[] = $rowItems_entregas;
			$deposito = $rowItems_entregas->deposito;
		}
		
		?>
		<tr><td><?php echo "a: " . $paciente ?></td><td><?php echo "de: " . $deposito ?></td></tr>
		<tr><td>&nbsp;</td></tr>
		<?php
		foreach ($items_entregas as $item) {
		?>
			<tr><td><?php echo $item->producto ?></td><td><?php echo $item->cantidad ?></td></tr>
		<?php
		}
		?>
		<tr><td colspan="10"><hr></td></tr>
		<?php
		
	} else if ($xml->rbt1['seleccionado']=="true" && stripos(" 'alta_ingreso'", "'" . $rowAuditoria->tags . "'") > 0) {
		?>
		<tr><td><?php echo $rowAuditoria->mysql_descrip ?></td></tr>
		<tr><td><?php echo $rowAuditoria->SYSusuario ?></td><td><?php echo $rowAuditoria->fecha_hora ?></td></tr>
		<?php
		
		$sql = "SELECT * FROM ingresos WHERE id_ingreso=" . $rowAuditoria->id_registro;
		$rsIngresos = mysql_query($sql);
		$rowIngresos = mysql_fetch_object($rsIngresos);
		
		if ($rowIngresos->tipo_ingreso == "D") {
			$sql = "SELECT denominacion FROM depositos WHERE id_deposito=" . $rowIngresos->id_deposito;
			$rsDepositos = mysql_query($sql);
			$rowDepositos = mysql_fetch_object($rsDepositos);
			$paciente = $rowDepositos->denominacion;
		} else {
			$paciente = "Proveed.N.Rem. " . $rowIngresos->nro_remito;
		}
		
		$items_ingresos = array();
		$sql = "SELECT CONCAT(productos.descripcion, ' - ', tipos_productos.tipo_producto) AS producto, cantidad, depositos.denominacion AS deposito FROM (((items_ingresos INNER JOIN productos_depositos USING(id_producto_deposito)) LEFT JOIN productos USING(id_producto)) LEFT JOIN tipos_productos USING(id_tipo_producto)) LEFT JOIN depositos USING(id_deposito) WHERE id_ingreso=" . $rowAuditoria->id_registro;
		$rsItems_ingresos = mysql_query($sql);
		while ($rowItems_ingresos = mysql_fetch_object($rsItems_ingresos)) {
			$items_ingresos[] = $rowItems_ingresos;
			$deposito = $rowItems_ingresos->deposito;
		}
		
		?>
		<tr><td><?php echo "a: " . $deposito ?></td><td><?php echo "de: " .  $paciente ?></td></tr>
		<tr><td>&nbsp;</td></tr>
		<?php
		foreach ($items_ingresos as $item) {
		?>
			<tr><td><?php echo $item->producto ?></td><td><?php echo $item->cantidad ?></td></tr>
		<?php
		}
		?>
		<tr><td colspan="10"><hr></td></tr>
		<?php
		
	}
}





























































if ($xml->rbt0['seleccionado']=="true") {
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Auditoria entregas</title>
	</head>
	<body>
	<table border="0" cellpadding=0 cellspacing=0 width=750 height=1% align="center">
	<tr><td align="center" colspan="6"><big>AUDITORIA ENTREGAS</big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td colspan="10"><hr></td></tr>
	<?php
	
	$sql = "SELECT * FROM _auditoria WHERE tags IN ('autoriza_entrega_paciente', 'entrega_paciente', 'anula_entrega_paciente', 'autoriza_entrega_hospital/area', 'entrega_hospital/area', 'anula_entrega_hospital/area', 'entrega_deposito')";
	if ($xml->usuario['seleccionado']=="true") {
		$sql.= " AND SYSusuario='" . $xml->usuario['usuario'] . "'";
	}
	if ($xml->fecha['seleccionado']=="true") {
		$sql.= " AND (DATE(fecha_hora) BETWEEN '" . $xml->fecha['desde'] . "' AND '" . $xml->fecha['hasta'] . "')";
	}
	$sql.= " ORDER BY fecha_hora";
	$rsAuditoria = mysql_query($sql);
	while ($rowAuditoria = mysql_fetch_object($rsAuditoria)) {
		?>
		<tr><td><?php echo $rowAuditoria->mysql_descrip ?></td></tr>
		<tr><td><?php echo $rowAuditoria->SYSusuario ?></td><td><?php echo $rowAuditoria->fecha_hora ?></td></tr>
		<?php
		
		$sql = "SELECT * FROM entregas WHERE id_entrega=" . $rowAuditoria->id_registro;
		$rsEntregas = mysql_query($sql);
		$rowEntregas = mysql_fetch_object($rsEntregas);
		
		if ($rowEntregas->id_paciente != "0") {
			$sql = "SELECT salud1._personas.persona_nombre FROM pacientes INNER JOIN salud1._personas USING(persona_id) WHERE id_paciente=" . $rowEntregas->id_paciente;
			$rsPacientes = mysql_query($sql);
			$rowPacientes = mysql_fetch_object($rsPacientes);
			$paciente = $rowPacientes->persona_nombre;
		} else if ($rowEntregas->id_deposito != "0") {
			$sql = "SELECT denominacion FROM depositos WHERE id_deposito=" . $rowEntregas->id_deposito;
			$rsDepositos = mysql_query($sql);
			$rowDepositos = mysql_fetch_object($rsDepositos);
			$paciente = $rowDepositos->denominacion;
		} else {
			$sql = "SELECT DISTINCTROW CONCAT(_organismos_areas.organismo_area, ' (', CASE WHEN _organismos_areas.organismo_area_tipo_id='E' THEN _departamentos.departamento ELSE _organismos.organismo END, ')') AS destino";
			$sql.= " FROM (salud1._organismos_areas INNER JOIN salud1._organismos USING(organismo_id)) LEFT JOIN salud1._departamentos ON _organismos_areas.organismo_areas_id_departamento=_departamentos.codigo_indec";
			$sql.= " WHERE ((_organismos_areas.organismo_area_tipo_id='E' AND _departamentos.provincia_id=21) OR _organismos_areas.organismo_area_tipo_id<>'E') AND salud1._organismos_areas.organismo_area_id='" . $rowEntregas->organismo_area_id . "'";
			$rsOA = mysql_query($sql);
			$rowOA = mysql_fetch_object($rsOA);
			$paciente = $rowOA->destino;
		}
		
		$items_entregas = array();
		$sql = "SELECT CONCAT(productos.descripcion, ' - ', tipos_productos.tipo_producto) AS producto, cantidad, depositos.denominacion AS deposito FROM (((items_entregas INNER JOIN productos_depositos USING(id_producto_deposito)) LEFT JOIN productos USING(id_producto)) LEFT JOIN tipos_productos USING(id_tipo_producto)) LEFT JOIN depositos USING(id_deposito) WHERE id_entrega=" . $rowAuditoria->id_registro;
		$rsItems_entregas = mysql_query($sql);
		while ($rowItems_entregas = mysql_fetch_object($rsItems_entregas)) {
			$items_entregas[] = $rowItems_entregas;
			$deposito = $rowItems_entregas->deposito;
		}
		
		?>
		<tr><td><?php echo "a: " . $paciente ?></td><td><?php echo "de: " . $deposito ?></td></tr>
		<tr><td>&nbsp;</td></tr>
		<?php
		foreach ($items_entregas as $item) {
		?>
			<tr><td><?php echo $item->producto ?></td><td><?php echo $item->cantidad ?></td></tr>
		<?php
		}
		?>
		<tr><td colspan="10"><hr></td></tr>
		<?php
		
	}
} else if ($xml->rbt1['seleccionado']=="true") {
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Auditoria ingresos</title>
	</head>
	<body>
	<table border="0" cellpadding=0 cellspacing=0 width=750 height=1% align="center">
	<tr><td align="center" colspan="6"><big>AUDITORIA INGRESOS</big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td colspan="10"><hr></td></tr>
	<?php
	
	$sql = "SELECT * FROM _auditoria WHERE tags IN ('alta_ingreso') ORDER BY fecha_hora";
	$rsAuditoria = mysql_query($sql);
	while ($rowAuditoria = mysql_fetch_object($rsAuditoria)) {
		?>
		<tr><td><?php echo $rowAuditoria->mysql_descrip ?></td></tr>
		<tr><td><?php echo $rowAuditoria->SYSusuario ?></td><td><?php echo $rowAuditoria->fecha_hora ?></td></tr>
		<?php
		
		$sql = "SELECT * FROM ingresos WHERE id_ingreso=" . $rowAuditoria->id_registro;
		$rsIngresos = mysql_query($sql);
		$rowIngresos = mysql_fetch_object($rsIngresos);
		
		if ($rowIngresos->tipo_ingreso == "D") {
			$sql = "SELECT denominacion FROM depositos WHERE id_deposito=" . $rowIngresos->id_deposito;
			$rsDepositos = mysql_query($sql);
			$rowDepositos = mysql_fetch_object($rsDepositos);
			$paciente = $rowDepositos->denominacion;
		} else {
			$paciente = "Proveed.N.Rem. " . $rowIngresos->nro_remito;
		}
		
		$items_ingresos = array();
		$sql = "SELECT CONCAT(productos.descripcion, ' - ', tipos_productos.tipo_producto) AS producto, cantidad, depositos.denominacion AS deposito FROM (((items_ingresos INNER JOIN productos_depositos USING(id_producto_deposito)) LEFT JOIN productos USING(id_producto)) LEFT JOIN tipos_productos USING(id_tipo_producto)) LEFT JOIN depositos USING(id_deposito) WHERE id_ingreso=" . $rowAuditoria->id_registro;
		$rsItems_ingresos = mysql_query($sql);
		while ($rowItems_ingresos = mysql_fetch_object($rsItems_ingresos)) {
			$items_ingresos[] = $rowItems_ingresos;
			$deposito = $rowItems_ingresos->deposito;
		}
		
		?>
		<tr><td><?php echo "a: " . $deposito ?></td><td><?php echo "de: " .  $paciente ?></td></tr>
		<tr><td>&nbsp;</td></tr>
		<?php
		foreach ($items_ingresos as $item) {
		?>
			<tr><td><?php echo $item->producto ?></td><td><?php echo $item->cantidad ?></td></tr>
		<?php
		}
		?>
		<tr><td colspan="10"><hr></td></tr>
		<?php
	}
}


?>