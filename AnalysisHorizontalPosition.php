<?php

/*
Shows the horizontal analysis of the statement of financial position.

Parameters:
	PeriodFrom: Select the beginning of the reporting period.
	PeriodTo: Select the end of the reporting period.
	Period: Select a period instead of using the beginning and end of the reporting period.
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

require(__DIR__ . '/includes/session.php');

$Title = __('Horizontal Analysis of Statement of Financial Position');
$ViewTopic = 'GeneralLedger';
$BookMark = 'AnalysisHorizontalPosition';
include('includes/header.php');

include('includes/GLFunctions.php');

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
if (isset($_GET['ShowDetail'])) {
	$_POST['ShowDetail'] = $_GET['ShowDetail'];
}
if (isset($_GET['ShowZeroBalance'])) {
	$_POST['ShowZeroBalance'] = $_GET['ShowZeroBalance'];
}
if (isset($_GET['NewReport'])) {
	$_POST['NewReport'] = $_GET['NewReport'];
}

include('includes/SQL_CommonFunctions.php');
include('includes/AccountSectionsDef.php'); // This loads the $Sections variable

if (!isset($_POST['PeriodTo']) or isset($_POST['NewReport'])) {

	/*Show a form to allow input of criteria for TB to show */
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/printer.png" title="', // Icon image.
		$Title, '" /> ', // Icon title.
		$Title, '</p>';// Page title.
	fShowPageHelp(// Shows the page help text if $_SESSION['ShowFieldHelp'] is true or is not set
		__('Shows the horizontal analysis of the statement of financial position.') . '<br />' .
		__('Horizontal analysis (also known as trend analysis) is a financial statement analysis technique that shows changes in the amounts of corresponding financial statement items over a period of time. It is a useful tool to evaluate trend situations.'). '<br />' .
		__('The statements for two periods are used in horizontal analysis. The earliest period is used as the base period. The items on the later statement are compared with items on the statement of the base period. The changes are shown both in currency (actual change) and percentage (relative change).') . '<br />' .
		__('webERP is an accrual based system (not a cash based system). Accrual systems include items when they are invoiced to the customer, and when expenses are owed based on the supplier invoice date.'));// Function fShowPageHelp() in ~/includes/MiscFunctions.php
	// BEGIN ReportParametersFormStart:
	echo // Shows a form to input the report parameters:
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />', // Input table:
		'<fieldset>', // Content of the header and footer of the input table:
		'<legend>', __('Report Parameters'), '</legend>';
	// END ReportParametersFormStart.
	// Content of the body of the input table:
	// Select period to:
	echo	'<field>
				<label for="PeriodTo">', __('Select the balance date'), '</label>
				<select id="PeriodTo" name="PeriodTo" required="required">';

	$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$LastDateInPeriod = EndDateSQLFromPeriodNo($PeriodNo);

	$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
	$Periods = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Periods)) {
		echo '<option';
		if ($MyRow['periodno'] == $PeriodNo) {
			echo ' selected="selected"';
		}
		echo ' value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
	}
	echo		'</select>
			</field>
			<field>
				<label for="ShowDetail">', __('Detail or summary'), '</label>
				<select name="ShowDetail" required="required">
					<option value="Summary">', __('Summary'), '</option>
					<option selected="selected" value="Detailed">', __('All Accounts'), '</option>
				</select>
					<fieldhelp>', __('Selecting Summary will show on the totals at the account group level'), '</fieldhelp>
			</field>',
	// Show accounts with zero balance:
			'<field>',
				'<label for="ShowZeroBalance">', __('Show accounts with zero balance'), '</label>
				<input';
	if (isset($_POST['ShowZeroBalance'])) {
		echo ' checked="checked"';
	} else {
		echo '';
	}
	echo ' id="ShowZeroBalance" name="ShowZeroBalance" type="checkbox">
				<fieldhelp>', __('Check this box to show accounts with zero balance'),'</fieldhelp>
			</field>';
	// BEGIN ReportParametersFormEnd:
	echo '</fieldset>
			<div class="centre">
				<button name="Submit" type="submit" value="submit"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/tick.svg" /> ', __('Submit'), '</button>
				<button onclick="window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/return.svg" /> ', __('Return'), '</button>
			</div>',
		'</form>';
	// END ReportParametersFormEnd.

} else {

	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];

	$EndDateSQL = EndDateSQLFromPeriodNo($_POST['PeriodTo']);
	$BalanceDate = ConvertSQLDate($EndDateSQL);

	// Page title as IAS 1, numerals 10 and 51:
	include_once('includes/CurrenciesArray.php');// Array to retrieve currency name.
	echo '<div id="Report">', // Division to identify the report block.
		'<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/gl.png" title="', // Icon image.
		__('Horizontal Analysis of Statement of Financial Position'), '" /> ', // Icon title.
		__('Horizontal Analysis of Statement of Financial Position'), '<br />', // Page title, reporting statement.
		stripslashes($_SESSION['CompanyRecord']['coyname']), '<br />', // Page title, reporting entity.
		__('as at'), ' ', $BalanceDate, '<br />', // Page title, reporting period.
		__('All amounts stated in'), ': ', __($CurrencyName[$_SESSION['CompanyRecord']['currencydefault']]), '</p>';// Page title, reporting presentation currency and level of rounding used.
	echo '<table class="scrollable">
		<thead>
		<tr>';
	if ($_POST['ShowDetail'] == 'Detailed') {// Detailed report:
		echo '<th class="text">', __('Account'), '</th>
			<th class="text">', __('Account Name'), '</th>';
	} else {// Summary report:
		echo '<th class="text" colspan="2">', __('Summary'), '</th>';
	}
	echo	'<th class="number">', __('Current period'), '</th>
			<th class="number">', __('Last period'), '</th>
			<th class="number">', __('Actual change'), '</th>
			<th class="number">', __('Relative change'), '</th>
		</tr>
		</thead>
		<tfoot>
			<tr>
				<td class="text" colspan="6">',// Prints an explanation of signs in actual and relative changes:
					'<br /><b>', __('Notes'), ':</b><br />',
					__('Actual change signs: a positive number indicates a source of funds; a negative number indicates an application of funds.'), '<br />',
					__('Relative change signs: a positive number indicates an increase in the amount of that account; a negative number indicates a decrease in the amount of that account.'), '<br />',
				'</td>
			</tr>
		</tfoot>
		<tbody>';// thead and tfoot used in conjunction with tbody enable scrolling of the table body independently of the header and footer. Also, when printing a large table that spans multiple pages, these elements can enable the table header to be printed at the top of each page.

	// Calculate B/Fwd retained earnings:
	$SQL = "SELECT
				SUM(CASE WHEN gltotals.period <= '" . $_POST['PeriodTo'] . "' THEN gltotals.amount ELSE 0 END) AS accumprofitbfwd,
				SUM(CASE WHEN gltotals.period <= '" . ($_POST['PeriodTo'] - 12) . "' THEN gltotals.amount ELSE 0 END) AS accumprofitbfwdly
			FROM chartmaster
			INNER JOIN accountgroups
				ON chartmaster.group_ = accountgroups.groupname
			INNER JOIN gltotals
				ON chartmaster.accountcode = gltotals.account
			WHERE accountgroups.pandl = 1";

	$AccumProfitResult = DB_query($SQL, __('The accumulated profits brought forward could not be calculated by the SQL because'));

	$AccumProfitRow = DB_fetch_array($AccumProfitResult); /*should only be one row returned */

	$SQL = "SELECT accountgroups.sectioninaccounts,
				accountgroups.groupname,
				accountgroups.parentgroupname,
				gltotals.account AS accountcode,
				chartmaster.accountname,
				SUM(CASE WHEN gltotals.period <= '" . $_POST['PeriodTo'] . "' THEN gltotals.amount ELSE 0 END) AS balancecfwd,
				SUM(CASE WHEN gltotals.period <= '" . ($_POST['PeriodTo'] - 12) . "' THEN gltotals.amount ELSE 0 END) AS balancecfwdly
			FROM chartmaster
			INNER JOIN accountgroups
				ON chartmaster.group_ = accountgroups.groupname
			INNER JOIN gltotals
				ON chartmaster.accountcode = gltotals.account
			INNER JOIN glaccountusers
				ON glaccountusers.accountcode = chartmaster.accountcode
				AND glaccountusers.userid = '" .  $_SESSION['UserID'] . "'
				AND glaccountusers.canview = 1
			WHERE accountgroups.pandl = 0
			GROUP BY accountgroups.groupname,
				gltotals.account,
				chartmaster.accountname,
				accountgroups.parentgroupname,
				accountgroups.sequenceintb,
				accountgroups.sectioninaccounts
			ORDER BY accountgroups.sectioninaccounts,
				accountgroups.sequenceintb,
				accountgroups.groupname,
				gltotals.account";

	$AccountsResult = DB_query($SQL, __('No general ledger accounts were returned by the SQL because'));

	$CheckTotal = 0;
	$CheckTotalLY = 0;

	$Section = '';
	$SectionBalance = 0;
	$SectionBalanceLY = 0;

	$ActGrp = '';
	$Level = 0;
	$ParentGroups = array();
	$ParentGroups[$Level] = '';
	$GroupTotal = array(0);
	$GroupTotalLY = array(0);

	$DrawTotalLine = '<tr>
		<td colspan="2">&nbsp;</td>
		<td><hr /></td>
		<td><hr /></td>
		<td><hr /></td>
		<td><hr /></td>
	</tr>';

	while ($MyRow = DB_fetch_array($AccountsResult)) {
		$AccountBalance = $MyRow['balancecfwd'];
		$AccountBalanceLY = $MyRow['balancecfwdly'];

		if ($MyRow['accountcode'] == $RetainedEarningsAct) {
			$AccountBalance += $AccumProfitRow['accumprofitbfwd'];
			$AccountBalanceLY += $AccumProfitRow['accumprofitbfwdly'];
		}

		if ($MyRow['groupname'] != $ActGrp AND $ActGrp != '') {
			if ($MyRow['parentgroupname'] != $ActGrp) {
				while ($MyRow['groupname'] != $ParentGroups[$Level] AND $Level > 0) {
					if ($_POST['ShowDetail'] == 'Detailed') {
						echo $DrawTotalLine;
					}
					echo '<tr>
							<td colspan="2">', $ParentGroups[$Level], '</td>
							<td class="number">', locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format($GroupTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format($GroupTotal[$Level] - $GroupTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', RelativeChange(-$GroupTotal[$Level], -$GroupTotalLY[$Level]), '</td>
						</tr>';
					$GroupTotal[$Level] = 0;
					$GroupTotalLY[$Level] = 0;
					$ParentGroups[$Level] = '';
					$Level--;
				}
				if ($_POST['ShowDetail'] == 'Detailed') {
					echo $DrawTotalLine;
				}
				echo '<tr>
						<td class="text" colspan="2">', $ParentGroups[$Level], '</td>
						<td class="number">', locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', locale_number_format($GroupTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', locale_number_format($GroupTotal[$Level] - $GroupTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', RelativeChange(-$GroupTotal[$Level], -$GroupTotalLY[$Level]), '</td>
					</tr>';
				$GroupTotal[$Level] = 0;
				$GroupTotalLY[$Level] = 0;
				$ParentGroups[$Level] = '';
			}
		}
		if ($MyRow['sectioninaccounts'] != $Section) {
			if ($Section != '') {
				echo $DrawTotalLine;
				echo '<tr>
						<td class="text" colspan="2"><h2>', $Sections[$Section], '</h2></td>
						<td class="number"><h2>', locale_number_format($SectionBalance, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
						<td class="number"><h2>', locale_number_format($SectionBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
						<td class="number"><h2>', locale_number_format($SectionBalance - $SectionBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
						<td class="number"><h2>', RelativeChange(-$SectionBalance, -$SectionBalanceLY), '</h2></td>
					</tr>';
			}
			$SectionBalance = 0;
			$SectionBalanceLY = 0;
			$Section = $MyRow['sectioninaccounts'];
			if ($_POST['ShowDetail'] == 'Detailed') {
				echo '<tr>
						<td colspan="6"><h2>', $Sections[$MyRow['sectioninaccounts']], '</h2></td>
					</tr>';
			}
		}

		if ($MyRow['groupname'] != $ActGrp) {

			if ($ActGrp != '' AND $MyRow['parentgroupname'] == $ActGrp) {
				$Level++;
			}

			if ($_POST['ShowDetail'] == 'Detailed') {
				$ActGrp = $MyRow['groupname'];
				echo '<tr>
						<td colspan="6"><h3>', $MyRow['groupname'], '</h3></td>
					</tr>';
			}
			$GroupTotal[$Level] = 0;
			$GroupTotalLY[$Level] = 0;
			$ActGrp = $MyRow['groupname'];
			$ParentGroups[$Level] = $MyRow['groupname'];
		}
		$SectionBalance += $AccountBalance;
		$SectionBalanceLY += $AccountBalanceLY;

		for ($i = 0; $i <= $Level; $i++) {
			$GroupTotalLY[$i] += $AccountBalanceLY;
			$GroupTotal[$i] += $AccountBalance;
		}
		$CheckTotal += $AccountBalance;
		$CheckTotalLY += $AccountBalanceLY;

		if ($_POST['ShowDetail'] == 'Detailed') {
			if (isset($_POST['ShowZeroBalance']) OR (!isset($_POST['ShowZeroBalance']) AND (round($AccountBalance, $_SESSION['CompanyRecord']['decimalplaces']) <> 0 OR round($AccountBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']) <> 0))) {
				echo '<tr class="striped_row">
						<td class="text"><a href="', $RootPath, '/GLAccountInquiry.php?Period=', $_POST['PeriodTo'], '&amp;Account=', $MyRow['accountcode'], '">', $MyRow['accountcode'], '</a></td>
						<td class="text">', htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false), '</td>
						<td class="number">', locale_number_format($AccountBalance, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', locale_number_format($AccountBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', locale_number_format($AccountBalance - $AccountBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', RelativeChange(-$AccountBalance, -$AccountBalanceLY), '</td>
					</tr>';
			}
		}
	}// End of loop.

	if (isset($MyRow)) {
		while ($MyRow['groupname'] != $ParentGroups[$Level] AND $Level > 0) {
			if ($_POST['ShowDetail'] == 'Detailed') {
				echo $DrawTotalLine;
			}
			echo '<tr>
					<td colspan="2">', $ParentGroups[$Level], '</td>
					<td class="number">', locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format($GroupTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format($GroupTotal[$Level] - $GroupTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', RelativeChange(-$GroupTotal[$Level], -$GroupTotalLY[$Level]), '</td>
				</tr>';
			$Level--;
		}
	}
	if ($_POST['ShowDetail'] == 'Detailed') {
		echo $DrawTotalLine;
	}
	echo '<tr>
			<td colspan="2">', $ParentGroups[$Level], '</td>
			<td class="number">', locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td class="number">', locale_number_format($GroupTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td class="number">', locale_number_format($GroupTotal[$Level] - $GroupTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td class="number">', RelativeChange(-$GroupTotal[$Level], -$GroupTotalLY[$Level]), '</td>
		</tr>';
	echo $DrawTotalLine;
	echo '<tr>
			<td colspan="2"><h2>', $Sections[$Section], '</h2></td>
			<td class="number"><h2>', locale_number_format($SectionBalance, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', locale_number_format($SectionBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', locale_number_format($SectionBalance - $SectionBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', RelativeChange(-$SectionBalance, -$SectionBalanceLY), '</h2></td>
		</tr>';

	if (isset($MyRow)) {
		$Section = $MyRow['sectioninaccounts'];

		if (isset($MyRow['sectioninaccounts']) and $_POST['ShowDetail'] == 'Detailed') {
			echo '<tr>
					<td colspan="6"><h2>', $Sections[$MyRow['sectioninaccounts']], '</h2></td>
				</tr>';
		}
	}
	echo $DrawTotalLine;
	echo'<tr>
			<td colspan="2"><h2>', __('Check Total'), '</h2></td>
			<td class="number"><h2>', locale_number_format($CheckTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', locale_number_format($CheckTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', locale_number_format($CheckTotal - $CheckTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', RelativeChange(-$CheckTotal, -$CheckTotalLY), '</h2></td>
		</tr>';
	echo $DrawTotalLine;
	echo '</tbody></table>',
		'</div>';// End div id="Report".
	// BEGIN ReportDocEndButtons:
	echo // Shows a form to select an action after the report was shown:
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />', // Resend report parameters:
		'<input type="hidden" name="PeriodTo" value="', $_POST['PeriodTo'], '" />',
		'<input name="ShowDetail" type="hidden" value="', $_POST['ShowDetail'], '" />',
		'<input name="ShowZeroBalance" type="hidden" value="', $_POST['ShowZeroBalance'], '" />',
		'<div class="centre noPrint">', // Form buttons:
			'<button onclick="window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/printer.png" /> ', __('Print'), '</button>', // "Print" button.
			'<button name="NewReport" type="submit" value="on"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/reports.png" /> ', __('New Report'), '</button>', // "New Report" button.
			'<button onclick="window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/return.svg" /> ', __('Return'), '</button>', // "Return" button.
		'</div>',
		'</form>';
	// END ReportDocEndButtons.
}
include('includes/footer.php');
