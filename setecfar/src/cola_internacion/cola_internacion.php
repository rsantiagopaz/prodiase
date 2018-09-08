<?php
include("../control_acceso_flex.php");
include("../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case 'lista_pacientes': {

		$xml=new SimpleXMLElement('<rows/>');
		$sql="SELECT i.id_ingreso, im.id_ingreso_movimiento, p.persona_nombre 'apeynom', ";
		$sql.="CASE p.persona_tipodoc WHEN 'D' THEN 'DNI' WHEN 'C' THEN 'LC' WHEN 'E' THEN 'LE' WHEN 'F' THEN 'CI' END as 'tipo_doc',p.persona_dni 'nrodoc', DATE_FORMAT(fecha_movimiento_ingreso,'%d/%m/%Y') 'fecha_ingreso' ";
		$sql.="FROM $suram.ingresos_movimientos im ";
		$sql.="INNER JOIN $suram.ingresos i USING(id_ingreso) ";
		$sql.="INNER JOIN $salud._personas p ON i.id_persona=p.persona_id ";
		$sql.="WHERE id_area_servicio_ingreso='".$_SESSION['usuario_servicio_id']."' ";
		$sql.="AND (ISNULL(i.id_tipo_egreso) AND (ISNULL(im.id_area_servicio_egreso))) ";
		$sql.="AND i.id_nivel='2' ";
		$sql.="AND p.persona_nombre LIKE '%$filtro_pacientes%' ";
		$sql.="ORDER BY im.fecha_movimiento_ingreso";
		
		toXML($xml, $sql, "pacientes");
		header('Content-Type: text/xml');
		echo $xml->asXML();
	
		break;
	}
}
?>