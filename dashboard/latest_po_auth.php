<?php

if (basename($_SERVER['SCRIPT_NAME']) != 'Dashboard.php') {
	// allow dashboard applet to run standalone
	$DirectoryLevelsDeep = 1;
	$PathPrefix = __DIR__ . '/../';
	require_once($PathPrefix . 'includes/session.php');
	$DashBoardURL = $RootPath . '/index.php';
}

	$ScriptTitle = __('Latest purchase orders to authorise');

	$SQL = "SELECT DISTINCT id FROM dashboard_scripts WHERE scripts='" . basename(basename(__FILE__)) . "'";
	$DashboardResult = DB_query($SQL);
	$DashboardRow = DB_fetch_array($DashboardResult);

	echo '<table class="DashboardTable">
			<tr>
				<th colspan="5">
					<div class="CanvasTitle">', $ScriptTitle, '
						<a class="CloseButton" href="', $DashBoardURL, '?Remove=', urlencode($DashboardRow['id']), '" target="_parent" title="', __('Remove this applet from dashboard'), '" id="CloseButton" href="#">X</a>
					</div>
				</th>
			</tr>';

	$SQL = "SELECT purchorders.*,
			suppliers.suppname,
			suppliers.currcode,
			www_users.realname,
			www_users.email,
			currencies.decimalplaces AS currdecimalplaces
		FROM purchorders INNER JOIN suppliers
			ON suppliers.supplierid=purchorders.supplierno
		INNER JOIN currencies
			ON suppliers.currcode=currencies.currabrev
		INNER JOIN www_users
			ON www_users.userid=purchorders.initiator
		WHERE status='Pending' LIMIT 10";
	$DashboardResult = DB_query($SQL);
	echo '<tbody>
		<tr>
			<th>', __('Supplier'), '</th>
			<th>', __('Order Date'), '</th>
			<th>', __('Delivery Date'), '</th>
			<th>', __('Total Amount'), '</th>
			<th>', __('Status'), '</th>
		</tr>';
	$k = 0;
	while ($Row = DB_fetch_array($DashboardResult)) {
		$AuthSQL = "SELECT authlevel
					FROM purchorderauth
					WHERE currabrev='" . $Row['currcode'] . "'
						AND userid='" . $_SESSION['UserID'] . "'";

		$AuthResult = DB_query($AuthSQL);
		$MyAuthRow = DB_fetch_array($AuthResult);
		$AuthLevel = $MyAuthRow['authlevel'];

		$OrderValueSQL = "SELECT sum(unitprice*quantityord) as ordervalue,
							sum(unitprice*quantityord) as total
						FROM purchorderdetails
						GROUP BY orderno";

		$OrderValueResult = DB_query($OrderValueSQL);
		$MyOrderValueRow = DB_fetch_array($OrderValueResult);
		$OrderValue = $MyOrderValueRow['ordervalue'];
		$TotalOV = $MyOrderValueRow['total'];

		$FormatedOrderDate2 = ConvertSQLDate($Row['orddate']);
		$FormatedDelDate2 = ConvertSQLDate($Row['deliverydate']);

		echo '<tr class="striped_row">
			<td>', $Row['suppname'], '</td>
			<td>', $FormatedOrderDate2, '</td>
			<td>', $FormatedDelDate2, '</td>
			<td class="number">', locale_number_format($TotalOV, $Row['currdecimalplaces']), '</td>
			<td>', $Row['status'], '</td>
		</tr>';

	}
	echo '</tbody>
	</table>';
