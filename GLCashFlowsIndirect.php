<?php

// Shows a statement of cash flows for the period using the indirect method.
// This program is under the GNU General Public License, last version. 2016-10-08.
// This creative work is under the CC BY-NC-SA, last version. 2016-10-08.

/*
Info about a statement of cash flows using the indirect method: IAS 7 - Statement of Cash Flows.

Parameters:
	PeriodFrom: Select the beginning of the reporting period.
	PeriodTo: Select the end of the reporting period.
	Period: Select a period instead of using the beginning and end of the reporting period.
	ShowZeroBalance: Check this box to show all accounts including those with zero balance.
	ShowCash: Check this box to show cash and cash equivalents accounts.
	NewReport: Click this button to start a new report.
	IsIncluded: Parameter to indicate that a script is included within another.
*/

// BEGIN: Functions division ===================================================
function CashFlowsActivityName($Activity) {
	// Converts the cash flow activity number to an activity text.
	switch($Activity) {
		case -1: return __('Not set up');
		case 0: return __('No effect on cash flow');
		case 1: return __('Operating activities');
		case 2: return __('Investing activities');
		case 3: return __('Financing activities');
		case 4: return __('Cash or cash equivalent');
		default: return __('Unknown');
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
// END: Functions division =====================================================

// BEGIN: Procedure division ===================================================
if(!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
	include('includes/session.php');
}
$Title = __('Statement of Cash Flows, Indirect Method');
if(!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
	$ViewTopic = 'GeneralLedger';
	$BookMark = 'GLCashFlowsIndirect';
	include('includes/header.php');
}

// Merges gets into posts:
if(isset($_GET['PeriodFrom']) AND is_numeric($_GET['PeriodFrom'])) {
	$_POST['PeriodFrom'] = $_GET['PeriodFrom'];
}
if(isset($_GET['PeriodTo']) AND is_numeric($_GET['PeriodTo'])) {
	$_POST['PeriodTo'] = $_GET['PeriodTo'];
}
if(isset($_GET['ShowZeroBalance'])) {
	$_POST['ShowZeroBalance'] = $_GET['ShowZeroBalance'];
}
if(isset($_GET['ShowCash'])) {
	$_POST['ShowCash'] = $_GET['ShowCash'];
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

// Main code:
if(isset($_POST['PeriodFrom']) AND isset($_POST['PeriodTo']) AND !$_POST['NewReport']) {
	// If PeriodFrom and PeriodTo are set and it is not a NewReport, generates the report:

	echo '<div class="sheet">';// Division to identify the report block.
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" title="', // Icon image.
		$Title, '" /> ', // Icon title.
		// Page title as IAS1 numerals 10 and 51:
		$Title, '<br />', // Page title, reporting statement.
		stripslashes($_SESSION['CompanyRecord']['coyname']), '<br />'; // Page title, reporting entity.
	if (!is_numeric($_POST['PeriodFrom']) OR !is_numeric($_POST['PeriodTo'])) {
		prnMsg(__('The period to and period from must both be entered as numbers'),'error');
		include('includes/footer.php');
		exit();
	}
	$PeriodFromName = EndDateSQLFromPeriodNo($_POST['PeriodFrom']);
	$PeriodToName = EndDateSQLFromPeriodNo($_POST['PeriodTo']);
	echo __('From'), ' ', MonthAndYearFromSQLDate($PeriodFromName), ' ', __('to'), ' ', MonthAndYearFromSQLDate($PeriodToName), '<br />'; // Page title, reporting period.
	include_once('includes/CurrenciesArray.php');// Array to retrieve currency name.
	echo __('All amounts stated in'), ': ', __($CurrencyName[$_SESSION['CompanyRecord']['currencydefault']]), '</p>';// Page title, reporting presentation currency and level of rounding used.
	echo '<table class="selection">',
		// Content of the header and footer of the output table:
		'<thead>
			<tr>
				<th>', __('Account'), '</th>
				<th>', __('Account Name'), '</th>
				<th colspan="2">', __('Period Actual'), '</th>
				<th colspan="2">', __('Last Year'), '</th>
			</tr>
		</thead><tfoot>
			<tr>
				<td class="text" colspan="6">',// Prints an explanation of signs in actual and relative changes:
					'<br /><b>', __('Notes'), ':</b><br />',
					__('Cash flows signs: a negative number indicates a cash flow used in activities; a positive number indicates a cash flow provided by activities.'), '<br />';
	if($_POST['ShowCash']) {
		echo		__('Cash and cash equivalents signs: a negative number indicates a cash outflow; a positive number indicates a cash inflow.'), '<br />';
	}
	echo		'</td>
			</tr>
		</tfoot><tbody>';
	// Initialise section accumulators:
	$ActualSection = 0;
	$ActualTotal = 0;
	$LastSection = 0;
	$LastTotal = 0;

	// Gets the net profit for the period GL account:
	if(!isset($_SESSION['PeriodProfitAccount'])) {
		$_SESSION['PeriodProfitAccount'] = '';
		$MyRow = DB_fetch_array(DB_query("SELECT confvalue FROM `config` WHERE confname ='PeriodProfitAccount'"));
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
	// Outputs the table:
	// Net profit − dividends = Retained earnings:
	echo '<tr>
			<td class="text" colspan="6"><br /><h2>', __('Net profit and dividends'), '</h2></td>
		</tr>
		<tr class="striped_row">
			<td>&nbsp;</td>
			<td class="text">', __('Net profit for the period'), '</td>';
	// Net profit for the period:
	$Sql = "SELECT
				Sum(CASE WHEN (gltotals.period >= '" . $_POST['PeriodFrom'] . "' AND gltotals.period <= '" . $_POST['PeriodTo'] . "') THEN -gltotals.amount ELSE 0 END) AS ActualProfit,
				Sum(CASE WHEN (gltotals.period >= '" . ($_POST['PeriodFrom']-12) . "' AND gltotals.period <= '" . ($_POST['PeriodTo']-12) . "') THEN -gltotals.amount ELSE 0 END) AS LastProfit
			FROM chartmaster
				INNER JOIN gltotals ON chartmaster.accountcode=gltotals.account
				INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
			WHERE accountgroups.pandl=1";
	$Result = DB_query($Sql);
	$MyRow1 = DB_fetch_array($Result);
	echo	colDebitCredit($MyRow1['ActualProfit']),
			colDebitCredit($MyRow1['LastProfit']),
		'</tr>
		<tr class="striped_row">
			<td>&nbsp;</td>
			<td class="text">', __('Dividends'), '</td>';
	// Dividends:
	$Sql = "SELECT
				Sum(CASE WHEN (gltotals.period >= '" . $_POST['PeriodFrom'] . "' AND gltotals.period <= '" . $_POST['PeriodTo'] . "') THEN gltotals.amount ELSE 0 END) AS ActualRetained,
				Sum(CASE WHEN (gltotals.period >= '" . ($_POST['PeriodFrom']-12) . "' AND gltotals.period <= '" . ($_POST['PeriodTo']-12) . "') THEN gltotals.amount ELSE 0 END) AS LastRetained
			FROM chartmaster
				INNER JOIN gltotals ON chartmaster.accountcode=gltotals.account
				INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
			WHERE accountgroups.pandl=0
				AND gltotals.account!='" . $_SESSION['PeriodProfitAccount'] . "'
				AND gltotals.account!='" . $_SESSION['RetainedEarningsAccount'] . "'";// Gets retained earnings by the complement method to include differences. The complement method: Changes(retained earnings) = -Changes(other accounts).
	$Result = DB_query($Sql);
	$MyRow2 = DB_fetch_array($Result);
	echo	colDebitCredit($MyRow2['ActualRetained'] - $MyRow1['ActualProfit']),
			colDebitCredit($MyRow2['LastRetained'] - $MyRow1['LastProfit']),
		'</tr><tr>',
			'<td class="text" colspan="2">', __('Retained earnings'), '</td>',
	// Retained earnings changes:
				colDebitCredit($MyRow2['ActualRetained']),
				colDebitCredit($MyRow2['LastRetained']),
		'</tr>';
	$ActualTotal += $MyRow2['ActualRetained'];
	$LastTotal += $MyRow2['LastRetained'];
	// Cash flows sections:
	$Sql = "SELECT
				chartmaster.cashflowsactivity,
				gltotals.account,
				chartmaster.accountname,
				Sum(CASE WHEN (gltotals.period >= '" . $_POST['PeriodFrom'] . "' AND gltotals.period <= '" . $_POST['PeriodTo'] . "') THEN -gltotals.amount ELSE 0 END) AS ActualAmount,
				Sum(CASE WHEN (gltotals.period >= '" . ($_POST['PeriodFrom']-12) . "' AND gltotals.period <= '" . ($_POST['PeriodTo']-12) . "') THEN -gltotals.amount ELSE 0 END) AS LastAmount
			FROM chartmaster
				INNER JOIN gltotals ON chartmaster.accountcode=gltotals.account
				INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
			WHERE accountgroups.pandl=0 AND chartmaster.cashflowsactivity!=4
			GROUP BY
				gltotals.account,
				chartmaster.accountname,
				chartmaster.cashflowsactivity
			ORDER BY
				chartmaster.cashflowsactivity,
				gltotals.account";
	$Result = DB_query($Sql);
	$IdSection = -1;
	// Looks for an account without setting up:
	$NeedSetup = false;
	while($MyRow = DB_fetch_array($Result)) {
		if($MyRow['cashflowsactivity'] == -1) {
			$NeedSetup = true;
			echo '<tr><td colspan="6">&nbsp;</td></tr>';
			break;
		}
	}
	DB_data_seek($Result,0);
	while($MyRow = DB_fetch_array($Result)) {
		if($IdSection <> $MyRow['cashflowsactivity']) {
			// Prints section total:
			echo '<tr>
		    	<td class="text" colspan="2">', CashFlowsActivityName($IdSection), '</td>',
				colDebitCredit($ActualSection),
				colDebitCredit($LastSection),
		    '</tr>';
			// Resets section totals:
			$ActualSection = 0;
			$LastSection = 0;
			$IdSection = $MyRow['cashflowsactivity'];
			// Prints next section title:
			echo '<tr>
		    		<td class="text" colspan="6"><br /><h2>', CashFlowsActivityName($IdSection), '</h2></td>
		    	</tr>';
		}
		if($MyRow['ActualAmount']<>0
			OR $MyRow['LastAmount']<>0 OR isset($_POST['ShowZeroBalance'])) {

			echo '<tr class="striped_row">
					<td class="text"><a href="', $RootPath, '/GLAccountInquiry.php?PeriodFrom=', $_POST['PeriodFrom'], '&amp;PeriodTo=', $_POST['PeriodTo'], '&amp;Account=', $MyRow['account'], '">', $MyRow['account'], '</a></td>
					<td class="text">', $MyRow['accountname'], '</td>',
					colDebitCredit($MyRow['ActualAmount']),
					colDebitCredit($MyRow['LastAmount']),
				'</tr>';
			$ActualSection += $MyRow['ActualAmount'];
			$ActualTotal += $MyRow['ActualAmount'];
			$LastSection += $MyRow['LastAmount'];
			$LastTotal += $MyRow['LastAmount'];
		}
	}
	// Prints the last section total:
	echo '<tr>
			<td class="text" colspan="2">', CashFlowsActivityName($IdSection), '</td>',
			colDebitCredit($ActualSection),
			colDebitCredit($LastSection),
		'</tr>
		<tr><td colspan="6">&nbsp;</td></tr>',
	// Prints Net increase in cash and cash equivalents:
		'<tr>
			<td class="text" colspan="2"><b>', __('Net increase in cash and cash equivalents'), '</b></td>',
			colDebitCredit($ActualTotal),
			colDebitCredit($LastTotal),
		'</tr>';
	// Prints Cash and cash equivalents at beginning of period:
	if($_POST['ShowCash']) {
		// Prints a detail of Cash and cash equivalents at beginning of period (Parameters: PeriodFrom, PeriodTo, ShowZeroBalance=on/off, ShowCash=ON):
		echo '<tr><td colspan="6">&nbsp;</td></tr>';
		$ActualBeginning = 0;
		$LastBeginning = 0;
		// Calculate beginning balance by summing all prior periods from gltotals
		$Sql = "SELECT
					gltotals.account,
					chartmaster.accountname,
					Sum(CASE WHEN (gltotals.period < '" . $_POST['PeriodFrom'] . "') THEN gltotals.amount ELSE 0 END) AS ActualAmount,
					Sum(CASE WHEN (gltotals.period < '" . ($_POST['PeriodFrom']-12) . "') THEN gltotals.amount ELSE 0 END) AS LastAmount
				FROM chartmaster
					INNER JOIN gltotals ON chartmaster.accountcode=gltotals.account
					INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
				WHERE accountgroups.pandl=0 AND chartmaster.cashflowsactivity=4
				GROUP BY gltotals.account, chartmaster.accountname
				ORDER BY gltotals.account";
		$Result = DB_query($Sql);
		while($MyRow = DB_fetch_array($Result)) {
			if($MyRow['ActualAmount']<>0
				OR $MyRow['LastAmount']<>0 OR isset($_POST['ShowZeroBalance'])) {

				echo '<tr class="striped_row">
						<td class="text"><a href="', $RootPath, '/GLAccountInquiry.php?Period=', $_POST['PeriodFrom'], '&amp;Account=', $MyRow['account'], '">', $MyRow['account'], '</a></td>
						<td class="text">', $MyRow['accountname'], '</td>',
						colDebitCredit($MyRow['ActualAmount']),
						colDebitCredit($MyRow['LastAmount']),
					'</tr>';
				$ActualBeginning += $MyRow['ActualAmount'];
				$LastBeginning += $MyRow['LastAmount'];
			}
		}
	} else {
		// Prints a summary of Cash and cash equivalents at beginning of period (Parameters: PeriodFrom, PeriodTo, ShowZeroBalance=on/off, ShowCash=OFF):
		// Calculate beginning balance by summing all prior periods from gltotals
		$Sql = "SELECT
					Sum(CASE WHEN (gltotals.period < '" . $_POST['PeriodFrom'] . "') THEN gltotals.amount ELSE 0 END) AS ActualAmount,
					Sum(CASE WHEN (gltotals.period < '" . ($_POST['PeriodFrom']-12) . "') THEN gltotals.amount ELSE 0 END) AS LastAmount
				FROM chartmaster
					INNER JOIN gltotals ON chartmaster.accountcode=gltotals.account
					INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
				WHERE accountgroups.pandl=0 AND chartmaster.cashflowsactivity=4";
		$Result = DB_query($Sql);
		$MyRow = DB_fetch_array($Result);
		$ActualBeginning = $MyRow['ActualAmount'];
		$LastBeginning = $MyRow['LastAmount'];
	}
	echo '<tr>
			<td class="text" colspan="2"><b>', __('Cash and cash equivalents at beginning of period'), '</b></td>',
			colDebitCredit($ActualBeginning),
			colDebitCredit($LastBeginning),
		'</tr>';
	// Prints Cash and cash equivalents at end of period:
	if($_POST['ShowCash']) {
		// Prints a detail of Cash and cash equivalents at end of period (Parameters: PeriodFrom, PeriodTo, ShowZeroBalance=on/off, ShowCash=ON):
		echo '<tr><td colspan="6">&nbsp;</td></tr>';
		// Calculate ending balance by summing all periods up to PeriodTo from gltotals
		$Sql = "SELECT
					gltotals.account,
					chartmaster.accountname,
					Sum(CASE WHEN (gltotals.period <= '" . $_POST['PeriodTo'] . "') THEN gltotals.amount ELSE 0 END) AS ActualAmount,
					Sum(CASE WHEN (gltotals.period <= '" . ($_POST['PeriodTo']-12) . "') THEN gltotals.amount ELSE 0 END) AS LastAmount
				FROM chartmaster
					INNER JOIN gltotals ON chartmaster.accountcode=gltotals.account
					INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
				WHERE accountgroups.pandl=0 AND chartmaster.cashflowsactivity=4
				GROUP BY gltotals.account, chartmaster.accountname
				ORDER BY gltotals.account";
		$Result = DB_query($Sql);
		while($MyRow = DB_fetch_array($Result)) {
			if($MyRow['ActualAmount']<>0
				OR $MyRow['LastAmount']<>0 OR isset($_POST['ShowZeroBalance'])) {

				echo '<tr class="striped_row">
						<td class="text"><a href="', $RootPath, '/GLAccountInquiry.php?Period=', $_POST['PeriodTo'], '&amp;Account=', $MyRow['account'], '">', $MyRow['account'], '</a></td>
						<td class="text">', $MyRow['accountname'], '</td>',
						colDebitCredit($MyRow['ActualAmount']),
						colDebitCredit($MyRow['LastAmount']),
					'</tr>';
			}
		}
	}
	// Prints Cash and cash equivalents at end of period total:
	echo '<tr>
			<td class="text" colspan="2"><b>', __('Cash and cash equivalents at end of period'), '</b></td>',
			colDebitCredit($ActualTotal+$ActualBeginning),
			colDebitCredit($LastTotal+$LastBeginning),
		'</tr>';
	// Prints 'Cash or cash equivalent' section if selected (Parameters: PeriodFrom, PeriodTo, ShowZeroBalance=on/off, ShowCash=ON):
	if($_POST['ShowCash']) {
		// Prints 'Cash or cash equivalent' section title:
		echo '<tr><td colspan="6">&nbsp</td><tr>
			<tr>
	    		<td class="text" colspan="6"><br /><h2>', CashFlowsActivityName(4), '</h2></td>
	    	</tr>';
		// Initialise 'Cash or cash equivalent' section accumulators:
		$ActualCash = 0;
		$LastCash = 0;
		$Sql = "SELECT
			gltotals.account,
			chartmaster.accountname,
			Sum(CASE WHEN (gltotals.period >= '" . $_POST['PeriodFrom'] . "' AND gltotals.period <= '" . $_POST['PeriodTo'] . "') THEN gltotals.amount ELSE 0 END) AS ActualAmount,
			Sum(CASE WHEN (gltotals.period >= '" . ($_POST['PeriodFrom']-12) . "' AND gltotals.period <= '" . ($_POST['PeriodTo']-12) . "') THEN gltotals.amount ELSE 0 END) AS LastAmount
		FROM chartmaster
			INNER JOIN gltotals ON chartmaster.accountcode=gltotals.account
			INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
		WHERE accountgroups.pandl=0 AND chartmaster.cashflowsactivity=4
		GROUP BY gltotals.account, chartmaster.accountname
		ORDER BY
			gltotals.account";
		$Result = DB_query($Sql);
		while($MyRow = DB_fetch_array($Result)) {
			if($MyRow['ActualAmount']<>0
				OR $MyRow['LastAmount']<>0 OR isset($_POST['ShowZeroBalance'])) {

				echo '<tr class="striped_row">
						<td class="text"><a href="', $RootPath, '/GLAccountInquiry.php?PeriodFrom=', $_POST['PeriodFrom'], '&amp;PeriodTo=', $_POST['PeriodTo'], '&amp;Account=', $MyRow['account'], '">', $MyRow['account'], '</a></td>
						<td class="text">', $MyRow['accountname'], '</td>',
						colDebitCredit($MyRow['ActualAmount']),
						colDebitCredit($MyRow['LastAmount']),
					'</tr>';
				$ActualCash += $MyRow['ActualAmount'];
				$LastCash += $MyRow['LastAmount'];
			}
		}
		// Prints 'Cash or cash equivalent' section total:
		echo '<tr>
	    	<td class="text" colspan="2">', CashFlowsActivityName(4), '</td>',
			colDebitCredit($ActualCash),
			colDebitCredit($LastCash),
	    '</tr>';
	}
	echo '</tbody></table>',
		'</div>';// div id="Report".
	if(!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
		echo // Shows a form to select an action after the report was shown:
			'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
			'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
			// Resend report parameters:
			'<input name="PeriodFrom" type="hidden" value="', $_POST['PeriodFrom'], '" />',
			'<input name="PeriodTo" type="hidden" value="', $_POST['PeriodTo'], '" />',
			'<input name="ShowZeroBalance" type="hidden" value="', $_POST['ShowZeroBalance'], '" />',
			'<input name="ShowCash" type="hidden" value="', $_POST['ShowCash'], '" />',
			'<div class="centre noPrint">'; // Form buttons:
		if($NeedSetup) {
			echo '<button onclick="window.location=\'GLCashFlowsSetup.php\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/maintenance.png" /> ', __('Run Setup'), '</button>'; // "Run Setup" button.
		}
		echo	'<button onclick="window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/printer.png" /> ', __('Print'), '</button>', // "Print" button.
				'<button name="NewReport" type="submit" value="on"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/reports.png" /> ', __('New Report'), '</button>', // "New Report" button.
				'<button onclick="window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/return.svg" /> ', __('Return'), '</button>', // "Return" button.
			'</div>';
	}
} else {// If one or more parameters are NOT set or NOT valid, shows a parameters input form:
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/printer.png" title="', // Icon image.
		$Title, '" /> ', // Icon title.
		$Title, '</p>';// Page title.
	fShowPageHelp(// Shows the page help text if $_SESSION['ShowFieldHelp'] is true or is not set
		__('The statement of cash flows, also known as the successor of the old source and application of funds statement, reports how changes in balance sheet accounts and income affect cash and cash equivalents, and breaks the analysis down to operating, investing and financing activities.') . '<br />' .
		__('The purpose of the statement of cash flows is to show where the company got their money from and how it was spent during the period being reported for a user selectable range of periods.') . '<br />' .
		__('The statement of cash flows represents a period of time. This contrasts with the statement of financial position, which represents a single moment in time.') . '<br />' .
		__('webERP is an accrual based system (not a cash based system). Accrual systems include items when they are invoiced to the customer, and when expenses are owed based on the supplier invoice date.'));// Function fShowPageHelp() in ~/includes/MiscFunctions.php
	echo // Shows a form to input the report parameters:
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />'; // Input table:
		// Input table:

	if(!isset($_POST['PeriodTo'])) {
		$_POST['ShowZeroBalance'] = '';
		$_POST['ShowCash'] = '';
	}

	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>'; // Content of the header and footer of the input table:

	echo '<field>
			<label for="PeriodFrom">', __('Select period from'), '</label>
		 	<select id="PeriodFrom" name="PeriodFrom" required="required">';
	// Select period from:
			'<field>
				<label for="PeriodFrom">' . __('Select period from') . '</label>
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

	$Period = GetPeriod($FromDate);

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
		<fieldhelp>' . __('Select the beginning of the reporting period') . '</fieldhelp>
	</field>';

	// Select period to:
	if(!isset($_POST['PeriodTo'])) {
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
	while($MyRow = DB_fetch_array($Periods)) {
		echo '<option',($MyRow['periodno'] == $_POST['PeriodTo'] ? ' selected="selected"' : '' ) . ' value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
	}
	echo  '</select>
		<fieldhelp>' . __('Select the end of the reporting period') . '</fieldhelp>
	</field>';

	// OR Select period:
	if(!isset($_POST['Period'])) {
		$_POST['Period'] = '';
	}

	echo '<field>
			<label for="Period">', '<b>' . __('OR') . ' </b>' . __('Select Period'), '</label>
			', ReportPeriodList($_POST['Period'], array('l', 't')),
			'<fieldhelp>', __('Select a period instead of using the beginning and end of the reporting period.'), '</fieldhelp>
		</field>',
	// Show accounts with zero balance:
			'<field>
			 	<label for="ShowZeroBalance">', __('Show accounts with zero balance'), '</label>
			 	<input', ($_POST['ShowZeroBalance'] ? ' checked="checked"' : ''), ' id="ShowZeroBalance" name="ShowZeroBalance" type="checkbox">
			 	<fieldhelp>', __('Check this box to show all accounts including those with zero balance'), '</fieldhelp>
			</field>',
	// Show cash and cash equivalents accounts:
			'<field>
				<label for="ShowCash">', __('Show cash and cash equivalents accounts'), '</label>
			 	<input',($_POST['ShowCash'] ? ' checked="checked"' : ''), ' id="ShowCash" name="ShowCash" type="checkbox">
			 	<fieldhelp>', __('Check this box to show cash and cash equivalents accounts'), '</fieldhelp>
			</field>
		</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" title="PDF" value="'.__('PDF Cash Flow Statement').'" />
			<input type="submit" name="View" title="View" value="' . __('Show Cash Flow Statement') .'" />
		</div>',
		'</form>';
}
echo	'</form>';

if(!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
	include('includes/footer.php');
}
