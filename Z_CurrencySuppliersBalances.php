<?php
/* $Revision: 1.4 $ */
$PageSecurity=15;

include('includes/session.inc');
$title=_('Currency Debtor Balances');
include('includes/header.inc');

echo '<FONT SIZE=4><B>' . _('Suppliers Balances By Currency Totals') . '</B></FONT>';

$sql = 'SELECT SUM(ovamount+ovgst-alloc) AS currencybalance, 
		currcode, 
		SUM((ovamount+ovgst-alloc)/rate) AS localbalance 
		FROM supptrans INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid 
		WHERE (ovamount+ovgst-alloc)<>0 GROUP BY currcode';

$result = DB_query($sql,$db);

$LocalTotal =0;

echo '<TABLE>';

while ($myrow=DB_fetch_array($result)){

	echo '<TR><TD><FONT SIZE=4>' . _('Total Supplier Balances in') . ' </FONT></TD>
		<TD><FONT SIZE=4>' . $myrow['currcode'] . '</FONT></TD>
		<TD ALIGN=RIGHT><FONT SIZE=4>' . number_format($myrow['currencybalance'],2) . '</FONT></TD>
		<TD><FONT SIZE=4> ' . _('in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</FONT></TD>
		<TD ALIGN=RIGHT><FONT SIZE=4>' . number_format($myrow['localbalance'],2) . '</FONT></TD></TR>';
	$LocalTotal += $myrow['localbalance'];
}

echo '<TR><TD COLSPAN=4><FONT SIZE=4>' . _('Total Balances in local currency') . ':</FONT></TD>
	<TD ALIGN=RIGHT><FONT SIZE=4>' . number_format($LocalTotal,2) . '</FONT></TD></TR>';

echo '</TABLE>';

include('includes/footer.inc');
?>