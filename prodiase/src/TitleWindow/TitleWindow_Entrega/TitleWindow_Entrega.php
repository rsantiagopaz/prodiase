<?php
include("../../control_acceso_flex.php");
//include("../../conexion.php");
include("../../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "leer_stock" : {
		$xml = new SimpleXMLElement('<tratamientos/>');
		
		$sql = "SELECT id_paciente_producto, productos.descripcion, tipos_productos.tipo_producto, dosis_diaria, productos_depositos.*, '0' AS cantidad";
		$sql.= " FROM ((pacientes_productos INNER JOIN productos USING(id_producto)) INNER JOIN tipos_productos USING(id_tipo_producto)) INNER JOIN productos_depositos USING(id_producto)";
		$sql.= " WHERE id_paciente='" . $_REQUEST['id_paciente'] . "' AND pacientes_productos.estado='A' AND id_deposito='" . $_REQUEST['id_deposito'] . "'";
		
		toXML($xml, $sql, "tratamiento");
		
		header('Content-Type: text/xml');
		echo $xml->asXML();
		
		break;
	}
}

?>