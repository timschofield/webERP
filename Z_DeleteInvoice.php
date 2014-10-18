<?php

/* $Id: Z_DeleteInvoice.php 5784 2012-12-29 04:00:43Z daintree $*/

/* Script to delete an invoice expects and invoice number to delete
not included on any menu for obvious reasons
*
* STRONGLY RECOMMEND NOT USING THIS - CREDIT THE INVOICE AND RE INVOICE
* *
*
This page must be called directly using path/Z_DeleteInvoice.php?InvoiceNo=?????    !! */


include ('includes/session.inc');
$Title = _('Delete Invoice');
include('includes/header.inc');

if (!isset($_GET['InvoiceNo'])){
        prnMsg(_('This page must be called with the InvoiceNo to delete Z_DeleteInvoice.php?InvoiceNo=XX') . '. ' . _('This page should not be run by non-system administrators'),'info');
        include('includes/footer.inc');
        exit;
}
/*Get the order number that was invoiced */

$SQL = "SELECT order_, id
		FROM debtortrans
		WHERE debtortrans.type = 10
		AND transno = '" . $_GET['InvoiceNo'] . "'";

$Result = DB_query($SQL,$db);
$myrow = DB_fetch_row($Result);

$ProcessingOrder = $myrow[0];
$IDDebtorTrans = $myrow[1];

/*Now get the stock movements that were invoiced into an array */

//$SQL = 'SELECT stockid,
//               loccode,
//               bundle, <-- This column does not exist
//               debtorno,
//               branchcode,
//               prd,
//               qty,
//               mbflag

// We now use fully qualified column names
$SQL = "SELECT stockmoves.stockid,
               stockmoves.loccode,
               stockmoves.debtorno,
               stockmoves.branchcode,
               stockmoves.prd,
               stockmoves.qty,
               stockmaster.mbflag
        FROM stockmoves INNER JOIN stockmaster
             ON stockmoves.stockid = stockmaster.stockid
        WHERE transno ='" .$_GET['InvoiceNo'] . "' AND type=10";

$Result = DB_query($SQL,$db);

$i=0;

While ($myrow = DB_fetch_array($Result)){
	$StockMovement[$i] = $myrow;
	$i++;
}

prnMsg(_('The number of stock movements to be deleted is') . ': ' . DB_num_rows($Result),'info');

/*Setup a database transaction */
$result = DB_Txn_Begin($db);

/*Delete any log entries */

$SQL = "DELETE FROM orderdeliverydifferenceslog
               WHERE orderno = '". $ProcessingOrder . "'
               AND invoiceno = '" . $_GET['InvoiceNo'] . "'";

$ErrMsg = _('The SQL to delete the delivery differences records failed because');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
prnMsg(_('Any order delivery differences records have been deleted'),'info');

/*Now delete the custallocns */

$SQL = "DELETE custallocns FROM custallocns
        WHERE transid_allocto ='" . $IDDebtorTrans . "'";

$DbgMsg = _('The SQL that failed was');
$ErrMsg = _('The custallocns record could not be deleted') . ' - ' . _('the sql server returned the following error');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

prnMsg(_('The custallocns record has been deleted'),'info');

/*Now delete the debtortranstaxes */

$SQL = "DELETE debtortranstaxes FROM debtortranstaxes
               WHERE debtortransid ='" . $IDDebtorTrans . "'";
$DbgMsg = _('The SQL that failed was');
$ErrMsg = _('The debtortranstaxes record could not be deleted') . ' - ' . _('the sql server returned the following error');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

prnMsg(_('The debtortranstaxes record has been deleted'),'info');


/*Now delete the DebtorTrans */

$SQL = "DELETE FROM debtortrans
               WHERE transno ='" . $_GET['InvoiceNo'] . "'
               AND debtortrans.type=10";
$DbgMsg = _('The SQL that failed was');
$ErrMsg = _('The debtorTrans record could not be deleted') . ' - ' . _('the sql server returned the following error');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

prnMsg(_('The debtor transaction record has been deleted'),'info');


/*Now reverse updated SalesOrderDetails for the quantities invoiced and the actual dispatch dates. */

foreach ($StockMovement as $OrderLine) {

	$SQL = "UPDATE salesorderdetails SET qtyinvoiced = qtyinvoiced - " . $OrderLine['qty'] . ",
                                             completed = 0
                                WHERE orderno = '" . $ProcessingOrder . "'
                                AND stkcode = '" . $OrderLine['stockid'] . "'";

	$ErrMsg = _('The SQL to reverse the update of the sales order detail records failed because');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
    prnMsg(_('The sales order records have been updated as not invoiced'),'info');
/*reverse the update to LocStock */
    if ($OrderLine['mbflag']!='A' AND $OrderLine['mbflag']!='D'){

        	$ErrMsg = _('The SQL to reverse update to the location stock records failed because');
	        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
             prnMsg(_('Reversed the location stock quantities for items that decreased'),'info');
    }

/*This is a problem - should only update sales analysis what happens where there
have been previous sales to the same customer/branch for the same item
Delete Sales Analysis records */
	$SQL = "DELETE FROM salesanalysis
			   WHERE  periodno = '" . $OrderLine['prd'] . "'
			   AND cust='" . $OrderLine['debtorno'] . "'
			   AND custbranch = '" . $OrderLine['branchcode'] . "'
			   AND qty = '" . $OrderLine['qty'] . "'
			   AND stockid = '" . $OrderLine['stockid'] . "'";

	$ErrMsg = _('The SQL to delete the sales analysis records failed because');

	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg,true);
	prnMsg(_('Sales analysis records deleted') . ' - ' . _('this deleted all sales analysis for the customer/branch and items on this invoice'),'info');
}

$SQL = "DELETE stockmovestaxes.* FROM stockmovestaxes INNER JOIN stockmoves
		ON stockmovestaxes.stkmoveno=stockmoves.stkmoveno
		WHERE stockmoves.type=10 AND stockmoves.transno = '" . $_GET['InvoiceNo'] . "'";

$ErrMsg = _('SQL to delete the stock movement tax records failed with the message');
$Result = DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);
prnMsg(_('Deleted the credit note stock move taxes').'info');
echo '<br /><br />';

/* Delete the stock movements  */
$SQL = "DELETE FROM stockmoves WHERE type=10 AND transno = '" . $_GET['InvoiceNo'] . "'";

$ErrMsg = _('The SQL to delete the stock movement records failed because');
$Result = DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);
prnMsg(_('The stock movement records associated with the invoice have been deleted'),'info');
echo '<br /><br />';

/* Delete any GL Transaction records*/
$SQL = "DELETE FROM gltrans WHERE gltrans.type=10 AND gltrans.typeno='" . $_GET['InvoiceNo'] . "'";
$ErrMsg = _('The SQL to delete the general ledger journal records failed because');
$Result = DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);
prnMsg(_('The GL journal records associated with the invoice have been deleted'),'info');

$result = DB_Txn_Commit($db);

prnMsg(_('Invoice number') . ' ' . $_GET['InvoiceNo'] . ' ' . _('has been deleted'),'info');

include('includes/footer.inc');
?>