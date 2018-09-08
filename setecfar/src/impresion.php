<?php

include("control_acceso_flex.php");
//include("../../conexion.php");
include("rutinas.php");

switch ($_REQUEST['rutina'])
{
case "entregas_pacientes_por_producto" : {
	$sql = "SELECT productos.id_producto, productos.descripcion AS producto, SUM(items_entregas.cantidad) AS cantidad";
	$sql.= " FROM ((entregas INNER JOIN items_entregas USING(id_entrega)) INNER JOIN productos_depositos USING(id_producto_deposito)) INNER JOIN productos USING(id_producto)";
	$sql.= " WHERE entregas.estado <> 'X' AND entregas.id_paciente <> 0 AND (DATE(entregas.fecha) BETWEEN '" . $_REQUEST['desde'] . "' AND '" . $_REQUEST['hasta'] . "')";
	if (! is_null($_REQUEST['id_financiador'])) {
		if ($_REQUEST['id_financiador'] == "NULL") {
			$sql.= " AND ISNULL(entregas.id_financiador)";
		} else {
			$sql.= " AND entregas.id_financiador=" . $_REQUEST['id_financiador'];
		}
	}
	$sql.= " GROUP BY id_producto";
	$sql.= " ORDER BY producto";
	
	$rs = mysql_query($sql);
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Listado entregas a pacientes x producto</title>
	</head>
	<body>
	<table border="0" cellpadding=0 cellspacing=0 width=550 height=1% align="center">
	<tr><td align="center" colspan="6"><big>LISTADO ENTREGAS A PACIENTES x PRODUCTO - <?php echo date('Y-m-d') ?></big></td></tr>
	<tr><td align="center" colspan="6"><big>ENTRE <?php echo $_REQUEST['desde'] . " / " . $_REQUEST['hasta'] ?></big></td></tr>
	<?php
	if (! is_null($_REQUEST['id_financiador'])) {
		if ($_REQUEST['id_financiador'] == "NULL") {
			$rowFinanciadores = array();
			$rowFinanciadores['nombre'] = "sin OS";
		} else {
			$rsFinanciadores = mysql_query("SELECT CONCAT(siglas, ', ', nombre) AS nombre FROM suram.financiadores WHERE id_financiador=" . $_REQUEST['id_financiador']);
			$rowFinanciadores = mysql_fetch_array($rsFinanciadores);
		}
	?>
		<tr><td align="center" colspan="6"><big>O.SOCIAL:  <?php echo $rowFinanciadores['nombre'] ?></big></td></tr>
	<?php
	};
	?>
	<tr><td>&nbsp;</td></tr>
	
	<tr><td colspan="20">
	<table border="1" rules="all" cellpadding=0 cellspacing=0 width="99%" height=1% align="center">
	<tr><td>Producto</td><td align="right">Cant.</td></tr>
	<?php
	while ($row = mysql_fetch_array($rs)) {
		?>
		<tr>
		<td><?php echo $row['producto']; ?></td>
		<td align="right"><?php echo $row['cantidad']; ?></td>
		</tr>
		<?php
	}
	?>
	</table>
	</td></tr>
	</table>
	</body>
	</html>
	<?php
	
break;
}


case "historial_entregas" : {
		if ($_REQUEST['a_depositos']=='true') {
			$sql = "SELECT DISTINCTROW entregas.id_entrega, fecha, depositos.denominacion AS destino";
			$sql.= " FROM ((entregas INNER JOIN depositos USING(id_deposito)) INNER JOIN items_entregas USING (id_entrega)) INNER JOIN productos_depositos USING(id_producto_deposito)";
			$sql.= " WHERE entregas.estado <> 'X' AND productos_depositos.id_deposito='" . $_REQUEST['id_deposito'] . "' AND (DATE(entregas.fecha) BETWEEN '" . $_REQUEST['desde'] . "' AND '" . $_REQUEST['hasta'] . "')";
			$sql.= " ORDER BY fecha, entregas.id_entrega";
			
			?>
			<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
			<head>
				<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
				<title>HISTORIAL ENTREGAS A DEPOSITO</title>
			</head>
			<body>
			<input type="submit" value="Imprimir" onClick="window.print();"/>
			<table border="0" cellpadding=0 cellspacing=0 width=950 height=1% align="center">
			<tr><td align="center" colspan="20"><big>HISTORIAL ENTREGAS A DEPOSITO  <?php echo date('Y-m-d') ?></big></td></tr>
			<tr><td align="center" colspan="20"><big><?php echo $_REQUEST['deposito'] . ": " . $_REQUEST['desde'] . " / " . $_REQUEST['hasta'] ?></big></td></tr>
			<tr><td>&nbsp;</td></tr>
			<?php
		} else if ($_REQUEST['en_gral']=='true') {
			$sql = "SELECT DISTINCTROW entregas.id_entrega, entregas.estado, fecha, CONCAT(_organismos_areas.organismo_area, ' (', CASE WHEN _organismos_areas.organismo_area_tipo_id='E' THEN _departamentos.departamento ELSE _organismos.organismo END, ')') AS destino, depositos.denominacion AS deposito, depositos.id_deposito";
			$sql.= " FROM (((((entregas INNER JOIN salud1._organismos_areas USING(organismo_area_id)) INNER JOIN salud1._organismos USING(organismo_id)) INNER JOIN items_entregas USING (id_entrega)) INNER JOIN productos_depositos USING(id_producto_deposito)) INNER JOIN depositos ON productos_depositos.id_deposito = depositos.id_deposito) LEFT JOIN salud1._departamentos ON _organismos_areas.organismo_areas_id_departamento=_departamentos.codigo_indec";
			$sql.= " WHERE entregas.estado <> 'X' AND ((_organismos_areas.organismo_area_tipo_id='E' AND _departamentos.provincia_id=21) OR _organismos_areas.organismo_area_tipo_id<>'E') AND entregas.organismo_area_id <> '' AND productos_depositos.id_deposito=" . $_REQUEST['id_deposito'];
			$sql.= " ORDER BY fecha, entregas.id_entrega";
			
			?>
			<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
			<head>
				<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
				<title>HISTORIAL ENTREGAS EN GRAL.</title>
			</head>
			<body>
			<input type="submit" value="Imprimir" onClick="window.print();"/>
			<table border="0" cellpadding=0 cellspacing=0 width=950 height=1% align="center">
			<tr><td align="center" colspan="20"><big>HISTORIAL ENTREGAS EN GRAL.  <?php echo date('Y-m-d') ?></big></td></tr>
			<tr><td align="center" colspan="20"><big><?php echo $_REQUEST['deposito'] . ": " . $_REQUEST['desde'] . " / " . $_REQUEST['hasta'] ?></big></td></tr>
			<tr><td>&nbsp;</td></tr>
			<?php
		} else if ($_REQUEST['a_pacientes']=='true') {
			
		} else if ($_REQUEST['en_particular']=='true') {
			$sql = "SELECT DISTINCTROW entregas.id_entrega, entregas.estado, fecha, CONCAT(_organismos_areas.organismo_area, ' (', CASE WHEN _organismos_areas.organismo_area_tipo_id='E' THEN _departamentos.departamento ELSE _organismos.organismo END, ')') AS destino, depositos.denominacion AS deposito, depositos.id_deposito";
			$sql.= " FROM (((((entregas INNER JOIN salud1._organismos_areas USING(organismo_area_id)) INNER JOIN salud1._organismos USING(organismo_id)) INNER JOIN items_entregas USING (id_entrega)) INNER JOIN productos_depositos USING(id_producto_deposito)) INNER JOIN depositos ON productos_depositos.id_deposito = depositos.id_deposito) LEFT JOIN salud1._departamentos ON _organismos_areas.organismo_areas_id_departamento=_departamentos.codigo_indec";
			$sql.= " WHERE entregas.estado <> 'X' AND ((_organismos_areas.organismo_area_tipo_id='E' AND _departamentos.provincia_id=21) OR _organismos_areas.organismo_area_tipo_id<>'E') AND entregas.organismo_area_id = '" . $_REQUEST['organismo_area_id'] . "' AND productos_depositos.id_deposito=" . $_REQUEST['id_deposito'] . " AND (DATE(entregas.fecha) BETWEEN '" . $_REQUEST['desde'] . "' AND '" . $_REQUEST['hasta'] . "')";
			$sql.= " ORDER BY fecha, entregas.id_entrega";
			
			?>
			<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
			<head>
				<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
				<title>HISTORIAL ENTREGAS EN PARTICULAR</title>
			</head>
			<body>
			<input type="submit" value="Imprimir" onClick="window.print();"/>
			<table border="0" cellpadding=0 cellspacing=0 width=950 height=1% align="center">
			<tr><td align="center" colspan="20"><big>HISTORIAL ENTREGAS EN PARTICULAR  <?php echo date('Y-m-d') ?></big></td></tr>
			<tr><td align="center" colspan="20"><big><?php echo $_REQUEST['deposito'] . ": " . $_REQUEST['desde'] . " / " . $_REQUEST['hasta'] ?></big></td></tr>
			<tr><td>&nbsp;</td></tr>
			<?php
		}
	
		$rsEntregas = mysql_query($sql);
		while ($rowEntregas = mysql_fetch_array($rsEntregas)) {
			?>
			<tr><td>Fecha: <?php echo $rowEntregas['fecha']; ?></td></tr>
			<tr><td>Destino: <?php echo $rowEntregas['destino']; ?></td></tr>
			<?php
			?>
			<tr><td colspan="20">
			<table border="1" rules="all" cellpadding=0 cellspacing=0 width="100%" height=1% align="center">
			<?php
			
			$sql = "SELECT descripcion, tipo_producto, cantidad";
			$sql.= " FROM ((productos INNER JOIN tipos_productos USING(id_tipo_producto)) INNER JOIN productos_depositos USING(id_producto)) INNER JOIN items_entregas USING(id_producto_deposito)";
			$sql.= " WHERE id_entrega='" . $rowEntregas['id_entrega'] . "'";
			$sql.= " ORDER BY id_item_entrega";

			$rsItems = mysql_query($sql);
			while ($rowItems = mysql_fetch_array($rsItems)) {
				?>
				<tr>
				<td><?php echo $rowItems['descripcion']; ?></td>
				<td><?php echo $rowItems['tipo_producto']; ?></td>
				<td align="right"><?php echo $rowItems['cantidad']; ?></td>
				</tr>
				<?php
			}
			?>
			</table>
			</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td>&nbsp;</td></tr>
			<?php
		}
		?>
		</table>
		</body>
		</html>
		<?php
break;
}


case "consulta_combinada" : {
	$xml = new SimpleXMLElement(urldecode($_REQUEST['model']));
	
	if ($xml->rbt0['seleccionado']=="true") {
		$sql = "SELECT DISTINCTROW _personas.persona_id, _personas.persona_tipodoc, _personas.persona_dni, _personas.persona_nombre, _personas.persona_domicilio, _personas.persona_fecha_nacimiento, _personas.persona_sexo, pacientes.id_personal, pacientes.fecha_ingreso, pacientes.estado, localidad, tipos_pacientes.tipo_paciente AS tipopaciente";
		$sql.= " FROM (((salud1._personas LEFT JOIN salud1._localidades USING(localidad_id)) INNER JOIN pacientes USING (persona_id)) LEFT JOIN pacientes_productos USING (id_paciente)) INNER JOIN tipos_pacientes USING(id_tipo_paciente)";
		$sql.= " WHERE TRUE";
		
		$where = "";
		
		if ($xml->rbt0->medico['seleccionado']=="true") {
			$aux = array(); 
			foreach ($xml->rbt0->medico->row as $row) {
				$aux[] = "'" . $row['id'] . "'";
			}
			$aux = implode(", ", $aux);
			$where.= " AND pacientes.id_personal IN (" . $aux . ")";
		}
		
		if ($xml->rbt0->producto['seleccionado']=="true") {
			$aux = array(); 
			foreach ($xml->rbt0->producto->row as $row) {
				$aux[] = "'" . $row['id'] . "'";
			}
			$aux = implode(", ", $aux);
			$where.= " AND (pacientes_productos.id_producto IN (" . $aux . ") AND pacientes_productos.estado = 'A')";
		}
		
		if ($xml->rbt0->tipo_paciente['seleccionado']=="true") {
			$where.= " AND pacientes.id_tipo_paciente=" . $xml->rbt0->tipo_paciente['id_tipo_paciente'];
		}
		
		if ($xml->rbt0->fecha_ingreso['seleccionado']=="true") {
			$where.= " AND (pacientes.fecha_ingreso BETWEEN '" . $xml->rbt0->fecha_ingreso['desde'] . "' AND '" . $xml->rbt0->fecha_ingreso['hasta'] . "')";
		}
		
		$sql.= $where;
		$sql.= " ORDER BY _personas.persona_nombre";
		
		$rs = mysql_query($sql);
		
		$a = array();
		$a['D'] = "D.N.I.";
		$a['C'] = "Lib.Cívica";
		$a['E'] = "Lib.Enrolamiento";
		$a['P'] = "Pasaporte";
		$a['F'] = "Cédula";
		
		?>
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
		<head>
			<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
			<title>Consultas combinadas - Consulta pacientes</title>
		</head>
		<body>
		<input type="submit" value="Imprimir" onClick="window.print();"/>
		<table border="0" cellpadding=0 cellspacing=0 width=750 height=1% align="center">
		<tr><td align="center" colspan="6"><big>Consultas combinadas - Consulta pacientes</big></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td colspan="10"><?php echo htmlspecialchars_decode($xml->rbt0['observa']); ?></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>Cantidad: <?php echo mysql_num_rows($rs); ?></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td colspan="10"><hr></td></tr>
		<?php
		while ($row = mysql_fetch_array($rs)) {
			$row['tipodoc'] = $a[$row['persona_tipodoc']];
			$sql = "SELECT apenom FROM salud1._personal WHERE id_personal='" . $row['id_personal'] . "'";
			$rsMedico = mysql_query($sql);
			$rowMedico = mysql_fetch_array($rsMedico);
			$row['medico'] = $rowMedico['apenom'];
			$row['estado'] = ($row['estado'] == "A") ? "Activo" : "Inactivo";
		?>
		<tr><td colspan="20">
		<table border="0" rules="none" cellpadding=0 cellspacing=0 width="99%" height=1% align="center">
		<tr><td colspan="3">Nombre: <?php echo $row['persona_nombre']; ?></td><td>T.Pac.: <?php echo $row['tipopaciente']; ?></td></tr>
		<tr><td>T.Doc.: <?php echo $row['tipodoc']; ?></td><td>Nro.Doc.: <?php echo $row['persona_dni']; ?></td><td>F.nac.: <?php echo $row['persona_fecha_nacimiento']; ?></td><td>Estado: <?php echo $row['estado']; ?></td></tr>
		<tr><td colspan="10">Domicilio: <?php echo $row['persona_domicilio']; ?></td></tr>
		<tr><td colspan="10">Localidad: <?php echo $row['localidad']; ?></td></tr>
		<tr><td colspan="3">Medico: <?php echo $row['medico']; ?></td><td>F.Ing.: <?php echo $row['fecha_ingreso']; ?></td></tr>
		<tr><td colspan="10">Trat.realiz.: <?php echo $row['tratamientos_realizados']; ?></td></tr>
		<tr><td colspan="10"><hr></td></tr>
		</table>
		</td></tr>
		<?php
		}
		?>
		</table>
		</body>
		</html>
		<?php
	}
break;
}


case "entregas_por_producto" : {
	$sql = "SELECT productos.id_producto, productos.descripcion AS producto, SUM(items_entregas.cantidad) AS cantidad";
	$sql.= " FROM ((entregas INNER JOIN items_entregas USING(id_entrega)) INNER JOIN productos_depositos USING(id_producto_deposito)) INNER JOIN productos USING(id_producto)";
	$sql.= " WHERE entregas.estado <> 'X' AND (DATE(entregas.fecha) BETWEEN '" . $_REQUEST['desde'] . "' AND '" . $_REQUEST['hasta'] . "')";
	$sql.= " GROUP BY id_producto";
	$sql.= " ORDER BY producto";
	
	$rs = mysql_query($sql);
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Listado entregas x producto</title>
	</head>
	<body>
	<table border="0" cellpadding=0 cellspacing=0 width=550 height=1% align="center">
	<tr><td align="center" colspan="6"><big>LISTADO ENTREGAS x PRODUCTO - <?php echo date('Y-m-d') ?></big></td></tr>
	<tr><td align="center" colspan="6"><big>ENTRE <?php echo $_REQUEST['desde'] . " / " . $_REQUEST['hasta'] ?></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	
	<tr><td colspan="20">
	<table border="1" rules="all" cellpadding=0 cellspacing=0 width="99%" height=1% align="center">
	<tr><td>Producto</td><td align="right">Cant.</td></tr>
	<?php
	while ($row = mysql_fetch_array($rs)) {
		?>
		<tr>
		<td><?php echo $row['producto']; ?></td>
		<td align="right"><?php echo $row['cantidad']; ?></td>
		</tr>
		<?php
	}
	?>
	</table>
	</td></tr>
	</table>
	</body>
	</html>
	<?php
	
break;
}


case "entregas_a_pacientes" : {
	
	$sql = "SELECT entregas.id_entrega, entregas.fecha, entregas.estado, _personas.persona_nombre, _personas.persona_dni, productos.descripcion AS producto, items_entregas.cantidad";
	$sql.= " FROM ((((entregas INNER JOIN pacientes USING(id_paciente)) INNER JOIN salud1._personas USING(persona_id)) INNER JOIN items_entregas USING(id_entrega)) INNER JOIN productos_depositos USING(id_producto_deposito)) INNER JOIN productos USING(id_producto)";
	$sql.= " WHERE entregas.estado <> 'X' AND (DATE(entregas.fecha) BETWEEN '" . $_REQUEST['desde'] . "' AND '" . $_REQUEST['hasta'] . "')";
	if ($_REQUEST['en_particular']=="true") $sql.= " AND pacientes.id_paciente=" . $_REQUEST['id_paciente'];
	if (! is_null($_REQUEST['id_financiador'])) {
		if ($_REQUEST['id_financiador'] == "NULL") {
			$sql.= " AND ISNULL(entregas.id_financiador)";
		} else {
			$sql.= " AND entregas.id_financiador=" . $_REQUEST['id_financiador'];
		}
	}
	$sql.= " ORDER BY persona_nombre, fecha, id_entrega";
	
	$rs = mysql_query($sql);
	$id_entrega = "0";
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Listado entregas a pacientes</title>
	</head>
	<body>
	<table border="0" cellpadding=0 cellspacing=0 width=950 height=1% align="center">
	<tr><td align="center" colspan="6"><big>LISTADO ENTREGAS A PACIENTES <?php echo ($_REQUEST['en_particular']=="true") ? " EN PARTICULAR - " : " EN GRAL. - "; echo date('Y-m-d') ?></big></td></tr>
	<tr><td align="center" colspan="6"><big>ENTRE <?php echo $_REQUEST['desde'] . " / " . $_REQUEST['hasta'] ?></big></td></tr>
	<?php
	if (! is_null($_REQUEST['id_financiador'])) {
		if ($_REQUEST['id_financiador'] == "NULL") {
			$rowFinanciadores = array();
			$rowFinanciadores['nombre'] = "sin OS";
		} else {
			$rsFinanciadores = mysql_query("SELECT CONCAT(siglas, ', ', nombre) AS nombre FROM suram.financiadores WHERE id_financiador=" . $_REQUEST['id_financiador']);
			$rowFinanciadores = mysql_fetch_array($rsFinanciadores);
		}
	?>
		<tr><td align="center" colspan="6"><big>O.SOCIAL:  <?php echo $rowFinanciadores['nombre'] ?></big></td></tr>
	<?php
	};
	?>
	<tr><td>&nbsp;</td></tr>
	
	<tr><td colspan="20">
	<table border="1" rules="all" cellpadding=0 cellspacing=0 width="99%" height=1% align="center">
	<tr><td>Paciente</td><td>Nro.doc.</td><td align="center">Fecha</td><td align="center">Est.</td><td>Producto</td><td align="right">Cant.</td></tr>
	<?php
	while ($row = mysql_fetch_array($rs)) {
		if ($row['id_entrega']==$id_entrega) {
			?>
			<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td><?php echo $row['producto']; ?></td>
			<td align="right"><?php echo $row['cantidad']; ?></td>
			</tr>
			<?php
		} else {
			$id_entrega = $row['id_entrega'];
			?>
			<tr>
			<td><?php echo trim($row['persona_nombre']); ?></td>
			<td><?php echo $row['persona_dni']; ?></td>
			<td align="center"><?php echo $row['fecha']; ?></td>
			<td align="center"><?php echo $row['estado']; ?></td>
			<td><?php echo $row['producto']; ?></td>
			<td align="right"><?php echo $row['cantidad']; ?></td>
			</tr>
			<?php
		}
	}
	?>
	</table>
	</td></tr>
	</table>
	</body>
	</html>
	<?php

break;	
}


case "imprimir_pacientes" : {
	$sql = "SELECT _personas.persona_id, _personas.persona_tipodoc, _personas.persona_dni, _personas.persona_nombre, _personas.persona_domicilio, _personas.persona_fecha_nacimiento, _personas.persona_sexo, pacientes.id_personal, localidad, departamento, pacientes.tratamientos_realizados, pacientes.estado";
	$sql.= " FROM (((salud1._personas LEFT JOIN salud1._localidades USING(localidad_id)) LEFT JOIN salud1._departamentos USING(departamento_id)) INNER JOIN pacientes USING (persona_id))";
	
	$titulo = "LISTADO DE PACIENTES";
	if ($_REQUEST['estado']=="A") {
		$sql.= " WHERE pacientes.estado='A'";
		$titulo.= " ACTIVOS";
	} else if ($_REQUEST['estado']=="I") {
		$sql.= " WHERE pacientes.estado='I'";
		$titulo.= " INACTIVOS";
	}
	$sql.= " ORDER BY _personas.persona_nombre";
	
	$rs = mysql_query($sql);
	
	$a = array();
	$a['D'] = "D.N.I.";
	$a['C'] = "Lib.Cívica";
	$a['E'] = "Lib.Enrolamiento";
	$a['P'] = "Pasaporte";
	$a['F'] = "Cédula";
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Listado de pacientes</title>
	</head>
	<body>
	<table border="0" cellpadding=0 cellspacing=0 width=750 height=1% align="center">
	<tr><td align="center" colspan="6"><big><?php echo $titulo; ?></big></td></tr>
	<tr><td>Cantidad: <?php echo mysql_num_rows($rs); ?></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td colspan="10"><hr></td></tr>
	<?php
	while ($row = mysql_fetch_array($rs)) {
		$row['tipodoc'] = $a[$row['persona_tipodoc']];
		$sql = "SELECT apenom FROM salud1._personal WHERE id_personal='" . $row['id_personal'] . "'";
		$rsMedico = mysql_query($sql);
		$rowMedico = mysql_fetch_array($rsMedico);
		$row['medico'] = $rowMedico['apenom'];
		$row['estado'] = ($row['estado'] == "A") ? "Activo" : "Inactivo";
	?>
	<tr><td colspan="20">
	<table border="0" rules="none" cellpadding=0 cellspacing=0 width="99%" height=1% align="center">
	<tr><td colspan="10">Nombre: <?php echo $row['persona_nombre']; ?></td></tr>
	<tr><td>T.Doc.: <?php echo $row['tipodoc']; ?></td><td>Nro.Doc.: <?php echo $row['persona_dni']; ?></td><td>F.nac.: <?php echo $row['persona_fecha_nacimiento']; ?></td><td>Estado: <?php echo $row['estado']; ?></td></tr>
	<tr><td colspan="10">Domicilio: <?php echo $row['persona_domicilio']; ?></td></tr>
	<tr><td colspan="10">Localidad: <?php echo $row['localidad'] . " (" . $row['departamento'] . ")"; ?></td></tr>
	<tr><td colspan="10">Medico: <?php echo $row['medico']; ?></td></tr>
	<tr><td colspan="10">Trat.realiz.: <?php echo $row['tratamientos_realizados']; ?></td></tr>
	<tr><td colspan="10"><hr></td></tr>
	</table>
	</td></tr>
	<?php
	}
	?>
	</table>
	</body>
	</html>
	<?php
		
break;
}

}

?>