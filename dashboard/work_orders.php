<?php

if (basename($_SERVER['SCRIPT_NAME']) != 'Dashboard.php') {
	// allow dashboard applet to run standalone
	$DirectoryLevelsDeep = 1;
	$PathPrefix = __DIR__ . '/../';
	require_once($PathPrefix . 'includes/session.php');
	$DashBoardURL = $RootPath . '/index.php';
}

	$ScriptTitle = __('Latest work orders');

	$SQL = "SELECT DISTINCT id FROM dashboard_scripts WHERE scripts='" . basename(basename(__FILE__)) . "'";
	$DashboardResult = DB_query($SQL);
	$DashboardRow = DB_fetch_array($DashboardResult);

	echo '<table class="DashboardTable">
			<tr>
				<th colspan="4">
					<div class="CanvasTitle">', $ScriptTitle, '
						<a class="CloseButton" href="', $DashBoardURL, '?Remove=', urlencode($DashboardRow['id']), '" target="_parent" title="', __('Remove this applet from dashboard'), '" id="CloseButton" href="#">X</a>
					</div>
				</th>
			</tr>';

	$SQL = "SELECT workorders.wo,
				woitems.stockid,
				stockmaster.
				description,
				stockmaster.decimalplaces,
				woitems.qtyreqd,
				woitems.qtyrecd,
				workorders.requiredby,
				workorders.startdate
			FROM workorders
			INNER JOIN woitems
				ON workorders.wo = woitems.wo
			INNER JOIN stockmaster
				ON woitems.stockid = stockmaster.stockid
			ORDER BY workorders.requiredby DESC LIMIT 7";
	$WorkOrdersResult = DB_query($SQL);

	echo '<tbody>
		<tr>
			<th>', __('Item'), '</th>
			<th>', __('Required By'), '</th>
			<th>', __('Quantity Required'), '</th>
			<th>', __('Quantity Outstanding'), '</th>
		</tr>';

	while ($Row = DB_fetch_array($WorkOrdersResult)) {
		$StockId = $Row['stockid'];
		$FormatedRequiredByDate = ConvertSQLDate($Row['requiredby']);
		$FormatedStartDate = ConvertSQLDate($Row['startdate']);
		$qreq = locale_number_format($Row['qtyreqd'], $Row['decimalplaces']);
		$qout = locale_number_format($Row['qtyreqd'] - $Row['qtyrecd'], $Row['decimalplaces']);

		echo '<tr class="striped_row">
			<td><a href="', $RootPath, '/StockStatus.php?StockID=', urlencode($StockId), '" target="_blank">', $Row['stockid'], ' -', $Row['description'], '</td>
			<td class="number">', ConvertSQLDate($Row['requiredby']), '</td>
			<td class="number">', $qreq, '</td>
			<td class="number">', $qout, '</td>
		</tr>';

	}

	echo '</tbody>
	</table>';
