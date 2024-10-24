<?php
// GLProfit_Loss.php
// Shows the profit and loss of the company for the range of periods entered.
/*
Info about financial statements: IAS 1 - Presentation of Financial Statements.

Parameters:
	PeriodFrom: Select the beginning of the reporting period.
	PeriodTo: Select the end of the reporting period.
	Period: Select a period instead of using the beginning and end of the reporting period.
{	ShowBudget: Check this box to show the budget for the period. Not used in this script.}
	ShowDetail: Check this box to show all accounts instead a summary.
	ShowZeroBalance: Check this box to show all accounts including those with zero balance.
	NewReport: Click this button to start a new report.
	IsIncluded: Parameter to indicate that a script is included within another.
*/

// BEGIN: Functions division ===================================================
// END: Functions division =====================================================
// BEGIN: Procedure division ===================================================
include('includes/session.php');
use Dompdf\Dompdf;

$Title = _('Profit and Loss');
$Title2 = _('Statement of Comprehensive Income');// Name as IAS.
$ViewTopic= 'GeneralLedger';
$BookMark = 'ProfitAndLoss';

include_once('includes/SQL_CommonFunctions.inc');
include_once('includes/AccountSectionsDef.php'); // This loads the $Sections variable
include_once('includes/CurrenciesArray.php');// Array to retrieve currency name.

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {
	// Merges gets into posts:
	if(isset($_GET['PeriodFrom'])) {
		$_POST['PeriodFrom'] = $_GET['PeriodFrom'];
	}
	if(isset($_GET['PeriodTo'])) {
		$_POST['PeriodTo'] = $_GET['PeriodTo'];
	}

	// Sets PeriodFrom and PeriodTo from Period:
	if(isset($_POST['Period']) and $_POST['Period'] != '') {
		$_POST['PeriodFrom'] = ReportPeriod($_POST['Period'] . 'From');
		$_POST['PeriodTo'] = ReportPeriod($_POST['Period'] . 'To');
	}

	// Validates the data submitted in the form:
	if(isset($_POST['PeriodFrom']) and $_POST['PeriodFrom'] > $_POST['PeriodTo']) {
		// The beginning is after the end.
		$_POST['NewReport'] = 'on';
		prnMsg(_('The beginning of the period should be before or equal to the end of the period. Please reselect the reporting period.') . 'error');
	}
	if(isset($_POST['PeriodTo']) and $_POST['PeriodTo']-$_POST['PeriodFrom']+1 > 12) {
		// The reporting period is greater than 12 months.
		$_POST['NewReport'] = 'on';
		prnMsg(_('The period should be 12 months or less in duration. Please select an alternative period range.') . 'error');
	}

	$NumberOfMonths = $_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1;

	if ($NumberOfMonths >12) {
		echo '<br />';
		prnMsg(_('A period up to 12 months in duration can be specified') . ' - ' . _('the system automatically shows a comparative for the same period from the previous year') . ' - ' . _('it cannot do this if a period of more than 12 months is specified') . '. ' . _('Please select an alternative period range'),'error');
		include('includes/footer.php');
		exit;
	}

	$SQL = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $_POST['PeriodTo'] . "'";
	$PrdResult = DB_query($SQL);
	$MyPrdRow = DB_fetch_row($PrdResult);
	$PeriodToDate = MonthAndYearFromSQLDate($MyPrdRow[0]);

	$SQL = "SELECT
				accountgroups.sectioninaccounts,
				accountgroups.parentgroupname,
				accountgroups.groupname,
				chartdetails.accountcode,
				chartmaster.accountname,
				SUM(CASE WHEN chartdetails.period='" . $_POST['PeriodFrom'] . "' THEN chartdetails.bfwd ELSE 0 END) AS firstprdbfwd,
				SUM(CASE WHEN chartdetails.period='" . $_POST['PeriodFrom'] . "' THEN chartdetails.bfwdbudget ELSE 0 END) AS firstprdbudgetbfwd,
				SUM(CASE WHEN chartdetails.period='" . $_POST['PeriodTo'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lastprdcfwd,
				SUM(CASE WHEN chartdetails.period='" . ($_POST['PeriodFrom'] - 12) . "' THEN chartdetails.bfwd ELSE 0 END) AS lyfirstprdbfwd,
				SUM(CASE WHEN chartdetails.period='" . ($_POST['PeriodTo']-12) . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lylastprdcfwd,
				SUM(CASE WHEN chartdetails.period='" . $_POST['PeriodTo'] . "' THEN chartdetails.bfwdbudget + chartdetails.budget ELSE 0 END) AS lastprdbudgetcfwd
			FROM chartmaster
				INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
				INNER JOIN chartdetails	ON chartmaster.accountcode= chartdetails.accountcode
				INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" .  $_SESSION['UserID'] . "' AND glaccountusers.canview=1
			WHERE accountgroups.pandl=1
			GROUP BY
				accountgroups.sectioninaccounts,
				accountgroups.parentgroupname,
				accountgroups.groupname,
				chartdetails.accountcode,
				chartmaster.accountname
			ORDER BY
				accountgroups.sectioninaccounts,
				accountgroups.sequenceintb,
				accountgroups.groupname,
				chartdetails.accountcode";

	$AccountsResult = DB_query($SQL,_('No general ledger accounts were returned by the SQL because'),_('The SQL that failed was'));

	$HTML = '';

	$SQL = "SELECT lastdate_in_period
			FROM periods
			WHERE periodno='" . $_POST['PeriodTo'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	$PeriodToDate = MonthAndYearFromSQLDate($MyRow[0]);
	$NumberOfMonths = $_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1;
	$HTML .= '<div class="centre" id="ReportHeader">
				' . $_SESSION['CompanyRecord']['coyname'] . '<br />
				' . _('Profit and Loss for the month of ') . $PeriodToDate . '<br />
				' . _(' AND for the ') . $NumberOfMonths . ' ' . _('months to') . ' ' . $PeriodToDate . '<br />
				' . _('All amounts stated in') . ': ' . _($CurrencyName[$_SESSION['CompanyRecord']['currencydefault']]) . '
			</div>';// Page title.

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP ' . $Version . '>
					<meta name="Creator" content="webERP //www.weberp.org">
				</head>
				<body>';
//		_('From') . ' ' . $PeriodFromDate? . ' ' . _('to') . ' ' . $PeriodToDate . '<br />'; // Page title . reporting period.
	$HTML .= '<table class="selection">'.
		// Content of the header and footer of the output table:
		'<thead>
			<tr>';
	if ($_POST['ShowDetail']=='Detailed') {
		$HTML .= '<th>' . _('Account') . '</th><th>' . _('Account Name') . '</th>';
	} else { /*summary */
		$HTML .= '<th colspan="2">&nbsp;</th>';
	}
	$HTML .=		'<th colspan="2">' . _('Period Actual') . '</th>
				<th colspan="2">' . _('Period Budget') . '</th>
				<th colspan="2">' . _('Last Year') . '</th>
			</tr>
		<thead><tbody>';// thead used in conjunction with tbody enable scrolling of the table body independently of the header and footer. Also . when printing a large table that spans multiple pages . these elements can enable the table header to be printed at the top of each page.

	$Section = '';
	$SectionPrdActual= 0;
	$SectionPrdBudget= 0;
	$SectionPrdLY 	 = 0;

	$PeriodProfitLossActual = 0;
	$PeriodProfitLossBudget = 0;
	$PeriodProfitLossLY = 0;

	$ActGrp = '';
	$ParentGroups = array();
	$Level = 0;
	$ParentGroups[$Level] = '';
	$GrpPrdActual = array(0);
	$GrpPrdBudget = array(0);
	$GrpPrdLY = array(0);
	$TotalIncomeActual = 0;
	$TotalIncomeBudget = 0;
	$TotalIncomeLY = 0;

	while ($MyRow=DB_fetch_array($AccountsResult)) {
		if ($MyRow['groupname']!= $ActGrp) {
			if ($MyRow['parentgroupname']!= $ActGrp AND $ActGrp!='') {
				while ($MyRow['groupname']!= $ParentGroups[$Level] AND $Level>0) {
					if ($_POST['ShowDetail']=='Detailed') {
						$HTML .= '<tr>
								<td colspan="2"></td>
								<td colspan="6"><hr /></td>
							</tr>';
						$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('total');
					} else {
						$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
					}
					if ($Section ==1) { /*Income */
						$HTML .= '<tr>
								<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
								<td>&nbsp;</td>
								<td class="number">' . locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
								<td>&nbsp;</td>
								<td class="number">' . locale_number_format(-$GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
								<td>&nbsp;</td>
								<td class="number">' . locale_number_format(-$GrpPrdLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							</tr>';
					} else { /*Costs */
						$HTML .= '<tr>
								<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
								<td class="number">' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
								<td>&nbsp;</td>
								<td class="number">' . locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
								<td>&nbsp;</td>
								<td class="number">' . locale_number_format($GrpPrdLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
								<td>&nbsp;</td>
							</tr>';
					}
					$GrpPrdActual[$Level] = 0;
					$GrpPrdBudget[$Level] = 0;
					$GrpPrdLY[$Level] = 0;
					$ParentGroups[$Level] = '';
					$Level--;
				}//end while
				//still need to print out the old group totals
				if ($_POST['ShowDetail']=='Detailed') {
					$HTML .= '<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>';
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('total');
				} else {
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
				}

				if ($Section ==1) { /*Income */
					$HTML .= '<tr>
							<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$GrpPrdLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
				} else { /*Costs */
					$HTML .= '<tr>
							<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
							<td class="number">' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($GrpPrdLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
						</tr>';
				}
				$GrpPrdLY[$Level] = 0;
				$GrpPrdActual[$Level] = 0;
				$GrpPrdBudget[$Level] = 0;
				$ParentGroups[$Level] = '';
			}
		}

		if ($MyRow['sectioninaccounts']!= $Section) {

			if ($SectionPrdLY+$SectionPrdActual+$SectionPrdBudget !=0) {
				if ($Section==1) { /*Income*/
					$HTML .= '<tr>
							<td colspan="3"></td>
							<td><hr /></td>
							<td>&nbsp;</td>
							<td><hr /></td>
							<td>&nbsp;</td>
							<td><hr /></td>
						</tr>
						<tr style="background-color:#ffffff">
							<td colspan="2"><h2>' . $Sections[$Section] . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$SectionPrdBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$SectionPrdLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
					$TotalIncomeActual = -$SectionPrdActual;
					$TotalIncomeBudget = -$SectionPrdBudget;
					$TotalIncomeLY = -$SectionPrdLY;
				} else {
					$HTML .= '<tr>
							<td colspan="2"></td>
							<td><hr /></td>
							<td>&nbsp;</td>
							<td><hr /></td>
							<td>&nbsp;</td>
							<td><hr /></td>
						</tr>
						<tr>
							<td colspan="2"><h2>' . $Sections[$Section] . '</h2></td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($SectionPrdBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($SectionPrdLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
				}
				if ($Section==2) { /*Cost of Sales - need sub total for Gross Profit*/
					$HTML .= '<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>
						<tr style="background-color:#ffffff">
							<td colspan="2"><h2>' . _('Gross Profit') . '</h2></td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($TotalIncomeActual - $SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($TotalIncomeBudget - $SectionPrdBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($TotalIncomeLY - $SectionPrdLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';

					if ($TotalIncomeActual !=0) {
						$GPPercentActual = ($TotalIncomeActual - $SectionPrdActual)/$TotalIncomeActual*100;
					} else {
						$GPPercentActual = 0;
					}
					if ($TotalIncomeBudget !=0) {
						$GPPercentBudget = ($TotalIncomeBudget - $SectionPrdBudget)/$TotalIncomeBudget*100;
					} else {
						$GPPercentBudget = 0;
					}
					if ($TotalIncomeLY !=0) {
						$GPPercentLY = ($TotalIncomeLY - $SectionPrdLY)/$TotalIncomeLY*100;
					} else {
						$GPPercentLY = 0;
					}
					$HTML .= '<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>
						<tr style="background-color:#ffffff">
							<td colspan="2"><h4><i>' . _('Gross Profit Percent') . '</i></h4></td>
							<td>&nbsp;</td>
							<td class="number"><i>' . locale_number_format($GPPercentActual,1) . '%</i></td>
							<td>&nbsp;</td>
							<td class="number"><i>' . locale_number_format($GPPercentBudget,1) . '%</i></td>
							<td>&nbsp;</td>
							<td class="number"><i>' . locale_number_format($GPPercentLY,1) . '%</i></td>
						</tr>
						<tr><td colspan="6">&nbsp;</td></tr>';
				}

				if (($Section!=1) AND ($Section!=2)) {
					$HTML .= '<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>
						<tr style="background-color:#ffffff">
							<td colspan="2"><h4><b>' . _('Profit').' - '._('Loss'). ' '. _('after'). ' ' . $Sections[$Section] . '</b></h2></td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$PeriodProfitLossActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$PeriodProfitLossBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$PeriodProfitLossLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';

					if ($TotalIncomeActual !=0) {
						$NPPercentActual = (-$PeriodProfitLossActual)/$TotalIncomeActual*100;
					} else {
						$NPPercentActual = 0;
					}
					if ($TotalIncomeBudget !=0) {
						$NPPercentBudget = (-$PeriodProfitLossBudget)/$TotalIncomeBudget*100;
					} else {
						$NPPercentBudget = 0;
					}
					if ($TotalIncomeLY !=0) {
						$NPPercentLY = (-$PeriodProfitLossLY)/$TotalIncomeLY*100;
					} else {
						$NPPercentLY = 0;
					}
					$HTML .= '<tr style="background-color:#ffffff">
							<td colspan="2"><h4><i>' . _('P/L Percent after').' ' . $Sections[$Section] . '</i></h4></td>
							<td>&nbsp;</td>
							<td class="number"><i>' . locale_number_format($NPPercentActual, 1) . '%</i></td>
							<td>&nbsp;</td>
							<td class="number"><i>' . locale_number_format($NPPercentBudget, 1) . '%</i></td>
							<td>&nbsp;</td>
							<td class="number"><i>' . locale_number_format($NPPercentLY, 1) . '%</i></td>
						</tr>
						<tr><td colspan="6">&nbsp;</td></tr>
						<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>';
				}
			}
			$SectionPrdActual = 0;
			$SectionPrdBudget = 0;
			$SectionPrdLY = 0;
			$Section = $MyRow['sectioninaccounts'];
			if ($_POST['ShowDetail']=='Detailed') {
				$HTML .= '<tr>
						<td colspan="6"><h2><b>' . $Sections[$MyRow['sectioninaccounts']] . '</b></h2></td>
					</tr>';
			}
		}

		if ($MyRow['groupname']!= $ActGrp) {
			if ($MyRow['parentgroupname']== $ActGrp AND $ActGrp !='') { //adding another level of nesting
				$Level++;
			}

			$ParentGroups[$Level] = $MyRow['groupname'];
			$ActGrp = $MyRow['groupname'];
			if ($_POST['ShowDetail']=='Detailed') {
				$HTML .= '<tr>
						<th colspan="8"><b>' . $MyRow['groupname'] . '</b></th>
					</tr>';
			}
		}
		$AccountPeriodActual = $MyRow['lastprdcfwd'] - $MyRow['firstprdbfwd'];
		$AccountPeriodBudget = $MyRow['lastprdbudgetcfwd'] - $MyRow['firstprdbudgetbfwd'];
		$AccountPeriodLY = $MyRow['lylastprdcfwd'] - $MyRow['lyfirstprdbfwd'];
		$PeriodProfitLossActual += $AccountPeriodActual;
		$PeriodProfitLossBudget += $AccountPeriodBudget;
		$PeriodProfitLossLY += $AccountPeriodLY;

		for ($i=0;$i<=$Level;$i++) {
			if (!isset($GrpPrdActual[$i])) {$GrpPrdActual[$i]=0;}
			$GrpPrdActual[$i] += $AccountPeriodActual;
			if (!isset($GrpPrdBudget[$i])) {$GrpPrdBudget[$i]=0;}
			$GrpPrdBudget[$i] += $AccountPeriodBudget;
			if (!isset($GrpPrdLY[$i])) {$GrpPrdLY[$i]=0;}
			$GrpPrdLY[$i] += $AccountPeriodLY;
		}
		$SectionPrdActual += $AccountPeriodActual;
		$SectionPrdBudget += $AccountPeriodBudget;
		$SectionPrdLY += $AccountPeriodLY;

		if ($_POST['ShowDetail']=='Detailed') {
			if (isset($_POST['ShowZeroBalance']) OR (!isset($_POST['ShowZeroBalance']) AND ($AccountPeriodActual <> 0 OR $AccountPeriodBudget <> 0 OR $AccountPeriodLY <> 0))) {
				$ActEnquiryURL = '<a href="' . $RootPath . '/GLAccountInquiry.php?PeriodFrom=' . urlencode($_POST['PeriodFrom']) . '&amp;PeriodTo=' . urlencode($_POST['PeriodTo']) . '&amp;Account=' . urlencode($MyRow['accountcode']) . '&amp;Show=Yes">' . $MyRow['accountcode'] . '</a>';
				if ($Section == 1) {
					 $HTML .= '<tr class="striped_row">
							<td>' . $ActEnquiryURL . '</td>
							<td>' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES,'UTF-8', false) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$AccountPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$AccountPeriodBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$AccountPeriodLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
				} else {
					$HTML .= '<tr class="striped_row">
							<td>' . $ActEnquiryURL . '</td>
							<td>' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES,'UTF-8', false) . '</td>
							<td class="number">' . locale_number_format($AccountPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($AccountPeriodBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($AccountPeriodLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
						</tr>';
				}
			}
		}
		$LastGroup = $MyRow['groupname'];
		$LastSection = $MyRow['sectioninaccounts'];
	}
	//end of loop

	if ($LastGroup!= $ActGrp) {
		if ($MyRow['parentgroupname']!= $ActGrp AND $ActGrp!='') {
			while ($MyRow['groupname']!= $ParentGroups[$Level] AND $Level>0) {
				if ($_POST['ShowDetail']=='Detailed') {
					$HTML .= '<tr>
						<td colspan="2"></td>
						<td colspan="6"><hr /></td>
					</tr>';
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('total');
				} else {
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
				}
				if ($Section ==1) { /*Income */
					$HTML .= '<tr>
							<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$GrpPrdLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
				} else { /*Costs */
					$HTML .= '<tr>
							<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
							<td class="number">' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($GrpPrdLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
						</tr>';
				}
				$GrpPrdActual[$Level] = 0;
				$GrpPrdBudget[$Level] = 0;
				$GrpPrdLY[$Level] = 0;
				$ParentGroups[$Level] = '';
				$Level--;
			}//end while
			//still need to print out the old group totals
			if ($_POST['ShowDetail']=='Detailed') {
					$HTML .= '<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>';
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('total');
				} else {
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
				}

			if ($Section ==1) { /*Income */
				$HTML .= '<tr>
					<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
						<td>&nbsp;</td>
						<td class="number">' . locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td>&nbsp;</td>
						<td class="number">' . locale_number_format(-$GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td>&nbsp;</td>
						<td class="number">' . locale_number_format(-$GrpPrdLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					</tr>';
			} else { /*Costs */
				$HTML .= '<tr>
						<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
						<td class="number">' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td>&nbsp;</td>
						<td class="number">' . locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td>&nbsp;</td>
						<td class="number">' . locale_number_format($GrpPrdLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td>&nbsp;</td>
					</tr>';
			}
			$GrpPrdActual[$Level] = 0;
			$GrpPrdBudget[$Level] = 0;
			$GrpPrdLY[$Level] = 0;
			$ParentGroups[$Level] = '';
		}
	}

	if ($LastSection!= $Section) {

		if ($Section==1) { /*Income*/
			$HTML .= '<tr>
					<td colspan="3"></td>
					<td><hr /></td>
					<td>&nbsp;</td>
					<td><hr /></td>
					<td>&nbsp;</td>
					<td><hr /></td>
				</tr>
				<tr>
					<td colspan="2"><h2>' . $Sections[$Section] . '</h2></td>
					<td>&nbsp;</td>
					<td class="number">' . locale_number_format(-$SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td>&nbsp;</td>
					<td class="number">' . locale_number_format(-$SectionPrdBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td>&nbsp;</td>
					<td class="number">' . locale_number_format(-$SectionPrdLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';
			$TotalIncomeActual = -$SectionPrdActual;
			$TotalIncomeBudget = -$SectionPrdBudget;
			$TotalIncomeLY = -$SectionPrdLY;
		} else {
			$HTML .= '<tr>
					<td colspan="2"></td>
					<td><hr /></td>
					<td>&nbsp;</td>
					<td><hr /></td>
					<td>&nbsp;</td>
					<td><hr /></td>
				</tr>
				<tr>
					<td colspan="2"><h2>' . $Sections[$Section] . '</h2></td>
					<td>&nbsp;</td>
					<td class="number">' . locale_number_format($SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td>&nbsp;</td>
					<td class="number">' . locale_number_format($SectionPrdBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td>&nbsp;</td>
					<td class="number">' . locale_number_format($SectionPrdLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';
		}
		if ($Section==2) { /*Cost of Sales - need sub total for Gross Profit*/
			$HTML .= '<tr>
					<td colspan="2"></td>
					<td colspan="6"><hr /></td>
				</tr>
				<tr>
					<td colspan="2"><h2>' . _('Gross Profit') . '</h2></td>
					<td>&nbsp;</td>
					<td class="number">' . locale_number_format($TotalIncomeActual - $SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td>&nbsp;</td>
					<td class="number">' . locale_number_format($TotalIncomeBudget - $SectionPrdBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td>&nbsp;</td>
					<td class="number">' . locale_number_format($TotalIncomeLY - $SectionPrdLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';

			if ($TotalIncomeActual !=0) {
				$GPPercentActual = ($TotalIncomeActual - $SectionPrdActual)/$TotalIncomeActual*100;
			} else {
				$GPPercentActual = 0;
			}
			if ($TotalIncomeBudget !=0) {
				$GPPercentBudget = ($TotalIncomeBudget - $SectionPrdBudget)/$TotalIncomeBudget*100;
			} else {
				$GPPercentBudget = 0;
			}
			if ($TotalIncomeLY !=0) {
				$GPPercentLY = ($TotalIncomeLY - $SectionPrdLY)/$TotalIncomeLY*100;
			} else {
				$GPPercentLY = 0;
			}
			$HTML .= '<tr>
					<td colspan="2"></td>
					<td colspan="6"><hr /></td>
				</tr>
				<tr>
					<td colspan="2"><h4><i>' . _('Gross Profit Percent') . '</i></h4></td>
					<td>&nbsp;</td>
					<td class="number"><i>' . locale_number_format($GPPercentActual, 1) . '%</i></td>
					<td>&nbsp;</td>
					<td class="number"><i>' . locale_number_format($GPPercentBudget, 1) . '%</i></td>
					<td>&nbsp;</td>
					<td class="number"><i>' . locale_number_format($GPPercentLY, 1). '%</i></td>
				</tr>
				<tr><td colspan="6">&nbsp;</td></tr>';
		}

		$SectionPrdActual = 0;
		$SectionPrdBudget = 0;
		$SectionPrdLY = 0;

		$Section = $MyRow['sectioninaccounts'];

		if ($_POST['ShowDetail']=='Detailed' and isset($Sections[$MyRow['sectioninaccounts']])) {
			$HTML .= '<tr>
				<td colspan="6"><h2><b>' . $Sections[$MyRow['sectioninaccounts']] . '</b></h2></td>
				</tr>';
		}
	}

	$HTML .= '<tr>
			<td colspan="2"></td>
			<td colspan="6"><hr /></td>
		</tr>';

	$HTML .= '<tr style="background-color:#ffffff">
			<td colspan="2"><h2><b>' . _('Profit').' - '._('Loss') . '</b></h2></td>
			<td>&nbsp;</td>
			<td class="number">' . locale_number_format(-$PeriodProfitLossActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td>&nbsp;</td>
			<td class="number">' . locale_number_format(-$PeriodProfitLossBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td>&nbsp;</td>
			<td class="number">' . locale_number_format(-$PeriodProfitLossLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';


	if ($TotalIncomeActual !=0) {
		$NPPercentActual = (-$PeriodProfitLossActual)/$TotalIncomeActual*100;
	} else {
		$NPPercentActual = 0;
	}
	if ($TotalIncomeBudget !=0) {
		$NPPercentBudget=(-$PeriodProfitLossBudget)/$TotalIncomeBudget*100;
	} else {
		$NPPercentBudget=0;
	}
	if ($TotalIncomeLY !=0) {
		$NPPercentLY = (-$PeriodProfitLossLY)/$TotalIncomeLY*100;
	} else {
		$NPPercentLY = 0;
	}
	$HTML .= '<tr>
			<td colspan="2"></td>
			<td colspan="6"><hr /></td>
		</tr>
		<tr style="background-color:#ffffff">
				<td colspan="2"><h4><i>' . _('Net Profit Percent') . '</i></h4></td>
				<td>&nbsp;</td>
				<td class="number"><i>' . locale_number_format($NPPercentActual, 1) . '%</i></td>
				<td>&nbsp;</td>
				<td class="number"><i>' . locale_number_format($NPPercentBudget, 1) . '%</i></td>
				<td>&nbsp;</td>
				<td class="number"><i>' . locale_number_format($NPPercentLY, 1) . '%</i></td>
		</tr>
		<tr><td colspan="6">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2"></td>
			<td colspan="6"><hr /></td>
		</tr>
		</tbody></table>';

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
		$Title = _('General Ledger Profit and Loss');
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/gl.png" title="' . _('Profit and Loss Report') . '" alt="" />
				' . _('Profit and Loss Report') . '
			</p>';
		echo $HTML;
		echo // Shows a form to select an action after the report was shown:
		'<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">',
		'<input name="FormID" type="hidden" value="' . $_SESSION['FormID'] . '" />',
		// Resend report parameters:
		'<input name="PeriodFrom" type="hidden" value="' . $_POST['PeriodFrom'] . '" />',
		'<input name="PeriodTo" type="hidden" value="' . $_POST['PeriodTo'] . '" />',
		'<div class="centre">
			<input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" />
		</div>' .
		'</form>';
	}

} else {

	include('includes/header.php');

	echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/printer.png" title="' . // Icon image.
		$Title2 . '" /> ' . // Icon title.
		$Title . '</p>';// Page title.
	fShowPageHelp(// Shows the page help text if $_SESSION['ShowFieldHelp'] is TRUE or is not set
		_('Profit and loss statement (P&amp;L) . also called an Income Statement . or Statement of Operations . this is the statement that indicates how the revenue (money received from the sale of products and services before expenses are taken out . also known as the top line) is transformed into the net income (the result after all revenues and expenses have been accounted for . also known as the bottom line).') . '<br />' .
		_('The purpose of the income statement is to show whether the company made or lost money during the period being reported.') . '<br />' .
		_('The P&amp;L represents a period of time. This contrasts with the Balance Sheet . which represents a single moment in time.') . '<br />' .
		_('webERP is an accrual based system (not a cash based system). Accrual systems include items when they are invoiced to the customer . and when expenses are owed based on the supplier invoice date.'));// Function fShowPageHelp() in ~/includes/MiscFunctions.php
	echo // Shows a form to input the report parameters:
		'<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">',
		'<input name="FormID" type="hidden" value="' . $_SESSION['FormID'] . '" />',
		// Input table:
		'<fieldset>
			<legend>' . _('Report Criteria') . '</legend>',
	// Content of the body of the input table:
	// Select period from:
			'<field>
				<label for="PeriodFrom">' . _('Select period from') . '</label>
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
		<fieldhelp>' . _('Select the beginning of the reporting period') . '</fieldhelp>
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
	echo '<field>
			<label for="PeriodTo">' . _('Select period to') . '</label>
		 	<select id="PeriodTo" name="PeriodTo" required="required">';
	DB_data_seek($Periods, 0);
	while($MyRow = DB_fetch_array($Periods)) {
		echo '<option',($MyRow['periodno'] == $_POST['PeriodTo'] ? ' selected="selected"' : '' ) . ' value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
	}
	echo  '</select>
		<fieldhelp>' . _('Select the end of the reporting period') . '</fieldhelp>
	</field>';
	// OR Select period:
	if(!isset($_POST['Period'])) {
		$_POST['Period'] = '';
	}
	echo '<h3>' . _('OR') . '</h3>';

	echo '<field>
			<label for="Period">' . _('Select Period') . '</label>
			' . ReportPeriodList($_POST['Period'], array('l', 't')),
			'<fieldhelp>' . _('Select a period instead of using the beginning and end of the reporting period.') . '</fieldhelp>
		</field>';

	echo '<field>
			<label for="ShowDetail">' . _('Detail or summary') . '</label>
			<select name="ShowDetail">
				<option value="Summary">' . _('Summary') . '</option>
				<option selected="selected" value="Detailed">' . _('All Accounts') . '</option>
			</select>
		</field>',
		// Show accounts with zero balance:
		'<field>',
			'<label for="ShowZeroBalance">' . _('Show accounts with zero balance') . '</label>
		 	<input',(isset($_POST['ShowZeroBalance']) && $_POST['ShowZeroBalance'] ? ' checked="checked"' : '') . ' id="ShowZeroBalance" name="ShowZeroBalance" type="checkbox">
		 	<fieldhelp>' . _('Check this box to show all accounts including those with zero balance') . '</fieldhelp>
		</field>',
		'</fieldset>';

	/*Now do the posting while the user is thinking about the period to select */

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" title="PDF" value="'._('PDF P & L Account').'" />
			<input type="submit" name="View" title="View" value="' . _('Show P & L Account') .'" />
		</div>',
		'</form>';

	include('includes/GLPostings.inc');
	include('includes/footer.php');

}

?>