<?php
//extrae todo el arreglo Request y lo pone como variables
foreach ($_REQUEST as $key => $value)
{
 	if(get_magic_quotes_gpc()) {
    	${$key} = mysql_real_escape_string(stripslashes($value));
    } else {
        ${$key} = mysql_real_escape_string($value);
    }
}

function auditoria($sqltext, $descrip = null, $tags = null, $id_registro = null, $json = null) {
	$sql = "INSERT _auditoria SET SYSusuario='" . $GLOBALS["SYSusuario"] . "', _sessionid='" . $GLOBALS["_sessionid"] . "', tags='" . $tags . "', id_registro=" . ((is_null($id_registro)) ? "NULL" : $id_registro) . ", mysql_query='" . mysql_real_escape_string($sqltext) . "', mysql_descrip='" . $descrip . "', fecha_hora=NOW(), ip='" . getRealIP() . "', json=" . ((is_null($json)) ? "NULL" : "'" . $json . "'");
	mysql_query($sql);
}

function getRealIP() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
	return $_SERVER['REMOTE_ADDR'];
}

function loadXML($data) {
  $xml = @simplexml_load_string($data);
  if (!is_object($xml))
    throw new Exception('Error en la lectura del XML',1001);
  return $xml;
}

function toXML(&$xml, $paramet , $tag = "row") {
	if (is_string($paramet)) {
		$cadena = strtoupper(substr(trim($paramet), 0, 6));
		if ($cadena=="INSERT" || $cadena=="SELECT") {
			$paramet = @mysql_query($paramet);
			if (mysql_errno() > 0) {
			 	$error="Error devuelto por la Base de Datos: ".mysql_errno()." ".mysql_error()."\n";
			 	$nodo=$xml->addChild("error", $error);
			} else if ($cadena=="INSERT"){ 
				$nodo=$xml->addChild("insert_id", mysql_insert_id());
			} else {
				$nodo = toXML($xml, $paramet, $tag);
			}
		} else {
			$nodo=new SimpleXMLElement($paramet);
			$nodo=$xml->addChild($nodo->getName(), $nodo[0]);
		}
	} else if (is_resource($paramet)) {
		while ($row = mysql_fetch_array($paramet)) {
			$nodo=$xml->addChild($tag);	
			foreach($row as $key => $value) {
				if (!is_numeric($key)) $nodo->addAttribute($key, $value);
			}
		}
		$nodo=null;
	} else if (is_array($paramet)) {
		$nodo=$xml->addChild($tag);
		foreach($paramet as $key => $value) {
			if (!is_numeric($key)) $nodo->addAttribute($key, $value);
		}
	} else if (is_a($paramet, "SimpleXMLElement")) {
		$nodo=$xml->addChild($paramet->getName(), $paramet);
		foreach($paramet->attributes() as $key => $value) {
    		$nodo->addAttribute($key, $value);
		}
		foreach ($paramet->children() as $hijo) {
			toXML($nodo, $hijo);
		}
	}
	return $nodo;
}

function DMYYYY($fecha) {
	$f=explode("-", $fecha);
	return (int) $f[2] . "/" . (int) $f[1] . "/" . (int) $f[0];
}
function YYYYDM($fecha) {
	$fecha = explode('/',$fecha);
	$fecha = $fecha[2].'-'.$fecha[1].'-'.$fecha[0];
	
	return $fecha;
}
?>