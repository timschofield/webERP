<?php
/* $Revision: 1.1 $ */
$title = "Specifiy Credited Controlled Items";
$PageSecurity = 3;

/* Session started in header.inc for password checking and authorisation level check */
include("includes/DefineCartClass.php");
include("includes/DefineSerialItems.php");
include("includes/session.inc");
include("includes/header.inc");


if ($_GET['CreditInvoice']=="Yes" OR $_POST['CreditInvoice']=="Yes"){
	$CreditLink = "Credit_Invoice.php";
} else {
	$CreditLink = "SelectCreditItems.php";
}

if (isset($_GET['StockID'])){
	$StockID = $_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID = $_POST['StockID'];
} else {
	echo "<CENTER><A HREF='" . $rootpath . "/" . $CreditLink . "?" . SID . "'>Select Credit Items</A><br>";
	echo "<BR><B>Error:</B>This page can only be opened if a Line Item on a credit note has been selected. Please do that first.<BR>";
	echo "</CENTER>";
	include("includes/footer.inc");
	exit;
}



if (!isset($_SESSION['CreditItems'])) {
	/* This page can only be called with a credit note entry part entered */
	echo "<CENTER><A HREF='" . $rootpath . "/" . $CreditLink . "?" . SID . "'>Select items to credit</A><<br>";
	prnMsg("This page can only be opened if a controlled credit note line item has been selected. Please do that first.<BR>","error");
	echo "</CENTER>";
	include("includes/footer.inc");
	exit;
}


/*Save some typing by referring to the line item class object in short form */
$LineItem = &$_SESSION['CreditItems']->LineItems[$StockID];

//Make sure this item is really controlled
if ( $LineItem->Controlled != 1 ){
	echo "<CENTER><A HREF='" . $rootpath . "/" . $CreditLink . "?" . SID . "'>Back to The Credit note entry</A></CENTER>";
	prnMsg("<BR>Notice - The line item must be defined as controlled to require input of the batch numbers or serial numbers being credited","error");
	include("includes/footer.inc");
	exit;
}

/*Now add serial items entered - there is debate about whether or not to validate these entries against
previous sales to the customer - so that only serial items that previously existed can be credited from the customer. However there are circumstances that could warrant crediting items which were never sold to the
customer - a bad debt recovery, or a contra for example. Also older serial items may have been purged */

if ($_POST['AddBatches']=='Enter'){

	for ($i=0;$i < 10;$i++){
		if(strlen($_POST['SerialNo' . $i]) >0 AND strlen($_POST['SerialNo' . $i]) <21 AND is_numeric($_POST['Qty' .$i])){

			$LineItem->SerialItems[$_POST['SerialNo' . $i]] = new SerialItem($_POST['SerialNo' . $i], $_POST['Qty' . $i]);

		} /* end if posted [Serialno . i] is not blank */

	} /* end of the loop aroung the form input fields */

} /*end if the user hit the enter button */

if (isset($_GET['Delete'])){
	unset($LineItem->SerialItems[$_GET['Delete']]);
}


echo "<CENTER><FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

echo "<INPUT TYPE=HIDDEN NAME='StockID' VALUE=$StockID>";

if ($CreditLink == "Credit_Invoice.php"){
	echo "<INPUT TYPE=HIDDEN NAME='CreditInvoice' VALUE=Yes>";
}

echo "<br><a href='" . $rootpath . "/" . $CreditLink . "?" . SID . "'>Back To Credit Note Entry</a>";

echo "<br><FONT SIZE=2><B>Credit of Controlled Item " . $LineItem->StockID  . " - " . $LineItem->ItemDescription . " from " . $_SESSION['Items']->CustomerName . "</B></FONT>";



include ("includes/InputSerialItems.php");

echo "</TR></TABLE>";

/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for dispatch */
if ($CreditLink = "Credit_Invoice.php"){
	$_SESSION['CreditItems']->LineItems[$StockID]->QtyDispatched = $TotalQuantity;
} else {
	$_SESSION['CreditItems']->LineItems[$StockID]->Quantity = $TotalQuantity;
}

echo "<br><INPUT TYPE=SUBMIT NAME='AddBatches' VALUE='Enter'><BR>";



include("includes/footer.inc");
exit;


?>

