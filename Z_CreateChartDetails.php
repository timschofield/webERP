<?php

$title = "Create Chart Details Records";
$PageSecurity = 9;

include ("includes/session.inc");
include ("includes/header.inc");

/*Script to insert ChartDetails records where one should already exist
only necessary where manual entry of chartdetails has stuffed the system */

$CreateFrom = 49;
$CreateTo = 60;

$ChartAccounts = DB_query("SELECT AccountCode FROM ChartMaster",$db);

While ($AccountRow = DB_fetch_array($ChartAccounts)){

	echo "<BR>Creating Chart Details for Account code " . $AccountRow['AccountCode'];

	for ($PeriodNo=$CreateFrom;$PeriodNo <= $CreateTo;$PeriodNo++) {

		echo "<LI>Period Number $PeriodNo</LI>";

		$sql = "INSERT INTO ChartDetails (AccountCode, Period) VALUES (" . $AccountRow['AccountCode'] . ", " . $PeriodNo . ")";
		$InsChartDetails = DB_query($sql,$db,'','','',false);

	}
}

/*Now run through each of the new chartdetail records created for each account and update them with the B/Fwd and B/Fwd budget no updates would be required where there were previously no chart details set up ie FirstPeriodPostedTo > 0 */

for ($PeriodNo=$CreateFrom;$PeriodNo<=$CreateTo; $PeriodNo++) {

	$sql = "SELECT AccountCode, Period, Actual + BFwd AS CFwd, Budget + BFwdBudget AS CFwdBudget FROM ChartDetails WHERE Period =" . ($PeriodNo  - 1);
	$ChartDetailsCFwd = DB_query($sql,$db);

	while ($myrow = DB_fetch_array($ChartDetailsCFwd)){

		$sql = "UPDATE ChartDetails SET BFwd =" . $myrow['CFwd'] . ", BFwdBudget =" . $myrow['CFwdBudget'] . " WHERE AccountCode = " . $myrow['AccountCode'] . " AND Period >=" . $PeriodNo;
		$UpdChartDetails = DB_query($sql,$db);
	}
}

echo "<P>Chart Details Created successfully";


?>