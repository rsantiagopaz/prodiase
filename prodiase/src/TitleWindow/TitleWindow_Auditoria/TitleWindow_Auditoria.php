<?php

set_time_limit(0);

include("../../control_acceso_flex.php");
//include("../../conexion.php");
include("../../rutinas.php");


$xml = new SimpleXMLElement($_REQUEST['model']);

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<title>
	<?php
	if ($xml->rbtEntregas['seleccionado']=="true") {
		echo "Auditoria entregas";
	} else if ($xml->rbtIngresos['seleccionado']=="true") {
		echo "Auditoria ingresos";
	} else if ($xml->rbtPacientes['seleccionado']=="true") {
		echo "Auditoria pacientes";
	} else {
		echo "Auditoria todos";
	}
	?>
	</title>
</head>
<body>
<table border="0" cellpadding=0 cellspacing=0 width=750 height=1% align="center">
<tr><td align="center" colspan="10"><big>
	<?php
	if ($xml->rbtEntregas['seleccionado']=="true") {
		echo "AUDITORIA ENTREGAS - " . date('Y-m-d');
	} else if ($xml->rbtIngresos['seleccionado']=="true") {
		echo "AUDITORIA INGRESOS - " . date('Y-m-d');
	} else if ($xml->rbtPacientes['seleccionado']=="true") {
		echo "AUDITORIA PACIENTES - " . date('Y-m-d');
	} else {
		echo "AUDITORIA TODOS - " . date('Y-m-d');
	}
	?>

</big></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td colspan="20">
<table border="1" rules="none" cellpadding=0 cellspacing=0 width="100%" height=1% align="center">
<tr><td colspan="2" align="center">Parámetros</td></tr>
<?php
if ($xml->usuario['seleccionado']=="true") {
	echo "<tr><td>Usuario:</td><td>" . $xml->usuario['usuario'] . "</td></tr>";
}
if ($xml->producto['seleccionado']=="true") {
	echo "<tr><td>Producto:</td><td>" . $xml->producto['texto'] . "</td></tr>";
}
if ($xml->paciente['seleccionado']=="true") {
	echo "<tr><td>Paciente:</td><td>" . $xml->paciente['texto'] . "</td></tr>";
}
if ($xml->deposito['seleccionado']=="true") {
	echo "<tr><td>Depósito:</td><td>" . $xml->deposito['texto'] . "</td></tr>";
}
if ($xml->fecha['seleccionado']=="true") {
	echo "<tr><td>Fecha:</td><td>desde " . $xml->fecha['desde'] . " hasta " . $xml->fecha['hasta'] . "</td></tr>";
}
?>
</table>
</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td colspan="10"><hr></td></tr>
<?php


if ($xml->rbtEntregas['seleccionado']=="true") {
	$sql = "SELECT * FROM _auditoria WHERE tags IN ('autoriza_entrega_paciente', 'entrega_paciente', 'anula_entrega_paciente', 'autoriza_entrega_hospital/area', 'entrega_hospital/area', 'anula_entrega_hospital/area', 'entrega_deposito')";
} else if ($xml->rbtIngresos['seleccionado']=="true") {
	$sql = "SELECT * FROM _auditoria WHERE tags IN ('alta_ingreso')";
} else if ($xml->rbtPacientes['seleccionado']=="true") {
	$sql = "SELECT * FROM _auditoria WHERE tags IN ('alta_paciente', 'actualiza_paciente', 'alta_tratamiento_paciente', 'desactiva_tratamiento_paciente')";
} else {
	$sql = "SELECT * FROM _auditoria WHERE TRUE";
}

if ($xml->usuario['seleccionado']=="true") {
	$sql.= " AND SYSusuario='" . $xml->usuario['usuario'] . "'";
}
if ($xml->fecha['seleccionado']=="true") {
	$sql.= " AND (DATE(fecha_hora) BETWEEN '" . $xml->fecha['desde'] . "' AND '" . $xml->fecha['hasta'] . "')";
}

$sql.= " ORDER BY fecha_hora";
$rsAuditoria = mysql_query($sql);
while ($rowAuditoria = mysql_fetch_object($rsAuditoria)) {
	$bandera1 = true;
	$bandera2 = false;
	$html = "";
	
	$html.= "<tr><td>&nbsp;</td></tr>";
	$html.= "<tr><td>" . $rowAuditoria->mysql_descrip . "</td></tr>";
	$html.= "<tr><td>&nbsp;</td></tr>";
	$html.= "<tr><td>" . $rowAuditoria->SYSusuario . "</td><td>" . $rowAuditoria->fecha_hora . "</td></tr>";
	
	if (stripos(" 'autoriza_entrega_paciente', 'entrega_paciente', 'anula_entrega_paciente', 'autoriza_entrega_hospital/area', 'entrega_hospital/area', 'anula_entrega_hospital/area', 'entrega_deposito'", "'" . $rowAuditoria->tags . "'") > 0) {
		
		$sql = "SELECT * FROM entregas WHERE id_entrega=" . $rowAuditoria->id_registro;
		$rsEntregas = mysql_query($sql);
		$rowEntregas = mysql_fetch_object($rsEntregas);
		
		if ($rowEntregas->id_paciente != "0") {
			$sql = "SELECT CONCAT(salud1._personas.persona_nombre, ' - ', salud1._personas.persona_dni, ' (', CASE salud1._personas.persona_tipodoc WHEN 'D' THEN 'D.N.I.' WHEN 'C' THEN 'Lib.Civica' WHEN 'E' THEN 'Lib.Enrolamiento' WHEN 'P' THEN 'Pasaporte' WHEN 'F' THEN 'Cedula' END, ')') AS descrip FROM pacientes INNER JOIN salud1._personas USING(persona_id) WHERE id_paciente=" . $rowEntregas->id_paciente;
			$rsPacientes = mysql_query($sql);
			$rowPacientes = mysql_fetch_object($rsPacientes);
			$paciente = $rowPacientes->descrip;
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
		
		if ($xml->paciente['seleccionado']=="true" && $xml->paciente['id_paciente'] != $rowEntregas->id_paciente) $bandera1 = false;
		
		$items_entregas = array();
		$sql = "SELECT productos.id_producto, CONCAT(productos.descripcion, ' | ', tipos_productos.tipo_producto) AS producto, cantidad, depositos.id_deposito, depositos.denominacion AS deposito FROM (((items_entregas INNER JOIN productos_depositos USING(id_producto_deposito)) LEFT JOIN productos USING(id_producto)) LEFT JOIN tipos_productos USING(id_tipo_producto)) LEFT JOIN depositos USING(id_deposito) WHERE id_entrega=" . $rowAuditoria->id_registro;
		$rsItems_entregas = mysql_query($sql);
		while ($rowItems_entregas = mysql_fetch_object($rsItems_entregas)) {
			$items_entregas[] = $rowItems_entregas;
			$deposito = $rowItems_entregas->deposito;
			
			if ($xml->deposito['seleccionado']=="true" && $xml->deposito['id_deposito'] != $rowItems_entregas->id_deposito) $bandera1 = false;
		}
		

		$html.= "<tr><td>a: " . $paciente . "</td><td>de: " . $deposito . "</td></tr>";
		$html.= "<tr><td>&nbsp;</td></tr>";

		foreach ($items_entregas as $item) {
			if ($xml->producto['seleccionado']=="false" || ($xml->producto['seleccionado']=="true" && $xml->producto['id_producto'] == $item->id_producto)) {
				$html.= "<tr><td>" . $item->producto . "</td><td>" . $item->cantidad . "</td></tr>";
				$bandera2 = true;
			}
		}
		
	} else if (stripos(" 'alta_ingreso'", "'" . $rowAuditoria->tags . "'") > 0) {
		
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
		$sql = "SELECT productos.id_producto, CONCAT(productos.descripcion, ' | ', tipos_productos.tipo_producto) AS producto, cantidad, depositos.id_deposito, depositos.denominacion AS deposito FROM (((items_ingresos INNER JOIN productos_depositos USING(id_producto_deposito)) LEFT JOIN productos USING(id_producto)) LEFT JOIN tipos_productos USING(id_tipo_producto)) LEFT JOIN depositos USING(id_deposito) WHERE id_ingreso=" . $rowAuditoria->id_registro;
		$rsItems_ingresos = mysql_query($sql);
		while ($rowItems_ingresos = mysql_fetch_object($rsItems_ingresos)) {
			$items_ingresos[] = $rowItems_ingresos;
			$deposito = $rowItems_ingresos->deposito;
			
			if ($xml->deposito['seleccionado']=="true" && $xml->deposito['id_deposito'] != $rowItems_ingresos->id_deposito) $bandera1 = false;
		}
		
		$html.= "<tr><td>a: " . $deposito . "</td><td>de: " .  $paciente . "</td></tr>";
		$html.= "<tr><td>&nbsp;</td></tr>";

		foreach ($items_ingresos as $item) {
			if ($xml->producto['seleccionado']=="false" || ($xml->producto['seleccionado']=="true" && $xml->producto['id_producto'] == $item->id_producto)) {
				$html.= "<tr><td>" . $item->producto . "</td><td>" . $item->cantidad . "</td></tr>";
				$bandera2 = true;
			}
		}

	} else if (stripos(" 'alta_paciente', 'actualiza_paciente'", "'" . $rowAuditoria->tags . "'") > 0) {
		$bandera2 = true;
		
		$sql = "SELECT CONCAT(salud1._personas.persona_nombre, ' - ', salud1._personas.persona_dni, ' (', CASE salud1._personas.persona_tipodoc WHEN 'D' THEN 'D.N.I.' WHEN 'C' THEN 'Lib.Civica' WHEN 'E' THEN 'Lib.Enrolamiento' WHEN 'P' THEN 'Pasaporte' WHEN 'F' THEN 'Cedula' END, ')') AS descrip FROM pacientes INNER JOIN salud1._personas USING(persona_id) WHERE id_paciente=" . $rowAuditoria->id_registro;
		$rsPacientes = mysql_query($sql);
		$rowPacientes = mysql_fetch_object($rsPacientes);
		$paciente = $rowPacientes->descrip;

		
		if ($xml->paciente['seleccionado']=="true" && $xml->paciente['id_paciente'] != $rowAuditoria->id_registro) $bandera1 = false;
		
		$html.= "<tr><td>a: " . $paciente . "</td></tr>";
		$html.= "<tr><td>&nbsp;</td></tr>";

	} else if (stripos(" 'alta_tratamiento_paciente', 'desactiva_tratamiento_paciente'", "'" . $rowAuditoria->tags . "'") > 0) {
		$bandera2 = true;
		
		$sql = "SELECT * FROM pacientes_productos WHERE id_paciente_producto=" . $rowAuditoria->id_registro;
		$rspacientes_productos = mysql_query($sql);
		$rowpacientes_productos = mysql_fetch_object($rspacientes_productos);
		
		$sql = "SELECT CONCAT(salud1._personas.persona_nombre, ' - ', salud1._personas.persona_dni, ' (', CASE salud1._personas.persona_tipodoc WHEN 'D' THEN 'D.N.I.' WHEN 'C' THEN 'Lib.Civica' WHEN 'E' THEN 'Lib.Enrolamiento' WHEN 'P' THEN 'Pasaporte' WHEN 'F' THEN 'Cedula' END, ')') AS descrip FROM pacientes INNER JOIN salud1._personas USING(persona_id) WHERE id_paciente=" . $rowpacientes_productos->id_paciente;
		$rsPacientes = mysql_query($sql);
		$rowPacientes = mysql_fetch_object($rsPacientes);
		$paciente = $rowPacientes->descrip;
		
		$html.= "<tr><td>a: " . $paciente . "</td></tr>";
		$html.= "<tr><td>&nbsp;</td></tr>";
		
		if ($xml->paciente['seleccionado']=="true" && $xml->paciente['id_paciente'] != $rowpacientes_productos->id_paciente) $bandera1 = false;
		
		$sql = "SELECT id_producto, CONCAT(productos.descripcion, ' | ', tipos_productos.tipo_producto) AS producto FROM productos INNER JOIN tipos_productos USING(id_tipo_producto) WHERE id_producto=" . $rowpacientes_productos->id_producto;
		$rsProducto = mysql_query($sql);
		$rowProducto = mysql_fetch_object($rsProducto);
		
		$html.= "<tr><td>" . $rowProducto->producto . "</td><td>" . $rowpacientes_productos->dosis_diaria . "</td></tr>";

		if ($xml->producto['seleccionado']=="true" && $xml->producto['id_producto'] != $rowProducto->id_producto) $bandera2 = false;
		
	} else if (stripos(" 'alta_producto'", "'" . $rowAuditoria->tags . "'") > 0) {
		$bandera2 = true;
		
		$sql = "SELECT id_producto, CONCAT(productos.descripcion, ' | ', tipos_productos.tipo_producto) AS producto FROM productos INNER JOIN tipos_productos USING(id_tipo_producto) WHERE id_producto=" . $rowAuditoria->id_registro;
		$rsProducto = mysql_query($sql);
		$rowProducto = mysql_fetch_object($rsProducto);
		
		$html.= "<tr><td>" . $rowProducto->producto . "</td></tr>";

		if ($xml->producto['seleccionado']=="true" && $xml->producto['id_producto'] != $rowProducto->id_producto) $bandera2 = false;
	}
	
	$html.= "<tr><td>&nbsp;</td></tr>";
	$html.= '<tr><td colspan="10"><hr></td></tr>';
	
	if ($bandera1 && $bandera2) echo $html;
}


?>
</table>
</body>
<?php


?>