<?php

/* Script to delete a credit note - it expects and credit note number to delete
not included on any menu for obvious reasons

STRONGLY RECOMMEND NOT USING THIS -  RE INVOICE INSTEAD

must be called directly with path/DeleteCreditnote.php?CreditNoteNo=???????

!! */


include('includes/session.php');
$Title = __('Delete Credit Note');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');


if (!isset($_GET['CreditNoteNo'])){
        prnMsg(__('This page must be called with the credit note number') . ' - ' . __('it is not intended for use by non-system administrators'),'info');
}
/*get the order number that was credited */

$SQL = "SELECT order_, id
		FROM debtortrans
		WHERE transno='" . $_GET['CreditNoteNo'] . "' AND type='11'";
$Result = DB_query($SQL);

$MyRow= DB_fetch_row($Result);
$OrderNo = $MyRow[0];
$IDDebtorTrans = $MyRow[1];

/*Now get the stock movements that were credited into an array */

$SQL = "SELECT stockid,
				loccode,
				debtorno,
				branchcode,
				prd,
				qty
			FROM stockmoves
			WHERE transno ='" .$_GET['CreditNoteNo'] . "' AND type='11'";
$Result = DB_query($SQL);

$i=0;

while ($MyRow = DB_fetch_array($Result)){
	$StockMovement[$i] = $MyRow;
	$i++;
}

prnMsg(__('The number of stock movements to be deleted is') . ': ' . DB_num_rows($Result), 'info');


DB_Txn_Begin(); /* commence a database transaction */

/*Now delete the custallocns */

$SQL = "DELETE FROM custallocns
        WHERE transid_allocfrom ='" . $IDDebtorTrans . "'";

$ErrMsg = __('The custallocns record could not be deleted') . ' - ' . __('the sql server returned the following error');
$Result = DB_query($SQL, $ErrMsg, '', true);

prnMsg(__('The custallocns record has been deleted'), 'info');

/*Now delete the debtortranstaxes */

$SQL = "DELETE debtortranstaxes FROM debtortranstaxes
               WHERE debtortransid ='" . $IDDebtorTrans . "'";
$ErrMsg = __('The debtortranstaxes record could not be deleted') . ' - ' . __('the sql server returned the following error');
$Result = DB_query($SQL, $ErrMsg, '', true);

prnMsg(__('The debtortranstaxes record has been deleted'), 'info');

/*Now delete the DebtorTrans */
$SQL = "DELETE FROM debtortrans
               WHERE transno ='" . $_GET['CreditNoteNo'] . "' AND Type=11";
$ErrMsg = __('A problem was encountered trying to delete the Debtor transaction record');
$Result = DB_query($SQL, $ErrMsg, '', true);

/*Now reverse updated SalesOrderDetails for the quantities credited */

foreach ($StockMovement as $CreditLine) {

	$SQL = "UPDATE salesorderdetails SET qtyinvoiced = qtyinvoiced - " . $CreditLine['qty'] . "
                       WHERE orderno = '" . $OrderNo . "'
                       AND stkcode = '" . $CreditLine['stockid'] . "'";

	$ErrMsg =__('A problem was encountered attempting to reverse the update the sales order detail record') . ' - ' . __('the SQL server returned the following error message');
	$Result = DB_query($SQL, $ErrMsg, '', true);

/*reverse the update to LocStock */

	$SQL = "UPDATE locstock SET locstock.quantity = locstock.quantity + " . $CreditLine['qty'] . "
			             WHERE  locstock.stockid = '" . $CreditLine['stockid'] . "'
			             AND loccode = '" . $CreditLine['loccode'] . "'";

	$ErrMsg = __('SQL to reverse update to the location stock records failed with the error');

	$Result = DB_query($SQL, $ErrMsg, '', true);

/*Delete Sales Analysis records
 * This is unreliable as the salesanalysis record contains totals for the item cust custbranch periodno */
	$SQL = "DELETE FROM salesanalysis
                       WHERE periodno = '" . $CreditLine['prd'] . "'
                       AND cust='" . $CreditLine['debtorno'] . "'
                       AND custbranch = '" . $CreditLine['branchcode'] . "'
                       AND qty = '" . $CreditLine['qty'] . "'
                       AND stockid = '" . $CreditLine['stockid'] . "'";

	$ErrMsg = __('The SQL to delete the sales analysis records with the message');

	$Result = DB_query($SQL, $ErrMsg, '', true);
}

/* Delete the stock movements  */
$SQL = "DELETE stockmovestaxes.* FROM stockmovestaxes INNER JOIN stockmoves
			ON stockmovestaxes.stkmoveno=stockmoves.stkmoveno
               WHERE stockmoves.type=11 AND stockmoves.transno = '" . $_GET['CreditNoteNo'] . "'";

$ErrMsg = __('SQL to delete the stock movement tax records failed with the message');
$Result = DB_query($SQL, $ErrMsg, '', true);
prnMsg(__('Deleted the credit note stock move taxes'), 'info');
echo '<br /><br />';


/* Delete the stock serial movements  */
$SQL = "DELETE stockserialmoves.* FROM stockserialmoves INNER JOIN stockmoves
			ON stockserialmoves.stkmoveno=stockmoves.stkmoveno
               WHERE stockmoves.type=11 AND stockmoves.transno = '" . $_GET['CreditNoteNo'] . "'";

$ErrMsg = __('SQL to delete the stock serial moves records failed with the message');
$Result = DB_query($SQL, $ErrMsg, '', true);
prnMsg(__('Deleted the credit note stock serial moves'), 'info');
echo '<br /><br />';


$SQL = "DELETE FROM stockmoves
               WHERE type=11 AND transno = '" . $_GET['CreditNoteNo'] . "'";

$ErrMsg = __('SQL to delete the stock movement record failed with the message');
$Result = DB_query($SQL, $ErrMsg, '', true);
prnMsg(__('Deleted the credit note stock movements'), 'info');
echo '<br /><br />';


$SQL = "DELETE FROM gltrans WHERE type=11 AND typeno= '" . $_GET['CreditNoteNo'] . "'";
$ErrMsg = __('SQL to delete the gl transaction records failed with the message');
$Result = DB_query($SQL, $ErrMsg, '', true);
prnMsg(__('Deleted the credit note general ledger transactions'), 'info');

DB_Txn_Commit();
prnMsg(__('Credit note number') . ' ' . $_GET['CreditNoteNo'] . ' ' . __('has been completely deleted') . '. ' . __('To ensure the integrity of the general ledger transactions must be reposted from the period the credit note was created'), 'info');

include('includes/footer.php');
