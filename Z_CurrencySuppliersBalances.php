<?php
/* $Revision: 1.3 $ */
$PageSecurity=15;

include('includes/session.inc');
$title=_('Currency Debtor Balances');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$CompanyRecord = ReadInCompanyRecord(&$db);


echo '<FONT SIZE=4><B>' . _('Suppliers Balances By Currency Totals') . '</B></FONT>';

$sql = 'SELECT Sum(OvAmount+OvGST-Alloc) AS CurrencyBalance, CurrCode, SUM((OvAmount+OvGST-Alloc)/Rate) AS LocalBalance FROM SuppTrans INNER JOIN Suppliers ON SuppTrans.SupplierNo=Suppliers.SupplierID WHERE (OvAmount+OvGST-Alloc)<>0 GROUP BY CurrCode';

$result = DB_query($sql,$db);

$LocalTotal =0;

echo '<TABLE>';

while ($myrow=DB_fetch_array($result)){

	echo '<TR><TD><FONT SIZE=4>' . _('Total Supplier Balances in') . ' </FONT></TD>
		<TD><FONT SIZE=4>' . $myrow['CurrCode'] . '</FONT></TD>
		<TD ALIGN=RIGHT><FONT SIZE=4>' . number_format($myrow['CurrencyBalance'],2) . '</FONT></TD>
		<TD><FONT SIZE=4> ' . _('in') . ' ' . $CompanyRecord['CurrencyDefault'] . '</FONT></TD>
		<TD ALIGN=RIGHT><FONT SIZE=4>' . number_format($myrow['LocalBalance'],2) . '</FONT></TD></TR>';
	$LocalTotal += $myrow['LocalBalance'];
}

echo '<TR><TD COLSPAN=4><FONT SIZE=4>' . _('Total Balances in local currency') . ':</FONT></TD>
	<TD ALIGN=RIGHT><FONT SIZE=4>' . number_format($LocalTotal,2) . '</FONT></TD></TR>';

echo '</TABLE>';

include('includes/footer.inc');
?>

