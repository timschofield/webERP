<?php
/* $Revision: 1.4 $ */
$PageSecurity=15;

include('includes/session.inc');
$title=_('Check Period Sales Ledger Control Account');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('include/DateFunctions.inc');

$CompanyRecord = ReadInCompanyRecord(&$db);

$Period = GetPeriod(Date($DefaultDateFormat));

echo '<FONT SIZE=4><B>' . _('Debtors Ledger Control Totals for Period') . ' ' . $Period . ' </B></FONT>';

$sql = "SELECT BFwd, Actual FROM ChartDetails WHERE Period=$Period AND AccountCode=" . $CompanyRecord['DebtorsAct'];
$DebtorsAct = DB_query($sql,$db);

$DebtorsActDetail = DB_fetch_array($DebtorsAct);

echo '<TABLE><TR><TD>' . _('Brought forward balance per GL') . ':</TD><TD ALIGN=RIGHT>' . number_format($DebtorsActDetail['BFwd'],2) . '</TD></TR>';

$sql = "SELECT Sum((OvAmount+OvGST)/Rate) AS TotInvNetCrds FROM DebtorTrans WHERE Prd=$Period AND (Type=10 OR Type=11)";
$InvNetCrdsResult = DB_query($sql,$db);
$InvNetCrdsDetail = DB_fetch_array($InvNetCrdsResult);

echo '<TR><TD>' . _('Total Invoices Net of Credits') . '</TD><TD ALIGN=RIGHT>' . number_format($InvNetCrdsDetail['TotInvNetCrds'],2) . '</TD></TR>';

$sql = "SELECT Sum((OvAmount+OvGST)/Rate) AS TotReceipts FROM DebtorTrans WHERE Prd=$Period AND Type=12";
$ReceiptsResult = DB_query($sql,$db);
$ReceiptsDetail = DB_fetch_array($ReceiptsResult);

echo '<TR><TD>' . _('Total Receipts for the period') . '</TD><TD ALIGN=RIGHT>' . number_format($ReceiptsDetail['TotReceipts'],2) . '</TD></TR>';

echo '<TR><TD>' . _('Calculated Balance C/FWD') . '</TD><TD ALIGN=RIGHT>' . number_format(($DebtorsActDetail['BFwd']+ $InvNetCrdsDetail['TotInvNetCrds']+ $ReceiptsDetail['TotReceipts']),2) . '</TD></TR>';

echo '<TR><TD>' . _('Balance per GL C/FWD') . '</TD><TD ALIGN=RIGHT>' . number_format($DebtorsActDetail['BFwd']+$DebtorsActDetail['Actual'],2) . '</TD></TR>';
echo '</TABLE>';

include('includes/footer.inc');
?>
