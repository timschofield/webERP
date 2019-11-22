<?php

include('includes/KLPOSGeneral.php');

//################## PRINTING STUFF #####################
include 'includes/WebClientPrint/WebClientPrint.php';
use Neodynamic\SDK\Web\WebClientPrint;
use Neodynamic\SDK\Web\Utils;
use Neodynamic\SDK\Web\DefaultPrinter;
use Neodynamic\SDK\Web\InstalledPrinter;
use Neodynamic\SDK\Web\ClientPrintJob;
//################## PRINTING STUFF #####################	

if(isset($_GET['identifier'])) {
	$identifier = $_GET['identifier'];
} elseif(isset($_POST['identifier'])) {
	$identifier = $_POST['identifier'];
} else {
	$identifier = '';
}

$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'));
if (isset($DirectoryLevelsDeep)){
	for ($i=0;$i<$DirectoryLevelsDeep;$i++){
		$RootPath = mb_substr($RootPath,0, strrpos($RootPath,'/'));
	}
}
if ($RootPath == "/" OR $RootPath == "\\") {
	$RootPath = "";
}

// RICARD: 
//if($_SESSION['HTTPS_Only'] == 1){
//	$Protocol = "https";
//}else{
//	$Protocol = "http";
//}
//$filename =  $Protocol . "://$_SERVER[HTTP_HOST]" . $RootPath . '/includes/WebClientPrint/wcpcache/'.$identifier.'.pos';   

$filename =  './includes/WebClientPrint/wcpcache/'.$identifier.'.pos';   

$texttoprint = file_get_contents($filename,false);

//################## PRINTING STUFF #####################

//Create a ClientPrintJob obj that will be processed at the client side by the WCPP
		$cpj = new ClientPrintJob();
		//set ESC/POS commands to print...
		$cpj->printerCommands = $texttoprint;
		$cpj->formatHexValues = true;
		//set client printer
		$cpj->clientPrinter = new DefaultPrinter();
		
		//Send ClientPrintJob back to the client
		ob_start();
		ob_clean();
		echo $cpj->sendToClient();
		ob_end_flush();
		exit();
		
//################## PRINTING STUFF #####################

?>
