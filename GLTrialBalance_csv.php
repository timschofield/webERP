<?php


/*Through deviousness and cunning, this system allows trial balances for any date range that recalcuates the p & l balances
and shows the balance sheets as at the end of the period selected - so first off need to show the input of criteria screen
while the user is selecting the criteria the system is posting any unposted transactions */

/*Needs to have PeriodFrom and PeriodTo sent with URL
 * also need to work on authentication with username and password sent too*/


//$AllowAnyone = true;

//Page must be called with GLTrialBalance_csv.php?CompanyName=XXXXX&PeriodFrom=Y&PeriodTo=Z
$_POST['CompanyNameField'] = $_GET['CompanyName'];
$_SESSION['DatabaseName'] =  $_GET['CompanyName'];
//htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') = dirname(htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')) .'/GLTrialBalance_csv.php?PeriodTo=' . $_GET['PeriodTo'] . '&PeriodFrom=' . $_GET['PeriodFrom'];

include ('includes/session.php');
include('includes/SQL_CommonFunctions.inc');

include ('includes/GLPostings.inc'); //do any outstanding posting

$NumberOfMonths = $_GET['PeriodTo'] - $_GET['PeriodFrom'] + 1;

$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];

$SQL = "SELECT accountgroups.groupname,
			accountgroups.parentgroupname,
			accountgroups.pandl,
			chartdetails.accountcode ,
			chartmaster.accountname,
			Sum(CASE WHEN chartdetails.period='" . $_GET['PeriodFrom'] . "' THEN chartdetails.bfwd ELSE 0 END) AS firstprdbfwd,
			Sum(CASE WHEN chartdetails.period='" . $_GET['PeriodFrom'] . "' THEN chartdetails.bfwdbudget ELSE 0 END) AS firstprdbudgetbfwd,
			Sum(CASE WHEN chartdetails.period='" . $_GET['PeriodTo'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lastprdcfwd,
			Sum(CASE WHEN chartdetails.period='" . $_GET['PeriodTo'] . "' THEN chartdetails.actual ELSE 0 END) AS monthactual,
			Sum(CASE WHEN chartdetails.period='" . $_GET['PeriodTo'] . "' THEN chartdetails.budget ELSE 0 END) AS monthbudget,
			Sum(CASE WHEN chartdetails.period='" . $_GET['PeriodTo'] . "' THEN chartdetails.bfwdbudget + chartdetails.budget ELSE 0 END) AS lastprdbudgetcfwd
		FROM chartmaster INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
			INNER JOIN chartdetails ON chartmaster.accountcode= chartdetails.accountcode
		GROUP BY accountgroups.groupname,
				accountgroups.parentgroupname,
				accountgroups.pandl,
				accountgroups.sequenceintb,
				chartdetails.accountcode,
				chartmaster.accountname
		ORDER BY accountgroups.pandl desc,
			accountgroups.sequenceintb,
			accountgroups.groupname,
			chartdetails.accountcode";

$AccountsResult = DB_query($SQL);

while ($MyRow=DB_fetch_array($AccountsResult)) {

	if ($MyRow['pandl']==1) {
			$AccountPeriodActual = $MyRow['lastprdcfwd'] - $MyRow['firstprdbfwd'];
			$AccountPeriodBudget = $MyRow['lastprdbudgetcfwd'] - $MyRow['firstprdbudgetbfwd'];
			$PeriodProfitLoss += $AccountPeriodActual;
			$PeriodBudgetProfitLoss += $AccountPeriodBudget;
			$MonthProfitLoss += $MyRow['monthactual'];
			$MonthBudgetProfitLoss += $MyRow['monthbudget'];
			$BFwdProfitLoss += $MyRow['firstprdbfwd'];
	} else { /*PandL ==0 its a balance sheet account */
			if ($MyRow['accountcode']==$RetainedEarningsAct) {
				$AccountPeriodActual = $BFwdProfitLoss + $MyRow['lastprdcfwd'];
				$AccountPeriodBudget = $BFwdProfitLoss + $MyRow['lastprdbudgetcfwd'] - $MyRow['firstprdbudgetbfwd'];
			} else {
				$AccountPeriodActual = $MyRow['lastprdcfwd'];
				$AccountPeriodBudget = $MyRow['firstprdbfwd'] + $MyRow['lastprdbudgetcfwd'] - $MyRow['firstprdbudgetbfwd'];
			}
	}

	$CSV_File .= $MyRow['accountcode'] . ', ' . stripcomma($MyRow['accountname']) . ', ' . $AccountPeriodActual . ', ' . $AccountPeriodBudget  . "\n";
} //loop through the accounts

function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(',', '', $str);
}
echo $CSV_File;

?>