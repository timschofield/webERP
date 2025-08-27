<?php

/// @todo lock behind admin access

$PageSecurity = 0;

require(__DIR__ . '/includes/session.php');

$SQL = 'SHOW TABLES';
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

while ($MyRow = DB_fetch_array($Result)) {
	$CreateSQL = "SHOW CREATE TABLE " . $MyRow[0];
	$CreateResult = DB_query($CreateSQL);
	$CreateRow = DB_fetch_array($CreateResult);

	file_put_contents('install/sql/tables/' . $CreateRow[0] . '.sql', $CreateRow[1]);
}
