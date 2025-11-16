<?php

$SQL = "SELECT DEFAULT_CHARACTER_SET_NAME
		FROM INFORMATION_SCHEMA.SCHEMATA
		WHERE SCHEMA_NAME = '" . $_SESSION['DatabaseName'] . "'";
$Result = DB_query($SQL);
$Row = DB_fetch_array($Result);
$CurrentCharset = $Row['DEFAULT_CHARACTER_SET_NAME'];

if ($CurrentCharset == 'utf8' || $CurrentCharset == 'utf8mb3') {
	$NewCharset = 'utf8mb4';
	$NewCollation = 'utf8mb4_unicode_ci';

	$SQL = "ALTER DATABASE `" . $_SESSION['DatabaseName'] . "`
			CHARACTER SET = " . $NewCharset . "
			COLLATE = " . $NewCollation;
	$Result = DB_query($SQL);

	if ($Result) {
		$_SESSION['Updates']['Messages'][] = __('Database charset updated to utf8mb4 for MySQL 8.4+ compatibility');
	}
}

$SQL = "SHOW KEYS FROM stockserialitems WHERE Key_name = 'stockid'";
$Result = DB_query($SQL);
if (DB_num_rows($Result) == 0) {
	$SQL = "ALTER TABLE stockserialitems
			ADD UNIQUE KEY stockid (stockid, loccode, serialno)";
	$Result = DB_query($SQL);
	if ($Result) {
		$_SESSION['Updates']['Messages'][] = __('Added unique key to stockserialitems table');
	}
}

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('MySQL 8.4+ utf8mb4 compatibility'));
}
