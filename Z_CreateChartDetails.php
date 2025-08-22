<?php

include('includes/session.php');
$Title = __('Create Chart Details Records');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');

/*Script to insert ChartDetails records where one should already exist
only necessary where manual entry of chartdetails has stuffed the system */

$FirstPeriodResult = DB_query("SELECT MIN(periodno) FROM periods");
$FirstPeriodRow = DB_fetch_row($FirstPeriodResult);

$LastPeriodResult = DB_query("SELECT MAX(periodno) FROM periods");
$LastPeriodRow = DB_fetch_row($LastPeriodResult);

$CreateFrom = $FirstPeriodRow[0];
$CreateTo = $LastPeriodRow[0];


/*First off see if there are any chartdetails missing create recordset of */

$SQL = "SELECT chartmaster.accountcode, MIN(periods.periodno) AS startperiod
		FROM chartmaster CROSS JOIN periods
			LEFT JOIN chartdetails ON chartmaster.accountcode = chartdetails.accountcode
				AND periods.periodno = chartdetails.period
		WHERE (periods.periodno BETWEEN '"  . $CreateFrom . "' AND '" . $CreateTo . "')
		AND chartdetails.accountcode IS NULL
		GROUP BY chartmaster.accountcode";

$ChartDetailsNotSetUpResult = DB_query($SQL,__('Could not test to see that all chart detail records properly initiated'));

if(DB_num_rows($ChartDetailsNotSetUpResult)>0){

	/*Now insert the chartdetails records that do not already exist */
	$SQL = "INSERT INTO chartdetails (accountcode, period)
			SELECT chartmaster.accountcode, periods.periodno
		FROM chartmaster CROSS JOIN periods
			LEFT JOIN chartdetails ON chartmaster.accountcode = chartdetails.accountcode
				AND periods.periodno = chartdetails.period
		WHERE (periods.periodno BETWEEN '"  . $CreateFrom . "' AND '" . $CreateTo . "')
		AND chartdetails.accountcode IS NULL";

	$ErrMsg = __('Inserting new chart details records required failed because');
	$InsChartDetailsRecords = DB_query($SQL, $ErrMsg);


	while ($AccountRow = DB_fetch_array($ChartDetailsNotSetUpResult)){

		/*Now run through each of the new chartdetail records created for each account and update them with the B/Fwd and B/Fwd budget no updates would be required where there were previously no chart details set up ie FirstPeriodPostedTo > 0 */

		$SQL = "SELECT actual,
				bfwd,
				budget,
				bfwdbudget,
				period
			FROM chartdetails
			WHERE period >='" . ($AccountRow['period']-1) . "'
			AND accountcode='" . $AccountRow['accountcode'] . "'
			ORDER BY period";
		$ChartDetails = DB_query($SQL);

		DB_Txn_Begin();
		$BFwd = '';
		$BFwdBudget ='';
		$CFwd=0;
		$CFwdBudget=0;
		while ($MyRow = DB_fetch_array($ChartDetails)){
			if ($BFwd =''){
				$BFwd = $MyRow['bfwd'];
				$BFwdBudget = $MyRow['bfwdbudget'];
			} else {
				$BFwd +=$MyRow['actual'];
				$BFwdBudget += $MyRow['budget'];
				$SQL = "UPDATE chartdetails SET bfwd ='" . $BFwd . "',
							bfwdbudget ='" . $BFwdBudget . "'
					WHERE accountcode = '" . $AccountRow['accountcode'] . "'
					AND period ='" . ($MyRow['period']+1) . "'";

				$UpdChartDetails = DB_query($SQL, '', '', false, false);
			}
		}

		DB_Txn_Commit();

		DB_free_result($ChartDetailsCFwd);
	}

	prnMsg(__('Chart Details Created successfully'),'success');

} else {

	prnMsg(__('No additional chart details were required to be added'),'success');
}

include('includes/footer.php');
