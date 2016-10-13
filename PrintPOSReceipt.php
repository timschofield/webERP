<?php

//  IMPORTANT: the file WebClientPrint.php MUST NOT require authentication!!! It must allow Anonymous access because WCPP cannot provide any user credentials!!!
// include('includes/session.inc');
include('includes/KLPointOfSale.php');
include('includes/KLPrintESCPOS.php');

//################## SILENT PRINTING STUFF #####################
include 'includes/WebClientPrint.php';
    use Neodynamic\SDK\Web\WebClientPrint;
    use Neodynamic\SDK\Web\Utils;
    use Neodynamic\SDK\Web\DefaultPrinter;
    use Neodynamic\SDK\Web\InstalledPrinter;
    use Neodynamic\SDK\Web\ClientPrintJob;
//################## SILENT PRINTING STUFF #####################	


if(isset($_GET['identifier'])) {
//	$identifier = filter_number_format($_GET['identifier']);
	$identifier = $_GET['identifier'];
} elseif(isset($_POST['identifier'])) {
//	$identifier = filter_number_format($_POST['identifier']);
	$identifier = $_POST['identifier'];
} else {
	$identifier = '';
}

if(isset($_GET['orderno'])) {
	$OrderNo = $_GET['orderno'];
} elseif(isset($_POST['orderno'])) {
	$OrderNo = $_POST['orderno'];
}

if (TRUE) {
	$TextToPrint = KLPrintReceiptCreateText($identifier, $OrderNo);
	//echo $TextToPrint;
	//
	// HERE WE SHOULD START THE PRINTING PROCESS OF THE POS RECEIPT
	//
	
	
	//################## SILENT PRINTING STUFF #####################
	
	//Create a ClientPrintJob obj that will be processed at the client side by the WCPP
            $cpj = new ClientPrintJob();
            //set ESC/POS commands to print...
            $cpj->printerCommands = $TextToPrint;
            $cpj->formatHexValues = true;
            //set client printer
            $cpj->clientPrinter = new DefaultPrinter();
            
            //Send ClientPrintJob back to the client
            ob_start();
            ob_clean();
            echo $cpj->sendToClient();
            ob_end_flush();
            exit();
			
	//################## SILENT PRINTING STUFF #####################
	
	
} 

?>
