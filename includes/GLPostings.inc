<?php


/* This file contains the code to post GL transactions.

This file can be included on any page that needs GL postings to be posted eg inquiries or GL reports
GL posting thus becomes an invisible/automatic process to the user

The logic of GL posting consists of:


Then looping through all unposted GL transactions in GLTrans table and

1. Debit amounts increase the charge in the period for the account and credit amounts decrease the charge.
2. Chart Details records for all following periods have the b/fwd balance increased for debit amounts and decreased for credits.
3. Once these updates are done the GLTrans record is flagged as posted.


Notes:

ChartDetail records should already exist - they are created (from includes/DateFunctions.in GetPeriod) when a new period is created or when a new GL account is created for all periods in the periods table. However, we may need to create new ones if the user posts back to a period before periods are currently set up - which is not actually possible with the config parameter ProhibitGLPostingsBefore set (However, is a problem when it is not set)
*
* Is it worth checking that periods are set up for all gltrans periods that exist in gltrans? They should already exist.
*/


$PeriodResult = DB_query("SELECT MIN(periodno), MAX(periodno) FROM periods");
$PeriodRow = DB_fetch_row($PeriodResult);
$CreateFrom = $PeriodRow[0];
$CreateTo = $PeriodRow[1];

if (is_null($CreateFrom)){
	//There are no periods defined
	$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (-1,'" . Date('Y-m-d',mktime(0,0,0,Date('m')-1,0,Date('Y'))) . "')",_('Could not insert first period'));
	$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (0,'" . Date('Y-m-d',mktime(0,0,0,Date('m')+1,0,Date('Y'))) . "')",_('Could not insert first period'));
	$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (1,'" . Date('Y-m-d',mktime(0,0,0,Date('m')+2,0,Date('Y'))) . "')",_('Could not insert second period'));
	$CreateFrom=-1;
	$CreateTo=1;
}

/*First off see if there are in fact any chartdetails */

$sql = "SELECT chartmaster.accountcode
		FROM (chartmaster CROSS JOIN periods)
		LEFT JOIN chartdetails ON chartmaster.accountcode = chartdetails.accountcode
		AND periods.periodno = chartdetails.period
		WHERE (periods.periodno BETWEEN '"  . $CreateFrom . "' AND '" . $CreateTo . "')
		AND chartdetails.actual IS NULL";

$ChartDetailsNotSetUpResult = DB_query($sql,_('Could not test to see that all chart detail records properly initiated'));

if(DB_num_rows($ChartDetailsNotSetUpResult)>0){

	//first figure out the periods and account codes for which new chart details need to be added and make an array of them
	$sql = "SELECT chartmaster.accountcode,periods.periodno
		FROM (chartmaster CROSS JOIN periods)
		LEFT JOIN chartdetails ON chartmaster.accountcode=chartdetails.accountcode
		AND periods.periodno=chartdetails.period
		WHERE (periods.periodno BETWEEN '" . $CreateFrom . "' AND '" . $CreateTo . "')
		AND chartdetails.accountcode IS NULL ORDER BY accountcode,periodno";
	$ErrMsg = _('Failed to retrieve new account code and periods');
	$NewPeriodResult = DB_query($sql,$ErrMsg);
	if (DB_num_rows($NewPeriodResult)>0){
		$NewPeriods = array();
		while ($NewPeriodsRow = DB_fetch_array($NewPeriodResult)) {
			if (!isset($NewPeriods[$NewPeriodsRow['accountcode']])) {
				$NewPeriods[$NewPeriodsRow['accountcode']] = $NewPeriodsRow['periods'];
			}
		}
	}
	//now insert the necessary chartdetails
	$sql = "INSERT INTO chartdetails (accountcode, period)
			SELECT chartmaster.accountcode, periods.periodno
			FROM (chartmaster CROSS JOIN periods)
			LEFT JOIN chartdetails ON chartmaster.accountcode = chartdetails.accountcode
			AND periods.periodno = chartdetails.period
			WHERE (periods.periodno BETWEEN '"  . $CreateFrom . "' AND '" . $CreateTo . "')
			AND chartdetails.accountcode IS NULL";

	$ErrMsg = _('Inserting new chart details records required failed because');
	$InsChartDetailsRecords = DB_query($sql,$ErrMsg);
}

//now lets update those new created period with the bfwd amount
if (isset($NewPeriods)) {
	foreach ($NewPeriods as $Account=>$Period) {
		if ($Period>$CreateFrom) {
			$sql = "UPDATE chartdetails
					SET bfwd=(SELECT t.newbfwd FROM (
													SELECT bfwd+actual as newbfwd FROM chartdetails WHERE accountcode='" . $Account . "' AND period='" . ($Period - 1) . "') AS t)
					WHERE accountcode='" . $Account . "' AND period>= " . $Period;
			$ErrMsg = _('Failed to update the bfwd amount');
			$BfwdResult = DB_query($sql,$ErrMsg);
		}
	}
}

/*All the ChartDetail records should have been created now and be available to accept postings */

for ( $CurrPeriod = $CreateFrom; $CurrPeriod <= $CreateTo; $CurrPeriod++ ) {
	//get all the unposted transactions for the first and successive periods ordered by account
	$sql = "SELECT counterindex,
					periodno,
					account,
					amount
				FROM gltrans
				WHERE posted=0
				AND periodno='" . $CurrPeriod . "'
				ORDER BY account";

	$UnpostedTransResult = DB_query($sql);

	$TransStart = DB_Txn_Begin();
	$CurrentAccount='0';
	$TotalAmount=0;
	while ($UnpostedTrans=DB_fetch_array($UnpostedTransResult)) {
		if($CurrentAccount != $UnpostedTrans['account'] AND $CurrentAccount!='0') {
			$sql = "UPDATE chartdetails SET actual = actual + " . $TotalAmount . "
					WHERE accountcode = '" . $CurrentAccount . "'
					AND period= '" . $CurrPeriod . "'";
			$ErrMsg = _('Failed to update actual amount in chartdetails table');
			$PostPrd = DB_query($sql,$ErrMsg,'',true);
			/*Update the BFwd for all following ChartDetail records */
			$sql = "UPDATE chartdetails SET bfwd = bfwd + " . $TotalAmount . "
					WHERE accountcode = '" . $CurrentAccount . "'
					AND period > '" . $CurrPeriod . "'";
			$ErrMsg = _('Failed to update bfwd amount in chartdetails table');
			$PostBFwds = DB_query($sql,$ErrMsg,'',true);
			$TotalAmount = 0;
		}
		$CurrentAccount = $UnpostedTrans['account'];
		$TotalAmount += $UnpostedTrans['amount'];
	}
	// There will be one account still to post after the loop
	if($CurrentAccount != '0') {
		$sql = "UPDATE chartdetails SET actual = actual + " . $TotalAmount . "
				WHERE accountcode = '" . $CurrentAccount . "'
				AND period= '" . $CurrPeriod . "'";
		$ErrMsg = _('Failed to update actual amount in chartdetails table');
		$PostPrd = DB_query($sql,$ErrMsg,'',true);
		/*Update the BFwd for all following ChartDetail records */
		$sql = "UPDATE chartdetails SET bfwd = bfwd + " . $TotalAmount . "
				WHERE accountcode = '" . $CurrentAccount . "'
				AND period > '" . $CurrPeriod . "'";
		$ErrMsg = _('Failed to update actual amount in chartdetails table');
		$PostBFwds = DB_query($sql,$ErrMsg,'',true);
	}

	$sql = "UPDATE gltrans SET posted = 1 WHERE periodno = '" . $CurrPeriod . "' AND posted=0";
	$ErrMsg = _('Failed to update gltrans table');
	$Posted = DB_query($sql,$ErrMsg,'',true);

	$TransCommit = DB_Txn_Commit();
}

?>
