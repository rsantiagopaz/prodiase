<?php
include("../control_acceso_flex.php");
include("../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "traer_vacunas":{
		$vacuna = strtoupper($vacuna);
		$xml=new SimpleXMLElement('<rows/>');
		$sql= "SELECT id_dosis, CONCAT(nombre,' - dosis ',denominacion) as nombre, enfermedades ";		
		$sql.= "FROM $salud.027_vacunas JOIN $salud.027_dosis USING(id_vacuna) ";
		$sql.= "WHERE CONCAT(nombre,' - dosis ',denominacion) LIKE '%$vacuna%' ";
		$sql.= "ORDER BY nombre";
		
		$SELECT = mysql_query($sql);
		toXML($xml, $sql, "vacuna");
		header('Content-Type: text/xml');
		echo $xml->asXML();

		break;
	}
}
?>