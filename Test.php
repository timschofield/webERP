<?php
$PageSecurity = 0;
include('includes/session.php');
//include('includes/DateFunctions.inc');

$SQL = "TRUNCATE gltotals";
$Result = DB_query($SQL);

$PeriodsSQL = "SELECT periodno FROM periods";
$PeriodsResult = DB_query($PeriodsSQL);
while ($PeriodRow = DB_fetch_array($PeriodsResult)) {
	$CreateEntriesSQL = "INSERT INTO gltotals (account, period, amount) SELECT accountcode, '" . $PeriodRow['periodno'] . "', 0 FROM chartmaster";
	$CreateEntriesResult = DB_query($CreateEntriesSQL);
}

$TotalsSQL = "SELECT account, period FROM gltotals";
$TotalsResult = DB_query($TotalsSQL);
while ($TotalsRow = DB_fetch_array($TotalsResult)) {
	$TotalSum = "SELECT SUM(amount) as total FROM gltrans WHERE account='" . $TotalsRow['account'] . "' AND periodno='" . $TotalsRow['period'] . "'";
	$TotalResult = DB_query($TotalSum);
	$TotalRow = DB_fetch_array($TotalResult);
	if (!isset($TotalRow['total']) or $TotalRow['total'] == '') {
		$TotalRow['total'] = 0;
	}
	$UpdateSQL = "UPDATE gltotals SET amount='" . $TotalRow['total'] . "'
									WHERE account='" . $TotalsRow['account'] . "'
									AND period='" . $TotalsRow['period'] . "'";
	$UpdateResult = DB_query($UpdateSQL);
}

$SQL = "INSERT INTO tags VALUES(0, 'None')";
$Result = DB_query($SQL);
$SQL = "INSERT INTO gltags (SELECT counterindex, 1  FROM gltrans)";
$Result = DB_query($SQL);

?>