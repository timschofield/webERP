<?php
/* $Revision: 1.7 $ */


$PageSecurity=15;

include ('includes/session.inc');
$title = _('Recalculation of GL Balances in Chart Details Table');
include('includes/header.inc');
include ('includes/DateFunctions.inc');


echo "<FORM METHOD='POST' ACTION=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';


if (!isset($_POST['FromPeriod'])){

/*Show a form to allow input of criteria for TB to show */
	echo '<CENTER><TABLE>
                             <TR>
                                 <TD>' . _('Select Period From') . ":</TD>
                                 <TD><SELECT Name='FromPeriod'>";

	$sql = 'SELECT periodno,
                       lastdate_in_period
                FROM periods ORDER BY periodno';
	$Periods = DB_query($sql,$db);


	while ($myrow=DB_fetch_array($Periods,$db)){
		echo '<OPTION VALUE=' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
	}

	echo '</SELECT></TD>
             </TR>
             </TABLE>';

	echo "<INPUT TYPE=SUBMIT Name='recalc' Value='" . _('Do the Recalculation') . "'></CENTER></FORM>";

} else {  /*OK do the updates */

	/* Make the posted flag on all GL entries including and after the period selected = 0 */
	$sql = 'UPDATE gltrans SET posted=0 WHERE periodno >='. $_POST['FromPeriod'];
	$UpdGLTransPostedFlag = DB_query($sql,$db);

	/* Now make all the actuals 0 for all periods including and after the period from */
	$sql = 'UPDATE chartdetails SET actual =0 WHERE period >= ' . $_POST['FromPeriod'];
	$UpdActualChartDetails = DB_query($sql,$db);

	/*Now repost the lot */

	include('includes/GLPostings.inc');

	/*Make a note of all the subsequent periods to recalculate the B/Fwd balances for */

	$sql = 'SELECT periodno FROM periods WHERE periodno >= '. $_POST['FromPeriod'] . ' ORDER BY periodno';
	$Periods = DB_query($sql,$db);

	while ($PeriodRow=DB_fetch_row($Periods)){

		$sql='SELECT accountcode,
                             period,
                             budget,
                             actual,
                             bfwd,
                             bfwdbudget
                        FROM chartdetails
                        WHERE period ='. $PeriodRow[0];

		$ErrMsg = _('Now hang on we have a problem here because');
		$result = DB_query($sql,$db,$ErrMsg);

		while ($myrow=DB_fetch_array($result)){

			$CFwd = $myrow['bfwd'] + $myrow['actual'];
			$CFwdBudget = $myrow['bfwdbudget'] + $myrow['budget'];
			echo '<BR>' . _('Account Code') . ' : ' . $myrow['accountcode'] . ' ' . _('Period') . ' : ' . $myrow['period'];

			$sql = 'UPDATE chartdetails SET bfwd=' . $CFwd . ',
							bfwdbudget=' . $CFwdBudget . ' 
					WHERE period=' . ($myrow['period'] +1) . ' 
					AND  accountcode = ' . $myrow['accountcode'];

			$updresult = DB_query($sql,$db,$ErrMsg);
		}
	} /* end of for loop */
}

include('includes/footer.inc');
?>
