<?php
/* $Revision: 1.2 $ */
/* Script to delete an invoice expects and invoice number to delete 
not included on any menu for obvious reasons 
This page must be called directly using path/Z_DeleteInvoice.php?InvoiceNo=?????    !! */

$PageSecurity=15;

include ("includes/session.inc");
include("includes/header.inc");


/*Get the order number that was invoiced */

$SQL = "SELECT Order_ FROM DebtorTrans WHERE Type = 10	and TransNo = " . $_GET['InvoiceNo'];

$Result = DB_query($SQL,$db);  
$myrow = DB_fetch_row($Result);

$ProcessingOrder = $myrow[0];

/*Now get the stock movements that were invoiced into an array */


$SQL = "SELECT StockID, LocCode, Bundle, DebtorNo, BranchCode, Prd, Qty FROM StockMoves WHERE TransNo =" .$_GET['InvoiceNo'] . " AND Type=10";

$Result = DB_query($SQL,$db);  

$i=0;

While ($myrow = DB_fetch_array($Result)){
	$StockMovement[$i] = $myrow;
	$i++;
}

echo "<P>The number of stock movements to be deleted is: " . DB_num_rows($Result);

/*Now delete the DebtorTrans */

$SQL = "Delete FROM DebtorTrans WHERE TransNo =" . $_GET['InvoiceNo'] . " AND Type=10";
$Result = DB_query($SQL,$db);

echo "<P>SQL to delete the Debtor transaction record:<BR>$SQL<BR>";

/*Delete any log entries */ 

$SQL = "DELETE FROM OrderDeliveryDifferencesLog	WHERE OrderNo = ". $ProcessingOrder . " AND InvoiceNo = " . $_GET['InvoiceNo'];

echo "<P>SQL to delete the delivery differences record(s):<BR>$SQL<BR>";
$Result = DB_query($SQL,$db); 


/*Now reverse updated SalesOrderDetails for the quantities invoiced and the actual dispatch dates. */

foreach ($StockMovement as $OrderLine) {
	
	$SQL = "UPDATE SalesOrderDetails SET QtyInvoiced = QtyInvoiced - " . $OrderLine["Qty"] . ", Completed = 0 WHERE OrderNo = " . $ProcessingOrder . " AND StkCode = '" . $OrderLine["StockID"] . "'";

	echo "SQL to reverse update the sales order detail record:<BR>$SQL<BR>";
	$Result = DB_query($SQL,$db); 

/*reverse the update to LocStock */	

	$SQL = "UPDATE LocStock SET LocStock.Quantity = LocStock.Quantity + " . $OrderLine["Qty"] . " WHERE  LocStock.StockID = '" . $OrderLine["StockID"] . "' AND LocCode = '" . $OrderLine["LocCode"] . "'";

	echo "SQL to reverse update to the location stock records:<BR>$SQL<BR>";
		
	$Result = DB_query($SQL, $db);
		
/*Delete Sales Analysis records */
	$SQL = "DELETE FROM SalesAnalysis WHERE  PeriodNo = " . $OrderLine["Prd"] . " AND Cust='" . $OrderLine["DebtorNo"] . "' AND CustBranch = '" . $OrderLine["BranchCode"] . "' AND Qty = " . $OrderLine["Qty"] . " AND StockID = '" . $OrderLine["StockID"] . "'"; 

	echo "SQL to delete the sales analysis records:<BR>$SQL<BR>";
		
	$Result = DB_query($SQL, $db);

}

/* Delete the stock movements  */

$SQL = "DELETE FROM StockMoves WHERE Type=10 AND TransNo = " . $_GET['InvoiceNo'];

echo "SQL to delete the stock movement record:<BR>$SQL<BR>";
$Result = DB_query($SQL, $db);
	

/* Delete any GL Transaction records*/

$SQL = "DELETE FROM GLTrans WHERE Type=10 AND TypeNo = " . $_GET['InvoiceNo'];
echo "SQL to delete all GL posting:<BR>$SQL<BR>";
$Result = DB_query($SQL,$db);

include("includes/footer.inc");
?>
