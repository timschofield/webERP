<?php
/* $Revision: 1.3 $ */
/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */

$title = "Enter Supplier Invoice Against Goods Received";

$PageSecurity = 5;

include("includes/DefineSuppTransClass.php");
/* Session started in header.inc for password checking and authorisation level check */
include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");

if (!isset($_SESSION['SuppTrans'])){
	echo "<P>To enter a supplier transactions the supplier must first be selected from the supplier selection screen, then the link to enter a supplier invoice must be clicked on.";
	echo "<BR><A HREF='$rootpath/SelectSupplier.php?" . SID ."'>Select A Supplier to Enter a Transaction For</A>";
	exit;
	/*It all stops here if there aint no supplier selected and invoice initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to Invoice button then process this first before showing  all GRNs on the invoice otherwise it wouldnt show the latest addition*/

if ($_POST['AddGRNToTrans']=="Add to Invoice" ){

	$InputError=False;

	if ($_POST['This_QuantityInv'] >= ($_POST['QtyRecd'] - $_POST['Prev_QuantityInv'])){
		$Complete = True;
	} else {
		$Complete = False;
	}
	if ($Check_Qty_Charged_vs_Del_Qty==True) {
		if ($_POST['This_QuantityInv']/($_POST['QtyRecd'] - $_POST['Prev_QuantityInv']) > (1+ ($OverChargeProportion / 100))){
			echo "<P><FONT COLOR=RED SIZE=4><B>Error:<BR></FONT>The quantity being invoiced is more than the outstanding quantity by more than " . $OverChargeProportion . " percent. The system is set up to prohibit this. See the system administrator to modify the set up parameters if necessary.";
			$InputError = True;
		}
	}
	if (!is_numeric($_POST['ChgPrice']) AND $_POST['ChgPrice']<0){
		$InputError = True;
		echo "<P><FONT COLOR=RED SIZE=4><B>Error:<BR></FONT>The price charged in the suppliers currency is either not numeric or negative. The goods received cannot be invoiced at this price.";
	} elseif ($Check_Price_Charged_vs_Order_Price==True) {
		if ($_POST['ChgPrice']/$_POST['OrderPrice'] > (1+ ($OverChargeProportion / 100))){
			echo "<P><FONT COLOR=RED SIZE=4><B>Error:<BR>The price being invoiced is more than the purchase order price by more than " . $OverChargeProportion . "%. The system is set up to prohibit this. See the system administrator to modify the set up parameters if necessary.";
			$InputError = True;
		}
	}

	if ($InputError==False){
		$_SESSION['SuppTrans']->Add_GRN_To_Trans($_POST['GRNNumber'], $_POST['PODetailItem'], $_POST['ItemCode'], $_POST['ItemDescription'], $_POST['QtyRecd'], $_POST['Prev_QuantityInv'], $_POST['This_QuantityInv'], $_POST['OrderPrice'], $_POST['ChgPrice'], $Complete, $_POST['StdCostUnit'], $_POST['ShiptRef'], $_POST['JobRef'], $_POST['GLCode']);
	}
}

if (isset($_GET['Delete'])){

	$_SESSION['SuppTrans']->Remove_GRN_From_Trans($_GET['Delete']);

}




/*Show all the selected GRNs so far from the SESSION['SuppTrans']->GRNs array */

echo "<CENTER><FONT SIZE=4 COLOR=BLUE>Invoiced Goods Received Selected";
echo "<TABLE CELLPADDING=1>";

$tableheader = "<TR BGCOLOR=#800000><TD class='tableheader'>Sequence #</TD><TD class='tableheader'>Item Code</TD><TD class='tableheader'>Description</TD><TD class='tableheader'>Quantity Charged</TD><TD class='tableheader'>Price Charge in " . $_SESSION['SuppTrans']->CurrCode . "</TD><TD class='tableheader'>Line Value in " . $_SESSION['SuppTrans']->CurrCode . "</TD></TR>";

echo $tableheader;

$TotalValueCharged=0;

foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

	echo "<TR><TD>" . $EnteredGRN->GRNNo . "</TD><TD>" . $EnteredGRN->ItemCode . "</TD><TD>" . $EnteredGRN->ItemDescription . "</TD><TD ALIGN=RIGHT>" . number_format($EnteredGRN->This_QuantityInv,2) . "</TD><TD ALIGN=RIGHT>" . number_format($EnteredGRN->ChgPrice,2) . "</TD><TD ALIGN=RIGHT>" . number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,2) . "</TD><TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "Delete=" . $EnteredGRN->GRNNo . "'>Delete</A></TD></TR>";

	$TotalValueCharged = $TotalValueCharged + ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv);

	$i++;
	if ($i>15){
		$i=0;
		echo $tableheader;
	}
}

echo "<TR><TD COLSPAN=5 ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE>Total Value of Goods Charged:</FONT></TD><TD ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE><U>" . number_format($TotalValueCharged,2) . "</U></FONT></TD></TR>";
echo "</TABLE><BR><A HREF='$rootpath/SupplierInvoice.php?" . SID ."'>Back to Invoice Entry</A><HR>";


/* Now get all the outstanding GRNs for this supplier from the database*/

$SQL = "SELECT GRNBatch, GRNNo, PurchOrderDetails.OrderNo, PurchOrderDetails.UnitPrice, GRNs.ItemCode, GRNs.DeliveryDate, GRNs.ItemDescription, GRNs.QtyRecd, GRNs.QuantityInv, PurchOrderDetails.StdCostUnit FROM GRNs, PurchOrderDetails WHERE GRNs.PODetailItem=PurchOrderDetails.PODetailItem AND GRNs.SupplierID ='" . $_SESSION['SuppTrans']->SupplierID . "' AND GRNs.QtyRecd - GRNs.QuantityInv > 0 ORDER BY GRNs.GRNNo";
$GRNResults = DB_query($SQL,$db);

if (DB_num_rows($GRNResults)==0){
	echo "<P>There are no outstanding goods received from " . $_SESSION['SuppTrans']->SupplierName . " that have not been invoiced by them.<BR> The goods must first be received using the link below to select purchase orders to receive.";
	echo "<P><A HREF='$rootpath/PO_SelectOSPurchOrder.php?" . SID . "SupplierID=" . $_SESSION['SuppTrans']->SupplierID ."'>Select Purchase Orders to receive</A>";
	exit;
}

/*Set up a table to show the GRNs outstanding for selection */
echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";


echo "<CENTER><FONT SIZE=4 COLOR=BLUE>Goods Received Yet to be Invoiced From " . $_SESSION['SuppTrans']->SupplierName;
echo "<TABLE CELLPADDING=1 COLSPAN=7>";

$tableheader = "<TR BGCOLOR=#800000><TD class='tableheader'>GRN Batch</TD><TD class='tableheader'>Sequence #</TD><TD  class='tableheader'>Order</TD><TD  class='tableheader'>Item Code</TD><TD class='tableheader'>Description</TD><TD class='tableheader'>Delivered</TD><TD class='tableheader'>Total Qty Received</TD><TD class='tableheader'>Qty Already Invoiced</TD><TD class='tableheader'>Qty Yet To Invoice</TD><TD class='tableheader'>Order Price in " . $_SESSION['SuppTrans']->CurrCode . "</TD><TD class='tableheader'>Line Value in " . $_SESSION['SuppTrans']->CurrCode . "</TD></TR>";

echo $tableheader;
$i=0;
while ($myrow=DB_fetch_array($GRNResults)){

	$GRNAlreadyOnInvoice = False;

	foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
		if ($EnteredGRN->GRNNo == $myrow["GRNNo"]) {
			$GRNAlreadyOnInvoice = True;
		}
	}
	if ($GRNAlreadyOnInvoice == False){
		echo "<TR><TD>" . $myrow["GRNBatch"] . "</TD><TD><INPUT TYPE=Submit NAME='GRNNo' Value='" . $myrow["GRNNo"] . "'></TD><TD>" . $myrow["OrderNo"] . "</TD><TD>" . $myrow["ItemCode"] . "</TD><TD>" . $myrow["ItemDescription"] . "</TD><TD>" . ConvertSQLDate($myrow["DeliveryDate"]) . "</TD><TD ALIGN=RIGHT>" . number_format($myrow["QtyRecd"],2) . "</TD><TD ALIGN=RIGHT>" . number_format($myrow["Quantityinv"],2) . "</TD><TD ALIGN=RIGHT>" . number_format($myrow["QtyRecd"] - $myrow["QuantityInv"],2) . "</TD><TD ALIGN=RIGHT>" . number_format($myrow["UnitPrice"],2) . "</TD><TD ALIGN=RIGHT>" . number_format($myrow["UnitPrice"]*($myrow["QtyRecd"] - $myrow["QuantityInv"]),2) . "</TD></TR>";
		$i++;
		if ($i>15){
			$i=0;
			echo $tableheader;
		}
	}
}

echo "</TABLE>";

if (isset($_POST['GRNNo']) AND $_POST['GRNNo']!=""){

	$SQL = "SELECT GRNNo, GRNs.PODetailItem, PurchOrderDetails.UnitPrice, PurchOrderDetails.GLCode, GRNs.ItemCode, GRNs.DeliveryDate, GRNs.ItemDescription, GRNs.QuantityInv, GRNs.QtyRecd, GRNs.QtyRecd - GRNs.QuantityInv AS QtyOstdg, PurchOrderDetails.StdCostUnit, PurchOrderDetails.ShiptRef, PurchOrderDetails.JobRef, Shipments.Closed FROM GRNs, PurchOrderDetails LEFT JOIN Shipments ON PurchOrderDetails.ShiptRef=Shipments.ShiptRef WHERE GRNs.PODetailItem=PurchOrderDetails.PODetailItem AND GRNs.GRNNo=" .$_POST['GRNNo'];
	$GRNEntryResult = DB_query($SQL,$db);
	$myrow = DB_fetch_array($GRNEntryResult);

	echo "<P><FONT SIZE=4 COLOR=BLUE><B>GRN Selected For Adding To A Purchase Invoice</FONT></B>";
	echo "<TABLE><TR BGCOLOR=#800000><TD class='tableheader'>Seqnce #</TD><TD class='tableheader'>Item</TD><TD class='tableheader'>Qty Outstanding</TD><TD class='tableheader'>Qty Invoiced</TD><TD class='tableheader'>Order Price in " .  $_SESSION['SuppTrans']->CurrCode . "</TD><TD class='tableheader'>Actual Price in " .  $_SESSION['SuppTrans']->CurrCode . "</TD></TR>";

	echo "<TR><TD>" . $_POST['GRNNo'] . "</TD><TD>" . $myrow['ItemCode'] . " " . $myrow['ItemDescription'] . "</TD><TD ALIGN=RIGHT>" . number_format($myrow['QtyOstdg'],2) . "</TD><TD><INPUT TYPE=Text Name='This_QuantityInv' Value=" . $myrow['QtyOstdg'] . " SIZE=11 MAXLENGTH=10></TD><TD ALIGN=RIGHT>" . $myrow['UnitPrice'] . "</TD><TD><INPUT TYPE=Text Name='ChgPrice' Value=" . $myrow['UnitPrice'] . " SIZE=11 MAXLENGTH=10></TD></TR>";
	echo "</TABLE>";

	if ($myrow["Closed"]==1){ /*Shipment is closed so pre-empt problems later by warning the user - need to modify the order first */
		echo "<INPUT TYPE=HIDDEN NAME='ShiptRef' Value=''>";
		echo "<P>Unfortunately, the shipment that this purchase order line item was allocated to has been closed - if you add this item to the transaction then no shipments will not be updated. If you wish to allocate the order line item to a different shipment the order must be modified first.";
	} else {
		echo "<INPUT TYPE=HIDDEN NAME='ShiptRef' Value='" . $myrow['ShiptRef'] . "'>";
	}

	echo "<P><INPUT TYPE=Submit Name='AddGRNToTrans' Value='Add to Invoice'>";


	echo "<INPUT TYPE=HIDDEN NAME='GRNNumber' VALUE=" . $_POST['GRNNo'] . ">";
	echo "<INPUT TYPE=HIDDEN NAME='ItemCode' VALUE='" . $myrow['ItemCode'] . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='ItemDescription' VALUE='" . $myrow['ItemDescription'] . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='QtyRecd' VALUE=" . $myrow['QtyRecd'] . ">";
	echo "<INPUT TYPE=HIDDEN NAME='Prev_QuantityInv' VALUE=" . $myrow['QuantityInv'] . ">";
	echo "<INPUT TYPE=HIDDEN NAME='OrderPrice' VALUE=" . $myrow['UnitPrice'] . ">";
	echo "<INPUT TYPE=HIDDEN NAME='StdCostUnit' VALUE=" . $myrow['StdCostUnit'] . ">";

	echo "<INPUT TYPE=HIDDEN NAME='JobRef' Value='" . $myrow['JobRef'] . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='GLCode' Value='" . $myrow['GLCode'] . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='PODetailItem' Value='" . $myrow['PODetailItem'] . "'>";
}

echo "</form>";
include("includes/footer.inc");
?>
