<?php

/*
Shows the horizontal analysis of the statement of comprehensive income.

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

$Title = __('Horizontal Analysis of Statement of Comprehensive Income');
$ViewTopic = 'GeneralLedger';
$BookMark = 'AnalysisHorizontalIncome';
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
include('includes/AccountSectionsDef.php');// This loads the $Sections variable.

if (isset($_POST['PeriodFrom']) and ($_POST['PeriodFrom'] > $_POST['PeriodTo'])) {
	prnMsg(__('The selected period from is actually after the period to') . '! ' .
		__('Please reselect the reporting period'), 'error');
	$_POST['NewReport'] = 'on';
}

if (isset($_POST['Period']) and $_POST['Period'] != '') {
	$_POST['PeriodFrom'] = ReportPeriod($_POST['Period'], 'From');
	$_POST['PeriodTo'] = ReportPeriod($_POST['Period'], 'To');
}

if ((!isset($_POST['PeriodFrom']) or !isset($_POST['PeriodTo'])) or isset($_POST['NewReport'])) {
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/printer.png" title="', // Icon image.
		$Title, '" /> ', // Icon title.
		$Title, '</p>';// Page title.
	fShowPageHelp(// Shows the page help text if $_SESSION['ShowFieldHelp'] is true or is not set
		__('Shows the horizontal analysis of the statement of comprehensive income.') . '<br />' .
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

	echo	'<field>
				<label for="PeriodFrom">', __('Select period from'), ':</label>
				<select name="PeriodFrom" required="required">';

	if (Date('m') > $_SESSION['YearEnd']) {
		/*Dates in SQL format */
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
	} else {
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y') - 1));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y') - 1));
	}

	$SQL = "SELECT periodno, lastdate_in_period
			FROM periods
			ORDER BY periodno DESC";
	$Periods = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Periods)) {
		if (isset($_POST['PeriodFrom']) AND $_POST['PeriodFrom'] != '') {
			if ($_POST['PeriodFrom'] == $MyRow['periodno']) {
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' .
					MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $MyRow['periodno'] . '">' .
					MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		} else {
			if ($MyRow['lastdate_in_period'] == $DefaultFromDate) {
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' .
					MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $MyRow['periodno'] . '">' .
					MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		}
	}

	echo '</select>
		</field>',
	// Select period to:
			'<field>
				<label for="PeriodTo">', __('Select period to'), '</label>
				<select id="PeriodTo" name="PeriodTo" required="required">';

	if (!isset($_POST['PeriodTo']) OR $_POST['PeriodTo'] == '') {
		$LastDate = date('Y-m-d', mktime(0, 0, 0, Date('m') + 1, 0, Date('Y')));
		$SQL = "SELECT periodno FROM periods where lastdate_in_period = '" . $LastDate . "'";
		$MaxPrd = DB_query($SQL);
		$MaxPrdRow = DB_fetch_row($MaxPrd);
		$DefaultPeriodTo = (int) ($MaxPrdRow[0]);
	} else {
		$DefaultPeriodTo = $_POST['PeriodTo'];
	}

	DB_data_seek($Periods, 0);

	while ($MyRow = DB_fetch_array($Periods)) {
		echo '<option';
		if ($MyRow['periodno'] == $DefaultPeriodTo) {
			echo ' selected="selected"';
		}
		echo ' value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
	}

	echo '</select>
		</field>';

	if (!isset($_POST['Period'])) {
		$_POST['Period'] = '';
	}

	echo	'<field>
				<label for="Period">', '<b>' , __('OR') , ' </b>' , __('Select Period'), '</label>
				', ReportPeriodList($_POST['Period'], array('l', 't')), '
			</field>',
	// Show all accounts instead a summary:
			'<field>
				<label for="ShowDetail">', __('Detail or summary'), '</label>
				<select name="ShowDetail" required="required">
					<option value="Summary">', __('Summary'), '</option>
					<option selected="selected" value="Detailed">', __('All Accounts'), '</option>
					</select>
					<fieldhelp>', __('Selecting Summary will show on the totals at the account group level'), '</fieldhelp
			</field>',
	// Show accounts with zero balance:
			'<field>
				<label for="ShowZeroBalance">', __('Show accounts with zero balance'), '</label>
				<input';
	if (isset($_POST['ShowZeroBalance'])) {
		echo ' checked="checked"';
	} else {
		echo '';
	}
	echo ' id="ShowZeroBalance" name="ShowZeroBalance" type="checkbox">', // "Checked" if ShowZeroBalance is set AND it is true.
					'<fieldhelp>', __('Check this box to show accounts with zero balance'), '</fieldhelp>
			</field>';
	// BEGIN ReportParametersFormEnd:
	echo '</fieldset>',
			'<div class="centre">',
				'<button name="Submit" type="submit" value="submit"><img alt="" src="', $RootPath, '/css/', $Theme,
					'/images/tick.svg" /> ', __('Submit'), '</button>', // "Submit" button.
				'<button onclick="window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath,
					'/css/', $Theme, '/images/return.svg" /> ', __('Return'), '</button>', // "Return" button.
			'</div>',
		'</form>';
	// END ReportParametersFormEnd.

} else {
	$NumberOfMonths = $_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1;
	if ($NumberOfMonths > 12) {
		echo '<br />';
		prnMsg(__('A period up to 12 months in duration can be specified') . ' - ' .
			__('the system automatically shows a comparative for the same period from the previous year') . ' - ' .
			__('it cannot do this if a period of more than 12 months is specified') . '. ' .
			__('Please select an alternative period range'), 'error');
		include('includes/footer.php');
		exit();
	}

	$EndDateSQL = EndDateSQLFromPeriodNo($_POST['PeriodTo']);
	$PeriodToDate = MonthAndYearFromSQLDate($EndDateSQL);

	// Page title as IAS 1, numerals 10 and 51:
	include_once('includes/CurrenciesArray.php');// Array to retrieve currency name.
	echo '<div id="Report">', // Division to identify the report block.
		'<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" title="', // Icon image.
		$Title, '" /> ', // Icon title.
		$Title, '<br />', // Page title, reporting statement.
		stripslashes($_SESSION['CompanyRecord']['coyname']), '<br />', // Page title, reporting entity.
		__('For'), ' ', $NumberOfMonths, ' ', __('months to'), ' ', $PeriodToDate, '<br />', // Page title, reporting period.
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
					__('Actual change signs: a positive number indicates a variation that increases the net profit; a negative number indicates a variation that decreases the net profit.'), '<br />',
					__('Relative change signs: a positive number indicates an increase in the amount of that account; a negative number indicates a decrease in the amount of that account.'), '<br />',
				'</td>
			</tr>
		</tfoot>
		<tbody>';// thead and tfoot used in conjunction with tbody enable scrolling of the table body independently of the header and footer. Also, when printing a large table that spans multiple pages, these elements can enable the table header to be printed at the top of each page.

	$SQL = "SELECT accountgroups.sectioninaccounts,
					accountgroups.parentgroupname,
					accountgroups.groupname,
					chartmaster.accountcode,
					chartmaster.accountname,
					SUM(CASE WHEN gltotals.period >= '" . $_POST['PeriodFrom'] . "' AND gltotals.period <= '" . $_POST['PeriodTo'] . "' THEN gltotals.amount ELSE 0 END) AS PeriodActual,
					SUM(CASE WHEN gltotals.period >= '" . ($_POST['PeriodFrom'] - 12) . "' AND gltotals.period <= '" . ($_POST['PeriodTo'] - 12) . "' THEN gltotals.amount ELSE 0 END) AS LYPeriodActual
			FROM chartmaster
			INNER JOIN accountgroups
				ON chartmaster.group_ = accountgroups.groupname
			INNER JOIN gltotals
				ON chartmaster.accountcode = gltotals.account
			INNER JOIN glaccountusers
				ON glaccountusers.accountcode = chartmaster.accountcode
					AND glaccountusers.userid = '" .  $_SESSION['UserID'] . "'
					AND glaccountusers.canview = 1
			WHERE accountgroups.pandl = 1
			GROUP BY accountgroups.sectioninaccounts,
					accountgroups.parentgroupname,
					accountgroups.groupname,
						chartmaster.accountcode,
					chartmaster.accountname
			ORDER BY accountgroups.sectioninaccounts,
					accountgroups.sequenceintb,
					accountgroups.groupname,
					chartmaster.accountcode";
	$AccountsResult = DB_query($SQL, __('No general ledger accounts were returned by the SQL because'), __('The SQL that failed was'));

	$PeriodTotal = 0;
	$PeriodTotalLY = 0;

	$Section = '';
	$SectionTotal = 0;
	$SectionTotalLY = 0;

	$ActGrp = '';
	$GrpTotal = array(0);
	$GrpTotalLY = array(0);
	$Level = 0;
	$ParentGroups = array();
	$ParentGroups[$Level] = '';

	$DrawTotalLine = '<tr>
		<td colspan="2">&nbsp;</td>
		<td><hr /></td>
		<td><hr /></td>
		<td><hr /></td>
		<td><hr /></td>
	</tr>';

	while ($MyRow = DB_fetch_array($AccountsResult)) {
		if ($MyRow['groupname'] != $ActGrp) {
			if ($MyRow['parentgroupname'] != $ActGrp AND $ActGrp != '') {
				while ($MyRow['groupname'] != $ParentGroups[$Level] AND $Level > 0) {
					if ($_POST['ShowDetail'] == 'Detailed') {
						echo $DrawTotalLine;
						$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level] . ' *' . __('total');
					} else {
						$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level];
					}
					echo '<tr>
							<td class="text" colspan="2">', $ActGrpLabel, '</td>
							<td class="number">', locale_number_format(-$GrpTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format(-$GrpTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format(-$GrpTotal[$Level] + $GrpTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', RelativeChange(-$GrpTotal[$Level], -$GrpTotalLY[$Level]), '</td>
						</tr>';
					$GrpTotal[$Level] = 0;
					$GrpTotalLY[$Level] = 0;
					$ParentGroups[$Level] = '';
					$Level--;
				}// End while.

				//still need to print out the old group totals

				if ($_POST['ShowDetail'] == 'Detailed') {
					echo $DrawTotalLine;
					$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level] . ' ' . __('total');
				} else {
					$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level];
				}

				if ($Section == 1) {// Income
				echo '<tr>
						<td class="text" colspan="2">', $ActGrpLabel, '</td>
						<td class="number">', locale_number_format(-$GrpTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', locale_number_format(-$GrpTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', locale_number_format(-$GrpTotal[$Level] + $GrpTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', RelativeChange(-$GrpTotal[$Level], -$GrpTotalLY[$Level]), '</td>
					</tr>';
				} else {// Costs
				echo '<tr>
						<td class="text" colspan="2">', $ActGrpLabel, '</td>
						<td class="number">', locale_number_format(-$GrpTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', locale_number_format(-$GrpTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', locale_number_format(-$GrpTotal[$Level] + $GrpTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', RelativeChange(-$GrpTotal[$Level], -$GrpTotalLY[$Level]), '</td>
					</tr>';
				}
				$GrpTotalLY[$Level] = 0;
				$GrpTotal[$Level] = 0;
				$ParentGroups[$Level] = '';
			}
		}

		if ($MyRow['sectioninaccounts'] != $Section) {

			if ($SectionTotal + $SectionTotalLY != 0) {

				if ($Section == 1) {// Income.
					echo $DrawTotalLine;
					echo '<tr>
							<td class="text" colspan="2"><h2>', $Sections[$Section], '</h2></td>
							<td class="number"><h2>', locale_number_format(-$SectionTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
							<td class="number"><h2>', locale_number_format(-$SectionTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
							<td class="number"><h2>', locale_number_format(-$SectionTotal + $SectionTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
							<td class="number"><h2>', RelativeChange(-$SectionTotal, -$SectionTotalLY), '</h2></td>
						</tr>';
					$GPIncome = $SectionTotal;
					$GPIncomeLY = $SectionTotalLY;
				} else {
					echo '<tr>
							<td class="text" colspan="2"><h2>', $Sections[$Section], '</h2></td>
							<td class="number"><h2>', locale_number_format(-$SectionTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
							<td class="number"><h2>', locale_number_format(-$SectionTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
							<td class="number"><h2>', locale_number_format(-$SectionTotal + $SectionTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
							<td class="number"><h2>', RelativeChange(-$SectionTotal, -$SectionTotalLY), '</h2></td>
						</tr>';
				}

				if ($Section == 2) {// Cost of Sales - need sub total for Gross Profit.
					echo $DrawTotalLine;
					echo '<tr>
							<td class="text" colspan="2"><h2>', __('Gross Profit'), '</h2></td>
							<td class="number"><h2>', locale_number_format(-($GPIncome + $SectionTotal), $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
							<td class="number"><h2>', locale_number_format(-($GPIncomeLY + $SectionTotalLY), $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
							<td class="number"><h2>', locale_number_format(-($GPIncome + $SectionTotal) + ($GPIncomeLY + $SectionTotalLY), $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
							<td class="number"><h2>', RelativeChange(-($GPIncome + $SectionTotal), -($GPIncomeLY + $SectionTotalLY)), '</h2></td>
						</tr>';
				}

				if (($Section != 1) AND ($Section != 2)) {
					echo $DrawTotalLine;
					echo '<tr>
							<td class="text" colspan="2"><h2>', __('Earnings after'), ' ', $Sections[$Section], '</h2></td>
							<td class="number"><h2>', locale_number_format(-$PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
							<td class="number"><h2>', locale_number_format(-$PeriodTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
							<td class="number"><h2>', locale_number_format(-$PeriodTotal + $PeriodTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
							<td class="number"><h2>', RelativeChange(-$PeriodTotal, -$PeriodTotalLY), '</h2></td>
						</tr>';
					echo $DrawTotalLine;
				}
			}

			$Section = $MyRow['sectioninaccounts'];
			$SectionTotal = 0;
			$SectionTotalLY = 0;

			if ($_POST['ShowDetail'] == 'Detailed') {
				echo '<tr>
						<td colspan="6"><h2>', $Sections[$MyRow['sectioninaccounts']], '</h2></td>
					</tr>';
			}
		}

		if ($MyRow['groupname'] != $ActGrp) {
			if ($MyRow['parentgroupname'] == $ActGrp AND $ActGrp != '') {// Adding another level of nesting
				$Level++;
			}
			$ActGrp = $MyRow['groupname'];
			$ParentGroups[$Level] = $MyRow['groupname'];
			if ($_POST['ShowDetail'] == 'Detailed') {
				echo '<tr>
						<td colspan="6"><h2>', $MyRow['groupname'], '</h2></td>
					</tr>';
			}
		}

		// Set totals for account, groups, section and period:
		$AccountTotal = $MyRow['PeriodActual'];
		$AccountTotalLY = $MyRow['LYPeriodActual'];
		for ($i = 0; $i <= $Level; $i++) {
			if (!isset($GrpTotalLY[$i])) {$GrpTotalLY[$i] = 0;}
			$GrpTotalLY[$i] += $AccountTotalLY;
			if (!isset($GrpTotal[$i])) {$GrpTotal[$i] = 0;}
			$GrpTotal[$i] += $AccountTotal;
		}
		$SectionTotal += $AccountTotal;
		$SectionTotalLY += $AccountTotalLY;
		$PeriodTotal += $AccountTotal;
		$PeriodTotalLY += $AccountTotalLY;

		if ($_POST['ShowDetail'] == 'Detailed') {
			if (isset($_POST['ShowZeroBalance']) OR (!isset($_POST['ShowZeroBalance']) AND ($AccountTotal != 0 OR $AccountTotalLY != 0))) {
				$AccountInquiryLink = $RootPath . '/GLAccountInquiry.php?' .
					'PeriodFrom=' . urlencode($_POST['PeriodFrom']) .
					'&amp;PeriodTo=' . urlencode($_POST['PeriodTo']) .
					'&amp;Account=' . urlencode($MyRow['accountcode']) .
					'&amp;Show=Yes';
				echo '<tr class="striped_row">
							<td class="text"><a href="', $AccountInquiryLink, '">', $MyRow['accountcode'], '</a></td>';
				if ($Section == 1) {
					echo '	<td class="text">', htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false), '</td>
							<td class="number">', locale_number_format(-$AccountTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format(-$AccountTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format(-$AccountTotal + $AccountTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', RelativeChange(-$AccountTotal, -$AccountTotalLY), '</td>
						</tr>';
				} else {
					echo '	<td class="text">', htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false), '</td>
							<td class="number">', locale_number_format(-$AccountTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format(-$AccountTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format(-$AccountTotal + $AccountTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', RelativeChange(-$AccountTotal, -$AccountTotalLY), '</td>
						</tr>';
				}
			}
		}
	}// End of loop.

	if (isset($MyRow['groupname']) and $MyRow['groupname'] != $ActGrp) {
		if ($MyRow['parentgroupname'] != $ActGrp AND $ActGrp != '') {
			while ($MyRow['groupname'] != $ParentGroups[$Level] AND $Level > 0) {
				if ($_POST['ShowDetail'] == 'Detailed') {
					echo $DrawTotalLine;
					$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level] . ' ' . __('total');
				} else {
					$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level];
				}
				if ($Section == 1) {// Income.
					echo '<tr>
							<td colspan="2"><h3>', $ActGrpLabel, '</h3></td>
							<td class="number">', locale_number_format(-$GrpTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format(-$GrpTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format(-$GrpTotal[$Level] + $GrpTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', RelativeChange(-$GrpTotal[$Level], -$GrpTotalLY[$Level]), '</td>
						</tr>';
				} else {// Costs.
					echo '<tr>
							<td colspan="2"><h3>', $ActGrpLabel, '</h3></td>
							<td class="number">', locale_number_format(-$GrpTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format(-$GrpTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format(-$GrpTotal[$Level] + $GrpTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', RelativeChange(-$GrpTotal[$Level], -$GrpTotalLY[$Level]), '</td>
						</tr>';
				}
				$GrpTotal[$Level] = 0;
				$GrpTotalLY[$Level] = 0;
				$ParentGroups[$Level] = '';
				$Level--;
			}// End while.
			//still need to print out the old group totals
			if ($_POST['ShowDetail'] == 'Detailed') {
				echo $DrawTotalLine;
				$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level] . ' ' . __('total');
			} else {
				$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level];
			}
			echo '<tr>
					<td colspan="2"><h3>', $ActGrpLabel, '</h3></td>
					<td class="number">', locale_number_format(-$GrpTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format(-$GrpTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format(-$GrpTotal[$Level] + $GrpTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', RelativeChange(-$GrpTotal[$Level], -$GrpTotalLY[$Level]), '</td>
				</tr>';
			$GrpTotal[$Level] = 0;
			$GrpTotalLY[$Level] = 0;
			$ParentGroups[$Level] = '';
		}
	}

	if (isset($MyRow['sectioninaccounts']) && $MyRow['sectioninaccounts'] != $Section) {

		if ($Section == 1) {// Income.
			echo $DrawTotalLine,
				'<tr>
					<td colspan="2"><h2>', $Sections[$Section], '</h2></td>
					<td class="number"><h2>', locale_number_format(-$SectionTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
					<td class="number"><h2>', locale_number_format(-$SectionTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
					<td class="number"><h2>', locale_number_format(-$SectionTotal + $SectionTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
					<td class="number"><h2>', RelativeChange(-$SectionTotal, -$SectionTotalLY), '</h2></td>
				</tr>';
			$GPIncome = $SectionTotal;
			$GPIncomeLY = $SectionTotalLY;
		} else {
			echo $DrawTotalLine,
				'<tr>
					<td colspan="2"><h2>', $Sections[$Section], '</h2></td>
					<td class="number"><h2>', locale_number_format(-$SectionTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
					<td class="number"><h2>', locale_number_format(-$SectionTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
					<td class="number"><h2>', locale_number_format(-$SectionTotal + $SectionTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
					<td class="number"><h2>', RelativeChange(-$SectionTotal, -$SectionTotalLY), '</h2></td>
				</tr>';
		}
		if ($Section == 2) {// Cost of Sales - need sub total for Gross Profit.
			echo $DrawTotalLine,
				'<tr>
					<td colspan="2"><h2>', __('Gross Profit'), '</h2></td>
					<td class="number"><h2>', locale_number_format(-($GPIncome + $SectionTotal), $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
					<td class="number"><h2>', locale_number_format(-($GPIncomeLY + $SectionTotalLY), $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
					<td class="number"><h2>', locale_number_format(-($GPIncome + $SectionTotal) + ($GPIncomeLY + $SectionTotalLY), $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
					<td class="number"><h2>', RelativeChange(-($GPIncome + $SectionTotal), -($GPIncomeLY + $SectionTotalLY)), '</h2></td>
				</tr>';
		}
		$Section = $MyRow['sectioninaccounts'];
		$SectionTotal = 0;
		$SectionTotalLY = 0;

		if ($_POST['ShowDetail'] == 'Detailed' and isset($Sections[$MyRow['sectioninaccounts']])) {
			echo '<tr>
					<td colspan="6"><h2>', $Sections[$MyRow['sectioninaccounts']], '</h2></td>
				</tr>';
		}
	}

	echo $DrawTotalLine;
	echo '<tr>
			<td colspan="2"><h2>', __('Net Profit'), '</h2></td>
			<td class="number"><h2>', locale_number_format(-$PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', locale_number_format(-$PeriodTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', locale_number_format(-$PeriodTotal + $PeriodTotalLY, $_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', RelativeChange(-$PeriodTotal, -$PeriodTotalLY), '</h2></td>
		</tr>';
	echo $DrawTotalLine;
	echo '</tbody></table>',
		'</div>';// End div id="Report".
	// BEGIN ReportDocEndButtons:
	echo // Shows a form to select an action after the report was shown:
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />', // Resend report parameters:
		'<input type="hidden" name="PeriodFrom" value="', $_POST['PeriodFrom'], '" />',
		'<input type="hidden" name="PeriodTo" value="', $_POST['PeriodTo'], '" />',
		'<input name="ShowDetail" type="hidden" value="', $_POST['ShowDetail'], '" />',
		'<input name="ShowZeroBalance" type="hidden" value="', ($_POST['ShowZeroBalance'] ?? 0), '" />',
		'<div class="centre noPrint">', // Form buttons:
			'<button onclick="window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/printer.png" /> ', __('Print'), '</button>', // "Print" button.
			'<button name="NewReport" type="submit" value="on"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/reports.png" /> ', __('New Report'), '</button>', // "New Report" button.
			'<button onclick="window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath,
				'/css/', $Theme, '/images/return.svg" /> ', __('Return'), '</button>', // "Return" button.
		'</div>',
		'</form>';
	// END ReportDocEndButtons.
}

include('includes/footer.php');
