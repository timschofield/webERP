<?php
/* $Revision: 1.9 $ */
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

$LastPeriodResult = DB_query('SELECT MAX(periodno) FROM periods',$db);
$LastPeriodRow = DB_fetch_row($LastPeriodResult);

$CreateFrom = $FirstPeriodRow[0];
$CreateTo = $LastPeriodRow[0];


/*First off see if there are any chartdetails missing create recordset of */

$sql = 'SELECT chartmaster.accountcode, MIN(periods.periodno) AS startperiod 
		FROM chartmaster CROSS JOIN periods 
			LEFT JOIN chartdetails ON chartmaster.accountcode = chartdetails.accountcode 
				AND periods.periodno = chartdetails.period
		WHERE (periods.periodno BETWEEN '  . $CreateFrom . ' AND ' . $CreateTo . ') 
		AND chartdetails.accountcode IS NULL
		GROUP BY chartmaster.accountcode';

$ChartDetailsNotSetUpResult = DB_query($sql,$db,_('Could not test to see that all chart detail records properly initiated'));

if(DB_num_rows($ChartDetailsNotSetUpResult)>0){
					
	/*Now insert the chartdetails records that do not already exist */
	$sql = 'INSERT INTO chartdetails (accountcode, period)
			SELECT chartmaster.accountcode, periods.periodno 
		FROM chartmaster CROSS JOIN periods 
			LEFT JOIN chartdetails ON chartmaster.accountcode = chartdetails.accountcode 
				AND periods.periodno = chartdetails.period
		WHERE (periods.periodno BETWEEN '  . $CreateFrom . ' AND ' . $CreateTo . ') 
		AND chartdetails.accountcode IS NULL';
	
	$ErrMsg = _('Inserting new chart details records required failed because');
	$InsChartDetailsRecords = DB_query($sql,$db,$ErrMsg);
	
}

/*All the ChartDetail records should have been created now and be available to accept postings */

$sql = 'SELECT counterindex, periodno, account, amount FROM gltrans WHERE posted=0';

$UnpostedTransResult = DB_query($sql, $db);

$TransStart = DB_query('BEGIN',$db);

while ($UnpostedTrans=DB_fetch_array($UnpostedTransResult)){
	/*
	if ($debug==1){
		echo '<BR>GLTrans Counter ' . $UnpostedTrans['counterindex'] . ' period ' . $UnpostedTrans['periodno'] . ' account - ' . $UnpostedTrans['account'] . ' amount= ' . $UnpostedTrans['amount'];
	}
	*/
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
	
		DB_query('BEGIN',$db);
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
					
		DB_query('COMMIT',$db);
			
		DB_free_result($ChartDetailsCFwd);
	}
}

?>