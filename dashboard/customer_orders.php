<?php

if (basename($_SERVER['SCRIPT_NAME']) != 'Dashboard.php') {
	// allow dashboard applet to run standalone
	$DirectoryLevelsDeep = 1;
	$PathPrefix = __DIR__ . '/../';
	require_once($PathPrefix . 'includes/session.php');
	$DashBoardURL = $RootPath . '/index.php';
}

	$ScriptTitle = __('Latest Customer Orders');

	$SQL = "SELECT id FROM dashboard_scripts WHERE scripts='" . basename(basename(__FILE__)) . "'";
	$DashboardResult = DB_query($SQL);
	$DashboardRow = DB_fetch_array($DashboardResult);

	echo '<table class="DashboardTable">
		<tr>
			<th colspan="6">
				', $ScriptTitle, '
				<a class="CloseButton" href="', $DashBoardURL, '?Remove=', urlencode($DashboardRow['id']), '" target="_parent" title="', __('Remove this applet from dashboard'), '" id="CloseButton">X</a>
			</th>
		</tr>';

	$SQL = "SELECT salesorders.orderno,
				debtorsmaster.name,
				debtorsmaster.currcode,
				salesorders.orddate,
				salesorders.deliverydate,
				currencies.decimalplaces AS currdecimalplaces,
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
			FROM salesorders
			INNER JOIN salesorderdetails
				ON salesorders.orderno = salesorderdetails.orderno
			INNER JOIN debtorsmaster
				ON salesorders.debtorno = debtorsmaster.debtorno
			INNER JOIN custbranch
				ON salesorders.branchcode = custbranch.branchcode
				AND salesorders.debtorno = custbranch.debtorno
			INNER JOIN currencies
				ON debtorsmaster.currcode = currencies.currabrev
			WHERE salesorderdetails.completed = 0
			GROUP BY salesorders.orderno,
					debtorsmaster.name,
					currencies.decimalplaces,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate
			ORDER BY salesorders.orderno LIMIT 5";

	$SalesOrdersResult = DB_query($SQL);

	$TotalSalesOrders = 0;
	echo '<tr>
		<th>', __('Order number'), '</th>
		<th>', __('Customer'), '</th>
		<th>', __('Order Date'), '</th>
		<th>', __('Delivery Date'), '</th>
		<th class="number">', __('Order Amount'), '</th>
		<th>', __('Currency'), '</th>
	</tr> ';
	$k = 0;

	while ($Row = DB_fetch_array($SalesOrdersResult)) {
		$DecimalPlaces = $Row['currdecimalplaces'];
		$FormatedOrderValue = locale_number_format($Row['ordervalue'], $Row['currdecimalplaces']);
		$OrderDate = ConvertSQLDate($Row['orddate']);
		$DelDate = ConvertSQLDate($Row['deliverydate']);
		$TotalSalesOrders+= $Row['ordervalue'];
		echo '<tr class="striped_row">
			<td> ', $Row['orderno'], ' </td>
			<td> ', $Row['name'], ' </td>
			<td>', $OrderDate, '</td>
			<td>', $DelDate, '</td>
			<td class="number">', $FormatedOrderValue, '</td>
			<td>', $Row['currcode'], '</td>
		</tr>';
	}

	if (DB_num_rows($SalesOrdersResult) > 0) {
		echo '<tr class="total_row">
			<td colspan=3>', __('Total'), '</td>
			<td colspan=2 class="number">', locale_number_format($TotalSalesOrders, $DecimalPlaces), '</td>
			<td></td>
		</tr>';
	}

	echo '</table>';
