<?php

/*The supplier transaction uses the SuppTrans class to hold the information about the credit note
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */

$title = "Enter Supplier Credit Note Against Goods Received";

$PageSecurity = 5;

include("includes/DefineSuppTransClass.php");
/* Session started in header.inc for password checking and authorisation level check */
include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");


if (!isset($_SESSION['SuppTrans'])){
	echo "<P>To enter a supplier transactions the supplier must first be selected from the supplier selection screen, then the link to enter a supplier credit note must be clicked on.";
	echo "<BR><A HREF='$rootpath/SelectSupplier.php?" . SID ."'>Select A Supplier to Enter a Transaction For</A>";
	exit;
	/*It all stops here if there aint no supplier selected and credit note initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to Credit Note button then process this first before showing all GRNs on the credit note otherwise it wouldnt show the latest addition*/

if ($_POST['AddGRNToTrans']=="Add to Credit Note" ){

	$InputError=False;

	$Complete = False;

	if (!is_numeric($_POST['ChgPrice']) AND $_POST['ChgPrice']<0){
		$InputError = True;
		echo "<P><FONT COLOR=RED SIZE=4><B>Error:<BR></FONT>The price charged in the suppliers currency is either not numeric or negative. The goods received cannot be credited at this price.";
	}

	if ($InputError==False){
		$_SESSION['SuppTrans']->Add_GRN_To_Trans($_POST['GRNNumber'], $_POST['PODetailItem'], $_POST['ItemCode'], $_POST['ItemDescription'], $_POST['QtyRecd'], $_POST['Prev_QuantityInv'], $_POST['This_QuantityCredited'], $_POST['OrderPrice'], $_POST['ChgPrice'], $Complete, $_POST['StdCostUnit'], $_POST['ShiptRef'], $_POST['JobRef'], $_POST['GLCode']);
	}
}

if (isset($_GET['Delete'])){

	$_SESSION['SuppTrans']->Remove_GRN_From_Trans($_GET['Delete']);

}


/*Show all the selected GRNs so far from the SESSION['SuppTrans']->GRNs array */

echo "<CENTER><FONT SIZE=4 COLOR=BLUE>Credits Against Goods Received Selected";
echo "<TABLE CELLPADDING=0>";
$TableHeader = "<TR><TD class='tableheader'>GRN</TD><TD class='tableheader'>Item Code</TD><TD class='tableheader'>Description</TD><TD class='tableheader'>Quantity Credited</TD><TD class='tableheader'>Price Credited in " . $_SESSION['SuppTrans']->CurrCode . "</TD><TD class='tableheader'>Line Value In " . $_SESSION['SuppTrans']->CurrCode . "</TD></TR>";

echo $TableHeader;

$TotalValueCharged=0;

foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

	echo "<TR><TD>" . $EnteredGRN->GRNNo . "</TD><TD>" . $EnteredGRN->ItemCode . "</TD><TD>" . $EnteredGRN->ItemDescription . "</TD><TD ALIGN=RIGHT>" . number_format($EnteredGRN->This_QuantityInv,2) . "</TD><TD ALIGN=RIGHT>" . number_format($EnteredGRN->ChgPrice,2) . "</TD><TD ALIGN=RIGHT>" . number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,2) . "</TD><TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "Delete=" . $EnteredGRN->GRNNo . "'>Delete</A></TD></TR>";

	$TotalValueCharged = $TotalValueCharged + ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv);

	$i++;
	if ($i>15){
		$i=0;
		echo $TableHeader;
	}
}

echo "<TR><TD COLSPAN=5 ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE>Total Value Credited Against Goods:</FONT></TD><TD ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE><U>" . number_format($TotalValueCharged,2) . "</U></FONT></TD></TR>";
echo "</TABLE><BR><A HREF='$rootpath/SupplierCredit.php?" . SID ."'>Back to Credit Note Entry</A><HR>";


/* Now get all the GRNs for this supplier from the database
after the date entered */
if (!isset($_POST['Show_Since'])){
	$_POST['Show_Since'] =  Date($DefaultDateFormat,Mktime(0,0,0,Date("m")-2,Date("d"),Date("Y")));
}

$SQL = "SELECT GRNNo, PurchOrderDetails.OrderNo, PurchOrderDetails.UnitPrice, GRNs.ItemCode, GRNs.DeliveryDate, GRNs.ItemDescription, GRNs.QtyRecd, GRNs.QuantityInv, PurchOrderDetails.StdCostUnit FROM GRNs, PurchOrderDetails WHERE GRNs.PODetailItem=PurchOrderDetails.PODetailItem AND GRNs.SupplierID ='" . $_SESSION['SuppTrans']->SupplierID . "' AND GRNs.DeliveryDate >= '" . FormatDateForSQL($_POST['Show_Since']) . "' ORDER BY GRNs.GRNNo";
$GRNResults = DB_query($SQL,$db);

if (DB_num_rows($GRNResults)==0){
	echo "<P>There are no goods received records for " . $_SESSION['SuppTrans']->SupplierName . "<BR> To enter a credit against goods received, the goods must first be received using the link below to select purchase orders to receive.";
	echo "<P><A HREF='$rootpath/PO_SelectPurchOrder.php?" . SID . "SupplierID=" . $_SESSION['SuppTrans']->SupplierID ."'>Select Purchase Orders to Receive</A>";
	exit;
}

/*Set up a table to show the GRNs outstanding for selection */
echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo "<BR>Show Goods Received Since: <INPUT TYPE=Text NAME='Show_Since' MAXLENGTH=11 SIZE=12 VALUE='" . $_POST['Show_Since'] . "'>";
echo "<FONT SIZE=4 COLOR=BLUE> From " . $_SESSION['SuppTrans']->SupplierName;

echo "<TABLE CELLPADDING=2 COLSPAN=7>";

$TableHeader = "<TR><TD class='tableheader'>GRN</TD><TD class='tableheader'>Order</TD><TD class='tableheader'>Item Code</TD><TD class='tableheader'>Description</TD><TD class='tableheader'>Delivered</TD><TD class='tableheader'>Total Qty<BR>Received</TD><TD class='tableheader'>Qty Already<BR>credit noted</TD><TD class='tableheader'>Qty Yet<BR>To credit note</TD><TD class='tableheader'>Order Price<BR>" . $_SESSION['SuppTrans']->CurrCode . "</TD><TD class='tableheader'>Line Value<BR>In " . $_SESSION['SuppTrans']->CurrCode . "</TD></TR>";

echo $TableHeader;

$i=0;
while ($myrow=DB_fetch_array($GRNResults)){

	$GRNAlreadyOnCredit = False;

	foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
		if ($EnteredGRN->GRNNo == $myrow["GRNNo"]) {
			$GRNAlreadyOnCredit = True;
		}
	}
	if ($GRNAlreadyOnCredit == False){
		echo "<TR><TD><INPUT TYPE=Submit NAME='GRNNo' Value='" . $myrow["GRNNo"] . "'></TD><TD>" . $myrow["OrderNo"] . "</TD><TD>" . $myrow["ItemCode"] . "</TD><TD>" . $myrow["ItemDescription"] . "</TD><TD>" . ConvertSQLDate($myrow["DeliveryDate"]) . "</TD><TD ALIGN=RIGHT>" . number_format($myrow["QtyRecd"],2) . "</TD><TD ALIGN=RIGHT>" . number_format($myrow["Quantityinv"],2) . "</TD><TD ALIGN=RIGHT>" . number_format($myrow["QtyRecd"] - $myrow["QuantityInv"],2) . "</TD><TD ALIGN=RIGHT>" . number_format($myrow["UnitPrice"],2) . "</TD><TD ALIGN=RIGHT>" . number_format($myrow["UnitPrice"]*($myrow["QtyRecd"] - $myrow["QuantityInv"]),2) . "</TD></TR>";
		$i++;
		if ($i>15){
			$i=0;
			echo $TableHeader;
		}
	}
}

echo "</TABLE>";

if (isset($_POST['GRNNo']) AND $_POST['GRNNo']!=""){

	$SQL = "SELECT GRNNo, GRNs.PODetailItem, PurchOrderDetails.UnitPrice, PurchOrderDetails.GLCode, GRNs.ItemCode, GRNs.DeliveryDate, GRNs.ItemDescription, GRNs.QuantityInv, GRNs.QtyRecd, GRNs.QtyRecd - GRNs.QuantityInv AS QtyOstdg, PurchOrderDetails.StdCostUnit, PurchOrderDetails.ShiptRef, PurchOrderDetails.JobRef, Shipments.Closed FROM GRNs, PurchOrderDetails LEFT JOIN Shipments ON PurchOrderDetails.ShiptRef=Shipments.ShiptRef WHERE GRNs.PODetailItem=PurchOrderDetails.PODetailItem AND GRNs.GRNNo=" .$_POST['GRNNo'];
	$GRNEntryResult = DB_query($SQL,$db);
	$myrow = DB_fetch_array($GRNEntryResult);

	echo "<P><FONT SIZE=4 COLOR=BLUE><B>GRN Selected For Adding To A Suppliers Credit Note</FONT></B>";

	echo "<TABLE><TR><TD class='tableheader'>GRN</TD><TD class='tableheader'>Item</TD><TD class='tableheader'>Quantity<BR>Outstanding</TD><TD class='tableheader'>Quantity<BR>credited</TD><TD class='tableheader'>Order<BR>Price " .  $_SESSION['SuppTrans']->CurrCode . "</TD><TD class='tableheader'>Credit<BR>Price " .  $_SESSION['SuppTrans']->CurrCode . "</TD></TR>";

	echo "<TR><TD>" . $_POST['GRNNo'] . "</TD><TD>" . $myrow['ItemCode'] . " " . $myrow['ItemDescription'] . "</TD><TD ALIGN=RIGHT>" . number_format($myrow['QtyOstdg'],2) . "</TD><TD><INPUT TYPE=Text Name='This_QuantityCredited' Value=" . $myrow['QtyOstdg'] . " SIZE=11 MAXLENGTH=10></TD><TD ALIGN=RIGHT>" . $myrow['UnitPrice'] . "</TD><TD><INPUT TYPE=Text Name='ChgPrice' Value=" . $myrow['UnitPrice'] . " SIZE=11 MAXLENGTH=10></TD></TR>";
	echo "</TABLE>";

	if ($myrow["Closed"]==1){ /*Shipment is closed so pre-empt problems later by warning the user - need to modify the order first */
		echo "<INPUT TYPE=HIDDEN NAME='ShiptRef' Value=''>";
		echo "<P>Unfortunately, the shipment that this purchase order line item was allocated to has been closed - if you add this item to the transaction then no shipments will not be updated. If you wish to allocate the order line item to a different shipment the order must be modified first.";
	} else {
		echo "<INPUT TYPE=HIDDEN NAME='ShiptRef' Value='" . $myrow['ShiptRef'] . "'>";
	}

	echo "<P><INPUT TYPE=Submit Name='AddGRNToTrans' Value='Add to Credit Note'>";


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

