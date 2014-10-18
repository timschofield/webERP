<?php
/* $Id: Z_CurrencyDebtorsBalances.php 5784 2012-12-29 04:00:43Z daintree $*/

include('includes/session.inc');
$Title=_('Currency Debtor Balances');
include('includes/header.inc');

echo '<div class="centre"><h3>' . _('Debtors Balances By Currency Totals') . '</h3></div>';

$sql = "SELECT SUM(ovamount+ovgst+ovdiscount+ovfreight-alloc) AS currencybalance,
		currcode,
		decimalplaces AS currdecimalplaces,
		SUM((ovamount+ovgst+ovdiscount+ovfreight-alloc)/debtortrans.rate) AS localbalance
	FROM debtortrans INNER JOIN debtorsmaster
		ON debtortrans.debtorno=debtorsmaster.debtorno
	INNER JOIN currencies
	ON debtorsmaster.currcode=currencies.currabrev
	WHERE (ovamount+ovgst+ovdiscount+ovfreight-alloc)<>0 GROUP BY currcode";

$result = DB_query($sql,$db);


$LocalTotal =0;

echo '<table>';

while ($myrow=DB_fetch_array($result)){

	echo '<tr>
			<td>' . _('Total Debtor Balances in') . ' </td>
			<td>' . $myrow['currcode'] . '</td>
			<td class="number">' . locale_number_format($myrow['currencybalance'],$myrow['currdecimalplaces']) . '</td>
			<td>' . _('in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</td>
			<td class="number">' . locale_number_format($myrow['localbalance'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';
	$LocalTotal += $myrow['localbalance'];
}

echo '<tr>
		<td colspan="4">' . _('Total Balances in local currency') . ':</td>
		<td class="number">' . locale_number_format($LocalTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</td></tr>';

echo '</table>';

include('includes/footer.inc');
?>