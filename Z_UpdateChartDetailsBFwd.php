<?php


include ('includes/session.php');
$Title = _('Recalculation of Brought Forward Balances in Chart Details Table');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php'); ;
include('includes/header.php');

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if ($_POST['FromPeriod'] > $_POST['ToPeriod']){
	prnMsg(_('The selected period from is actually after the period to') . '. ' . _('Please re-select the reporting period'),'error');
	unset ($_POST['FromPeriod']);
	unset ($_POST['ToPeriod']);

}

if (!isset($_POST['FromPeriod']) OR !isset($_POST['ToPeriod'])){


/*Show a form to allow input of criteria for TB to show */
	echo '<table><tr><td>' . _('Select Period From') . ':</td><td><select name="FromPeriod">';

	$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno";
	$Periods = DB_query($SQL);


	while ($MyRow=DB_fetch_array($Periods)){
		echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
	}

	echo '</select></td></tr>';

	$SQL = "SELECT MAX(periodno) FROM periods";
	$MaxPrd = DB_query($SQL);
	$MaxPrdrow = DB_fetch_row($MaxPrd);

	$DefaultToPeriod = (int) ($MaxPrdrow[0]-1);

	echo '<tr><td>' . _('Select Period To') . ':</td><td><select name="ToPeriod">';

	$RetResult = DB_data_seek($Periods,0);

	while ($MyRow=DB_fetch_array($Periods)){

		if($MyRow['periodno']==$DefaultToPeriod){
			echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
	}
	echo '</select></td></tr></table>';

	echo '<div class="centre"><input type="submit" name="recalc" value="' . _('Do the Recalculation') . '" /></div>
        </div>
        </form>';

} else {  /*OK do the updates */

	for ($i=$_POST['FromPeriod'];$i<=$_POST['ToPeriod'];$i++){

		$SQL="SELECT accountcode,
					period,
					budget,
					actual,
					bfwd,
					bfwdbudget
				FROM chartdetails
				WHERE period ='" . $i . "'";

		$ErrMsg = _('Could not retrieve the ChartDetail records because');
		$Result = DB_query($SQL,$ErrMsg);

		while ($MyRow=DB_fetch_array($Result)){

			$CFwd = $MyRow['bfwd'] + $MyRow['actual'];
			$CFwdBudget = $MyRow['bfwdbudget'] + $MyRow['budget'];

			echo '<br />' . _('Account Code') . ': ' . $MyRow['accountcode'] . ' ' . _('Period') .': ' . $MyRow['period'];

			$SQL = "UPDATE chartdetails SET bfwd='" . $CFwd . "',
										bfwdbudget='" . $CFwdBudget . "'
					WHERE period='" . ($MyRow['period'] +1) . "'
					AND  accountcode = '" . $MyRow['accountcode'] . "'";

			$ErrMsg =_('Could not update the chartdetails record because');
			$UpdResult = DB_query($SQL,$ErrMsg);
		}
	} /* end of for loop */
}

include('includes/footer.php');
?>