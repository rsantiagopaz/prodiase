<?php
include("../control_acceso_flex.php");
include("../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "traer_practicas":{
		$xml=new SimpleXMLElement('<rows/>');
		$sql= "SELECT id_practica, procedimiento 'descripcion' ";
		$sql.= "FROM nomenclador_practicas ";
		$sql.= "WHERE procedimiento LIKE '%$descripcion%' ";
		$sql.= "ORDER BY procedimiento ";
		
		$SELECT = mysql_query($sql);
		toXML($xml, $sql, "practica");
		header('Content-Type: text/xml');
		echo $xml->asXML();

		break;
	}
}
?>