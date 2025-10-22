<?php

/* This script is to view the items purchased by a customer. */

require(__DIR__ . '/includes/session.php');

$Title = __('Customer Purchases');// Screen identificator.
$ViewTopic = 'ARInquiries';// Filename's id in ManualContents.php's TOC.
/* This help needs to be written...
$BookMark = 'CustomerPurchases';// Anchor's id in the manual's html document.*/
include('includes/header.php');

if(isset($_GET['DebtorNo'])) {
	$DebtorNo = $_GET['DebtorNo'];// Set DebtorNo from $_GET['DebtorNo'].
} elseif(isset($_POST['DebtorNo'])) {
	$DebtorNo = $_POST['DebtorNo'];// Set DebtorNo from $_POST['DebtorNo'].
} else {
	prnMsg(__('This script must be called with a customer code.'), 'info');
	include('includes/footer.php');
	exit();
}

$SQL = "SELECT debtorsmaster.name,
				custbranch.brname
		FROM debtorsmaster
		INNER JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno
		WHERE debtorsmaster.debtorno = '" . $DebtorNo . "'";

$ErrMsg = __('The customer details could not be retrieved by the SQL because');
$CustomerResult = DB_query($SQL, $ErrMsg);
$CustomerRecord = DB_fetch_array($CustomerResult);

echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/customer.png" title="' .
	__('Customer') . '" /> ' .// Icon title.
	__('Items Purchased by Customer') . '<br />' . $DebtorNo . " - " . $CustomerRecord['name'] . '</p>';// Page title.

$SQL = "SELECT stockmoves.stockid,
			stockmaster.description,
			stockmaster.units,
			systypes.typename,
			transno,
			locations.locationname,
			trandate,
			stockmoves.branchcode,
			price,
			reference,
			qty,
			discountpercent,
			narrative
		FROM stockmoves
		INNER JOIN stockmaster
			ON stockmaster.stockid=stockmoves.stockid
		INNER JOIN systypes
			ON stockmoves.type=systypes.typeid
		INNER JOIN locations
			ON stockmoves.loccode=locations.loccode
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1";

$SQLWhere=" WHERE stockmoves.debtorno='" . $DebtorNo . "'";

if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " INNER JOIN custbranch
				ON stockmoves.branchcode=custbranch.branchcode";
	$SQLWhere .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
}

$SQL .= $SQLWhere . " ORDER BY trandate DESC";

$ErrMsg = __('The stock movement details could not be retrieved by the SQL because');
$StockMovesResult = DB_query($SQL, $ErrMsg);

if (DB_num_rows($StockMovesResult) == 0) {
	echo '<br />';
	prnMsg(__('There are no items for this customer'), 'notice');
	echo '<br />';
} //DB_num_rows($StockMovesResult) == 0
else {
	echo '<table class="selection">
			<tr>
				<th>' . __('Transaction Date') . '</th>
				<th>' . __('Stock ID') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('Type') . '</th>
				<th>' . __('Transaction No.') . '</th>
				<th>' . __('From Location') . '</th>
				<th>' . __('Branch Code') . '</th>
				<th>' . __('Quantity') . '</th>
				<th>' . __('Unit') . '</th>
				<th>' . __('Price') . '</th>
				<th>' . __('Discount') . '</th>
				<th>' . __('Total') . '</th>
				<th>' . __('Reference') . '</th>
				<th>' . __('Narrative') . '</th>
			</tr>';

	while ($StockMovesRow = DB_fetch_array($StockMovesResult)) {
		echo '<tr>
				<td>' . ConvertSQLDate($StockMovesRow['trandate']) . '</td>
				<td>' . $StockMovesRow['stockid'] . '</td>
				<td>' . $StockMovesRow['description'] . '</td>
				<td>' . __($StockMovesRow['typename']) . '</td>
				<td class="number">' . $StockMovesRow['transno'] . '</td>
				<td>' . $StockMovesRow['locationname'] . '</td>
				<td>' . $StockMovesRow['branchcode'] . '</td>
				<td class="number">' . -$StockMovesRow['qty'] . '</td>
				<td>' . $StockMovesRow['units'] . '</td>
				<td class="number">' . locale_number_format($StockMovesRow['price'] * (1 - $StockMovesRow['discountpercent']), $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format(($StockMovesRow['discountpercent'] * 100),2) . '%' . '</td>
				<td class="number">' . locale_number_format((-$StockMovesRow['qty'] * $StockMovesRow['price'] * (1 - $StockMovesRow['discountpercent'])), $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . $StockMovesRow['reference'] . '</td>
				<td>' . $StockMovesRow['narrative'] . '</td>
			</tr>';

	} //$StockMovesRow = DB_fetch_array($StockMovesResult)

	echo '</table>';
}

echo '<br /><div class="centre"><a href="' . $RootPath . '/SelectCustomer.php">' . __('Return to customer selection screen') . '</a></div><br />';

include('includes/footer.php');
