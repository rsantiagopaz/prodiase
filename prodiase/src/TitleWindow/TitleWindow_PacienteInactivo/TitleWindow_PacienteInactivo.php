<?php

include("../../control_acceso_flex.php");
//include("../../conexion.php");
include("../../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "imprimir" : {
		
		$xml = new SimpleXMLElement($_SESSION['pasar_inactivo']);
		
		?>
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
		<head>
			<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
			<title>Listado pacientes sin entregas</title>
		</head>
		<body>
		<input type="submit" value="Imprimir" onClick="window.print();"/>
		<table border="0" cellpadding="0" cellspacing="0" width="800" align="center">
		<tr><td align="center" colspan="6"><big><b>Dirección General del Programa Provincial de Atención</b></big></td></tr>
		<tr><td align="center" colspan="6"><big><b>y Prevención de la Diabetes</b></big></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td align="center" colspan="6"><big><b>Ministerio de Salud y Desarrollo Social</b></big></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td align="center" colspan="6"><big><b>LISTADO PACIENTES SIN ENTREGAS</b></big></td></tr>
		<tr><td align="center" colspan="6"><big><?php echo date("Y-m-d H:i:s"); ?></big></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>Desde: <?php echo $_REQUEST['fecha']; ?></td><td>Cantidad seleccionada: <?php echo count($xml->item); ?></td></tr>
		<tr><td>&nbsp;</td></tr>
		
		<tr>
		<td colSpan="10">
		<table border="1" cellpadding="5" cellspacing="0" width="100%" align="center">
		<thead>
		<tr><th>Nombre</th><th>Nro.doc.</th><th>Ingreso</th><th>Ult.entrega</th></tr>
		</thead>
		<tbody>
	
		<?php
		
		
		foreach ($xml->item as $item) {
			?>
			<tr><td><?php echo $item['persona_nombre']; ?></td><td><?php echo $item['persona_dni']; ?></td><td><?php echo $item['fecha_ingreso']; ?></td><td><?php echo $item['fecha']; ?></td></tr>
			<?php
		}
		
		unset($_SESSION['pasar_inactivo']);
		$_SESSION['pasar_inactivo'] = "<root/>";
		
	
		?>
		
		</tbody>
		</table>
		</td>
		</tr>
		
	
		</table>
		</body>
		</html>
		<?php
		
	break;
	}
	
	
	case "pasar_inactivo" : {
		
		$xml = new SimpleXMLElement($_SESSION['pasar_inactivo']);
		$aux = array();
		
		foreach ($xml->item as $item) {
			$aux[] = $item['id_paciente'];
		}

		$sql = "UPDATE pacientes SET estado='I', inactivo='O' WHERE id_paciente IN(" . implode(",", $aux) . ")";
		mysql_query($sql);
		
		unset($_SESSION['pasar_inactivo']);
		$_SESSION['pasar_inactivo'] = "<root/>";
		
		break;
	}
	
	
	case "guardar_inactivo" : {
		
		$_SESSION['pasar_inactivo'] = $_REQUEST['model'];
		
		break;
	}
	
	
	case "leer_paciente" : {
		
		unset($_SESSION['pasar_inactivo']);
		$_SESSION['pasar_inactivo'] = "<root/>";
		
		$xml = new SimpleXMLElement('<pacientes/>');
		
		$sql = "SELECT pacientes.id_paciente, pacientes.fecha_ingreso, _personas.persona_nombre, _personas.persona_dni";
		$sql.= " FROM pacientes INNER JOIN salud1._personas USING(persona_id)";
		$sql.= " WHERE pacientes.estado='A'";
		$sql.= " ORDER BY persona_nombre";
		
		$rsPaciente = mysql_query($sql);
		while ($rowPaciente = mysql_fetch_assoc($rsPaciente)) {
			
			$sql = "SELECT fecha, estado";
			$sql.= " FROM entregas";
			$sql.= " WHERE id_paciente=" . $rowPaciente['id_paciente'];
			$sql.= " ORDER BY id_entrega DESC";
			$sql.= " LIMIT 1";
			
			$rsEntrega = mysql_query($sql);
			if (mysql_num_rows($rsEntrega) > 0) {
				$rowEntrega = mysql_fetch_assoc($rsEntrega);
				if ($rowEntrega['fecha'] < $_REQUEST['fecha']) {
					$rowPaciente['fecha'] = $rowEntrega['fecha'];
					$rowPaciente['estado'] = $rowEntrega['estado'];
					
					toXML($xml, $rowPaciente, "paciente");
				}
			}
		}
		
		header('Content-Type: text/xml');
		echo $xml->asXML();
		
		break;
	}
}

?>