<?php
/* $Revision: 1.2 $ */
$title = "Stock Status";

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");


if (isset($_GET['StockID'])){
	$StockID =$_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID =$_POST['StockID'];
}


$result = DB_query("SELECT Description, Units, MBflag FROM StockMaster WHERE StockID='$StockID'",$db);
$myrow = DB_fetch_row($result);
echo "<BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B>  (In units of $myrow[1])</FONT>";
$Its_A_KitSet_Assembly_Or_Dummy =False;
if ($myrow[2]=="K"){
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	echo "<BR>This is a kitset part and cannot have a stock holding, only the total quantity on outstanding sales orders is shown.";
} elseif ($myrow[2]=="A"){
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	echo "<BR>This is an assembly part and cannot have a stock holding, only the total quantity on outstanding sales orders is shown.";
} elseif ($myrow[2]=="D"){
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	echo "<BR>This is an dummy part and cannot have a stock holding, only the total quantity on outstanding sales orders is shown.";
}

echo "<HR><FORM ACTION='" . $_SERVER['PHP_SELF'] . "?". SID . "' METHOD=POST>";
echo "Stock Code:<input type=text name='StockID' size=21 value='$StockID'  maxlength=20>";

echo "     <INPUT TYPE=SUBMIT NAME='ShowStatus' VALUE='Show Stock Status'><HR>";



$sql = "SELECT LocStock.LocCode, Locations.LocationName, LocStock.Quantity, LocStock.ReorderLevel FROM LocStock, Locations WHERE LocStock.LocCode=Locations.LocCode AND LocStock.StockID = '" . $StockID . "' ORDER BY LocStock.LocCode";
$LocStockResult = DB_query($sql, $db);

if (DB_error_no($db) !=0) {
	echo "The stock held at each location cannot be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
	   echo "<BR>The SQL that failed was $sql";
	}
	exit;
}

echo "<TABLE CELLPADDING=2 BORDER=0>";

if ($Its_A_KitSet_Assembly_Or_Dummy == True){
	$tableheader = "<TR><TD class='tableheader'>Location</TD><TD class='tableheader'>Demand</TD></TR>";
} else {
	$tableheader = "<TR><TD class='tableheader'>Location</TD><TD class='tableheader'>Quantity On Hand</TD>
					<TD class='tableheader'>Re-Order Level</FONT></TD><TD class='tableheader'>Demand</TD>
					<TD class='tableheader'>Available</TD><TD class='tableheader'>On Order</TD></TR>";
}
echo $tableheader;
$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($LocStockResult)) {

	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	$sql = "SELECT Sum(SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced) AS DEM FROM SalesOrderDetails, SalesOrders  WHERE SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND SalesOrders.FromStkLoc='" . $myrow["LocCode"] . "' AND SalesOrderDetails.Completed=0 AND SalesOrderDetails.StkCode='" . $StockID . "'";
	$DemandResult = DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		echo "The demand for this product from " . $myrow["LocCode"] . " cannot be retrieved because - " . DB_error_msg($db);
		if ($debug==1){
		   echo "<BR>The SQL that failed was $sql";
		}
		exit;
	}

	if (DB_num_rows($DemandResult)==1){
	  $DemandRow = DB_fetch_row($DemandResult);
	  $DemandQty =  $DemandRow[0];
	} else {
	  $DemandQty =0;
	}

	//Also need to add in the demand as a component of an assembly items if this items has any assembly parents.
	$sql = "SELECT Sum((SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced)*BOM.Quantity) AS DEM FROM SalesOrderDetails, SalesOrders, BOM, StockMaster  WHERE SalesOrderDetails.StkCode=BOM.Parent AND SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND SalesOrders.FromStkLoc='" . $myrow["LocCode"] . "' AND  SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced > 0 AND BOM.Component='" . $StockID . "' AND StockMaster.StockID=BOM.Parent AND StockMaster.MBflag='A'";

	$DemandResult = DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		echo "The demand for this product from " . $myrow["LocCode"] . " cannot be retrieved because - " . DB_error_msg($db);
		if ($debug==1){
		   echo "<BR>The SQL that failed was $sql";
		}
		exit;
	}

	if (DB_num_rows($DemandResult)==1){
	  $DemandRow = DB_fetch_row($DemandResult);
	  $DemandQty += $DemandRow[0];
	}



	if ($Its_A_KitSet_Assembly_Or_Dummy == False){
		$sql = "SELECT Sum(PurchOrderDetails.QuantityOrd - PurchOrderDetails.QuantityRecd) AS QOO FROM PurchOrderDetails INNER JOIN PurchOrders ON PurchOrderDetails.OrderNo=PurchOrders.OrderNo WHERE PurchOrders.IntoStockLocation='" . $myrow["LocCode"] . "' AND PurchOrderDetails.ItemCode='" . $StockID . "'";
		$QOOResult = DB_query($sql,$db);
		if (DB_error_no($db) !=0) {
			echo "The quantity on order for this product to be received into " . $myrow["LocCode"] . " cannot be retrieved because - " . DB_error_msg($db);
			if ($debug==1){
			echo "<BR>The SQL that failed was $sql";
			}
			exit;
		}
		if (DB_num_rows($QOOResult)==1){
		$QOORow = DB_fetch_row($QOOResult);
		$QOO =  $QOORow[0];
		} else {
		$QOOQty = 0;
		}

	/*			Location				   Quantity On Hand				    Re-Order Level					     Demand					   Available					      On Order			 Location		 Quantity On Hand   Re-Order Level 	    Demand					  Available				 On Order   */
		printf("<td>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td></tr>", $myrow["LocationName"], number_format($myrow["Quantity"],2), number_format($myrow["ReorderLevel"],2), number_format($DemandQty,2), number_format($myrow["Quantity"] - $DemandQty,2),number_format($QOO,2));

	} else {
	/* It must be a dummy, assembly or kitset part */
/*			        Location			     Demand				 Location	    Demand		 */
		printf("<td>%s</td><td ALIGN=RIGHT>%s</td></tr>", $myrow["LocationName"],  number_format($DemandQty, 2));
		
	}
	$j++;
	If ($j == 12){
		$j=1;
		echo $tableheader;
	}
//end of page full new headings if
}
//end of while loop

echo "</TABLE><HR>";
echo "<A HREF='$rootpath/StockMovements.php?" . SID . "StockID=$StockID'>Show Movements</A>";
echo "<BR><A HREF='$rootpath/StockUsage.php?" . SID . "StockID=$StockID'>Show Usage</A>";
echo "<BR><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "SelectedStockItem=$StockID'>Search Outstanding Sales Orders</A>";
echo "<BR><A HREF='$rootpath/SelectCompletedOrder.php?" . SID . "SelectedStockItem=$StockID'>Search Completed Sales Orders</A>";
if ($Its_A_KitSet_Assembly_Or_Dummy ==False){
	echo "<BR><A HREF='$rootpath/PO_SelectPurchOrder.php?" .SID . "SelectedStockItem=$StockID'>Search Outstanding Purchase Orders</A>";
}

echo "</form>";
include("includes/footer.inc");

?>
