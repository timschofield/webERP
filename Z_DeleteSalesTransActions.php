<?php

/*Script to Delete all sales transactions*/

$title = "UTILITY PAGE That deletes all records from selected transaction tables";
$PageSecurity=15;
include ("includes/session.inc");
include("includes/header.inc");

if (isset($_POST['ProcessDeletions'])){

	if ($_POST['SalesAnalysis']=="on"){

		echo "<BR>Deleting sales analysis records";

		$sql = "TRUNCATE TABLE SalesAnalysis";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to delete Sales Analysis records failed, the SQL statement was:<BR>$sql";
			exit;
		}
	}

	if ($_POST['DebtorTrans']=="on"){

		echo "<BR>Deleting customer statement transactions and allocation records";

		$sql = "TRUNCATE TABLE DebtorTrans";
		$Result = DB_query($sql,$db);
		$sql = "TRUNCATE TABLE CustAllocns";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to delete customer transaction records failed, the SQL statement was:<BR>$sql";
			exit;
		}
		$sql = "UPDATE SysTypes SET TypeNo =0 WHERE TypeID =10 OR TypeID=11 OR TypeID=15 OR TypeID=12";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to update the transaction number of stock receipts has failed, the SQL statement was:<BR>$sql";
			exit;
		}
	}
	if ($_POST['SalesOrders']=="on"){

		echo "<BR>Deleting all sales order records";

		$sql = "TRUNCATE TABLE SalesOrderDetails";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to delete sales order detail records failed, the SQL statement was:<BR>$sql";
			exit;
		}
		$sql = "TRUNCATE TABLE SalesOrders";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to delete sales order header records failed, the SQL statement was:<BR>$sql";
			exit;
		}
		$sql = "UPDATE SysTypes SET TypeNo =0 WHERE TypeID =30";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to update the transaction number of sales orders has failed, the SQL statement was:<BR>$sql";
			exit;
		}

	}
	if ($_POST['ZeroStock']=="on"){

		echo "<BR>Making stock for all parts and locations nil";

		$sql = "UPDATE LocStock SET Quantity=0";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to make all stocks zero failed, the SQL statement was:<BR>$sql";
			exit;
		}
	}
	if ($_POST['ZeroSalesOrders']=="on"){

		echo "<BR>Making the quantity invoiced zero on all orders";

		$sql = "UPDATE SalesOrderDetails SET QtyInvoiced=0, Completed=0";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to un-invoice all sales orders failed, the SQL statement was:<BR>$sql";
			exit;
		}
	}
	if ($_POST['SalesGL']=="on"){

		echo "<BR>Deleting all sales related GL Transactions";

		$sql = "DELETE FROM GLTrans WHERE Type>=10 and Type <=15";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to delete sales related GL Transactions failed, the SQL statement was:<BR>$sql";
			exit;
		}
	}

	if ($_POST['StockGL']=="on"){

		echo "<BR>Deleting all stock related GL Transactions";

		$sql = "DELETE FROM GLTrans WHERE Type=25 OR Type=17 OR Type=26 OR Type=28";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to delete stock related GL Transactions failed, the SQL statement was:<BR>$sql";
			exit;
		}
	}
	if ($_POST['ZeroPurchOrders']=="on"){

		echo "<BR>Zeroing all purchase order quantities received and uncompleting all purchase orders";

		$sql = "UPDATE PurchOrderDetails SET QuantityRecd=0, Completed=0";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to zero quantity received for all purchase orders line items and uncompleted all purchase order line items, the SQL statement was:<BR>$sql";
			exit;
		}
	}
	if ($_POST['PurchOrders']=="on"){

		echo "<BR>Deleting all Purchase Orders";

		$sql = "DELETE FROM PurchOrderDetails";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to delete all purchase order details failed, the SQL statement was:<BR>$sql";
			exit;
		}
		$sql = "DELETE FROM PurchOrders";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to delete all purchase orders failed, the SQL statement was:<BR>$sql";
			exit;
		}
		$sql = "UPDATE SysTypes SET TypeNo=0 WHERE TypeID =18";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to update the transaction number of stock receipts has failed, the SQL statement was:<BR>$sql";
			exit;
		}
	}
	if ($_POST['ReceiptStockMoves']=="on"){

		echo "<BR>Deleting all stock movements for receipt of stocks";

		$sql = "DELETE FROM StockMoves WHERE Type=25";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to delete all stock movements for the receipt of goods failed, the SQL statement was:<BR>$sql";
			exit;
		}
	$sql = "UPDATE SysTypes SET TypeNo=0 WHERE TypeID =16 OR TypeID=17";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to reinitialise to 0 the transaction number of stock adjustments and location transfers has failed, the SQL statement was:<BR>$sql";
			exit;
		}
	}
	if ($_POST['GRNs']=="on"){

		echo "<BR>Deleting all GRN records";

		$sql = "TRUNCATE TABLE GRNs";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to delete Sales Analysis records failed, the SQL statement was:<BR>$sql";
			exit;
		}
		$sql = "UPDATE SysTypes SET TypeID =1 WHERE TypeNo =25";
		$Result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The SQL to update the transaction number of stock receipts has failed, the SQL statement was:<BR>$sql";
			exit;
		}
	}
}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' METHOD=POST>";

?>
<P><CENTER><TABLE>
<TR><TD>Delete All Sales Analysis</TD><TD><INPUT TYPE=checkbox NAME=SalesAnalysis></TD></TR>
<TR><TD>Delete All statement transactions</TD><TD><INPUT TYPE=checkbox NAME=DebtorTrans></TD></TR>
<TR><TD>Zero All stock balances</TD><TD><INPUT TYPE=checkbox NAME=ZeroStock></TD></TR>
<TR><TD>Make Invoiced Qty Of All Sales Orders Nil</TD><TD><INPUT TYPE=checkbox NAME=ZeroSalesOrders></TD></TR>
<TR><TD>Delete All Sales Orders</TD><TD><INPUT TYPE=checkbox NAME=SalesOrders></TD></TR>
<TR><TD>Zero Received Qty of all purchase orders</TD><TD><INPUT TYPE=checkbox NAME=ZeroPurchOrders></TD></TR>
<TR><TD>Delete All Purchase Orders</TD><TD><INPUT TYPE=checkbox NAME=PurchOrders></TD></TR>
<TR><TD>Delete All Sales related stock movements</TD><TD><INPUT TYPE=checkbox NAME=SalesStockMoves></TD></TR>
<TR><TD>Delete All Stock Receipt stock movements</TD><TD><INPUT TYPE=checkbox NAME=ReceiptStockMoves></TD></TR>
<TR><TD>Delete All Sales GL Transactions</TD><TD><INPUT TYPE=checkbox NAME=SalesGL></TD></TR>
<TR><TD>Delete All Stock GL Transactions</TD><TD><INPUT TYPE=checkbox NAME=StockGL></TD></TR>
<TR><TD>Delete All PO Goods Received (GRNs)</TD><TD><INPUT TYPE=checkbox NAME=GRNs></TD></TR>
</TABLE>

<INPUT TYPE=SUBMIT NAME=ProcessDeletions VALUE=Process>

</FORM>

<? include("includes/footer.inc"); ?>
