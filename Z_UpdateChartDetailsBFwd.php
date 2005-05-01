<?php

/* $Revision: 1.9 $ */

$PageSecurity=15;
include ('includes/session.inc');
$title = _('Recalculation of Brought Forward Balances in Chart Details Table');
include('includes/header.inc');


echo "<FORM METHOD='POST' ACTION=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

if ($_POST['FromPeriod'] > $_POST['ToPeriod']){
	prnMsg(_('The selected period from is actually after the period to') . '. ' . _('Please re-select the reporting period'),'error');
	unset ($_POST['FromPeriod']);
	unset ($_POST['ToPeriod']);

}

if (!isset($_POST['FromPeriod']) OR !isset($_POST['ToPeriod'])){


/*Show a form to allow input of criteria for TB to show */
	echo '<CENTER><TABLE><TR><TD>' . _('Select Period From') . ":</TD><TD><SELECT Name='FromPeriod'>";

	$sql = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno';
	$Periods = DB_query($sql,$db);


	while ($myrow=DB_fetch_array($Periods,$db)){

		echo '<OPTION VALUE=' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);

	}

	echo '</SELECT></TD></TR>';

	$sql = 'SELECT MAX(periodno) FROM periods';
	$MaxPrd = DB_query($sql,$db);
	$MaxPrdrow = DB_fetch_row($MaxPrd);

	$DefaultToPeriod = (int) ($MaxPrdrow[0]-1);

	echo '<TR><TD>' . _('Select Period To') . ":</TD><TD><SELECT Name='ToPeriod'>";

	$RetResult = DB_data_seek($Periods,0);

	while ($myrow=DB_fetch_array($Periods,$db)){

		if($myrow['periodno']==$DefaultToPeriod){
			echo '<OPTION SELECTED VALUE=' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
		} else {
			echo '<OPTION VALUE =' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
		}
	}
	echo '</SELECT></TD></TR></TABLE>';

	echo "<INPUT TYPE=SUBMIT Name='recalc' Value='" . _('Do the Recalculation') . "'></CENTER></FORM>";

} else {  /*OK do the updates */

	for ($i=$_POST['FromPeriod'];$i<=$_POST['ToPeriod'];$i++){

		$sql='SELECT accountcode, period, budget, actual, bfwd, bfwdbudget FROM chartdetails WHERE period ='. $i;

		$ErrMsg = _('Could not retrieve the ChartDetail records becaue');
		$result = DB_query($sql,$db,$ErrMsg);

		while ($myrow=DB_fetch_array($result)){

			$CFwd = $myrow['bfwd'] + $myrow['actual'];
			$CFwdBudget = $myrow['bfwdbudget'] + $myrow['budget'];

			echo '<BR>' . _('Account Code') . ': ' . $myrow['accountcode'] . ' ' . _('Period') .': ' . $myrow['period'];

			$sql = 'UPDATE chartdetails SET bfwd=' . $CFwd . ', bfwdbudget=' . $CFwdBudget . ' WHERE period=' . ($myrow['period'] +1) . ' AND  accountcode = ' . $myrow['accountcode'];

			$ErrMsg =_('Could not update the chartdetails record because');
			$updresult = DB_query($sql,$db,$ErrMsg);
		}
	} /* end of for loop */
}

include('includes/footer.inc');
?>
