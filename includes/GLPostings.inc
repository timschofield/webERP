<?php
/* $Revision: 1.7 $ */
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

This posting code is not really that good since there are a heap of queires thrown at the DB
I would prefer to have fewer more complex queries.

However until subqueries are in MySQL there isnt much choice - at least I cant see a better way .... anyone ??

NEED to have a function that checks the TB of a period actually balances.
NEED to have a function that reposts from a given period having first checked the b/fwd balances balance!

*/


/*Add a check to ensure chart details now set up for all GLTrans not posted */

$sql = 'SELECT gltrans.account,
		MIN(gltrans.periodno) AS fromperiod
	FROM gltrans LEFT JOIN chartdetails
		ON gltrans.account=chartdetails.accountcode
		AND gltrans.periodno=chartdetails.period
	WHERE gltrans.posted=0
	AND chartdetails.accountcode IS NULL
	GROUP BY gltrans.account';
	
$ErrMsg = _('The test to see that all chart details records required have been set up failed because');
$ChartDetailsStillRequired = DB_query($sql,$db,$ErrMsg);
if (DB_num_rows($ChartDetailsStillRequired)>0){

	/*Script to insert ChartDetails records where one should already exist
	only necessary where manual entry of chartdetails has stuffed the system */

	$FirstPeriodResult = DB_query('SELECT MIN(periodno) FROM periods',$db);
	$FirstPeriodRow = DB_fetch_row($FirstPeriodResult);

	$LastPeriodResult = DB_query('SELECT MAX(periodno) FROM periods',$db);
	$LastPeriodRow = DB_fetch_row($LastPeriodResult);

	$CreateFrom = $FirstPeriodRow[0];
	$CreateTo = $LastPeriodRow[0];

	$ChartAccounts = DB_query('SELECT accountcode FROM chartmaster',$db);

	While ($AccountRow = DB_fetch_array($ChartAccounts)){

		for ($PeriodNo=$CreateFrom;$PeriodNo <= $CreateTo;$PeriodNo++) {

			echo '<LI>' . _('Period Number') . ' ' . $PeriodNo . '</LI>';
			
			// Check if there is an chart details record set up
			$sql = 'SELECT count(*) FROM chartdetails 
					WHERE accountcode='.$AccountRow['accountcode'].'
					AND period=' . $PeriodNo;
			$InsChartDetails = DB_query($sql,$db,'','','',false);
			$CountRows = DB_fetch_row($InsChartDetails);
			$AccountExistsAlready = $CountRows[0];
			DB_free_result($InsChartDetails);
			if(! $AccountExistsAlready) {
				$sql = 'INSERT INTO chartdetails (accountcode,
							period)
					VALUES (' . $AccountRow['accountcode'] . ',
						' . $PeriodNo . ')';
				$InsChartDetails = DB_query($sql,$db);
				DB_free_result($InsChartDetails);
			}
			
		}

	/*Now run through each of the new chartdetail records created for each account and update them with the B/Fwd and B/Fwd budget no updates would be required where there were previously no chart details set up ie FirstPeriodPostedTo > 0 */

		for ($PeriodNo=$CreateFrom;$PeriodNo<=$CreateTo; $PeriodNo++) {

			$sql = 'SELECT accountcode,
					period,
					actual + bfwd AS cfwd,
					budget + bfwdbudget AS cfwdbudget
				FROM chartdetails WHERE period =' . ($PeriodNo  - 1);
			$ChartDetailsCFwd = DB_query($sql,$db);

			while ($myrow = DB_fetch_array($ChartDetailsCFwd)){

				$sql = 'UPDATE chartdetails SET bfwd =' . $myrow['cfwd'] . ',
								bfwdbudget =' . $myrow['cfwdbudget'] . '
						WHERE accountcode = ' . $myrow['accountcode'] . '
						AND period >=' . $PeriodNo;
				$UpdChartDetails = DB_query($sql,$db, '', '', '', false);
				DB_free_result($UpdChartDetails);
			}
			DB_free_result($ChartDetailsCFwd);
		}

	}
}


/*All the ChartDetail records should have been created now and be available to accept postings */

$sql = 'SELECT counterindex, periodno, account, amount FROM gltrans WHERE posted=0';

$UnpostedTransResult = DB_query($sql, $db);

$TransStart = DB_query('BEGIN',$db);

while ($UnpostedTrans=DB_fetch_array($UnpostedTransResult)){

	if ($debug==1){
		echo '<BR>GLTrans Counter ' . $UnpostedTrans['counterindex'] . ' period ' . $UnpostedTrans['periodno'] . ' account - ' . $UnpostedTrans['account'] . ' amount= ' . $UnpostedTrans['amount'];
	}
	/*Update the Actual charge in the period of the transaction */
	$sql = 'UPDATE chartdetails SET actual = actual + ' . $UnpostedTrans['amount'] . ' 
		WHERE accountcode = ' . $UnpostedTrans['account'] . ' 
		AND period= ' . $UnpostedTrans['periodno'];
	$PostPrd = DB_query($sql,$db);
	/*Update the BFwd for all following ChartDetail records */
	$sql = 'UPDATE chartdetails SET bfwd = bfwd + ' . $UnpostedTrans['amount'] . ' 
			WHERE accountcode = ' . $UnpostedTrans['account'] . ' 
			AND period > ' . $UnpostedTrans['periodno'];
	$PostBFwds = DB_query($sql,$db);

	$sql = 'UPDATE gltrans SET posted = 1 WHERE counterindex = ' . $UnpostedTrans['counterindex'];
	$Posted = DB_query($sql,$db);
	
}
$TransCommit = DB_query('COMMIT',$db);

?>