<?php

/**
 * GLAccountGraph.php
 * By Paul Becker
 *
 * This script generates a graph visualizing General Ledger (GL) account transactions
 * over a selected period. It allows users to choose a specific GL account,
 * define a date range (either by selecting start and end periods or a predefined period),
 * select the type of graph (bar, line, pie, etc.), and choose whether to display
 * the periodic variation or the cumulative account value.
 *
 * Features:
 * - Selection of GL account accessible to the user.
 * - Flexible period selection (From/To periods or predefined report periods).
 * - Various graph types available (bars, lines, pie, area, etc.).
 * - Option to display transaction amounts (variation) or cumulative account balance (value).
 * - Option to invert the graph values.
 * - Uses the PHPlot library for graph generation.
 * - Displays the generated graph inline and provides an option to re-select criteria.
 *
 * Workflow:
 * 1. Includes necessary session and header files.
 * 2. Checks if form data (Account, PeriodFrom, PeriodTo, etc.) is submitted.
 * 3. If form data is not present or invalid (e.g., PeriodFrom > PeriodTo), it displays the selection form:
 *    - Fetches available GL accounts for the user.
 *    - Provides options for graph type, display type (variation/value), and period selection.
 *    - Includes a submit button to generate the graph.
 * 4. If valid form data is submitted:
 *    - Constructs the graph title based on selected criteria.
 *    - Builds the SQL query based on the selected display type (variation or value).
 *    - Fetches the GL transaction data for the selected account and period range.
 *    - Initializes and configures the PHPlot object with selected options (title, type, colors, etc.).
 *    - Formats the fetched data into an array suitable for PHPlot.
 *    - Generates the graph image and saves it to the server.
 *    - Displays the generated graph image on the page.
 *    - Provides a link to return to the selection form.
 * 5. Includes the footer file.
 */

require(__DIR__ . '/includes/session.php');

$Title = __('GL Account Graph');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountGraph';
include('includes/header.php');

include('includes/GLFunctions.php');

$NewReport = '';
$SelectedAccount = '';

if (isset($_POST['Account'])) {
	$SelectedAccount = $_POST['Account'];
} elseif (isset($_GET['Account'])) {
	$SelectedAccount = $_GET['Account'];
}

if (isset($_POST['Period']) and $_POST['Period'] != '') {
	$_POST['PeriodFrom'] = ReportPeriod($_POST['Period'], 'From');
	$_POST['PeriodTo'] = ReportPeriod($_POST['Period'], 'To');
}

if (isset($_POST['PeriodFrom']) and isset($_POST['PeriodTo'])) {

	if ($_POST['PeriodFrom'] > $_POST['PeriodTo']) {
		prnMsg(__('The selected period from is actually after the period to! Please re-select the reporting period'), 'error');
		$NewReport = 'on';
	}

}

if ((!isset($_POST['PeriodFrom']) or !isset($_POST['PeriodTo'])) or $NewReport == 'on') {

	echo '<form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>
			<field>
				<label for="Account">' . __('Select GL Account') . ':</label>
				<select name="Account">';

	$SQL = "SELECT chartmaster.accountcode,
				bankaccounts.accountcode AS bankact,
				bankaccounts.currcode,
				chartmaster.accountname
			FROM chartmaster
			LEFT JOIN bankaccounts
				ON chartmaster.accountcode = bankaccounts.accountcode
			INNER JOIN glaccountusers
				ON glaccountusers.accountcode = chartmaster.accountcode
					AND glaccountusers.userid = '" . $_SESSION['UserID'] . "'
					AND glaccountusers.canview = 1
			ORDER BY chartmaster.accountcode";
	$AccountResult = DB_query($SQL);
	$BankAccount = false;
	while ($MyRow = DB_fetch_array($AccountResult)) {
		if ($MyRow['accountcode'] == $SelectedAccount) {
			if (!is_null($MyRow['bankact'])) {
				$BankAccount = true;
			}
			echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">'
				. $MyRow['accountcode'] . ' ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false)
				. '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] . '">'
				. $MyRow['accountcode'] . ' ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false)
				. '</option>';
		}
	}
	echo '</select>
			</td>
		</field>';

	echo '<field>
			<label for="GraphType">' . __('Graph Type') . '</label>
			<select name="GraphType">
					<option value="bars">' . __('Bar Graph') . '</option>
					<option value="stackedbars">' . __('Stacked Bar Graph') . '</option>
					<option value="lines">' . __('Line Graph') . '</option>
					<option value="linepoints">' . __('Line Point Graph') . '</option>
					<option value="area">' . __('Area Graph') . '</option>
					<option value="points">' . __('Points Graph') . '</option>
					<option value="pie">' . __('Pie Graph') . '</option>
					<option value="thinbarline">' . __('Thin Bar Line Graph') . '</option>
					<option value="squared">' . __('Squared Graph') . '</option>
					<option value="stackedarea">' . __('Stacked Area Graph') . '</option>
				</select>
			</field>';

	echo '<field>
			<label for="DisplayType">' . __('Display Type') . '</label>
			<select name="DisplayType">
				<option selected="selected" value="variation">' . __('Variation') . '</option>
				<option value="value">' . __('Value') . '</option>
			</select>
		</field>';

	echo '<field>
			<label for="InvertGraph">', __('Invert Graph'), '</label>
			<input type="checkbox" name="InvertGraph" />
		</field>';

	echo '<field>
			<label for="PeriodFrom">' . __('Select Period From') . ':</label>
			<select name="PeriodFrom">';

	if (Date('m') > $_SESSION['YearEnd']) {
		/*Dates in SQL format */
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
	} else {
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y') - 1));
	}
	$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno";
	$Periods = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Periods)) {
		if (isset($_POST['PeriodFrom']) and $_POST['PeriodFrom'] != '') {
			if ($_POST['PeriodFrom'] == $MyRow['periodno']) {
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">'
					. MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $MyRow['periodno'] . '">'
					. MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		} else {
			if ($MyRow['lastdate_in_period'] == $DefaultFromDate) {
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">'
					. MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $MyRow['periodno'] . '">'
					. MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		}
	}

	echo '</select>
		</field>';
	if (!isset($_POST['PeriodTo']) or $_POST['PeriodTo'] == '') {
		$DefaultPeriodTo = GetPeriod(DateAdd(ConvertSQLDate($DefaultFromDate), 'm', 11));
	} else {
		$DefaultPeriodTo = $_POST['PeriodTo'];
	}

	echo '<field>
			<label for="PeriodTo">' . __('Select Period To') . ':</label>
			<select name="PeriodTo">';

	DB_data_seek($Periods, 0);

	while ($MyRow = DB_fetch_array($Periods)) {

		if ($MyRow['periodno'] == $DefaultPeriodTo) {
			echo '<option selected="selected" value="' . $MyRow['periodno'] . '">'
				. MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value ="' . $MyRow['periodno'] . '">'
				. MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
	}
	echo '</select>
		</field>';

	if (!isset($_POST['Period'])) {
		$_POST['Period'] = '';
	}

	echo '<field>
			<label for="Period">', '<b>', __('OR'), ' </b>', __('Select Period'), '</label>
			' . ReportPeriodList($_POST['Period']) . '
		</field>';

	echo '</fieldset>
			<div class="centre">
				<input type="submit" name="ShowGraph" value="' . __('Show Account Graph') . '" />
			</div>
		</form>';
	include('includes/footer.php');
} else {

	$GraphTitle = '';
	$AccountName = GetGLAccountName($SelectedAccount);

	if ($_POST['DisplayType'] == 'value') {
		$GraphTitle = $AccountName . ' ' . __('GL Account Graph - Account Value') . "\n\r";
	} else {
		$GraphTitle = $AccountName . ' ' . __('GL Account Graph - Actual Transactions') . "\n\r";
	}
	$SQL = "SELECT YEAR(`lastdate_in_period`) AS year,
					MONTHNAME(`lastdate_in_period`) AS month
			FROM `periods`
			WHERE `periodno` = '" . $_POST['PeriodFrom'] . "'
				OR periodno = '" . $_POST['PeriodTo'] . "'";

	$Result = DB_query($SQL);

	$PeriodFromRow = DB_fetch_array($Result);
	$Starting = $PeriodFromRow['month'] . ' ' . $PeriodFromRow['year'];

	$PeriodToRow = DB_fetch_array($Result);
	$Ending = $PeriodToRow['month'] . ' ' . $PeriodToRow['year'];

	$GraphTitle .= ' ' . __('From Period') . ' ' . $Starting . ' ' . __('to') . ' ' . $Ending . "\n\r";

	if ($_POST['DisplayType'] == 'value') {
		// Calculate cumulative value
		$SQL = "SELECT p_to.periodno,
					   p_to.lastdate_in_period,
					   (SELECT SUM(gltotals.amount)
						FROM gltotals
						WHERE gltotals.account = '" . $SelectedAccount . "'
							AND gltotals.period <= p_to.periodno) AS cumulative_actual
				FROM periods p_to
				WHERE p_to.periodno >= '" . $_POST['PeriodFrom'] . "'
					AND p_to.periodno <= '" . $_POST['PeriodTo'] . "'
				ORDER BY p_to.periodno";
		$DataColumn = 'cumulative_actual';
		$LegendText = __('Value');
	} else {
		// Show variation per period (original query)
		$SQL = "SELECT periods.periodno,
					periods.lastdate_in_period,
					COALESCE(gltotals.amount, 0) AS actual
				FROM periods
				LEFT JOIN gltotals
					ON periods.periodno = gltotals.period
						AND gltotals.account = '" . $SelectedAccount . "'
				WHERE periods.periodno >= '" . $_POST['PeriodFrom'] . "'
					AND periods.periodno <= '" . $_POST['PeriodTo'] . "'
				GROUP BY periods.periodno,
						 periods.lastdate_in_period,
						 gltotals.amount
				ORDER BY periods.periodno";
		$DataColumn = 'actual';
		$LegendText = __('Actual');
	}

	$Graph = new PHPlot(1200, 600);
	$Graph->SetTitle($GraphTitle);
	$Graph->SetTitleColor('blue');
	$Graph->SetOutputFile('companies/' . $_SESSION['DatabaseName'] . '/reports/glaccountgraph.png');
	$Graph->SetXTitle(__('Month'));

	$Graph->SetXTickPos('none');
	$Graph->SetXTickLabelPos('none');
	$Graph->SetXLabelAngle(90);
	$Graph->SetBackgroundColor('white');
	$Graph->SetFileFormat('png');
	$Graph->SetPlotType($_POST['GraphType']);
	$Graph->SetIsInline('1');
	$Graph->SetShading(5);
	$Graph->SetDrawYGrid(true);
	$Graph->SetDataType('text-data');
	$Graph->TuneYAutoRange(0, 0, 0);
	$Graph->SetNumberFormat($DecimalPoint, $ThousandsSeparator);
	$Graph->SetPrecisionY($_SESSION['CompanyRecord']['decimalplaces']);

	$SalesResult = DB_query($SQL);
	if (DB_error_no() != 0) {

		prnMsg(__('The GL Account graph data for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg(), 'error');
		include('includes/footer.php');
		exit();
	}
	if (DB_num_rows($SalesResult) == 0) {
		prnMsg(__('There is not GL Account data for the criteria entered to graph'), 'info');
		include('includes/footer.php');
		exit();
	}

	$GraphArray = array();
	$i = 0;
	while ($MyRow = DB_fetch_array($SalesResult)) {
		$Value = isset($_POST['InvertGraph']) ? -$MyRow[$DataColumn] : $MyRow[$DataColumn];
		$GraphArray[$i] = array(MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), $Value);
		$i++;
	}

	$Graph->SetDataValues($GraphArray);
	$Graph->SetDataColors(
		array('grey'), //Data Colors
		array('black') //Border Colors
	);
	$Graph->SetLegend(array($LegendText));
	$Graph->SetYDataLabelPos('plotin');

	//Draw it
	$Graph->DrawGraph();
	echo '<table class="selection">
			<tr>
				<td><img class="graph" src="companies/' . $_SESSION['DatabaseName'] . '/reports/glaccountgraph.png" alt="Sales Report Graph"></img></td>
			</tr>
		  </table>';

	echo '<div class="noPrint centre">
			<a href="', basename(__FILE__), '">', __('Select Different Criteria'), '</a>
		</div>';
	include('includes/footer.php');
}
