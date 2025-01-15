<?php
// GLTrialBalance.php
// Shows the trial balance for the month and the for the period selected together with the budgeted trial balances.

/*Through deviousness AND cunning, this system allows trial balances for any date range that recalculates the P&L balances
and shows the balance sheets as at the end of the period selected - so first off need to show the input of criteria screen
while the user is selecting the criteria the system is posting any unposted transactions */

/*
global $_dompdf_warnings;
$_dompdf_warnings = array();
global $_dompdf_show_warnings;
$_dompdf_show_warnings = true;
*/

include ('includes/session.php');
use Dompdf\Dompdf;

$Title = _('Trial Balance');
$ViewTopic = 'GeneralLedger';
$BookMark = 'TrialBalance';

include('includes/SQL_CommonFunctions.inc');
include('includes/AccountSectionsDef.php'); // This loads the $Sections variable

if (isset($_POST['PrintPDF']) or isset($_POST['ViewTB'])) {

	// Merges gets into posts:
	if(isset($_GET['PeriodFrom'])) {
		$_POST['PeriodFrom'] = $_GET['PeriodFrom'];
	}
	if(isset($_GET['PeriodTo'])) {
		$_POST['PeriodTo'] = $_GET['PeriodTo'];
	}
	if(isset($_GET['Period'])) {
		$_POST['Period'] = $_GET['Period'];
	}

	// Sets PeriodFrom and PeriodTo from Period:
	if(isset($_POST['Period']) and $_POST['Period'] != '') {
		$_POST['PeriodFrom'] = ReportPeriod($_POST['Period'], 'From');
		$_POST['PeriodTo'] = ReportPeriod($_POST['Period'], 'To');
	}
	if(isset($_POST['PeriodFrom']) and $_POST['PeriodFrom'] > $_POST['PeriodTo']) {
		// The beginning is after the end.
		$_POST['NewReport'] = 'on';
		prnMsg(_('The beginning of the period should be before or equal to the end of the period. Please reselect the reporting period.'), 'error');
	}
	if(isset($_POST['PeriodTo']) and $_POST['PeriodTo']-$_POST['PeriodFrom']+1 > 12) {
		// The reporting period is greater than 12 months.
		$_POST['NewReport'] = 'on';
		prnMsg(_('The period should be 12 months or less in duration. Please select an alternative period range.'), 'error');
	}

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP ' . $Version . '>
					<meta name="Creator" content="webERP http://www.weberp.org">
				</head>
				<body>';

	$SQL = "SELECT lastdate_in_period
			FROM periods
			WHERE periodno='" . $_POST['PeriodTo'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	$PeriodToDate = MonthAndYearFromSQLDate($MyRow[0]);
	$NumberOfMonths = $_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1;
	$HTML .= '<div class="centre" id="ReportHeader">
				' . $_SESSION['CompanyRecord']['coyname'] . '<br />
				' . _('Trial Balance for the month of ') . $PeriodToDate . '<br />
				' . _(' AND for the ') . $NumberOfMonths . ' ' . _('months to') . ' ' . $PeriodToDate .
			'</div>';// Page title.

	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];

	$HTML .= '<table cellpadding="2" class="selection"><tbody>';

	$TableHeader = '<tr>
						<th>' . _('Account') . '</th>
						<th>' . _('Account Name') . '</th>
						<th>' . _('Month Actual') . '</th>
						<th>' . _('Month Budget') . '</th>
						<th>' . _('Period Actual') . '</th>
						<th>' . _('Period Budget')  . '</th>
					</tr>';// RChacon: Can be part of a <thead>.*************

	$SQL = "SELECT accountgroups.groupname,
			accountgroups.parentgroupname,
			accountgroups.pandl,
			chartdetails.accountcode ,
			chartmaster.accountname,
			Sum(CASE WHEN chartdetails.period='" . $_POST['PeriodFrom'] . "' THEN chartdetails.bfwd ELSE 0 END) AS firstprdbfwd,
			Sum(CASE WHEN chartdetails.period='" . $_POST['PeriodFrom'] . "' THEN chartdetails.bfwdbudget ELSE 0 END) AS firstprdbudgetbfwd,
			Sum(CASE WHEN chartdetails.period='" . $_POST['PeriodTo'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lastprdcfwd,
			Sum(CASE WHEN chartdetails.period='" . $_POST['PeriodTo'] . "' THEN chartdetails.actual ELSE 0 END) AS monthactual,
			Sum(CASE WHEN chartdetails.period='" . $_POST['PeriodTo'] . "' THEN chartdetails.budget ELSE 0 END) AS monthbudget,
			Sum(CASE WHEN chartdetails.period='" . $_POST['PeriodTo'] . "' THEN chartdetails.bfwdbudget + chartdetails.budget ELSE 0 END) AS lastprdbudgetcfwd
		FROM chartmaster
			INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
			INNER JOIN chartdetails ON chartmaster.accountcode= chartdetails.accountcode
			INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" . $_SESSION['UserID'] . "' AND glaccountusers.canview=1
		GROUP BY accountgroups.groupname,
				accountgroups.pandl,
				accountgroups.sequenceintb,
				accountgroups.parentgroupname,
				chartdetails.accountcode,
				chartmaster.accountname
		ORDER BY accountgroups.pandl desc,
			accountgroups.sequenceintb,
			accountgroups.groupname,
			chartdetails.accountcode";

	$AccountsResult = DB_query($SQL, _('No general ledger accounts were returned by the SQL because'), _('The SQL that failed was:'));

	/*show a table of the accounts info returned by the SQL
	Account Code, Account Name, Month Actual, Month Budget, Period Actual, Period Budget */

	$ActGrp ='';
	$ParentGroups = array();
	$Level =1; //level of nested sub-groups
	$ParentGroups[$Level]='';
	$GrpActual =array(0);
	$GrpBudget =array(0);
	$GrpPrdActual =array(0);
	$GrpPrdBudget =array(0);

	$PeriodProfitLoss = 0;
	$PeriodBudgetProfitLoss = 0;
	$MonthProfitLoss = 0;
	$MonthBudgetProfitLoss = 0;
	$BFwdProfitLoss = 0;
	$CheckMonth = 0;
	$CheckBudgetMonth = 0;
	$CheckPeriodActual = 0;
	$CheckPeriodBudget = 0;

	while ($MyRow=DB_fetch_array($AccountsResult)) {

		if ($MyRow['groupname']!= $ActGrp ) {
			if ($ActGrp !='') { //so its not the first account group of the first account displayed
				if ($MyRow['parentgroupname']==$ActGrp) {
					$Level++;
					$ParentGroups[$Level]=$MyRow['groupname'];
					$GrpActual[$Level] = 0;
					$GrpBudget[$Level] = 0;
					$GrpPrdActual[$Level] = 0;
					$GrpPrdBudget[$Level] = 0;
					$ParentGroups[$Level]='';
				} elseif ($ParentGroups[$Level]==$MyRow['parentgroupname']) {
					$HTML .= '<tr>
						<td colspan="2"><i>' . $ParentGroups[$Level] . ' '. _('Total') . ' </i></td>
						<td class="number"><i>' . locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
						<td class="number"><i>' . locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']). '</i></td>
						<td class="number"><i>' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']). '</i></td>
						<td class="number"><i>' . locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']). '</i></td>
						</tr>';

					$GrpActual[$Level] = 0;
					$GrpBudget[$Level] = 0;
					$GrpPrdActual[$Level] = 0;
					$GrpPrdBudget[$Level] = 0;
					$ParentGroups[$Level]=$MyRow['groupname'];
				} else {
					do {
						$HTML .= '<tr>
							<td colspan="2"><i>' . $ParentGroups[$Level] . ' ' . _('Total') . ' </i></td>
							<td class="number"><i>' . locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']). '</i></td>
							<td class="number"><i>' . locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
							<td class="number"><i>' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
							<td class="number"><i>' . locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']). '</i></td>
							</tr>';

						$GrpActual[$Level] = 0;
						$GrpBudget[$Level] = 0;
						$GrpPrdActual[$Level] = 0;
						$GrpPrdBudget[$Level] = 0;
						$ParentGroups[$Level]='';
						$Level--;

					} while ($Level>0 AND $MyRow['groupname']!=$ParentGroups[$Level]);

					if ($Level>0) {
						$HTML .= '<tr>
						<td colspan="2"><i>' . $ParentGroups[$Level]. ' ' . _('Total') . ' </i></td>
						<td class="number"><i>' . locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
						<td class="number"><i>' . locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']). '</i></td>
						<td class="number"><i>' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']). '</i></td>
						<td class="number"><i>' . locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']). '</i></td>
						</tr>';

						$GrpActual[$Level] = 0;
						$GrpBudget[$Level] = 0;
						$GrpPrdActual[$Level] = 0;
						$GrpPrdBudget[$Level] = 0;
						$ParentGroups[$Level]='';
					} else {
						$Level=1;
					}
				}
			}
			$ParentGroups[$Level]=$MyRow['groupname'];
			$ActGrp = $MyRow['groupname'];
			$HTML .= '<tr>
					<td colspan="6"><h2>' . $MyRow['groupname'] . '</h2></td>
				</tr>';
			$HTML .= $TableHeader;// RChacon: Can be part of a <thead>.*************
		}

		/*MonthActual, MonthBudget, FirstPrdBFwd, FirstPrdBudgetBFwd, LastPrdBudgetCFwd, LastPrdCFwd */

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

		if (!isset($GrpActual[$Level])) {
			$GrpActual[$Level]=0;
		}
		if (!isset($GrpBudget[$Level])) {
			$GrpBudget[$Level]=0;
		}
		if (!isset($GrpPrdActual[$Level])) {
			$GrpPrdActual[$Level]=0;
		}
		if (!isset($GrpPrdBudget[$Level])) {
			$GrpPrdBudget[$Level]=0;
		}
		$GrpActual[$Level] +=$MyRow['monthactual'];
		$GrpBudget[$Level] +=$MyRow['monthbudget'];
		$GrpPrdActual[$Level] +=$AccountPeriodActual;
		$GrpPrdBudget[$Level] +=$AccountPeriodBudget;

		$CheckMonth += $MyRow['monthactual'];
		$CheckBudgetMonth += $MyRow['monthbudget'];
		$CheckPeriodActual += $AccountPeriodActual;
		$CheckPeriodBudget += $AccountPeriodBudget;

		$HTML .= '<tr class="striped_row">
				<td><a href="' . $RootPath . '/GLAccountInquiry.php?PeriodFrom=' . $_POST['PeriodFrom'] . '&amp;PeriodTo=' . $_POST['PeriodTo'] . '&amp;Account=' . $MyRow['accountcode'] . '&amp;Show=Yes">' . $MyRow['accountcode'] . '</a></td>
				<td>' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES,'UTF-8', false) . '</td>
				<td class="number">' . locale_number_format($MyRow['monthactual'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MyRow['monthbudget'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($AccountPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($AccountPeriodBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';
	}
	//end of while loop

	if ($ActGrp !='') { //so its not the first account group of the first account displayed
		if (isset($MyRow['parentgroupname']) and $MyRow['parentgroupname']==$ActGrp) {
			$Level++;
			$ParentGroups[$Level]=$MyRow['groupname'];
		} elseif (isset($MyRow['parentgroupname']) and $ParentGroups[$Level]==$MyRow['parentgroupname']) {
			$HTML .= '<tr>
					<td colspan="2"><i>' . $ParentGroups[$Level] . ' ' . _('Total') . ' </i></td>
					<td class="number"><i>' . locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
					<td class="number"><i>' . locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
					<td class="number"><i>' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
					<td class="number"><i>' . locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
				</tr>';
			$GrpActual[$Level] = 0;
			$GrpBudget[$Level] = 0;
			$GrpPrdActual[$Level] = 0;
			$GrpPrdBudget[$Level] = 0;
			$ParentGroups[$Level] = $MyRow['groupname'];
		} else {
			do {
				$HTML .= '<tr>
						<td colspan="2"><i>' . $ParentGroups[$Level] . ' ' . _('Total') . ' </i></td>
						<td class="number"><i>' . locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
						<td class="number"><i>' . locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
						<td class="number"><i>' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
						<td class="number"><i>' . locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
					</tr>';
				$GrpActual[$Level] = 0;
				$GrpBudget[$Level] = 0;
				$GrpPrdActual[$Level] = 0;
				$GrpPrdBudget[$Level] = 0;
				$ParentGroups[$Level] = '';
				$Level--;
			} while (isset($ParentGroups[$Level]) AND ($MyRow['groupname']!=$ParentGroups[$Level] AND $Level>0));

			if ($Level >0) {
				$HTML .= '<tr>
						<td colspan="2"><i>' . $ParentGroups[$Level] . ' ' . _('Total') . ' </i></td>
						<td class="number"><i>' . locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
						<td class="number"><i>' . locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
						<td class="number"><i>' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
						<td class="number"><i>' . locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</i></td>
					</tr>';
				$GrpActual[$Level] = 0;
				$GrpBudget[$Level] = 0;
				$GrpPrdActual[$Level] = 0;
				$GrpPrdBudget[$Level] = 0;
				$ParentGroups[$Level] = '';
			} else {
				$Level =1;
			}
		}
	}

	$HTML .= '<tr style="background-color:#ffffff">
				<td colspan="2"><b>' . _('Check Totals') . '</b></td>
				<td class="number">' . locale_number_format($CheckMonth, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($CheckBudgetMonth, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($CheckPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($CheckPeriodBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr></tbody></table>';// div id="Report".

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</body></html>';
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_Trial_Balance_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = _('General Ledger Trial Balance');
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/gl.png" title="' . _('Trial Balance Report') . '" alt="" />
				' . _('Trial Balance Report') . '
			</p>';
		echo $HTML;
	echo // Shows a form to select an action after the report was shown:
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
		// Resend report parameters:
		'<input name="PeriodFrom" type="hidden" value="', $_POST['PeriodFrom'], '" />',
		'<input name="PeriodTo" type="hidden" value="', $_POST['PeriodTo'], '" />',
		'<div class="centre">
			<input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" />
		</div>' .
		'</form>';
	}


} else if ((! isset($_POST['PeriodFrom'])
	AND ! isset($_POST['PeriodTo']))
	OR isset($_POST['NewReport'])) {

	// If PeriodFrom or PeriodTo are NOT set or it is a NewReport, shows a parameters input form:
	include('includes/header.php');
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/printer.png" title="', // Icon image.
		_('Print Trial Balance'), '" /> ', // Icon title.
		$Title, '</p>', // Page title.
	// Shows a form to input the report parameters:
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post" target="_blank">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
	// Input table:
		'<fieldset>
			<legend>', _('Report Criteria'), '</legend>';
	// Content of the body of the input table:

	// Select period from:
	echo '<field>
			<label for="PeriodFrom">', _('Select period from'), '</label>
			<select id="PeriodFrom" name="PeriodFrom" required="required">';
	$Periods = DB_query('SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC');

	if (Date('m') > $_SESSION['YearEnd']) {
		/*Dates in SQL format */
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0, $_SESSION['YearEnd'] + 2,0,Date('Y')));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0, $_SESSION['YearEnd'] + 2,0,Date('Y')));
	} else {
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0, $_SESSION['YearEnd'] + 2,0,Date('Y')-1));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0, $_SESSION['YearEnd'] + 2,0,Date('Y')-1));
	}

	$period = GetPeriod($FromDate);

	while ($MyRow=DB_fetch_array($Periods)) {
		if(isset($_POST['PeriodFrom']) AND $_POST['PeriodFrom']!='') {
			if( $_POST['PeriodFrom']== $MyRow['periodno']) {
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' .MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		} else {
			if($MyRow['lastdate_in_period']== $DefaultFromDate) {
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		}
	}
	echo '</select>
		<fieldhelp>', _('Select the beginning of the reporting period'), '</fieldhelp>
	</field>';

	// Select period to:
	if(!isset($_POST['PeriodTo'])) {
		$PeriodSQL = "SELECT periodno
						FROM periods
						WHERE MONTH(lastdate_in_period) = MONTH(CURRENT_DATE())
						AND YEAR(lastdate_in_period ) = YEAR(CURRENT_DATE())";
		$PeriodResult = DB_query($PeriodSQL);
		$PeriodRow = DB_fetch_array($PeriodResult);
		$_POST['PeriodTo'] = $PeriodRow['periodno'];;
	}
	// Select period to:
	echo '<field>
			<label for="PeriodTo">', _('Select period to'), '</label>
		 	<select id="PeriodTo" name="PeriodTo" required="required">';
	if(!isset($_POST['PeriodTo'])) {
		$_POST['PeriodTo'] = GetPeriod(date($_SESSION['DefaultDateFormat']));
	}
	DB_data_seek($Periods, 0);
	while($MyRow = DB_fetch_array($Periods)) {
		if ($MyRow['periodno'] == $_POST['PeriodTo']) {
			echo '<option selected="selected" value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
		} else {
			echo '<option value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
		}
	}
	echo '</select>
		<fieldhelp>', _('Select the end of the reporting period'), '</fieldhelp>
	</field>';
	// OR Select period:
	if(!isset($_POST['Period'])) {
		$_POST['Period'] = '';
	}
	echo '<h3>', _('OR'), '</h3>';

	echo '<field>
			<label for="Period">', _('Select Period'), '</label>
			', ReportPeriodList($_POST['Period'], array('l', 't')),
			'<fieldhelp>', _('Select a period instead of using the beginning and end of the reporting period.'),
		'</field>',
	'</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" title="PDF" value="'._('PDF Trial Balance').'" />
			<input type="submit" name="ViewTB" title="View" value="' . _('Show Trial Balance') .'" />
		</div>',
		'</form>';

	// Now do the posting while the user is thinking about the period to select:
	include ('includes/GLPostings.inc');
}



include('includes/footer.php');
?>