<?php
/* $Revision: 1.3 $ */
/* Script to delete an invoice expects and invoice number to delete
not included on any menu for obvious reasons
This page must be called directly using path/Z_DeleteInvoice.php?InvoiceNo=?????    !! */

$PageSecurity=15;

include ('includes/session.inc');
$title = _('Delete Invoice');
include('includes/header.inc');

if (!isset($_GET['InvoiceNo'])){
        prnMsg(_('This page must be called with the InvoiceNo to delete Z_DeleteInvoice.php?InvoiceNo=XX. This page should not be run by non-system administrators'),'info');
        include('includes/footer.inc');
        exit;
}
/*Get the order number that was invoiced */

$SQL = 'SELECT Order_
               FROM DebtorTrans
        WHERE Type = 10	and TransNo = ' . $_GET['InvoiceNo'];

$Result = DB_query($SQL,$db);
$myrow = DB_fetch_row($Result);

$ProcessingOrder = $myrow[0];

/*Now get the stock movements that were invoiced into an array */

$SQL = 'SELECT StockID,
               LocCode,
               Bundle,
               DebtorNo,
               BranchCode,
               Prd,
               Qty,
               MBflag
        FROM StockMoves INNER JOIN StockMaster
             ON StockMoves.StockID = StockMaster.StockID
        WHERE TransNo =' .$_GET['InvoiceNo'] . ' AND Type=10';

$Result = DB_query($SQL,$db);

$i=0;

While ($myrow = DB_fetch_array($Result)){
	$StockMovement[$i] = $myrow;
	$i++;
}

prnMsg(_('The number of stock movements to be deleted is') . ': ' . DB_num_rows($Result),'info');

/*Setup a database transaction */
$result = DB_query('BEGIN',$db);

/*Now delete the DebtorTrans */

$SQL = 'DELETE FROM DebtorTrans
               WHERE TransNo =' . $_GET['InvoiceNo'] . '
               AND Type=10';
$DbgMsg = _('The SQL that failed was');
$ErrMsg = _('The debtorTrans record could not be deleted - the sql server returned the following error');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

prnMsg(_('The debtor transaction record has been deleted'),'info');

/*Delete any log entries */

$SQL = 'DELETE FROM OrderDeliveryDifferencesLog
               WHERE OrderNo = '. $ProcessingOrder . '
               AND InvoiceNo = ' . $_GET['InvoiceNo'];

$ErrMsg = _('The SQL to delete the delivery differences record(s) falied because');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
prnMsg(_('Any order delivery differences record(s) have been deleted'),'info');

/*Now reverse updated SalesOrderDetails for the quantities invoiced and the actual dispatch dates. */

foreach ($StockMovement as $OrderLine) {

	$SQL = 'UPDATE SalesOrderDetails SET QtyInvoiced = QtyInvoiced - ' . $OrderLine['Qty'] . ',
                                             Completed = 0
                                WHERE OrderNo = ' . $ProcessingOrder . "
                                AND StkCode = '" . $OrderLine['StockID'] . "'";

	$ErrMsg = _('The SQL to reverse the update of the sales order detail records falied because');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
        prnMsg(_('The sales order records have been updated as not invoiced'),'info');
/*reverse the update to LocStock */
        if ($OrderLine['MBflag']!='A' AND $OrderLine['MBflag']!='D'){

        	$ErrMsg = _('The SQL to reverse update to the location stock records failed because');

	        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                prnMsg(_('Reversed the location stock quantities for items that decreased'),'info');
        }

/*This is a problem - should only update sales analysis what happens where there
have been previous sales to the same customer/branch for the same item
Delete Sales Analysis records */
	$SQL = 'DELETE FROM SalesAnalysis
                       WHERE  PeriodNo = ' . $OrderLine['Prd'] . "
                       AND Cust='" . $OrderLine['DebtorNo'] . "'
                       AND CustBranch = '" . $OrderLine['BranchCode'] . "'
                       AND Qty = " . $OrderLine['Qty'] . "
                       AND StockID = '" . $OrderLine['StockID'] . "'";

	$ErrMsg = _('The SQL to delete the sales analysis records failed because');

	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg,true);
	prnMsg(_('Sales analysis records deleted - this deleted all sales analysis for the customer/branch and items on this invoice'),'info');
}

/* Delete the stock movements  */

$SQL = 'DELETE FROM StockMoves WHERE Type=10 AND TransNo = ' . $_GET['InvoiceNo'];

$ErrMsg _('The SQL to delete the stock movement records failed because');
$Result = DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);
prnMsg(_('The stock movement records associated with the invoice have been deleted'),'info');
echo '<BR><BR>';
prnMsg(_('Invoice number') . ' ' . $_GET['InvoiceNo'} . ' ' . _('has been deleted'),'info');
/* Delete any GL Transaction records*/
include('includes/footer.inc');
?>
