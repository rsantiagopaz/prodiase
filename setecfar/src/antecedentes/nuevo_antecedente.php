<?php
include("../control_acceso_flex.php");
include("../rutinas.php");

function generoXmlOkErrores($_ok,$_errores,$otroNodoXml)
{	
	$xml = "<?xml version='1.0' encoding='UTF-8' ?>";
	$xml.= "<xml>";
	if(!empty($_ok)) {
		$xml.=  "<ok>$_ok</ok>";
	}
	if(!empty($_errores)) { 
		$xml.=  "<error>$_errores</error>";
	}	
	$xml.=$otroNodoXml;	
	$xml.= "</xml>";
	header('Content-Type: text/xml');
	print $xml;
}

switch ($_REQUEST['rutina'])
{
	case "traer_antecedentes":{
		$antecedente = strtoupper($antecedente);
		$xml=new SimpleXMLElement('<rows/>');
		$sql= "SELECT id_antecedente, antecedente ";		
		$sql.= "FROM antecedentes ";
		$sql.= "WHERE antecedente LIKE '%$antecedente%' ";
		$sql.= "ORDER BY antecedente ";
		
		$SELECT = mysql_query($sql);
		toXML($xml, $sql, "antecedente");
		header('Content-Type: text/xml');
		echo $xml->asXML();

		break;
	}
	case "armarXmlTipoAntec":{
		global $salud;
	    $errores="";	
	    $xmlDpto= "<tiposantecedentes>";
	    $query = "SELECT * FROM $salud.027_tipo_antec ORDER BY descripcion";
	    $result = mysql_query($query);
	    if (mysql_errno() > 0) {
	    	$errores.="Error devuelto por la Base de Datos: ".mysql_errno()." ".mysql_error()."\n";
	    } else {          
		    $xmlDpto.= "<tipoantecedente>";
		    $xmlDpto.= "<id_tipo_antec></id_tipo_antec>";
		    $xmlDpto.= "<descripcion></descripcion>";
		    $xmlDpto.= "</tipoantecedente>";
	
	        while($row = mysql_fetch_array($result))
	        {	
	        	$xmlDpto.= "<tipoantecedente>";
	            $xmlDpto.= "<id_tipo_antec>".$row["id_tipo_antec"]."</id_tipo_antec>";
	            $xmlDpto.= "<descripcion>".$row["descripcion"]."</descripcion>";
	        	$xmlDpto.= "</tipoantecedente>";
	        }
	    } 
	    $xmlDpto.= "</tiposantecedentes>";
	    generoXmlOkErrores("",$errores,$xmlDpto);
	    break;
	}
	case "armarXmlAntec":{
		global $salud;
	    $errores="";	
	    $xmlDpto= "<antecedentes>";
	    $query = "SELECT * FROM $salud.027_antecedentes WHERE id_tipo_antec = '".$_REQUEST['id_tipo_antec']."' ORDER BY antecedente";
	    $result = mysql_query($query);
	    if (mysql_errno() > 0) {
	    	$errores.="Error devuelto por la Base de Datos: ".mysql_errno()." ".mysql_error()."\n";
	    } else {          
		    $xmlDpto.= "<antecedente>";
		    $xmlDpto.= "<id_antecedente></id_antecedente>";
		    $xmlDpto.= "<antecedente></antecedente>";
		    $xmlDpto.= "</antecedente>";
	
	        while($row = mysql_fetch_array($result))
	        {	
	        	$xmlDpto.= "<antecedente>";
	            $xmlDpto.= "<id_antecedente>".$row["id_antecedente"]."</id_antecedente>";
	            $xmlDpto.= "<antecedente>".$row["antecedente"]."</antecedente>";
	        	$xmlDpto.= "</antecedente>";
	        }
	    } 
	    $xmlDpto.= "</antecedentes>";
	    generoXmlOkErrores("",$errores,$xmlDpto);
	    break;
	}
}
?>