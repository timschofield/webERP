<?php
/* $Revision: 1.13 $ */
include('includes/DefinePOClass.php');
include('includes/DefineSerialItems.php');

$PageSecurity = 11;

include('includes/session.inc');

$title = _('Receive Controlled Items');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/header.inc');

if (!isset($_SESSION['PO'])) {
	/* This page can only be called with a purchase order number for receiving*/
	echo '<CENTER><A HREF="' . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '">'.
		_('Select a purchase order to receive'). '</A></CENTER><br>';
	prnMsg( _('This page can only be opened if a purchase order and line item has been selected') . '. ' . _('Please do that first'),'error');
	include('includes/footer.inc');
	exit;
}

if ($_GET['LineNo']>0){
	$LineNo = $_GET['LineNo'];
} else if ($_POST['LineNo']>0){
	$LineNo = $_POST['LineNo'];
} else {
	echo '<CENTER><A HREF="' . $rootpath . '/GoodsReceived.php?' . SID . '">'.
		_('Select a line Item to Receive').'</A></CENTER>';
	prnMsg( _('This page can only be opened if a Line Item on a PO has been selected') . '. ' . _('Please do that first'), 'error');
	include( 'includes/footer.inc');
	exit;
}

global $LineItem;
$LineItem = &$_SESSION['PO']->LineItems[$LineNo];

if ($LineItem->Controlled !=1 ){ /*This page only relavent for controlled items */

	echo '<CENTER><A HREF="' . $rootpath . '/GoodsReceived.php?' . SID . '">'.
		_('Back to the Purchase Order'). '</A></CENTER>';
	prnMsg( _('The line being recevied must be controlled as defined in the item defintion'), 'error');
	include('includes/footer.inc');
	exit;
}

/********************************************
  Get the page going....
********************************************/
echo '<CENTER>';

echo '<BR><A HREF="'.$rootpath.'/GoodsReceived.php?' . SID . '">'. _('Back To Purchase Order'). ' # '. $_SESSION['PO']->OrderNo . '</a>';

echo '<BR><FONT SIZE=2><B>'. _('Receive controlled item'). ' '. $LineItem->StockID  . ' - ' . $LineItem->ItemDescription .
	' ' . _('on order') . ' ' . $_SESSION['PO']->OrderNo . ' ' . _('from') . ' ' . $_SESSION['PO']->SupplierName . '</B></FONT>';

/** vars needed by InputSerialItem : **/
$LocationOut = $_SESSION['PO']->Location;
$ItemMustExist = false;
$StockID = $LineItem->StockID;
$InOutModifier=1;
$ShowExisting = false;
include ('includes/InputSerialItems.php');

//echo '<BR><INPUT TYPE=SUBMIT NAME=\'AddBatches\' VALUE=\'Enter\'><BR>';
//echo '</CENTER>';
/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for dispatch */
$_SESSION['PO']->LineItems[$LineItem->LineNo]->ReceiveQty = $TotalQuantity;

include( 'includes/footer.inc');
?>
