<?php
/*Through deviousness and cunning, this system allows trial balances for
 * any date range that recalcuates the p & l balances and shows the balance
 * sheets as at the end of the period selected - so first off need to show
 * the input of criteria screen
*/
$PageSecurity = 1;
include ('includes/session.php');

use Dompdf\Dompdf;

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$Title = _('Trial Balance');
include ('includes/SQL_CommonFunctions.php');
include ('includes/AccountSectionsDef.php'); //this reads in the Accounts Sections array
// Merges gets into posts:
if (isset($_GET['PeriodFrom'])) {
	$_POST['PeriodFrom'] = $_GET['PeriodFrom'];
}
if (isset($_GET['PeriodTo'])) {
	$_POST['PeriodTo'] = $_GET['PeriodTo'];
}
if (isset($_GET['Period'])) {
	$_POST['Period'] = $_GET['Period'];
}

if (isset($_POST['PeriodFrom']) and isset($_POST['PeriodTo']) and $_POST['PeriodFrom'] > $_POST['PeriodTo']) {

	prnMsg(_('The selected period from is actually after the period to! Please re-select the reporting period'), 'error');
	$_POST['NewReport'] = _('Select A Different Period');
}

if (isset($_POST['PrintPDF']) or isset($_POST['View']) or isset($_POST['Spreadsheet'])) {

	$PeriodToDate = MonthAndYearFromSQLDate(EndDateSQLFromPeriodNo($_POST['PeriodTo']));
	$NumberOfMonths = $_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1;

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">';
	$HTML .= '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$HTML .= '<input type="hidden" name="PeriodFrom" value="' . $_POST['PeriodFrom'] . '" />';
	$HTML .= '<input type="hidden" name="PeriodTo" value="' . $_POST['PeriodTo'] . '" />';

	$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<table>
					<thead>
						<tr>
							<th colspan="6">
								<b>' . _('Trial Balance for the month of ') . $PeriodToDate . _(' and for the ') . $NumberOfMonths . _(' months to ') . $PeriodToDate . '</b>
							</th>
						</tr>
						<tr>
							<th>' . _('Account') . '</th>
							<th>' . _('Account Name') . '</th>
							<th>' . _('Month Actual') . '</th>
							<th>' . _('Month Budget') . '</th>
							<th>' . _('Period Actual') . '</th>
							<th>' . _('Period Budget') . '</th>
						</tr>
					</thead>
					<tbody>';

	$ViewTopic = 'GeneralLedger';
	$BookMark = 'TrialBalance';

	if ($_POST['Period'] != '') {
		$_POST['PeriodFrom'] = ReportPeriod($_POST['Period'], 'From');
		$_POST['PeriodTo'] = ReportPeriod($_POST['Period'], 'To');
	}

	$NumberOfMonths = $_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1;

	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];

	/* Firstly get the account totals for this period */
	$ThisMonthSQL = "SELECT account,
							SUM(amount) AS monthtotal
						FROM gltotals
						WHERE period='" . $_POST['PeriodTo'] . "'
						GROUP BY account";
	$ThisMonthResult = DB_query($ThisMonthSQL);
	$ThisMonthArray = array();

	while ($ThisMonthRow = DB_fetch_array($ThisMonthResult)) {
		$ThisMonthArray[$ThisMonthRow['account']] = $ThisMonthRow['monthtotal'];
	}

	/* Then get this periods cumulative P&L accounts */
	$ThisPeriodPLSQL = "SELECT account,
								SUM(amount) AS periodtotal
						FROM gltotals
						INNER JOIN chartmaster
							ON gltotals.account=chartmaster.accountcode
						INNER JOIN accountgroups
							ON chartmaster.group_=accountgroups.groupname
						WHERE period<='" . $_POST['PeriodTo'] . "'
							AND period>='" . $_POST['PeriodFrom'] . "'
							AND pandl=1
						GROUP BY account";
	$ThisPeriodPLResult = DB_query($ThisPeriodPLSQL);
	$ThisPeriodArray = array();

	while ($ThisPeriodPLRow = DB_fetch_array($ThisPeriodPLResult)) {
		$ThisPeriodArray[$ThisPeriodPLRow['account']] = $ThisPeriodPLRow['periodtotal'];
	}

	/* Then get this periods cumulative BS accounts */
	$ThisPeriodBSSQL = "SELECT account,
								SUM(amount) AS periodtotal
						FROM gltotals
						INNER JOIN chartmaster
							ON gltotals.account=chartmaster.accountcode
						INNER JOIN accountgroups
							ON chartmaster.group_=accountgroups.groupname
						WHERE period<='" . $_POST['PeriodTo'] . "'
							AND pandl=0
						GROUP BY account";
	$ThisPeriodBSResult = DB_query($ThisPeriodBSSQL);

	while ($ThisPeriodBSRow = DB_fetch_array($ThisPeriodBSResult)) {
		$ThisPeriodArray[$ThisPeriodBSRow['account']] = $ThisPeriodBSRow['periodtotal'];
	}

	/* Get the retained earnings amount */
	$RetainedEarningsSQL = "SELECT SUM(amount) AS retainedearnings
							FROM gltotals
							INNER JOIN chartmaster
								ON gltotals.account=chartmaster.accountcode
							INNER JOIN accountgroups
								ON chartmaster.group_=accountgroups.groupname
							WHERE period<'" . $_POST['PeriodFrom'] . "'
								AND pandl=1";
	$RetainedEarningsResult = DB_query($RetainedEarningsSQL);
	$RetainedEarningsRow = DB_fetch_array($RetainedEarningsResult);

	// Get all account codes
	$SQL = "SELECT chartmaster.accountcode,
					chartmaster.group_,
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
			ORDER BY groupname,
					accountcode";
	$AccountListResult = DB_query($SQL);
	$AccountListRow = DB_fetch_array($AccountListResult);

	$HTML .= '<tr>
				<td></td>
			</tr>';
	$HTML .= '<tr class="total_row">
				<td>' . $AccountListRow['group_'] . '</td>
				<td colspan="6"></td>
			</tr>';

	$LastGroup = $AccountListRow['group_'];
	$LastGroupName = $AccountListRow['group_'];

	$SQL = "SELECT amount AS monthbudget
			FROM glbudgetdetails
			WHERE account='" . $AccountListRow['accountcode'] . "'
				AND period='" . $_POST['PeriodTo'] . "'
				AND headerid='" . $_POST['SelectedBudget'] . "'";
	$MonthBudgetResult = DB_query($SQL);
	$MonthBudgetRow = DB_fetch_array($MonthBudgetResult);
	if (!isset($MonthBudgetRow['monthbudget'])) {
		$MonthBudgetRow['monthbudget'] = 0;
	}

	$SQL = "SELECT SUM(amount) AS periodbudget
			FROM glbudgetdetails
			WHERE account='" . $AccountListRow['accountcode'] . "'
				AND period>='" . $_POST['PeriodFrom'] . "'
				AND period<='" . $_POST['PeriodTo'] . "'
				AND headerid='" . $_POST['SelectedBudget'] . "'";
	$PeriodBudgetResult = DB_query($SQL);
	$PeriodBudgetRow = DB_fetch_array($PeriodBudgetResult);
	if (!isset($PeriodBudgetRow['periodbudget'])) {
		$PeriodBudgetRow['periodbudget'] = 0;
	}

	if (!isset($ThisMonthArray[$AccountListRow['accountcode']])) {
		$ThisMonthArray[$AccountListRow['accountcode']] = 0;
	}
	if (!isset($ThisPeriodArray[$AccountListRow['accountcode']])) {
		$ThisPeriodArray[$AccountListRow['accountcode']] = 0;
	}

	$HTML .= '<tr class="striped_row">
				<td><a href="' . $RootPath . '/GLAccountInquiry.php?PeriodFrom=' . $_POST['PeriodFrom'] . '&amp;PeriodTo=' . $_POST['PeriodTo'] . '&amp;Account=' . $AccountListRow['accountcode'] . '&amp;Show=Yes">' . $AccountListRow['accountcode'] . '</a></td>
				<td>' . $AccountListRow['accountname'] . '</td>
				<td class="number">' . locale_number_format($ThisMonthArray[$AccountListRow['accountcode']], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MonthBudgetRow['monthbudget'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($ThisPeriodArray[$AccountListRow['accountcode']], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($PeriodBudgetRow['periodbudget'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';

	$MonthActualGroupTotal = $ThisMonthArray[$AccountListRow['accountcode']];
	$MonthBudgetGroupTotal = $MonthBudgetRow['monthbudget'];
	$PeriodActualGroupTotal = $ThisPeriodArray[$AccountListRow['accountcode']];
	$PeriodBudgetGroupTotal = $PeriodBudgetRow['periodbudget'];

	$CumulativeMonthActualGroupTotal = 0;
	$CumulativePeriodActualGroupTotal = 0;

	while ($AccountListRow = DB_fetch_array($AccountListResult)) {
		if (!isset($ThisMonthArray[$AccountListRow['accountcode']])) {
			$ThisMonthArray[$AccountListRow['accountcode']] = 0;
		}
		if (!isset($ThisPeriodArray[$AccountListRow['accountcode']])) {
			$ThisPeriodArray[$AccountListRow['accountcode']] = 0;
		}
		if ($_SESSION['CompanyRecord']['retainedearnings'] == $AccountListRow['accountcode']) {
			$ThisMonthArray[$AccountListRow['accountcode']] = 0;
			$ThisPeriodArray[$AccountListRow['accountcode']] = $RetainedEarningsRow['retainedearnings'];
		}
		if ($AccountListRow['group_'] != $LastGroup) {
			$HTML .= '<tr>
						<td></td>
					</tr>';
			$HTML .= '<tr class="total_row">
						<td>' . _('Total') . '</td>
						<td>' . $LastGroupName . '</td>
						<td class="number">' . locale_number_format($MonthActualGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($MonthBudgetGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($PeriodActualGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($PeriodBudgetGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					</tr>';
			$HTML .= '<tr>
						<td></td>
					</tr>';

			$HTML .= '<tr>
						<td></td>
					</tr>';
			$HTML .= '<tr class="total_row">
						<td>' . $AccountListRow['group_'] . '</td>
						<td colspan="6"></td>
					</tr>';

			$LastGroup = $AccountListRow['group_'];
			$LastGroupName = $AccountListRow['group_'];

			$CumulativeMonthActualGroupTotal+= $MonthActualGroupTotal;
			$CumulativePeriodActualGroupTotal+= $PeriodActualGroupTotal;

			$MonthActualGroupTotal = 0;
			$MonthBudgetGroupTotal = 0;
			$PeriodActualGroupTotal = 0;
			$PeriodBudgetGroupTotal = 0;

		}

		$SQL = "SELECT amount AS monthbudget
				FROM glbudgetdetails
				WHERE account='" . $AccountListRow['accountcode'] . "'
					AND period='" . $_POST['PeriodTo'] . "'
					AND headerid='" . $_POST['SelectedBudget'] . "'";
		$MonthBudgetResult = DB_query($SQL);
		$MonthBudgetRow = DB_fetch_array($MonthBudgetResult);
		if (!isset($MonthBudgetRow['monthbudget'])) {
			$MonthBudgetRow['monthbudget'] = 0;
		}

		$SQL = "SELECT SUM(amount) AS periodbudget
				FROM glbudgetdetails
				WHERE account='" . $AccountListRow['accountcode'] . "'
					AND period>='" . $_POST['PeriodFrom'] . "'
					AND period<='" . $_POST['PeriodTo'] . "'
					AND headerid='" . $_POST['SelectedBudget'] . "'";
		$PeriodBudgetResult = DB_query($SQL);
		$PeriodBudgetRow = DB_fetch_array($PeriodBudgetResult);
		if (!isset($PeriodBudgetRow['periodbudget'])) {
			$PeriodBudgetRow['periodbudget'] = 0;
		}

		$HTML .= '<tr class="striped_row">
					<td><a href="' . $RootPath . '/GLAccountInquiry.php?PeriodFrom=' . $_POST['PeriodFrom'] . '&amp;PeriodTo=' . $_POST['PeriodTo'] . '&amp;Account=' . $AccountListRow['accountcode'] . '&amp;Show=Yes">' . $AccountListRow['accountcode'] . '</a></td>
					<td>' . $AccountListRow['accountname'] . '</td>
					<td class="number">' . locale_number_format($ThisMonthArray[$AccountListRow['accountcode']], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($MonthBudgetRow['monthbudget'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($ThisPeriodArray[$AccountListRow['accountcode']], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($PeriodBudgetRow['periodbudget'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';
		$MonthActualGroupTotal+= $ThisMonthArray[$AccountListRow['accountcode']];
		$MonthBudgetGroupTotal+= $MonthBudgetRow['monthbudget'];
		$PeriodActualGroupTotal+= $ThisPeriodArray[$AccountListRow['accountcode']];
		$PeriodBudgetGroupTotal+= $PeriodBudgetRow['periodbudget'];
	}
	$HTML .= '<tr>
				<td></td>
			</tr>';
	$HTML .= '<tr class="total_row">
				<td>' . _('Total') . '</td>
				<td>' . $LastGroupName . '</td>
				<td class="number">' . locale_number_format($MonthActualGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MonthBudgetGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($PeriodActualGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($PeriodBudgetGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';
	$HTML .= '<tr>
				<td></td>
			</tr>';

	$CumulativeMonthActualGroupTotal+= $MonthActualGroupTotal;
	$CumulativePeriodActualGroupTotal+= $PeriodActualGroupTotal;

	$HTML .= '<tr>
				<td></td>
			</tr>';
	$HTML .= '<tr class="total_row">
				<td>' . _('Check Totals') . '</td>
				<td></td>
				<td class="number">' . locale_number_format($CumulativeMonthActualGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number"></td>
				<td class="number">' . locale_number_format($CumulativePeriodActualGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number"></td>
			</tr>';
	$HTML .= '<tr>
				<td></td>
			</tr>';

	$HTML .= '</table>';

	$HTML .= '</form>';
	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	} else {
		$HTML .= '</tbody>
				</table>
				<div class="centre">
					<form><input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body>
		</html>';

	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_GLTrialBalance_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} elseif (isset($_POST['Spreadsheet'])) {
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

		$File = 'GLTrialBalance-' . Date('Y-m-d'). '.' . 'ods';

		header('Content-Disposition: attachment;filename="' . $File . '"');
		header('Cache-Control: max-age=0');
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
		$spreadsheet = $reader->loadFromString($HTML);

		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Ods');
		$writer->save('php://output');
	} else {
		$Title = _('General Ledger Trial Balance');
		include ('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/gl.png" title="' . _('Trial Balance Report') . '" alt="" />
				' . _('Trial Balance Report') . '
			</p>';
		echo $HTML;
		include ('includes/footer.php');
	}

} else {

	$ViewTopic = 'GeneralLedger';
	$BookMark = 'TrialBalance';
	include ('includes/header.php');
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/gl.png" title="', _('Trial Balance'), '" alt="', _('Print'), '" />', ' ', _('Trial Balance Report'), '
		</p>';
	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" target="_blank">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (Date('m') > $_SESSION['YearEnd']) {
		/*Dates in SQL format */
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
	} else {
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y') - 1));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y') - 1));
	}
	/*GetPeriod function creates periods if need be the return value is not used */
	$NotUsedPeriodNo = GetPeriod($FromDate);

	/*Show a form to allow input of criteria for TB to show */
	echo '<fieldset>
			<legend>', _('Input criteria for Trial Balance'), '</legend>
			<field>
				<label for="PeriodFrom">', _('Select Period From'), ':</label>
				<select name="PeriodFrom" autofocus="autofocus">';
	$NextYear = date('Y-m-d', strtotime('+1 Year'));
	$SQL = "SELECT periodno,
					lastdate_in_period
				FROM periods
				WHERE lastdate_in_period < '" . $NextYear . "'
				ORDER BY periodno DESC";
	$Periods = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Periods)) {
		if (isset($_POST['PeriodFrom']) and $_POST['PeriodFrom'] != '') {
			if ($_POST['PeriodFrom'] == $MyRow['periodno']) {
				echo '<option selected="selected" value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
			} else {
				echo '<option value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
			}
		} else {
			if ($MyRow['lastdate_in_period'] == $DefaultFromDate) {
				echo '<option selected="selected" value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
			} else {
				echo '<option value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
			}
		}
	}
	echo '</select>
		<fieldhelp>', _('Select the starting period for this report'), '</fieldhelp>
	</field>';

	if (!isset($_POST['PeriodTo']) or $_POST['PeriodTo'] == '') {
		$DefaultPeriodTo = GetPeriod(date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m') + 1, 0, Date('Y'))));
	} else {
		$DefaultPeriodTo = $_POST['PeriodTo'];
	}

	echo '<field>
			<label for="PeriodTo">', _('Select Period To'), ':</label>
			<select name="PeriodTo">';

	DB_data_seek($Periods, 0);

	while ($MyRow = DB_fetch_array($Periods)) {

		if ($MyRow['periodno'] == $DefaultPeriodTo) {
			echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value ="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
	}
	echo '</select>
		<fieldhelp>', _('Select the end period for this report'), '</fieldhelp>
	</field>';

	echo '<h3>', _('OR'), '</h3>';

	if (!isset($_POST['Period'])) {
		$_POST['Period'] = '';
	}

	echo '<field>
			<label for="Period">', _('Select Period'), ':</label>
			', ReportPeriodList($_POST['Period'], array('l', 't')), '
			<fieldhelp>', _('Select a predefined period from this list. If a selection is made here it will override anything selected in the From and To options above.'), '</fieldhelp>
		</field>';

	$SQL = "SELECT `id`,
					`name`,
					`current`
				FROM glbudgetheaders";
	$Result = DB_query($SQL);
	echo '<field>
			<label for="SelectedBudget">', _('Budget To Show Comparisons With'), '</label>
			<select name="SelectedBudget">';
	while ($MyRow = DB_fetch_array($Result)) {
		if (!isset($_POST['SelectedBudget']) and $MyRow['current'] == 1) {
			$_POST['SelectedBudget'] = $MyRow['id'];
		}
		if ($MyRow['id'] == $_POST['SelectedBudget']) {
			echo '<option selected="selected" value="', $MyRow['id'], '">', $MyRow['name'], '</option>';
		} else {
			echo '<option value="', $MyRow['id'], '">', $MyRow['name'], '</option>';
		}
	}
	echo '<fieldhelp>', _('Select the budget to make comparisons with.'), '</fieldhelp>
		</select>
	</field>';

	echo '</fieldset>';

	echo '<div class="centre">
				<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . _('Print PDF') . '" />
				<input type="submit" name="View" title="View Report" value="' . _('View') . '" />
				<input type="submit" name="Spreadsheet" title="Spreadsheet" value="' . _('Spreadsheet') . '" />
		</div>';

	echo '</form>';
	include ('includes/footer.php');
}
