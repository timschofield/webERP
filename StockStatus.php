<?php
/* $Revision: 1.7 $ */

$PageSecurity = 2;

include('includes/session.inc');

$title = _('Stock Status');

include('includes/header.inc');
include('includes/DateFunctions.inc');


if (isset($_GET['StockID'])){
	$StockID =$_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID =$_POST['StockID'];
}


$result = DB_query("SELECT Description, 
                           Units, 
                           MBflag, 
                           DecimalPlaces, 
                           Serialised, 
                           Controlled 
                           FROM 
                           StockMaster 
                           WHERE 
                           StockID='$StockID'",
                           $db, 
                           '<BR>' . _('Could not retrieve the requested item'),
                           '<BR>' . _('The SQL used to retrieve the items was') . ':');

$myrow = DB_fetch_row($result);

$DecimalPlaces = $myrow[3];
$Serialised = $myrow[4];
$Controlled = $myrow[5];

echo '<BR><FONT COLOR=BLUE SIZE=3><B>' . $StockID . ' - ' . $myrow[0] . ' </B>  (' . _('In units of') . ' ' . $myrow[1] . ')</FONT>';
$Its_A_KitSet_Assembly_Or_Dummy =False;
if ($myrow[2]=='K'){
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	echo '<BR>' . _('This is a kitset part and cannot have a stock holding, only the total quantity on outstanding sales orders is shown') . '.';
} elseif ($myrow[2]=='A'){
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	echo '<BR>' . _('This is an assembly part and cannot have a stock holding, only the total quantity on outstanding sales orders is shown') . '.';
} elseif ($myrow[2]=='D'){
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	echo '<BR>' . _('This is an dummy part and cannot have a stock holding, only the total quantity on outstanding sales orders is shown') . '.';
}

echo '<HR><FORM ACTION="' . $_SERVER['PHP_SELF'] . '?'. SID . '" METHOD=POST>';
echo _('Stock Code') . ':<input type=text name="StockID" size=21 value="' . $StockID . '" maxlength=20>';

echo ' <INPUT TYPE=SUBMIT NAME="ShowStatus" VALUE="' . _('Show Stock Status') . '"><HR>';

$sql = "SELECT LocStock.LocCode, 
               Locations.LocationName, 
               LocStock.Quantity, 
               LocStock.ReorderLevel 
               FROM LocStock, 
                    Locations 
               WHERE LocStock.LocCode=Locations.LocCode AND 
                     LocStock.StockID = '" . $StockID . "' 
               ORDER BY LocStock.LocCode";

$ErrMsg = _('The stock held at each location cannot be retrieved because') . ':';
$DbgMsg = '<BR>' . _('The SQL that failed was') . ':';
$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

echo '<TABLE CELLPADDING=2 BORDER=0>';

if ($Its_A_KitSet_Assembly_Or_Dummy == True){
	$tableheader = '<TR>
			<TD class="tableheader">' . _('Location') . '</TD>
			<TD class="tableheader">' . _('Demand') . '</TD>
			</TR>';
} else {
	$tableheader = '<TR>
			<TD class="tableheader">' . _('Location') . '</TD>
			<TD class="tableheader">' . _('Quantity On Hand') . '</TD>
			<TD class="tableheader">' . _('Re-Order Level') . '</FONT></TD>
			<TD class="tableheader">' . _('Demand') . '</TD>
			<TD class="tableheader">' . _('Available') . '</TD>
			<TD class="tableheader">' . _('On Order') . '</TD>
			</TR>';
}
echo $tableheader;
$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($LocStockResult)) {

	if ($k==1){
		echo '<tr bgcolor="#CCCCCC">';
		$k=0;
	} else {
		echo '<tr bgcolor="#EEEEEE">';
		$k=1;
	}

	$sql = "SELECT Sum(SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced) 
                 AS DEM 
                 FROM SalesOrderDetails, 
                      SalesOrders  
                 WHERE SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND 
                 SalesOrders.FromStkLoc='" . $myrow["LocCode"] . "' AND 
                 SalesOrderDetails.Completed=0 AND 
                 SalesOrderDetails.StkCode='" . $StockID . "'";

	$ErrMsg = _('The demand for this product from') . ' ' . $myrow["LocCode"] . ' ' . _('cannot be retrieved because') . ':';
	$Dbgmsg = '<BR>' . _('The SQL that failed was') . ':';
	$DemandResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($DemandResult)==1){
	  $DemandRow = DB_fetch_row($DemandResult);
	  $DemandQty =  $DemandRow[0];
	} else {
	  $DemandQty =0;
	}

	//Also need to add in the demand as a component of an assembly items if this items has any assembly parents.
	$sql = "SELECT Sum((SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced)*BOM.Quantity) 
                 AS DEM 
                 FROM SalesOrderDetails, 
                      SalesOrders, 
                      BOM, 
                      StockMaster  
                 WHERE SalesOrderDetails.StkCode=BOM.Parent AND 
                       SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND 
                       SalesOrders.FromStkLoc='" . $myrow["LocCode"] . "' AND  
                       SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced > 0 AND 
                       BOM.Component='" . $StockID . "' AND StockMaster.StockID=BOM.Parent AND 
                       StockMaster.MBflag='A'";

	$ErrMsg = _('The demand for this product from') . ' ' . $myrow["LocCode"] . ' ' . _('cannot be retrieved because') . ':';
	$Dbgmsg = '<BR>' . _('The SQL that failed was') . ':';
	$DemandResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($DemandResult)==1){
		$DemandRow = DB_fetch_row($DemandResult);
		$DemandQty += $DemandRow[0];
	}



	if ($Its_A_KitSet_Assembly_Or_Dummy == False){

		$sql = "SELECT Sum(PurchOrderDetails.QuantityOrd - PurchOrderDetails.QuantityRecd) 
                   AS QOO 
                   FROM PurchOrderDetails 
                   INNER JOIN PurchOrders ON PurchOrderDetails.OrderNo=PurchOrders.OrderNo 
                   WHERE PurchOrders.IntoStockLocation='" . $myrow["LocCode"] . "' AND 
                   PurchOrderDetails.ItemCode='" . $StockID . "'";
		$ErrMsg = _('The quantity on order for this product to be received into') . ' ' . $myrow["LocCode"] . ' ' . _('cannot be retrieved because') . ':';
		$DbgMsg = '<BR>' . _('The SQL that failed was') . ':';
		$QOOResult = DB_query($sql,$db,$ErrMsg, $DbgMsg);

		if (DB_num_rows($QOOResult)==1){
			$QOORow = DB_fetch_row($QOOResult);
			$QOO =  $QOORow[0];
		} else {
			$QOOQty = 0;
		}

		printf("<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>",
			$myrow["LocationName"],
			number_format($myrow["Quantity"],$DecimalPlaces),
			number_format($myrow["ReorderLevel"],$DecimalPlaces),
			number_format($DemandQty,$DecimalPlaces),
			number_format($myrow["Quantity"] - $DemandQty,$DecimalPlaces),
			number_format($QOO,$DecimalPlaces)
			);

		if ($Serialised ==1){ /*The line is a serialised item*/

			echo '<TD><A target="_blank" HREF="' . $rootpath . '/StockSerialItems.php?' . SID . 'Serialised=Yes&Location=' . $myrow['LocCode'] . '&StockID=' .$StockID . '">' . _('Serial Numbers') . '</A></TD></TR>';
		} elseif ($Controlled==1){
			echo '<TD><A target="_blank" HREF="' . $rootpath . '/StockSerialItems.php?' . SID . 'Location=' . $myrow['LocCode'] . '&StockID=' .$StockID . '">' . _('Batches') . '</A></TD></TR>';
		}

	} else {
	/* It must be a dummy, assembly or kitset part */

		printf("<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			</tr>",
			$myrow["LocationName"],
			number_format($DemandQty, $DecimalPlaces)
			);
	}
	$j++;
	If ($j == 12){
		$j=1;
		echo $tableheader;
	}
//end of page full new headings if
}
//end of while loop

echo '</TABLE><HR>';
echo '<A HREF="' . $rootpath . '/StockMovements.php?' . SID . 'StockID=' . $StockID . '">' . _('Show Movements') . '</A>';
echo '<BR><A HREF="' . $rootpath . '/StockUsage.php?' . SID . 'StockID=' . $StockID . '">' . _('Show Usage') . '</A>';
echo '<BR><A HREF="' . $rootpath . '/SelectSalesOrder.php?' . SID . 'SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Sales Orders') . '</A>';
echo '<BR><A HREF="' . $rootpath . '/SelectCompletedOrder.php?' . SID . 'SelectedStockItem=' . $StockID . '">' . _('Search Completed Sales Orders') . '</A>';
if ($Its_A_KitSet_Assembly_Or_Dummy ==False){
	echo '<BR><A HREF="' . $rootpath . '/PO_SelectOSPurchOrder.php?' .SID . 'SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Purchase Orders') . '</A>';
}

echo '</form>';
include('includes/footer.inc');

?>
