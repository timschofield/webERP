<?php
/* $Revision: 1.7 $ */

include('includes/DefineSerialItems.php');
include('includes/DefineStockTransfers.php');

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Transfer Controlled Items');

/* Session started in session.inc for password checking and authorisation level check */

include('includes/header.inc');


if (!isset($_SESSION['Transfer'])) {
	/* This page can only be called when a stock Transfer is pending */
	echo '<CENTER><A HREF="' . $rootpath . '/StockTransfers.php?' . SID . '&NewTransfer=Yes">'._('Enter A Stock Transfer').'</A><br>';
	prnMsg( _('This page can only be opened if a Stock Transfer for a Controlled Item has been initiated').'<BR>','error');
	echo '</CENTER>';
	include('includes/footer.inc');
	exit;
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
		echo '<CENTER><A HREF="' . $rootpath . '/StockLocTransferReceive.php?' . SID . '>'._('Receive A Stock Transfer').'</A></CENTER>';
	} else {
		echo '<CENTER><A HREF="' . $rootpath . '/StockTransfers.php?' . SID . '&NewTransfer=Yes">'._('Enter A Stock Transfer').'</A></CENTER>';
	}
	prnMsg('<BR>'. _('Notice') . ' - ' . _('The transferred item must be defined as controlled to require input of the batch numbers or serial numbers being transferred'),'error');
	include('includes/footer.inc');
	exit;
}

echo '<CENTER>';

if (isset($TransferItem)){

	echo _('Transfer Items is set equal to') . ' ' . $TransferItem;
	
	echo '<br><a href="'.$rootpath.'/StockLocTransferReceive.php?'  . SID . '">'._('Back To Transfer Screen').'</A>';
} else {
	echo '<br><a href="'.$rootpath.'/StockTransfers.php?'  . SID . '">'._('Back To Transfer Screen').'</A>';
}

echo '<br><FONT SIZE=2><B>'. _('Transfer of controlled item'). ' ' . $LineItem->StockID  . ' - ' . $LineItem->ItemDescription . '</B></FONT>';

/** vars needed by InputSerialItem : **/
$LocationOut = $_SESSION['Transfer']->StockLocationFrom;
$ItemMustExist = true;
$StockID = $LineItem->StockID;
$InOutModifier=1; //seems odd, but it's correct
$ShowExisting = true;
include ('includes/InputSerialItems.php');

/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for adjusting */
$LineItem->Quantity = $TotalQuantity;

/*Also a multi select box for adding bundles to the Transfer without keying */

include('includes/footer.inc');
exit;
?>
