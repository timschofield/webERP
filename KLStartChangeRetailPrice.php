<?php

include ('includes/session.php');
$Title = _('KL Start Process Change Retail Price');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				_('Retail Price') . '" alt="" />' . ' ' . _('KL Start the Process of Change Retail Prices for').' ' . $_GET['Item']. '.</p>';

if (!isset($_GET['Item']) or !isset($_GET['NewPrice'])){
	echo '<br />';
	prnMsg( _('This page must be given the item code and its new Retail price.'), 'error');
	include('includes/footer.php');
	exit;
}elseif ((ItemCodeQOH($_GET['Item'],'CODE_FULL', "ALL") != 0) AND (GetTotalItemsChangingPrice() >= MAX_ITEMS_CHANGING_PRICE) AND (!$KL_SystemAdmin)) {
	echo '<br />';
	prnMsg(_('Too many items changing price at the same time. Maximum = '). MAX_ITEMS_CHANGING_PRICE,'error');
	include('includes/footer.php');
	exit;
}

DB_Txn_Begin();

$SQL = "INSERT INTO klchangeprice 
				(stockid,
				startprocessdate,
				newretailprice,
				endprocessdate)
		VALUES ('" . $_GET['Item'] . "',
			CURRENT_DATE,
			'" . filter_number_format($_GET['NewPrice']) . "',
			'1000-01-01')";
$Msg = _('KL Retail Price Change Step 01 record for') . ' ' . $_GET['Item'] . ' ' . _('has been created');
$ErrMsg = _('The insert or update of the KL Retail Price Change Step 01 failed because');
$DbgMsg = _('The SQL that was used and failed was');
$Result = DB_query($SQL,$ErrMsg, $DbgMsg);
prnMsg($Msg , 'success');

SetRLZeroAtPointOfSales($_GET['Item']);
SetChangePriceFlag(1, $_GET['Item']);
KLSendEmail("ChangePriceStarted", "Silent", $_GET['Item']);

DB_Txn_Commit();

include('includes/footer.php');

?>