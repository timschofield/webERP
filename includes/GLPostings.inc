<?php

/* $Revision: 1.17 $ */

/* This file contains the code to post GL transactions.

This file can be included on any page that needs GL postings to be posted eg inquiries or GL reports
GL posting thus becomes an invisible/automatic process to the user

The logic of GL posting consists of:


Then looping through all unposted GL transactions in GLTrans table and

1. Debit amounts increase the charge in the period for the account and credit amounts decrease the charge.
2. Chart Details records for all following periods have the b/fwd balance increased for debit amounts and decreased for credits.
3. Once these updates are done the GLTrans record is flagged as posted.


Notes:

ChartDetail records should already exist - they are created when a new period is created or when a new GL account is created for all periods in the periods table

NEED to have a function that checks the TB of a period actually balances.
NEED to have a function that reposts from a given period having first checked the b/fwd balances balance!

*/


$FirstPeriodResult = DB_query('SELECT MIN(periodno) FROM periods',$db);
$FirstPeriodRow = DB_fetch_row($FirstPeriodResult);
$CreateFrom = $FirstPeriodRow[0];

if (is_null($FirstPeriodRow[0])){
	//There are no periods defined
	$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (0,'" . Date('Y-m-d',mktime(0,0,0,Date('m')+1,0,Date('Y'))) . "')",$db,_('Could not insert first period'));
	$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (1,'" . Date('Y-m-d',mktime(0,0,0,Date('m')+2,0,Date('Y'))) . "')",$db,_('Could not insert second period'));
	$CreateFrom=0;
}

$LastPeriodResult = DB_query('SELECT MAX(periodno) FROM periods',$db);
$LastPeriodRow = DB_fetch_row($LastPeriodResult);


$CreateTo = $LastPeriodRow[0];



/*First off see if there are in fact any chartdetails */

$ChartDetailsResult = DB_query('SELECT * FROM chartdetails',$db);
$sql = 'SELECT chartmaster.accountcode, MIN(periods.periodno) AS startperiod
				FROM (chartmaster CROSS JOIN periods)
				LEFT JOIN chartdetails ON chartmaster.accountcode = chartdetails.accountcode
				AND periods.periodno = chartdetails.period

				WHERE (periods.periodno BETWEEN '  . $CreateFrom . ' AND ' . $CreateTo . ')
				AND chartdetails.actual IS NULL
				GROUP BY chartmaster.accountcode';

$ChartDetailsNotSetUpResult = DB_query($sql,$db,_('Could not test to see that all chart detail records properly initiated'));

if(DB_num_rows($ChartDetailsNotSetUpResult)>0){

		/*Now insert the chartdetails records that do not already exist */
		$sql = 'INSERT INTO chartdetails (accountcode, period)
					SELECT chartmaster.accountcode, periods.periodno
					FROM (chartmaster CROSS JOIN periods)
					LEFT JOIN chartdetails ON chartmaster.accountcode = chartdetails.accountcode
					AND periods.periodno = chartdetails.period
					WHERE (periods.periodno BETWEEN '  . $CreateFrom . ' AND ' . $CreateTo . ')
					AND chartdetails.accountcode IS NULL';

		$ErrMsg = _('Inserting new chart details records required failed because');
		$InsChartDetailsRecords = DB_query($sql,$db,$ErrMsg);
}



/*All the ChartDetail records should have been created now and be available to accept postings */

for ( $CurrPeriod = $CreateFrom; $CurrPeriod <= $CreateTo; $CurrPeriod++ ) {

		$sql = 'SELECT counterindex,
			periodno,
			account,
			amount
		FROM gltrans
		WHERE posted=0
		AND periodno=' . $CurrPeriod . '
		ORDER BY account';

	$UnpostedTransResult = DB_query($sql, $db);

	$TransStart = DB_Txn_Begin($db);
	$CurrentAccount=0;
	$TotalAmount=0;
	while ($UnpostedTrans=DB_fetch_array($UnpostedTransResult)){
		if($CurrentAccount != $UnpostedTrans['account']) {
			if($CurrentAccount != 0) {
				$sql = 'UPDATE chartdetails SET actual = actual + ' . $TotalAmount . '
					WHERE accountcode = ' . $CurrentAccount . '
					AND period= ' . $CurrPeriod;
				$PostPrd = DB_query($sql,$db);
				/*Update the BFwd for all following ChartDetail records */
				$sql = 'UPDATE chartdetails SET bfwd = bfwd + ' . $TotalAmount . '
					WHERE accountcode = ' . $CurrentAccount . '
					AND period > ' . $CurrPeriod;
				$PostBFwds = DB_query($sql,$db);
			}
			$TotalAmount = 0;
			$CurrentAccount = $UnpostedTrans['account'];
		}
		$TotalAmount = $TotalAmount + $UnpostedTrans['amount'];
		/*Update the Actual charge in the period of the transaction */
		$sql = 'UPDATE gltrans SET posted = 1 WHERE counterindex = ' . $UnpostedTrans['counterindex'];
		$Posted = DB_query($sql,$db);
	}
	// There will be one chartdetail update outstanding if we processed anything
	if($CurrentAccount != 0) {
		$sql = 'UPDATE chartdetails SET actual = actual + ' . $TotalAmount . '
			WHERE accountcode = ' . $CurrentAccount . '
			AND period= ' . $CurrPeriod;
		$PostPrd = DB_query($sql,$db);
		/*Update the BFwd for all following ChartDetail records */
		$sql = 'UPDATE chartdetails SET bfwd = bfwd + ' . $TotalAmount . '
			WHERE accountcode = ' . $CurrentAccount . '
			AND period > ' . $CurrPeriod;
		$PostBFwds = DB_query($sql,$db);
	}
	$TransCommit = DB_Txn_Commit($db);
}

/* The Code below has been superseeded by Danie's above - this grouping by account then summing the total postings for an account
was required as an optimisation for postgres */

/*
$sql = 'SELECT counterindex, periodno, account, amount FROM gltrans WHERE posted=0';

$UnpostedTransResult = DB_query($sql, $db);

$TransStart = DB_Txn_Begin($db);

while ($UnpostedTrans=DB_fetch_array($UnpostedTransResult)){
	$sql = 'UPDATE chartdetails SET actual = actual + ' . $UnpostedTrans['amount'] . '
		WHERE accountcode = ' . $UnpostedTrans['account'] . '
		AND period= ' . $UnpostedTrans['periodno'];
	$PostPrd = DB_query($sql,$db);

	$sql = 'UPDATE chartdetails SET bfwd = bfwd + ' . $UnpostedTrans['amount'] . '
			WHERE accountcode = ' . $UnpostedTrans['account'] . '
			AND period > ' . $UnpostedTrans['periodno'];
	$PostBFwds = DB_query($sql,$db);

	$sql = 'UPDATE gltrans SET posted = 1 WHERE counterindex = ' . $UnpostedTrans['counterindex'];
	$Posted = DB_query($sql,$db);

}
$TransCommit = DB_Txn_Commit($db));
*/

if (DB_num_rows($ChartDetailsNotSetUpResult)>0){

	While ($AccountRow = DB_fetch_array($ChartDetailsNotSetUpResult)){

		/*Now run through each of the new chartdetail records created for each account and update them with the B/Fwd and B/Fwd budget no updates would be required where there were previously no chart details set up */


		$sql = 'SELECT actual,
				bfwd,
				budget,
				bfwdbudget,
				period
			FROM chartdetails
			WHERE period >=' . ($AccountRow['startperiod']-1) . '
			AND accountcode=' . $AccountRow['accountcode'] . '
			ORDER BY period';
		$ChartDetails = DB_query($sql,$db);

		DB_Txn_Begin($db);
		$myrow = DB_fetch_array($ChartDetails);

		$BFwd = $myrow['bfwd'];
		$BFwdBudget = $myrow['bfwdbudget'];

		while ($myrow = DB_fetch_array($ChartDetails)){
			if ($myrow['period'] < $CreateTo) {
				$BFwd +=$myrow['actual'];
				$BFwdBudget += $myrow['budget'];
				$sql = 'UPDATE chartdetails SET bfwd =' . $BFwd . ',
							bfwdbudget =' . $BFwdBudget . '
					WHERE accountcode = ' . $AccountRow['accountcode'] . '
					AND period =' . $myrow['period']+1;

				$UpdChartDetails = DB_query($sql,$db, '', '', '', false);
			}
		}

		DB_Txn_Commit($db);
	}
}

?>