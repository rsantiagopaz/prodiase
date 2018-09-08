<?php
include("../control_acceso_flex.php");
include("../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case 'traer_combos': {
		$xml=new SimpleXMLElement('<rows/>');
		
		$sql="SELECT * ";
		$sql.="FROM tipos_egresos ";
		$sql.="WHERE id_tipo_egreso <> '5' ";
		$sql.="ORDER BY descripcion";
		toXML($xml, $sql, "tegreso");
		
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
	case "traer_establecimientos":{
		$xml=new SimpleXMLElement('<rows/>');
		
		$sql="SELECT organismo_area_id 'id_establecimiento', organismo_area 'descripcion'";
		$sql.="FROM $salud._organismos_areas ";
		$sql.="WHERE organismo_area_tipo_id='E' ";
		$sql.="AND organismo_area_estado='1' ";
		$sql.= "AND organismo_area LIKE '%$descripcion%' ";
		$sql.="ORDER BY organismo_area";
		toXML($xml, $sql, "establecimiento");
		
		header('Content-Type: text/xml');
		echo $xml->asXML();
		break;
	}
	case "guardar":{
		$fecha_egreso = YYYYDM($fecha_egreso);
			
		$xml=new SimpleXMLElement('<rows/>');
		
		$sql="UPDATE ingresos_movimientos ";
		$sql.="SET fecha_movimiento_egreso='$fecha_egreso', ";
		$sql.="id_recursos_humanos_egreso='$id_personal', ";
		$sql.="observaciones='$observaciones' ";
		$sql.="WHERE id_ingreso_movimiento='$id_ingreso_movimiento' ";
		toXML($xml, $sql, "egreso");
		
		$sql="SELECT id_ingreso ";
		$sql.="FROM ingresos_movimientos ";
		$sql.="WHERE id_ingreso_movimiento='$id_ingreso_movimiento' ";
		$row = mysql_query($sql);
		$rs = mysql_fetch_array($row);
		$id_ingreso = $rs['id_ingreso'];
			
		//pregunto si se trata de un traslado
		if ($id_tipo_egreso=="2"){
			$sql="UPDATE ingresos ";
			$sql.="SET id_tipo_egreso='$id_tipo_egreso', ";
			$sql.="id_establecimiento_hacia='$id_establecimiento' ";
			$sql.="WHERE id_ingreso='$id_ingreso' ";
			toXML($xml, $sql, "egreso1");
		}else{
			$sql="UPDATE ingresos ";
			$sql.="SET id_tipo_egreso='$id_tipo_egreso' ";
			$sql.="WHERE id_ingreso='$id_ingreso' ";
			toXML($xml, $sql, "egreso1");
		}
		
		header('Content-Type: text/xml');
		echo $xml->asXML();
		break;
	}
	
		
}
?>