<?php

/* Databases upgraded from old versions can still contain MySQL "zero dates" ('0000-00-00' or
 * '0000-00-00 00:00:00'). webERP uses '1000-01-01' for "no date" and ConvertSQLDate() rejects zero
 * dates, so pages such as StockCostUpdate.php stop with "The date does not appear to be in a valid
 * format". Replace them in every date and datetime column: NULL where the column allows it,
 * otherwise '1000-01-01'. Comparing with < '1000-01-01' avoids the zero date literal, which strict
 * SQL modes reject. */
$SQL = "SELECT table_name AS tablename,
			column_name AS columnname,
			data_type AS datatype,
			is_nullable AS isnullable
		FROM information_schema.columns
		WHERE table_schema = '" . $_SESSION['DatabaseName'] . "'
			AND data_type IN ('date', 'datetime')";
$ColumnsResult = DB_query($SQL);
while ($MyRow = DB_fetch_array($ColumnsResult)) {
	if ($MyRow['isnullable'] == 'YES') {
		$NewValue = 'NULL';
	} elseif ($MyRow['datatype'] == 'date') {
		$NewValue = "'1000-01-01'";
	} else {
		$NewValue = "'1000-01-01 00:00:00'";
	}
	$SQL = "UPDATE " . $MyRow['tablename'] . "
			SET " . $MyRow['columnname'] . " = " . $NewValue . "
			WHERE " . $MyRow['columnname'] . " < '1000-01-01'";
	$ErrMsg = __('Could not replace zero dates in') . ' ' . $MyRow['tablename'] . '.' . $MyRow['columnname'];
	DB_query($SQL, $ErrMsg);
}

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Replace zero dates left over from old versions with 1000-01-01'));
}
