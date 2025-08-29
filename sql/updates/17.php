<?php

CreateTable('gltotals', "CREATE TABLE IF NOT EXISTS `gltotals` (
  `account` varchar(20) NOT NULL DEFAULT '',
  `period` smallint(6) NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0.0,
  PRIMARY KEY  (`account`, `period`)
)");

$SQL = "TRUNCATE gltotals";
$Result = DB_query($SQL);

$SQL = "DROP TRIGGER IF EXISTS gltrans_after_insert";
$Result = DB_query($SQL);
$SQL = "DROP TRIGGER IF EXISTS gltrans_after_update";
$Result = DB_query($SQL);
$SQL = "DROP TRIGGER IF EXISTS gltrans_after_delete";
$Result = DB_query($SQL);


$_SESSION['Updates']['Successes'] = 0;
$_SESSION['Updates']['Errors'] = 0;

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
	if (DB_error_no($UpdateResult) == 0) {
		$_SESSION['Updates']['Successes']++;
	} else {
		$_SESSION['Updates']['Errors']++;
	}
}


$SQL = "CREATE TRIGGER gltrans_after_insert AFTER INSERT ON gltrans FOR EACH ROW
		BEGIN
			INSERT INTO gltotals (account, period, amount)
			VALUES (NEW.account, NEW.periodno, NEW.amount)
			ON DUPLICATE KEY UPDATE amount = amount + NEW.amount;
		END";
$Result = DB_query($SQL);

$SQL = "CREATE TRIGGER `gltrans_after_update` AFTER UPDATE ON `gltrans` FOR EACH ROW
		BEGIN
			IF NEW.account <> OLD.account OR NEW.periodno <> OLD.periodno THEN
				-- Handle account or period changes.
				-- Deduct the old amount from the old account/period.
				UPDATE gltotals
				SET amount = amount - OLD.amount
				WHERE account = OLD.account AND period = OLD.periodno;

				-- Add the new amount to the new account/period.
				INSERT INTO gltotals (account, period, amount)
				VALUES (NEW.account, NEW.periodno, NEW.amount)
				ON DUPLICATE KEY UPDATE amount = amount + NEW.amount;
			ELSE
				-- Just update the amount if account and period are the same.
				UPDATE gltotals
				SET amount = amount - OLD.amount + NEW.amount
				WHERE account = NEW.account AND period = NEW.periodno;
			END IF;
		END";
$Result = DB_query($SQL);

$SQL = "CREATE TRIGGER `gltrans_after_delete` AFTER DELETE ON `gltrans` FOR EACH ROW
		BEGIN
			UPDATE gltotals
			SET amount = amount - OLD.amount
			WHERE account = OLD.account AND period = OLD.periodno;
		END";
$Result = DB_query($SQL);

UpdateDBNo(basename(__FILE__, '.php'), __('Create General Ledger totals from gltrans table'));
