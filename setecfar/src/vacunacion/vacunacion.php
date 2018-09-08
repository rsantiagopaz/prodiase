<?php
include("../control_acceso_flex.php");
include("../rutinas.php");

switch ($_REQUEST['rutina'])
{
	/*case 'traer_vacunacion': {

		$xml=new SimpleXMLElement('<rows/>');
		
		$query = "SELECT id_persona FROM ingresos WHERE id_ingreso = '$id_ingreso'";
		
		$result = mysql_query($query);
		
		if ($row = mysql_fetch_array($result)){						
			$id_persona = $row['id_persona'];
			
			$sql="SELECT id_vacunacion, id_dosis, CONCAT(nombre,' - dosis: ',denominacion) as nombre, enfermedades, DATE_FORMAT(fecha,'%d/%m/%y') as fecha ";
			$sql.="FROM vacunaciones ";
			$sql.="JOIN dosis USING(id_dosis) ";
			$sql.="JOIN vacunas USING(id_vacuna) ";
			$sql.="WHERE id_persona='$id_persona' ";
			$sql.="ORDER BY fecha";
		}
		
		
		toXML($xml, $sql, "vacunacion");
		header('Content-Type: text/xml');
		echo $xml->asXML();
	
		break;
	}*/
	case 'insert': 
	{
		$_REQUEST["xmlVacunacion"] = str_replace('\"','"',$_REQUEST["xmlVacunacion"]);
		$xml_Vacunacion = loadXML($_REQUEST["xmlVacunacion"]);
		
		$query = "SELECT id_persona FROM $suram.ingresos INNER JOIN $suram.ingresos_movimientos im USING(id_ingreso) ";
		$query.="WHERE im.id_ingreso_movimiento='".$xml_Vacunacion["id_ingreso_movimiento"]."' ";
		$result = mysql_query($query);
		
		$row = mysql_fetch_array($result);
		
		$id_persona = $row['id_persona'];	
		
		$fecha = YYYYDM($xml_Vacunacion["fecha"]);
		$xml=new SimpleXMLElement('<rows/>');
		
		$sql="INSERT $salud.027_vacunaciones ";
		$sql.="SET id_dosis='".$xml_Vacunacion["id_dosis"]."', ";
		$sql.="id_persona='".$id_persona."', ";		
		$sql.="id_fecha='$fecha' ";
		toXML($xml, $sql, "add");
				
		header('Content-Type: text/xml');
		echo $xml->asXML();
		break;
	}
	case 'update': 
	{
		$_REQUEST["xmlVacunacion"] = str_replace('\"','"',$_REQUEST["xmlVacunacion"]);
		$xml_Vacunacion = loadXML($_REQUEST["xmlVacunacion"]);
		
		$query = "SELECT id_persona FROM $suram.ingresos INNER JOIN $suram.ingresos_movimientos im USING(id_ingreso) ";
		$query.="WHERE im.id_ingreso_movimiento='".$xml_Vacunacion["id_ingreso_movimiento"]."' ";
		$result = mysql_query($query);
		
		$row = mysql_fetch_array($result);
		
		$id_persona = $row['id_persona'];
		
		$fecha = YYYYDM($xml_Vacunacion["fecha"]);
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
		$_REQUEST["xmlVacunacion"] = str_replace('\"','"',$_REQUEST["xmlVacunacion"]);
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