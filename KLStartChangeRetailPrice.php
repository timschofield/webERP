<?php

require(__DIR__ . '/includes/session.php');

$Title = __('KL Start Process Change Retail Price');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLBoards.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLPrices.php');
include(__DIR__ . '/includes/KLEmails.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				__('Retail Price') . '" alt="" />' . ' ' . __('KL Start the Process of Change Retail Prices for').' ' . $_GET['Item']. '.</p>';

if (!isset($_GET['Item']) or !isset($_GET['NewPrice'])){
	echo '<br />';
	prnMsg( __('This page must be given the item code and its new Retail price.'), 'error');
	include(__DIR__ . '/includes/footer.php');
	exit();
} elseif ((ItemCodeQOH($_GET['Item'],'CODE_FULL', "ALL") != 0) AND (GetTotalItemsChangingPrice() >= $_SESSION['MaxItemsChangingPrice']) AND (!$KL_SystemAdmin)) {
	echo '<br />';
	prnMsg(__('Too many items changing price at the same time. Maximum = '). $_SESSION['MaxItemsChangingPrice'],'error');
	include(__DIR__ . '/includes/footer.php');
	exit();
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
$Msg = __('KL Retail Price Change Step 01 record for') . ' ' . $_GET['Item'] . ' ' . __('has been created');
$ErrMsg = __('The insert or update of the KL Retail Price Change Step 01 failed because');
$Result = DB_query($SQL,$ErrMsg);
prnMsg($Msg , 'success');

SetRLZeroAtPointOfSales($_GET['Item']);
SetChangePriceFlag(1, $_GET['Item']);
KLSendEmail("ChangePriceStarted", "Silent", $_GET['Item']);

DB_Txn_Commit();

include(__DIR__ . '/includes/footer.php');
