<?php

include("includes/DateFunctions.inc");

$PageSecurity = 2;

$title = "Search Purchase Orders";
include("includes/session.inc");
include("includes/header.inc");

if (isset($_GET['SelectedStockItem'])){
	$SelectedStockItem=$_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])){
	$SelectedStockItem=$_POST['SelectedStockItem'];
}

if (isset($_GET['OrderNumber'])){
	$OrderNumber=$_GET['OrderNumber'];
} elseif (isset($_POST['OrderNumber'])){
	$OrderNumber=$_POST['OrderNumber'];
}

if (isset($_GET['SelectedSupplier'])){
	$SelectedSupplier=$_GET['SelectedSupplier'];
} elseif (isset($_POST['SelectedSupplier'])){
	$SelectedSupplier=$_POST['SelectedSupplier'];
}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";


If ($_POST['ResetPart']){
     unset($SelectedStockItem);
}

If (isset($OrderNumber) && $OrderNumber!="") {
	if (!is_numeric($OrderNumber)){
		  echo "<BR><B>The Order Number entered <U>MUST</U> be numeric.</B><BR>";
		  unset ($OrderNumber);
	} else {
		echo "Order Number - $OrderNumber";
	}
} else {
	If ($SelectedSupplier) {
		echo "For supplier: $SelectedSupplier and ";
		echo "<input type=hidden name='SelectedSupplier' value=$SelectedSupplier>";
	}
	If ($SelectedStockItem) {
		 echo "for the part: " . $SelectedStockItem . " and <input type=hidden name='SelectedStockItem' value='$SelectedStockItem'>";
	}
}

if ($_POST['SearchParts']){

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		echo "<BR>Stock description keywords have been used in preference to the Stock code extract entered.";
	}
	If ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$i=0;
		$SearchString = "%";
		while (strpos($_POST['Keywords'], " ", $i)) {
			$wrdlen=strpos($_POST['Keywords']," ",$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . "%";
			$i=strpos($_POST['Keywords']," ",$i) +1;
		}
		$SearchString = $SearchString. substr($_POST['Keywords'],$i)."%";

		$SQL = "SELECT StockMaster.StockID, Description, Sum(LocStock.Quantity) AS QOH,  Units, Sum(PurchOrderDetails.QuantityOrd-PurchOrderDetails.QuantityRecd) AS QORD FROM StockMaster INNER JOIN LocStock ON StockMaster.StockID = LocStock.StockID INNER JOIN PurchOrderDetails ON StockMaster.StockID=PurchOrderDetails.ItemCode WHERE PurchOrderDetails.Completed=1 AND StockMaster.Description LIKE '$SearchString' AND CategoryID='" . $_POST['StockCat'] . "' GROUP BY StockMaster.StockID, Description, Units ORDER BY StockMaster.StockID";
	 } elseif ($_POST['StockCode']){
		$SQL = "SELECT StockMaster.StockID, Description, Sum(LocStock.Quantity) AS QOH, Sum(PurchOrderDetails.QuantityOrd-PurchOrderDetails.QuantityRecd) AS QORD, Units FROM StockMaster INNER JOIN LocStock ON StockMaster.StockID = LocStock.StockID INNER JOIN PurchOrderDetails ON StockMaster.StockID=PurchOrderDetails.ItemCode WHERE PurchOrderDetails.Completed=1 AND StockMaster.StockID like '%" . $_POST['StockCode'] . "%' AND CategoryID='" . $_POST['StockCat'] . "' GROUP BY StockMaster.StockID, Description, Units ORDER BY StockMaster.StockID";
	 } elseif (!$_POST['StockCode'] AND !$_POST['Keywords']) {
		$SQL = "SELECT StockMaster.StockID, Description, Sum(LocStock.Quantity) AS QOH, Units, Sum(PurchOrderDetails.QuantityOrd-PurchOrderDetails.QuantityRecd) AS QORD FROM StockMaster INNER JOIN LocStock ON StockMaster.StockID = LocStock.StockID INNER JOIN PurchOrderDetails ON StockMaster.StockID=PurchOrderDetails.ItemCode WHERE PurchOrderDetails.Completed=1 AND CategoryID='" . $_POST['StockCat'] . "' GROUP BY StockMaster.StockID, Description, Units ORDER BY StockMaster.StockID";
	 }

	$StockItemsResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		echo "<BR>No stock items were returned by the SQL because - " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>The SQL used to retrieve the searched parts was:<BR>$SQL";
		}
	}
}

/* Not appropriate really to restrict search by date since user may miss older
ouststanding orders
	$OrdersAfterDate = Date("d/m/Y",Mktime(0,0,0,Date("m")-2,Date("d"),Date("Y")));
*/

if ($OrderNumber=="" OR !isset($OrderNumber)){

	echo "order number: <INPUT type=text name='OrderNumber' MAXLENGTH =8 SIZE=9> Into Stock Location:<SELECT name='StockLocation'> ";
	$sql = "SELECT LocCode, LocationName FROM Locations";
	$resultStkLocs = DB_query($sql,$db);
	while ($myrow=DB_fetch_array($resultStkLocs)){
		if (isset($_POST['StockLocation'])){
			if ($myrow["LocCode"] == $_POST['StockLocation']){
			echo "<OPTION SELECTED Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
			} else {
			echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
			}
		} elseif ($myrow["LocCode"]==$_SESSION['UserStockLocation']){
			echo "<OPTION SELECTED Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		} else {
			echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		}
	}

	echo "</SELECT> <INPUT TYPE=SUBMIT NAME='SearchOrders' VALUE='Search Purchase Orders'>";
}

$SQL="SELECT CategoryID, CategoryDescription FROM StockCategory ORDER BY CategoryDescription";
$result1 = DB_query($SQL,$db);

?>

<HR>
<FONT SIZE=1>To search for purchase orders for a specific part use the part selection facilities below</FONT>		 <INPUT TYPE=SUBMIT NAME="SearchParts" VALUE="Search Parts Now"><INPUT TYPE=SUBMIT NAME="ResetPart" VALUE="Clear Part Selection">
<TABLE>
<TR>
<TD><FONT SIZE=1>Select a stock category:</FONT>
<SELECT NAME="StockCat">
<?php
while ($myrow1 = DB_fetch_array($result1)) {
	if ($myrow1['CategoryID']==$_POST["StockCat"]){
		echo "<OPTION SELECTED VALUE='". $myrow1["CategoryID"] . "'>" . $myrow1["CategoryDescription"];
	} else {
		echo "<OPTION VALUE='". $myrow1["CategoryID"] . "'>" . $myrow1["CategoryDescription"];
	}
}
?>
</SELECT>
<TD><FONT SIZE=1>Enter text extract(s) in the <B>description</B>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25></TD></TR>
<TR><TD></TD>
<TD><FONT SIZE 3><B>OR </B></FONT><FONT SIZE=1>Enter extract of the <B>Stock Code</B>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="StockCode" SIZE=15 MAXLENGTH=18></TD>
</TR>
</TABLE>

<HR>

<?php

If ($StockItemsResult) {

	echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>";
	$TableHeader = "<TR><TD class='tableheader'>Code</TD><TD class='tableheader'>Description</TD><TD class='tableheader'>On Hand</TD><TD class='tableheader'>Orders Ostdg</TD><TD class='tableheader'>Units</TD></TR>";

	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}
/*										      Code	 Description		     On Hand		  Orders Ostdg     Units		     Code		  Description 	 On Hand     Orders Ostdg		Units	 */
		printf("<td><INPUT TYPE=SUBMIT NAME='SelectedStockItem' VALUE='%s'</td><td>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td>%s</td></tr>", $myrow["StockID"], $myrow["Description"], $myrow["QOH"], $myrow["QORD"],$myrow["Units"]);

		$j++;
		If ($j == 12){
			$j=1;
			echo $TableHeader;
		}
//end of page full new headings if
	}
//end of while loop

	echo "</TABLE>";

}
//end if stock search results to show
  else {

	//figure out the SQL required from the inputs available

	if (isset($OrderNumber) && $OrderNumber !="") {
		$SQL = "SELECT PurchOrders.OrderNo, Suppliers.SuppName, PurchOrders.OrdDate, PurchOrders.Initiator,PurchOrders.RequisitionNo, PurchOrders.AllowPrint, Suppliers.CurrCode, Sum(PurchOrderDetails.UnitPrice*PurchOrderDetails.QuantityOrd) AS OrderValue FROM PurchOrders, PurchOrderDetails, Suppliers WHERE PurchOrders.OrderNo = PurchOrderDetails.OrderNo AND PurchOrders.SupplierNo = Suppliers.SupplierID AND PurchOrders.OrderNo=". $OrderNumber ." GROUP BY PurchOrders.OrderNo";
	} else {

	      /* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */

		if (isset($SelectedSupplier)) {

			if (isset($SelectedStockItem)) {
				$SQL = "SELECT PurchOrders.OrderNo, Suppliers.SuppName, PurchOrders.OrdDate, PurchOrders.Initiator, PurchOrders.RequisitionNo,  PurchOrders.AllowPrint, Suppliers.CurrCode, Sum(PurchOrderDetails.UnitPrice*PurchOrderDetails.QuantityOrd) AS OrderValue FROM PurchOrders, PurchOrderDetails, Suppliers WHERE PurchOrders.OrderNo = PurchOrderDetails.OrderNo AND PurchOrders.SupplierNo = Suppliers.SupplierID AND  PurchOrderDetails.ItemCode='". $SelectedStockItem ."' AND PurchOrders.SupplierNo='" . $SelectedSupplier ."' AND PurchOrders.IntoStockLocation = '". $_POST['StockLocation'] . "' GROUP BY PurchOrders.OrderNo";
			} else {
				$SQL = "SELECT PurchOrders.OrderNo, Suppliers.SuppName, PurchOrders.OrdDate, PurchOrders.Initiator, PurchOrders.RequisitionNo,  PurchOrders.AllowPrint, Suppliers.CurrCode, Sum(PurchOrderDetails.UnitPrice*PurchOrderDetails.QuantityOrd) AS OrderValue FROM PurchOrders, PurchOrderDetails, Suppliers WHERE PurchOrders.OrderNo = PurchOrderDetails.OrderNo AND PurchOrders.SupplierNo = Suppliers.SupplierID AND PurchOrders.SupplierNo='" . $SelectedSupplier ."' AND PurchOrders.IntoStockLocation = '". $_POST['StockLocation'] . "' GROUP BY PurchOrders.OrderNo";
			}
		} else { //no supplier selected
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT PurchOrders.OrderNo, Suppliers.SuppName, PurchOrders.OrdDate, PurchOrders.Initiator, PurchOrders.RequisitionNo, PurchOrders.AllowPrint, Suppliers.CurrCode, Sum(PurchOrderDetails.UnitPrice*PurchOrderDetails.QuantityOrd) AS OrderValue FROM PurchOrders, PurchOrderDetails, Suppliers WHERE PurchOrders.OrderNo = PurchOrderDetails.OrderNo AND PurchOrders.SupplierNo = Suppliers.SupplierID AND PurchOrderDetails.ItemCode='". $SelectedStockItem ."' AND PurchOrders.IntoStockLocation = '". $_POST['StockLocation'] . "' GROUP BY PurchOrders.OrderNo";
			} else {
				$SQL = "SELECT PurchOrders.OrderNo, Suppliers.SuppName, PurchOrders.OrdDate, PurchOrders.Initiator, PurchOrders.RequisitionNo,  PurchOrders.AllowPrint, Suppliers.CurrCode, Sum(PurchOrderDetails.UnitPrice*PurchOrderDetails.QuantityOrd) AS OrderValue FROM PurchOrders, PurchOrderDetails, Suppliers WHERE PurchOrders.OrderNo = PurchOrderDetails.OrderNo AND PurchOrders.SupplierNo = Suppliers.SupplierID AND PurchOrders.IntoStockLocation = '". $_POST['StockLocation'] . "' GROUP BY PurchOrders.OrderNo";
			}

		} //end selected supplier
	} //end not order number selected

	$PurchOrdersResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		echo "No orders were returned by the SQL because - " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>$SQL";
		}
	}

	if (DB_num_rows($PurchOrdersResult)>0){
		/*show a table of the orders returned by the SQL */

		echo "<TABLE CELLPADDING=2 COLSPAN=7 WIDTH=100%>";
		$TableHeader = "<TR><TD class='tableheader'>View</TD><TD class='tableheader'>Supplier</TD><TD class='tableheader'>Currency</TD><TD class='tableheader'>Requisition</TD><TD class='tableheader'>Order Date</TD><TD class='tableheader'>Initiator</TD><TD class='tableheader'>Order Total</TD></TR>";

		echo $TableHeader;

		$j = 1;
		$k=0; //row colour counter
		while ($myrow=DB_fetch_array($PurchOrdersResult)) {


			if ($k==1){ /*alternate bgcolour of row for highlighting */
				echo "<tr bgcolor='#CCCCCC'>";
				$k=0;
			} else {
				echo "<tr bgcolor='#EEEEEE'>";
				$k++;
			}

			$ViewPurchOrder = $rootpath . "/PO_OrderDetails.php?" . SID . "OrderNo=" . $myrow["OrderNo"];

			$FormatedOrderDate = ConvertSQLDate($myrow["OrdDate"]);
			$FormatedOrderValue = number_format($myrow["OrderValue"],2);
	/*						  View					   Supplier				    Currency			   Requisition		     Order Date			     Initiator				Order Total
						ModifyPage, $myrow["OrderNo"],	      $myrow["SuppName"],		    $myrow["CurrCode"],	     $myrow["RequisitionNo"]	    $FormatedOrderDate,		     $myrow["Initiator"]		     $FormatedOrderValue */
			printf("<td><A HREF='%s'>%s</A></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td></tr>", $ViewPurchOrder, $myrow["OrderNo"], $myrow["SuppName"], $myrow["CurrCode"], $myrow["RequisitionNo"], $FormatedOrderDate, $myrow["Initiator"], $FormatedOrderValue);

			$j++;
			If ($j == 12){
				$j=1;
				echo $TableHeader;
			}
		//end of page full new headings if
		}
		//end of while loop

		echo "</TABLE>";
	} // end if purchase orders to show
}

echo "</form>";
include("includes/footer.inc");
?>
