<?php
/* $Revision: 1.3 $ */
$title = "Recalculation of Brought Forward Balances in Chart Details Table";
$PageSecurity=15;
include ("includes/session.inc");
include("includes/header.inc");
include ("includes/DateFunctions.inc");



echo "<FORM METHOD='POST' ACTION=" . $_SERVER["PHP_SELF"] . "?" . SID . ">";

if ($_POST["FromPeriod"] > $_POST["ToPeriod"]){
	echo "<P>The selected period from is actually after the period to! Please re-select the reporting period.";
	unset ($_POST["FromPeriod"]);
	unset ($_POST["ToPeriod"]);

}

if (!isset($_POST["FromPeriod"]) OR !isset($_POST["ToPeriod"])){


/*Show a form to allow input of criteria for TB to show */
	echo "<CENTER><TABLE><TR><TD>Select Period From:</TD><TD><SELECT Name='FromPeriod'>";

	$sql = "SELECT PeriodNo, LastDate_In_Period FROM Periods ORDER BY PeriodNo";
	$Periods = DB_query($sql,$db);


	while ($myrow=DB_fetch_array($Periods,$db)){

		echo "<OPTION VALUE=" . $myrow["PeriodNo"] . ">" . MonthAndYearFromSQLDate($myrow["LastDate_In_Period"]);

	}

	echo "</SELECT></TD></TR>";

	$sql = "SELECT Max(PeriodNo) FROM Periods";
	$MaxPrd = DB_query($sql,$db);
	$MaxPrdrow = DB_fetch_row($MaxPrd);

	$DefaultToPeriod = (int) ($MaxPrdrow[0]-1);

	echo "<TR><TD>Select Period To:</TD><TD><SELECT Name='ToPeriod'>";

	$RetResult = DB_data_seek($Periods,0);

	while ($myrow=DB_fetch_array($Periods,$db)){

		if($myrow["PeriodNo"]==$DefaultToPeriod){
			echo "<OPTION SELECTED VALUE=" . $myrow["PeriodNo"] . ">" . MonthAndYearFromSQLDate($myrow["LastDate_In_Period"]);
		} else {
			echo "<OPTION VALUE =" . $myrow["PeriodNo"] . ">" . MonthAndYearFromSQLDate($myrow["LastDate_In_Period"]);
		}
	}
	echo "</SELECT></TD></TR></TABLE>";

	echo "<INPUT TYPE=SUBMIT Name='recalc' Value='Do the Recalculation'></CENTER></FORM>";

} else {  /*OK do the updates */

	for ($i=$_POST["FromPeriod"];$i<=$_POST["ToPeriod"];$i++){

		$sql="SELECT AccountCode, Period, Budget, Actual, BFwd, BFwdBudget FROM ChartDetails WHERE Period =". $i;

		$result = DB_query($sql,$db);

		if (DB_error_no($db)!=0){
			echo "<BR><BR>Now hang on we have a problem here. " . DB_error_msg($db) . "<BR>The SQL that failed was :<BR>" . $sql;
			exit;
		}

		while ($myrow=DB_fetch_array($result)){
		
			$CFwd = $myrow["BFwd"] + $myrow["Actual"];
			$CFwdBudget = $myrow["BFwdBudget"] + $myrow["Budget"];

			echo "<BR>Account code : " . $myrow["AccountCode"] . " Period : " . $myrow["Period"];

			$sql = "UPDATE ChartDetails SET BFwd=" . $CFwd . ", BFwdBudget=" . $CFwdBudget . " WHERE Period=" . ($myrow["Period"] +1) . " AND  AccountCode = " . $myrow["AccountCode"];

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
