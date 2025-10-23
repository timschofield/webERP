<?php

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineCartClass.php');
include('includes/DefineSerialItems.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Specify Picked Controlled Items');
include('includes/header.php');

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other order entry sessions on the same machine  */
	$identifier = date('U');
} else {
	$identifier = $_GET['identifier'];
}

if (isset($_GET['LineNo'])) {
	$LineNo = (int) $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])) {
	$LineNo = (int) $_POST['LineNo'];
} else {
	echo '<div class="centre">
			<a href="' . $RootPath . '/PickingLists.php">' . __('Select a pick list line to process') . '</a>
		</div>';
	prnMsg(__('This page can only be opened if a pick list has been selected') . '. ' . __('Please do that first'), 'error');
	include('includes/footer.php');
	exit();
}

if (!isset($_SESSION['Items' . $identifier]) OR !isset($_SESSION['ProcessingPick'])) {
	/* This page can only be called with a sales order number to invoice */
	echo '<div class="centre">
			<a href="' . $RootPath . '/SelectPickingLists.php">' . __('Select a a pick List to maintain') . '</a>
		</div>';
	prnMsg(__('This page can only be opened if a pick list has been selected'), 'error');
	include('includes/footer.php');
	exit();
}


/*Save some typing by referring to the line item class object in short form */
$LineItem =& $_SESSION['Items' . $identifier]->LineItems[$LineNo];


//Make sure this item is really controlled
if ($LineItem->Controlled != 1) {
	echo '<div class="centre"><a href="' . $RootPath . '/PickingLists.php">' . __('Back to the Sales Order') . '</a></div>';
	prnMsg(__('The line item must be defined as controlled to require input of the batch numbers or serial numbers being sold'), 'error');
	include('includes/footer.php');
	exit();
}

/********************************************
Get the page going....
********************************************/
echo '<div class="centre">';

echo '<br /><a href="' . $RootPath . '/PickingLists.php?identifier=' . $identifier . '">' . __('Back to Picking List') . '</a>';

echo '<br /><b>' . __('Dispatch of up to') . ' ' . locale_number_format($LineItem->Quantity - $LineItem->QtyInv, $LineItem->DecimalPlaces) . ' ' . __('Controlled items') . ' ' . $LineItem->StockID . ' - ' . $LineItem->ItemDescription . ' ' . __('on Picklist') . ' ' . $_SESSION['Items' . $identifier]->OrderNo . ' ' . __('to') . ' ' . $_SESSION['Items' . $identifier]->CustomerName . '</b></div>';

/** vars needed by InputSerialItem : **/
$StockID = $LineItem->StockID;
$RecvQty = $LineItem->Quantity - $LineItem->QtyInv;
$ItemMustExist = true;

/*Can only invoice valid batches/serial numbered items that exist */
$LocationOut = $_SESSION['Items' . $identifier]->Location;
if ($_SESSION['RequirePickingNote'] == 1) {
	$OrderstoPick = $_SESSION['Items' . $identifier]->OrderNo;
} else {
	unset($OrderstoPick);
}

$InOutModifier = 1;
$ShowExisting = true;

include('includes/InputSerialItems.php');

/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for dispatch */
$_SESSION['Items' . $identifier]->LineItems[$LineNo]->QtyDispatched = $TotalQuantity;

include('includes/footer.php');
