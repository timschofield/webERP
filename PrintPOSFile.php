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

if (strpos($_SERVER['PHP_SELF'],"TEST")!== false){
	$CachePath = '/var/www/vhosts/kapal-laut.com/ptadu-development.com/TEST/weberp/includes/WebClientPrint/wcpcache/';
}else{
	$CachePath = '/var/www/vhosts/kapal-laut.com/ptadu-development.com/weberp/includes/WebClientPrint/wcpcache/';
}

$filename =  "file://" . $CachePath . $identifier.'.pos';   

$texttoprint = file_get_contents($filename);

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
		header('Content-type: application/octet-stream');
		echo $cpj->sendToClient();
		ob_end_flush();
		exit();
		
//################## PRINTING STUFF #####################





?>
