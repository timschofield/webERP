<?php

/* HR Salary Increase Processing */

require(__DIR__ . '/includes/session.php');

$Title = __('Salary Increase');
$ViewTopic = 'HumanResources';
$BookMark = 'HRSalaryIncrease';

include(__DIR__ . '/includes/header.php');

// Get system options
$SQL = "SELECT optionname, optionvalue FROM hrsystemoptions WHERE optionname IN ('MinSalaryIncreasePercent', 'MaxSalaryIncreasePercent')";
$OptionsResult = DB_query($SQL);
$SystemOptions = array();
while ($OptionRow = DB_fetch_array($OptionsResult)) {
	$SystemOptions[$OptionRow['optionname']] = $OptionRow['optionvalue'];
}
$MinSalaryIncreasePercent = isset($SystemOptions['MinSalaryIncreasePercent']) ? $SystemOptions['MinSalaryIncreasePercent'] : 0;
$MaxSalaryIncreasePercent = isset($SystemOptions['MaxSalaryIncreasePercent']) ? $SystemOptions['MaxSalaryIncreasePercent'] : 15;

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/money_add.png" title="' . __('Salary Increase') . '" /> ' .
		__('Salary Increase Processing') . '
	</p>';

// Process batch salary increases
if (isset($_POST['ProcessIncreases'])) {
	$InputError = 0;

	if (!is_date($_POST['EffectiveDate'])) {
		$InputError = 1;
		prnMsg(__('The effective date must be a valid date'), 'error');
	}

	if ($InputError != 1 && isset($_POST['SelectedEmployees']) && is_array($_POST['SelectedEmployees'])) {
		$EffectiveDate = FormatDateForSQL($_POST['EffectiveDate']);
		$ChangeReason = $_POST['ChangeReason'];
		$ProcessedCount = 0;

		DB_Txn_Begin();

		foreach ($_POST['SelectedEmployees'] as $EmployeeID) {
			$EmployeeID = (int)$EmployeeID;

			if (isset($_POST['NewSalary_' . $EmployeeID]) && $_POST['NewSalary_' . $EmployeeID] > 0) {
				// Get current salary
				$SQL = "SELECT currentsalary FROM hremployeecompensation
						WHERE employeeid = " . $EmployeeID . "
						ORDER BY effectivedate DESC LIMIT 1";
				$Result = DB_query($SQL);

				$PreviousSalary = 0;
				if (DB_num_rows($Result) > 0) {
					$Row = DB_fetch_array($Result);
					$PreviousSalary = $Row['currentsalary'];
				}

				$NewSalary = filter_var($_POST['NewSalary_' . $EmployeeID], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
				$IncreaseAmount = $NewSalary - $PreviousSalary;
				$IncreasePercentage = $PreviousSalary > 0 ? (($IncreaseAmount / $PreviousSalary) * 100) : 0;

				// Validate against system-defined min/max increase percentages
				if ($IncreasePercentage < $MinSalaryIncreasePercent) {
					prnMsg(__('Salary increase for employee') . ' ' . $EmployeeID . ' ' . __('is below minimum allowed percentage of') . ' ' . $MinSalaryIncreasePercent . '%', 'warn');
				}
				if ($IncreasePercentage > $MaxSalaryIncreasePercent) {
					prnMsg(__('Salary increase for employee') . ' ' . $EmployeeID . ' ' . __('exceeds maximum allowed percentage of') . ' ' . $MaxSalaryIncreasePercent . '%', 'warn');
				}

				// Get employee's current grade and step
				$SQL = "SELECT gradeid, stepid FROM hremployeecompensation
						WHERE employeeid = " . $EmployeeID . "
						ORDER BY effectivedate DESC LIMIT 1";
				$Result = DB_query($SQL);
				$GradeStepRow = DB_fetch_array($Result);
				$GradeID = $GradeStepRow['gradeid'];
				$StepID = $GradeStepRow['stepid'];

				// Insert new compensation record
				$SQL = "INSERT INTO hremployeecompensation (
							employeeid, gradeid, stepid,
							currentsalary, previoussalary,
							increaseamount, increasepercentage,
							effectivedate, changereason,
							createdby, createddate
						) VALUES (
							" . $EmployeeID . ",
							" . $GradeID . ",
							" . ($StepID ? $StepID : 'NULL') . ",
							" . $NewSalary . ",
							" . $PreviousSalary . ",
							" . $IncreaseAmount . ",
							" . $IncreasePercentage . ",
							'" . $EffectiveDate . "',
							'" . $ChangeReason . "',
							'" . $_SESSION['UserID'] . "',
							NOW()
						)";

				$Result = DB_query($SQL);
				if ($Result) {
					$ProcessedCount++;
				}
			}
		}

		DB_Txn_Commit();

		prnMsg($ProcessedCount . ' ' . __('salary increase(s) have been processed successfully'), 'success');
	}
}

// Display salary increase form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />

		<fieldset>
		<legend>' . __('Increase Parameters') . '</legend>
		<field>
			<label for="EffectiveDate">' . __('Effective Date') . ':</label>
			<input type="date" name="EffectiveDate" class="date" value="' . Date($_SESSION['DefaultDateFormat']) . '" required="required" />
		</field>

		<field>
			<label for="ChangeReason">' . __('Change Reason') . ':</label>
			<input type="text" name="ChangeReason" size="50" value="' . __('Annual Salary Increase') . '" required="required" />
		</field>

		<field>
			<label for="FilterDepartment">' . __('Filter by Department') . ':</label>
			<select name="FilterDepartment" onchange="this.form.submit()">
				<option value="">' . __('All Departments') . '</option>';

$SQL = "SELECT departmentid, description FROM departments WHERE active = 1 ORDER BY description";
$Result = DB_query($SQL);
while ($Row = DB_fetch_array($Result)) {
	echo '<option value="' . $Row['departmentid'] . '"' .
		(isset($_POST['FilterDepartment']) && $_POST['FilterDepartment'] == $Row['departmentid'] ? ' selected="selected"' : '') .
		'>' . $Row['description'] . '</option>';
}

echo '</select>
		</field>

		<field>
			<label for="DefaultIncreasePercent">' . __('Default Increase %') . ':</label>
			<input type="number" name="DefaultIncreasePercent" id="DefaultIncreasePercent" value="3.0" step="0.1" />
			<input type="button" value="' . __('Apply to All') . '" onclick="applyDefaultIncrease()" />
			<br /><em>' . __('System limits') . ': ' . $MinSalaryIncreasePercent . '% - ' . $MaxSalaryIncreasePercent . '%</em>
		</field>
		</fieldset>';

// Build employee list query
$WhereClause = "e.employmentstatus = 'Active'";
if (isset($_POST['FilterDepartment']) && $_POST['FilterDepartment'] != '') {
	$WhereClause .= " AND e.departmentid = " . (int)$_POST['FilterDepartment'];
}

$SQL = "SELECT e.employeeid, e.employeenumber, e.firstname, e.lastname,
			d.description, p.positiontitle,
			COALESCE(c.basesalary, e.currentsalary, 0) as currentsalary
		FROM hremployees e
		LEFT JOIN departments d ON e.departmentid = d.departmentid
		LEFT JOIN hrpositions p ON e.positionid = p.positionid
		LEFT JOIN hremployeecompensation c ON e.employeeid = c.employeeid
			AND c.compensationid = (
				SELECT MAX(compensationid)
				FROM hremployeecompensation
				WHERE employeeid = e.employeeid
			)
		WHERE " . $WhereClause . "
		ORDER BY e.lastname, e.firstname";

$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	echo '<tr><td colspan="8">' . __('No employees found') . '</td></tr>';
} else {

	echo '<table class="selection">
			<tr>
				<th><input type="checkbox" id="SelectAll" onclick="toggleAll(this)" /></th>
				<th>' . __('Employee #') . '</th>
				<th>' . __('Name') . '</th>
				<th>' . __('Department') . '</th>
				<th>' . __('Position') . '</th>
				<th>' . __('Current Salary') . '</th>
				<th>' . __('Increase %') . '</th>
				<th>' . __('New Salary') . '</th>
			</tr>';

	$EmployeeCount = 0;
	while ($Row = DB_fetch_array($Result)) {
		$EmployeeCount++;
		$CurrentSalary = $Row['currentsalary'] ? $Row['currentsalary'] : 0;

		echo '<tr class="striped_row">
				<td><input type="checkbox" name="SelectedEmployees[]" value="' . $Row['employeeid'] . '" class="employee-checkbox" /></td>
				<td>' . $Row['employeenumber'] . '</td>
				<td>' . $Row['firstname'] . ' ' . $Row['lastname'] . '</td>
				<td>' . $Row['description'] . '</td>
				<td>' . $Row['positiontitle'] . '</td>
				<td class="number">
					<span id="current_' . $Row['employeeid'] . '">' . locale_number_format($CurrentSalary, $_SESSION['CompanyRecord']['decimalplaces']) . '</span>
					<input type="hidden" id="currentval_' . $Row['employeeid'] . '" value="' . $CurrentSalary . '" />
				</td>
				<td><input type="number" name="IncreasePercent_' . $Row['employeeid'] . '" id="increase_' . $Row['employeeid'] . '"
						value="0" step="0.1" size="5" style="width: 60px;"
						onchange="calculateNewSalary(' . $Row['employeeid'] . ')" /></td>
				<td><input type="number" name="NewSalary_' . $Row['employeeid'] . '" id="newsalary_' . $Row['employeeid'] . '"
						value="' . $CurrentSalary . '" step="0.01" style="width: 100px;" /></td>
			</tr>';
	}
}

echo '</table>';

if (DB_num_rows($Result) > 0) {
	echo '<div class="centre">
			<br /><input type="submit" name="ProcessIncreases" value="' . __('Process Selected Increases') . '"
				onclick="return confirm(\'' . __('Are you sure you want to process these salary increases?') . '\');" />
		</div>';
}

echo '</form>';

// JavaScript for salary calculations
echo '<script type="text/javascript">
	function toggleAll(source) {
		var checkboxes = document.getElementsByClassName("employee-checkbox");
		for (var i = 0; i < checkboxes.length; i++) {
			checkboxes[i].checked = source.checked;
		}
	}

	function calculateNewSalary(employeeId) {
		var currentSalary = parseFloat(document.getElementById("currentval_" + employeeId).value);
		var increasePercent = parseFloat(document.getElementById("increase_" + employeeId).value);

		if (isNaN(increasePercent)) increasePercent = 0;
		if (isNaN(currentSalary)) currentSalary = 0;

		var newSalary = currentSalary * (1 + (increasePercent / 100));
		document.getElementById("newsalary_" + employeeId).value = newSalary.toFixed(2);
	}

	function applyDefaultIncrease() {
		var defaultPercent = document.getElementById("DefaultIncreasePercent").value;
		var checkboxes = document.getElementsByClassName("employee-checkbox");

		for (var i = 0; i < checkboxes.length; i++) {
			if (checkboxes[i].checked) {
				var employeeId = checkboxes[i].value;
				document.getElementById("increase_" + employeeId).value = defaultPercent;
				calculateNewSalary(employeeId);
			}
		}
	}
</script>';

include(__DIR__ . '/includes/footer.php');

?>
