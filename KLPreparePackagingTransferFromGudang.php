<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Send email to team to prepare a Packaging Transfer');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLEmails.php');

if (!isset($_GET['From'])){
	prnMsg( __('This page must be given the gudang location code.'), 'error');
	include(__DIR__ . '/includes/footer.php');
	exit();
} else {
	$EmailText = "From: " . $_GET['From'] . " " . GetLocationNameFromCode($_GET['From']) . "\n";
}

if (!isset($_GET['To'])){
	prnMsg( __('This page must be given the destination location code.'), 'error');
	include(__DIR__ . '/includes/footer.php');
	exit();
} else {
	$LocationNameTo = GetLocationNameFromCode($_GET['To']);
	$EmailText = $EmailText . "To: " . $_GET['To'] . " " . $LocationNameTo . "\n\n" ;
}

$NumParam = 1;
$CheckParam = true;
while ($CheckParam){
	$VarItem = 'Item'.$NumParam;
	$VarQty = 'Qty'.$NumParam;
	if (isset($_GET[$VarItem])){
		$EmailText = $EmailText . $_GET[$VarQty] . " x " . $_GET[$VarItem] . " " . GetItemDescriptionFromCode($_GET[$VarItem]) . "\n" ;
	} else {
		$CheckParam = false;
	}
	$NumParam++;
}

KLSendEmail("SendPackagingFromGudang", "ShortConfirmation", $LocationNameTo, $EmailText);

DB_Txn_Begin();

$SQL = "UPDATE locations 
		SET klemaillastpackacgingtransfer = CURRENT_DATE
		WHERE loccode =	'" . $_GET['To'] . "'";
$ErrMsg =__('Could not update the date of the last packaging transfer reminder because');
$Result = DB_query($SQL,$ErrMsg);
prnMsg("Updated date of email for packaging transfer to shop to today");

DB_Txn_Commit();
 
include(__DIR__ . '/includes/footer.php');
