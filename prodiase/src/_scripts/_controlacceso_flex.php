<?php

header('Expires: Wed, 23 Dec 1980 00:30:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');


 global $_sessionid;
 
 global $SYSusuario;
 global $SYSusuario_nombre;
 global $SYSusuario_estado;
 global $SYSusuario_organismo_id;
 global $SYSusuario_organismo;
 global $SYSusuario_organismo_area_id;
 global $SYSusuario_organismo_area;
 global $SYSusuario_organismo_area_mesa_entrada;
 global $SYSsistemas_perfiles_usuario;
 
 
 global $link_salud1;

 // USO DE SESION
 session_start();
 
 
//$_sessionid=$HTTP_SESSION_VARS['_sessionid']; 
$_sessionid = $_SESSION['SYSsesion_id'];
$SYSusuario = $_SESSION['usuario'];
$id_oas_usuario = $_SESSION['id_oas_usuario'];
 include($SYSpathraiz.'config.php');
  
  
 $_acceso='NO';
 
if (empty($_sessionid))
{
   $_acceso='NO';
   $_mensaje='¡ NO EXISTE SESION (si el mensaje persiste, comuníquelo al administrador de sistemas) !';
   $_login ='SI';
}
else
{ 
   $result=mysql_query("SELECT * FROM $salud._sesiones WHERE _sessionid='$_sessionid'");
   $num=mysql_numrows($result);
   $_acceso='SI';
   $_login='NO';
   if ($num<=0)
   {
      $_acceso='NO';
      $_mensaje='¡ NO SE ENCONTRO SESION: '.$_sessionid.' (es probable que UD. no haya ingresado sus datos de acceso aún) !';
      $_login ='SI';
   }
   else
   {
      $SYSusuario            = mysql_result($result,0,SYSusuario);
	  $SYSsesiondetalle      = mysql_result($result,0,SYSsesiondetalle);
	  $SYSsesionfecha_cierre = mysql_result($result,0,SYSsesionfecha_cierre);
	  $SYSsesionhora_cierre  = mysql_result($result,0,SYSsesionhora_cierre);
	  $SYSsesionfecha_ultimo = mysql_result($result,0,SYSsesionfecha_ultimo);
	  $SYSsesionhora_ultimo  = mysql_result($result,0,SYSsesionhora_ultimo);
		
	  if (empty($SYSusuario))
	  {
		$_acceso='NO';
		$_mensaje='¡ NO SE ENCONTRO EL USUARIO VINCULADO A LA SESION: '.$_sessionid.' !';
      } 
	  else
	  {
		 $sql="SELECT *  ";
	     $sql.="FROM $salud._usuarios u ";
	     $sql.="INNER JOIN $salud.oas_usuarios USING(SYSusuario) ";
	     $sql.="INNER JOIN $salud._organismos_areas_servicios oas USING(id_organismo_area_servicio) ";
	   	 $sql.="INNER JOIN $salud._organismos_areas a ON a.organismo_area_id=oas.id_organismo_area ";
	   	 $sql.="INNER JOIN $salud._servicios s USING(id_servicio) ";
         $sql.="INNER JOIN $salud._organismos USING(organismo_id) ";
         $sql.="INNER JOIN $salud._personas p ON p.persona_id=u.id_persona ";
         $sql.="WHERE $salud.oas_usuarios.id_oas_usuario='$id_oas_usuario' ";

         $result=@mysql_query($sql);
			 
         $num=mysql_numrows($result);
         if ($num>0)
         {
		   $SYSusuario_nombre            = mysql_result($result,0,persona_nombre);
		   $SYSusuario_estado            = mysql_result($result,0,SYSusuario_estado);
		   $SYSusuario_organismo_id      = mysql_result($result,0,organismo_id);
		   $SYSusuario_organismo         = mysql_result($result,0,organismo);
		   $SYSusuario_organismo_area_id = mysql_result($result,0,organismo_area_id);
		   $SYSusuario_organismo_area    = mysql_result($result,0,organismo_area);
		   $SYSusuario_organismo_area_mesa_entrada = mysql_result($result,0,organismo_area_mesa_entrada);
		   $SYSusuario_oas_id = mysql_result($result,0,id_oas_usuario);
		   //$SYSusuario_ = mysql_result($result,0,);
		   $SYSsistemas_perfiles_usuario[0]='';  
		   if ($SYSusuario_estado==1)
		   {
               $_acceso='SI';
               $_login ='NO';
			   
               // Verifico si el usuario está autorizado a utilizar el sistema.
			   if (empty($SYSsistema_id))
			   {
                  $_acceso='NO';
                  $_login ='NO';
				  $_mensaje='¡ ERROR DE SISTEMA: SYSsistema_id vacío !';
   				}
				else
				{
				  // Veo en tabla _sistemas_ususarios si está autorizado el usuario
                  $sql="SELECT *  ";
				  $sql.="FROM $salud._sistemas_usuarios ";
				  $sql.="INNER JOIN $salud.sistemas_perfiles_usuarios_oas USING(id_sistema_usuario) ";
				  $sql.="WHERE $salud.sistemas_perfiles_usuarios_oas.id_oas_usuario='$id_oas_usuario' ";
				  $sql.="AND $salud._sistemas_usuarios.sistema_id='$SYSsistema_id' ";
				        
				  $result = mysql_query($sql);
				  $nr = mysql_num_rows($result);
				  if ($nr>0)
				  {
					 $_acceso='SI';
					 $_login ='NO';
					 $SYSsistemas_perfiles_usuario = array();
					 while ($row = mysql_fetch_array($result))
					 {
					   	$SYSsistemas_perfiles_usuario[]=$row["perfil_id"];
					 }
					 $_SESSION['sistemas_perfiles_usuario'] = $SYSsistemas_perfiles_usuario;
					        
				  
					}
					else
				    {
		              $_acceso='NO';
			          $_mensaje.='¡ El usuario '.$SYSusuario.' NO ESTA AUTORIZADO a utilizar el sistema '.$SYSsistema_id.' !';
			          $_login='SI';
				    }
				}
			}
			else
		  	{
           		$_acceso='NO';
           		$_mensaje='¡ El usuario '.$SYSusuario.' SE encuentra deshabilitado !';
           		$_login ='NO';
		  	}
		}
		else
		{
		    $_acceso='NO';
		    $_mensaje.='¡ El usuario '.$SYSusuario.' NO ESTA AUTORIZADO a utilizar el sistema '.$SYSsistema_id.' !';
		    $_login='SI';
		}
	}
  }
}

if ($_acceso=='SI')
    {
	 $_fechaactual = date("d/m/Y");
	 $_horaactual  = date("H:i:s");
     $SYSsesiondetalle.=$SYSpatharchivo.'<br>';
	 mysql_query ("UPDATE $salud._sesiones SET 
	        SYSsesiondetalle='$SYSsesiondetalle',
			SYSsesionfecha_ultimo='$_fechaactual',
			SYSsesionhora_ultimo='$_horaactual'
           WHERE _sessionid='$_sessionid'"); 
	}
   
 // FIN: USO DE SESION

//print $_mensaje;
//print $_acceso;
?>
