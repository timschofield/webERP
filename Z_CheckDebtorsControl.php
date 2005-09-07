<?php
/* $Revision: 1.6 $ */
$PageSecurity=15;

include('includes/session.inc');
$title=_('Check Period Sales Ledger Control Account');
include('includes/header.inc');
include('includes/DateFunctions.inc');


$Period = GetPeriod(Date($_SESSION['DefaultDateFormat']));

echo '<FONT SIZE=4><B>' . _('Debtors Ledger Control Totals for Period') . ' ' . $Period . ' </B></FONT>';

$sql = "SELECT bfwd, actual FROM chartdetails WHERE period=$Period AND accountcode=" . $_SESSION['CompanyRecord']['debtorsact'];
$DebtorsAct = DB_query($sql,$db);

$DebtorsActDetail = DB_fetch_array($DebtorsAct);

echo '<TABLE><TR><TD>' . _('Brought forward balance per GL') . ':</TD><TD ALIGN=RIGHT>' . number_format($DebtorsActDetail['bfwd'],2) . '</TD></TR>';

$sql = "SELECT SUM((ovamount+ovgst)/rate) AS totinvnetcrds FROM debtortrans WHERE prd=$Period AND (type=10 OR type=11)";
$InvNetCrdsResult = DB_query($sql,$db);
$InvNetCrdsDetail = DB_fetch_array($InvNetCrdsResult);

echo '<TR><TD>' . _('Total Invoices Net of Credits') . '</TD><TD ALIGN=RIGHT>' . number_format($InvNetCrdsDetail['totinvnetcrds'],2) . '</TD></TR>';

$sql = "SELECT SUM((ovamount+ovgst)/rate) AS totreceipts FROM debtortrans WHERE prd=$Period AND type=12";
$ReceiptsResult = DB_query($sql,$db);
$ReceiptsDetail = DB_fetch_array($ReceiptsResult);

echo '<TR><TD>' . _('Total Receipts for the period') . '</TD><TD ALIGN=RIGHT>' . number_format($ReceiptsDetail['totreceipts'],2) . '</TD></TR>';

echo '<TR><TD>' . _('Calculated Balance C/FWD') . '</TD><TD ALIGN=RIGHT>' . number_format(($DebtorsActDetail['bfwd']+ $InvNetCrdsDetail['totinvnetcrds']+ $ReceiptsDetail['totreceipts']),2) . '</TD></TR>';

echo '<TR><TD>' . _('Balance per GL C/FWD') . '</TD><TD ALIGN=RIGHT>' . number_format($DebtorsActDetail['bfwd']+$DebtorsActDetail['actual'],2) . '</TD></TR>';
echo '</TABLE>';

include('includes/footer.inc');
?>