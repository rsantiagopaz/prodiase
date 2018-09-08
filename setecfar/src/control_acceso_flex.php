<?php  
require_once('config.php');
//-------------- CONTROL DE ACCESO FLEX (25/02/2009)-------------------
$SYSsistema_id='036';
$SYSsistema_perfil_login='036001';
$SYSpathraiz='';
 while (!file_exists($SYSpathraiz.'_raiz.php'))	
  {
   $SYSpathraiz='../'.$SYSpathraiz;
  }
$SYSpatharchivo='http://'.$HTTP_SERVER_VARS["HTTP_HOST"].$HTTP_SERVER_VARS["REQUEST_URI"];
include($SYSpathraiz.'_scripts/_controlacceso_flex.php'); 
// ------------ FIN CONTROL DE ACCESO FLEX------------------------------

if($_acceso!='SI')
 {
  $_xml_error_detalle = "<?xml version='1.0' encoding='UTF-8' ?>";
  $_xml_error_detalle.= "<xml>";
  $_xml_error_detalle.=  "<error>$_mensaje</error>";
  $_xml_error_detalle.=  "<_acceso>$_acceso</_acceso>";
  $_xml_error_detalle.=  "<_login>$_login</_login>";
  $_xml_error_detalle.= "</xml>";
  header('Content-Type: text/xml');
  print $_xml_error_detalle;
  exit;
 }
include($SYSpathraiz.'_scripts/_auditoria.php');  


?>