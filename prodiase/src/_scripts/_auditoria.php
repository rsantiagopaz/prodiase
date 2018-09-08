<?php

// Creado: 4/9/2007 By Jorge Mitre

// Parámetros de entrada:
//    $_mysql_query
//    $_mysql_link
//    $_auditoria_php_file = __FILE__
//    $_auditoria_php_line = __LINE__

// Parámetros de salida:
//    $_mysql_result
//    $_mysql_errno
//    $_mysql_error 

function _auditoria($_mysql_query, 
                    $_mysql_link,
					$_link_auditoria,
					$_auditoria_php_file,
                    $_auditoria_php_line,
					&$_mysql_result,
					&$_mysql_errno, 
					&$_mysql_error,
					&$_mysql_insert_id)
{

// print $_mysql_link;
// print '<br>';
// print $_mysql_query;

$_auditoria_query_user     = 'SELECT USER()     AS mysql_user';
$_auditoria_query_database = 'SELECT DATABASE() AS mysql_database';

if($_mysql_link) 
  {
    // print '<br>pasa 1';
   $_mysql_result=@mysql_query($_mysql_query,$_mysql_link);
   $_mysql_insert_id=mysql_insert_id();
  }
 else
  {
    // print '<br>pasa 2';
    $_mysql_result=@mysql_query($_mysql_query);  
	$_mysql_insert_id=mysql_insert_id();
  } 
  
if(mysql_errno()>0)
  {
   // print '<br>pasa 3';
   $_mysql_errno = mysql_errno();
   $_mysql_error = mysql_error();
  }
 else
  {
    // print '<br>pasa 4';
   // obtengo usuario y base de datos
   $_auditoria_query_user     = 'SELECT USER()     AS mysql_user';
   $_auditoria_query_database = 'SELECT DATABASE() AS mysql_database';
   if($_mysql_link) 
    {
	 // print '<br>pasa 5';
     $_auditoria_result_user     = @mysql_query($_auditoria_query_user    , $_mysql_link);
	 $_auditoria_result_database = @mysql_query($_auditoria_query_database, $_mysql_link);
    }
   else
    {
	 // print '<br>pasa 6';
     $_auditoria_result_user     = @mysql_query($_auditoria_query_user    );
	 $_auditoria_result_database = @mysql_query($_auditoria_query_database);
    } 
   if( !(mysql_errno()>0) )  	
     {
	  // print '<br>pasa 7';
	  $_auditoria_row=@mysql_fetch_array($_auditoria_result_user);
	  $_auditoria_mysql_user = $_auditoria_row["mysql_user"];

	  $_auditoria_row=@mysql_fetch_array($_auditoria_result_database);
	  $_auditoria_mysql_database = $_auditoria_row["mysql_database"];
	 }
	else
	 {
	  $_auditoria_mysql_user     = '?';
	  $_auditoria_mysql_database = '?';
	 } 
   // print $_auditoria_mysql_user;
   // print $_auditoria_mysql_database;
	 
   // ----------------------------
   $_auditoria_mysql_operacion  = substr(trim(strtoupper($_mysql_query)),0,6);
   $_auditoria_fecha            = date("Y-m-d"); 
   $_auditoria_hora             = date("H:i:s");
   $_auditoria_ip               = $_SERVER["REMOTE_ADDR"]; 

   // Agregado el 27/6/2008, por el problemas de las comillas simples:
   $AuxCadena = "_barra_invertida_comilla_simple_" ;
   $_mysql_query = str_replace("\'",$AuxCadena,$_mysql_query);
   // ---
   $_mysql_query = trim(str_replace("'","\'",$_mysql_query));
   $_mysql_query = trim(str_replace($AuxCadena,"\'",$_mysql_query));
   
   $SYSusuario = $GLOBALS["SYSusuario"];
   $_sessionid = $GLOBALS["_sessionid"];
   
   
   $_auditoria_query = "
	          INSERT INTO _auditoria 
			    (SYSusuario   , _sessionid    ,  mysql_query     , mysql_user               , mysql_database               , mysql_operacion               , php_file               , php_line               , fecha             , hora             ,  ip )
               VALUES 
			    ('$SYSusuario', '$_sessionid' , '$_mysql_query'  , '$_auditoria_mysql_user' , '$_auditoria_mysql_database' , '$_auditoria_mysql_operacion' , '$_auditoria_php_file' , '$_auditoria_php_line' , '$_auditoria_fecha', '$_auditoria_hora', '$_auditoria_ip')
	          "; 
   //// print "<br><br>link $_link_auditoria <br><br>";
   if($_link_auditoria)
    {
	 // print '<br>pasa 8<br><br>';
	 // print $_auditoria_query;
	 if ( !(@mysql_select_db("sursde", $_link_auditoria)) )
	  {
	   if ( !(@mysql_select_db("salud1", $_link_auditoria)) )
		{
		 
		 // no pudo seleccionr la bd
	     $_mysql_errno = mysql_errno(); 
	     $_mysql_error = mysql_error().". No pudo seleccionr la bd donde esta tabla _auditoria en ".__FILE__." línea ".__LINE__;
		}
	  }
	 if (empty($_mysql_error)) 
	  {
       @mysql_query($_auditoria_query,$_link_auditoria);
       if(mysql_errno()>0)
         {
	      $_mysql_errno = mysql_errno(); 
	      $_mysql_error = mysql_error().". En ".__FILE__." línea ".__LINE__;
	     }
	   // Deja seleccionada la BD que estaba anteriormente seleccionada

       if($_mysql_link) 
         {		 
	      @mysql_select_db($_auditoria_result_database, $_mysql_link); 
		 } 
		else
		 {
		  // Ojo que en este caso usa el ultimo link usado 
		  @mysql_select_db($_auditoria_result_database); 
		 } 
      }
	}
   else
    {
	 $_mysql_errno = -1; 
	 $_mysql_error = "No se puedo utilizar el enlace (\$_link_auditoria) a la base de datos donde se encuentra la tabla _auditoria";
	}
   
	
  } 

// print "<br><br>";  
// print $_mysql_errno;
// print "<br>";  
// print $_mysql_error;
  
mysql_select_db($_auditoria_result_database);  
}  
?>