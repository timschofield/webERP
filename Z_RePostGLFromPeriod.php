<?php
/* $Revision: 1.3 $ */
$title = "Recalculation of GL Balances in Chart Details Table";

$PageSecurity=15;

include ("includes/session.inc");
include("includes/header.inc");
include ("includes/DateFunctions.inc");


echo "<FORM METHOD='POST' ACTION=" . $_SERVER["PHP_SELF"] . "?" . SID . ">";


if (!isset($_POST["FromPeriod"])){

/*Show a form to allow input of criteria for TB to show */
	echo "<CENTER><TABLE><TR><TD>Select Period From:</TD><TD><SELECT Name='FromPeriod'>";

	$sql = "SELECT PeriodNo, LastDate_In_Period FROM Periods ORDER BY PeriodNo";
	$Periods = DB_query($sql,$db);


	while ($myrow=DB_fetch_array($Periods,$db)){

		echo "<OPTION VALUE=" . $myrow["PeriodNo"] . ">" . MonthAndYearFromSQLDate($myrow["LastDate_In_Period"]);

	}

	echo "</SELECT></TD></TR></TABLE>";

	echo "<INPUT TYPE=SUBMIT Name='recalc' Value='Do the Recalculation'></CENTER></FORM>";

} else {  /*OK do the updates */

	/* Make the posted flag on all GL entries including and after the period selected = 0 */
	$sql = "UPDATE GLTrans SET Posted=0 WHERE PeriodNo >=". $_POST["FromPeriod"];
	$UpdGLTransPostedFlag = DB_query($sql,$db);

	/* Now make all the actuals 0 for all periods including and after the period from */
	$sql = "UPDATE ChartDetails SET Actual =0 WHERE Period >= " . $_POST["FromPeriod"];
	$UpdActualChartDetails = DB_query($sql,$db);

	/*Now repost the lot */

	include("includes/GLPostings.inc");

	/*Make a note of all the subsequent periods to recalculate the B/Fwd balances for */

	$sql = "SELECT PeriodNo FROM Periods WHERE PeriodNo >= ". $_POST["FromPeriod"] . " ORDER BY PeriodNo";
	$Periods = DB_query($sql,$db);

	while ($PeriodRow=DB_fetch_row($Periods)){

		$sql="SELECT AccountCode, Period, Budget, Actual, BFwd, BFwdBudget FROM ChartDetails WHERE Period =". $PeriodRow[0];

		$result = DB_query($sql,$db);

		if (DB_error_no($db)!=0){
			echo "<BR><BR>Now hang on we have a problem here. " . DB_error_msg($db) . "<BR>The SQL that failed was :<BR>" . $sql;
			exit;
		}

		while ($myrow=DB_fetch_array($result)){
		
			$CFwd = $myrow["BFwd"] + $myrow["Actual"];
			$CFwdBudget = $myrow['BFwdBudget'] + $myrow['Budget'];
			echo "<BR>Account code : " . $myrow["AccountCode"] . " Period : " . $myrow["Period"];

			$sql = "UPDATE ChartDetails SET BFwd=" . $CFwd . ",BFwdBudget=" . $CFwdBudget . " WHERE Period=" . ($myrow["Period"] +1) . " AND  AccountCode = " . $myrow["AccountCode"];

			$updresult = DB_query($sql,$db);

			if (DB_error_no($db)!=0){

				echo "<BR><BR>Now hang on we have a problem here. " . DB_error_msg($db) . "<BR>The SQL that failed was :<BR>" . $sql;
				exit;
			}
		}
	} /* end of for loop */
}

include("includes/footer.inc");
?>
