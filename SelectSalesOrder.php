<?php
/* $Revision: 1.2 $ */
include("includes/DateFunctions.inc");

$title = "Search Outstanding Sales Orders";

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");

echo "<FORM ACTION=" . $_SERVER['PHP_SELF'] ."?" .SID . " METHOD=POST>";


If (isset($_POST['ResetPart'])){
     unset($_REQUEST['SelectedStockItem']);
}

If (isset($_REQUEST['OrderNumber']) AND $_REQUEST['OrderNumber']!="") {
	if (!is_numeric($_REQUEST['OrderNumber'])){
		  echo "<BR><B>The Order Number entered <U>MUST</U> be numeric.</B><BR>";
		  unset ($_REQUEST['OrderNumber']);
	} else {
		echo "Order Number - " . $_REQUEST['OrderNumber'];
	}
} else {
	If (isset($_REQUEST['SelectedCustomer'])) {
		echo "For customer: " . $_REQUEST['SelectedCustomer'] . " and ";
		echo "<input type=hidden name='SelectedCustomer' value=" . $_REQUEST['SelectedCustomer'] . ">";
	}
	If (isset($_REQUEST['SelectedStockItem'])) {
		 echo "for the part: " . $_REQUEST['SelectedStockItem'] . " and <input type=hidden name='SelectedStockItem' value='" . $_REQUEST['SelectedStockItem'] . "'>";
	}
}

if ($_POST["SearchParts"]=="Search Parts Now"){

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		echo "Stock description keywords have been used in preference to the Stock code extract entered.";
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
		$SearchString = $SearchString . substr($_POST['Keywords'],$i)."%";

		$SQL = "SELECT StockMaster.StockID, Description, Sum(LocStock.Quantity) AS QOH,  Units, LocStock WHERE StockMaster.StockID=LocStock.StockID AND Description LIKE '" . $SearchString . "' AND CategoryID='" . $_POST['StockCat']. "' GROUP BY StockMaster.StockID, Description, Units ORDER BY StockMaster.StockID";
	 } elseif (isset($_POST['StockCode'])){
		$SQL = "SELECT StockMaster.StockID, Description, Sum(LocStock.Quantity) AS QOH, Units FROM StockMaster, LocStock WHERE StockMaster.StockID=LocStock.StockID AND StockMaster.StockID like '%" . $_POST['StockCode'] . "%' AND CategoryID='" . $_POST['StockCat'] . "' GROUP BY StockMaster.StockID, Description, Units ORDER BY StockMaster.StockID";
	 } elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		$SQL = "SELECT StockMaster.StockID, Description, Sum(LocStock.Quantity) AS QOH, Units FROM StockMaster, LocStock WHERE StockMaster.StockID=LocStock.StockID AND CategoryID='" . $_POST['StockCat'] ."' GROUP BY StockMaster.StockID, Description, Units ORDER BY StockMaster.StockID";
	 }

	$StockItemsResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		echo "<BR>No stock items were returned by the SQL because - " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>The SQL used to retrieve the searched parts was:<BR>$SQL";
		}
	}
}

if (isset($_POST["StockID"])){
	$StockID =$_POST["StockID"];
} elseif (isset($_GET["StockID"])){
	$StockID =$_GET["StockID"];
}

if (!isset($StockID)) {

     /* Not appropriate really to restrict search by date since may miss older
     ouststanding orders
	$OrdersAfterDate = Date("d/m/Y",Mktime(0,0,0,Date("m")-2,Date("d"),Date("Y")));
     */

	if ($_REQUEST['OrderNumber']=="" OR !$_REQUEST['OrderNumber']){

		echo "Order number: <INPUT type=text name='OrderNumber' MAXLENGTH =8 SIZE=9>  From Stock Location:<SELECT name='StockLocation'> ";
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

		echo "</SELECT>  <INPUT TYPE=SUBMIT NAME='SearchOrders' VALUE='Search Orders'>";
	}

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
		echo "<OPTION VALUE='". $myrow1["CategoryID"] . "'>" . $myrow1["CategoryDescription"];
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

If (isset($StockItemsResult)) {

	echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>";
	$TableHeader = "<TR><TD class='tableheader'>Code</TD><TD class='tableheader'>Description</TD><TD class='tableheader'>On Hand</TD><TD class='tableheader'>Units</TD></TR>";
	echo $TableHeader;

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

		printf("<td><FONT SIZE=1><INPUT TYPE=SUBMIT NAME='SelectedStockItem' VALUE='%s'</FONT></td><td><FONT SIZE=1>%s</FONT></td><td ALIGN=RIGHT><FONT SIZE=1>%s</FONT></td><td><FONT SIZE=1>%s</FONT></td></tr>", $myrow["StockID"], $myrow["Description"], $myrow["QOH"],$myrow["Units"]);

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

	if (isset($_REQUEST['OrderNumber']) && $_REQUEST['OrderNumber'] !="") {
			$SQL = "SELECT SalesOrders.OrderNo, DebtorsMaster.Name, CustBranch.BrName, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliveryDate,  SalesOrders.DeliverTo, Sum(SalesOrderDetails.UnitPrice*SalesOrderDetails.Quantity*(1-SalesOrderDetails.DiscountPercent)) AS OrderValue FROM SalesOrders, SalesOrderDetails, DebtorsMaster, CustBranch WHERE SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND SalesOrders.BranchCode = CustBranch.BranchCode AND SalesOrders.DebtorNo = DebtorsMaster.DebtorNo AND DebtorsMaster.DebtorNo = CustBranch.DebtorNo AND SalesOrderDetails.Completed=0 AND SalesOrders.OrderNo=". $_REQUEST['OrderNumber'] ." GROUP BY SalesOrders.OrderNo";
	} else {
	      /* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */

		if (isset($_REQUEST['SelectedCustomer'])) {

			if (isset($_REQUEST['SelectedStockItem'])) {
				$SQL = "SELECT SalesOrders.OrderNo, DebtorsMaster.Name, CustBranch.BrName, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliveryDate,  SalesOrders.DeliverTo, SalesOrderDetails.UnitPrice*SalesOrderDetails.Quantity*(1-SalesOrderDetails.DiscountPercent)) AS OrderValue FROM SalesOrders, SalesOrderDetails, DebtorsMaster, CustBranch WHERE SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND SalesOrders.DebtorNo = DebtorsMaster.DebtorNo AND DebtorsMaster.DebtorNo = CustBranch.DebtorNo AND SalesOrders.BranchCode = CustBranch.BranchCode AND SalesOrderDetails.Completed=0 AND SalesOrderDetails.StkCode='". $_REQUEST['SelectedStockItem'] ."' AND SalesOrders.DebtorNo='" . $_REQUEST['SelectedCustomer'] ."' AND SalesOrders.FromStkLoc = '". $_POST['StockLocation'] . "' GROUP BY SalesOrders.OrderNo, SalesOrders.DebtorNo, SalesOrders.BranchCode, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo";

			} else {
				$SQL = "SELECT SalesOrders.OrderNo, DebtorsMaster.Name, CustBranch.BrName, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo, SalesOrders.DeliveryDate, Sum(SalesOrderDetails.UnitPrice*SalesOrderDetails.Quantity*(1-SalesOrderDetails.DiscountPercent)) AS OrderValue FROM SalesOrders, SalesOrderDetails, DebtorsMaster, CustBranch WHERE SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND SalesOrders.DebtorNo = DebtorsMaster.DebtorNo AND DebtorsMaster.DebtorNo = CustBranch.DebtorNo AND SalesOrders.BranchCode = CustBranch.BranchCode AND SalesOrderDetails.Completed=0 AND SalesOrders.DebtorNo='" . $_REQUEST['SelectedCustomer'] . "' AND SalesOrders.FromStkLoc = '". $_POST['StockLocation'] . "' GROUP BY SalesOrders.OrderNo, SalesOrders.DebtorNo, SalesOrders.BranchCode, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo";

			}
		} else { //no customer selected
			if (isset($_REQUEST['SelectedStockItem'])) {
				$SQL = "SELECT SalesOrders.OrderNo, DebtorsMaster.Name, CustBranch.BrName, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo, SalesOrders.DeliveryDate, Sum(SalesOrderDetails.UnitPrice*SalesOrderDetails.Quantity*(1-SalesOrderDetails.DiscountPercent)) AS OrderValue FROM SalesOrders, SalesOrderDetails, DebtorsMaster, CustBranch WHERE SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND SalesOrders.DebtorNo = DebtorsMaster.DebtorNo AND DebtorsMaster.DebtorNo = CustBranch.DebtorNo AND SalesOrders.BranchCode = CustBranch.BranchCode AND SalesOrderDetails.Completed=0 AND SalesOrderDetails.StkCode='". $_REQUEST['SelectedStockItem'] . "' AND SalesOrders.FromStkLoc = '". $_POST['StockLocation'] . "' GROUP BY SalesOrders.OrderNo, SalesOrders.DebtorNo, SalesOrders.BranchCode, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo";
			} else {
				$SQL = "SELECT SalesOrders.OrderNo, DebtorsMaster.Name, CustBranch.BrName, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo, SalesOrders.DeliveryDate, Sum(SalesOrderDetails.UnitPrice*SalesOrderDetails.Quantity*(1-SalesOrderDetails.DiscountPercent)) AS OrderValue FROM SalesOrders, SalesOrderDetails, DebtorsMaster, CustBranch WHERE SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND SalesOrders.DebtorNo = DebtorsMaster.DebtorNo AND DebtorsMaster.DebtorNo = CustBranch.DebtorNo AND SalesOrders.BranchCode = CustBranch.BranchCode AND SalesOrderDetails.Completed=0 AND SalesOrders.FromStkLoc = '". $_POST['StockLocation'] . "' GROUP BY SalesOrders.OrderNo, SalesOrders.DebtorNo, SalesOrders.BranchCode, SalesOrders.CustomerRef, SalesOrders.OrdDate, SalesOrders.DeliverTo ";
			}

		} //end selected customer
	} //end not order number selected

	$SalesOrdersResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		echo "No orders were returned by the SQL because - " . DB_error_msg($db);
		echo "<BR>$SQL";
	}

	/*show a table of the orders returned by the SQL */

	echo "<TABLE CELLPADDING=2 COLSPAN=7 WIDTH=100%>";

	$tableheader = "<TR BGCOLOR =#800000><TD class='tableheader'>Modify</TD><TD class='tableheader'>Invoice</TD><TD class='tableheader'>Disp. Note</TD><TD class='tableheader'>Customer</TD><TD class='tableheader'>Branch</TD><TD class='tableheader'>Cust Order #</TD><TD class='tableheader'>Order Date</TD><TD class='tableheader'>Req Del Date</TD><TD class='tableheader'>Delivery To</TD><TD class='tableheader'>Order Total</TD></TR>";
	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($SalesOrdersResult)) {


		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		$ModifyPage = $rootpath . "/SelectOrderItems.php?" . SID . "ModifyOrderNumber=" . $myrow["OrderNo"];
		$Confirm_Invoice = $rootpath . "/ConfirmDispatch_Invoice.php?" . SID . "OrderNumber=" .$myrow["OrderNo"];
		$PrintDispatchNote = $rootpath . "/PrintCustOrder.php?" . SID . "TransNo=" . $myrow["OrderNo"];
		$FormatedDelDate = ConvertSQLDate($myrow["DeliveryDate"]);
		$FormatedOrderDate = ConvertSQLDate($myrow["OrdDate"]);
		$FormatedOrderValue = number_format($myrow["OrderValue"],2);

		printf("<td><A HREF='%s'>%s</A></td><td><A HREF='%s'>Invoice</A></td><td><A target='_blank' HREF='%s'>Print</A></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td></tr>", $ModifyPage, $myrow["OrderNo"], $Confirm_Invoice, $PrintDispatchNote, $myrow["Name"], $myrow["BrName"], $myrow["CustomerRef"],$FormatedOrderDate,$FormatedDelDate, $myrow["DeliverTo"], $FormatedOrderValue);

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

?>
</FORM>

<?php } //end StockID already selected

include("includes/footer.inc");
?>

