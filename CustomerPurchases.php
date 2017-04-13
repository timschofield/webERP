<?php
/* $Id: CustomerPurchases.php 7090 2015-01-20 13:43:08Z daintree $*/
/* This script is to view the items purchased by a customer. */

include('includes/session.php');
$Title = _('Customer Purchases');// Screen identificator.
$ViewTopic = 'ARInquiries';// Filename's id in ManualContents.php's TOC.
/* This help needs to be writing...
$BookMark = 'CustomerPurchases';// Anchor's id in the manual's html document.*/
include('includes/header.php');

if(isset($_GET['DebtorNo'])) {
	$DebtorNo = $_GET['DebtorNo'];// Set DebtorNo from $_GET['DebtorNo'].
} elseif(isset($_POST['DebtorNo'])) {
	$DebtorNo = $_POST['DebtorNo'];// Set DebtorNo from $_POST['DebtorNo'].
} else {
	prnMsg(_('This script must be called with a customer code.'), 'info');
	include('includes/footer.php');
	exit;
}

$SQL = "SELECT debtorsmaster.name,
				custbranch.brname
		FROM debtorsmaster
		INNER JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno
		WHERE debtorsmaster.debtorno = '" . $DebtorNo . "'";

$ErrMsg = _('The customer details could not be retrieved by the SQL because');
$CustomerResult = DB_query($SQL, $ErrMsg);
$CustomerRecord = DB_fetch_array($CustomerResult);

echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/customer.png" title="' .
	_('Customer') . '" /> ' .// Icon title.
	_('Items Purchased by Customer') . '<br />' . $DebtorNo . " - " . $CustomerRecord['name'] . '</p>';// Page title.

$SQL = "SELECT stockmoves.stockid,
			stockmaster.description,
			systypes.typename,
			transno,
			locations.locationname,
			trandate,
			stockmoves.branchcode,
			price,
			reference,
			qty,
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

$ErrMsg = _('The stock movement details could not be retrieved by the SQL because');
$StockMovesResult = DB_query($SQL, $ErrMsg);

if (DB_num_rows($StockMovesResult) == 0) {
	echo '<br />';
	prnMsg(_('There are no items for this customer'), 'notice');
	echo '<br />';
} //DB_num_rows($StockMovesResult) == 0
else {
	echo '<table class="selection">
			<tr>
				<th>' . _('Transaction Date') . '</th>
				<th>' . _('Stock ID') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('Type') . '</th>
				<th>' . _('Transaction No.') . '</th>
				<th>' . _('From Location') . '</th>
				<th>' . _('Branch Code') . '</th>
				<th>' . _('Price') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Amount of Sale') . '</th>
				<th>' . _('Reference') . '</th>
				<th>' . _('Narrative') . '</th>
			</tr>';

	while ($StockMovesRow = DB_fetch_array($StockMovesResult)) {
		echo '<tr>
				<td>' . ConvertSQLDate($StockMovesRow['trandate']) . '</td>
				<td>' . $StockMovesRow['stockid'] . '</td>
				<td>' . $StockMovesRow['description'] . '</td>
				<td>' . _($StockMovesRow['typename']) . '</td>
				<td class="number">' . $StockMovesRow['transno'] . '</td>
				<td>' . $StockMovesRow['locationname'] . '</td>
				<td>' . $StockMovesRow['branchcode'] . '</td>
				<td class="number">' . locale_number_format($StockMovesRow['price'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format(-$StockMovesRow['qty'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format((-$StockMovesRow['qty'] * $StockMovesRow['price']), $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . $StockMovesRow['reference'] . '</td>
				<td>' . $StockMovesRow['narrative'] . '</td>
			</tr>';

	} //$StockMovesRow = DB_fetch_array($StockMovesResult)

	echo '</table>';
}

echo '<br /><div class="centre"><a href="SelectCustomer.php">' . _('Return to customer selection screen') . '</a></div><br />';

include('includes/footer.php');
?>
