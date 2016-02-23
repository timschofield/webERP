<?php

/* This file contains the code to post GL transactions.
 *
 * This file can be included on any page that needs GL postings to be posted eg inquiries or GL reports
 * GL posting thus becomes an invisible/automatic process to the user
 *
 * The logic of GL posting consists of:
 *
 * Then looping through all unposted GL transactions in GLTrans table and
 *
 * 1. Debit amounts increase the charge in the period for the account and credit amounts decrease the charge.
 * 2. Chart Details records for all following periods have the b/fwd balance increased for debit amounts and decreased for credits.
 * 3. Once these updates are done the GLTrans record is flagged as posted.
 *
 * Notes:
 * ChartDetail records should already exist - they are created (from includes/DateFunctions.in GetPeriod) when a new period is created
 * or when a new GL account is created for all periods in the periods table. However, we may need to create new ones if the user posts
 * back to a period before periods are currently set up - which is not actually possible with the config parameter ProhibitGLPostingsBefore
 * set (However, is a problem when it is not set)
 */

$PeriodResult = DB_query("SELECT MIN(periodno) AS createfrom, MAX(periodno) AS createto FROM periods");
$PeriodRow = DB_fetch_array($PeriodResult);

if (is_null($PeriodRow['createfrom'])) {
	//There are no periods defined
	$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (-1, LAST_DAY(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)))", _('Could not insert last period'));
	$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES ( 0, LAST_DAY(CURRENT_DATE))", _('Could not insert this period'));
	$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES ( 1, LAST_DAY(DATE_ADD(CURRENT_DATE, INTERVAL 1 MONTH)))", _('Could not insert next period'));
	$CreateFrom = -1;
	$CreateTo = 1;
} else {
	$CreateFrom = $PeriodRow['createfrom'];
	$CreateTo = $PeriodRow['createto'];
}

/* Then get a list of all the account/period combinations where
 * a chartdetails reference doesn't exist
 */
$SQL = "SELECT chartmaster.accountcode,periods.periodno
				FROM (chartmaster CROSS JOIN periods)
				LEFT JOIN chartdetails
					ON chartmaster.accountcode = chartdetails.accountcode
					AND periods.periodno = chartdetails.period
				WHERE (periods.periodno BETWEEN '" . $CreateFrom . "' AND '" . $CreateTo . "')
					AND chartdetails.actual IS NULL";

$ChartDetailsNotSetUpResult = DB_query($SQL, _('Could not test to see that all chart detail records properly initiated'));

if (DB_num_rows($ChartDetailsNotSetUpResult) > 0) {
	/* Initilly insert the chartdetails records that do not already exist*/
	$SQL = "INSERT INTO chartdetails (accountcode, period)
					SELECT chartmaster.accountcode,
							periods.periodno
						FROM (chartmaster CROSS JOIN periods)
						LEFT JOIN chartdetails
							ON chartmaster.accountcode = chartdetails.accountcode
							AND periods.periodno = chartdetails.period
						WHERE (periods.periodno BETWEEN '" . $CreateFrom . "' AND '" . $CreateTo . "')
							AND chartdetails.accountcode IS NULL
							AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'";

	$ErrMsg = _('Inserting new chart details records required failed because');
	$InsChartDetailsRecords = DB_query($SQL, $ErrMsg);

	/* Then update the bfwd and budgetbfwd balances for all of the new records */
	while ($ChartDetailsRow = DB_fetch_array($ChartDetailsNotSetUpResult)) {
		/* Get the previous months figures */
		$SQL = "SELECT accountcode,
						period,
						actual,
						bfwd
					FROM chartdetails
					WHERE period ='" . ($ChartDetailsRow['periodno']-1) . "'
						AND accountcode='" . $ChartDetailsRow['accountcode'] . "'";
		$ErrMsg = _('Could not retrieve the ChartDetail records because');
		$Result = DB_query($SQL, $ErrMsg);

		$MyRow = DB_fetch_array($Result);
		$CFwd = $MyRow['bfwd'] + $MyRow['actual'];
		$SQL = "UPDATE chartdetails SET bfwd='" . $CFwd . "'
					WHERE period='" . ($MyRow['period'] + 1) . "'
					AND  accountcode = '" . $MyRow['accountcode'] . "'";
		$ErrMsg = _('Could not update the chartdetails record because');
		$UpdateResult = DB_query($SQL, $ErrMsg);
	}
}

/*All the ChartDetail records should have been created now and be available to accept postings */

for ($CurrPeriod = $CreateFrom; $CurrPeriod <= $CreateTo; $CurrPeriod++) {

	$SQL = "SELECT counterindex,
					periodno,
					account,
					amount
				FROM gltrans
				WHERE posted=0
					AND periodno='" . $CurrPeriod . "'
				ORDER BY account";
	$UnpostedTransResult = DB_query($SQL);

	DB_Txn_Begin();
	$CurrentAccount = '0';
	$TotalAmount = 0;
	while ($UnpostedTrans = DB_fetch_array($UnpostedTransResult)) {

		if ($CurrentAccount != $UnpostedTrans['account']) {
			if ($CurrentAccount != 0) {
				$ErrMsg = _('Cannot update chartdetails');
				$DbgMsg = _('The SQL that failed to update the chartdetails table');
				$SQL = "UPDATE chartdetails SET actual = actual + " . $TotalAmount . ",
												bfwd = bfwd + " . $TotalAmount . "
						WHERE accountcode = '" . $CurrentAccount . "'
						AND period= '" . $CurrPeriod . "'";
				$PostPrd = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			}
			$TotalAmount = 0;
		}
		$CurrentAccount = $UnpostedTrans['account'];
		$TotalAmount += $UnpostedTrans['amount'];
	}
	// There will be one account still to post after the loop
	if ($CurrentAccount != 0) {
		$ErrMsg = _('Cannot update chartdetails');
		$DbgMsg = _('The SQL that failed to update the chartdetails table');
		$SQL = "UPDATE chartdetails SET actual = actual + " . $TotalAmount . ",
										bfwd = bfwd + " . $TotalAmount . "
				WHERE accountcode = '" . $CurrentAccount . "'
				AND period= '" . $CurrPeriod . "'";
		$PostPrd = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	}

	$ErrMsg = _('Failed to mark all the transactions as posted');
	$DbgMsg = _('The SQL that failed to mark all transactions as posted');
	$SQL = "UPDATE gltrans SET posted = 1 WHERE periodno = '" . $CurrPeriod . "' AND posted=0";
	$Posted = DB_query($SQL, $ErrMsg, $DbgMsg, true);

	DB_Txn_Commit();
}

?>