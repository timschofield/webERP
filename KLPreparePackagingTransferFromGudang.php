<?php

include ('includes/session.php');
$Title = _('Send email to team to prepare a Packaging Transfer');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLEmails.php');

 
if (!isset($_GET['From'])){
	echo '<br />';
	prnMsg( _('This page must be given the gudang location code.'), 'error');
	include('includes/footer.php');
	exit;
}else{
	$EmailText = "From: " . $_GET['From'] . " " . GetLocationNameFromCode($_GET['From']) . "\n";
}

if (!isset($_GET['To'])){
	echo '<br />';
	prnMsg( _('This page must be given the destination location code.'), 'error');
	include('includes/footer.php');
	exit;
}else{
	$LocationNameTo = GetLocationNameFromCode($_GET['To']);
	$EmailText = $EmailText . "To: " . $_GET['To'] . " " . $LocationNameTo . "\n\n" ;
}

$NumParam = 1;
$CheckParam = TRUE;
while ($CheckParam){
	$VarItem = 'Item'.$NumParam;
	$VarQty = 'Qty'.$NumParam;
	if (isset($_GET[$VarItem])){
		$EmailText = $EmailText . $_GET[$VarQty] . " x " . $_GET[$VarItem] . " " . GetItemDescriptionFromCode($_GET[$VarItem]) . "\n" ;
	}else{
		$CheckParam = FALSE;
	}
	$NumParam++;
}

KLSendEmail("SendPackagingFromGudang", "ShortConfirmation", $LocationNameTo, $EmailText);

DB_Txn_Begin();

$SQL = "UPDATE locations 
		SET klemaillastpackacgingtransfer = CURRENT_DATE
		WHERE loccode =	'" . $_GET['To'] . "'";
$ErrMsg =_('Could not update the date of the last packaging transfer reminder because');
$Result = DB_query($SQL,$ErrMsg);
prnMsg("Updated date of email for packaging transfer to shop to today");

DB_Txn_Commit();

 
 
include('includes/footer.php');

?>