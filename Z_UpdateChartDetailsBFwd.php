<?php
/* $Revision: 1.4 $ */
$PageSecurity=15;
include ('includes/session.inc');
$title = _('Recalculation of Brought Forward Balances in Chart Details Table');
include('includes/header.inc');
include ('includes/DateFunctions.inc');



echo "<FORM METHOD='POST' ACTION=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

if ($_POST['FromPeriod'] > $_POST['ToPeriod']){
	prnMsg(_('The selected period from is actually after the period to! Please re-select the reporting period'),'error');
	unset ($_POST['FromPeriod']);
	unset ($_POST['ToPeriod']);

}

if (!isset($_POST['FromPeriod']) OR !isset($_POST['ToPeriod'])){


/*Show a form to allow input of criteria for TB to show */
	echo '<CENTER><TABLE><TR><TD>' . _('Select Period From') . ":</TD><TD><SELECT Name='FromPeriod'>";

	$sql = 'SELECT PeriodNo, LastDate_In_Period FROM Periods ORDER BY PeriodNo';
	$Periods = DB_query($sql,$db);


	while ($myrow=DB_fetch_array($Periods,$db)){

		echo '<OPTION VALUE=' . $myrow['PeriodNo'] . '>' . MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);

	}

	echo '</SELECT></TD></TR>';

	$sql = 'SELECT Max(PeriodNo) FROM Periods';
	$MaxPrd = DB_query($sql,$db);
	$MaxPrdrow = DB_fetch_row($MaxPrd);

	$DefaultToPeriod = (int) ($MaxPrdrow[0]-1);

	echo '<TR><TD>' . _('Select Period To') . ":</TD><TD><SELECT Name='ToPeriod'>";

	$RetResult = DB_data_seek($Periods,0);

	while ($myrow=DB_fetch_array($Periods,$db)){

		if($myrow['PeriodNo']==$DefaultToPeriod){
			echo '<OPTION SELECTED VALUE=' . $myrow['PeriodNo'] . '>' . MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);
		} else {
			echo '<OPTION VALUE =' . $myrow['PeriodNo'] . '>' . MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);
		}
	}
	echo '</SELECT></TD></TR></TABLE>';

	echo "<INPUT TYPE=SUBMIT Name='recalc' Value='" . _('Do the Recalculation') . "'></CENTER></FORM>";

} else {  /*OK do the updates */

	for ($i=$_POST['FromPeriod'];$i<=$_POST['ToPeriod'];$i++){

		$sql='SELECT AccountCode, Period, Budget, Actual, BFwd, BFwdBudget FROM ChartDetails WHERE Period ='. $i;

		$ErrMsg = _('Could not retrieve the ChartDetail records becaue');
		$result = DB_query($sql,$db,$ErrMsg);

		while ($myrow=DB_fetch_array($result)){

			$CFwd = $myrow['BFwd'] + $myrow['Actual'];
			$CFwdBudget = $myrow['BFwdBudget'] + $myrow['Budget'];

			echo '<BR>' . _('Account code') . ': ' . $myrow['AccountCode'] . ' ' . _('Period') .': ' . $myrow['Period'];

			$sql = 'UPDATE ChartDetails SET BFwd=' . $CFwd . ', BFwdBudget=' . $CFwdBudget . ' WHERE Period=' . ($myrow['Period'] +1) . ' AND  AccountCode = ' . $myrow['AccountCode'];

			$ErrMsg =_('Could not update the chartdetails record because');
			$updresult = DB_query($sql,$db,$ErrMsg);
		}
	} /* end of for loop */
}

include('includes/footer.inc');
?>
