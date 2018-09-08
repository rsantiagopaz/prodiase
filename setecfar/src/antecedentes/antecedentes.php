<?php
include("../control_acceso_flex.php");
include("../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case 'traer_antecedentes': {

		$xml=new SimpleXMLElement('<rows/>');
		
		$query = "SELECT id_persona FROM ingresos WHERE id_ingreso = '$id_ingreso'";
		
		$result = mysql_query($query);
		
		if ($row = mysql_fetch_array($result)){						
			$id_persona = $row['id_persona'];
			
			$sql="SELECT a.id_antecedente_ingresos, id_antecedente, antecedente, observaciones, DATE_FORMAT(fecha,'%d/%m/%y') as fecha, accion ";
			$sql.="FROM antecedentes_pacientes a ";
			$sql.="JOIN antecedentes USING(id_antecedente) ";
			$sql.="WHERE id_persona='$id_persona' ";
			$sql.="ORDER BY fecha";
		}
		
		
		toXML($xml, $sql, "antecedente");
		header('Content-Type: text/xml');
		echo $xml->asXML();
	
		break;
	}
	case 'guardar':{		
		break;
	}
	case 'insert': 
	{
		$_REQUEST["xmlAntecedente"] = str_replace('\"','"',$_REQUEST["xmlAntecedente"]);
		$xml_Antecedente = loadXML($_REQUEST["xmlAntecedente"]);
		
		$query = "SELECT id_persona FROM $suram.ingresos INNER JOIN $suram.ingresos_movimientos im USING(id_ingreso) ";
		$query.="WHERE im.id_ingreso_movimiento='".$xml_Antecedente["id_ingreso_movimiento"]."' ";
		$result = mysql_query($query);
		
		$row = mysql_fetch_array($result);
		
		$id_persona = $row['id_persona'];	
		
		$fecha = YYYYDM($xml_Antecedente["fecha"]);
		
		if ($xml_Antecedente["accion"] == 'Alta') {
			$accion = 'A';
		} elseif ($xml_Antecedente["accion"] == 'Modificación') {
			$accion = 'M';
		} else {
			$accion = 'B';
		}
		
		$xml=new SimpleXMLElement('<rows/>');
		
		$sql="INSERT $salud.027_antecedentes_personas ";
		$sql.="SET id_antecedente='".$xml_Antecedente["id_antecedente"]."', ";
		$sql.="observaciones='".$xml_Antecedente["observaciones"]."', ";
		$sql.="id_persona='".$id_persona."', ";
		$sql.="usuario='".$SYSusuario."', ";
		$sql.="accion='".$accion."', ";
		$sql.="fecha='$fecha' ";
		toXML($xml, $sql, "add");
				
		header('Content-Type: text/xml');
		echo $xml->asXML();
		break;
	}
	case 'update': 
	{
		$_REQUEST["xmlAntecedente"] = str_replace('\"','"',$_REQUEST["xmlAntecedente"]);
		$xml_Vacunacion = loadXML($_REQUEST["xmlAntecedente"]);	
		
		$fecha_Vacunacion = YYYYDM($xml_Vacunacion["fecha_Vacunacion"]);
		$xml=new SimpleXMLElement('<rows/>');
		
		$sql="UPDATE $salud.027_vacunaciones ";
		$sql.="SET id_dosis='".$xml_Vacunacion["id_dosis"]."', ";
		$sql.="id_persona='".$id_persona."', ";		
		$sql.="id_fecha='$fecha' ";
		$sql.="WHERE id_vacunacion='".$xml_Vacunacion["id_vacunacion"]."' ";
		toXML($xml, $sql, "upd");
				
		header('Content-Type: text/xml');
		echo $xml->asXML();
		break;
	}
	case 'delete': 
	{
		$_REQUEST["xmlAntecedente"] = str_replace('\"','"',$_REQUEST["xmlAntecedente"]);
		$xml_Vacunacion = loadXML($_REQUEST["xmlVacunacion"]);	
		
		
		$xml=new SimpleXMLElement('<rows/>');
		
		$sql="DELETE FROM $salud.027_vacunaciones ";
		$sql.="WHERE id_vacunacion='".$xml_Vacunacion["id_vacunacion"]."' ";
		toXML($xml, $sql, "del");
				
		header('Content-Type: text/xml');
		echo $xml->asXML();
		break;
	}
}
?>