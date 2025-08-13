<?php

$SQL = "ALTER TABLE `suppliers` MODIFY `lastpaiddate` DATE";
$Result = DB_query($SQL);

if (DB_error_no($Result) == 0) {
	$_SESSION['Updates']['Successes']++;
} else {
	$_SESSION['Updates']['Errors']++;
}

UpdateDBNo(basename(__FILE__, '.php'), __('Change column lastpaiddate in suppliers table to be of type date, not datetime'));
