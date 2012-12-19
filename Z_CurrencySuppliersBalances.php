<?php
/* $Id$*/

include('includes/session.inc');
$title=_('Currency Debtor Balances');
include('includes/header.inc');

echo '<div class="centre"><h3>' . _('Suppliers Balances By Currency Totals') . '</h3></div>';

$sql = "SELECT SUM(ovamount+ovgst-alloc) AS currencybalance,
		currcode,
		decimalplaces AS currdecimalplaces,
		SUM((ovamount+ovgst-alloc)/supptrans.rate) AS localbalance
		FROM supptrans INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid 
		INNER JOIN currencies ON suppliers.currcode=currencies.currabrev
		WHERE (ovamount+ovgst-alloc)<>0 
		GROUP BY currcode";

$result = DB_query($sql,$db);

$LocalTotal =0;

echo '<table>';

while ($myrow=DB_fetch_array($result)){

	echo '<tr>
			<td>' . _('Total Supplier Balances in') . ' </td>
			<td>' . $myrow['currcode'] . '</td>
			<td class="number">' . locale_number_format($myrow['currencybalance'],$myrow['currdecimalplaces']) . '</td>
			<td> ' . _('in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</td>
			<td class="number">' . locale_number_format($myrow['localbalance'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';
	$LocalTotal += $myrow['localbalance'];
}

echo '<tr>
		<td colspan="4">' . _('Total Balances in local currency') . ':</td>
		<td class="number">' . locale_number_format($LocalTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
	</tr>';

echo '</table>';

include('includes/footer.inc');
?>