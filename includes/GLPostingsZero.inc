<?php

/* $Id: GLPostings.inc 6945 2014-10-27 07:20:48Z daintree $*/

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
*/


$FirstPeriodResult = DB_query("SELECT MIN(periodno) FROM periods");
$FirstPeriodRow = DB_fetch_row($FirstPeriodResult);
$CreateFrom = $FirstPeriodRow[0];

if (is_null($FirstPeriodRow[0])){
	//There are no periods defined
	$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (-1,'" . Date('Y-m-d',mktime(0,0,0,Date('m')-1,0,Date('Y'))) . "')",_('Could not insert first period'));
	$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (0,'" . Date('Y-m-d',mktime(0,0,0,Date('m')+1,0,Date('Y'))) . "')",_('Could not insert first period'));
	$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (1,'" . Date('Y-m-d',mktime(0,0,0,Date('m')+2,0,Date('Y'))) . "')",_('Could not insert second period'));
	$CreateFrom=-1;
}

$LastPeriodResult = DB_query("SELECT MAX(periodno) FROM periods");
$LastPeriodRow = DB_fetch_row($LastPeriodResult);


$CreateTo = $LastPeriodRow[0];

/*First off see if there are in fact any chartdetails */

$sql = "SELECT chartmaster.accountcode, MIN(periods.periodno) AS startperiod
				FROM (chartmaster CROSS JOIN periods)
				LEFT JOIN chartdetails ON chartmaster.accountcode = chartdetails.accountcode
				AND periods.periodno = chartdetails.period
				WHERE (periods.periodno BETWEEN '"  . $CreateFrom . "' AND '" . $CreateTo . "')
				AND chartdetails.actual IS NULL
				GROUP BY chartmaster.accountcode";

$ChartDetailsNotSetUpResult = DB_query($sql,_('Could not test to see that all chart detail records properly initiated'));

if(DB_num_rows($ChartDetailsNotSetUpResult)>0){

		/*Now insert the chartdetails records that do not already exist */
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
			$PostPrd = DB_query($sql);
			/*Update the BFwd for all following ChartDetail records */
			$sql = "UPDATE chartdetails SET bfwd = bfwd + " . $TotalAmount . "
					WHERE accountcode = '" . $CurrentAccount . "'
					AND period > '" . $CurrPeriod . "'";
			$PostBFwds = DB_query($sql);
			$TotalAmount = 0;
		}
		$CurrentAccount = $UnpostedTrans['account'];
		$TotalAmount += $UnpostedTrans['amount'];
	}
	// There will be one account still to post after the loop
	if($CurrentAccount != 0) {
		$sql = "UPDATE chartdetails SET actual = actual + " . $TotalAmount . "
				WHERE accountcode = '" . $CurrentAccount . "'
				AND period= '" . $CurrPeriod . "'";
		$PostPrd = DB_query($sql);
		/*Update the BFwd for all following ChartDetail records */
		$sql = "UPDATE chartdetails SET bfwd = bfwd + " . $TotalAmount . "
				WHERE accountcode = '" . $CurrentAccount . "'
				AND period > '" . $CurrPeriod . "'";
		$PostBFwds = DB_query($sql);
	}

	$sql = "UPDATE gltrans SET posted = 1 WHERE periodno = '" . $CurrPeriod . "' AND posted=0";
	$Posted = DB_query($sql);

	$TransCommit = DB_Txn_Commit();
}

?>