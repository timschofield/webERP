<?php

if (basename($_SERVER['SCRIPT_NAME']) != 'Dashboard.php') {
	// allow dashboard applet to run standalone
	$DirectoryLevelsDeep = 1;
	$PathPrefix = __DIR__ . '/../';
	require_once($PathPrefix . 'includes/session.php');
	$DashBoardURL = $RootPath . '/index.php';
}

	$ScriptTitle = __('Latest bank transactions');

	$SQL = "SELECT id FROM dashboard_scripts WHERE scripts='" . basename(basename(__FILE__)) . "'";
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

	$SQL = "SELECT banktrans.currcode,
				banktrans.amount,
				banktrans.functionalexrate,
				banktrans.exrate,
				banktrans.banktranstype,
				banktrans.transdate,
				bankaccounts.bankaccountname,
				systypes.typename,
				currencies.decimalplaces
			FROM banktrans
			INNER JOIN bankaccounts
				ON banktrans.bankact=bankaccounts.accountcode
			INNER JOIN systypes
				ON banktrans.type=systypes.typeid
			INNER JOIN currencies
				ON banktrans.currcode=currencies.currabrev
			ORDER BY banktrans.transdate DESC, ABS(banktrans.amount) DESC LIMIT 10";

	$DashboardResult = DB_query($SQL);
	$AccountCurrTotal = 0;
	$LocalCurrTotal = 0;

	echo '<tbody>
		<tr>
			<th>', __('Currency'), '</th>
			<th>', __('Amount'), '</th>
			<th>', __('Transaction Type'), '</th>
			<th>', __('Transaction Date'), '</th>
			<th>', __('Account Name'), '</th>
		</tr>';

	$k = 0;

	while ($Row = DB_fetch_array($DashboardResult)) {

		$AccountCurrTotal+= $Row['amount'];
		$LocalCurrTotal+= $Row['amount'] / $Row['functionalexrate'] / $Row['exrate'];
		echo '<tr class="striped_row">
			<td>', $Row['currcode'], '</td>
			<td class="number">', locale_number_format($Row['amount'], $Row['decimalplaces']), '</td>
			<td>', $Row['typename'], '</td>
			<td>', ConvertSQLDate($Row['transdate']), '</td>
			<td class="number">', $Row['bankaccountname'], '</td>
		</tr>';
	}
	echo '</tbody>
	</table>';
