<?php

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineSerialItems.php');
include('includes/DefineStockTransfers.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Transfer Controlled Items');
$ViewTopic = 'Inventory';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Inventory') . '" alt="" /><b>' . $Title . '</b></p>';

if (!isset($_SESSION['Transfer'])) {
	/* This page can only be called when a stock Transfer is pending */
	echo '<div class="centre"><a href="' . $RootPath . '/StockTransfers.php?NewTransfer=Yes">' . __('Enter A Stock Transfer') . '</a><br />';
	prnMsg( __('This page can only be opened if a Stock Transfer for a Controlled Item has been initiated'),'error');
	echo '</div>';
	include('includes/footer.php');
	exit();
}


if (isset($_GET['TransferItem'])){
	$TransferItem = $_GET['TransferItem'];
	$_SESSION['TransferItem'] = $_GET['TransferItem'];
} elseif (isset($_SESSION['TransferItem'])){
	$TransferItem = $_SESSION['TransferItem'];
}


/*Save some typing by referring to the line item class object in short form */
if (isset($TransferItem)){ /*we are in a bulk transfer */
	$LineItem = &$_SESSION['Transfer']->TransferItem[$TransferItem];
} else { /*we are in an individual transfer */
	$LineItem = &$_SESSION['Transfer']->TransferItem[0];
}

//Make sure this item is really controlled
if ($LineItem->Controlled != 1 ){
	if (isset($TransferItem)){
		echo '<div class="centre"><a href="' . $RootPath . '/StockLocTransferReceive.php>' . __('Receive A Stock Transfer') . '</a></div>';
	} else {
		echo '<div class="centre"><a href="' . $RootPath . '/StockTransfers.php?NewTransfer=Yes">' . __('Enter A Stock Transfer') . '</a></div>';
	}
	prnMsg(__('Notice') . ' - ' . __('The transferred item must be defined as controlled to require input of the batch numbers or serial numbers being transferred'),'error');
	include('includes/footer.php');
	exit();
}

echo '<div class="centre">';

if (isset($TransferItem)){

	echo __('Transfer Items is set equal to') . ' ' . $TransferItem;

	echo '<br />
			<a href="'.$RootPath.'/StockLocTransferReceive.php?StockID='.$LineItem->StockID.'">' . __('Back To Transfer Screen') . '</a>';
} else {
	echo '<br />
			<a href="'.$RootPath.'/StockTransfers.php?StockID='.$LineItem->StockID. '">' . __('Back To Transfer Screen') . '</a>';
}

echo '<br />
	<font size="2"><b>' .  __('Transfer of controlled item'). ' ' . $LineItem->StockID  . ' - ' . $LineItem->ItemDescription . '</b></font>
	</div>';

/** vars needed by InputSerialItem : **/
$LocationOut = $_SESSION['Transfer']->StockLocationFrom;
$ItemMustExist = true;
$StockID = $LineItem->StockID;
$InOutModifier=1;
$ShowExisting = true;
if (isset($TransferItem)){
	$LineNo=$TransferItem;
} else {
	$LineNo=0;
}

include('includes/InputSerialItems.php');

/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for adjusting */
$LineItem->Quantity = $TotalQuantity;

/*Also a multi select box for adding bundles to the Transfer without keying */

include('includes/footer.php');
