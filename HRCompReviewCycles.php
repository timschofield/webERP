<?php

/* HR Compensation Review Cycles Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Compensation Review Cycles');
$ViewTopic = 'HumanResources';
$BookMark = 'HRCompReviewCycles';

include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/money_add.png" title="' . __('Review Cycles') . '" /> ' .
		__('Compensation Review Cycles') . '
	</p>';

// Handle form submission
if (isset($_POST['Submit'])) {

	$InputError = 0;

	if (trim($_POST['CycleName']) == '') {
		$InputError = 1;
		prnMsg(__('The cycle name must not be empty'), 'error');
	}
	if ($InputError != 1) {

		if (isset($_POST['CycleID']) && $_POST['CycleID'] > 0) {
			// Update existing cycle
			$SQL = "UPDATE hrcompreviewcycles SET
						cyclename = '" . $_POST['CycleName'] . "',
						fiscalyear = " . (int)$_POST['FiscalYear'] . ",
						startdate = '" . $_POST['StartDate'] . "',
						enddate = '" . $_POST['EndDate'] . "',
						budgetamount = " . filter_var($_POST['BudgetAmount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						status = '" . $_POST['Status'] . "'
					WHERE reviewcycleid = " . (int)$_POST['CycleID'];

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Review cycle has been updated successfully'), 'success');
			}
		} else {
			// Insert new cycle
			$SQL = "INSERT INTO hrcompreviewcycles (
						cyclename, fiscalyear, startdate, enddate,
						budgetamount, status
					) VALUES (
						'" . $_POST['CycleName'] . "',
						" . (int)$_POST['FiscalYear'] . ",
						'" . $_POST['StartDate'] . "',
						'" . $_POST['EndDate'] . "',
						" . filter_var($_POST['BudgetAmount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						'" . $_POST['Status'] . "'
					)";

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Review cycle has been created successfully'), 'success');
			}
		}
		unset($_POST['CycleID']);
	}
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['CycleID'])) {
	$SQL = "DELETE FROM hrcompreviewcycles WHERE reviewcycleid = " . (int)$_GET['CycleID'];
	$Result = DB_query($SQL);
	if ($Result) {
		prnMsg(__('Review cycle has been deleted successfully'), 'success');
	}
}

// Display cycles list with statistics
echo '<table class="selection">
		<tr>
			<th>' . __('Cycle Name') . '</th>
			<th>' . __('Fiscal Year') . '</th>
			<th>' . __('Start Date') . '</th>
			<th>' . __('End Date') . '</th>
			<th>' . __('Budget Amount') . '</th>
			<th>' . __('Status') . '</th>
			<th>' . __('Actions') . '</th>
		</tr>';

$SQL = "SELECT * FROM hrcompreviewcycles ORDER BY fiscalyear DESC, startdate DESC";
$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	echo '<tr><td colspan="7">' . __('No review cycles defined') . '</td></tr>';
} else {
	while ($Row = DB_fetch_array($Result)) {
		// Get cycle statistics
		$SQL_Stats = "SELECT COUNT(*) as reviewcount
					FROM hremployeecompensation
					WHERE effectivedate BETWEEN '" . $Row['startdate'] . "' AND '" . $Row['enddate'] . "'";
		$StatsResult = DB_query($SQL_Stats);
		$StatsRow = DB_fetch_array($StatsResult);
		$ReviewCount = $StatsRow['reviewcount'];

		$StatusClass = '';
		switch ($Row['status']) {
			case 'Planning':
				$StatusClass = 'background-color: #FFF9C4;';
				break;
			case 'Active':
				$StatusClass = 'background-color: #C8E6C9;';
				break;
			case 'Completed':
				$StatusClass = 'background-color: #E0E0E0;';
				break;
			case 'Cancelled':
				$StatusClass = 'background-color: #FFCDD2;';
				break;
		}

		echo '<tr style="' . $StatusClass . '">
				<td><strong>' . $Row['cyclename'] . '</strong><br /><small>' . $ReviewCount . ' ' . __('reviews') . '</small></td>
				<td>' . $Row['fiscalyear'] . '</td>
				<td>' . ConvertSQLDate($Row['startdate']) . '</td>
				<td>' . ConvertSQLDate($Row['enddate']) . '</td>
				<td class="number">' . ($Row['budgetamount'] > 0 ? locale_number_format($Row['budgetamount'], $_SESSION['CompanyRecord']['decimalplaces']) : '-') . '</td>
				<td>' . __($Row['status']) . '</td>
				<td>
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?view=' . $Row['reviewcycleid'] . '">' . __('View') . '</a> |
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?edit=' . $Row['reviewcycleid'] . '">' . __('Edit') . '</a> |
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?delete=1&CycleID=' . $Row['reviewcycleid'] . '" onclick="return confirm(\'' . __('Are you sure you want to delete this review cycle?') . '\');">' . __('Delete') . '</a>
				</td>
			</tr>';
	}
}

echo '</table>';

// View cycle details
if (isset($_GET['view'])) {
	$CycleID = (int)$_GET['view'];

	$SQL = "SELECT * FROM hrcompreviewcycles WHERE reviewcycleid = " . $CycleID;
	$Result = DB_query($SQL);
	$CycleRow = DB_fetch_array($Result);

	echo '<br /><h3>' . __('Review Cycle Details') . ': ' . $CycleRow['cyclename'] . '</h3>';

	echo '<table class="selection">
			<tr>
				<th colspan="2">' . __('Cycle Information') . '</th>
			</tr>
			<tr>
				<td width="30%"><strong>' . __('Fiscal Year') . ':</strong></td>
				<td>' . $CycleRow['fiscalyear'] . '</td>
			</tr>
			<tr>
				<td><strong>' . __('Status') . ':</strong></td>
				<td>' . __($CycleRow['status']) . '</td>
			</tr>
			<tr>
				<td><strong>' . __('Period') . ':</strong></td>
				<td>' . ConvertSQLDate($CycleRow['startdate']) . ' to ' . ConvertSQLDate($CycleRow['enddate']) . '</td>
			</tr>
			<tr>
				<td><strong>' . __('Effective Date') . ':</strong></td>
				<td>' . ($CycleRow['effectivedate'] ? ConvertSQLDate($CycleRow['effectivedate']) : __('Not Set')) . '</td>
			</tr>
			<tr>
				<td><strong>' . __('Budget Amount') . ':</strong></td>
				<td>' . ($CycleRow['budgetamount'] > 0 ? locale_number_format($CycleRow['budgetamount'], $_SESSION['CompanyRecord']['decimalplaces']) : __('Not Set')) . '</td>
			</tr>
			<tr>
				<td><strong>' . __('Budget Percentage') . ':</strong></td>
				<td>' . ($CycleRow['budgetpercentage'] > 0 ? number_format($CycleRow['budgetpercentage'], 2) . '%' : __('Not Set')) . '</td>
			</tr>
			<tr>
				<td><strong>' . __('Description') . ':</strong></td>
				<td>' . nl2br($CycleRow['description']) . '</td>
			</tr>
		</table>';

	// Get cycle statistics
	$SQL = "SELECT
				COUNT(*) as totalreviews,
				SUM(increaseamount) as totalincreases,
				AVG(increasepercentage) as avgincrease
			FROM hremployeecompensation
			WHERE effectivedate BETWEEN '" . $CycleRow['startdate'] . "' AND '" . $CycleRow['enddate'] . "'";
	$StatsResult = DB_query($SQL);
	$StatsRow = DB_fetch_array($StatsResult);

	echo '<br /><table class="selection">
			<tr>
				<th colspan="2">' . __('Cycle Statistics') . '</th>
			</tr>
			<tr>
				<td width="30%"><strong>' . __('Total Reviews') . ':</strong></td>
				<td>' . $StatsRow['totalreviews'] . '</td>
			</tr>
			<tr>
				<td><strong>' . __('Total Increases') . ':</strong></td>
				<td>' . ($StatsRow['totalincreases'] ? locale_number_format($StatsRow['totalincreases'], $_SESSION['CompanyRecord']['decimalplaces']) : '0.00') . '</td>
			</tr>
			<tr>
				<td><strong>' . __('Average Increase %') . ':</strong></td>
				<td>' . ($StatsRow['avgincrease'] ? number_format($StatsRow['avgincrease'], 2) . '%' : '0.00%') . '</td>
			</tr>';

	if ($CycleRow['budgetamount'] > 0 && $StatsRow['totalincreases'] > 0) {
		$BudgetUsed = ($StatsRow['totalincreases'] / $CycleRow['budgetamount']) * 100;
		echo '<tr>
				<td><strong>' . __('Budget Used') . ':</strong></td>
				<td>' . number_format($BudgetUsed, 2) . '%</td>
			</tr>';
	}

	echo '</table>';

	echo '<div class="centre"><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Back to List') . '</a></div>';
}

// Add/Edit form
if (isset($_GET['edit']) || isset($_GET['new'])) {
	$CycleID = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
	$CycleName = '';
	$FiscalYear = date('Y');
	$StartDate = '';
	$EndDate = '';
	$BudgetAmount = 0;
	$Status = 'Planning';

	if ($CycleID > 0) {
		$SQL = "SELECT * FROM hrcompreviewcycles WHERE reviewcycleid = " . $CycleID;
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) > 0) {
			$Row = DB_fetch_array($Result);
			$CycleName = $Row['cyclename'];
			$FiscalYear = $Row['fiscalyear'];
			$StartDate = ConvertSQLDate($Row['startdate']);
			$EndDate = ConvertSQLDate($Row['enddate']);
			$BudgetAmount = $Row['budgetamount'];
			$Status = $Row['status'];
		}
	}

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


	echo '<fieldset>
			<legend>' . ($CycleID > 0 ? __('Edit Review Cycle') : __('Add Review Cycle')) . '</legend>';
	if ($CycleID > 0) {
		echo '<input type="hidden" name="CycleID" value="' . $CycleID . '" />';
	}

	echo '<field>
				<label for="CycleName">' . __('Cycle Name') . ':</label>
				<input type="text" name="CycleName" value="' . $CycleName . '" size="50" maxlength="100" required="required" />
			</field>

			<field>
				<label for="FiscalYear">' . __('Fiscal Year') . ':</label>
				<input type="number" name="FiscalYear" value="' . $FiscalYear . '" min="2000" max="2100" required="required" />
			</field>

			<field>
				<label for="StartDate">' . __('Start Date') . ':</label>
				<input type="date" name="StartDate" class="date" value="' . $StartDate . '" required="required" />
			</field>

			<field>
				<label for="EndDate">' . __('End Date') . ':</label>
				<input type="date" name="EndDate" class="date" value="' . $EndDate . '" required="required" />
			</field>

			<field>
				<label for="BudgetAmount">' . __('Budget Amount') . ':</label>
				<input type="number" name="BudgetAmount" value="' . $BudgetAmount . '" step="0.01" />
			</field>

			<field>
				<label for="Status">' . __('Status') . ':</label>
				<select name="Status">
					<option value="Planning"' . ($Status == 'Planning' ? ' selected="selected"' : '') . '>' . __('Planning') . '</option>
					<option value="Active"' . ($Status == 'Active' ? ' selected="selected"' : '') . '>' . __('Active') . '</option>
					<option value="Completed"' . ($Status == 'Completed' ? ' selected="selected"' : '') . '>' . __('Completed') . '</option>
					<option value="Cancelled"' . ($Status == 'Cancelled' ? ' selected="selected"' : '') . '>' . __('Cancelled') . '</option>
				</select>
			</field>
		</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="Submit" value="' . __('Save') . '" />
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Cancel') . '</a>
		</div>';
	echo '</form>';
}

if (!isset($_GET['edit']) && !isset($_GET['new']) && !isset($_GET['view'])) {
	echo '<div class="centre">
			<br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?new=1">' . __('Add New Review Cycle') . '</a>
		</div>';
}

include(__DIR__ . '/includes/footer.php');

?>
