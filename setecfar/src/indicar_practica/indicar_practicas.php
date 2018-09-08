<?php
include("../control_acceso_flex.php");
include("../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case 'insert': 
	{
		$_REQUEST["xmlPractica"] = str_replace('\"','"',$_REQUEST["xmlPractica"]);
		$xml_practica = loadXML($_REQUEST["xmlPractica"]);	
		
		$fecha_solicitud = YYYYDM($xml_practica["fecha_solicitud"]);
		$xml=new SimpleXMLElement('<rows/>');
		
		$sql="INSERT solicitudes ";
		$sql.="SET id_ingreso_movimiento_solicitud='".$xml_practica["id_ingreso_movimiento_solicitud"]."', ";
		$sql.="id_practica='".$xml_practica["id_practica"]."', ";
		$sql.="resultados='".$xml_practica["resultados"]."', ";
		$sql.="fecha_solicitud='$fecha_solicitud' ";
		toXML($xml, $sql, "add");
				
		header('Content-Type: text/xml');
		echo $xml->asXML();
		break;
	}
	case 'update': 
	{
		$_REQUEST["xmlPractica"] = str_replace('\"','"',$_REQUEST["xmlPractica"]);
		$xml_practica = loadXML($_REQUEST["xmlPractica"]);	
		
		$fecha_solicitud = YYYYDM($xml_practica["fecha_solicitud"]);
		$xml=new SimpleXMLElement('<rows/>');
		
		$sql="UPDATE solicitudes ";
		$sql.="SET id_practica='".$xml_practica["id_practica"]."', ";
		$sql.="resultados='".$xml_practica["resultados"]."', ";
		$sql.="fecha_solicitud='$fecha_solicitud' ";
		$sql.="WHERE id_solicitudes='".$xml_practica["id_solicitudes"]."' ";
		toXML($xml, $sql, "upd");
				
		header('Content-Type: text/xml');
		echo $xml->asXML();
		break;
	}
	case 'delete': 
	{
		$_REQUEST["xmlPractica"] = str_replace('\"','"',$_REQUEST["xmlPractica"]);
		$xml_practica = loadXML($_REQUEST["xmlPractica"]);	
		
		$xml=new SimpleXMLElement('<rows/>');
		
		$sql="DELETE FROM solicitudes ";
		$sql.="WHERE id_solicitudes='".$xml_practica["id_solicitudes"]."' ";
		toXML($xml, $sql, "del");
				
		header('Content-Type: text/xml');
		echo $xml->asXML();
		break;
	}
}

?>