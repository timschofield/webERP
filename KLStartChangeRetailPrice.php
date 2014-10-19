<?php

include ('includes/session.inc');
$Title = _('Kapal-Laut. Start Process Change Retail Price');
include('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				_('retail Price') . '" alt="" />' . ' ' . _('KL Start the Process of Change Retail and Wholesale Prices for').' ' . $_GET['Item']. '.</p>';

if (!isset($_GET['Item']) or !isset($_GET['NewPrice'])){
	echo '<br />';
	prnMsg( _('This page must be given the item code and its new Retail price.'), 'error');
	include('includes/footer.inc');
	exit;
}

DB_Txn_Begin($db);

$sql = "INSERT INTO klchangeprice 
				(stockid,
				startprocessdate,
				newretailprice,
				endprocessdate)
		VALUES ('" . $_GET['Item'] . "',
			'" . Date('Y-m-d') . "',
			'" . filter_number_format($_GET['NewPrice']) . "',
			'0000-00-00')";
$msg = _('KL Retail Price Change Step 01 record for') . ' ' . $_GET['Item'] . ' ' . _('has been created');
$ErrMsg = _('The insert or update of the KL Retail Price Change Step 01 failed because');
$DbgMsg = _('The SQL that was used and failed was');
$result = DB_query($sql,$db,$ErrMsg, $DbgMsg);
prnMsg($msg , 'success');

SetRLZeroAtPointOfSales($_GET['Item'], $db);
SetChangePriceFlag(1, $_GET['Item'], $db);
KLSendEmail("ChangePriceStarted", "Silent", $_GET['Item']);

DB_Txn_Commit($db);

include('includes/footer.inc');

?>