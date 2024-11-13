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
	switch($Activity) {
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
	if($Amount < 0) {
		return '<td class="number">' . locale_number_format($Amount, $_SESSION['CompanyRecord']['decimalplaces']) . '</td><td>&nbsp;</td>';// Outflow.
	} else {
		return '<td>&nbsp;</td><td class="number">' . locale_number_format($Amount, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>';// Inflow.
	}
}
function colDebitCreditPercent($Amount) {
	// Function to display in debit or Credit columns in a HTML table.
	if($Amount < 0) {
		return '<td class="number">' . locale_number_format($Amount, 1) .'%' . '</td><td>&nbsp;</td>';// Outflow.
	} else {
		return '<td>&nbsp;</td><td class="number">' . locale_number_format($Amount, 1) .'%' . '</td>';// Inflow.
	}
}// END: Functions division -----------------------------------------------------

// BEGIN: Procedure division ---------------------------------------------------
include('includes/session.php');
$Title = _('KL Statement of Cash Flows, Cash Accounts Difference Method');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLCashFlowsIndirect';
include('includes/header.php');
include('includes/KLDefines.php');

// Merges gets into posts:
if(isset($_GET['PeriodFrom'])) {// Select period from.
	$_POST['PeriodFrom'] = $_GET['PeriodFrom'];
}
if(isset($_GET['PeriodTo'])) {// Select period to.
	$_POST['PeriodTo'] = $_GET['PeriodTo'];
}
if(isset($_GET['ShowZeroBalance'])) {// Show accounts with zero balance.
	$_POST['ShowZeroBalance'] = $_GET['ShowZeroBalance'];
}
if(isset($_GET['ShowCash'])) {// Show cash accounts.
	$_POST['ShowCash'] = $_GET['ShowCash'];
}

// Validates the data submitted in the form:
if($_POST['PeriodFrom'] > $_POST['PeriodTo']) {
	// The beginning is after the end.
	unset($_POST['PeriodFrom']);
	unset($_POST['PeriodTo']);
	prnMsg(_('The beginning of the period should be before or equal to the end of the period. Please reselect the reporting period.'), 'error');
}

/*if($_POST['PeriodTo']-$_POST['PeriodFrom']+1 > 12) {
	// The reporting period is greater than 12 months.
	unset($_POST['PeriodFrom']);
	unset($_POST['PeriodTo']);
	prnMsg(_('The period should be 12 months or less in duration. Please select an alternative period range.'), 'error');
}*/

// Main code:
if(isset($_POST['PeriodFrom']) AND isset($_POST['PeriodTo']) AND $_POST['Action']!='New') {// If all parameters are set and valid, generates the report:
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/reports.png" title="', // Icon image.
		$Title, '" /> ', // Icon title.
		$Title, '<br />', // Page title, reporting statement.
		stripslashes($_SESSION['CompanyRecord']['coyname']), '<br />'; // Page title, reporting entity.
	$Result = DB_query('SELECT lastdate_in_period FROM `periods` WHERE `periodno`=' . $_POST['PeriodFrom']);
	$PeriodFromName = DB_fetch_array($Result);
	$Result = DB_query('SELECT lastdate_in_period FROM `periods` WHERE `periodno`=' . $_POST['PeriodTo']);
	$PeriodToName = DB_fetch_array($Result);
	echo _('From'), ' ', MonthAndYearFromSQLDate($PeriodFromName['lastdate_in_period']), ' ', _('to'), ' ', MonthAndYearFromSQLDate($PeriodToName['lastdate_in_period']), '<br />'; // Page title, reporting period.
	include_once('includes/CurrenciesArray.php');// Array to retrieve currency name.
	echo _('All amounts stated in'), ': ', _($CurrencyName[$_SESSION['CompanyRecord']['currencydefault']]), '</p>';// Page title, reporting presentation currency and level of rounding used.
	echo '<table class="selection">',
		// Content of the header and footer of the output table:
		'<thead>
			<tr>
				<th>', _('Account'), '</th>
				<th>', _('Account Name'), '</th>
				<th colspan="2">', _('Period Actual'), '</th>';
	// Initialise section accumulators:
	$ActualSection = 0;
	$ActualTotal = 0;
	$LastSection = 0;
	$LastTotal = 0;
	$k = 1;// Lines counter.
	// Gets the net profit for the period GL account:
	if(!isset($_SESSION['PeriodProfitAccount'])) {
		$_SESSION['PeriodProfitAccount'] = '';
		$Result = DB_query("SELECT confvalue FROM `config` WHERE confname ='PeriodProfitAccount'");
		$MyRow = DB_fetch_array($Result);
		if($MyRow) {
			$_SESSION['PeriodProfitAccount'] = $MyRow['confvalue'];
		}
	}
	// Gets the retained earnings GL account:
	if(!isset($_SESSION['RetainedEarningsAccount'])) {
		$_SESSION['RetainedEarningsAccount'] = '';
		$Result = DB_query("SELECT retainedearnings FROM companies WHERE coycode = 1");
		$MyRow = DB_fetch_array($Result);
		if($MyRow) {
			$_SESSION['RetainedEarningsAccount'] = $MyRow['retainedearnings'];
		}
	}
	include('includes/GLPostings.inc');// Posts pending GL transactions.
	// Outputs the table:
	echo		'<th colspan="2">', _('Period Budget'), '</th>',
				'<th colspan="2">', _('Last Year'), '</th>
			</tr>
		</thead><tfoot>
			<tr>',
				'<td class="text" colspan="8">',// Prints an explanation of signs in actual and relative changes:
					'<br /><b>', _('Notes'), ':</b><br />',
					_('Cash flows signs: a negative number indicates a cash flow used in activities; a positive number indicates a cash flow provided by activities.'), '<br />';
	if($_POST['ShowCash']) {
		echo		_('Cash signs: a negative number indicates a cash outflow; a positive number indicates a cash inflow.'), '<br />';
	}
	echo		'</td>
			</tr>
		</tfoot><tbody>';
	if($_POST['ShowCash']) {
		// Prints a detail of Cash at beginning of period (Parameters: PeriodFrom, PeriodTo, ShowZeroBalance=on/off, ShowCash=ON):
		echo '<tr><td colspan="8">&nbsp;</td></tr>';
		$ActualBeginning = 0;
		$BudgetBeginning = 0;
		$LastBeginning = 0;
		$Sql = "SELECT
					chartdetails.accountcode,
					chartmaster.accountname,
					Sum(CASE WHEN (chartdetails.period = '" . ($_POST['PeriodFrom']) . "') THEN chartdetails.bfwd ELSE 0 END) AS ActualAmount,
					Sum(CASE WHEN (chartdetails.period = '" . ($_POST['PeriodFrom']) . "') THEN chartdetails.bfwdbudget ELSE 0 END) AS BudgetAmount,
					Sum(CASE WHEN (chartdetails.period = '" . ($_POST['PeriodFrom']-12) . "') THEN chartdetails.bfwd ELSE 0 END) AS LastAmount
				FROM chartmaster
					INNER JOIN chartdetails ON chartmaster.accountcode=chartdetails.accountcode
					INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
				WHERE accountgroups.pandl=0 AND chartmaster.cashflowsactivity=4
				GROUP BY chartdetails.accountcode
				ORDER BY chartdetails.accountcode";
		$Result = DB_query($Sql);
		foreach($Result as $MyRow) {
		if(ABS($MyRow['ActualAmount'])> 1
			OR ABS($MyRow['BudgetAmount'])> 1
			OR ABS($MyRow['LastAmount'])> 1 OR isset($_POST['ShowZeroBalance'])) {
				if($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo	'<td class="text"><a href="', $RootPath, '/GLAccountInquiry.php?Period=', $_POST['PeriodFrom'], '&amp;Account=', $MyRow['accountcode'], '">', $MyRow['accountcode'], '</a></td>',
						'<td class="text">', $MyRow['accountname'], '</td>',
						colDebitCredit($MyRow['ActualAmount']),
						colDebitCredit($MyRow['BudgetAmount']),
						colDebitCredit($MyRow['LastAmount']),
					'</tr>';
				$ActualBeginning += $MyRow['ActualAmount'];
				$BudgetBeginning += $MyRow['BudgetAmount'];
				$LastBeginning += $MyRow['LastAmount'];
			}
		}
	} else {
		// Prints a summary of Cash at beginning of period (Parameters: PeriodFrom, PeriodTo, ShowZeroBalance=on/off, ShowCash=OFF):
		$Sql = "SELECT
					Sum(CASE WHEN (chartdetails.period = '" . ($_POST['PeriodFrom']) . "') THEN chartdetails.bfwd ELSE 0 END) AS ActualAmount,
					Sum(CASE WHEN (chartdetails.period = '" . ($_POST['PeriodFrom']) . "') THEN chartdetails.bfwdbudget ELSE 0 END) AS BudgetAmount,
					Sum(CASE WHEN (chartdetails.period = '" . ($_POST['PeriodFrom']-12) . "') THEN chartdetails.bfwd ELSE 0 END) AS LastAmount
				FROM chartmaster
					INNER JOIN chartdetails ON chartmaster.accountcode=chartdetails.accountcode
					INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
				WHERE accountgroups.pandl=0 AND chartmaster.cashflowsactivity=4";
		$Result = DB_query($Sql);
		$MyRow = DB_fetch_array($Result);
		$ActualBeginning = $MyRow['ActualAmount'];
		$BudgetBeginning = $MyRow['BudgetAmount'];
		$LastBeginning = $MyRow['LastAmount'];
	}
	echo '<tr>
			<td class="text" colspan="2"><b>', _('Cash at beginning of period'), '</b></td>',
			colDebitCredit($ActualBeginning),
			colDebitCredit($BudgetBeginning),
			colDebitCredit($LastBeginning),
		'</tr>';
	$CashAtStartPeriod = $ActualBeginning;

	if($_POST['ShowCash']) {
		// Prints a detail of Cash at End of period (Parameters: PeriodFrom, PeriodTo, ShowZeroBalance=on/off, ShowCash=ON):
		echo '<tr><td colspan="8">&nbsp;</td></tr>';
		$ActualEnd = 0;
		$BudgetEnd = 0;
		$LastEnd = 0;
		$Sql = "SELECT
					chartdetails.accountcode,
					chartmaster.accountname,
					Sum(CASE WHEN (chartdetails.period = '" . ($_POST['PeriodTo']) . "') THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS ActualAmount,
					Sum(CASE WHEN (chartdetails.period = '" . ($_POST['PeriodTo']) . "') THEN chartdetails.bfwdbudget ELSE 0 END) AS BudgetAmount,
					Sum(CASE WHEN (chartdetails.period = '" . ($_POST['PeriodTo']-12) . "') THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS LastAmount
				FROM chartmaster
					INNER JOIN chartdetails ON chartmaster.accountcode=chartdetails.accountcode
					INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
				WHERE accountgroups.pandl=0 AND chartmaster.cashflowsactivity=4
				GROUP BY chartdetails.accountcode
				ORDER BY chartdetails.accountcode";
		$Result = DB_query($Sql);
		foreach($Result as $MyRow) {
		if(ABS($MyRow['ActualAmount'])> 1
			OR ABS($MyRow['BudgetAmount'])> 1
			OR ABS($MyRow['LastAmount'])> 1 OR isset($_POST['ShowZeroBalance'])) {
				if($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo	'<td class="text"><a href="', $RootPath, '/GLAccountInquiry.php?Period=', $_POST['PeriodFrom'], '&amp;Account=', $MyRow['accountcode'], '">', $MyRow['accountcode'], '</a></td>',
						'<td class="text">', $MyRow['accountname'], '</td>',
						colDebitCredit($MyRow['ActualAmount']),
						colDebitCredit($MyRow['BudgetAmount']),
						colDebitCredit($MyRow['LastAmount']),
					'</tr>';
				$ActualEnd += $MyRow['ActualAmount'];
				$BudgetEnd += $MyRow['BudgetAmount'];
				$LastEnd += $MyRow['LastAmount'];
			}
		}
	} else {
		// Prints a summary of Cash at End of period (Parameters: PeriodFrom, PeriodTo, ShowZeroBalance=on/off, ShowCash=OFF):
		$Sql = "SELECT
					Sum(CASE WHEN (chartdetails.period = '" . ($_POST['PeriodTo']) . "') THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS ActualAmount,
					Sum(CASE WHEN (chartdetails.period = '" . ($_POST['PeriodTo']) . "') THEN chartdetails.bfwdbudget ELSE 0 END) AS BudgetAmount,
					Sum(CASE WHEN (chartdetails.period = '" . ($_POST['PeriodTo']-12) . "') THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS LastAmount
				FROM chartmaster
					INNER JOIN chartdetails ON chartmaster.accountcode=chartdetails.accountcode
					INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
				WHERE accountgroups.pandl=0 AND chartmaster.cashflowsactivity=4";
		$Result = DB_query($Sql);
		$MyRow = DB_fetch_array($Result);
		$ActualEnd = $MyRow['ActualAmount'];
		$BudgetEnd = $MyRow['BudgetAmount'];
		$LastEnd = $MyRow['LastAmount'];
	}
	echo '<tr>
			<td class="text" colspan="2"><b>', _('Cash at End of period'), '</b></td>',
			colDebitCredit($ActualEnd),
			colDebitCredit($BudgetEnd),
			colDebitCredit($LastEnd),
		'</tr>';

	$CashAtEndPeriod = $ActualEnd;
	$CashVariation = $CashAtEndPeriod - $CashAtStartPeriod;

	echo '<tr>
			<td class="text" colspan="2"><b>', _('Cash Flow'), '</b></td>',
			colDebitCredit($CashVariation),
			colDebitCredit(0),
			colDebitCredit(0),
		'</tr>';

	echo '<tr><td colspan="8">&nbsp;</td></tr>';
	$NumPeriods = $_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1;
	$MonthlyVariation = ROUND($CashVariation / $NumPeriods, 1);
	$MonthlyPercent = ROUND($MonthlyVariation / $CashAtStartPeriod * 100, 1);
	echo '<tr>
			<td class="text" colspan="2"><b>', _('Average monthly cash variation'), '</b></td>',
			colDebitCredit($MonthlyVariation),
			colDebitCredit(0),
			colDebitCredit(0),
		'</tr>';
	echo '<tr>
			<td class="text" colspan="2"><b>', _('Average monthly cash variation in %'), '</b></td>',
			colDebitCreditPercent($MonthlyPercent),
			colDebitCredit(0),
			colDebitCredit(0),
		'</tr>';
	if ($CashVariation < 0){
		echo '<tr>
				<td class="text" colspan="2"><b>', _('Cash needed to Shutdown'), '</b></td>',
				colDebitCredit(MINIMUM_SURVIVAL_CASH),
				colDebitCredit(0),
				colDebitCredit(0),
			'</tr>';
		$CashAvailable = round($CashAtEndPeriod - MINIMUM_SURVIVAL_CASH, 0, PHP_ROUND_HALF_DOWN);
		echo '<tr>
				<td class="text" colspan="2"><b>', _('Cash Still Available'), '</b></td>',
				colDebitCredit($CashAvailable),
				colDebitCredit(0),
				colDebitCredit(0),
			'</tr>';
		$SurvivalMonths = -round($CashAvailable / $MonthlyVariation, 0, PHP_ROUND_HALF_DOWN);
		echo '<tr>
				<td class="text" colspan="2"><b>', _('Cash survival in months'), '</b></td>',
				colDebitCredit($SurvivalMonths),
				colDebitCredit(0),
				colDebitCredit(0),
			'</tr>';
	}

	echo '</tbody></table>',
		'<br />',
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
		'<input name="PeriodFrom" type="hidden" value="', $_POST['PeriodFrom'], '" />',
		'<input name="PeriodTo" type="hidden" value="', $_POST['PeriodTo'], '" />',
		'<input name="ShowDetail" type="hidden" value="', $_POST['ShowDetail'], '" />',
		'<input name="ShowZeroBalance" type="hidden" value="', $_POST['ShowZeroBalance'], '" />',
		'<input name="ShowCash" type="hidden" value="', $_POST['ShowCash'], '" />',
		'<div class="centre noprint">'; // Form buttons:
	if($NeedSetup) {
		echo '<button onclick="javascript:window.location=\'GLCashFlowsSetup.php\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/maintenance.png" /> ', _('Run Setup'), '</button>'; // "Run Setup" button.
	}
	echo	'<button onclick="javascript:window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/printer.png" /> ', _('Print'), '</button>', // "Print" button.
			'<button name="Action" type="submit" value="New"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/reports.png" /> ', _('New Report'), '</button>', // "New Report" button.
			'<button onclick="javascript:window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/return.svg" /> ', _('Return'), '</button>', // "Return" button.
		'</div>';
} else {// If one or more parameters are NOT set or NOT valid, shows a parameters input form:
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/reports.png" title="', // Icon image.
		$Title, '" /> ', // Icon title.
		$Title, '</p>';// Page title.
	if(!isset($_SESSION['ShowPageHelp']) OR $_SESSION['ShowPageHelp']) {// If it is not set the $_SESSION['ShowPageHelp'] parameter OR it is TRUE, shows the page help text:
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
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '"/>', // Form's head.
		// Input table:
		'<table class="selection">',
		// Content of the header and footer of the input table:
		'<thead>
			<tr>
				<th colspan="2">', _('Report Parameters'), '</th>
			</tr>
		</thead><tfoot>
			<tr>
				<td colspan="2">',
					'<div class="centre">',
						'<button name="Submit" type="submit" value="', _('Submit'), '"><img alt="" src="', $RootPath, '/css/', $Theme,
							'/images/tick.svg" /> ', _('Submit'), '</button>', // "Submit" button.
						'<button onclick="window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
							'/images/return.svg" /> ', _('Return'), '</button>', // "Return" button.
					'</div>',
				'</td>
			</tr>
		</tfoot><tbody>',
		// Content of the body of the input table:
			// Select period from:
			'<tr>',
				'<td><label for="PeriodFrom">', _('Select period from'), '</label></td>
		 		<td><select id="PeriodFrom" name="PeriodFrom" required="required">';
	$Periods = DB_query('SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno ASC');
	if(!isset($_POST['PeriodFrom'])) {
		$BeginMonth = ($_SESSION['YearEnd']==12 ? 1 : $_SESSION['YearEnd']+1);// Sets January as the month that follows December.
		if($BeginMonth <= date('n')) {// It is a month in the current year.
			$BeginDate = mktime(0, 0, 0, $BeginMonth, 1, date('Y'));
		} else {// It is a month in the previous year.
			$BeginDate = mktime(0, 0, 0, $BeginMonth, 1, date('Y')-1);
		}
		$_POST['PeriodFrom'] = GetPeriod(date($_SESSION['DefaultDateFormat'], $BeginDate));
	}
	foreach($Periods as $MyRow) {
	    echo			'<option',($MyRow['periodno'] == $_POST['PeriodFrom'] ? ' selected="selected"' : '' ), ' value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
	}
	echo			'</select>',
					(!isset($_SESSION['ShowFieldHelp']) || $_SESSION['ShowFieldHelp'] ? _('Select the beginning of the reporting period') : ''), // If it is not set the $_SESSION['ShowFieldHelp'] parameter OR it is TRUE, shows the page help text.
		 		'</td>
			</tr>',
			// Select period to:
			'<tr>',
				'<td><label for="PeriodTo">', _('Select period to'), '</label></td>
		 		<td><select id="PeriodTo" name="PeriodTo" required="required">';
	if(!isset($_POST['PeriodTo'])) {
		$_POST['PeriodTo'] = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	}
	foreach($Periods as $MyRow) {
	    echo			'<option',($MyRow['periodno'] == $_POST['PeriodTo'] ? ' selected="selected"' : '' ), ' value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
	}
	echo			'</select>',
					(!isset($_SESSION['ShowFieldHelp']) || $_SESSION['ShowFieldHelp'] ? _('Select the end of the reporting period') : ''), // If it is not set the $_SESSION['ShowFieldHelp'] parameter OR it is TRUE, shows the page help text.
		 		'</td>
			</tr>',
			// Show accounts with zero balance:
			'<tr>',
				'<td><label for="ShowZeroBalance">', _('Show accounts with zero balance'), '</label></td>
			 	<td><input',(isset($_POST['ShowZeroBalance']) && $_POST['ShowZeroBalance'] ? ' checked="checked"' : ''), ' id="ShowZeroBalance" name="ShowZeroBalance" type="checkbox">', // "Checked" if ShowZeroBalance is set AND it is TRUE.
					(!isset($_SESSION['ShowFieldHelp']) || $_SESSION['ShowFieldHelp'] ? _('Check this box to show all accounts including those with zero balance') : ''), // If it is not set the $_SESSION['ShowFieldHelp'] parameter OR it is TRUE, shows the page help text.
		 		'</td>
			</tr>',
			// Show cash accounts:
			'<tr>',
			 	'<td><label for="ShowCash">', _('Show cash accounts'), '</label></td>
			 	<td><input',($_POST['ShowCash'] ? ' checked="checked"' : ''), ' id="ShowCash" name="ShowCash" type="checkbox">', // "Checked" if ShowZeroBalance is set AND it is TRUE.
					(!isset($_SESSION['ShowFieldHelp']) || $_SESSION['ShowFieldHelp'] ? _('Check this box to show cash accounts') : ''), // If it is not set the $_SESSION['ShowFieldHelp'] parameter OR it is TRUE, shows the page help text.
		 		'</td>
			</tr>',
		 '</tbody></table>';
}
echo	'</form>';
include('includes/footer.php');
?>
