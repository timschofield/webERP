<?php

/* HR Employee Compensation Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Employee Compensation');
$ViewTopic = 'HumanResources';
$BookMark = 'HREmployeeCompensation';

include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/money.png" title="' . __('Employee Compensation') . '" /> ' .
		__('Employee Compensation Records') . '
	</p>';

// Search/Filter form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
		<legend class="search">' . __('Search Employees') . '</legend>
		<field>
			<label for="SearchName">' . __('Employee Name') . ':</label>
			<input type="text" name="SearchName" value="' . (isset($_POST['SearchName']) ? htmlspecialchars($_POST['SearchName'], ENT_QUOTES, 'UTF-8') : '') . '" />
		</field>
		<field>
			<label for="SearchDepartment">' . __('Department') . ':</label>
			<select name="SearchDepartment">
				<option value="">' . __('All Departments') . '</option>';

$SQL = "SELECT departmentid, description FROM departments WHERE active = 1 ORDER BY description";
$Result = DB_query($SQL);
while ($Row = DB_fetch_array($Result)) {
	echo '<option value="' . $Row['departmentid'] . '"' .
		(isset($_POST['SearchDepartment']) && $_POST['SearchDepartment'] == $Row['departmentid'] ? ' selected="selected"' : '') .
		'>' . htmlspecialchars($Row['description'], ENT_QUOTES, 'UTF-8') . '</option>';
}

echo '</select>
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="Search" value="' . __('Search') . '" />
		</div>
	</form>';

// Add/Edit compensation form
if (isset($_GET['edit'])) {
	$EmployeeID = (int)$_GET['edit'];

	if (isset($_POST['SubmitCompensation'])) {
		$InputError = 0;

		if (!is_date($_POST['EffectiveDate'])) {
			$InputError = 1;
			prnMsg(__('The effective date must be a valid date'), 'error');
		}
		if ((int)$_POST['GradeID'] == 0) {
			$InputError = 1;
			prnMsg(__('Please select a pay grade'), 'error');
		}
		if (filter_var($_POST['CurrentSalary'], FILTER_VALIDATE_FLOAT) === false || $_POST['CurrentSalary'] <= 0) {
			$InputError = 1;
			prnMsg(__('Please enter a valid salary amount'), 'error');
		}

		if ($InputError != 1) {
			// Get previous salary for calculation
			$SQL = "SELECT basesalary
					FROM hremployeecompensation
					WHERE employeeid = " . $EmployeeID . "
					ORDER BY effectivedate DESC LIMIT 1";
			$Result = DB_query($SQL);
			$PreviousSalary = 0;
			if (DB_num_rows($Result) > 0) {
				$PrevRow = DB_fetch_array($Result);
				$PreviousSalary = $PrevRow['basesalary'];
			}

			$CurrentSalary = filter_var($_POST['CurrentSalary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			$IncreaseAmount = $CurrentSalary - $PreviousSalary;
			$IncreasePercentage = $PreviousSalary > 0 ? (($IncreaseAmount / $PreviousSalary) * 100) : 0;
			$ChangeReason = DB_escape_string($_POST['ChangeReason']);

			$SQL = "INSERT INTO hremployeecompensation (
						employeeid, paygradeid, paystepid,
						basesalary,
						effectivedate, increaseamount, increasepercentage, notes,
						createdby, createddate
					) VALUES (
						" . $EmployeeID . ",
						" . (int)$_POST['GradeID'] . ",
						" . ((int)$_POST['StepID'] > 0 ? (int)$_POST['StepID'] : 'NULL') . ",
						" . $CurrentSalary . ",
						'" . FormatDateForSQL($_POST['EffectiveDate']) . "',
						" . $IncreaseAmount . ",
						" . $IncreasePercentage . ",
						'" . $ChangeReason . "',
						'" . $_SESSION['UserID'] . "',
						NOW()
					)";

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Compensation record has been created successfully'), 'success');
				echo '<meta http-equiv="refresh" content="2; url=' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?view=' . $EmployeeID . '" />';
			}
		}
	}

	$SQL = "SELECT CONCAT(firstname, ' ', lastname) as fullname, employeenumber
			FROM hremployees WHERE employeeid = " . $EmployeeID;
	$Result = DB_query($SQL);
	$EmpRow = DB_fetch_array($Result);

	echo '<br /><form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?edit=' . $EmployeeID . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<fieldset>
			<legend>' . __('Add Compensation Record for') . ': ' . htmlspecialchars($EmpRow['fullname'], ENT_QUOTES, 'UTF-8') . '</legend>

			<field>
				<label for="EffectiveDate">' . __('Effective Date') . ':</label>
				<input type="date" name="EffectiveDate" class="date" value="' . date('Y-m-d') . '" required="required" />
			</field>

			<field>
				<label for="GradeID">' . __('Pay Grade') . ':</label>
				<select name="GradeID" id="GradeID" required="required" onchange="loadSteps()">
					<option value="0">' . __('Select Pay Grade') . '</option>';

	$SQL = "SELECT paygradeid, paygradecode, paygradename FROM hrpaygrades WHERE active = 1 ORDER BY paygradecode";
	$Result = DB_query($SQL);
	while ($Row = DB_fetch_array($Result)) {
		echo '<option value="' . $Row['paygradeid'] . '">' . htmlspecialchars($Row['paygradecode'], ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars($Row['paygradename'], ENT_QUOTES, 'UTF-8') . '</option>';
	}

	echo '</select>
			</field>

			<field>
				<label for="StepID">' . __('Pay Step') . ':</label>
				<select name="StepID" id="StepID">
					<option value="0">' . __('Select Step') . '</option>
				</select>
			</field>

			<field>
				<label for="CurrentSalary">' . __('Current Salary') . ':</label>
				<input type="number" name="CurrentSalary" step="0.01" required="required" />
			</field>

			<field>
				<label for="ChangeReason">' . __('Change Reason') . ':</label>
				<textarea name="ChangeReason" rows="3" cols="50" required="required"></textarea>
			</field>

		</fieldset>
		<div class="centre">
			<input type="submit" name="SubmitCompensation" value="' . __('Save') . '" />
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Cancel') . '</a>
		</div>
		</form>';

	// JavaScript to load steps based on selected grade
	echo '<script type="text/javascript">
		function loadSteps() {
			var gradeId = document.getElementById("GradeID").value;
			var stepSelect = document.getElementById("StepID");

			// Clear existing options
			stepSelect.innerHTML = "<option value=\'0\'>' . __('Select Step') . '</option>";

			if (gradeId > 0) {
				// AJAX call to get steps - simplified version
				// In production, implement proper AJAX call
			}
		}
		</script>';
}

// View compensation history
if (isset($_GET['view'])) {
	$EmployeeID = (int)$_GET['view'];

	$SQL = "SELECT CONCAT(firstname, ' ', lastname) as fullname, employeenumber
			FROM hremployees WHERE employeeid = " . $EmployeeID;
	$Result = DB_query($SQL);
	$EmpRow = DB_fetch_array($Result);

	echo '<br /><h3>' . __('Compensation History for') . ': ' . htmlspecialchars($EmpRow['fullname'], ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($EmpRow['employeenumber'], ENT_QUOTES, 'UTF-8') . ')</h3>';

	echo '<table class="selection">
			<tr>
				<th>' . __('Effective Date') . '</th>
				<th>' . __('Pay Grade') . '</th>
				<th>' . __('Pay Step') . '</th>
				<th>' . __('Salary') . '</th>
				<th>' . __('Increase %') . '</th>
				<th>' . __('Increase Amount') . '</th>
				<th>' . __('Reason') . '</th>
				<th>' . __('Created By') . '</th>
			</tr>';

	$SQL = "SELECT c.*, g.paygradecode, s.stepnumber, c.createdby
			FROM hremployeecompensation c
			LEFT JOIN hrpaygrades g ON c.paygradeid = g.paygradeid
			LEFT JOIN hrpaysteps s ON c.paystepid = s.paystepid
			WHERE c.employeeid = " . $EmployeeID . "
			ORDER BY c.effectivedate DESC";

	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		echo '<tr><td colspan="8">' . __('No compensation history found') . '</td></tr>';
	} else {
		while ($Row = DB_fetch_array($Result)) {
			$SalaryDisplay = locale_number_format($Row['basesalary'], $_SESSION['CompanyRecord']['decimalplaces']);
			if (!empty($Row['currencycode'])) {
				$SalaryDisplay .= ' ' . htmlspecialchars($Row['currencycode'], ENT_QUOTES, 'UTF-8');
			}
			echo '<tr class="striped_row">
					<td>' . ConvertSQLDate($Row['effectivedate']) . '</td>
					<td>' . htmlspecialchars($Row['paygradecode'], ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . ($Row['stepnumber'] ? htmlspecialchars($Row['stepnumber'], ENT_QUOTES, 'UTF-8') : '-') . '</td>
					<td class="number">' . $SalaryDisplay . '</td>
					<td class="number">' . ($Row['increasepercentage'] ? number_format($Row['increasepercentage'], 2) . '%' : '-') . '</td>
					<td class="number">' . ($Row['increaseamount'] ? locale_number_format($Row['increaseamount'], $_SESSION['CompanyRecord']['decimalplaces']) : '-') . '</td>
					<td>' . htmlspecialchars($Row['notes'], ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . htmlspecialchars($Row['createdby'], ENT_QUOTES, 'UTF-8') . '</td>
				</tr>';
		}
	}

	echo '</table>';
	echo '<div class="centre"><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Back to List') . '</a></div>';
}

// Build search query
$WhereClause = "e.employmentstatus = 'Active'";

if (isset($_POST['Search'])) {
	if (!empty($_POST['SearchName'])) {
		$SearchName = DB_escape_string($_POST['SearchName']);
		$WhereClause .= " AND (e.firstname LIKE '%" . $SearchName . "%' OR e.lastname LIKE '%" . $SearchName . "%')";
	}
	if (!empty($_POST['SearchDepartment'])) {
		$WhereClause .= " AND e.departmentid = " . (int)$_POST['SearchDepartment'];
	}
}

// Display employee compensation list
echo '<table class="selection">
		<tr>
			<th>' . __('Employee #') . '</th>
			<th>' . __('Name') . '</th>
			<th>' . __('Department') . '</th>
			<th>' . __('Position') . '</th>
			<th>' . __('Pay Grade') . '</th>
			<th>' . __('Pay Step') . '</th>
			<th>' . __('Current Salary') . '</th>
			<th>' . __('Effective Date') . '</th>
			<th>' . __('Actions') . '</th>
		</tr>';

$SQL = "SELECT e.employeeid, e.employeenumber, e.firstname, e.lastname,
			d.description, p.positiontitle,
			c.compensationid, c.basesalary, c.currencycode, c.effectivedate,
			g.paygradecode, g.paygradename, s.stepnumber
		FROM hremployees e
		LEFT JOIN departments d ON e.departmentid = d.departmentid
		LEFT JOIN hrpositions p ON e.positionid = p.positionid
		LEFT JOIN hremployeecompensation c ON e.employeeid = c.employeeid
			AND c.compensationid = (
				SELECT compensationid
				FROM hremployeecompensation
				WHERE employeeid = e.employeeid
				ORDER BY effectivedate DESC, compensationid DESC
				LIMIT 1
			)
		LEFT JOIN hrpaygrades g ON c.paygradeid = g.paygradeid
		LEFT JOIN hrpaysteps s ON c.paystepid = s.paystepid
		WHERE " . $WhereClause . "
		ORDER BY e.lastname, e.firstname";

$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	echo '<tr><td colspan="9">' . __('No employees found') . '</td></tr>';
} else {
	while ($Row = DB_fetch_array($Result)) {
		$SalaryDisplay = '-';
		if ($Row['basesalary']) {
			$SalaryDisplay = locale_number_format($Row['basesalary'], $_SESSION['CompanyRecord']['decimalplaces']);
			if (!empty($Row['currencycode'])) {
				$SalaryDisplay .= ' ' . htmlspecialchars($Row['currencycode'], ENT_QUOTES, 'UTF-8');
			}
		}
		echo '<tr class="striped_row">
				<td>' . htmlspecialchars($Row['employeenumber'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($Row['firstname'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($Row['lastname'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($Row['description'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($Row['positiontitle'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($Row['paygradecode'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . ($Row['stepnumber'] ? htmlspecialchars($Row['stepnumber'], ENT_QUOTES, 'UTF-8') : '-') . '</td>
				<td class="number">' . $SalaryDisplay . '</td>
				<td>' . ($Row['effectivedate'] ? ConvertSQLDate($Row['effectivedate']) : '-') . '</td>
				<td>
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?view=' . $Row['employeeid'] . '">' . __('View History') . '</a> |
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?edit=' . $Row['employeeid'] . '">' . __('Add/Edit') . '</a>
				</td>
			</tr>';
	}
}

echo '</table>';

include(__DIR__ . '/includes/footer.php');

?>
