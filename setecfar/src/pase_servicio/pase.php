<?php
include("../control_acceso_flex.php");
include("../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case 'traer_combos': {
		$xml=new SimpleXMLElement('<rows/>');
		$id_area = $_SESSION['usuario_organismo_area_id'];

		$sql="SELECT id_organismo_area_servicio 'id_area_servicio', denominacion ";
		$sql.="FROM $salud._organismos_areas_servicios oas ";
		$sql.="INNER JOIN $salud._servicios USING(id_servicio) ";
		$sql.="WHERE oas.id_organismo_area='$id_area' ";
		$sql.="ORDER BY denominacion";
		toXML($xml, $sql, "servicio");
		
		$sql="SELECT p.persona_nombre, pl.id_personal ";
		$sql.="FROM $salud._organismos_areas_servicios oas ";
		$sql.="INNER JOIN $salud._personal pl ON pl.id_areas_servicios = oas.id_servicio ";
		$sql.="INNER JOIN $salud._personas p ON p.persona_id = pl.id_persona ";
		$sql.="WHERE pl.asistencial = 'S' ";
		$sql.="AND oas.id_organismo_area = '".$_SESSION['usuario_organismo_area_id']."' ";
		toXML($xml, $sql, "medico");
		
		header('Content-Type: text/xml');
		echo $xml->asXML();
		break;
	}
	case "guardar":{
		$fecha_pase = YYYYDM($fecha_pase);
		$xml=new SimpleXMLElement('<rows/>');
		
		$sql="UPDATE ingresos_movimientos ";
		$sql.="SET fecha_movimiento_egreso='$fecha_pase', ";
		$sql.="id_area_servicio_egreso='$id_servicio', ";
		$sql.="id_recursos_humanos_egreso='$id_personal', ";
		$sql.="observaciones='$observaciones' ";
		$sql.="WHERE id_ingreso_movimiento='$id_ingreso_movimiento' ";
		toXML($xml, $sql, "pase");
				
		header('Content-Type: text/xml');
		echo $xml->asXML();
		break;
	}
}
?>