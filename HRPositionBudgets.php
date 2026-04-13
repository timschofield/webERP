<?php

/* Position Budgets Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Position Budgets');
$ViewTopic = 'HumanResources';
$BookMark = 'HRPositionBudgets';

include(__DIR__ . '/includes/header.php');

echo '<a class="toplink" href="' . $RootPath . '/HRDashboard.php">' . __('Return to HR Dashboard') . '</a>';

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/money.png" title="' . __('Budgets') . '" /> ' .
		__('Position Budgets Management') . '
	</p>';

// Handle form submission
if (isset($_POST['Submit'])) {

	$InputError = 0;

	if (!isset($_POST['PositionID']) || $_POST['PositionID'] == '') {
		$InputError = 1;
		prnMsg(__('Position must be selected'), 'error');
	}
	if (!isset($_POST['FiscalYear']) || $_POST['FiscalYear'] == '') {
		$InputError = 1;
		prnMsg(__('Fiscal year is required'), 'error');
	}

	if ($InputError == 0) {

		$PositionID = (int)$_POST['PositionID'];
		$DepartmentID = isset($_POST['DepartmentID']) && $_POST['DepartmentID'] != '' ? (int)$_POST['DepartmentID'] : 'NULL';
		$FiscalYear = (int)$_POST['FiscalYear'];
		$BudgetedPositions = isset($_POST['BudgetedPositions']) ? (float)$_POST['BudgetedPositions'] : 'NULL';
		$BudgetedSalary = isset($_POST['BudgetedSalary']) ? (float)$_POST['BudgetedSalary'] : 'NULL';
		$FilledPositions = isset($_POST['FilledPositions']) ? (float)$_POST['FilledPositions'] : 'NULL';
		$ActualPositions = isset($_POST['ActualPositions']) ? (float)$_POST['ActualPositions'] : 'NULL';
		$ActualSalary = isset($_POST['ActualSalary']) ? (float)$_POST['ActualSalary'] : 'NULL';
		$Notes = $_POST['Notes'];

		if (isset($_GET['BudgetID']) && $_GET['BudgetID'] != '') {
			// Update
			$BudgetID = (int)$_GET['BudgetID'];

			$SQL = "UPDATE hrpositionbudgets SET
					positionid = " . $PositionID . ",
					departmentid = " . $DepartmentID . ",
					fiscalyear = " . $FiscalYear . ",
					budgetedpositions = " . $BudgetedPositions . ",
					budgetedsalary = " . $BudgetedSalary . ",
					filledpositions = " . $FilledPositions . ",
					actualpositions = " . $ActualPositions . ",
					actualsalary = " . $ActualSalary . ",
					notes = '" . $Notes . "'
				WHERE budgetid = " . $BudgetID;

			$Result = DB_query($SQL);
			prnMsg(__('Position budget has been updated'), 'success');

		} else {
			// Insert
			$SQL = "INSERT INTO hrpositionbudgets (
					positionid,
					departmentid,
					fiscalyear,
					budgetedpositions,
					budgetedsalary,
					filledpositions,
					actualpositions,
					actualsalary,
					notes
				) VALUES (
					" . $PositionID . ",
					" . $DepartmentID . ",
					" . $FiscalYear . ",
					" . $BudgetedPositions . ",
					" . $BudgetedSalary . ",
					" . $FilledPositions . ",
					" . $ActualPositions . ",
					" . $ActualSalary . ",
					'" . $Notes . "'
				)";

			$Result = DB_query($SQL);
			prnMsg(__('Position budget has been created'), 'success');
		}
	}
}

// Handle delete
if (isset($_GET['Delete']) && isset($_GET['BudgetID'])) {
	$BudgetID = (int)$_GET['BudgetID'];

	$SQL = "DELETE FROM hrpositionbudgets WHERE budgetid = " . $BudgetID;
	$Result = DB_query($SQL);

	prnMsg(__('Position budget has been deleted'), 'success');
	unset($_GET['BudgetID']);
}

// Load for edit
if (isset($_GET['BudgetID'])) {
	$BudgetID = (int)$_GET['BudgetID'];

	$SQL = "SELECT * FROM hrpositionbudgets WHERE budgetid = " . $BudgetID;
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);

		$PositionID = $MyRow['positionid'];
		$DepartmentID = $MyRow['departmentid'];
		$FiscalYear = $MyRow['fiscalyear'];
		$BudgetedPositions = $MyRow['budgetedpositions'];
		$BudgetedSalary = $MyRow['budgetedsalary'];
		$ActualPositions = $MyRow['actualpositions'];
		$ActualSalary = $MyRow['actualsalary'];
		$Notes = $MyRow['notes'];
	}
}

// Entry form
if (!isset($PositionID)) {
	$PositionID = '';
	$DepartmentID = '';
	$FiscalYear = date('Y');
	$BudgetedPositions = 1;
	$BudgetedSalary = 0;
	$ActualPositions = 0;
	$ActualSalary = 0;
	$Notes = '';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . (isset($_GET['BudgetID']) ? '?BudgetID=' . urlencode($_GET['BudgetID']) : '') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . (isset($_GET['BudgetID']) ? __('Edit Position Budget') : __('Create Position Budget')) . '</legend>';

echo '<field>
		<label for="PositionID">' . __('Position') . ':</label>
		<select name="PositionID" required>
			<option value="">' . __('Select Position') . '</option>';

$SQL = "SELECT positionid, positioncode, positiontitle FROM hrpositions ORDER BY positioncode";
$Result = DB_query($SQL);
while ($PosRow = DB_fetch_array($Result)) {
	echo '<option value="' . $PosRow['positionid'] . '"' . ($PositionID == $PosRow['positionid'] ? ' selected' : '') . '>' .
		htmlspecialchars($PosRow['positioncode'] . ' - ' . $PosRow['positiontitle'], ENT_QUOTES, 'UTF-8') . '</option>';
}

echo '</select>
	</field>';

echo '<field>
		<label for="DepartmentID">' . __('Department') . ':</label>
		<select name="DepartmentID">
			<option value="">' . __('Select Department') . '</option>';

$SQL = "SELECT departmentid, departmentcode, description FROM departments ORDER BY departmentcode";
$Result = DB_query($SQL);
while ($DeptRow = DB_fetch_array($Result)) {
	echo '<option value="' . $DeptRow['departmentid'] . '"' . ($DepartmentID == $DeptRow['departmentid'] ? ' selected' : '') . '>' .
		htmlspecialchars($DeptRow['departmentcode'] . ' - ' . $DeptRow['description'], ENT_QUOTES, 'UTF-8') . '</option>';
}

echo '</select>
	</field>';

echo '<field>
		<label for="FiscalYear">' . __('Fiscal Year') . ':</label>
		<input type="number" name="FiscalYear" min="2000" max="2100" value="' . $FiscalYear . '" required />
	</field>';

echo '<field>
		<label for="BudgetedPositions">' . __('Budgeted Positions') . ':</label>
		<input type="number" name="BudgetedPositions" min="0" step="0.01" value="' . $BudgetedPositions . '" required />
	</field>';

echo '<field>
		<label for="BudgetedSalary">' . __('Budgeted Salary') . ':</label>
		<input type="number" name="BudgetedSalary" step="0.01" min="0" value="' . $BudgetedSalary . '" required />
	</field>';

echo '<field>
		<label for="ActualPositions">' . __('Actual Positions') . ':</label>
		<input type="number" name="ActualPositions" min="0" step="0.01" value="' . $ActualPositions . '" />
	</field>';

echo '<field>
		<label for="ActualSalary">' . __('Actual Salary') . ':</label>
		<input type="number" name="ActualSalary" step="0.01" min="0" value="' . $ActualSalary . '" />
	</field>';

echo '<field>
		<label for="Notes">' . __('Notes') . ':</label>
		<textarea name="Notes" rows="3" cols="60">' . htmlspecialchars($Notes, ENT_QUOTES, 'UTF-8') . '</textarea>
	</field>';
echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Save Budget') . '" />
	</div>';

echo '</form>';

// List budgets
$SQL = "SELECT
		b.*,
		p.positioncode,
		p.positiontitle,
		d.description
	FROM hrpositionbudgets b
	INNER JOIN hrpositions p ON b.positionid = p.positionid
	LEFT JOIN departments d ON b.departmentid = d.departmentid
	ORDER BY b.fiscalyear DESC, p.positioncode";

$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {

echo '<table class="selection">
		<thead>
			<tr>
				<th>' . __('Fiscal Year') . '</th>
				<th>' . __('Position') . '</th>
				<th>' . __('Department') . '</th>
				<th>' . __('Budgeted Positions') . '</th>
				<th>' . __('Budgeted Salary') . '</th>
				<th>' . __('Actual Positions') . '</th>
				<th>' . __('Actual Salary') . '</th>
				<th>' . __('Actions') . '</th>
			</tr>
		</thead>
		<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>' . $MyRow['fiscalyear'] . '</td>
				<td>' . htmlspecialchars($MyRow['positioncode'] . ' - ' . $MyRow['positiontitle'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($MyRow['description'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . $MyRow['budgetedpositions'] . '</td>
				<td>' . locale_number_format($MyRow['budgetedsalary'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td>' . ($MyRow['actualpositions'] !== null ? $MyRow['actualpositions'] : '') . '</td>
				<td>' . ($MyRow['actualsalary'] !== null ? locale_number_format($MyRow['actualsalary'], $_SESSION['CompanyRecord']['decimalplaces']) : '') . '</td>
				<td class="centre">
					<a href="' . $_SERVER['PHP_SELF'] . '?BudgetID=' . urlencode($MyRow['budgetid']) . '">' . __('Edit') . '</a> |
					<a href="' . $_SERVER['PHP_SELF'] . '?BudgetID=' . urlencode($MyRow['budgetid']) . '&Delete=1" onclick="return confirm(\'' . __('Delete this budget?') . '\');">' . __('Delete') . '</a>
				</td>
			</tr>';
	}

	echo '</tbody>
		</table>';
} else {
	echo '<div class="centre">' . __('No position budgets defined') . '</div>';
}

include(__DIR__ . '/includes/footer.php');

?>
