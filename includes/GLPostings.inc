<?php

/* This file contains the code to post GL transactions.

This file can be included on any page that needs GL postings to be posted eg inquiries or GL reports
GL posting thus becomes an invisible/automatic process to the user

The logic of GL posting consists of:

Creating the Chart Details records for all accounts/periods where one does not already exist and might be required.

Then looping through all unposted GL transactions in GLTrans table and

1. Debit amounts increase the charge in the period for the account and credit amounts decrease the charge.
2. Chart Details records for all following periods have the b/fwd balance increased for debit amounts and decreased for credits.
3. Once these updates are done the GLTrans record is flagged as posted.


Notes:

This posting code is not really that good since there are a heap of queires thrown at the DB
I would prefer to have fewer more complex queries.

However until subqueries are in MySQL there isnt much choice - at least I cant see a better way .... anyone ??

NEED to have a function that checks the TB of a period actually balances.
NEED to have a function that reposts from a given period having first checked the b/fwd balances balance!

*/

/*Get the Last Period that has a GL transaction entry to it */

$sql = "SELECT Max(PeriodNo) AS GLTransLatestPeriod FROM GLTrans WHERE Posted=0";
$GetLatestPeriod = DB_query($sql,$db);
$myrow = DB_fetch_row($GetLatestPeriod);
$LastPeriodPostedTo = $myrow[0];


/* Now get the last period that has ChartDetails records already defined
if the last period that has GL Entries is greater than the last period that
has ChartDetail records set up then we need to create newies */

$sql = "SELECT Max(Period) AS ChartLatestPeriod FROM ChartDetails";
$GetLastChartDetailPeriod = DB_query($sql,$db);
$myrow = DB_fetch_row($GetLastChartDetailPeriod);

if ($myrow[0] != NULL){
	$LastChartDetailPeriod = $myrow[0];
} else {
	/*Figure out when the first GL Postings that have been entered */
	$sql = "SELECT Min(PeriodNo) AS GLTransFisrtPeriod FROM GLTrans WHERE Posted=0";
	$GetFirstPeriod = DB_query($sql,$db);
	$myrow = DB_fetch_row($GetFirstPeriod);
	$FirstPeriodPostedTo = $myrow[0];
}

if ($FirstPeriodPostedTo > 0){
	$CreateChartDetailsFrom = $FirstPeriodPostedTo;
} else {
	$CreateChartDetailsFrom = $LastChartDetailPeriod + 1;
}


if ($CreateChartDetailsFrom <= $LastPeriodPostedTo){ /*Then there are new chartdetail records to be created */

	$sql = "begin";
	$TransStart = DB_query($sql,$db);

	/*Right now then .... create the chart detail records for all accounts from CreateChartDetailsFrom to LastPeriodPostedTo */

	for ($i=$CreateChartDetailsFrom; $i<=($LastPeriodPostedTo); $i++) {

		$sql = "INSERT INTO ChartDetails (AccountCode, Period) SELECT AccountCode," . $i . " FROM ChartMaster";
		$InsChartDetails = DB_query($sql,$db);
	}

	/*Now run through each of the new chartdetail records created for each account and update them with the B/Fwd and B/Fwd budget
	No updates would be required where there were previously no chart details set up ie FirstPeriodPostedTo > 0 */
	if (! isset($FirstPeriodPostedTo)){

		$sql = "SELECT AccountCode, Period, Actual + BFwd AS CFwd, Budget + BFwdBudget AS CFwdBudget FROM ChartDetails WHERE Period =" . ($CreateChartDetailsFrom  - 1);
		$ChartDetailsCFwd = DB_query($sql,$db);
		while ($myrow = DB_fetch_array($ChartDetailsCFwd)){

			$sql = "UPDATE ChartDetails SET BFwd =" . $myrow['CFwd'] . ", BFwdBudget =" . $myrow['CFwdBudget'] . " WHERE AccountCode = " . $myrow['AccountCode'] . " AND Period >=" . $CreateChartDetailsFrom;
			$UpdChartDetails = DB_query($sql,$db);
		}
	}

	$sql = "Commit";
	$TransCommit = DB_query($sql,$db);
}

/*Add a check to ensure chart details now set up for all GLTrans not posted */

$sql = "SELECT GLTrans.Account, MIN(GLTrans.PeriodNo) AS FromPeriod FROM GLTrans LEFT JOIN ChartDetails ON GLTrans.Account=ChartDetails.AccountCode AND GLTrans.PeriodNo=ChartDetails.Period WHERE GLTrans.Posted=0 AND ChartDetails.AccountCode IS NULL GROUP BY GLTrans.Account";

$ChartDetailsStillRequired = DB_query($sql,$db);
if (DB_num_rows($ChartDetailsStillRequired)>0){

	while ($myrow = DB_fetch_array($ChartDetailsStillRequired)){

		echo "<BR>Creating ChartDetail for Account " . $myrow['Account'];

		$sql = "begin";
		$TransStart = DB_query($sql,$db);

		/*Right now then .... create the chart detail records for the account concerned to LastPeriodPostedTo */

		for ($i=$myrow['FromPeriod'];$i<=($LastPeriodPostedTo); $i++) {

			$sql = "INSERT INTO ChartDetails (AccountCode, Period) VALUES(" . $myrow['Account'] . "," . $i . ")";
			$InsChartDetails = DB_query($sql,$db);
		}

	/*Now run through each of the new chartdetail records created for each account and update them with the B/Fwd and B/Fwd budget */

		$sql = "SELECT Period, Actual + BFwd AS CFwd, Budget + BFwdBudget AS CFwdBudget FROM ChartDetails WHERE Period =" . ($myrow['FromPeriod']  - 1) . " AND AccountCode=" . $myrow['Account'];
		$ExistingChartDetail = DB_query($sql,$db);
		if (DB_num_rows($ExistingChartDetail)==1){
			$sql = "UPDATE ChartDetails SET BFwd =" . $myrow['CFwd'] . ", BFwdBudget =" . $myrow['CFwdBudget'] . " WHERE AccountCode = " . $myrow['AccountCode'] . " AND Period >=" . $myrow['FromPeriod'];
			$UpdChartDetails = DB_query($sql,$db);
		}

		$sql = "Commit";
		$TransCommit = DB_query($sql,$db);
	} /* end of loop to get all accounts without chart details where there should be */
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
