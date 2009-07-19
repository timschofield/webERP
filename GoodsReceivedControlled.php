<?php
/* $Revision: 1.15 $ */
include('includes/DefinePOClass.php');
include('includes/DefineSerialItems.php');

$PageSecurity = 11;

include('includes/session.inc');

$title = _('Receive Controlled Items');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/header.inc');

if (!isset($_SESSION['PO'])) {
	/* This page can only be called with a purchase order number for receiving*/
	echo '<div class="centre"><a href="' . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '">'.
		_('Select a purchase order to receive'). '</a></div><br>';
	prnMsg( _('This page can only be opened if a purchase order and line item has been selected') . '. ' . _('Please do that first'),'error');
	include('includes/footer.inc');
	exit;
}

if ($_GET['LineNo']>0){
	$LineNo = $_GET['LineNo'];
} else if ($_POST['LineNo']>0){
	$LineNo = $_POST['LineNo'];
} else {
	echo '<div class="centre"><a href="' . $rootpath . '/GoodsReceived.php?' . SID . '">'.
		_('Select a line Item to Receive').'</a></div>';
	prnMsg( _('This page can only be opened if a Line Item on a PO has been selected') . '. ' . _('Please do that first'), 'error');
	include( 'includes/footer.inc');
	exit;
}

global $LineItem;
$LineItem = &$_SESSION['PO']->LineItems[$LineNo];

if ($LineItem->Controlled !=1 ){ /*This page only relavent for controlled items */

	echo '<div class="centre"><a href="' . $rootpath . '/GoodsReceived.php?' . SID . '">'.
		_('Back to the Purchase Order'). '</a></div>';
	prnMsg( _('The line being received must be controlled as defined in the item definition'), 'error');
	include('includes/footer.inc');
	exit;
}

/********************************************
  Get the page going....
********************************************/
echo '<div class="centre">';

echo '<br><a href="'.$rootpath.'/GoodsReceived.php?' . SID . '">'. _('Back To Purchase Order'). ' # '. $_SESSION['PO']->OrderNo . '</a>';

echo '<br><font size=2><b>'. _('Receive controlled item'). ' '. $LineItem->StockID  . ' - ' . $LineItem->ItemDescription .
	' ' . _('on order') . ' ' . $_SESSION['PO']->OrderNo . ' ' . _('from') . ' ' . $_SESSION['PO']->SupplierName . '</b></font></div>';

/** vars needed by InputSerialItem : **/
$LocationOut = $_SESSION['PO']->Location;
$ItemMustExist = false;
$StockID = $LineItem->StockID;
$InOutModifier=1;
$ShowExisting = false;
include ('includes/InputSerialItems.php');

//echo '<br><input type=submit name=\'AddBatches\' VALUE=\'Enter\'><br>';

/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for dispatch */
$_SESSION['PO']->LineItems[$LineItem->LineNo]->ReceiveQty = $TotalQuantity;

include( 'includes/footer.inc');
?>