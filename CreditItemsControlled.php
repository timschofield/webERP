<?php
/* $Revision: 1.9 $ */
include('includes/DefineCartClass.php');
include('includes/DefineSerialItems.php');
$PageSecurity = 3;
include('includes/session.inc');

$title = _('Specify Credited Controlled Items');

/* Session started in header.inc for password checking and authorisation level check */
include('includes/header.inc');


if ($_GET['CreditInvoice']=='Yes' || $_POST['CreditInvoice']=='Yes'){
	$_SESSION['CreditInv']=true;
} else {
	$_SESSION['CreditInv']=false;
}
if ($_SESSION['CreditInv']){
	$CreditLink = 'Credit_Invoice.php';
} else {
	$CreditLink = 'SelectCreditItems.php';
}


if (isset($_GET['LineNo'])){
        $LineNo = $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
        $LineNo = $_POST['LineNo'];
} else { 
	echo '<CENTER><A HREF="' . $rootpath . '/' . $CreditLink . '?' . SID . '">'. _('Select Credit Items'). '</A><BR><BR>';
	prnMsg( _('This page can only be opened if a Line Item on a credit note has been selected.') . ' ' . _('Please do that first'), 'error');
	echo '</CENTER>';
	include('includes/footer.inc');
	exit;
}



if (!isset($_SESSION['CreditItems'])) {
	/* This page can only be called with a credit note entry part entered */
	echo '<CENTER><A HREF="' . $rootpath . '/' . $CreditLink . '?' . SID . '">'. _('Select Credit Items'). '</A><BR><BR>';
	prnMsg( _('This page can only be opened if a controlled credit note line item has been selected.') . ' ' . _('Please do that first'),'error');
	echo '</CENTER>';
	include('includes/footer.inc');
	exit;
}


/*Save some typing by referring to the line item class object in short form */
$LineItem = &$_SESSION['CreditItems']->LineItems[$LineNo];

//Make sure this item is really controlled
if ( $LineItem->Controlled != 1 ){
	echo '<CENTER><A HREF="' . $rootpath . '/' . $CreditLink . '?' . SID . '">'. _('Back to Credit Note Entry').'</A></CENTER>';
	echo '<BR>';
	prnMsg( _('Notice') . ' - ' . _('The line item must be defined as controlled to require input of the batch numbers or serial numbers being credited'),'warn');
	include('includes/footer.inc');
	exit;
}

/*Now add serial items entered - there is debate about whether or not to validate these entries against
previous sales to the customer - so that only serial items that previously existed can be credited from the customer. However there are circumstances that could warrant crediting items which were never sold to the
customer - a bad debt recovery, or a contra for example. Also older serial items may have been purged */
if (isset($_GET['Delete'])){
	unset($LineItem->SerialItems[$_GET['Delete']]);
}

echo '<CENTER>';

if ($CreditLink == 'Credit_Invoice.php'){
	echo '<INPUT TYPE=HIDDEN NAME="CreditInvoice" VALUE="Yes">';
}

echo '<BR><A HREF="' . $rootpath . '/' . $CreditLink . '?' . SID . '">'. _('Back to Credit Note Entry'). '</A>';

echo '<BR><FONT SIZE=2><B>'. _('Credit of Controlled Item'). ' ' . $LineItem->StockID  . ' - ' . $LineItem->ItemDescription . ' '. _('from') .' '. $_SESSION['CreditItems']->CustomerName . '</B></FONT>';

/** vars needed by InputSerialItem : **/
$LocationOut = $_SESSION['CreditItems']->Location;
/* $_SESSION['CreditingControlledItems_MustExist'] is in config.php - Phil and Jesse disagree on the default treatment
compromise position make it user configurable */
$ItemMustExist = $_SESSION['CreditingControlledItems_MustExist'];
$StockID = $LineItem->StockID;
$InOutModifier=1;
$ShowExisting = false;
include ('includes/InputSerialItems.php');

echo '</TR></TABLE>';

/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for dispatch */
if ($CreditLink == 'Credit_Invoice.php'){
	$_SESSION['CreditItems']->LineItems[$LineNo]->QtyDispatched = $TotalQuantity;
} else {
	$_SESSION['CreditItems']->LineItems[$LineNo]->Quantity = $TotalQuantity;
}

include('includes/footer.inc');
exit;
?>
