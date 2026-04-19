<?php

/* HR Employees Directory and Maintenance */

require(__DIR__ . '/includes/session.php');

$Title = __('HR Employees');
$ViewTopic = 'HumanResources';
$BookMark = 'HREmployees';

include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/UIGeneralFunctions.php');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/user.png" title="' . __('Employees') . '" /> ' .
		__('HR Employees Directory') . '
	</p>';

if (isset($_GET['SelectedEmployee'])) {
	$SelectedEmployee = $_GET['SelectedEmployee'];
} elseif (isset($_POST['SelectedEmployee'])) {
	$SelectedEmployee = $_POST['SelectedEmployee'];
}

if (isset($_POST['Submit'])) {
	$InputError = 0;

	if (trim($_POST['FirstName']) == '') {
		$InputError = 1;
		prnMsg(__('The employee first name must not be empty'), 'error');
	}
	if (trim($_POST['LastName']) == '') {
		$InputError = 1;
		prnMsg(__('The employee last name must not be empty'), 'error');
	}
	if (trim($_POST['EmployeeNumber']) == '') {
		$InputError = 1;
		prnMsg(__('The employee number must not be empty'), 'error');
	}

	if (isset($SelectedEmployee) AND $InputError != 1) {
		// Update existing employee
		$SQL = "UPDATE hremployees SET
					employeenumber = '" . $_POST['EmployeeNumber'] . "',
					firstname = '" . $_POST['FirstName'] . "',
					middlename = '" . $_POST['MiddleName'] . "',
					lastname = '" . $_POST['LastName'] . "',
					hiredate = '" . FormatDateForSQL($_POST['HireDate']) . "',
					birthdate = '" . FormatDateForSQL($_POST['BirthDate']) . "',";

		if ($_POST['EmploymentStatus'] != 'Terminated') {
			$SQL .= " terminationdate = '1000-01-01',";
		} elseif ($_POST['TerminationDate'] == '1000-01-01') {
			$SQL .= " terminationdate = NOW(),";
		}
		
		$SQL .=	"	gender = '" . $_POST['Gender'] . "',
					email = '" . $_POST['Email'] . "',
					phone = '" . $_POST['Phone'] . "',
					departmentid = " . (int)$_POST['DepartmentID'] . ",
					positionid = " . (int)$_POST['PositionID'] . ",
					supervisorid = " . (int)$_POST['SupervisorID'] . ",
					employmentstatus = '" . $_POST['EmploymentStatus'] . "',
					employmenttype = '" . $_POST['EmploymentType'] . "',
					locationid = " . (int)$_POST['LocationID'] . ",
					stockid = " . ($_POST['StockID'] != '' ? "'" . $_POST['StockID'] . "'" : "NULL") . ",
					normalhours = " . (float)$_POST['NormalHours'] . ",
					currency = " . ($_POST['Currency'] != '' ? "'" . $_POST['Currency'] . "'" : "'" . $_SESSION['CompanyRecord']['currencydefault'] . "'") . ",
					userid = '" . $_POST['UserID'] . "',
					modifiedby = '" . $_SESSION['UserID'] . "',
					modifieddate = NOW()
				WHERE employeeid = '" . $SelectedEmployee . "'";

		$ErrMsg = __('Could not update the employee because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('Employee') . ' ' . $_POST['FirstName'] . ' ' . $_POST['LastName'] . ' ' . __('has been updated'), 'success');

	} elseif ($InputError != 1) {
		// Insert new employee
		$SQL = "SELECT COUNT(*) FROM hremployees WHERE employeenumber = '" . $_POST['EmployeeNumber'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);

		if ($MyRow[0] > 0) {
			$InputError = 1;
			prnMsg(__('An employee with this number already exists'), 'error');
		} else {
			$SQL = "INSERT INTO hremployees (
						employeenumber,
						firstname,
						middlename,
						lastname,
						hiredate,
						birthdate,
						gender,
						email,
						phone,
						departmentid,
						positionid,
						supervisorid,
						employmentstatus,
						employmenttype,
						locationid,
						stockid,
						normalhours,
						currency,
						userid,
						createdby
					) VALUES (
						'" . $_POST['EmployeeNumber'] . "',
						'" . $_POST['FirstName'] . "',
						'" . $_POST['MiddleName'] . "',
						'" . $_POST['LastName'] . "',
						'" . FormatDateForSQL($_POST['HireDate']) . "',
						'" . FormatDateForSQL($_POST['BirthDate']) . "',
						'" . $_POST['Gender'] . "',
						'" . $_POST['Email'] . "',
						'" . $_POST['Phone'] . "',
						" . (int)$_POST['DepartmentID'] . ",
						" . (int)$_POST['PositionID'] . ",
						" . (int)$_POST['SupervisorID'] . ",
						'" . $_POST['EmploymentStatus'] . "',
						'" . $_POST['EmploymentType'] . "',
						" . (int)$_POST['LocationID'] . ",
						" . ($_POST['StockID'] != '' ? "'" . $_POST['StockID'] . "'" : "NULL") . ",
						" . (float)$_POST['NormalHours'] . ",
						" . ($_POST['Currency'] != '' ? "'" . $_POST['Currency'] . "'" : "'" . $_SESSION['CompanyRecord']['currencydefault'] . "'") . ",
						'" . $_POST['UserID'] . "',
						'" . $_SESSION['UserID'] . "'
					)";

			$ErrMsg = __('Could not add the employee because');
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(__('Employee') . ' ' . $_POST['FirstName'] . ' ' . $_POST['LastName'] . ' ' . __('has been added'), 'success');
		}
	}

	if ($InputError != 1) {
		unset($SelectedEmployee);
		unset($_POST);
	}

} elseif (isset($_GET['delete'])) {
	$CancelDelete = 0;

	// Check if employee has compensation records
	$SQL = "SELECT COUNT(*) FROM hremployeecompensation WHERE employeeid = '" . $SelectedEmployee . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);

	if ($MyRow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(__('Cannot delete this employee because there are') . ' ' . $MyRow[0] . ' ' . __('compensation records'), 'error');
	}

	// Check if employee has performance appraisals
	$SQL = "SELECT COUNT(*) FROM hrperfappraisals WHERE employeeid = '" . $SelectedEmployee . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);

	if ($MyRow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(__('Cannot delete this employee because there are') . ' ' . $MyRow[0] . ' ' . __('performance appraisals'), 'error');
	}

	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM hremployees WHERE employeeid = '" . $SelectedEmployee . "'";
		$ErrMsg = __('Could not delete the employee because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('Employee has been deleted'), 'success');
		unset($SelectedEmployee);
	}
}

// Entry form for adding/editing employee
if (isset($SelectedEmployee)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Show All Employees') . '</a></div>';
}

echo '<br />';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedEmployee)) {
	$SQL = "SELECT employeeid,
				employeenumber,
				userid,
				firstname,
				middlename,
				lastname,
				hiredate,
				terminationdate,
				birthdate,
				gender,
				email,
				phone,
				departmentid,
				positionid,
				supervisorid,
				employmentstatus,
				employmenttype,
				locationid,
				stockid,
				normalhours,
				currentsalary,
				currency
		FROM hremployees
		WHERE employeeid = '" . $SelectedEmployee . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['EmployeeID'] = $MyRow['employeeid'];
	$_POST['EmployeeNumber'] = $MyRow['employeenumber'];
	$_POST['UserID'] = $MyRow['userid'];
	$_POST['FirstName'] = $MyRow['firstname'];
	$_POST['MiddleName'] = $MyRow['middlename'];
	$_POST['LastName'] = $MyRow['lastname'];
	$_POST['HireDate'] = $MyRow['hiredate'];
	$_POST['BirthDate'] = $MyRow['birthdate'];
	$_POST['TerminationDate'] = $MyRow['terminationdate'];
	$_POST['Gender'] = $MyRow['gender'];
	$_POST['Email'] = $MyRow['email'];
	$_POST['Phone'] = $MyRow['phone'];
	$_POST['DepartmentID'] = $MyRow['departmentid'];
	$_POST['PositionID'] = $MyRow['positionid'];
	$_POST['SupervisorID'] = $MyRow['supervisorid'];
	$_POST['EmploymentStatus'] = $MyRow['employmentstatus'];
	$_POST['EmploymentType'] = $MyRow['employmenttype'];
	$_POST['LocationID'] = $MyRow['locationid'];
	$_POST['StockID'] = $MyRow['stockid'];
	$_POST['NormalHours'] = $MyRow['normalhours'];
	$_POST['CurrentSalary'] = $MyRow['currentsalary'];
	$_POST['Currency'] = $MyRow['currency'];

	echo '<input type="hidden" name="SelectedEmployee" value="' . $SelectedEmployee . '" />';
	echo '<fieldset>';
	echo '<legend>' . __('Edit Employee') . '</legend>';
} else {
	echo '<fieldset>';
	echo '<legend>' . __('Add New Employee') . '</legend>';
}

if (!isset($_POST['BirthDate']) 
	or $_POST['BirthDate'] == '1000-01-01'
	or $_POST['BirthDate'] == NULL) {
	$_POST['BirthDate'] = '1000-01-01';
}

if (!isset($_POST['HireDate']) 
	or $_POST['HireDate'] == '1000-01-01'
	or $_POST['HireDate'] == NULL) {
	$_POST['HireDate'] = '1000-01-01';
}

if (!isset($_POST['TerminationDate']) 
	or $_POST['TerminationDate'] == '1000-01-01'
	or $_POST['TerminationDate'] == NULL) {
	$_POST['TerminationDate'] = '1000-01-01';
}

echo '<field>
		<label>' . __('Employee Number') . ':</label>
		<input type="text" name="EmployeeNumber" required="required" size="20" maxlength="20" value="' . (isset($_POST['EmployeeNumber']) ? $_POST['EmployeeNumber'] : '') . '" />
	</field>';

echo '<field>
		<label>' . __('First Name') . ':</label>
		<input type="text" name="FirstName" required="required" size="30" maxlength="50" value="' . (isset($_POST['FirstName']) ? $_POST['FirstName'] : '') . '" />
	</field>';

echo '<field>
		<label>' . __('Middle Name') . ':</label>
		<input type="text" name="MiddleName" size="30" maxlength="50" value="' . (isset($_POST['MiddleName']) ? $_POST['MiddleName'] : '') . '" />
	</field>';

echo '<field>
		<label>' . __('Last Name') . ':</label>
		<input type="text" name="LastName" required="required" size="30" maxlength="50" value="' . (isset($_POST['LastName']) ? $_POST['LastName'] : '') . '" />
	</field>';

echo '<field>
		<label>' . __('Email') . ':</label>
		<input type="email" name="Email" size="40" maxlength="100" value="' . (isset($_POST['Email']) ? $_POST['Email'] : '') . '" />
	</field>';

echo '<field>
		<label>' . __('Phone') . ':</label>
		<input type="tel" name="Phone" size="20" maxlength="20" value="' . (isset($_POST['Phone']) ? $_POST['Phone'] : '') . '" />
	</field>';

echo FieldToSelectOneDate('BirthDate', $_POST['BirthDate'], __('Birth Date'), '', '', 2, true, false);

echo '<field>
		<label>' . __('Gender') . ':</label>
		<select name="Gender" required>
			<option value="">-</option>
			<option value="M"' . (isset($_POST['Gender']) && $_POST['Gender'] == 'M' ? ' selected="selected"' : '') . '>' . __('Male') . '</option>
			<option value="F"' . (isset($_POST['Gender']) && $_POST['Gender'] == 'F' ? ' selected="selected"' : '') . '>' . __('Female') . '</option>
			<option value="Other"' . (isset($_POST['Gender']) && $_POST['Gender'] == 'Other' ? ' selected="selected"' : '') . '>' . __('Other') . '</option>
		</select>
	</field>';

echo FieldToSelectOneDate('HireDate', $_POST['HireDate'], __('Hire Date'), '', '', 2, true, false);

// Department dropdown
echo '<field>
		<label>' . __('Department') . ':</label>
		<select name="DepartmentID">';
echo '<option value="0">' . __('None') . '</option>';
$SQL = "SELECT departmentid, description FROM departments WHERE active = 1 ORDER BY description";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['DepartmentID']) AND $_POST['DepartmentID'] == $MyRow['departmentid']) {
		echo '<option selected="selected" value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
	}
}
echo '</select>
	</field>';

// Position dropdown
echo '<field>
		<label>' . __('Position') . ':</label>
		<select name="PositionID">';
echo '<option value="0">' . __('None') . '</option>';
$SQL = "SELECT positionid, positiontitle FROM hrpositions WHERE active = 1 ORDER BY positiontitle";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['PositionID'])AND $_POST['PositionID'] == $MyRow['positionid']) {
		echo '<option selected="selected" value="' . $MyRow['positionid'] . '">' . $MyRow['positiontitle'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['positionid'] . '">' . $MyRow['positiontitle'] . '</option>';
	}
}
echo '</select>
	</field>';

// Supervisor dropdown
echo '<field>
		<label>' . __('Supervisor') . ':</label>
		<select name="SupervisorID">';
echo '<option value="0">' . __('None') . '</option>';
$SQL = "SELECT employeeid, CONCAT(firstname, ' ', lastname) as fullname
		FROM hremployees
		WHERE employmentstatus = 'Active'";
if (isset($SelectedEmployee)) {
	$SQL .= " AND employeeid != '" . $SelectedEmployee . "'";
}
$SQL .= " ORDER BY lastname, firstname";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['SupervisorID']) AND $_POST['SupervisorID'] == $MyRow['employeeid']) {
		echo '<option selected="selected" value="' . $MyRow['employeeid'] . '">' . $MyRow['fullname'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['employeeid'] . '">' . $MyRow['fullname'] . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label>' . __('Employment Status') . ':</label>
		<select name="EmploymentStatus">
			<option value="Active"' . (isset($_POST['EmploymentStatus']) && $_POST['EmploymentStatus'] == 'Active' ? ' selected="selected"' : '') . '>' . __('Active') . '</option>
			<option value="Terminated"' . (isset($_POST['EmploymentStatus']) && $_POST['EmploymentStatus'] == 'Terminated' ? ' selected="selected"' : '') . '>' . __('Terminated') . '</option>
			<option value="On Leave"' . (isset($_POST['EmploymentStatus']) && $_POST['EmploymentStatus'] == 'On Leave' ? ' selected="selected"' : '') . '>' . __('On Leave') . '</option>
			<option value="Suspended"' . (isset($_POST['EmploymentStatus']) && $_POST['EmploymentStatus'] == 'Suspended' ? ' selected="selected"' : '') . '>' . __('Suspended') . '</option>
		</select>
	</field>';

echo '<field>
		<label>' . __('Employment Type') . ':</label>
		<select name="EmploymentType">
			<option value="Full-Time"' . (isset($_POST['EmploymentType']) && $_POST['EmploymentType'] == 'Full-Time' ? ' selected="selected"' : '') . '>' . __('Full-Time') . '</option>
			<option value="Part-Time"' . (isset($_POST['EmploymentType']) && $_POST['EmploymentType'] == 'Part-Time' ? ' selected="selected"' : '') . '>' . __('Part-Time') . '</option>
			<option value="Contract"' . (isset($_POST['EmploymentType']) && $_POST['EmploymentType'] == 'Contract' ? ' selected="selected"' : '') . '>' . __('Contract') . '</option>
			<option value="Temporary"' . (isset($_POST['EmploymentType']) && $_POST['EmploymentType'] == 'Temporary' ? ' selected="selected"' : '') . '>' . __('Temporary') . '</option>
		</select>
	</field>';

// Location dropdown
echo '<field>
		<label>' . __('Location') . ':</label>
		<select name="LocationID">';
echo '<option value="0">' . __('None') . '</option>';
$SQL = "SELECT loccode, locationname FROM locations ORDER BY locationname";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['LocationID']) AND $_POST['LocationID'] == $MyRow['loccode']) {
		echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
}
echo '</select>
	</field>';

// Stock ID (Labour Item) dropdown
echo '<field>
		<label>' . __('Labour Item') . ':</label>
		<select name="StockID">';
echo '<option value="">' . __('None') . '</option>';
$SQL = "SELECT stockid, description FROM stockmaster WHERE mbflag='D' ORDER BY stockid";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['StockID']) AND $_POST['StockID'] == $MyRow['stockid']) {
		echo '<option selected="selected" value="' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . ' - ' . $MyRow['description'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . ' - ' . $MyRow['description'] . '</option>';
	}
}
echo '</select>
	</field>';

// Normal Hours
echo '<field>
		<label>' . __('Normal Hours/Week') . ':</label>
		<input type="number" name="NormalHours" step="0.5" min="0" max="168" value="' . (isset($_POST['NormalHours']) ? $_POST['NormalHours'] : '40') . '" />
	</field>';

// Currency dropdown
echo '<field>
		<label>' . __('Salary Currency') . ':</label>
		<select name="Currency">';
echo '<option value="">' . __('None') . '</option>';
$SQL = "SELECT currabrev, currency FROM currencies ORDER BY currency";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['Currency']) AND $_POST['Currency'] == $MyRow['currabrev']) {
		echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . ' (' . $MyRow['currabrev'] . ')</option>';
	} else {
		echo '<option value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . ' (' . $MyRow['currabrev'] . ')</option>';
	}
}
echo '</select>
	</field>';

// User ID dropdown for linking to www_users
echo '<field>
		<label>' . __('webERP System User') . ':</label>
		<select name="UserID">';
echo '<option value="">' . __('None') . '</option>';
$SQL = "SELECT userid, realname FROM www_users ORDER BY realname";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['UserID']) AND $_POST['UserID'] == $MyRow['userid']) {
		echo '<option selected="selected" value="' . $MyRow['userid'] . '">' . $MyRow['realname'] . ' (' . $MyRow['userid'] . ')</option>';
	} else {
		echo '<option value="' . $MyRow['userid'] . '">' . $MyRow['realname'] . ' (' . $MyRow['userid'] . ')</option>';
	}
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Submit') . '" />
	</div>
	</form>';

// Search form
if (!isset($SelectedEmployee)) {
	echo '<br />';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend class="search">' . __('Employee Search') . '</legend>';
	echo '<field>
			<tr>
			<td>' . __('Name or Number') . ':</td>
			<td><input type="text" name="Keywords" size="20" value="' . (isset($_POST['Keywords']) ? $_POST['Keywords'] : '') . '" /></td>
			<td>' . __('Department') . ':</td>
			<td><select name="SearchDepartment">';
	echo '<option value="">' . __('All') . '</option>';
	$SQL = "SELECT departmentid, description FROM departments WHERE active = 1 ORDER BY description";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SearchDepartment']) AND $_POST['SearchDepartment'] == $MyRow['departmentid']) {
			echo '<option selected="selected" value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
		}
	}
	echo '</select></td>';
	echo '<td>' . __('Status') . ':</td>
			<td><select name="SearchStatus">
				<option value="">' . __('All') . '</option>
				<option value="Active"' . (isset($_POST['SearchStatus']) && $_POST['SearchStatus'] == 'Active' ? ' selected="selected"' : '') . '>' . __('Active') . '</option>
				<option value="Terminated"' . (isset($_POST['SearchStatus']) && $_POST['SearchStatus'] == 'Terminated' ? ' selected="selected"' : '') . '>' . __('Terminated') . '</option>
				<option value="On Leave"' . (isset($_POST['SearchStatus']) && $_POST['SearchStatus'] == 'On Leave' ? ' selected="selected"' : '') . '>' . __('On Leave') . '</option>
				<option value="Suspended"' . (isset($_POST['SearchStatus']) && $_POST['SearchStatus'] == 'Suspended' ? ' selected="selected"' : '') . '>' . __('Suspended') . '</option>
			</select></td>
		</tr>';
	echo '<tr>
			<td colspan="6" class="centre"><input type="submit" name="SearchButton" value="' . __('Search') . '" /></td>
		</field>';
	echo '</fieldset>';
	echo '</form>';

	// Display list of employees
	$SQL = "SELECT e.employeeid,
				e.employeenumber,
				e.firstname,
				e.lastname,
				e.email,
				e.phone,
				d.description,
				p.positiontitle,
				e.employmentstatus,
				e.hiredate
			FROM hremployees e
			LEFT JOIN departments d ON e.departmentid = d.departmentid
			LEFT JOIN hrpositions p ON e.positionid = p.positionid
			WHERE 1=1";

	if (isset($_POST['Keywords']) AND $_POST['Keywords'] != '') {
		$Keywords = $_POST['Keywords'];
		$SQL .= " AND (e.firstname LIKE '%" . $Keywords . "%'
				OR e.lastname LIKE '%" . $Keywords . "%'
				OR e.employeenumber LIKE '%" . $Keywords . "%')";
	}
	if (isset($_POST['SearchDepartment']) AND $_POST['SearchDepartment'] != '') {
		$SQL .= " AND e.departmentid = '" . (int)$_POST['SearchDepartment'] . "'";
	}
	if (isset($_POST['SearchStatus']) AND $_POST['SearchStatus'] != '') {
		$SQL .= " AND e.employmentstatus = '" . $_POST['SearchStatus'] . "'";
	}

	$SQL .= " ORDER BY e.lastname, e.firstname LIMIT 100";

	$Result = DB_query($SQL);

	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . __('Employee #') . '</th>
					<th class="SortedColumn">' . __('Name') . '</th>
					<th class="SortedColumn">' . __('Email') . '</th>
					<th class="SortedColumn">' . __('Phone') . '</th>
					<th class="SortedColumn">' . __('Department') . '</th>
					<th class="SortedColumn">' . __('Position') . '</th>
					<th class="SortedColumn">' . __('Status') . '</th>
					<th class="SortedColumn">' . __('Hire Date') . '</th>
					<th colspan="2">&nbsp;</th>
				</tr>
			<thead>
			<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>' . str_pad($MyRow['employeenumber'], 6, '0', STR_PAD_LEFT) . '</td>
				<td>' . $MyRow['firstname'] . ' ' . $MyRow['lastname'] . '</td>
				<td>' . $MyRow['email'] . '</td>
				<td>' . $MyRow['phone'] . '</td>
				<td>' . $MyRow['description'] . '</td>
				<td>' . $MyRow['positiontitle'] . '</td>
				<td>' . $MyRow['employmentstatus'] . '</td>
				<td>' . ConvertSQLDate($MyRow['hiredate']) . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedEmployee=' . $MyRow['employeeid'] . '">' . __('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedEmployee=' . $MyRow['employeeid'] . '&amp;delete=yes" onclick="return confirm(\'' . __('Are you sure you wish to delete this employee?') . '\');">' . __('Delete') . '</a></td>
			</tr>';
	}

	echo '</tbody>
		</table>';
}

include(__DIR__ . '/includes/footer.php');

?>
