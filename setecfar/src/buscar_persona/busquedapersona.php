<?php
include("../control_acceso_flex.php");
include("../rutinas.php");

switch ($_REQUEST['rutina'])
{
	case "buscar_persona":{
		$xml=new SimpleXMLElement('<rows/>');
		
		$query = "SELECT persona_id FROM $salud._personas WHERE persona_tipodoc = '$tipo_doc' AND persona_dni = '$nro_doc'";
				
		$result = mysql_query($query);
		
		if ($row = mysql_fetch_array($result)) {						
			$id_persona = $row['persona_id'];
		
			// Traer los datos de la persona aunque no estén los datos de localidad (FIJARSE EN EL JOIN))
			$sql="SELECT persona_id 'id_persona',persona_nombre 'apeynom', persona_tipodoc 'tipo_doc', persona_dni 'nrodoc', IF( persona_sexo = 'M', 'Masculino', 'Femenino' ) as 'sexo', ";
			$sql.="DATE_FORMAT(persona_fecha_nacimiento,'%d/%m/%Y') as 'fechanac', ";
			$sql.="(YEAR(CURRENT_DATE)-YEAR(persona_fecha_nacimiento))-(RIGHT(CURRENT_DATE,5)<RIGHT(persona_fecha_nacimiento,5))  'edad', persona_domicilio 'domicilio', localidad, departamento, provincias ";
			$sql.="FROM $salud._personas p ";			
			$sql.="LEFT JOIN $salud._localidades l ON p.localidad_id = l.localidad_id ";
			$sql.="LEFT JOIN $salud._departamentos d USING(departamento_id) ";
			$sql.="LEFT JOIN $salud._provincias pr ON d.provincia_id = pr.provincias_id ";
			$sql.="WHERE p.persona_id='$id_persona'";
			toXML($xml, $sql, "persona");
			
			/*$sql="SELECT f.id_financiador, f.nombre, f.codigo_anssal, tf.descripcion ";
			$sql.="FROM personas p, ";		
			$sql.="padrones pa, ";
			$sql.="financiadores f, ";
			$sql.="tipos_financiadores tf ";
			$sql.="WHERE p.id_persona='$id_persona' ";
			$sql.="AND tf.id_tipo_financiador=f.id_tipo_financiador ";
			$sql.="AND f.id_financiador=pa.id_financiador ";
			$sql.="AND (p.nrodoc=pa.nrodoc AND p.tipo_doc=pa.tipo_doc) ";
			$sql.="ORDER BY nombre";
			toXML($xml, $sql, "cobertura");*/
			
			$sql="SELECT id_ingreso, motivo, observaciones, ";			
			$sql.="DATE_FORMAT(fecha_consulta_ingreso,'%d/%m/%Y') 'fecha', ";
			$sql.="IF (organismo_area_id = '".$_SESSION['usuario_organismo_area_id']."','pase_interno','pase_externo') as pase, ";
			$sql.="IF (id_tipo_egreso IS NULL,'abierto','cerrado') 'estado', id_establecimiento_desde ";
			$sql.="FROM ingresos ";			
			$sql.="WHERE id_persona='$id_persona' ";
			$sql.="ORDER BY fecha_consulta_ingreso DESC LIMIT 1";
			
			toXML($xml, $sql, "ingresos");					
			
			$result = mysql_query($sql);
			
			$nodo=$xml->addChild("ingresos2");
			
			if ($row = mysql_fetch_array($result)) {
				$id_ingreso = $row['id_ingreso'];
				
				$nodo2=$nodo->addChild("item_ingreso");				
				foreach($row as $key => $value) {						 
					if (!is_numeric($key)) $nodo2->addAttribute($key, $value);
				}          		
           		
           		$sql="SELECT id_ingreso_movimiento, ";
				$sql.="DATE_FORMAT(fecha_movimiento_ingreso,'%d/%m/%Y') 'fecha_movimiento_ingreso', ";
				$sql.="id_area_servicio_ingreso, ";
				$sql.="fecha_movimiento_egreso, id_area_servicio_egreso ";
				$sql.="FROM ingresos_movimientos ";			
				$sql.="WHERE id_ingreso='$id_ingreso' ";
				$sql.="ORDER BY fecha_movimiento_ingreso DESC LIMIT 1";
				
				$result2 = mysql_query($sql);
				
				while($row2 = mysql_fetch_array($result2))
				{
					$nodo3=$nodo2->addChild("servicio");				
					foreach($row2 as $key => $value) {						 
						if (!is_numeric($key)) $nodo3->addAttribute($key, $value);
					}	
				}
			}
		}
		
		$sql="SELECT f.id_financiador, f.nombre, f.codigo_anssal, tf.descripcion ";
		$sql.="FROM padrones pa, ";		
		$sql.="financiadores f, ";
		$sql.="tipos_financiadores tf ";
		$sql.="WHERE pa.nrodoc='$nro_doc' ";
		$sql.="AND tf.id_tipo_financiador=f.id_tipo_financiador ";
		$sql.="AND f.id_financiador=pa.id_financiador ";		
		$sql.="ORDER BY nombre";
		toXML($xml, $sql, "cobertura");
						
		header('Content-Type: text/xml');
		echo $xml->asXML();

		break;
	}
}
?>