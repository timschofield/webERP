<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Print Transfer from Shop to Kantor');
include('includes/header.php');

include('includes/StockFunctions.php');

include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPOSGeneral.php');

include('includes/WebClientPrint/WebClientPrint.php');
include('includes/KLESCPOSCommands.php');

$InputError = false;
$ErrorMessage = '';

// Get transfer reference from URL parameter or POST
if (isset($_GET['TransferID'])){
	$TransferReference = trim($_GET['TransferID']);
} elseif (isset($_POST['PrintTransfer'])){
	if (!isset($_POST['TransferReference']) || trim($_POST['TransferReference']) == ''){
		$InputError = true;
		$ErrorMessage = __('Please enter a transfer reference number');
	} else {
		$TransferReference = trim($_POST['TransferReference']);
	}
} else {
	$TransferReference = '';
	$InputError = true;
}

if ($TransferReference != '' && !$InputError){
	// Check if transfer exists
	$SQL = "SELECT COUNT(*) FROM loctransfers WHERE reference = '" . $TransferReference . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] == 0){
		$InputError = true;
		$ErrorMessage = __('Transfer reference not found in the system');
	}
}

if (!$InputError AND $TransferReference != ''){
	$TextToPrint = KLPrintReturnTransferToKantor($TransferReference);
	
	//################## PRINTING STUFF ##################### 
	$identifier = GetPOSIdentifier();
	$FileName = GetFilenameFromPOSIdentifier($identifier);  
	file_put_contents($FileName, $TextToPrint);
	$TextActionToPrint = 'Print Transfer number: ' . $TransferReference;
	include('includes/KLSilentPrinting.php');
	//################## PRINTING STUFF ##################### 
} else {
	prnMsg($ErrorMessage,'error');
}

include('includes/footer.php');
