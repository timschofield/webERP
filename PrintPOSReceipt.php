<?php

include('includes/KLPointOfSale.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');

//################## PRINTING STUFF #####################
include 'includes/WebClientPrint.php';
use Neodynamic\SDK\Web\WebClientPrint;
use Neodynamic\SDK\Web\Utils;
use Neodynamic\SDK\Web\DefaultPrinter;
use Neodynamic\SDK\Web\InstalledPrinter;
use Neodynamic\SDK\Web\ClientPrintJob;
//################## PRINTING STUFF #####################	


if(isset($_GET['texttoprint'])) {
	$texttoprint = $_GET['texttoprint'];
} elseif(isset($_POST['texttoprint'])) {
	$texttoprint = $_POST['texttoprint'];
} else {
	$texttoprint = '';
}

$texttoprint = str_replace("%20", " ", $texttoprint);

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
