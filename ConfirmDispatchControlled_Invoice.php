<?php
/* $Revision: 1.5 $ */
$title = "Specifiy Dispatched Controlled Items";
$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */
include("includes/DefineCartClass.php");
include("includes/DefineSerialItems.php");
include("includes/session.inc");
include("includes/header.inc");

if (isset($_GET['StockID'])){
	$StockID = $_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID = $_POST['StockID'];
} else {
	echo "<CENTER><A HREF='" . $rootpath . "/ConfirmDispatch_Invoice.php?" . SID . "'>Select a line Item to Receive</A><br>";
	echo "<BR><B>Error:</B>This page can only be opened if a Line Item on a Sales Order has been selected. Please do that first.<BR>";
	echo "</CENTER>";
	include("includes/footer.inc");
	exit;
}

if (!isset($_SESSION['Items']) OR !isset($_SESSION['ProcessingOrder'])) {
	/* This page can only be called with a sales order number to invoice */
	echo "<CENTER><A HREF='" . $rootpath . "/SelectSalesOrder.php?" . SID . "'>Select a sales order to invoice</A><<br>";
	prnMsg("This page can only be opened if a purchase order and line item has been selected. Please do that first.<BR>","error");
	echo "</CENTER>";
	include("includes/footer.inc");
	exit;
}


/*Save some typing by referring to the line item class object in short form */
$LineItem = &$_SESSION['Items']->LineItems[$StockID];

//Make sure this item is really controlled
if ( $LineItem->Controlled != 1 ){
	echo "<CENTER><A HREF='" . $rootpath . "/ConfirmDispatch_Invoice.php?" . SID . "'>Back to The Sales Order</A></CENTER>";
	prnMsg("<BR>Notice - The line item must be defined as controlled to require input of the batch numbers or serial numbers being sold","error");
	include("includes/footer.inc");
	exit;
}

$LocationOut = $_SESSION['Items']->Location;

include ("includes/Add_SerialItemsOut.php");


echo "<CENTER><FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

echo "<INPUT TYPE=HIDDEN NAME='StockID' VALUE=$StockID>";

echo "<br><a href='$rootpath/ConfirmDispatch_Invoice.php?" . SID . "'>Back To Confirmation of Dispatch/Invoice</a>";

echo "<br><FONT SIZE=2><B>Dispatch of controlled item " . $LineItem->StockID  . " - " . $LineItem->ItemDescription . " on order " . $_SESSION['Items']->OrderNo . " to " . $_SESSION['Items']->CustomerName . "</B></FONT>";



include ("includes/InputSerialItems.php");


/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for dispatch */
$_SESSION['Items']->LineItems[$LineItem->StockID]->QtyDispatched = $TotalQuantity;

/*Also a multi select box for adding bundles to the dispatch without keying */

$sql = "SELECT SerialNo, Quantity FROM StockSerialItems WHERE StockID='" . $StockID . "' AND LocCode ='" . $_SESSION['Items']->Location . "' AND Quantity > 0";

$Bundles = DB_query($sql,$db, "<BR>Could not retrieve the items for $StockID");
if (DB_num_rows($Bundles)>0){

	echo "<TD VALIGN=TOP><B>Select Existing Items</B><BR>";
	echo "<SELECT Name=Bundles[] multiple>";

	$id=0;

	while ($myrow=DB_fetch_array($Bundles,$db)){

		echo "<OPTION VALUE=" . $myrow["SerialNo"] . ">" . $myrow["SerialNo"];
		if ($LineItem->Serialised==0){
			echo " - " . $myrow['Quantity'];
		}
	}

	echo "</SELECT></TD>";
}

echo "</TR></TABLE>";

echo "<br><INPUT TYPE=SUBMIT NAME='AddBatches' VALUE='Enter'><BR>";

include("includes/footer.inc");
exit;
?>