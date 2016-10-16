<?php

include('includes/KLPointOfSale.php');

//################## PRINTING STUFF #####################
include 'includes/WebClientPrint.php';
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

// RICARD: To be improved to remove the hardcoded paths and get just 1 wcpcache folder in all installation
if (webERP_in_test()){
	$filename = 'https://www.bumibiru.com/TEST/weberp/wcpcache/'.$identifier.'.pos';   
}else{
	$filename = 'https://www.bumibiru.com/weberp/wcpcache/'.$identifier.'.pos';   
}
	
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
		echo $cpj->sendToClient();
		ob_end_flush();
		exit();
		
//################## PRINTING STUFF #####################





?>
