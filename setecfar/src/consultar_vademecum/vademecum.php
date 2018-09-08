<?php
include("../control_acceso_flex.php");
include("../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "traer_vademecum":{
		$xml=new SimpleXMLElement('<rows/>');
		$sql= "SELECT monodroga, presentacion, concentracion ";
		$sql.= "FROM vademecum ";
		$sql.= "WHERE monodroga LIKE '%$filter%' ";
		$sql.= "ORDER BY monodroga ";
		
		$SELECT = mysql_query($sql);
		toXML($xml, $sql, "vademecum");
		header('Content-Type: text/xml');
		echo $xml->asXML();

		break;
	}
}
?>