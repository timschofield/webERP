<?php
/* $Revision: 1.4 $ */
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

$sql = "SELECT GLTrans.Account,
		MIN(GLTrans.PeriodNo) AS FromPeriod
	FROM GLTrans LEFT JOIN ChartDetails
		ON GLTrans.Account=ChartDetails.AccountCode
		AND GLTrans.PeriodNo=ChartDetails.Period
	WHERE GLTrans.Posted=0
	AND ChartDetails.AccountCode IS NULL
	GROUP BY GLTrans.Account";

$ErrMsg = _('The test to see that all chart details records required have been set up failed because');
$ChartDetailsStillRequired = DB_query($sql,$db,$ErrMsg);
if (DB_num_rows($ChartDetailsStillRequired)>0){

	/*Script to insert ChartDetails records where one should already exist
	only necessary where manual entry of chartdetails has stuffed the system */

	$FirstPeriodResult = DB_query('SELECT Min(PeriodNo) FROM Periods',$db);
	$FirstPeriodRow = DB_fetch_row($FirstPeriodResult);

	$LastPeriodResult = DB_query('SELECT Max(PeriodNo) FROM Periods',$db);
	$LastPeriodRow = DB_fetch_row($LastPeriodResult);

	$CreateFrom = $FirstPeriodRow[0];
	$CreateTo = $LastPeriodRow[0];;

	$ChartAccounts = DB_query('SELECT AccountCode FROM ChartMaster',$db);

	While ($AccountRow = DB_fetch_array($ChartAccounts)){

		for ($PeriodNo=$CreateFrom;$PeriodNo <= $CreateTo;$PeriodNo++) {

			echo '<LI>' . _('Period Number') . ' ' . $PeriodNo . '</LI>';

			$sql = "INSERT INTO ChartDetails (AccountCode,
								Period)
						VALUES (" . $AccountRow['AccountCode'] . ",
							" . $PeriodNo . ")";
			$InsChartDetails = DB_query($sql,$db,'','','',false);
			/*dont trap errors here the thinking is that duplicate records will return and error that is ignored
			otherwise a chart detail record is created correctly - all fine */

		}

	/*Now run through each of the new chartdetail records created for each account and update them with the B/Fwd and B/Fwd budget no updates would be required where there were previously no chart details set up ie FirstPeriodPostedTo > 0 */

		for ($PeriodNo=$CreateFrom;$PeriodNo<=$CreateTo; $PeriodNo++) {

			$sql = "SELECT AccountCode,
					Period,
					Actual + BFwd AS CFwd,
					Budget + BFwdBudget AS CFwdBudget
				FROM ChartDetails WHERE Period =" . ($PeriodNo  - 1);
			$ChartDetailsCFwd = DB_query($sql,$db);

			while ($myrow = DB_fetch_array($ChartDetailsCFwd)){

				$sql = "UPDATE ChartDetails SET BFwd =" . $myrow['CFwd'] . ",
								BFwdBudget =" . $myrow['CFwdBudget'] . "
						WHERE AccountCode = " . $myrow['AccountCode'] . "
						AND Period >=" . $PeriodNo;
				$UpdChartDetails = DB_query($sql,$db);
			}
		}

	}
}


/*All the ChartDetail records should have been created now and be available to accept postings */

$sql = "Select CounterIndex, PeriodNo, Account, Amount, Posted FROM GLTrans WHERE Posted=0";

$UnpostedTransResult = DB_query($sql, $db);

$sql = "begin";
$TransStart = DB_query($sql,$db);

while ($UnpostedTrans=DB_fetch_array($UnpostedTransResult)){

	/*Update the Actual charge in the period of the transaction */
	$sql = "UPDATE ChartDetails SET Actual = Actual + " . $UnpostedTrans["Amount"] . " WHERE AccountCode = " . $UnpostedTrans["Account"] . " AND Period= " . $UnpostedTrans["PeriodNo"];
	$PostPrd = DB_query($sql,$db);
	/*Update the BFwd for all following ChartDetail records */
	$sql = "UPDATE ChartDetails SET BFwd = BFwd + " . $UnpostedTrans["Amount"] . " WHERE AccountCode = " . $UnpostedTrans["Account"] . " AND Period > " . $UnpostedTrans["PeriodNo"];
	$PostBFwds = DB_query($sql,$db);

	$sql = "UPDATE GLTrans SET Posted = 1 WHERE CounterIndex = " . $UnpostedTrans['CounterIndex'];
	$Posted = DB_query($sql,$db);
}

$sql = "Commit";
$TransCommit = DB_query($sql,$db);

?>
