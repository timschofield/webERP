<?php
/* $Revision: 1.2 $ */
include("includes/DateFunctions.inc");

$title = "Search All Sales Orders";

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID ."' METHOD=POST>";

if (isset($_GET['SelectedStockItem'])){
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])){
	$SelectedStockItem = $_POST['SelectedStockItem'];
}
if (isset($_GET['OrderNumber'])){
	$OrderNumber = $_GET['OrderNumber'];
} elseif (isset($_POST['OrderNumber'])){
	$OrderNumber = $_POST['OrderNumber'];
}
if (isset($_GET['SelectedCustomer'])){
	$SelectedCustomer = $_GET['SelectedCustomer'];
} elseif (isset($_POST['SelectedCustomer'])){
	$SelectedCustomer = $_POST['SelectedCustomer'];
}

if ($SelectedStockItem==""){
	unset($SelectedStockItem);
}
if ($OrderNumber==""){
	unset($OrderNumber);
}
if ($SelectedCustomer==""){
	unset($SelectedCustomer);
}
If ($_POST['ResetPart']){
		unset($SelectedStockItem);
}

If (isset($OrderNumber)) {
	echo "Order Number - $OrderNumber";
} else {
	If (isset($SelectedCustomer)) {
		echo "For customer: $SelectedCustomer and ";
		echo "<input type=hidden name='SelectedCustomer' value=$SelectedCustomer>";
	}

	If (isset($SelectedStockItem)) {

		echo "for the part: " . $SelectedStockItem . " and <input type=hidden name='SelectedStockItem' value='$SelectedStockItem'>";

	}
}


if ($_POST['SearchParts']!=""){

	If ($_POST['Keywords']!="" AND $_POST['StockCode']!="") {
		echo "Stock description keywords have been used in preference to the Stock code extract entered.";
	}
	If ($_POST['Keywords']!="") {
		//insert wildcard characters in spaces

		$i=0;
		$SearchString = "%";
		while (strpos($_POST['Keywords'], " ", $i)) {
			$wrdlen=strpos($_POST['Keywords']," ",$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . "%";
			$i=strpos($_POST['Keywords']," ",$i) +1;
		}
		$SearchString = $SearchString. substr($_POST['Keywords'],$i)."%";

		$SQL = "SELECT StockMaster.StockID, Description, Sum(LocStock.Quantity) AS QOH,  Units, Sum(SalesOrderDetails.Quantity - SalesOrderDetails.QtyInvoiced) AS QDEM FROM StockMaster, LocStock, SalesOrderDetails WHERE StockMaster.StockID=LocStock.StockID AND StockMaster.StockID = SalesOrderDetails.StkCode AND SalesOrderDetails.Completed =0 AND Description LIKE '$SearchString' AND CategoryID='" . $_POST['StockCat'] . "' GROUP BY StockMaster.StockID, Description, Units ORDER BY StockMaster.StockID";

	} elseif ($_POST['StockCode']!=""){

		$SQL = "SELECT StockMaster.StockID, Description, Sum(LocStock.Quantity) AS QOH, Sum(SalesOrderDetails.Quantity - SalesOrderDetails.QtyInvoiced) AS QDEM, Units FROM StockMaster, LocStock, SalesOrderDetails WHERE StockMaster.StockID=LocStock.StockID AND StockMaster.StockID = SalesOrderDetails.StkCode AND SalesOrderDetails.Completed =0 AND StockMaster.StockID like '%" . $_POST['StockCode'] . "%' AND CategoryID='" . $_POST['StockCat'] . "' GROUP BY StockMaster.StockID, Description, Units ORDER BY StockMaster.StockID";

	} elseif ($_POST['StockCode']=="" AND $_POST['Keywords']=="" AND $_POST['StockCat']!="") {
		
		$SQL = "SELECT StockMaster.StockID, Description, Sum(LocStock.Quantity) AS QOH, Sum(SalesOrderDetails.Quantity - SalesOrderDetails.QtyInvoiced) AS QDEM, Units FROM StockMaster, LocStock, SalesOrderDetails WHERE StockMaster.StockID=LocStock.StockID AND StockMaster.StockID = SalesOrderDetails.StkCode AND SalesOrderDetails.Completed =0 AND CategoryID='" . $_POST['StockCat'] . "' GROUP BY StockMaster.StockID, Description, Units ORDER BY StockMaster.StockID";

	}

	if (strlen($SQL)<2){
		echo "<BR>No selections have been made to search for parts - choose a stock category or enter some characters of the code or description then try again.<P>";
	} else {
		$StockItemsResult = DB_query($SQL,$db);

		if (DB_error_no($db) !=0) {
			echo "<BR>No stock items were returned by the SQL because - " . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The SQL used to retrieve the searched parts was:<BR>$SQL";
			}
		} elseif (DB_num_rows($StockItemsResult)==1){
		  	$myrow = DB_fetch_row($StockItemsResult);
		  	$SelectedStockItem = $myrow[0];
			$_POST['SearchOrders']='True';
		  	unset($StockItemsResult);
		  	echo "<BR>For the part: " . $SelectedStockItem . " and <input type=hidden name='SelectedStockItem' value='$SelectedStockItem'>";
		}
	}
} else if ($_POST['SearchOrders']=="Search Orders" AND Is_Date($_POST['OrdersAfterDate'])==1) {

	//figure out the SQL required from the inputs available
	if (isset($OrderNumber)) {
			$SQL = "SELECT SalesOrders.OrderNo, DebtorsMaster.Name, CustBranch.BrName, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliveryDate,  SalesOrders.DeliverTo, Sum(SalesOrderDetails.UnitPrice*SalesOrderDetails.Quantity*(1-SalesOrderDetails.DiscountPercent)) AS OrderValue FROM SalesOrders, SalesOrderDetails, DebtorsMaster, CustBranch WHERE SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND SalesOrders.BranchCode = CustBranch.BranchCode AND SalesOrders.DebtorNo = DebtorsMaster.DebtorNo AND DebtorsMaster.DebtorNo = CustBranch.DebtorNo AND SalesOrders.OrderNo=". $OrderNumber ." GROUP BY SalesOrders.OrderNo";
	} else {
		$DateAfterCriteria = FormatDateforSQL($_POST['OrdersAfterDate']);

		if (isset($SelectedCustomer) AND !isset($OrderNumber)) {

			if (isset($SelectedStockItem)) {
				$SQL = "SELECT SalesOrders.OrderNo, DebtorsMaster.Name, CustBranch.BrName, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliveryDate,  SalesOrders.DeliverTo, SalesOrderDetails.UnitPrice*SalesOrderDetails.Quantity*(1-SalesOrderDetails.DiscountPercent)) AS OrderValue FROM SalesOrders, SalesOrderDetails, DebtorsMaster, CustBranch WHERE SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND SalesOrders.BranchCode = CustBranch.BranchCode AND SalesOrders.DebtorNo = DebtorsMaster.DebtorNo AND DebtorsMaster.DebtorNo = CustBranch.DebtorNo AND SalesOrderDetails.StkCode='". $SelectedStockItem ."' AND SalesOrders.DebtorNo='" . $SelectedCustomer ."' AND SalesOrders.OrdDate >= '" . $DateAfterCriteria ."' GROUP BY SalesOrders.OrderNo, SalesOrders.DebtorNo, SalesOrders.BranchCode, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo";
			} else {
				$SQL = "SELECT SalesOrders.OrderNo, DebtorsMaster.Name, CustBranch.BrName, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo, SalesOrders.DeliveryDate, Sum(SalesOrderDetails.UnitPrice*SalesOrderDetails.Quantity*(1-SalesOrderDetails.DiscountPercent)) AS OrderValue FROM SalesOrders, SalesOrderDetails, DebtorsMaster, CustBranch WHERE SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND SalesOrders.DebtorNo = DebtorsMaster.DebtorNo AND SalesOrders.BranchCode = CustBranch.BranchCode AND DebtorsMaster.DebtorNo = CustBranch.DebtorNo AND SalesOrders.DebtorNo='" . $SelectedCustomer . "' AND SalesOrders.OrdDate >= '" . $DateAfterCriteria . "' GROUP BY SalesOrders.OrderNo, SalesOrders.DebtorNo, SalesOrders.BranchCode, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo";
			}
		} else { //no customer selected
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT SalesOrders.OrderNo, DebtorsMaster.Name, CustBranch.BrName, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo, SalesOrders.DeliveryDate, Sum(SalesOrderDetails.UnitPrice*SalesOrderDetails.Quantity*(1-SalesOrderDetails.DiscountPercent)) AS OrderValue FROM SalesOrders, SalesOrderDetails, DebtorsMaster, CustBranch WHERE SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND SalesOrders.DebtorNo = DebtorsMaster.DebtorNo AND SalesOrders.BranchCode = CustBranch.BranchCode AND DebtorsMaster.DebtorNo = CustBranch.DebtorNo AND SalesOrderDetails.StkCode='". $SelectedStockItem ."'  AND SalesOrders.OrdDate >= '" . $DateAfterCriteria . "' GROUP BY SalesOrders.OrderNo, SalesOrders.DebtorNo, SalesOrders.BranchCode, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo";
			} else {
				$SQL = "SELECT SalesOrders.OrderNo, DebtorsMaster.Name, CustBranch.BrName, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo, SalesOrders.DeliveryDate, Sum(SalesOrderDetails.UnitPrice*SalesOrderDetails.Quantity*(1-SalesOrderDetails.DiscountPercent)) AS OrderValue FROM SalesOrders, SalesOrderDetails, DebtorsMaster, CustBranch WHERE SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND SalesOrders.DebtorNo = DebtorsMaster.DebtorNo AND SalesOrders.BranchCode = CustBranch.BranchCode AND DebtorsMaster.DebtorNo = CustBranch.DebtorNo AND SalesOrders.OrdDate >= '$DateAfterCriteria' GROUP BY SalesOrders.OrderNo, SalesOrders.DebtorNo, SalesOrders.BranchCode, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo ";
			}

		} //end selected customer
	} //end not order number selected

	$SalesOrdersResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		echo "<BR>No orders were returned by the SQL because - " . DB_error_msg($db);
		echo "<BR>$SQL";
	}

}//end of which button clicked options

if (!isset($_POST['OrdersAfterDate']) OR $_POST['OrdersAfterDate'] == "" OR ! Is_Date($_POST['OrdersAfterDate'])){
	$_POST['OrdersAfterDate'] = Date($DefaultDateFormat,Mktime(0,0,0,Date("m")-2,Date("d"),Date("Y")));
}
if ($OrderNumber=="" OR !isset($OrderNumber)){
	echo "order number: <INPUT type=text name='OrderNumber' MAXLENGTH =8 SIZE=9> for all orders placed after: <INPUT type=text name='OrdersAfterDate' MAXLENGTH =10 SIZE=11 value=" . $_POST['OrdersAfterDate'] . ">     <INPUT TYPE=SUBMIT NAME='SearchOrders' VALUE='Search Orders'>";
}

if (!isset($SelectedStockItem)) {
	$SQL="SELECT CategoryID, CategoryDescription FROM StockCategory ORDER BY CategoryDescription";
	$result1 = DB_query($SQL,$db);
?>

      <HR>
      <FONT SIZE=1>To search for sales orders for a specific part use the part selection facilities below</FONT>	     <INPUT TYPE=SUBMIT NAME="SearchParts" VALUE="Search Parts Now"><INPUT TYPE=SUBMIT NAME="ResetPart" VALUE="Clear Part Selection">
      <TABLE>
      <TR>
      <TD><FONT SIZE=1>Select a stock category:</FONT>
      <SELECT NAME="StockCat">
<?php
	while ($myrow1 = DB_fetch_array($result1)) {
		if ($myrow1["CategoryID"] == $_POST['StockCat']){
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
}


If (isset($StockItemsResult)) {

	echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>";

	$TableHeadings = "<TR><TD class='tableheader'>Code</TD><TD class='tableheader'>Description</TD><TD class='tableheader'>On Hand</TD><TD class='tableheader'>Purchase Orders</TD><TD class='tableheader'>Sales Orders</TD><TD class='tableheader'>Units</TD></TR>";

	echo $TableHeadings;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		printf("<td><FONT SIZE=1><INPUT TYPE=SUBMIT NAME='SelectedStockItem' VALUE='%s'</FONT></td><td><FONT SIZE=1>%s</FONT></td><td ALIGN=RIGHT><FONT SIZE=1>%s</FONT></td><td ALIGN=RIGHT><FONT SIZE=1>%s</FONT></td><td ALIGN=RIGHT><FONT SIZE=1>%s</FONT></td><td><FONT SIZE=1>%s</FONT></td></tr>", $myrow["StockID"], $myrow["Description"], $myrow["QOH"], $myrow["QOO"],$myrow["QDEM"],$myrow["Units"]);

		$j++;
		If ($j == 12){
			$j=1;
			echo $TableHeadings;
		}
//end of page full new headings if
	}
//end of while loop

	echo "</TABLE>";

}
//end if stock search results to show

If ($SalesOrdersResult) {

/*show a table of the orders returned by the SQL */

	echo "<TABLE CELLPADDING=2 COLSPAN=6 WIDTH=100%>";
	$tableheader = "<TR><TD class='tableheader'>Order #</TD><TD class='tableheader'>Customer</TD><TD  class='tableheader'>Branch</TD><TD class='tableheader'>Cust Order #</TD><TD class='tableheader'>Order Date</TD><TD class='tableheader'>Req Del Date</TD><TD class='tableheader'>Delivery To</TD><TD class='tableheader'>Order Total</TD></TR>";
	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($SalesOrdersResult)) {


		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}

		$ViewPage = $rootpath . "/OrderDetails.php?" .SID . "OrderNumber=" . $myrow["OrderNo"];
		$FormatedDelDate = ConvertSQLDate($myrow["DeliveryDate"]);
		$FormatedOrderDate = ConvertSQLDate($myrow["OrdDate"]);
		$FormatedOrderValue = number_format($myrow["OrderValue"],2);

		printf("<td><A target='_blank' HREF='%s'>%s</A></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td></tr>", $ViewPage, $myrow["OrderNo"], $myrow["Name"], $myrow["BrName"], $myrow["CustomerRef"],$FormatedOrderDate,$FormatedDelDate, $myrow["DeliverTo"], $FormatedOrderValue);

		$j++;
		If ($j == 12){
			$j=1;
			echo $tableheader;
		}
//end of page full new headings if
	}
//end of while loop

	echo "</TABLE>";


}

echo "</form>";
include("includes/footer.inc");

?>
