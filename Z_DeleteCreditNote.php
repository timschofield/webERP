<?php
/* $Revision: 1.2 $ */
/* Script to delete a credit note - it expects and credit note number to delete 
not included on any menu for obvious reasons 

must be called directly with path/DeleteCreditnote.php?CreditNoteNo=???????

!! */

$PageSecurity=15;

include ("includes/session.inc");
include("includes/header.inc");


/*get the order number that was credited */

$SQL = "SELECT Order_ FROM DebtorTrans WHERE TransNo=" . $_GET['CreditNoteNo'] . " AND Type=11";
$Result = DB_query($SQL, $db);  

$myrow = DB_fetch_row($Result);
$OrderNo = $myrow[0];

/*Now get the stock movements that were credited into an array */

$SQL = "SELECT StockID, LocCode, Bundle, DebtorNo, BranchCode, Prd, Qty FROM StockMoves WHERE TransNo =" .$_GET['CreditNoteNo'] . " AND Type=11";
$Result = DB_query($SQL,$db);  

$i=0;

While ($myrow = DB_fetch_array($Result)){
	$StockMovement[$i] = $myrow;
	$i++;
}

echo "<P>The number of stock movements to be deleted is: " . DB_num_rows($Result);

/*Now delete the DebtorTrans */

$SQL = "Delete FROM DebtorTrans WHERE TransNo =" . $_GET['CreditNoteNo'] . " AND Type=11";
$Result = DB_query($SQL,$db);

echo "<P>SQL to delete the Debtor transaction record:<BR>$SQL<BR>";


/*Now reverse updated SalesOrderDetails for the quantities credited */

foreach ($StockMovement as $CreditLine) {
	
	$SQL = "UPDATE SalesOrderDetails SET QtyInvoiced = QtyInvoiced - " . $CreditLine["Qty"] . " WHERE OrderNo = " . $OrderNo . " AND StkCode = '" . $CreditLine["StockID"] . "'";

	echo "SQL to reverse update the sales order detail record:<BR>$SQL<BR>";
	$Result = DB_query($SQL,$db); 

/*reverse the update to LocStock */	

	$SQL = "UPDATE LocStock SET LocStock.Quantity = LocStock.Quantity + " . $CreditLine["Qty"] . " WHERE  LocStock.StockID = '" . $CreditLine["StockID"] . "' AND LocCode = '" . $CreditLine["LocCode"] . "'";

	echo "SQL to reverse update to the location stock records:<BR>$SQL<BR>";
		
	$Result = DB_query($SQL, $db);
		
/*Delete Sales Analysis records */
	$SQL = "DELETE FROM SalesAnalysis WHERE  PeriodNo = " . $CreditLine["Prd"] . " AND Cust='" . $CreditLine["DebtorNo"] . "' AND CustBranch = '" . $CreditLine["BranchCode"] . "' AND Qty = " . $CreditLine["Qty"] . " AND StockID = '" . $CreditLine["StockID"] . "'"; 

	echo "SQL to delete the sales analysis records:<BR>$SQL<BR>";
		
	$Result = DB_query($SQL, $db);

}

/* Delete the stock movements  */

$SQL = "DELETE FROM StockMoves WHERE Type=11 AND TransNo = " . $_GET['CreditNoteNo'];

echo "SQL to delete the stock movement record:<BR>$SQL<BR>";
$Result = DB_query($SQL, $db);
	

/* Delete any GL Transaction records*/

$SQL = "DELETE FROM GLTrans WHERE Type=11 AND TypeNo = " . $_GET['CreditNoteNo'];				echo "SQL to delete all GL posting:<BR>$SQL<BR>";
$Result = DB_query($SQL,$db);

include("includes/footer.inc");
?>
