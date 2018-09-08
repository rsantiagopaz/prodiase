<?php
require_once("dompdf/dompdf_config.inc.php");
$url = rawurldecode($_REQUEST['url']);
$dompdf = new DOMPDF();
$dompdf->set_base_path("../impresiones");
$dompdf->load_html_file($url);
if ($_REQUEST["apaisada"]=='1'){
    $dompdf->set_paper($_REQUEST["paper"],'landscape');    
}
else{
    $dompdf->set_paper($_REQUEST["paper"]);
}
$dompdf->render();
$dompdf->stream($_REQUEST['nombre'].".pdf");

?>
