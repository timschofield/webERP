<?php

/* $Revision: 1.4 $ */

/*Through deviousness and cunning, this system allows trial balances for any date range that recalcuates the p & l balances
and shows the balance sheets as at the end of the period selected - so first off need to show the input of criteria screen
while the user is selecting the criteria the system is posting any unposted transactions */


$PageSecurity = 8;

include ("includes/session.inc");
$title = _('Trial Balance');
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");


echo '<FORM METHOD="POST" ACTION="' . $_SERVER["PHP_SELF"] . '?' . SID . '">';

if ($_POST["FromPeriod"] > $_POST["ToPeriod"]){
	echo '<P>' . _('The selected period from is actually after the period to! Please re-select the reporting period');
	$_POST["SelectADifferentPeriod"]=_('Select A Different Period');
}

if ((! isset($_POST["FromPeriod"]) AND ! isset($_POST["ToPeriod"])) OR $_POST["SelectADifferentPeriod"]==_('Select A Different Period')){

	if (Date("m") > $YearEnd){
		/*Dates in SQL format */
		$DefaultFromDate = Date ("Y-m-d", Mktime(0,0,0,$YearEnd + 2,0,Date("Y")));
	} else {
		$DefaultFromDate = Date ("Y-m-d", Mktime(0,0,0,$YearEnd + 2,0,Date("Y")-1));
	}

/*Show a form to allow input of criteria for TB to show */
	echo '<CENTER><TABLE><TR><TD>' . _('Select Period From:') . '</TD><TD><SELECT Name="FromPeriod">';

	$sql = 'SELECT PeriodNo, LastDate_In_Period FROM Periods';
	$Periods = DB_query($sql,$db);


	while ($myrow=DB_fetch_array($Periods,$db)){
		if(isset($_POST['FromPeriod']) AND $_POST['FromPeriod']!=''){
			if( $_POST['FromPeriod']== $myrow['PeriodNo']){
				echo '<OPTION SELECTED VALUE="' . $myrow['PeriodNo'] . '">' .MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);
			} else {
				echo '<OPTION VALUE="' . $myrow['PeriodNo'] . '">' . MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);
			}
		} else {
			if($myrow['LastDate_In_Period']==$DefaultFromDate){
				echo '<OPTION SELECTED VALUE="' . $myrow['PeriodNo'] . '">' . MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);
			} else {
				echo '<OPTION VALUE="' . $myrow['PeriodNo'] . '">' . MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);
			}
		}
	}

	echo '</SELECT></TD></TR>';
	if (!isset($_POST['ToPeriod']) OR $_POST['ToPeriod']==''){
		$sql = 'SELECT Max(PeriodNo) FROM Periods';
		$MaxPrd = DB_query($sql,$db);
		$MaxPrdrow = DB_fetch_row($MaxPrd);

		$DefaultToPeriod = (int) ($MaxPrdrow[0]-1);
	} else {
		$DefaultToPeriod = $_POST['ToPeriod'];
	}

	echo '<TR><TD>' . _('Select Period To:') .'</TD><TD><SELECT Name="ToPeriod">';

	$RetResult = DB_data_seek($Periods,0);

	while ($myrow=DB_fetch_array($Periods,$db)){

		if($myrow['PeriodNo']==$DefaultToPeriod){
			echo '<OPTION SELECTED VALUE="' . $myrow['PeriodNo'] . '">' . MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);
		} else {
			echo '<OPTION VALUE ="' . $myrow['PeriodNo'] . '">' . MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);
		}
	}
	echo '</SELECT></TD></TR></TABLE>';

	echo '<INPUT TYPE=SUBMIT Name="ShowTB" Value="' . _('Show Trial Balance') .'"></CENTER>';

/*Now do the posting while the user is thinking about the period to select */

	include ('includes/GLPostings.inc');

} else {

	echo '<INPUT TYPE=HIDDEN NAME="FromPeriod" VALUE="' . $_POST['FromPeriod'] . '"><INPUT TYPE=HIDDEN NAME="ToPeriod" VALUE="' . $_POST['ToPeriod'] . '">';

	$NumberOfMonths = $_POST['ToPeriod'] - $_POST['FromPeriod'] + 1;

	$sql = 'SELECT LastDate_in_Period FROM Periods WHERE PeriodNo=' . $_POST['ToPeriod'];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$PeriodToDate = MonthAndYearFromSQLDate($myrow[0]);

	$CompanyRecord = ReadInCompanyRecord($db);
	$RetainedEarningsAct = $CompanyRecord['RetainedEarnings'];

	$SQL = 'SELECT AccountGroups.GroupName,
			AccountGroups.PandL,
			ChartDetails.AccountCode ,
			ChartMaster.AccountName,
			Sum(CASE WHEN ChartDetails.Period=' . $_POST['FromPeriod'] . ' THEN ChartDetails.BFwd ELSE 0 END) AS FirstPrdBFwd,
			Sum(CASE WHEN ChartDetails.Period=' . $_POST['FromPeriod'] . ' THEN ChartDetails.BFwdBudget ELSE 0 END) AS FirstPrdBudgetBFwd,
			Sum(CASE WHEN ChartDetails.Period=' . $_POST['ToPeriod'] . ' THEN ChartDetails.BFwd + ChartDetails.Actual ELSE 0 END) AS LastPrdCFwd,
			Sum(CASE WHEN ChartDetails.Period=' . $_POST['ToPeriod'] . ' THEN ChartDetails.Actual ELSE 0 END) AS MonthActual,
			Sum(CASE WHEN ChartDetails.Period=' . $_POST['ToPeriod'] . ' THEN ChartDetails.Budget ELSE 0 END) AS MonthBudget,
			Sum(CASE WHEN ChartDetails.Period=' . $_POST['ToPeriod'] . ' THEN ChartDetails.BFwdBudget + ChartDetails.Budget ELSE 0 END) AS LastPrdBudgetCFwd
		FROM ChartMaster INNER JOIN AccountGroups ON ChartMaster.Group_ = AccountGroups.GroupName
			INNER JOIN ChartDetails ON ChartMaster.AccountCode= ChartDetails.AccountCode
		GROUP BY AccountGroups.GroupName,
				AccountGroups.PandL,
				ChartDetails.AccountCode,
				ChartMaster.AccountName
		ORDER BY AccountGroups.PandL DESC,
			AccountGroups.SequenceInTB,
			ChartDetails.AccountCode';

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


		if ($myrow['GroupName']!= $ActGrp){

			if ($GrpActual+$GrpBudget+$GrpPrdActual+$GrpPrdBudget !=0){
				echo '<TR>
					<TD COLSPAN=2></TD>
					<TD COLSPAN=4><HR></TD>
				</TR>';
				printf('<TR>
					<td COLSPAN=2><FONT SIZE=4>%s Total</FONT></td>
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

			$ActGrp = $myrow['GroupName'];
			printf('<TR>
				<td COLSPAN=6><FONT SIZE=4 COLOR=BLUE><B>%s</B></FONT></TD>
				</TR>',
				$myrow['GroupName']);
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


		if ($myrow['PandL']==1){

			$AccountPeriodActual = $myrow['LastPrdCFwd'] - $myrow['FirstPrdBFwd'];
			$AccountPeriodBudget = $myrow['LastPrdBudgetCFwd'] - $myrow['FirstPrdBudgetBFwd'];

			$PeriodProfitLoss += $AccountPeriodActual;
			$PeriodBudgetProfitLoss += $AccountPeriodBudget;
			$MonthProfitLoss += $myrow['MonthActual'];
			$MonthBudgetProfitLoss += $myrow['Budget'];
			$BFwdProfitLoss += $myrow['FirstPrdBFwd'];
		} else { /*PandL ==0 its a balance sheet account */
			if ($myrow['AccountCode']==$RetainedEarningsAct){
				$AccountPeriodActual = $BFwdProfitLoss + $myrow['LastPrdCFwd'];
				$AccountPeriodBudget = $BFwdProfitLoss + $myrow['LastPrdBudgetCFwd'] - $myrow['FirstPrdBudgetBFwd'];
			} else {
				$AccountPeriodActual = $myrow['LastPrdCFwd'];
				$AccountPeriodBudget = $myrow['FirstPrdBFwd'] + $myrow['LastPrdBudgetCFwd'] - $myrow['FirstPrdBudgetBFwd'];
			}

		}


		$GrpActual +=$myrow['MonthActual'];
		$GrpBudget +=$myrow['MonthBudget'];
		$GrpPrdActual +=$AccountPeriodActual;
		$GrpPrdBudget +=$AccountPeriodBudget;

		$CheckMonth += $myrow['MonthActual'];
		$CheckBudgetMonth += $myrow['MonthBudget'];
		$CheckPeriodActual += $AccountPeriodActual;
		$CheckPeriodBudget += $AccountPeriodBudget;

		$ActEnquiryURL = '<A HREF="'. $rootpath . '/GLAccountInquiry.php?' . SID . 'Period=' . $_POST['ToPeriod'] . '&Account=' . $myrow['AccountCode'] . '&Show=Yes">' . $myrow['AccountCode'] . '<A>';

		printf('<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			</tr>',
			$ActEnquiryURL,
			$myrow['AccountName'],
			number_format($myrow['MonthActual'],2),
			number_format($myrow['MonthBudget'],2),
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
echo '</form>';
include('includes/footer.inc');

?>