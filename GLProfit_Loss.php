<?php

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

if (!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
	require(__DIR__ . '/includes/session.php');
}

use Dompdf\Dompdf;

$Title = __('Profit and Loss');
$Title2 = __('Statement of Comprehensive Income'); // Name as IAS.
$ViewTopic = 'GeneralLedger';
$BookMark = 'ProfitAndLoss';

include_once('includes/SQL_CommonFunctions.php');
include_once('includes/AccountSectionsDef.php'); // This loads the $Sections variable
include_once('includes/CurrenciesArray.php'); // Array to retrieve currency name.

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {
	// Initialize SelectedBudget if not set
	if (!isset($_POST['SelectedBudget'])) {
		$_POST['SelectedBudget'] = 0; // Default value when not selected

	}

	// Sets PeriodFrom and PeriodTo from Period:
	if (isset($_POST['Period']) and $_POST['Period'] != '') {
		$_POST['PeriodFrom'] = ReportPeriod($_POST['Period'], 'From');
		$_POST['PeriodTo'] = ReportPeriod($_POST['Period'], 'To');
	}

	$NumberOfMonths = $_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1;

	$PeriodToDate = MonthAndYearFromSQLDate(EndDateSQLFromPeriodNo($_POST['PeriodTo']));
	$PeriodFromDate = MonthAndYearFromSQLDate(EndDateSQLFromPeriodNo($_POST['PeriodFrom']));

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP ' . $Version . '>
					<meta name="Creator" content="webERP //www.weberp.org">
				</head>
				<body>';

	$HTML .= '<table summary="' . __('General Ledger Profit Loss Inquiry') . ' ' . __('From') . ' ' . $PeriodFromDate . ' ' . __('to') . ' ' . $PeriodToDate . '">
			<thead>
				<tr>
					<th colspan="8">
						<b>' . __('General Ledger Profit Loss Inquiry') . ' ' . __('From') . ' ' . $PeriodFromDate . ' ' . __('to') . ' ' . $PeriodToDate . '</b>
					</th>
				</tr>';

	if ($_POST['ShowDetail'] == 'Detailed') {
		$HTML .= '<tr>
				<th>' . __('Account') . '</th>
				<th>' . __('Account Name') . '</th>
				<th colspan="2">' . __('Period Actual') . '</th>
				<th colspan="2">' . __('Period Budget') . '</th>
				<th colspan="2">' . __('Last Year') . '</th>
			</tr>';
	}
	else {
		/*summary */
		$HTML .= '<tr>
				<th colspan="2"></th>
				<th colspan="2">' . __('Period Actual') . '</th>
				<th colspan="2">' . __('Period Budget') . '</th>
				<th colspan="2">' . __('Last Year') . '</th>
			</tr>';
	}
	$HTML .= '</thead>';
	$j = 1;

	$Section = '';
	$SectionPrdActual = 0;
	$SectionPrdLY = 0;
	$SectionPrdBudget = 0;

	$PeriodProfitLoss = 0;
	$PeriodProfitLoss = 0;
	$PeriodLYProfitLoss = 0;
	$PeriodBudgetProfitLoss = 0;

	$ActGrp = '';
	$ParentGroups = array();
	$Level = 0;
	$ParentGroups[$Level] = '';
	$GrpPrdActual = array(0);
	$GrpPrdLY = array(0);
	$GrpPrdBudget = array(0);
	$TotalIncome = 0;
	$TotalBudgetIncome = 0;
	$TotalLYIncome = 0;

	$PeriodProfitLossActual = 0;
	$PeriodProfitLossBudget = 0;
	$PeriodProfitLossLY = 0;

	// Get all account codes
	$SQL = "SELECT sectionid,
					sectionname,
					parentgroupname,
					chartmaster.group_,
					chartmaster.accountcode,
					group_,
					accountname,
					pandl
				FROM chartmaster
				INNER JOIN glaccountusers
					ON glaccountusers.accountcode=chartmaster.accountcode
					AND glaccountusers.userid='" . $_SESSION['UserID'] . "'
					AND glaccountusers.canview=1
				INNER JOIN accountgroups
					ON accountgroups.groupname=chartmaster.group_
				INNER JOIN accountsection
					ON accountsection.sectionid=accountgroups.sectioninaccounts
				WHERE pandl=1
				ORDER BY sequenceintb,
						group_,
						accountcode";
	$AccountListResult = DB_query($SQL);

	$SQL = "SELECT account,
					SUM(amount) AS accounttotal
				FROM gltotals
				WHERE period>='" . $_POST['PeriodFrom'] . "'
					AND period<='" . $_POST['PeriodTo'] . "'
				GROUP BY account
				ORDER BY account";
	$Result = DB_query($SQL);

	$ThisYearActuals = array();
	while ($MyRow = DB_fetch_array($Result)) {
		$ThisYearActuals[$MyRow['account']] = $MyRow['accounttotal'];
	}

	$SQL = "SELECT account,
					SUM(amount) AS accounttotal
				FROM gltotals
				WHERE period>='" . ($_POST['PeriodFrom'] - 12) . "'
					AND period<='" . ($_POST['PeriodTo'] - 12) . "'
				GROUP BY account
				ORDER BY account";
	$Result = DB_query($SQL);

	$LastYearActuals = array();
	while ($MyRow = DB_fetch_array($Result)) {
		$LastYearActuals[$MyRow['account']] = $MyRow['accounttotal'];
	}

	while ($MyRow = DB_fetch_array($AccountListResult)) {

		$SQL = "SELECT SUM(amount) AS periodbudget
				FROM glbudgetdetails
				WHERE account='" . $MyRow['accountcode'] . "'
					AND period>='" . $_POST['PeriodFrom'] . "'
					AND period<='" . $_POST['PeriodTo'] . "'
					AND headerid='" . $_POST['SelectedBudget'] . "'";
		$PeriodBudgetResult = DB_query($SQL);
		$PeriodBudgetRow = DB_fetch_array($PeriodBudgetResult);
		if (!isset($PeriodBudgetRow['periodbudget'])) {
			$PeriodBudgetRow['periodbudget'] = 0;
		}
		if ($MyRow['group_'] != $ActGrp) {
			if ($MyRow['parentgroupname'] != $ActGrp and $ActGrp != '') {
				while ($MyRow['group_'] != $ParentGroups[$Level] and $Level > 0) {
					if ($_POST['ShowDetail'] == 'Detailed') {
						$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level] . ' ' . __('total');
					}
					else {
						$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level];
					}
					if ($Section == 1) { /*Income */
						$HTML .= '<tr>
								<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
								<td>&nbsp;</td>
								<td class="number">' . locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
								<td>&nbsp;</td>
								<td class="number">' . locale_number_format(-$GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
								<td>&nbsp;</td>
								<td class="number">' . locale_number_format(-$GrpPrdLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							</tr>';
					}
					else { /*Costs */
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
				} //end while
				//still need to print out the old group totals
				if ($_POST['ShowDetail'] == 'Detailed') {
					$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level] . ' ' . __('total');
				}
				else {
					$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level];
				}

				if ($Section == 1) { /*Income */
					$HTML .= '<tr class="total_row">
							<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$GrpPrdLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
				}
				else { /*Costs */
					$HTML .= '<tr class="total_row">
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

		if ($MyRow['sectionid'] != $Section) {

			if ($SectionPrdLY + $SectionPrdActual + $SectionPrdBudget != 0) {
				if ($Section == 1) { /*Income*/
					$HTML .= '<tr class="total_row">
							<td colspan="2"><h2>' . $Sections[$Section] . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$SectionPrdBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$SectionPrdLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
					$TotalIncomeActual = - $SectionPrdActual;
					$TotalIncomeBudget = - $SectionPrdBudget;
					$TotalIncomeLY = - $SectionPrdLY;
				}
				else {
					$HTML .= '<tr class="total_row">
							<td colspan="2"><h2>' . $Sections[$Section] . '</h2></td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($SectionPrdBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($SectionPrdLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
				}
				if ($Section == 2) { /*Cost of Sales - need sub total for Gross Profit*/
					$HTML .= '<tr class="total_row">
							<td colspan="2"><h2>' . __('Gross Profit') . '</h2></td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($TotalIncomeActual - $SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($TotalIncomeBudget - $SectionPrdBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($TotalIncomeLY - $SectionPrdLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';

					if ($TotalIncomeActual != 0) {
						$GPPercentActual = ($TotalIncomeActual - $SectionPrdActual) / $TotalIncomeActual * 100;
					}
					else {
						$GPPercentActual = 0;
					}
					if ($TotalIncomeBudget != 0) {
						$GPPercentBudget = ($TotalIncomeBudget - $SectionPrdBudget) / $TotalIncomeBudget * 100;
					}
					else {
						$GPPercentBudget = 0;
					}
					if ($TotalIncomeLY != 0) {
						$GPPercentLY = ($TotalIncomeLY - $SectionPrdLY) / $TotalIncomeLY * 100;
					}
					else {
						$GPPercentLY = 0;
					}
					$HTML .= '<tr class="total_row">
							<td colspan="2"><h4><i>' . __('Gross Profit Percent') . '</i></h4></td>
							<td>&nbsp;</td>
							<td class="number"><i>' . locale_number_format($GPPercentActual, 1) . '%</i></td>
							<td>&nbsp;</td>
							<td class="number"><i>' . locale_number_format($GPPercentBudget, 1) . '%</i></td>
							<td>&nbsp;</td>
							<td class="number"><i>' . locale_number_format($GPPercentLY, 1) . '%</i></td>
						</tr>
						<tr><td colspan="6">&nbsp;</td></tr>';
				}

				if (($Section != 1) and ($Section != 2)) {
					$HTML .= '<tr class="total_row">
							<td colspan="2"><h4><b>' . __('Profit') . ' - ' . __('Loss') . ' ' . __('after') . ' ' . $Sections[$Section] . '</b></h2></td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$PeriodProfitLossActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$PeriodProfitLossBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$PeriodProfitLossLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';

					if ($TotalIncomeActual != 0) {
						$NPPercentActual = (-$PeriodProfitLossActual) / $TotalIncomeActual * 100;
					}
					else {
						$NPPercentActual = 0;
					}
					if ($TotalIncomeBudget != 0) {
						$NPPercentBudget = (-$PeriodProfitLossBudget) / $TotalIncomeBudget * 100;
					}
					else {
						$NPPercentBudget = 0;
					}
					if ($TotalIncomeLY != 0) {
						$NPPercentLY = (-$PeriodProfitLossLY) / $TotalIncomeLY * 100;
					}
					else {
						$NPPercentLY = 0;
					}
					$HTML .= '<tr class="total_row">
							<td colspan="2"><h4><i>' . __('P/L Percent after') . ' ' . $Sections[$Section] . '</i></h4></td>
							<td>&nbsp;</td>
							<td class="number"><i>' . locale_number_format($NPPercentActual, 1) . '%</i></td>
							<td>&nbsp;</td>
							<td class="number"><i>' . locale_number_format($NPPercentBudget, 1) . '%</i></td>
							<td>&nbsp;</td>
							<td class="number"><i>' . locale_number_format($NPPercentLY, 1) . '%</i></td>
						</tr>
						<tr><td colspan="6">&nbsp;</td></tr>';
				}
			}
			$SectionPrdActual = 0;
			$SectionPrdBudget = 0;
			$SectionPrdLY = 0;
			$Section = $MyRow['sectionid'];
			if ($_POST['ShowDetail'] == 'Detailed') {
				$HTML .= '<tr>
						<td colspan="8"><h2><b>' . $Sections[$MyRow['sectionid']] . '</b></h2></td>
					</tr>';
			}
		}

		if ($MyRow['group_'] != $ActGrp) {
			if ($MyRow['parentgroupname'] == $ActGrp and $ActGrp != '') { //adding another level of nesting
				$Level++;
			}

			$ParentGroups[$Level] = $MyRow['group_'];
			$ActGrp = $MyRow['group_'];
			if ($_POST['ShowDetail'] == 'Detailed') {
				$HTML .= '<tr>
						<td colspan="8"><b>' . $MyRow['group_'] . '</b></td>
					</tr>';
			}
		}
		$AccountPeriodActual = $ThisYearActuals[$MyRow['accountcode']];
		$AccountPeriodBudget = $PeriodBudgetRow['periodbudget'];
		$AccountPeriodLY = $LastYearActuals[$MyRow['accountcode']];
		$PeriodProfitLossActual += $AccountPeriodActual;
		$PeriodProfitLossBudget += $AccountPeriodBudget;
		$PeriodProfitLossLY += $AccountPeriodLY;

		for ($i = 0;$i <= $Level;$i++) {
			if (!isset($GrpPrdActual[$i])) {
				$GrpPrdActual[$i] = 0;
			}
			$GrpPrdActual[$i] += $AccountPeriodActual;
			if (!isset($GrpPrdBudget[$i])) {
				$GrpPrdBudget[$i] = 0;
			}
			$GrpPrdBudget[$i] += $AccountPeriodBudget;
			if (!isset($GrpPrdLY[$i])) {
				$GrpPrdLY[$i] = 0;
			}
			$GrpPrdLY[$i] += $AccountPeriodLY;
		}
		$SectionPrdActual += $AccountPeriodActual;
		$SectionPrdBudget += $AccountPeriodBudget;
		$SectionPrdLY += $AccountPeriodLY;

		if ($_POST['ShowDetail'] == 'Detailed') {
			if (isset($_POST['ShowZeroBalance']) or (!isset($_POST['ShowZeroBalance']) and ($AccountPeriodActual <> 0 or $AccountPeriodBudget <> 0 or $AccountPeriodLY <> 0))) {
				$ActEnquiryURL = '<a href="' . $RootPath . '/GLAccountInquiry.php?PeriodFrom=' . urlencode($_POST['PeriodFrom']) . '&amp;PeriodTo=' . urlencode($_POST['PeriodTo']) . '&amp;Account=' . urlencode($MyRow['accountcode']) . '&amp;Show=Yes">' . $MyRow['accountcode'] . '</a>';
				if ($Section == 1) {
					$HTML .= '<tr class="striped_row">
							<td>' . $ActEnquiryURL . '</td>
							<td>' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$AccountPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$AccountPeriodBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$AccountPeriodLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
				}
				else {
					$HTML .= '<tr class="striped_row">
							<td>' . $ActEnquiryURL . '</td>
							<td>' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</td>
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
	}
	//end of loop


	if ($Section == 1) { /*Income */
		$HTML .= '<tr class="total_row">
							<td colspan="2"><h4><i>' . $ActGrp . '</i></h4></td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$GrpPrdLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
	}
	else { /*Costs */
		$HTML .= '<tr class="total_row">
							<td colspan="2"><h4><i>' . $ActGrp . '</i></h4></td>
							<td class="number">' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($GrpPrdLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
						</tr>';
	}
	$HTML .= '<tr class="total_row">
							<td colspan="2"><h2>' . $Sections[$Section] . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$SectionPrdBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format(-$SectionPrdLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';

	$HTML .= '<tr class="total_row">
			<td colspan="2"><h2><b>' . __('Profit') . ' - ' . __('Loss') . '</b></h2></td>
			<td>&nbsp;</td>
			<td class="number">' . locale_number_format(-$PeriodProfitLossActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td>&nbsp;</td>
			<td class="number">' . locale_number_format(-$PeriodProfitLossBudget, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td>&nbsp;</td>
			<td class="number">' . locale_number_format(-$PeriodProfitLossLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';

	if ($TotalIncomeActual != 0) {
		$NPPercentActual = (-$PeriodProfitLossActual) / $TotalIncomeActual * 100;
	}
	else {
		$NPPercentActual = 0;
	}
	if ($TotalIncomeBudget != 0) {
		$NPPercentBudget = (-$PeriodProfitLossBudget) / $TotalIncomeBudget * 100;
	}
	else {
		$NPPercentBudget = 0;
	}
	if ($TotalIncomeLY != 0) {
		$NPPercentLY = (-$PeriodProfitLossLY) / $TotalIncomeLY * 100;
	}
	else {
		$NPPercentLY = 0;
	}
	$HTML .= '<tr class="total_row">
				<td colspan="2"><h4><i>' . __('Net Profit Percent') . '</i></h4></td>
				<td>&nbsp;</td>
				<td class="number"><i>' . locale_number_format($NPPercentActual, 1) . '%</i></td>
				<td>&nbsp;</td>
				<td class="number"><i>' . locale_number_format($NPPercentBudget, 1) . '%</i></td>
				<td>&nbsp;</td>
				<td class="number"><i>' . locale_number_format($NPPercentLY, 1) . '%</i></td>
		</tr>
		<tr><td colspan="8">&nbsp;</td>
		</tr>
		</tbody></table></div>'; // div id="Report".


	$HTML .= '</table>';
	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</body></html>';
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_Profit_Loss_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
	}
	else {
		$Title = __('General Ledger Profit and Loss');
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/gl.png" title="' . __('Profit and Loss Report') . '" alt="" />
				' . __('Profit and Loss Report') . '
			</p>';
		echo $HTML;
		echo
		// Shows a form to select an action after the report was shown:
		'<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">', '<input name="FormID" type="hidden" value="' . $_SESSION['FormID'] . '" />',
		// Resend report parameters:
		'<input name="PeriodFrom" type="hidden" value="' . $_POST['PeriodFrom'] . '" />', '<input name="PeriodTo" type="hidden" value="' . $_POST['PeriodTo'] . '" />', '<div class="centre">
			<input type="submit" name="close" value="' . __('Close') . '" onclick="window.close()" />
		</div>' . '</form>';
	}

}
else {

	include('includes/header.php');

	echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/printer.png" title="' . // Icon image.
	$Title2 . '" /> ' . // Icon title.
	$Title . '</p>'; // Page title.
	fShowPageHelp( // Shows the page help text if $_SESSION['ShowFieldHelp'] is true or is not set
	__('Profit and loss statement (P&amp;L) . also called an Income Statement . or Statement of Operations . this is the statement that indicates how the revenue (money received from the sale of products and services before expenses are taken out . also known as the top line) is transformed into the net income (the result after all revenues and expenses have been accounted for . also known as the bottom line).') . '<br />' . __('The purpose of the income statement is to show whether the company made or lost money during the period being reported.') . '<br />' . __('The P&amp;L represents a period of time. This contrasts with the Balance Sheet . which represents a single moment in time.') . '<br />' . __('webERP is an accrual based system (not a cash based system). Accrual systems include items when they are invoiced to the customer . and when expenses are owed based on the supplier invoice date.')); // Function fShowPageHelp() in ~/includes/MiscFunctions.php
	echo
	// Shows a form to input the report parameters:
	'<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">', '<input name="FormID" type="hidden" value="' . $_SESSION['FormID'] . '" />',
	// Input table:
	'<fieldset>
			<legend>' . __('Report Criteria') . '</legend>',
	// Content of the body of the input table:
	// Select period from:
	'<field>
				<label for="PeriodFrom">' . __('Select period from') . '</label>
		 		<select id="PeriodFrom" name="PeriodFrom" required="required">';
	$Periods = DB_query('SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC');

	if (Date('m') > $_SESSION['YearEnd']) {
		/*Dates in SQL format */
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
	}
	else {
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y') - 1));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y') - 1));
	}

	$Period = GetPeriod($FromDate);

	while ($MyRow = DB_fetch_array($Periods)) {
		if (isset($_POST['PeriodFrom']) and $_POST['PeriodFrom'] != '') {
			if ($_POST['PeriodFrom'] == $MyRow['periodno']) {
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
			else {
				echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		}
		else {
			if ($MyRow['lastdate_in_period'] == $DefaultFromDate) {
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
			else {
				echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		}
	}

	echo '</select>
		<fieldhelp>' . __('Select the beginning of the reporting period') . '</fieldhelp>
	</field>';

	// Select period to:
	if (!isset($_POST['PeriodTo'])) {
		$PeriodSQL = "SELECT periodno
						FROM periods
						WHERE MONTH(lastdate_in_period) = MONTH(CURRENT_DATE())
						AND YEAR(lastdate_in_period ) = YEAR(CURRENT_DATE())";
		$PeriodResult = DB_query($PeriodSQL);
		$PeriodRow = DB_fetch_array($PeriodResult);
		$_POST['PeriodTo'] = $PeriodRow['periodno'];
	}
	echo '<field>
			<label for="PeriodTo">' . __('Select period to') . '</label>
		 	<select id="PeriodTo" name="PeriodTo" required="required">';
	DB_data_seek($Periods, 0);
	while ($MyRow = DB_fetch_array($Periods)) {
		echo '<option', ($MyRow['periodno'] == $_POST['PeriodTo'] ? ' selected="selected"' : '') . ' value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
	}
	echo '</select>
		<fieldhelp>' . __('Select the end of the reporting period') . '</fieldhelp>
	</field>';
	// OR Select period:
	if (!isset($_POST['Period'])) {
		$_POST['Period'] = '';
	}

	$SQL = "SELECT `id`,
					`name`,
					`current`
				FROM glbudgetheaders";
	$Result = DB_query($SQL);
	echo '<field>
			<label for="SelectedBudget">', __('Budget To Show Comparisons With'), '</label>
			<select name="SelectedBudget">';
	while ($MyRow = DB_fetch_array($Result)) {
		if (!isset($_POST['SelectedBudget']) and $MyRow['current'] == 1) {
			$_POST['SelectedBudget'] = $MyRow['id'];
		}
		if ($MyRow['id'] == $_POST['SelectedBudget']) {
			echo '<option selected="selected" value="', $MyRow['id'], '">', $MyRow['name'], '</option>';
		}
		else {
			echo '<option value="', $MyRow['id'], '">', $MyRow['name'], '</option>';
		}
	}
	echo '<fieldhelp>', __('Select the budget to make comparisons with.'), '</fieldhelp>
		</select>
	</field>';

	echo '<field>
			<label for="Period">' . '<b>' . __('OR') . ' </b>' . __('Select Period') . '</label>
			' . ReportPeriodList($_POST['Period'], array('l', 't')), '<fieldhelp>' . __('Select a period instead of using the beginning and end of the reporting period.') . '</fieldhelp>
		</field>';

	echo '<field>
			<label for="ShowDetail">' . __('Detail or summary') . '</label>
			<select name="ShowDetail">
				<option value="Summary">' . __('Summary') . '</option>
				<option selected="selected" value="Detailed">' . __('All Accounts') . '</option>
			</select>
		</field>',
	// Show accounts with zero balance:
	'<field>', '<label for="ShowZeroBalance">' . __('Show accounts with zero balance') . '</label>
		 	<input', (isset($_POST['ShowZeroBalance']) && $_POST['ShowZeroBalance'] ? ' checked="checked"' : '') . ' id="ShowZeroBalance" name="ShowZeroBalance" type="checkbox">
		 	<fieldhelp>' . __('Check this box to show all accounts including those with zero balance') . '</fieldhelp>
		</field>', '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" title="PDF" value="' . __('PDF P & L Account') . '" />
			<input type="submit" name="View" title="View" value="' . __('Show P & L Account') . '" />
		</div>', '</form>';

	include('includes/footer.php');
}
