<?php

/* This script is an utility to show suppliers balances in total by currency. */

require(__DIR__ . '/includes/session.php');

$Title = __('Currency Supplier Balances');
$ViewTopic = 'SpecialUtilities';
$BookMark = 'Z_CurrencySuppliersBalances';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/supplier.png" title="' .
	__('Show Local Currency Total Suppliers Balances') . '" /> ' .// Icon title.
	__('Suppliers Balances By Currency Totals') . '</p>';// Page title.

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
			<td>' . __('Total Supplier Balances in') . ' </td>
			<td>' . $MyRow['currcode'] . '</td>
			<td class="number">' . locale_number_format($MyRow['currencybalance'],$MyRow['currdecimalplaces']) . '</td>
			<td> ' . __('in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</td>
			<td class="number">' . locale_number_format($MyRow['localbalance'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';
	$LocalTotal += $MyRow['localbalance'];
}

echo '<tr>
		<td colspan="4">' . __('Total Balances in local currency') . ':</td>
		<td class="number">' . locale_number_format($LocalTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
	</tr>';

echo '</table>';

include('includes/footer.php');
