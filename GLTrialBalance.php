<?php

/* $Revision: 1.6 $ */

/*Through deviousness and cunning, this system allows trial balances for any date range that recalcuates the p & l balances
and shows the balance sheets as at the end of the period selected - so first off need to show the input of criteria screen
while the user is selecting the criteria the system is posting any unposted transactions */


$PageSecurity = 8;

include ('includes/session.inc');
$title = _('Trial Balance');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/AccountSectionsDef.inc'); //this reads in the Accounts Sections array


echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';

if ($_POST['FromPeriod'] > $_POST['ToPeriod']){
	echo '<P>' . _('The selected period from is actually after the period to! Please re-select the reporting period');
	$_POST['SelectADifferentPeriod']=_('Select A Different Period');
}

if ((! isset($_POST['FromPeriod']) AND ! isset($_POST['ToPeriod'])) OR $_POST['SelectADifferentPeriod']==_('Select A Different Period')){

	if (Date('m') > $_SESSION['YearEnd']){
		/*Dates in SQL format */
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')));
	} else {
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')-1));
	}

/*Show a form to allow input of criteria for TB to show */
	echo '<CENTER><TABLE><TR><TD>' . _('Select Period From:') . '</TD><TD><SELECT Name="FromPeriod">';

	$sql = 'SELECT periodno, lastdate_in_period FROM periods';
	$Periods = DB_query($sql,$db);


	while ($myrow=DB_fetch_array($Periods,$db)){
		if(isset($_POST['FromPeriod']) AND $_POST['FromPeriod']!=''){
			if( $_POST['FromPeriod']== $myrow['periodno']){
				echo '<OPTION SELECTED VALUE="' . $myrow['periodno'] . '">' .MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			} else {
				echo '<OPTION VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			}
		} else {
			if($myrow['lastdate_in_period']==$DefaultFromDate){
				echo '<OPTION SELECTED VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			} else {
				echo '<OPTION VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			}
		}
	}

	echo '</SELECT></TD></TR>';
	if (!isset($_POST['ToPeriod']) OR $_POST['ToPeriod']==''){
		$sql = 'SELECT Max(periodno) FROM periods';
		$MaxPrd = DB_query($sql,$db);
		$MaxPrdrow = DB_fetch_row($MaxPrd);

		$DefaultToPeriod = (int) ($MaxPrdrow[0]-1);
	} else {
		$DefaultToPeriod = $_POST['ToPeriod'];
	}

	echo '<TR><TD>' . _('Select Period To:') .'</TD><TD><SELECT Name="ToPeriod">';

	$RetResult = DB_data_seek($Periods,0);

	while ($myrow=DB_fetch_array($Periods,$db)){

		if($myrow['periodno']==$DefaultToPeriod){
			echo '<OPTION SELECTED VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
		} else {
			echo '<OPTION VALUE ="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
		}
	}
	echo '</SELECT></TD></TR></TABLE>';

	echo '<INPUT TYPE=SUBMIT Name="ShowTB" Value="' . _('Show Trial Balance') .'"></CENTER>';

/*Now do the posting while the user is thinking about the period to select */

	include ('includes/GLPostings.inc');

} else {

	echo '<INPUT TYPE=HIDDEN NAME="FromPeriod" VALUE="' . $_POST['FromPeriod'] . '"><INPUT TYPE=HIDDEN NAME="ToPeriod" VALUE="' . $_POST['ToPeriod'] . '">';

	$NumberOfMonths = $_POST['ToPeriod'] - $_POST['FromPeriod'] + 1;

	$sql = 'SELECT lastdate_in_period FROM periods WHERE periodno=' . $_POST['ToPeriod'];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$PeriodToDate = MonthAndYearFromSQLDate($myrow[0]);

	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];

	$SQL = 'SELECT accountgroups.groupname,
			accountgroups.pandl,
			chartdetails.accountcode ,
			chartmaster.accountname,
			Sum(CASE WHEN chartdetails.period=' . $_POST['FromPeriod'] . ' THEN chartdetails.bfwd ELSE 0 END) AS firstprdbfwd,
			Sum(CASE WHEN chartdetails.period=' . $_POST['FromPeriod'] . ' THEN chartdetails.bfwdbudget ELSE 0 END) AS firstprdbudgetbfwd,
			Sum(CASE WHEN chartdetails.period=' . $_POST['ToPeriod'] . ' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lastprdcfwd,
			Sum(CASE WHEN chartdetails.period=' . $_POST['ToPeriod'] . ' THEN chartdetails.actual ELSE 0 END) AS monthactual,
			Sum(CASE WHEN chartdetails.period=' . $_POST['ToPeriod'] . ' THEN chartdetails.budget ELSE 0 END) AS monthbudget,
			Sum(CASE WHEN chartdetails.period=' . $_POST['ToPeriod'] . ' THEN chartdetails.bfwdbudget + chartdetails.budget ELSE 0 END) AS lastprdbudgetcfwd
		FROM chartmaster INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
			INNER JOIN chartdetails ON chartmaster.accountcode= chartdetails.accountcode
		GROUP BY accountgroups.groupname,
				accountgroups.pandl,
				accountgroups.sequenceintb,
				chartdetails.accountcode,
				chartmaster.accountname
		ORDER BY accountgroups.pandl desc,
			accountgroups.sequenceintb,
			chartdetails.accountcode';

	$AccountsResult = DB_query($SQL,
				$db,
				 _('No general ledger accounts were returned by the SQL because'),
				 _('The SQL that failed was:'));

	echo '<CENTER><FONT SIZE=4 COLOR=BLUE><B>'. _('Trial Balance for the month of ') . $PeriodToDate . _(' and for the ') . $NumberOfMonths . _(' months to ') . $PeriodToDate .'</B></FONT><BR>';

	/*show a table of the accounts info returned by the SQL
	Account Code ,   Account Name , Month Actual, Month Budget, Period Actual, Period Budget */

	echo '<TABLE CELLPADDING=2>';
	$TableHeader = '<TR>
			<TD class="tableheader">' . _('Account') . '</TD>
			<TD class="tableheader">' . _('Account Name') . '</TD>
			<TD class="tableheader">' . _('Month Actual') . '</TD>
			<TD class="tableheader">' . _('Month Budget') . '</TD>
			<TD class="tableheader">' . _('Period Actual') . '</TD>
			<TD class="tableheader">' . _('Period Budget') .'</TD>
			</TR>';

	echo $TableHeader;
	$j = 1;
	$k=0; //row colour counter
	$ActGrp ='';

	$GrpActual =0;
	$GrpBudget =0;
	$GrpPrdActual =0;
	$GrpPrdBudget =0;

	while ($myrow=DB_fetch_array($AccountsResult)) {


		if ($myrow['groupname']!= $ActGrp){

			if ($GrpActual+$GrpBudget+$GrpPrdActual+$GrpPrdBudget !=0){
				echo '<TR>
					<TD COLSPAN=2></TD>
					<TD COLSPAN=4><HR></TD>
				</TR>';
				printf('<TR>
					<td COLSPAN=2><FONT SIZE=4>%s ' . _('Total') . '</FONT></td>
					<td ALIGN=RIGHT>%s</td>
					<td ALIGN=RIGHT>%s</td>
					<td ALIGN=RIGHT>%s</td>
					<td ALIGN=RIGHT>%s</td>
					</tr>',
					$ActGrp,
					number_format($GrpActual,2),
					number_format($GrpBudget,2),
					number_format($GrpPrdActual,2),
					number_format($GrpPrdBudget,2));
			}
			$GrpActual =0;
			$GrpBudget =0;
			$GrpPrdActual =0;
			$GrpPrdBudget =0;

			$ActGrp = $myrow['groupname'];
			printf('<TR>
				<td COLSPAN=6><FONT SIZE=4 COLOR=BLUE><B>%s</B></FONT></TD>
				</TR>',
				$myrow['groupname']);
			$j++;

		}
		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k++;
		}
		/*MonthActual, MonthBudget, FirstPrdBFwd, FirstPrdBudgetBFwd, LastPrdBudgetCFwd, LastPrdCFwd */


		if ($myrow['pandl']==1){

			$AccountPeriodActual = $myrow['lastprdcfwd'] - $myrow['firstprdbfwd'];
			$AccountPeriodBudget = $myrow['lastprdbudgetcfwd'] - $myrow['firstprdbudgetbfwd'];

			$PeriodProfitLoss += $AccountPeriodActual;
			$PeriodBudgetProfitLoss += $AccountPeriodBudget;
			$MonthProfitLoss += $myrow['monthactual'];
			$MonthBudgetProfitLoss += $myrow['budget'];
			$BFwdProfitLoss += $myrow['firstprdbfwd'];
		} else { /*PandL ==0 its a balance sheet account */
			if ($myrow['accountcode']==$RetainedEarningsAct){
				$AccountPeriodActual = $BFwdProfitLoss + $myrow['lastprdcfwd'];
				$AccountPeriodBudget = $BFwdProfitLoss + $myrow['lastprdbudgetcfwd'] - $myrow['firstprdbudgetbfwd'];
			} else {
				$AccountPeriodActual = $myrow['lastprdcfwd'];
				$AccountPeriodBudget = $myrow['firstprdbfwd'] + $myrow['lastprdbudgetcfwd'] - $myrow['firstprdbudgetbfwd'];
			}

		}


		$GrpActual +=$myrow['monthactual'];
		$GrpBudget +=$myrow['monthbudget'];
		$GrpPrdActual +=$AccountPeriodActual;
		$GrpPrdBudget +=$AccountPeriodBudget;

		$CheckMonth += $myrow['monthactual'];
		$CheckBudgetMonth += $myrow['monthbudget'];
		$CheckPeriodActual += $AccountPeriodActual;
		$CheckPeriodBudget += $AccountPeriodBudget;

		$ActEnquiryURL = '<A HREF="'. $rootpath . '/GLAccountInquiry.php?' . SID . 'Period=' . $_POST['ToPeriod'] . '&Account=' . $myrow['accountcode'] . '&Show=Yes">' . $myrow['accountcode'] . '<A>';

		printf('<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			</tr>',
			$ActEnquiryURL,
			$myrow['accountname'],
			number_format($myrow['monthactual'],2),
			number_format($myrow['monthbudget'],2),
			number_format($AccountPeriodActual,2),
			number_format($AccountPeriodBudget,2));

		$j++;
		If ($j == 18){
			$j=1;
			echo $TableHeader;
		}
	}
	//end of while loop
	printf('<tr bgcolor="#ffffff">
			<td COLSPAN=2><FONT COLOR=BLUE><B>' . _('Check Totals') . '</B></FONT></td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
		</tr>',
		number_format($CheckMonth,2),
		number_format($CheckBudgetMonth,2),
		number_format($CheckPeriodActual,2),
		number_format($CheckPeriodBudget,2));

	echo '</TABLE>';
	echo '<INPUT TYPE=SUBMIT Name="SelectADifferentPeriod" Value="' . _('Select A Different Period') . '"></CENTER>';
}
echo '</FORM>';
include('includes/footer.inc');

?>