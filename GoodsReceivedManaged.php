<?php
/* $Revision: 1.1 $ */
include('includes/DefinePOClass.php');

$title = _('Receive Warehouse Managed Items');
$PageSecurity = 11;
include('includes/session.inc');

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

if ($_SESSION['PO']->Managed !=1 ){ /*This page only relavent for managed locations */

	echo '<CENTER><A HREF="' . $rootpath . '/GoodsReceived.php?' . SID . '">'.
		_('Back to the Purchase Order'). '</A></CENTER>';
	prnMsg( _('The line being recevied must be part of warehouse managed location'), 'error');
	include('includes/footer.inc');
	exit;
}

/********************************************
  Get the page going....
********************************************/
echo '<CENTER>';

echo '<BR><FONT SIZE=2><B>'. _('Receive item'). ' '. $LineItem->StockID  . ' - ' . $LineItem->ItemDescription .
	' ' . _('on order') . ' ' . $_SESSION['PO']->OrderNo . ' ' . _('from') . ' ' . $_SESSION['PO']->SupplierName . ' ' . _('into warehouse bin') . '</B></FONT>';

echo '<FORM METHOD="POST" ACTION="'.$rootpath.'/GoodsReceived.php?' . SID . '">';
echo '<INPUT TYPE=HIDDEN NAME="LineNo" VALUE=' . $LineNo . '>';
echo "<INPUT TYPE=HIDDEN NAME='StockID' VALUE='$LineItem->StockID'>";
echo '<CENTER><TABLE BORDER=1><TR><TD>';

$sql = "SELECT binid FROM bins WHERE loccode='".$_SESSION['PO']->Location."'";

echo "<SELECT NAME='BinID'>";

$BinResult = DB_query($sql,$db);

while ($myrow=DB_fetch_array($BinResult)){
	if ($LineItem->BinID==$myrow['binid']){
		echo "<OPTION SELECTED VALUE=" . $myrow['binid'] . ">" . $myrow['binid'];
	} else {
		echo "<OPTION VALUE=" . $myrow['binid'] . ">" . $myrow['binid'];
	}
}

echo '</SELECT></TD></TR><TR><TD ALIGN=CENTER>';
echo '<input type=submit value="'. _('Set Bin'). '">';
echo '</TD></TR></TABLE>';
echo '</FORM>';

/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for dispatch */
$_SESSION['PO']->LineItems[$LineItem->LineNo]->ReceiveQty = $TotalQuantity;

include( 'includes/footer.inc');
?>
