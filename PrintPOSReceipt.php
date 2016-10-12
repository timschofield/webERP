<?php

include('includes/session.inc');
include('includes/KLPointOfSale.php');
include('includes/KLPrintESCPOS.php');

if(isset($_GET['identifier'])) {
	$identifier = filter_number_format($_GET['identifier']);
} elseif(isset($_POST['identifier'])) {
	$identifier = filter_number_format($_POST['identifier']);
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
	echo $TextToPrint;
	//
	// HERE WE SHOULD START THE PRINTING PROCESS OF THE POS RECEIPT
	//
} 

?>
