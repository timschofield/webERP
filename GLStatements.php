<?php
/*
	Shows a set of financial statements.
	This program is under the GNU General Public License, last version. 2016-10-08.
	This creative work is under the CC BY-NC-SA, last version. 2016-10-08.


Info about financial statements: IAS 1 - Presentation of Financial Statements.

Parameters:
	PeriodFrom: Select the beginning of the reporting period.
	PeriodTo: Select the end of the reporting period.
	Period: Select a period instead of using the beginning and end of the reporting period.
	ShowBudget: Check this box to show the budget.
	ShowDetail: Check this box to show all accounts instead a summary.
	ShowZeroBalance: Check this box to show accounts with zero balance.
	ShowFinancialPosition: Check this box to show the statement of financial position as at the end and at the beginning of the period;
	ShowComprehensiveIncome: Check this box to show the statement of comprehensive income;
	ShowChangesInEquity: Check this box to show the statement of changes in equity;
	ShowCashFlows: Check this box to show the statement of cash flows; and
	ShowNotes: Check this box to show the notes that summarize the significant accounting policies and other explanatory information.
	NewReport: Click this button to start a new report.
	IsIncluded: Parameter to indicate that a script is included within another.
*/

// BEGIN: Functions division ===================================================
// END: Functions division =====================================================

// BEGIN: Procedure division ===================================================
require(__DIR__ . '/includes/session.php');

$Title = __('Financial Statements');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLStatements';
include('includes/header.php');

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
if(isset($_GET['ShowBudget'])) {
	$_POST['ShowBudget'] = $_GET['ShowBudget'];
}
if(isset($_GET['ShowZeroBalance'])) {
	$_POST['ShowZeroBalance'] = $_GET['ShowZeroBalance'];
}
if(isset($_GET['ShowFinancialPosition'])) {
	$_POST['ShowFinancialPosition'] = $_GET['ShowFinancialPosition'];
}
if(isset($_GET['ShowComprehensiveIncome'])) {
	$_POST['ShowComprehensiveIncome'] = $_GET['ShowComprehensiveIncome'];
}
if(isset($_GET['ShowChangesInEquity'])) {
	$_POST['ShowChangesInEquity'] = $_GET['ShowChangesInEquity'];
}
if(isset($_GET['ShowCashFlows'])) {
	$_POST['ShowCashFlows'] = $_GET['ShowCashFlows'];
}
if(isset($_GET['ShowNotes'])) {
	$_POST['ShowNotes'] = $_GET['ShowNotes'];
}
if(isset($_GET['NewReport'])) {
	$_POST['NewReport'] = $_GET['NewReport'];
}

// Sets PeriodFrom and PeriodTo from Period:
if(isset($_POST['Period']) and $_POST['Period'] != '') {
	$_POST['PeriodFrom'] = ReportPeriod($_POST['Period'], 'From');
	$_POST['PeriodTo'] = ReportPeriod($_POST['Period'], 'To');
}

// Validates the data submitted in the form:
if(isset($_POST['PeriodFrom']) and $_POST['PeriodFrom'] > $_POST['PeriodTo']) {
	// The beginning is after the end.
	$_POST['NewReport'] = 'on';
	prnMsg(__('The beginning of the period should be before or equal to the end of the period. Please reselect the reporting period.'), 'error');
}
if(isset($_POST['PeriodTo']) and $_POST['PeriodTo']-$_POST['PeriodFrom']+1 > 12) {
	// The reporting period is greater than 12 months.
	$_POST['NewReport'] = 'on';
	prnMsg(__('The period should be 12 months or less in duration. Please select an alternative period range.'), 'error');
}
if(isset($_POST['PeriodFrom']) AND isset($_POST['PeriodTo']) AND !($_POST['ShowFinancialPosition']) AND !($_POST['ShowComprehensiveIncome']) AND !($_POST['ShowChangesInEquity']) AND !($_POST['ShowCashFlows']) AND !($_POST['ShowNotes'])) {
	// No financial statement was selected.
	$_POST['NewReport'] = 'on';
	prnMsg(__('You must select at least one financial statement. Please select financial statements.'), 'error');
}

// Main code:
if(isset($_POST['PeriodFrom']) AND isset($_POST['PeriodTo']) AND !$_POST['NewReport']) {
	// If PeriodFrom and PeriodTo are set and it is not a NewReport, generates the report:

	echo '<div class="sheet">';// Division to identify the report block.
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" title="', // Icon image.
		$Title, '" /> ', // Icon title.
		// Page title as IAS1 numerals 10 and 51:
		$Title, '<br />', // Page title, reporting statement.
		stripslashes($_SESSION['CompanyRecord']['coyname']), '<br />'; // Page title, reporting entity.
	$PeriodFromName = EndDateSQLFromPeriodNo($_POST['PeriodFrom']);
	$PeriodToName = EndDateSQLFromPeriodNo($_POST['PeriodTo']);
	echo __('From'), ' ', MonthAndYearFromSQLDate($PeriodFromName), ' ', __('to'), ' ', MonthAndYearFromSQLDate($PeriodToName), '<br />'; // Page title, reporting period.
	include_once('includes/CurrenciesArray.php');// Array to retrieve currency name.
	echo __('All amounts stated in'), ': ', __($CurrencyName[$_SESSION['CompanyRecord']['currencydefault']]), '</p>';// Page title, reporting presentation currency and level of rounding used.
	echo // Index of this report:
		'<p>', __('In this set of financial statements:'),
		(($_POST['ShowFinancialPosition']) ? '<br />* ' . __('Statement of financial position') . '.' : ''),
		(($_POST['ShowComprehensiveIncome']) ? '<br />* ' . __('Statement of comprehensive income') . '.' : ''),
		(($_POST['ShowChangesInEquity']) ? '<br />* ' . __('Statement of changes in equity') . '.' : ''),
		(($_POST['ShowCashFlows']) ? '<br />* ' . __('Statement of cash flows') . '.' : ''),
		(($_POST['ShowNotes']) ? '<br />* ' . __('Notes') . '.' : ''),
		'<p>';
	echo '</div>';// div id="Report".

	$IsIncluded = true;
	$PageBreak = '<hr class="PageBreak"/>' . chr(12);// Marker to indicate that the content that follows is part of a new page.
	// Displays the statements using the corresponding scripts:
	$_POST['ShowDetail'] = 'Detailed';
	if ($_POST['ShowFinancialPosition']) {
		echo $PageBreak;
		include('GLBalanceSheet.php');
	}
	if ($_POST['ShowComprehensiveIncome']) {
		echo $PageBreak;
		include('GLProfit_Loss.php');
	}
	if ($_POST['ShowChangesInEquity']) {
		echo $PageBreak;
		/// @bug this file does not exist
		include('GLChangesInEquity.php');
	}
	if ($_POST['ShowCashFlows']) {
		echo $PageBreak;
		include('GLCashFlowsIndirect.php');
	}
	if ($_POST['ShowNotes']) {
		echo $PageBreak;
		/// @bug this file does not exist
		include('GLNotes.php');
	}

	echo // Shows a form to select an action after the report was shown:
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
		// Resend report parameters:
		'<input name="PeriodFrom" type="hidden" value="', $_POST['PeriodFrom'], '" />',
		'<input name="PeriodTo" type="hidden" value="', $_POST['PeriodTo'], '" />',
		'<input name="ShowBudget" type="hidden" value="', $_POST['ShowBudget'], '" />',
		'<input name="ShowZeroBalance" type="hidden" value="', $_POST['ShowZeroBalance'], '" />',
		'<input name="ShowFinancialPosition" type="hidden" value="', $_POST['ShowFinancialPosition'], '" />',
		'<input name="ShowComprehensiveIncome" type="hidden" value="', $_POST['ShowComprehensiveIncome'], '" />',
		'<input name="ShowChangesInEquity" type="hidden" value="', $_POST['ShowChangesInEquity'], '" />',
		'<input name="ShowCashFlows" type="hidden" value="', $_POST['ShowCashFlows'], '" />',
		'<input name="ShowNotes" type="hidden" value="', $_POST['ShowNotes'], '" />',
		'<div class="centre noPrint">', // Form buttons:
			'<button onclick="window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/printer.png" /> ', __('Print'), '</button>', // "Print" button.
			'<button name="NewReport" type="submit" value="on"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/reports.png" /> ', __('New Report'), '</button>', // "New Report" button.
			'<button onclick="window.location=\'' . $RootPath . '/index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/return.svg" /> ', __('Return'), '</button>', // "Return" button.
		'</div>',
		'</form>';
} else {
	// If PeriodFrom or PeriodTo are NOT set or it is a NewReport, shows a parameters input form:
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" title="', // Icon image.
		$Title, '" /> ', // Icon title.
		$Title, '</p>';// Page title.
	fShowPageHelp(// Shows the page help text if $_SESSION['ShowFieldHelp'] is true or is not set
		__('Shows a set of financial statements.') . '<br />' .
		__('A complete set of financial statements comprises:(a) a statement of financial position as at the end and at the beginning of the period;(b) a statement of comprehensive income for the period;(c) a statement of changes in equity for the period;(d) a statement of cash flows for the period; and(e) notes that summarize the significant accounting policies and other explanatory information.') . '<br />' .
		__('webERP is an accrual based system (not a cash based system). Accrual systems include items when they are invoiced to the customer, and when expenses are owed based on the supplier invoice date.'));// Function fShowPageHelp() in ~/includes/MiscFunctions.php
	echo // Shows a form to input the report parameters:
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />', // Input table:
		'<fieldset>
			<legend>', __('Report Criteria'), '</legend>', // Content of the header and footer of the input table:
	// Content of the body of the input table:
	// Select period from:
			'<field>',
				'<label for="PeriodFrom">', __('Select period from'), '</label>
		 		<select id="PeriodFrom" name="PeriodFrom" required="required">';
	$Periods = DB_query('SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno ASC');
	if(!isset($_POST['PeriodFrom'])) {
		$_POST['ShowBudget'] = '';
		$_POST['ShowZeroBalance'] = '';
		$_POST['ShowFinancialPosition'] = '';
		$_POST['ShowComprehensiveIncome'] = '';
		$_POST['ShowCashFlows'] = '';
		$BeginMonth = ($_SESSION['YearEnd']==12 ? 1 : $_SESSION['YearEnd']+1);// Sets January as the month that follows December.
		if($BeginMonth <= date('n')) {// It is a month in the current year.
			$BeginDate = mktime(0, 0, 0, $BeginMonth, 1, date('Y'));
		} else {// It is a month in the previous year.
			$BeginDate = mktime(0, 0, 0, $BeginMonth, 1, date('Y')-1);
		}
		$_POST['PeriodFrom'] = GetPeriod(date($_SESSION['DefaultDateFormat'], $BeginDate));
	}
	while($MyRow = DB_fetch_array($Periods)) {
	    echo			'<option',($MyRow['periodno'] == $_POST['PeriodFrom'] ? ' selected="selected"' : '' ), ' value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
	}
	echo			'</select>
				<fieldhelp>', __('Select the beginning of the reporting period'), '</fieldhelp>
			</field>',
	// Select period to:
			'<field>',
				'<label for="PeriodTo">', __('Select period to'), '</label>
		 		<select id="PeriodTo" name="PeriodTo" required="required">';
	if(!isset($_POST['PeriodTo'])) {
		$_POST['PeriodTo'] = GetPeriod(date($_SESSION['DefaultDateFormat']));
	}
	DB_data_seek($Periods, 0);
	while($MyRow = DB_fetch_array($Periods)) {
	    echo			'<option',($MyRow['periodno'] == $_POST['PeriodTo'] ? ' selected="selected"' : '' ), ' value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
	}
	echo			'</select>
				<fieldhelp>', __('Select the end of the reporting period'), '</fieldhelp>
			</field>';
	// OR Select period:
	if(!isset($_POST['Period'])) {
		$_POST['Period'] = '';
	}
	echo '<field>
				<label for="Period">', '<b>' . __('OR') . ' </b>' . __('Select Period'), '</label>
				', ReportPeriodList($_POST['Period'], array('l', 't')),'
				<fieldhelp>', __('Select a period instead of using the beginning and end of the reporting period.'), '</fieldhelp>
			</field>',
	// Show the budget:
			'<field>
				<label for="ShowBudget">', __('Show the budget'), '</label>
			 	<input', ($_POST['ShowBudget'] ? ' checked="checked"' : ''), ' id="ShowBudget" name="ShowBudget" type="checkbox">', // "Checked" if ShowBudget is set AND it is true.
			 	'<fieldhelp>', __('Check this box to show the budget'), '</fieldhelp>
			</field>',
	// Show accounts with zero balance:
			'<field>
				<label for="ShowZeroBalance">', __('Show accounts with zero balance'), '</label>
			 	<input', ($_POST['ShowZeroBalance'] ? ' checked="checked"' : ''), ' id="ShowZeroBalance" name="ShowZeroBalance" type="checkbox">
			 	<fieldhelp>', __('Check this box to show accounts with zero balance'), '</fieldhelp>
			</field>',
	// Show the statement of financial position:
			'<field>
				<label for="ShowFinancialPosition">', __('Show the statement of financial position'), '</label>
			 	<input', ($_POST['ShowFinancialPosition'] ? ' checked="checked"' : ''), ' id="ShowFinancialPosition" name="ShowFinancialPosition" type="checkbox">
				<fieldhelp>', __('Check this box to show the statement of financial position'), '</fieldhelp>
			</field>',
	// Show the statement of comprehensive income:
			'<field>
				<label for="ShowComprehensiveIncome">', __('Show the statement of comprehensive income'), '</label>
			 	<td><input', ($_POST['ShowComprehensiveIncome'] ? ' checked="checked"' : ''), ' id="ShowComprehensiveIncome" name="ShowComprehensiveIncome" type="checkbox">
			 	<fieldhelp>', __('Check this box to show the statement of comprehensive income'), '</fieldhelp
			</field>';
	// Show the statement of changes in equity:
	if(file_exists('GLChangesInEquity.php')) {// Provisional, to be replaced by a rights verification.
		echo
			'<field>
				<label for="ShowChangesInEquity">', __('Show the statement of changes in equity'), '</label>
			 	<input', ($_POST['ShowChangesInEquity'] ? ' checked="checked"' : ''), ' id="ShowChangesInEquity" name="ShowChangesInEquity" type="checkbox">
			 	<fieldhelp>', __('Check this box to show the statement of changes in equity'), '</fieldhelp>
			</field>';
	}
	// Show the statement of cash flows:
	echo	'<field>
				<label for="ShowCashFlows">', __('Show the statement of cash flows'), '</label>
			 	<input', ($_POST['ShowCashFlows'] ? ' checked="checked"' : ''), ' id="ShowCashFlows" name="ShowCashFlows" type="checkbox">
			 	<fieldhelp>', __('Check this box to show the statement of cash flows'), '</fieldhelp>
			</field>';
	// Show the notes:
	if(file_exists('GLNotes.php')) {// Provisional, to be replaced by a rights verification.
		echo
			'<field>
				<label for="ShowNotes">', __('Show the notes'), '</label>
			 	<input', ($_POST['ShowNotes'] ? ' checked="checked"' : ''), ' id="ShowNotes" name="ShowNotes" type="checkbox">
			 	<fieldhelp>', __('Check this box to show the notes that summarize the significant accounting policies and other explanatory information'), '</fieldhelp>
			</field>';
	}
	echo
		'</fieldset>
			<div class="centre">
				<button name="Submit" type="submit" value="', __('Submit'), '"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/tick.svg" /> ', __('Submit'), '</button>
				<button onclick="window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/return.svg" /> ', __('Return'), '</button>
			</div>
		</form>';
}

include('includes/footer.php');
