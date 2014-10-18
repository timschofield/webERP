<?php

/* $Id: GLTrialBalance_csv.php 5768 2012-12-20 08:38:22Z daintree $*/

/*Through deviousness and cunning, this system allows trial balances for any date range that recalcuates the p & l balances
and shows the balance sheets as at the end of the period selected - so first off need to show the input of criteria screen
while the user is selecting the criteria the system is posting any unposted transactions */

/*Needs to have FromPeriod and ToPeriod sent with URL
 * also need to work on authentication with username and password sent too*/


$AllowAnyone =true;

//Page must be called with GLTrialBalance_csv.php?CompanyName=XXXXX&FromPeriod=Y&ToPeriod=Z
$_POST['CompanyNameField'] = $_GET['CompanyName'];
//htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') = dirname(htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')) .'/GLTrialBalance_csv.php?ToPeriod=' . $_GET['ToPeriod'] . '&FromPeriod=' . $_GET['FromPeriod'];

include ('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

include ('includes/GLPostings.inc'); //do any outstanding posting

$NumberOfMonths = $_GET['ToPeriod'] - $_GET['FromPeriod'] + 1;

$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];

$SQL = "SELECT accountgroups.groupname,
			accountgroups.parentgroupname,
			accountgroups.pandl,
			chartdetails.accountcode ,
			chartmaster.accountname,
			Sum(CASE WHEN chartdetails.period='" . $_GET['FromPeriod'] . "' THEN chartdetails.bfwd ELSE 0 END) AS firstprdbfwd,
			Sum(CASE WHEN chartdetails.period='" . $_GET['FromPeriod'] . "' THEN chartdetails.bfwdbudget ELSE 0 END) AS firstprdbudgetbfwd,
			Sum(CASE WHEN chartdetails.period='" . $_GET['ToPeriod'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lastprdcfwd,
			Sum(CASE WHEN chartdetails.period='" . $_GET['ToPeriod'] . "' THEN chartdetails.actual ELSE 0 END) AS monthactual,
			Sum(CASE WHEN chartdetails.period='" . $_GET['ToPeriod'] . "' THEN chartdetails.budget ELSE 0 END) AS monthbudget,
			Sum(CASE WHEN chartdetails.period='" . $_GET['ToPeriod'] . "' THEN chartdetails.bfwdbudget + chartdetails.budget ELSE 0 END) AS lastprdbudgetcfwd
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

$AccountsResult = DB_query($SQL,$db);

while ($myrow=DB_fetch_array($AccountsResult)) {

	if ($myrow['pandl']==1){
			$AccountPeriodActual = $myrow['lastprdcfwd'] - $myrow['firstprdbfwd'];
			$AccountPeriodBudget = $myrow['lastprdbudgetcfwd'] - $myrow['firstprdbudgetbfwd'];
			$PeriodProfitLoss += $AccountPeriodActual;
			$PeriodBudgetProfitLoss += $AccountPeriodBudget;
			$MonthProfitLoss += $myrow['monthactual'];
			$MonthBudgetProfitLoss += $myrow['monthbudget'];
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

	$CSV_File .= $myrow['accountcode'] . ', ' . stripcomma($myrow['accountname']) . ', ' . $AccountPeriodActual . ', ' . $AccountPeriodBudget  . "\n";
} //loop through the accounts

function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(',', '', $str);
}
echo $CSV_File;

?>