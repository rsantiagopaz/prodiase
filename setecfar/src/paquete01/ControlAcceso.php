<?php
//-------------- BUSCO LA RAIZ PARA ACCEDER A config.php ------------------
$SYSpathraiz='';
 while (!file_exists($SYSpathraiz.'_raiz.php'))	
  {
   $SYSpathraiz='../'.$SYSpathraiz;
  }
// ------------ FIN: BUSCO LA RAIZ PARA ACCEDER A config.php  -------------

 header('Cache-Control: no-cache, must-revalidate');
 session_start();
 
 //sleep(3);

// Conexión a la BD ----------------------------------------------------- 
include($SYSpathraiz."config.php");

 // Parámetros de entrada ---------------------------
 $rutina        = $_REQUEST['rutina'];
 $SYSusuario    = $_REQUEST['usuario'];
 $SYSpassword   = $_REQUEST['password']; 
 $SYSsistema_id = $_REQUEST['sistema_id'];
 $id_oas_usuario = $_REQUEST['id_oas_usuario'];  
 // FIN: Parámetros de entrada ----------------------


 switch($rutina)
 {
  //************************************************************************************************
  case 'TraerServicios':
  //************************************************************************************************
   {
   		$sql = "SELECT o.id_oas_usuario, a.organismo_area 'area', oas.id_organismo_area 'id_area' ,s.denominacion 'servicio' ";
   		$sql.="FROM $salud.oas_usuarios o ";
   		$sql.="INNER JOIN $salud._organismos_areas_servicios oas USING(id_organismo_area_servicio) ";
   		$sql.="INNER JOIN $salud._organismos_areas a ON a.organismo_area_id=oas.id_organismo_area ";
   		$sql.="INNER JOIN $salud._servicios s USING(id_servicio) ";
   		$sql.="WHERE SYSusuario = BINARY '$SYSusuario' ";
   		$sql.="ORDER BY a.organismo_area, s.denominacion ";
// echo $sql;
		$result = mysql_query($sql);
	  	$xml = "<?xml version='1.0' encoding='UTF-8' ?>";
	 	$xml.= "<xml>";
	 	
	 	if($row = mysql_fetch_array($result))
	  	{
	  		$id_area = $row['id_area'];
	  		$xml.="<areaservicio area='".$row['area']."' id_area='".$row['id_area']."'>";
	  		do{
	  			if ($id_area!=$row['id_area']){
	  				$xml.="</areaservicio>";
	  				$id_area = $row['id_area'];
	  				$xml.="<areaservicio area='".$row['area']."' id_area='".$row['id_area']."'>";
	  			}
	  			$xml.="<servicio id_area='".$row['id_area']."' id_oas_usuario='".$row['id_oas_usuario']."' servicio='".$row['servicio']."' />";	
	  		
	  		}while ($row = mysql_fetch_array($result));
	  		$xml.="</areaservicio>";
	  	}
	  	$xml.= "</xml>";
		header('Content-Type: text/xml');
     	print $xml;
	 
    	break;
   }	
  //************************************************************************************************
  case 'login':
  //************************************************************************************************
   {
    session_destroy();
	session_start();
	// Si ya había un usuario logueado, lo borra
	$_SESSION['usuario']   = '';
	$_SESSION['sesion_id'] = '';

	$_mensaje='';
	
	if (empty($SYSusuario))
	 $_mensaje.='No ingresó su nombre de usuario. ';
	
	if (empty($SYSpassword))
	 $_mensaje.='No ingresó su contraseña. ';

	if (empty($SYSsistema_id))
	 $_mensaje.='No está indicado a que sistema desea conectarse. ';
	 
	if (empty($id_oas_usuario))
	 $_mensaje.='No está indicado a desde que servicio desea conectarse. ';
	 
	// Verifico si el suario es válido
	if (!empty($SYSusuario) AND !empty($SYSpassword))
	{
		$result = mysql_query("SELECT * FROM $salud._usuarios WHERE SYSusuario = BINARY '$SYSusuario' AND SYSpassword = MD5('$SYSpassword')");
	  	// ---------------
	  	$fila=mysql_fetch_array($result);
	  
	  	$num=mysql_num_rows($result);
	  	if ($num==1)
		{
	         // Verifico si el usuario está activo es decir que "SYSusuario_estado = 1" 
	         $sql="SELECT *  ";
	         $sql.="FROM $salud._usuarios u ";
	         $sql.="INNER JOIN $salud.oas_usuarios USING(SYSusuario) ";
	         $sql.="INNER JOIN $salud._organismos_areas_servicios oas USING(id_organismo_area_servicio) ";
	   		 $sql.="INNER JOIN $salud._organismos_areas a ON a.organismo_area_id=oas.id_organismo_area ";
	   		 $sql.="INNER JOIN $salud._servicios s USING(id_servicio) ";
	         //$sql.="INNER JOIN $salud._organismos ON $salud._organismos.organismo_id = $salud._organismos_areas.organismo_id ";
	         $sql.="INNER JOIN $salud._organismos USING(organismo_id) ";
	         $sql.="INNER JOIN $salud._personas p ON p.persona_id=u.id_persona ";
	         $sql.="WHERE $salud.oas_usuarios.id_oas_usuario='$id_oas_usuario' ";
	
	
	         $result=@mysql_query($sql);		 
	         $num=@mysql_numrows($result);
	         if ($num>=1)
    	     {
				   $_SESSION['usuario']                             = mysql_result($result,0,SYSusuario);
		   		   $_SESSION['usuario_id']                             = mysql_result($result,0,SYSusuario);
				   $_SESSION['usuario_nombre']                         = mysql_result($result,0,persona_nombre);
				   $_SESSION['usuario_estado']                         = mysql_result($result,0,SYSusuario_estado);
				   $SYSusuario_estado =   mysql_result($result,0,SYSusuario_estado);
				   
				   $_SESSION['usuario_organismo_id']                = mysql_result($result,0,organismo_id);
				   $_SESSION['usuario_organismo']                   = mysql_result($result,0,organismo);
				   $_SESSION['usuario_organismo_area_id']           = mysql_result($result,0,organismo_area_id);
				   $_SESSION['usuario_organismo_area']              = mysql_result($result,0,organismo_area);
				   $_SESSION['usuario_organismo_area_mesa_entrada'] = mysql_result($result,0,organismo_area_mesa_entrada);
				   
				   $_SESSION['usuario_servicio']              = mysql_result($result,0,denominacion);
				   $_SESSION['usuario_servicio_id'] = mysql_result($result,0,id_servicio);
				   $_SESSION['id_oas_usuario']              = mysql_result($result,0,id_oas_usuario);
				   
				   $_SESSION['id_organismo_area_servicio'] = mysql_result($result,0,id_organismo_area_servicio);
				   
				   if ($SYSusuario_estado==1)
				   {
		   				$sql="SELECT *  ";
				        $sql.="FROM $salud._sistemas_usuarios ";
				        $sql.="INNER JOIN $salud.sistemas_perfiles_usuarios_oas USING(id_sistema_usuario) ";
				        $sql.="WHERE $salud.sistemas_perfiles_usuarios_oas.id_oas_usuario='$id_oas_usuario' ";
				        $sql.="AND $salud._sistemas_usuarios.sistema_id='$SYSsistema_id' ";
				        
				        $result = mysql_query($sql);
				        $nr = mysql_num_rows($result);
				        if ($nr>0)
				        {
					        $SYSsistemas_perfiles_usuario = array();
					        while ($row = mysql_fetch_array($result))
					        {
					        	$SYSsistemas_perfiles_usuario[]=$row["perfil_id"];
					        }
					        $_SESSION['sistemas_perfiles_usuario'] = $SYSsistemas_perfiles_usuario;
					        
					        // Como todo está bien, guardo la sesión en la BD
							$_sessionid = date('YmdHis')."-".session_id();	
							// Grabo en las variables de sesión:
							$_SESSION['SYSsesion_id'] = $_sessionid;
							$_SESSION['SYSusuario']   = $SYSusuario;
							// --
							$SYSsesionfecha = date('Y-m-d');
							$SYSsesionhora  = date('H:i:s');
							// Ingreso en tabla _sesiones los campos _sessionid y SYSusuario
							$ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];
							mysql_query ("INSERT INTO $salud._sesiones 
								   (
										 _sessionid,
										 SYSusuario,
										 SYSsesionfecha,
										 SYSsesionhora,
										 SYSsesiondetalle,
										 ip
										 )  
									   VALUES 
										(
										 '$_sessionid',	  
										 '$SYSusuario',
										 '$SYSsesionfecha',
										 '$SYSsesionhora',
										 '$SYSsesiondetalle',
										 '$ip'
										)");
									 // Verifico si se grabaron los datos
							$result=mysql_query("SELECT * FROM $salud._sesiones WHERE _sessionid='$_sessionid' AND SYSusuario='$SYSusuario'");
							$num=mysql_numrows($result);
							if ($num<=0)
							{
							  $_mensaje.='No se pudo grabar la sesión y el usuario en tabla _sesiones. Contáctese con el administrador del sistema. ';
							}
		        	}else{
		        	 	$_mensaje.='¡El usuario '.$SYSusuario.' NO ESTA AUTORIZADO a utilizar el sistema '.$SYSsistema_id.'! ';
		        	}
		   		}else{
		   			$_mensaje.='¡El usuario '.$SYSusuario.' Se encuentra deshabilitado ';
		   		}
		 	}else{
		 		$_mensaje.='El Usuario no esta Registrado ';
    	    }
		}else{
		 	$_mensaje.="¡ Sus datos de acceso NO SON VALIDOS !\n\n";
		 	$_mensaje.="Verifique haber ingresado bien su nombre de usuario y contraseña, respetando mayúsculas y minúsculas.\n";
		 	$_mensaje.="Por ejemplo, si su nombre de usuario es juanperez, no intente escribir JuanPerez ni JUANPEREZ, u otra forma. Idem para la contraseña.";
		 }
	}else{
		$_mensaje.='No indico Usuario y Contraseña ';
	}  
	 // Armo el XML ---------------------------------------
	 $xml = "<?xml version='1.0' encoding='UTF-8' ?>";
	 $xml.= "<xml>";
	 if (!empty($_mensaje))
	   {     
	    $xml.="<error>$_mensaje</error>";
	   }
	  else
	   {
	    $xml.="<ok>";
		$xml.="¡¡Bienvenido ".$_SESSION['usuario_nombre']." (".$SYSusuario.")!!\n\n";
		$xml.="Puede comenzar a trabajar. Recuerde CERRAR SESION cuando termine o si desea cambiar de usuario.\n\n";
		$xml.="¡NUNCA DEJE EL NAVEGADOR ABIERTO Y SE RETIRE!";
		$xml.="</ok>";
		// Aquí mando todos los valores necesarios para algunas propiedades de la clase ControlAcceso
		$xml.="<ControlAcceso>";
		
		$xml.=  "<_sistema_id>";
		$xml.=    $SYSsistema_id;
		$xml.=  "</_sistema_id>";

		$xml.=  "<usuario_servicio>";
		$xml.=    utf8_encode($_SESSION['usuario_servicio']);
		$xml.=  "</usuario_servicio>";
		
		$xml.=  "<usuario_servicio_id>";
		$xml.=    utf8_encode($_SESSION['usuario_servicio_id']);
		$xml.=  "</usuario_servicio_id>";
		
		$xml.=  "<id_oas_usuario>";
		$xml.=    $_SESSION['id_oas_usuario'];
		$xml.=  "</id_oas_usuario>";
		
		$xml.= "<id_organismo_area_servicio>";
		$xml.= $_SESSION['id_organismo_area_servicio'];
		$xml.= "</id_organismo_area_servicio>";

		$xml.=  "<_usuario>";
		$xml.=    utf8_encode($_SESSION['usuario']);
		$xml.=  "</_usuario>";
		
		$xml.=  "<_usuario_id>";
		$xml.=    utf8_encode($_SESSION['usuario_id']);
		$xml.=  "</_usuario_id>";
		
		$xml.=  "<_usuario_nombre>";
		$xml.=    utf8_encode($_SESSION['usuario_nombre']);
		$xml.=  "</_usuario_nombre>";
		
		$xml.=  "<_usuario_estado>";
		$xml.=    $_SESSION['usuario_estado'];
		$xml.=  "</_usuario_estado>";
		
		$xml.=  "<_sesion_id>";
		$xml.=    $_SESSION['SYSsesion_id'];
		$xml.=  "</_sesion_id>";
		
		$xml.=  "<_autorizado>";
		$xml.=   "true";
		$xml.=  "</_autorizado>";
		
		$xml.=  "<_usuario_organismo_id>";
		$xml.=    $_SESSION['usuario_organismo_id'];
		$xml.=  "</_usuario_organismo_id>";

		$xml.=  "<_usuario_organismo>";
		$xml.=   utf8_encode($_SESSION['usuario_organismo']);
		$xml.=  "</_usuario_organismo>";
		
		$xml.=  "<_usuario_organismo_area_id>";
		$xml.=   $_SESSION['usuario_organismo_area_id'];
		$xml.=  "</_usuario_organismo_area_id>";
		
		$xml.=  "<_usuario_organismo_area>";
		$xml.=    utf8_encode($_SESSION['usuario_organismo_area']);
		$xml.=  "</_usuario_organismo_area>";
		
		$xml.=  "<_usuario_sistemas_perfiles>";
		if(isset($_SESSION['sistemas_perfiles_usuario']) and !empty($_SESSION['sistemas_perfiles_usuario'])) 
		 {
		           $vectorPerfiles = $_SESSION['sistemas_perfiles_usuario'];
		           foreach($vectorPerfiles as $perfil_id)
				    {
					 $xml.= "<perfil_id>";
					 $xml.=   $perfil_id;
					 $xml.= "</perfil_id>";
					}
		 }			
		
		$xml.=  "</_usuario_sistemas_perfiles>";
		
		$xml.=  "<_usuario_organismo_area_mesa_entradas>";
		           if (empty($_SESSION['usuario_organismo_area_mesa_entrada']) )
				      $xml.= "0";
					 else 
					  $xml.= $_SESSION['usuario_organismo_area_mesa_entrada'];
		$xml.=  "</_usuario_organismo_area_mesa_entradas>";
		
	    $xml.="</ControlAcceso>";
		
		
	   }
     $xml.= "</xml>";
	 header('Content-Type: text/xml');
     print $xml;
	 // FIN: Armo el XML -----------------------------------
	 
    break;
   }
   
  //************************************************************************************************ 
  case 'logout':
  //************************************************************************************************
   {
    $ok='';
	$error='';
    if(isset($_SESSION['SYSsesion_id']) and !empty($_SESSION['SYSsesion_id']))
	  {

		$_sessionid = $_SESSION['SYSsesion_id'];
		$_fechaactual = date('Y-m-d');
		$_horaactual  = date('H:i:s');
		mysql_query ("UPDATE _sesiones SET 
				  SYSsesionfecha_cierre = '$_fechaactual' ,
				  SYSsesionhora_cierre  = '$_horaactual'
				  WHERE _sessionid='$_sessionid'"); 
		$_SESSION['SYSusuario']   = '';
		$_SESSION['SYSsesion_id'] = '';
		session_destroy();
		session_start();
		
	    $ok.="¡¡Sesión CERRADA EXITOSAMENTE!!\n\n";
		$ok.="Cierre todas las ventanas de su navegador."."\n";
		$ok.="($_sessionid)";
	  }
	 else
	  {
       $error.="¡¡No existe iniciada la sesión o la misma ya fue cerrada!!";	   
	  } 
    
	// Devuelvo el xml
	$xml = "<?xml version='1.0' encoding='UTF-8' ?>";
	$xml.= "<xml>";
	if(!empty($error))
	 {
	  $xml.="<error>$error</error>";
	 }

  	if(!empty($ok))
	 {
	  $xml.="<ok>".$ok."</ok>";
	 }
	$xml.="</xml>";  	  
	header('Content-Type: text/xml');
    print $xml;
    break;	  
   }



  //************************************************************************************************ 
  case 'ver_variables_sesion':
  //************************************************************************************************
   {
    print '$_SESSION[SYSsesion_id] = '.$_SESSION['SYSsesion_id'].'<br>';
	print '$_SESSION[SYSusuario] = '.$_SESSION['SYSusuario'].'<br>';
	print '$_SESSION[usuario_nombre] = '.$_SESSION['usuario_nombre'].'<br>';
	print '$_SESSION[usuario_estado] = '.$_SESSION['usuario_estado'].'<br>';
	print '$_SESSION[usuario_organismo_id] = '.$_SESSION['usuario_organismo_id'].'<br>';
	print '$_SESSION[usuario_organismo] = '.$_SESSION['usuario_organismo'].'<br>';
	print '$_SESSION[usuario_organismo_area_id] = '.$_SESSION['usuario_organismo_area_id'].'<br>';
	print '$_SESSION[usuario_organismo_area] = '.$_SESSION['usuario_organismo_area'].'<br>';
	print '$_SESSION[usuario_organismo_area_mesa_entrada] = '.$_SESSION['usuario_organismo_area_mesa_entrada'].'<br>';
	
	print '$_SESSION[sistemas_perfiles_usuario] = ';
	if(isset($_SESSION['sistemas_perfiles_usuario']))
	 {
	  foreach($_SESSION['sistemas_perfiles_usuario'] as $perfil_id)
	   {
	    print $perfil_id." | ";
	   }
	 } 
	
/*
	print '$_SESSION[]= '.$_SESSION[''].'<br>';
	print '$_SESSION[]= '.$_SESSION[''].'<br>';
	print '$_SESSION[]= '.$_SESSION[''].'<br>';
	print '$_SESSION[]= '.$_SESSION[''].'<br>';
	print '$_SESSION[]= '.$_SESSION[''].'<br>';
	print '$_SESSION[]= '.$_SESSION[''].'<br>';
*/	

    break;	
   }


  //************************************************************************************************ 
  case 'matar_sesion':
  //************************************************************************************************
   {
    session_destroy();
	session_start();
   }



  //************************************************************************************************ 
  case 'cambiar_password':
  //************************************************************************************************
   {
    // Parámetros de entrada:
	
	// --------------------------------------------
	$SYSusuario         = $_REQUEST["usuario"];
	$SYSpassword        = $_REQUEST["passwordActual"];
	$SYSpassword_nuevo1 = $_REQUEST["passwordNueva"];
	$SYSpassword_nuevo2 = $_REQUEST["passwordNuevaConfirmada"];	
	
//	$SYSpathraiz        = $_REQUEST["SYSpathraiz"];
//	$SYSpatharchivo     = $_REQUEST["SYSpatharchivo"];
	// --------------------------------------------
	
	//include('../config.php');
	
	
	$mensaje='';
	
	if (empty($SYSusuario))
	 $mensaje.="No ingresó su nombre de usuario.\n";
	
	if (empty($SYSpassword))
	 $mensaje.="No ingresó su password.\n";
	
	// Verifico si el suario es válido
	if (!empty($SYSusuario) AND !empty($SYSpassword))
	 {
	  // Modificado el 3/5/2007 para uso de MD5
	  $result=mysql_query("SELECT * FROM salud1._usuarios WHERE SYSusuario = BINARY '$SYSusuario' AND SYSpassword = MD5('$SYSpassword')");

	  $num=mysql_numrows($result);
	  if ($num==1)
		   {
			if ($SYSpassword_nuevo1==$SYSpassword_nuevo2)
			  {
				if (strlen($SYSpassword_nuevo1)>20 OR strlen($SYSpassword_nuevo1)<5)
				  {
				   $mensaje.="La contraseña nueva no debe exceder los 20 caracteres ni debe ser menor que 5 caracteres.\n";  
				  }
				 else
				  {
				   $CaracteresNoValidos="&/*!¡¿?+[]()%·$\=@'".'"';
				   // VERIFICO CARACTERES NO VALIDOS EN LA CONTRASEÑA
				   for ($i = 0; $i < (strlen($CaracteresNoValidos)); $i++)
					{
					 // Busco en el campo a validar si existe un caracter no válido
					 if(strstr($SYSpassword_nuevo1,$CaracteresNoValidos[$i]))
						 $mensaje.="No se permitre el caracter \" ".$CaracteresNoValidos[$i]." \" en la contraseña.\n";
					 }
				  // Fin VERIFICO CARACTERES NO VALIDOS 
	   
	
				  }
			  }
			 else
			  {
			   $mensaje.="No hay coincidencia en la confirmación de la contraseña nueva.\n";
			  }  	
		   }
		 else
		   {
			 $mensaje.="Sus datos de acceso no son válidos. Verifique haber ingresado bien su nombre de usuario y contraseña.\n";
		   }
	 }
	
	
	 if (!empty($mensaje))
	  {
	   $error = $mensaje;
      }
	 else
	  {
	/* Antes del 3/5/2007
		mysql_query ("UPDATE _usuarios SET 
				SYSpassword='$SYSpassword_nuevo1'
			   WHERE SYSusuario='$SYSusuario'"); 
	*/
	 // Desde el 3/5/2007
		mysql_query ("UPDATE salud1._usuarios SET 
				SYSpassword=MD5('$SYSpassword_nuevo1') 
			   WHERE SYSusuario= BINARY '$SYSusuario'"); 
	 // ---------------------------------------------		   
	   if (mysql_errno()>0)	
		 {
		  $error = "Error en BD: ".mysql_errno().": ".mysql_error();	 
		 } 
		else     
		 {
		  $ok = "¡ CAMBIO DE CONTRASEÑA EXITOSO !";
		 } 
	  }
	
	
    // Devuelvo el xml
	$xml = "<?xml version='1.0' encoding='UTF-8' ?>";
	$xml.= "<xml>";
	if(!empty($error))
	 {
	  $xml.="<error>$error</error>";
	 }

  	if(!empty($ok))
	 {
	  $xml.="<ok>".$ok."</ok>";
	 }
	$xml.="</xml>";  	  
	header('Content-Type: text/xml');
    print $xml;
	break;
   }



 }

?>