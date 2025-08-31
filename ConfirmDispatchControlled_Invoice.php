<?php

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineCartClass.php');
include('includes/DefineSerialItems.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Specify Dispatched Controlled Items');
$ViewTopic = 'ARTransactions';
$BookMark = 'ConfirmInvoice';
include('includes/header.php');

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other order entry sessions on the same machine  */
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}

if (isset($_GET['LineNo'])){
        $LineNo = (int)$_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
        $LineNo = (int)$_POST['LineNo'];
} else {
	echo '<div class="centre">
			<a href="' . $RootPath . '/ConfirmDispatch_Invoice.php">' .  __('Select a line item to invoice') . '</a>
			<br />
			<br />';
	prnMsg( __('This page can only be opened if a line item on a sales order to be invoiced has been selected') . '. ' . __('Please do that first'),'error');
	echo '</div>';
	include('includes/footer.php');
	exit();
}

if (!isset($_SESSION['Items'.$identifier]) OR !isset($_SESSION['ProcessingOrder'])) {
	/* This page can only be called with a sales order number to invoice */
	echo '<div class="centre">
			<a href="' . $RootPath . '/SelectSalesOrder.php">' .  __('Select a sales order to invoice') . '</a>
			<br />';
	prnMsg( __('This page can only be opened if a sales order and line item has been selected Please do that first'),'error');
	echo '</div>';
	include('includes/footer.php');
	exit();
}


/*Save some typing by referring to the line item class object in short form */
$LineItem = &$_SESSION['Items'.$identifier]->LineItems[$LineNo];


//Make sure this item is really controlled
if ( $LineItem->Controlled != 1 ){
	echo '<div class="centre"><a href="' . $RootPath . '/ConfirmDispatch_Invoice.php">' .  __('Back to the Sales Order'). '</a></div>';
	echo '<br />';
	prnMsg( __('The line item must be defined as controlled to require input of the batch numbers or serial numbers being sold'),'error');
	include('includes/footer.php');
	exit();
}

/********************************************
  Get the page going....
********************************************/
echo '<div class="centre">';

echo '<br /><a href="'. $RootPath. '/ConfirmDispatch_Invoice.php?identifier=' . $identifier . '">' .  __('Back to Confirmation of Dispatch') . '/' . __('Invoice'). '</a>';

echo '<br /><b>' .  __('Dispatch of up to').' '. locale_number_format($LineItem->Quantity-$LineItem->QtyInv, $LineItem->DecimalPlaces). ' '. __('Controlled items').' ' . $LineItem->StockID  . ' - ' . $LineItem->ItemDescription . ' '. __('on order').' ' . $_SESSION['Items'.$identifier]->OrderNo . ' '. __('to'). ' ' . $_SESSION['Items'.$identifier]->CustomerName . '</b></div>';

/** vars needed by InputSerialItem : **/
$StockID = $LineItem->StockID;
$RecvQty = $LineItem->Quantity-$LineItem->QtyInv;
$ItemMustExist = true;  /*Can only invoice valid batches/serial numbered items that exist */
$LocationOut = $_SESSION['Items'.$identifier]->Location;

if ($_SESSION['RequirePickingNote'] == 1) {
	$OrderstoPick = $_SESSION['Items'.$identifier]->OrderNo;
} else {
	unset($OrderstoPick);
}

$InOutModifier=1;
$ShowExisting=true;

include('includes/InputSerialItems.php');

/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for dispatch */
$_SESSION['Items'.$identifier]->LineItems[$LineNo]->QtyDispatched = $TotalQuantity;

include('includes/footer.php');
