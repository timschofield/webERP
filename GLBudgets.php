<?php

$PageSecurity = 10;

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

$title = _('Create GL Budgets');

include('includes/header.inc');

if (isset($_POST['SelectedAccount'])){
	$SelectedAccount = $_POST['SelectedAccount'];
} elseif (isset($_GET['SelectedAccount'])){
	$SelectedAccount = $_GET['SelectedAccount'];
}

//If an account hasn't been selected then select one here.
echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . ' METHOD=POST>';
echo '<CENTER><TABLE>';

echo '</BR><TR><TD>'.  _('Select GL Account').  ":</TD><TD><SELECT name='SelectedAccount'>";

$SQL = 'SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode';
$result=DB_query($SQL,$db);
if (DB_num_rows($result)==0){
	echo '</SELECT></TD></TR>';
	prnMsg(_('No General ledger accounts have been set up yet') . ' - ' . _('budgets cannot be allocated until the GL accounts are set up'),'warn');
} else {
	while ($myrow=DB_fetch_array($result)){
		$account = $myrow['accountcode'] . ' - ' . $myrow['accountname'];
		if ($SelectedAccount==$myrow['accountcode']){
			echo '<OPTION SELECTED value=' . $myrow['accountcode'] . '>' . $account;
		} else {
			echo '<OPTION value=' . $myrow['accountcode'] . '>' . $account;
		}
	}
	echo '</SELECT></TD></TR>';
}
echo '</TABLE></BR>';
echo "<INPUT TYPE=SUBMIT name=Select value='" . _('Select Account') . "'></CENTER></FORM>";

// End of account selection

if ($SelectedAccount != '') {	

	$CurrentYearEndPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat'],YearEndDate($_SESSION['YearEnd'],0)),$db);
	
// If the update button has been hit, then update chartdetails with the budget figures
// for this year and next.	
	if (isset($_POST['update'])) {
		$ErrMsg = _('Cannot update GL budgets');
		$DbgMsg = _('The SQL that failed to update the GL budgets was');
		for ($i=1; $i<=12; $i++) {
			$SQL='UPDATE chartdetails SET budget='.Round($_POST[$i."this"],2)." WHERE period=".
			  ($CurrentYearEndPeriod-(12-$i)) ." AND  accountcode = '" . $SelectedAccount."'";
			$result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);
			$SQL='UPDATE chartdetails SET budget='.Round($_POST[$i."next"],2)." WHERE period=".
			  ($CurrentYearEndPeriod+$i) ." AND  accountcode = '" . $SelectedAccount."'";
			$result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);
		}
	}
// End of update
	
	$YearEndYear=Date("Y", YearEndDate($_SESSION['YearEnd'],0));

// If the periods dont exist then create them - There must be a better way of doing this
	for ($i=1; $i <=36; $i++) {
		$MonthEnd=mktime(0,0,0,$_SESSION['YearEnd']+1+$i,0,$YearEndYear-2);
		$period=GetPeriod(Date($_SESSION['DefaultDateFormat'],$MonthEnd),$db);
		$PeriodEnd[$period]=Date("M Y",$MonthEnd);
	}
// End of create periods
	
	$SQL='SELECT period, budget, actual FROM chartdetails WHERE accountcode='.$SelectedAccount;
	$result=DB_query($SQL,$db);
	while ($myrow=DB_fetch_array($result)){
		$budget[$myrow['period']]=$myrow['budget'];
		$actual[$myrow['period']]=$myrow['actual'];	
	}

	$LastYearActual=0;
	$LastYearBudget=0;
	$ThisYearActual=0;
	$ThisYearBudget=0;
	$NextYearActual=0;
	$NextYearBudget=0;

// Table Headers

	echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . ' METHOD=POST>';
	echo '<CENTER><TABLE>';
	echo '<TR><TD class="tableheader" COLSPAN=3>'. _('Last Financial Year') .'</TD>';
	echo '<TD class="tableheader" COLSPAN=3>'. _('This Financial Year') .'</TD>';
	echo '<TD class="tableheader" COLSPAN=3>'. _('Next Financial Year') .'</TD></TR>';

	echo '<TR><TD class="tableheader" COLSPAN=3>'. _('Year ended').' - '.
	 	Date($_SESSION['DefaultDateFormat'],YearEndDate($_SESSION['YearEnd'], -1)) .'</TD>';
	echo '<TD class="tableheader" COLSPAN=3>'. _('Year ended').' - '.
	 	Date($_SESSION['DefaultDateFormat'],YearEndDate($_SESSION['YearEnd'],0)) .'</TD>';
	echo '<TD class="tableheader" COLSPAN=3>'. _('Year ended').' - '.
	 	Date($_SESSION['DefaultDateFormat'],YearEndDate($_SESSION['YearEnd'],1)) .'</TD></TR>';

	echo '<TR>';
	for ($i=0; $i<3; $i++) {
		echo '<TD class="tableheader">'. _('Period'). '</TD><TD class="tableheader">'. _('Actual') .
			 '</TD><TD class="tableheader">'. _('Budget') . '</TD>';
	}
	echo '</TR>';
	

// Main Table

	for ($i=1; $i<=12; $i++) {
		echo '<TR>';
		echo '<TD BGCOLOR="a9d3e7">'. $PeriodEnd[$CurrentYearEndPeriod-(24-$i)] .'</TD>';
		echo '<TD BGCOLOR="d2e5e8" ALIGN="RIGHT">'.number_format($actual[$CurrentYearEndPeriod-(24-$i)],2).'</TD>';
		echo '<TD BGCOLOR="d2e5e8" ALIGN="RIGHT">'.number_format($budget[$CurrentYearEndPeriod-(24-$i)],2).'</TD>';
		echo '<TD BGCOLOR="a9d3e7">'. $PeriodEnd[$CurrentYearEndPeriod-(12-$i)] .'</TD>';
		echo '<TD BGCOLOR="d2e5e8" ALIGN="RIGHT">'.number_format($actual[$CurrentYearEndPeriod-(12-$i)],2).'</TD>';
		echo '<TD><INPUT TYPE="TEXT" SIZE=8 NAME='.$i.'this'.' VALUE="'.number_format($budget[$CurrentYearEndPeriod-(12-$i)],2).'"></TD>';
		echo '<TD BGCOLOR="a9d3e7">'. $PeriodEnd[$CurrentYearEndPeriod+($i)] .'</TD>';
		echo '<TD BGCOLOR="d2e5e8" ALIGN="RIGHT">'.number_format($actual[$CurrentYearEndPeriod+($i)],2).'</TD>';
		echo '<TD><INPUT TYPE="TEXT" SIZE=8 NAME='.$i.'next'.' VALUE='.number_format($budget[$CurrentYearEndPeriod+($i)],2).'></TD>';
		echo '</TR>';
		$LastYearActual=$LastYearActual+$actual[$CurrentYearEndPeriod-(24-$i)];
		$LastYearBudget=$LastYearBudget+$budget[$CurrentYearEndPeriod-(24-$i)];
		$ThisYearActual=$ThisYearActual+$actual[$CurrentYearEndPeriod-(12-$i)];
		$ThisYearBudget=$ThisYearBudget+$budget[$CurrentYearEndPeriod-(12-$i)];
		$NextYearActual=$NextYearActual+$actual[$CurrentYearEndPeriod+($i)];
		$NextYearBudget=$NextYearBudget+$budget[$CurrentYearEndPeriod+($i)];
	}
	
// Total Line	
	
	echo '<TR><TD class="tableheader">'. _('Total') .'</TD>';
	echo '<TD class="tableheader" ALIGN="RIGHT">'.number_format($LastYearActual,2). '</TD>';
	echo '<TD class="tableheader" ALIGN="RIGHT">'.number_format($LastYearBudget,2). '</TD>';
	echo '<TD class="tableheader" ALIGN="RIGHT"></TD>';
	echo '<TD class="tableheader" ALIGN="RIGHT">'.number_format($ThisYearActual,2). '</TD>';
	echo '<TD class="tableheader" ALIGN="RIGHT"><INPUT TYPE="TEXT" SIZE=8 VALUE='.number_format($ThisYearBudget,2). '></TD>';
	echo '<TD class="tableheader" ALIGN="RIGHT"></TD>';
	echo '<TD class="tableheader" ALIGN="RIGHT">'.number_format($NextYearActual,2). '</TD>';
	echo '<TD class="tableheader" ALIGN="RIGHT"><INPUT TYPE="TEXT" SIZE=8 VALUE='.number_format($NextYearBudget,2). '></TD></TR>';
	echo '</TABLE>';
	echo '<INPUT TYPE=HIDDEN NAME="SelectedAccount" VALUE='.$SelectedAccount.'>';
	echo '</BR><INPUT TYPE=SUBMIT name=update value="' . _('Update') . '"></CENTER></FORM>';

	$SQL="SELECT MIN(periodno) FROM periods";
	$result=DB_query($SQL,$db);
	$MyRow=DB_fetch_array($result);
	$FirstPeriod=$MyRow[0];

	$SQL="SELECT MAX(periodno) FROM periods";
	$result=DB_query($SQL,$db);
	$MyRow=DB_fetch_array($result);
	$LastPeriod=$MyRow[0];

	for ($i=$FirstPeriod;$i<=$LastPeriod;$i++) {
		$sql='SELECT accountcode, period, budget, actual, bfwd, bfwdbudget FROM chartdetails WHERE period ='. $i . ' AND  accountcode = ' . $SelectedAccount;

		$ErrMsg = _('Could not retrieve the ChartDetail records becaue');
		$result = DB_query($sql,$db,$ErrMsg);

		while ($myrow=DB_fetch_array($result)){

			$CFwdBudget = $myrow['bfwdbudget'] + $myrow['budget'];
			$sql = 'UPDATE chartdetails SET bfwdbudget=' . $CFwdBudget . ' WHERE period=' . ($myrow['period'] +1) . ' AND  accountcode = ' . $SelectedAccount;

			$ErrMsg =_('Could not update the chartdetails record because');
			$updresult = DB_query($sql,$db,$ErrMsg);
		}
	} /* end of for loop */
}

include('includes/footer.inc');

?>