<?php

$PageSecurity = 9;

include ('includes/session.inc');
$title = _('Create Chart Details Records');
include ('includes/header.inc');

/*Script to insert ChartDetails records where one should already exist
only necessary where manual entry of chartdetails has stuffed the system */

$FirstPeriodResult = DB_query('SELECT MIN(periodno) FROM periods',$db);
$FirstPeriodRow = DB_fetch_row($FirstPeriodResult);

$LastPeriodResult = DB_query('SELECT MAX(periodno) FROM periods',$db);
$LatPeriodRow = DB_fetch_row($LastPeriodResult);

$CreateFrom = $FirstPeriodRow[0];
$CreateTo = $LastPeriodRow[0];;

$ChartAccounts = DB_query("SELECT accountcode FROM chartmaster",$db);

While ($AccountRow = DB_fetch_array($ChartAccounts)){

	prnMsg(_('Creating Chart Details for Account code') . ' ' . $AccountRow['accountcode'],'info');

	for ($PeriodNo=$CreateFrom;$PeriodNo <= $CreateTo;$PeriodNo++) {

		echo '<LI>' . _('Period Number') . ' ' . $PeriodNo . '</LI>';

		$sql = "INSERT INTO chartdetails (accountcode,
							period)
					VALUES (" . $AccountRow['accountcode'] . ",
						" . $PeriodNo . ")";
		$InsChartDetails = DB_query($sql,$db,'','','',false);
		/*dont trap errors here the thinking is that duplicate records will return and error that is ignored
		otherwise a chart detail record is created correctly - all fine */

	}

/*Now run through each of the new chartdetail records created for each account and update them with the B/Fwd and B/Fwd budget no updates would be required where there were previously no chart details set up ie FirstPeriodPostedTo > 0 */

	for ($PeriodNo=$CreateFrom;$PeriodNo<=$CreateTo; $PeriodNo++) {

		$sql = "SELECT accountcode,
				period,
				actual + bfwd AS cfwd,
				budget + bfwdbudget AS cfwdbudget
			FROM chartdetails WHERE period =" . ($PeriodNo  - 1);
		$ChartDetailsCFwd = DB_query($sql,$db);

		while ($myrow = DB_fetch_array($ChartDetailsCFwd)){

			$sql = "UPDATE chartdetails SET bfwd =" . $myrow['cfwd'] . ",
							bfwdbudget =" . $myrow['cfwdbudget'] . "
					WHERE accountcode = " . $myrow['accountcode'] . "
					AND period >=" . $PeriodNo;
			$UpdChartDetails = DB_query($sql,$db);
		}
	}

}

prnMsg(_('Chart Details Created successfully'),'success');
include('includes/footer.inc');
?>