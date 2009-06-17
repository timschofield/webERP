<?php

/* $Revision: 1.11 $ */

/* Script to delete a credit note - it expects and credit note number to delete
not included on any menu for obvious reasons

must be called directly with path/DeleteCreditnote.php?CreditNoteNo=???????

!! */

$PageSecurity=15;

include ('includes/session.inc');
$title = _('Delete Credit Note');
include('includes/header.inc');


if (!isset($_GET['CreditNoteNo'])){
        prnMsg(_('This page must be called with the credit note number') . ' - ' . _('it is not intended for use by non-system administrators'),'info');
}
/*get the order number that was credited */

$SQL = 'SELECT order_ FROM debtortrans WHERE transno=' . $_GET['CreditNoteNo'] . ' AND type=11';
$Result = DB_query($SQL, $db);

$myrow = DB_fetch_row($Result);
$OrderNo = $myrow[0];

/*Now get the stock movements that were credited into an array */

$SQL = 'SELECT stockid,
               loccode,
               debtorno,
               branchcode,
               prd,
               qty
        FROM stockmoves
        WHERE transno =' .$_GET['CreditNoteNo'] . ' AND type=11';
$Result = DB_query($SQL,$db);

$i=0;

While ($myrow = DB_fetch_array($Result)){
	$StockMovement[$i] = $myrow;
	$i++;
}

prnMsg(_('The number of stock movements to be deleted is') . ': ' . DB_num_rows($Result),'info');


$Result = DB_Txn_Begin($db); /* commence a database transaction */
/*Now delete the DebtorTrans */

$SQL = 'DELETE FROM debtortrans
               WHERE transno =' . $_GET['CreditNoteNo'] . ' AND Type=11';
$DbgMsg = _('The SQL that failed was');
$ErrMsg = _('A problem was encountered trying to delete the Debtor transaction record');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/*Now reverse updated SalesOrderDetails for the quantities credited */

foreach ($StockMovement as $CreditLine) {

	$SQL = 'UPDATE salesorderdetails SET qtyinvoiced = qtyinvoiced - ' . $CreditLine['qty'] . '
                       WHERE orderno = ' . $OrderNo . "
                       AND stkcode = '" . $CreditLine['stockid'] . "'";

	$ErrMsg =_('A problem was encountered attempting to reverse the update the sales order detail record') . ' - ' . _('the SQL server returned the following error message');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg, true);

/*reverse the update to LocStock */

	$SQL = 'UPDATE locstock SET locstock.quantity = locstock.quantity + ' . $CreditLine['qty'] . "
             WHERE  locstock.stockid = '" . $CreditLine['stockid'] . "'
             AND loccode = '" . $CreditLine['loccode'] . "'";

	$ErrMsg = _('SQL to reverse update to the location stock records failed with the error');

	$Result = DB_query($SQL, $db,$ErrMsg,$DbgMsg, true);

/*Delete Sales Analysis records */
	$SQL = 'DELETE FROM salesanalysis
                       WHERE periodno = ' . $CreditLine['prd'] . "
                       AND cust='" . $CreditLine['debtorno'] . "'
                       AND custbranch = '" . $CreditLine['branchcode'] . "'
                       AND qty = " . $CreditLine['qty'] . "
                       AND stockid = '" . $CreditLine['stockid'] . "'";

	$ErrMsg = _('The SQL to delete the sales analysis records with the message');

	$Result = DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);
}

/* Delete the stock movements  */

$SQL = 'DELETE FROM stockmoves
               WHERE type=11 AND transno = ' . $_GET['CreditNoteNo'];

$ErrMsg = _('SQL to delete the stock movement record failed with the message');
$Result = DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);
prnMsg(_('Deleted the credit note stock movements').'info');
echo '<br><br>';

$result = DB_Txn_Commit($db);
prnMsg(_('Credit note number') . ' ' . $_GET['CreditNoteNo'] . ' ' . _('has been completely deleted') . '. ' . _('To ensure the integrity of the general ledger transactions must be reposted from the period the credit note was created'),'info');

include('includes/footer.inc');
?>
