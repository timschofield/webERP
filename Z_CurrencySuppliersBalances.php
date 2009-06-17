<?php
/* $Revision: 1.5 $ */
$PageSecurity=15;

include('includes/session.inc');
$title=_('Currency Debtor Balances');
include('includes/header.inc');

echo '<font size=4><b>' . _('Suppliers Balances By Currency Totals') . '</b></font>';

$sql = 'SELECT SUM(ovamount+ovgst-alloc) AS currencybalance, 
		currcode, 
		SUM((ovamount+ovgst-alloc)/rate) AS localbalance 
		FROM supptrans INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid 
		WHERE (ovamount+ovgst-alloc)<>0 GROUP BY currcode';

$result = DB_query($sql,$db);

$LocalTotal =0;

echo '<table>';

while ($myrow=DB_fetch_array($result)){

	echo '<tr><td><font size=4>' . _('Total Supplier Balances in') . ' </font></td>
		<td><font size=4>' . $myrow['currcode'] . '</font></td>
		<td align=right><font size=4>' . number_format($myrow['currencybalance'],2) . '</font></td>
		<td><font size=4> ' . _('in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</font></td>
		<td align=right><font size=4>' . number_format($myrow['localbalance'],2) . '</font></td></tr>';
	$LocalTotal += $myrow['localbalance'];
}

echo '<tr><td colspan=4><font size=4>' . _('Total Balances in local currency') . ':</font></td>
	<td align=right><font size=4>' . number_format($LocalTotal,2) . '</font></td></tr>';

echo '</table>';

include('includes/footer.inc');
?>