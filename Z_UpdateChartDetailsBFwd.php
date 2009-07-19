<?php

/* $Revision: 1.11 $ */

$PageSecurity=15;
include ('includes/session.inc');
$title = _('Recalculation of Brought Forward Balances in Chart Details Table');
include('includes/header.inc');


echo "<form method='POST' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

if ($_POST['FromPeriod'] > $_POST['ToPeriod']){
	prnMsg(_('The selected period from is actually after the period to') . '. ' . _('Please re-select the reporting period'),'error');
	unset ($_POST['FromPeriod']);
	unset ($_POST['ToPeriod']);

}

if (!isset($_POST['FromPeriod']) OR !isset($_POST['ToPeriod'])){


/*Show a form to allow input of criteria for TB to show */
	echo '<table><tr><td>' . _('Select Period From') . ":</td><td><select Name='FromPeriod'>";

	$sql = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno';
	$Periods = DB_query($sql,$db);


	while ($myrow=DB_fetch_array($Periods,$db)){

		echo '<option VALUE=' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);

	}

	echo '</select></td></tr>';

	$sql = 'SELECT MAX(periodno) FROM periods';
	$MaxPrd = DB_query($sql,$db);
	$MaxPrdrow = DB_fetch_row($MaxPrd);

	$DefaultToPeriod = (int) ($MaxPrdrow[0]-1);

	echo '<tr><td>' . _('Select Period To') . ":</td><td><select Name='ToPeriod'>";

	$RetResult = DB_data_seek($Periods,0);

	while ($myrow=DB_fetch_array($Periods,$db)){

		if($myrow['periodno']==$DefaultToPeriod){
			echo '<option selected VALUE=' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
		} else {
			echo '<option VALUE =' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
		}
	}
	echo '</select></td></tr></table>';

	echo "<div class='centre'><input type=submit Name='recalc' Value='" . _('Do the Recalculation') . "'></div></form>";

} else {  /*OK do the updates */

	for ($i=$_POST['FromPeriod'];$i<=$_POST['ToPeriod'];$i++){

		$sql='SELECT accountcode, period, budget, actual, bfwd, bfwdbudget FROM chartdetails WHERE period ='. $i;

		$ErrMsg = _('Could not retrieve the ChartDetail records because');
		$result = DB_query($sql,$db,$ErrMsg);

		while ($myrow=DB_fetch_array($result)){

			$CFwd = $myrow['bfwd'] + $myrow['actual'];
			$CFwdBudget = $myrow['bfwdbudget'] + $myrow['budget'];

			echo '<br>' . _('Account Code') . ': ' . $myrow['accountcode'] . ' ' . _('Period') .': ' . $myrow['period'];

			$sql = 'UPDATE chartdetails SET bfwd=' . $CFwd . ', bfwdbudget=' . $CFwdBudget . ' WHERE period=' . ($myrow['period'] +1) . ' AND  accountcode = ' . $myrow['accountcode'];

			$ErrMsg =_('Could not update the chartdetails record because');
			$updresult = DB_query($sql,$db,$ErrMsg);
		}
	} /* end of for loop */
}

include('includes/footer.inc');
?>
