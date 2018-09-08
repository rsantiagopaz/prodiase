<?php 
$apaisada='0';
switch($_REQUEST['tipo'])
{
	case 'estudio':
		$url=urlencode("http://192.168.204.2/colegio/consultorio/impresiones/estudios.php?id=".$_REQUEST['id']."&id_paciente=".$_REQUEST['id_paciente']);
		$nombre="Receta de Estudios y Practicas";
		$paper='a5';
		break;
	case 'monodroga':
		$url=urlencode("http://192.168.204.2/colegio/consultorio/impresiones/medicamentos.php?id=".$_REQUEST['id']."&id_paciente=".$_REQUEST['id_paciente']);
		$nombre="Receta de Monodrogas";
		$paper='a5';
		break;
	case 'ficha':
		$url=urlencode("http://192.168.204.2/colegio/consultorio/impresiones/ficha.php?id=".$_REQUEST['id']);
		$nombre="Ficha Oncologica";
		$paper='legal';
		$apaisada='1';
		break;
}
//  header("Location: html2pdf.php?url=".$url."&nombre=".$nombre."&paper=".$paper."&apaisada=".$apaisada);
?>

<HTML>
<BODY onLoad="window.print();" >
<embed src="html2pdf.php?url=<? echo $url; ?>&nombre=<? echo $nombre;?>&paper=<? echo $paper; ?>&apaisada=<? echo $apaisada; ?>" type="application/pdf" width="100%" height="100%">
</BODY>
</HTML>