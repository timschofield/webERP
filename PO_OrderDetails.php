<?php
if (isset($_GET['OrderNo'])) {
	$title = "Reviewing Purchase Order Number " . $_GET['OrderNo'];
} else {
	$title = "Reviewing A Purchase Order";
}

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");

if (isset($_GET["FromGRNNo"])){

	$SQL= "SELECT PurchOrderDetails.OrderNo  FROM PurchOrderDetails,GRNs
		WHERE PurchOrderDetails.PODetailITem=GRNs.PODetailItem AND GRNs.GRNNo=" . $_GET["FromGRNNo"];

	$orderResult = DB_query($SQL, $db);

	if (DB_error_no($db) !=0) {
		echo "<BR>The search of the GRNs was unsucessful -the SQL statement returned the error: " . $DB_error_msg($db);
		 if ($debug==1){
		 	echo "<BR>The SQL that failed was<BR>" . $SQL;
		}
		exit;
	}

	$orderRow = DB_fetch_row($orderResult);
	$_GET['OrderNo'] = $orderRow[0];
	echo "<BR><FONT SIZE=4 COLOR=BLUE>Order Number " . $_GET['OrderNo'] . "</FONT>";
}

if (!isset($_GET['OrderNo'])) {
	die ("<BR>This page must be called with a purchase order number to review.");
}


$OrderHeaderSQL = "SELECT PurchOrders.*, Suppliers.SupplierID, Suppliers.SuppName, Suppliers.CurrCode
					FROM PurchOrders, Suppliers
					WHERE PurchOrders.SupplierNo = Suppliers.SupplierID
					AND PurchOrders.OrderNo = " . $_GET['OrderNo'];

$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db);

if (DB_error_no($db) !=0) {
	echo "<BR>The order requested could not be retrieved - the SQL returned the following error: " . DB_error_msg($db);
	if ($debug==1){
		echo "<BR>The SQL that failed was:<BR>$OrderHeaderSQL";
	}
	exit;
} elseif (DB_num_rows($GetOrdHdrResult)!=1) {
	echo "<BR>The order requested could not be retrieved - the SQL returned either 0 or several purchase orders: " . DB_error_msg($db);
	if ($debug==1){
		echo "<BR>The SQL that failed was:<BR>$OrderHeaderSQL";
	}
	exit;
}
 // the checks all good get the order now

$myrow = DB_fetch_array($GetOrdHdrResult);

/* SHOW ALL THE ORDER INFO IN ONE PLACE */

echo "<BR><CENTER><TABLE BORDER=0 CELLPADDING=2>";
echo "<TR><TD class='tableheader'>Supplier Code</TD><TD>" . $myrow['SupplierID'] . "</TD>
	<TD class='tableheader'>Supplier Name</TD><TD>" . $myrow['SuppName'] . "</TD></TR>";

echo "<TR><TD class='tableheader'>Ordered On</TD><TD>" . ConvertSQLDate($myrow['OrdDate']) . "</TD>
	<TD class='tableheader'>Delivery Address 1</TD><TD>" . $$myrow['DelAdd1'] . "</TD></TR>";

echo "<TR><TD class='tableheader'>Order Currency</TD><TD>" . $myrow['CurrCode'] . "</TD>
	<TD class='tableheader'>Delivery Address 2</TD><TD>" . $myrow['DelAdd2'] . "</TD></TR>";

echo "<TR><TD class='tableheader'>Exchange Rate</TD><TD>" . $myrow['Rate'] . "</TD>
	<TD class='tableheader'>Delivery Address 3</TD><TD>" . $myrow['DelAdd3'] . "</TD></TR>";

echo "<TR><TD class='tableheader'>Deliver Into Location</TD><TD>" . $myrow['IntoStockLocation'] . "</TD>
	<TD class='tableheader'>Delivery Address 4</TD><TD>" . $myrow['DelAdd4'] . "</TD></TR>";

echo "<TR><TD class='tableheader'>Initiator</TD><TD>" . $myrow['Initiator'] . "</TD>
	<TD class='tableheader'>Requistion Ref.</TD><TD>" . $myrow['RequisitionNo'] . "</TD></TR>";

echo "</TABLE>";
echo $myrow['Comments'] . "<BR></CENTER>";

/*Now get the line items */

$LineItemsSQL = "SELECT PurchOrderDetails.* FROM PurchOrderDetails
				WHERE PurchOrderDetails.OrderNo = " . $_GET['OrderNo'];

$LineItemsResult = db_query($LineItemsSQL,$db);

if (DB_error_no($db)!=0){
	echo "<BR>The line items of the purchase order could not be retrieved, the sql used caused the error:<BR>" . DB_error_msg($db);
	if ($debug==1){
		echo "<BR>The SQL used -that failed was:<BR>" . $LineItemsSQL;
	}
	exit;
	include ("includes/footer.inc");
}


echo "<CENTER><FONT SIZE=4 COLOR=BLUE>Order Line Details</FONT>";

echo "<TABLE COLSPAN=8 BORDER=0 CELLPADDING=0><TR><TD class='tableheader'>Item Code</TD><TD class='tableheader'>Item Description</TD><TD class='tableheader'>Ord Qty</TD><TD class='tableheader'>Qty Recd</TD><TD class='tableheader'>Qty Inv</TD><TD class='tableheader'>Ord Price</TD><TD class='tableheader'>Chg Price</TD><TD class='tableheader'>Reqd Date</TD></TR>";

$k =0;  //row colour counter
$OrderTotal=0;
$RecdTotal=0;

while ($myrow=db_fetch_array($LineItemsResult)) {

	$OrderTotal += ($myrow['QuantityOrd'] * $myrow['UnitPrice']);
	$RecdTotal += ($myrow['QuantityRecd'] * $myrow['UnitPrice']);

	$DisplayReqdDate = ConvertSQLDate($myrow['DeliveryDate']);

	// if overdue and outstanding quantities, then highlight as so
	if (($myrow['QuantityOrd'] - $myrow['QuantityRecd'] > 0)
	  	AND Date1GreaterThanDate2(Date($DefaultDateFormat), $DisplayReqdDate)){
    	 	echo "<tr class='OsRow'>";
	} else {
    		if ($k==1){
    			echo "<tr bgcolor='#CCCCCC'>";
    			$k=0;
    		} else {
    			echo "<tr bgcolor='#EEEEEE'>";
    			$k=1;
    		}
	}
		//Item Code    Item Description     Ord Qty     Qty Recd    Qty Inv  Ord Price Chg Price  Reqd Date
	printf ("<TD>%s</TD><TD>%s</TD><TD ALIGN=RIGHT>%01.2f</TD><TD ALIGN=RIGHT>%01.2f</TD><TD ALIGN=RIGHT>%01.2f</TD><TD ALIGN=RIGHT>%01.2f</TD><TD ALIGN=RIGHT>%01.2f</TD><TD>%s</TD></TR>" ,$myrow['ItemCode'], $myrow['ItemDescription'],$myrow['QuantityOrd'],$myrow['QuantityRecd'], $myrow['QtyInvoiced'],$myrow['UnitPrice'],$myrow['ActPrice'],$DisplayReqdDate);

}

echo "<TR><TD><BR></TD></TR><TD colspan=4 ALIGN=RIGHT>Total Order Value Excluding Tax</TD><TD COLSPAN=2 ALIGN=RIGHT>" . number_format($OrderTotal,2) . "</TD></TR>";
echo "<TR></TR><TD colspan=4 ALIGN=RIGHT>Total Order Value Received Excluding Tax</TD><TD COLSPAN=2 ALIGN=RIGHT>" . number_format($RecdTotal,2) . "</TD></TR>";
echo "</TABLE>";

echo "<BR>";

include ("includes/footer.inc");
?>
