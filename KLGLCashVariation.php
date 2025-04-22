<?php
/* $Id: GLCashFlowsIndirect.php 7672 2016-11-17 10:42:50Z rchacon $ */
/* Shows a statement of cash flows for the period using the indirect method. */
/* This program is under the GNU General Public License, last version. Rafael E. Chacón, 2016-10-08. */
/* This creative work is under the CC BY-NC-SA, later version. Rafael E. Chacón, 2016-10-08. */

// Notes:
// Coding Conventions/Style: http://www.weberp.org/CodingConventions.html
// Info about a statement of cash flows using the indirect method: IAS 7 - Statement of Cash Flows.

// BEGIN: Functions division ---------------------------------------------------
function CashFlowsActivityName($Activity) {
	// Converts the cash flow activity number to an activity text.
	switch ($Activity) {
		case -1: return _('Without setting up');
		case 0: return _('No effect on cash flow');
		case 1: return _('Operating activities (Net change in stock)');
		case 2: return _('Investing activities (Including depreciation)');
		case 3: return _('Financing activities');
		case 4: return _('Cash or cash equivalent');
		default: return _('Unknown');
	}
}

function colDebitCredit($Amount) {
	// Function to display in debit or Credit columns in a HTML table.
	if ($Amount < 0) {
		return '<td class="number">' . locale_number_format($Amount, $_SESSION['CompanyRecord']['decimalplaces']) . '</td><td>&nbsp;</td>'; // Outflow.
	} else {
		return '<td>&nbsp;</td><td class="number">' . locale_number_format($Amount, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>'; // Inflow.
	}
}

function colDebitCreditPercent($Amount) {
	// Function to display in debit or Credit columns in a HTML table.
	if ($Amount < 0) {
		return '<td class="number">' . locale_number_format($Amount, 1) . '%' . '</td><td>&nbsp;</td>'; // Outflow.
	} else {
		return '<td>&nbsp;</td><td class="number">' . locale_number_format($Amount, 1) . '%' . '</td>'; // Inflow.
	}
}
// END: Functions division -----------------------------------------------------

// BEGIN: Procedure division ---------------------------------------------------
include('includes/session.php');
$Title = _('KL Statement of Cash Flows, Cash Accounts Difference Method');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLCashFlowsIndirect';
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/UIGeneralFunctions.php'); // Added include
include('includes/KLUIGeneralFunctions.php'); // Added include

// Merges gets into posts:
if (isset($_GET['PeriodFrom'])) { // Select period from.
	$_POST['PeriodFrom'] = $_GET['PeriodFrom'];
}
if (isset($_GET['PeriodTo'])) { // Select period to.
	$_POST['PeriodTo'] = $_GET['PeriodTo'];
}
if (isset($_GET['ShowZeroBalance'])) { // Show accounts with zero balance.
	$_POST['ShowZeroBalance'] = $_GET['ShowZeroBalance'];
}
if (isset($_GET['ShowCash'])) { // Show cash accounts.
	$_POST['ShowCash'] = $_GET['ShowCash'];
}

// Validates the data submitted in the form:
if ($_POST['PeriodFrom'] > $_POST['PeriodTo']) {
	// The beginning is after the end.
	unset($_POST['PeriodFrom']);
	unset($_POST['PeriodTo']);
	prnMsg(_('The beginning of the period should be before or equal to the end of the period. Please reselect the reporting period.'), 'error');
}

if (isset($_POST['PeriodFrom']) && isset($_POST['PeriodTo']) && $_POST['Action'] != 'New') { // If all parameters are set and valid, generates the report:
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/reports.png" title="', // Icon image.
		$Title, '" /> ', // Icon title.
		$Title, '<br />', // Page title, reporting statement.
		stripslashes($_SESSION['CompanyRecord']['coyname']), '<br />'; // Page title, reporting entity.
	$Result = DB_query("SELECT lastdate_in_period FROM `periods` WHERE `periodno`=" . $_POST['PeriodFrom']);
	$PeriodFromName = DB_fetch_array($Result);
	$Result = DB_query("SELECT lastdate_in_period FROM `periods` WHERE `periodno`=" . $_POST['PeriodTo']);
	$PeriodToName = DB_fetch_array($Result);
	echo _('From'), ' ', MonthAndYearFromSQLDate($PeriodFromName['lastdate_in_period']),
		' ', _('to'), ' ', MonthAndYearFromSQLDate($PeriodToName['lastdate_in_period']), '<br />'; // Page title, reporting period.
	include_once('includes/CurrenciesArray.php'); // Array to retrieve currency name.
	echo _('All amounts stated in'), ': ', _($CurrencyName[$_SESSION['CompanyRecord']['currencydefault']]), '</p>'; // Page title, reporting presentation currency and level of rounding used.
	echo '<table class="selection">',
		// Content of the header and footer of the output table:
		'<thead>
			<tr>
				<th>', _('Account'), '</th>
				<th>', _('Account Name'), '</th>
				<th colspan="2">', _('Period Actual'), '</th>',
				'<th colspan="2">', _('Last Year Actual'), '</th>'; // Added Last Year Actual header
	// Initialise section accumulators:
	$ActualSection = 0;
	$ActualTotal = 0;
	$LYActualSection = 0; // Added Last Year accumulator
	$LYActualTotal = 0; // Added Last Year accumulator
	// Gets the net profit for the period GL account:
	if (!isset($_SESSION['PeriodProfitAccount'])) {
		$_SESSION['PeriodProfitAccount'] = '';
		$Result = DB_query("SELECT confvalue FROM `config` WHERE confname ='PeriodProfitAccount'");
		$MyRow = DB_fetch_array($Result);
		if ($MyRow) {
			$_SESSION['PeriodProfitAccount'] = $MyRow['confvalue'];
		}
	}
	// Gets the retained earnings GL account:
	if (!isset($_SESSION['RetainedEarningsAccount'])) {
		$_SESSION['RetainedEarningsAccount'] = '';
		$Result = DB_query("SELECT retainedearnings FROM companies WHERE coycode = 1");
		$MyRow = DB_fetch_array($Result);
		if ($MyRow) {
			$_SESSION['RetainedEarningsAccount'] = $MyRow['retainedearnings'];
		}
	}

	// Outputs the table:
	echo '</tr>
		</thead><tfoot>
			<tr>',
				'<td class="text" colspan="6">', // Prints an explanation of signs in actual and relative changes: // Adjusted colspan
					'<br /><b>', _('Notes'), ':</b><br />',
					_('Cash flows signs: a negative number indicates a cash flow used in activities; a positive number indicates a cash flow provided by activities.'), '<br />';
	if ($_POST['ShowCash']) {
		echo _('Cash signs: a negative number indicates a cash outflow; a positive number indicates a cash inflow.'), '<br />';
	}
	echo '</td>
			</tr>
		</tfoot><tbody>';
	if ($_POST['ShowCash']) {
		// Prints a detail of Cash at beginning of period (Parameters: PeriodFrom, PeriodTo, ShowZeroBalance=on/off, ShowCash=ON):
		echo '<tr><td colspan="6">&nbsp;</td></tr>'; // Adjusted colspan
		$ActualBeginning = 0;
		$LYActualBeginning = 0; // Added Last Year variable
		$SQL = "SELECT chartmaster.accountcode,
					chartmaster.accountname,
					(SELECT SUM(gltotals.amount)
						FROM gltotals
						WHERE gltotals.account = chartmaster.accountcode
						AND gltotals.period < '" . $_POST['PeriodFrom'] . "') AS ActualAmount,
					(SELECT SUM(gltotals.amount)
						FROM gltotals
						WHERE gltotals.account = chartmaster.accountcode
						AND gltotals.period < '" . ($_POST['PeriodFrom'] - 12) . "') AS LYActualAmount
				FROM chartmaster
					INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
				WHERE accountgroups.pandl = 0 AND chartmaster.cashflowsactivity = 4
				GROUP BY chartmaster.accountcode,
						 chartmaster.accountname
				ORDER BY chartmaster.accountcode";
		$Result = DB_query($SQL);
		foreach ($Result as $MyRow) {
			// Ensure ActualAmount is not null if no prior periods exist
			$MyRow['ActualAmount'] = $MyRow['ActualAmount'] ?? 0;
			$MyRow['LYActualAmount'] = $MyRow['LYActualAmount'] ?? 0; // Ensure LYActualAmount is not null
			if (ABS($MyRow['ActualAmount']) > 1 || ABS($MyRow['LYActualAmount']) > 1 || isset($_POST['ShowZeroBalance'])) { // Check LYActualAmount too
				echo '<tr class="striped_row">',
					'<td class="text"><a href="', $RootPath, '/GLAccountInquiry.php?Period=', $_POST['PeriodFrom'], '&amp;Account=', $MyRow['accountcode'], '">', $MyRow['accountcode'], '</a></td>',
					'<td class="text">', $MyRow['accountname'], '</td>',
					colDebitCredit($MyRow['ActualAmount']),
					colDebitCredit($MyRow['LYActualAmount']), // Added LY Actual cell
					'</tr>';
				$ActualBeginning += $MyRow['ActualAmount'];
				$LYActualBeginning += $MyRow['LYActualAmount']; // Accumulate LY Actual
			}
		}
	} else {
		// Prints a summary of Cash at beginning of period (Parameters: PeriodFrom, PeriodTo, ShowZeroBalance=on/off, ShowCash=OFF):
		$SQL = "SELECT SUM(CASE WHEN gltotals.period < '" . $_POST['PeriodFrom'] . "' THEN gltotals.amount ELSE 0 END) AS ActualAmount,
					   SUM(CASE WHEN gltotals.period < '" . ($_POST['PeriodFrom'] - 12) . "' THEN gltotals.amount ELSE 0 END) AS LYActualAmount
				FROM gltotals
					INNER JOIN chartmaster ON gltotals.account = chartmaster.accountcode
					INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
				WHERE accountgroups.pandl = 0
					AND chartmaster.cashflowsactivity = 4
					AND (gltotals.period < '" . $_POST['PeriodFrom'] . "' OR gltotals.period < '" . ($_POST['PeriodFrom'] - 12) . "')";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$ActualBeginning = $MyRow['ActualAmount'] ?? 0; // Ensure not null
		$LYActualBeginning = $MyRow['LYActualAmount'] ?? 0; // Ensure not null
	}
	echo '<tr>
			<td class="text" colspan="2"><b>', _('Cash at beginning of period'), '</b></td>',
			colDebitCredit($ActualBeginning),
			colDebitCredit($LYActualBeginning), // Added LY Actual cell
		'</tr>';
	$CashAtStartPeriod = $ActualBeginning;
	$LYCashAtStartPeriod = $LYActualBeginning; // Added LY variable

	if ($_POST['ShowCash']) {
		// Prints a detail of Cash at End of period (Parameters: PeriodFrom, PeriodTo, ShowZeroBalance=on/off, ShowCash=ON):
		echo '<tr><td colspan="6">&nbsp;</td></tr>'; // Adjusted colspan
		$ActualEnd = 0;
		$LYActualEnd = 0; // Added LY variable
		$SQL = "SELECT chartmaster.accountcode,
					chartmaster.accountname,
					(SELECT SUM(gltotals.amount)
						FROM gltotals
						WHERE gltotals.account = chartmaster.accountcode
						AND gltotals.period <= '" . $_POST['PeriodTo'] . "') AS ActualAmount,
					(SELECT SUM(gltotals.amount)
						FROM gltotals
						WHERE gltotals.account = chartmaster.accountcode
						AND gltotals.period <= '" . ($_POST['PeriodTo'] - 12) . "') AS LYActualAmount
				FROM chartmaster
					INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
				WHERE accountgroups.pandl = 0 AND chartmaster.cashflowsactivity = 4
				GROUP BY chartmaster.accountcode,
						 chartmaster.accountname
				ORDER BY chartmaster.accountcode";
		$Result = DB_query($SQL);
		foreach ($Result as $MyRow) {
			// Ensure ActualAmount is not null if no periods exist
			$MyRow['ActualAmount'] = $MyRow['ActualAmount'] ?? 0;
			$MyRow['LYActualAmount'] = $MyRow['LYActualAmount'] ?? 0; // Ensure LYActualAmount is not null
			if (ABS($MyRow['ActualAmount']) > 1 || ABS($MyRow['LYActualAmount']) > 1 || isset($_POST['ShowZeroBalance'])) { // Check LYActualAmount too
				echo '<tr class="striped_row">',
					'<td class="text"><a href="', $RootPath, '/GLAccountInquiry.php?Period=', $_POST['PeriodFrom'], '&amp;Account=', $MyRow['accountcode'], '">', $MyRow['accountcode'], '</a></td>',
					'<td class="text">', $MyRow['accountname'], '</td>',
					colDebitCredit($MyRow['ActualAmount']),
					colDebitCredit($MyRow['LYActualAmount']), // Added LY Actual cell
					'</tr>';
				$ActualEnd += $MyRow['ActualAmount'];
				$LYActualEnd += $MyRow['LYActualAmount']; // Accumulate LY Actual
			}
		}
	} else {
		// Prints a summary of Cash at End of period (Parameters: PeriodFrom, PeriodTo, ShowZeroBalance=on/off, ShowCash=OFF):
		$SQL = "SELECT SUM(CASE WHEN gltotals.period <= '" . $_POST['PeriodTo'] . "' THEN gltotals.amount ELSE 0 END) AS ActualAmount,
					   SUM(CASE WHEN gltotals.period <= '" . ($_POST['PeriodTo'] - 12) . "' THEN gltotals.amount ELSE 0 END) AS LYActualAmount
				FROM gltotals
					INNER JOIN chartmaster ON gltotals.account = chartmaster.accountcode
					INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
				WHERE accountgroups.pandl = 0
					AND chartmaster.cashflowsactivity = 4
					AND (gltotals.period <= '" . $_POST['PeriodTo'] . "' OR gltotals.period <= '" . ($_POST['PeriodTo'] - 12) . "')";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$ActualEnd = $MyRow['ActualAmount'] ?? 0; // Ensure not null
		$LYActualEnd = $MyRow['LYActualAmount'] ?? 0; // Ensure not null
	}
	echo '<tr>
			<td class="text" colspan="2"><b>', _('Cash at End of period'), '</b></td>',
			colDebitCredit($ActualEnd),
			colDebitCredit($LYActualEnd), // Added LY Actual cell
		'</tr>';

	$CashAtEndPeriod = $ActualEnd;
	$LYCashAtEndPeriod = $LYActualEnd; // Added LY variable
	$CashVariation = $CashAtEndPeriod - $CashAtStartPeriod;
	$LYCashVariation = $LYCashAtEndPeriod - $LYCashAtStartPeriod; // Added LY variable

	echo '<tr>
			<td class="text" colspan="2"><b>', _('Net Cash Flow'), '</b></td>',
			colDebitCredit($CashVariation),
			colDebitCredit($LYCashVariation), // Added LY Actual cell
		'</tr>';

	echo '<tr><td colspan="6">&nbsp;</td></tr>'; // Adjusted colspan
	$NumPeriods = $_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1;
	$MonthlyVariation = ($NumPeriods != 0) ? ROUND($CashVariation / $NumPeriods, 1) : 0; // Avoid division by zero
	$LYMonthlyVariation = ($NumPeriods != 0) ? ROUND($LYCashVariation / $NumPeriods, 1) : 0; // Added LY variable & check
	$MonthlyPercent = ($CashAtStartPeriod != 0) ? ROUND($MonthlyVariation / $CashAtStartPeriod * 100, 1) : 0; // Avoid division by zero
	$LYMonthlyPercent = ($LYCashAtStartPeriod != 0) ? ROUND($LYMonthlyVariation / $LYCashAtStartPeriod * 100, 1) : 0; // Added LY variable & check
	echo '<tr>
			<td class="text" colspan="2"><b>', _('Average monthly cash variation'), '</b></td>',
			colDebitCredit($MonthlyVariation),
			colDebitCredit($LYMonthlyVariation), // Added LY Actual cell
		'</tr>';
	echo '<tr>
			<td class="text" colspan="2"><b>', _('Average monthly cash variation in %'), '</b></td>',
			colDebitCreditPercent($MonthlyPercent),
			colDebitCreditPercent($LYMonthlyPercent), // Added LY Actual cell
		'</tr>';
	if ($CashVariation < 0) {
		echo '<tr>
				<td class="text" colspan="2"><b>', _('Cash needed to Shutdown'), '</b></td>',
				colDebitCredit(MINIMUM_SURVIVAL_CASH),
				'<td>&nbsp;</td><td>&nbsp;</td>', // Placeholder for LY
			'</tr>';
		$CashAvailable = round($CashAtEndPeriod - MINIMUM_SURVIVAL_CASH, 0, PHP_ROUND_HALF_DOWN);
		$LYCashAvailable = round($LYCashAtEndPeriod - MINIMUM_SURVIVAL_CASH, 0, PHP_ROUND_HALF_DOWN); // Added LY variable
		echo '<tr>
				<td class="text" colspan="2"><b>', _('Cash Still Available'), '</b></td>',
				colDebitCredit($CashAvailable),
				colDebitCredit($LYCashAvailable), // Added LY Actual cell
			'</tr>';
		$SurvivalMonths = ($MonthlyVariation != 0) ? -round($CashAvailable / $MonthlyVariation, 0, PHP_ROUND_HALF_DOWN) : 0; // Avoid division by zero
		$LYSurvivalMonths = ($LYMonthlyVariation != 0) ? -round($LYCashAvailable / $LYMonthlyVariation, 0, PHP_ROUND_HALF_DOWN) : 0; // Added LY variable & check
		echo '<tr>
				<td class="text" colspan="2"><b>', _('Cash survival in months'), '</b></td>',
				colDebitCredit($SurvivalMonths),
				colDebitCredit($LYSurvivalMonths), // Added LY Actual cell
			'</tr>';
	}

	echo '</tbody></table>',
		'<br />',
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
		'<input name="PeriodFrom" type="hidden" value="', $_POST['PeriodFrom'], '" />',
		'<input name="PeriodTo" type="hidden" value="', $_POST['PeriodTo'], '" />',
		'<input name="ShowDetail" type="hidden" value="', $_POST['ShowDetail'], '" />',
		'<input name="ShowZeroBalance" type="hidden" value="', $_POST['ShowZeroBalance'], '" />',
		'<input name="ShowCash" type="hidden" value="', $_POST['ShowCash'], '" />',
		'<div class="centre noprint">'; // Form buttons:
	if ($NeedSetup) { // Assuming $NeedSetup is defined elsewhere
		echo '<button onclick="javascript:window.location=\'GLCashFlowsSetup.php\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/maintenance.png" /> ', _('Run Setup'), '</button>'; // "Run Setup" button.
	}
	echo '<button onclick="javascript:window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
			'/images/printer.png" /> ', _('Print'), '</button>', // "Print" button.
		'<button name="Action" type="submit" value="New"><img alt="" src="', $RootPath, '/css/', $Theme,
			'/images/reports.png" /> ', _('New Report'), '</button>', // "New Report" button.
		'<button onclick="javascript:window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
			'/images/return.svg" /> ', _('Return'), '</button>', // "Return" button.
		'</div>';
} else { // If one or more parameters are NOT set or NOT valid, shows a parameters input form:
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/reports.png" title="', // Icon image.
		$Title, '" /> ', // Icon title.
		$Title, '</p>'; // Page title.
	if (!isset($_SESSION['ShowPageHelp']) || $_SESSION['ShowPageHelp']) { // If it is not set the $_SESSION['ShowPageHelp'] parameter OR it is TRUE, shows the page help text:
		echo '<div class="page_help_text">',
			_('The statement of cash flows, also known as the successor of the old source and application of funds statement, reports how changes in balance sheet accounts and income affect cash and cash equivalents, and breaks the analysis down to operating, investing and financing activities.'), '<br />',
			_('The purpose of the statement of cash flows is to show where the company got their money from and how it was spent during the period being reported for a user selectable range of periods.'), '<br />',
			_('The statement of cash flows represents a period of time. This contrasts with the statement of financial position, which represents a single moment in time.'), '<br />',
			_('webERP is an "accrual" based system (not a "cash based" system). Accrual systems include items when they are invoiced to the customer, and when expenses are owed based on the supplier invoice date.'),
			'</div>';
	}
	// Shows a form to allow input of criteria for the report to generate:
	echo '<br />',
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '"/>'; // Form's head.

	// Input fieldset:
	echo '<fieldset><legend>', _('Report Parameters'), '</legend>'; // Replaced table header

	// Select period from:
	if (!isset($_POST['PeriodFrom'])) {
		$BeginMonth = ($_SESSION['YearEnd'] == 12 ? 1 : $_SESSION['YearEnd'] + 1); // Sets January as the month that follows December.
		if ($BeginMonth <= date('n')) { // It is a month in the current year.
			$BeginDate = mktime(0, 0, 0, $BeginMonth, 1, date('Y'));
		} else { // It is a month in the previous year.
			$BeginDate = mktime(0, 0, 0, $BeginMonth, 1, date('Y') - 1);
		}
		$_POST['PeriodFrom'] = GetPeriod(date($_SESSION['DefaultDateFormat'], $BeginDate));
	}
	echo FieldToSelectOnePeriod('PeriodFrom',
								$_POST['PeriodFrom'],
								_('Select period from'),
								(!isset($_SESSION['ShowFieldHelp']) || $_SESSION['ShowFieldHelp'] ? _('Select the beginning of the reporting period') : ''),
								'', // Filter
								'', // TabIndex
								true, // Required
								false); // AutoFocus

	// Select period to:
	if (!isset($_POST['PeriodTo'])) {
		$_POST['PeriodTo'] = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	}
	echo FieldToSelectOnePeriod('PeriodTo',
								$_POST['PeriodTo'],
								_('Select period to'),
								(!isset($_SESSION['ShowFieldHelp']) || $_SESSION['ShowFieldHelp'] ? _('Select the end of the reporting period') : ''),
								'', // Filter
								'', // TabIndex
								true, // Required
								false); // AutoFocus

	// Show accounts with zero balance:
	echo FieldToSelectFromTwoOptions(1, _('Yes'),
									0, _('No'),
									'ShowZeroBalance',
									(isset($_POST['ShowZeroBalance']) && $_POST['ShowZeroBalance'] ? 1 : 0),
									_('Show accounts with zero balance'),
									(!isset($_SESSION['ShowFieldHelp']) || $_SESSION['ShowFieldHelp'] ? _('Check this box to show all accounts including those with zero balance') : ''),
									'', // Filter
									'', // TabIndex
									false, // Required
									false); // AutoFocus

	// Show cash accounts:
	echo FieldToSelectFromTwoOptions(1, _('Yes'),
									0, _('No'),
									'ShowCash',
									(isset($_POST['ShowCash']) && $_POST['ShowCash'] ? 1 : 0),
									_('Show cash accounts'),
									(!isset($_SESSION['ShowFieldHelp']) || $_SESSION['ShowFieldHelp'] ? _('Check this box to show cash accounts') : ''),
									'', // Filter
									'', // TabIndex
									false, // Required
									false); // AutoFocus

	echo '</fieldset>'; // Close fieldset

	// Replace submit buttons
	echo TwoButtonsCenteredForm('Submit', _('Submit'), 'Cancel', _('Return'), 'index.php?Application=GL');
}
echo '</form>';
include('includes/footer.php');
?>
