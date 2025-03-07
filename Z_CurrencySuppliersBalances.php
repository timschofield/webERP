<?php
/* This script is an utility to show suppliers balances in total by currency. */

include ('includes/session.php');
$Title = _('Currency Supplier Balances');// Screen identificator.
$ViewTopic = 'SpecialUtilities';// Filename's id in ManualContents.php's TOC.
$BookMark = 'Z_CurrencySuppliersBalances';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/supplier.png" title="' .
	_('Show Local Currency Total Suppliers Balances') . '" /> ' .// Icon title.
	_('Suppliers Balances By Currency Totals') . '</p>';// Page title.

$SQL = "SELECT SUM(ovamount+ovgst-alloc) AS currencybalance,
		currcode,
		decimalplaces AS currdecimalplaces,
		SUM((ovamount+ovgst-alloc)/supptrans.rate) AS localbalance
		FROM supptrans INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid
		INNER JOIN currencies ON suppliers.currcode=currencies.currabrev
		WHERE (ovamount+ovgst-alloc)<>0
		GROUP BY currcode";

$Result = DB_query($SQL);

$LocalTotal =0;

echo '<table>';

while ($MyRow=DB_fetch_array($Result)){

	echo '<tr>
			<td>' . _('Total Supplier Balances in') . ' </td>
			<td>' . $MyRow['currcode'] . '</td>
			<td class="number">' . locale_number_format($MyRow['currencybalance'],$MyRow['currdecimalplaces']) . '</td>
			<td> ' . _('in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</td>
			<td class="number">' . locale_number_format($MyRow['localbalance'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';
	$LocalTotal += $MyRow['localbalance'];
}

echo '<tr>
		<td colspan="4">' . _('Total Balances in local currency') . ':</td>
		<td class="number">' . locale_number_format($LocalTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
	</tr>';

echo '</table>';

include('includes/footer.php');
?>
