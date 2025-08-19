<?php

if (basename($_SERVER['SCRIPT_NAME']) != 'Dashboard.php') {
	// allow dashboard applet to run standalone
	$DirectoryLevelsDeep = 1;
	$PathPrefix = __DIR__ . '/../';
	require_once($PathPrefix . 'includes/session.php');
	$DashBoardURL = $RootPath . '/index.php';
}

	$ScriptTitle = __('MRP dashboard');

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

	$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.mbflag,
				SUM(locstock.quantity) AS qoh,
				stockmaster.units,
				stockmaster.decimalplaces
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
			ORDER BY stockmaster.stockid LIMIT 5";

	$SearchResult = DB_query($SQL);
	echo '<tbody>
		<tr>
			<th>', __('Code'), '</th>
			<th>', __('Description'), '</th>
			<th>', __('Total QTY on Hand'), '</th>
			<th>', __('Units'), '</th>
		</tr>';
	$k = 0;
	while ($Row = DB_fetch_array($SearchResult)) {
		$StockId = $Row['stockid'];
		$QOH = locale_number_format($Row['qoh'], $Row['decimalplaces']);

		echo '<tr class="striped_row">
			<td><a href="', $RootPath, '/StockStatus.php?StockID=', urlencode($StockId), '" target="_blank">', $Row['stockid'], '</td>
			<td>', $Row['description'], '</td>
			<td class="number">', $QOH, '</td>
			<td>', $Row['units'], '</td>
		</tr>';

	}

	echo '</tbody>
	</table>';
