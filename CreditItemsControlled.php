<?php
/* $Revision: 1.5 $ */
include('includes/DefineCartClass.php');
include('includes/DefineSerialItems.php');

$title = _('Specifiy Credited Controlled Items');
$PageSecurity = 3;
include('includes/session.inc');

/* Session started in header.inc for password checking and authorisation level check */
include('includes/header.inc');


if ($_GET['CreditInvoice']=='Yes' OR $_POST['CreditInvoice']=='Yes'){
	$CreditLink = 'Credit_Invoice.php';
} else {
	$CreditLink = 'SelectCreditItems.php';
}


if (isset($_GET['StockID'])){
	$StockID = $_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID = $_POST['StockID'];
} else {
	echo '<CENTER><A HREF="' . $rootpath . '/' . $CreditLink . '?' . SID . '">'. _('Select Credit Items'). '</A><BR>';
	echo '<BR>';
	prnMsg( _('This page can only be opened if a Line Item on a credit note has been selected') . '. ' . _('Please do that first'), 'error');
	echo '</CENTER>';
	include('includes/footer.inc');
	exit;
}



if (!isset($_SESSION['CreditItems'])) {
	/* This page can only be called with a credit note entry part entered */
	echo '<CENTER><A HREF="' . $rootpath . '/' . $CreditLink . '?' . SID . '">'. _('Select Credit Items'). '</A><BR>';
	echo '<BR>';
	prnMsg( _('This page can only be opened if a controlled credit note line item has been selected') . '. ' . _('Please do that first'),'error');
	echo '</CENTER>';
	include('includes/footer.inc');
	exit;
}


/*Save some typing by referring to the line item class object in short form */
$LineItem = &$_SESSION['CreditItems']->LineItems[$StockID];

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

if (isset($_POST['AddBatches'])){

	for ($i=0;$i < 10;$i++){
		if(strlen($_POST['SerialNo' . $i]) >0 AND strlen($_POST['SerialNo' . $i]) <21 AND is_numeric($_POST['Qty' .$i])){

			$LineItem->SerialItems[$_POST['SerialNo' . $i]] = new SerialItem($_POST['SerialNo' . $i], $_POST['Qty' . $i]);

		} /* end if posted [Serialno . i] is not blank */

	} /* end of the loop aroung the form input fields */

} /*end if the user hit the enter button */

if (isset($_GET['Delete'])){
	unset($LineItem->SerialItems[$_GET['Delete']]);
}

echo '<CENTER><FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<INPUT TYPE=HIDDEN NAME="StockID" VALUE="'.$StockID.'">';

if ($CreditLink == 'Credit_Invoice.php'){
	echo '<INPUT TYPE=HIDDEN NAME="CreditInvoice" VALUE="Yes">';
}

echo '<br><a href="' . $rootpath . '/' . $CreditLink . '?' . SID . '">'. _('Back to Credit Note Entry'). '</a>';

echo '<br><FONT SIZE=2><B>'. _('Credit of Controlled Item'). ' ' . $LineItem->StockID  . ' - ' . $LineItem->ItemDescription . ' '. _('from') .' '. $_SESSION['Items']->CustomerName . '</B></FONT>';

/** vars needed by InputSerialItem : **/
$LocationOut = $_SESSION['Transfer']->StockLocationFrom;
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
	$_SESSION['CreditItems']->LineItems[$StockID]->QtyDispatched = $TotalQuantity;
} else {
	$_SESSION['CreditItems']->LineItems[$StockID]->Quantity = $TotalQuantity;
}

include('includes/footer.inc');
exit;
?>
