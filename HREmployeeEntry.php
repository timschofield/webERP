<?php

/* HR Employee Entry - Add/Edit Employee Details */

require(__DIR__ . '/includes/session.php');

$Title = __('Employee Entry');
$ViewTopic = 'HumanResources';
$BookMark = 'HREmployeeEntry';

include(__DIR__ . '/includes/header.php');

// Get system options
$SQL = "SELECT optionname, optionvalue FROM hrsystemoptions WHERE optionname = 'ProbationPeriod'";
$OptionsResult = DB_query($SQL);
$ProbationPeriod = 90; // Default
if (DB_num_rows($OptionsResult) > 0) {
	$OptionRow = DB_fetch_array($OptionsResult);
	$ProbationPeriod = $OptionRow['optionvalue'];
}

echo '<a class="toplink" href="' . $RootPath . '/HREmployees.php">' . __('Back to Employee List') . '</a>';

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/user.png" title="' . __('Employee Entry') . '" /> ' .
		__('Add/Edit Employee') . '
	</p>';

// Get employee ID if editing
$EmployeeID = isset($_GET['EmployeeID']) ? (int)$_GET['EmployeeID'] : (isset($_POST['EmployeeID']) ? (int)$_POST['EmployeeID'] : 0);

if (isset($_POST['Submit'])) {
	$InputError = 0;

	// Validation
	if (trim($_POST['EmployeeNumber']) == '') {
		$InputError = 1;
		prnMsg(__('The employee number must not be empty'), 'error');
	}
	if (trim($_POST['FirstName']) == '') {
		$InputError = 1;
		prnMsg(__('The first name must not be empty'), 'error');
	}
	if (trim($_POST['LastName']) == '') {
		$InputError = 1;
		prnMsg(__('The last name must not be empty'), 'error');
	}
	if (!is_date($_POST['HireDate'])) {
		$InputError = 1;
		prnMsg(__('The hire date must be a valid date'), 'error');
	}

	if ($InputError != 1) {
		$SQL_HireDate = FormatDateForSQL($_POST['HireDate']);
		$SQL_BirthDate = !empty($_POST['BirthDate']) && is_date($_POST['BirthDate']) ? "'" . FormatDateForSQL($_POST['BirthDate']) . "'" : 'NULL';
		$SQL_TermDate = !empty($_POST['TerminationDate']) && is_date($_POST['TerminationDate']) ? "'" . FormatDateForSQL($_POST['TerminationDate']) . "'" : 'NULL';

		if ($EmployeeID > 0) {
			// Update existing employee
			$SQL = "UPDATE hremployees SET
						employeenumber = '" . $_POST['EmployeeNumber'] . "',
						firstname = '" . $_POST['FirstName'] . "',
						middlename = '" . $_POST['MiddleName'] . "',
						lastname = '" . $_POST['LastName'] . "',
						email = '" . $_POST['Email'] . "',
						phone = '" . $_POST['Phone'] . "',
						mobilephone = '" . $_POST['MobilePhone'] . "',
						address1 = '" . $_POST['Address1'] . "',
						address2 = '" . $_POST['Address2'] . "',
						city = '" . $_POST['City'] . "',
						state = '" . $_POST['State'] . "',
						postalcode = '" . $_POST['PostalCode'] . "',
						country = '" . $_POST['Country'] . "',
						birthdate = " . $SQL_BirthDate . ",
						hiredate = '" . $SQL_HireDate . "',
						terminationdate = " . $SQL_TermDate . ",
						departmentid = " . (int)$_POST['DepartmentID'] . ",
						positionid = " . (int)$_POST['PositionID'] . ",
						supervisorid = " . ((int)$_POST['SupervisorID'] > 0 ? (int)$_POST['SupervisorID'] : 'NULL') . ",
						employmentstatus = '" . $_POST['EmploymentStatus'] . "',
						employmenttype = '" . $_POST['EmploymentType'] . "',
						location = '" . $_POST['Location'] . "',
						modifiedby = '" . $_SESSION['UserID'] . "',
						modifieddate = NOW()
					WHERE employeeid = " . $EmployeeID;

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Employee has been updated successfully'), 'success');
			}
		} else {
			// Insert new employee
			$SQL = "INSERT INTO hremployees (
						employeenumber, firstname, middlename, lastname,
						email, phone, mobilephone,
						address1, address2, city, state, postalcode, country,
						birthdate, hiredate, terminationdate,
						departmentid, positionid, supervisorid,
						employmentstatus, employmenttype, location,
						createdby, createddate
					) VALUES (
						'" . $_POST['EmployeeNumber'] . "',
						'" . $_POST['FirstName'] . "',
						'" . $_POST['MiddleName'] . "',
						'" . $_POST['LastName'] . "',
						'" . $_POST['Email'] . "',
						'" . $_POST['Phone'] . "',
						'" . $_POST['MobilePhone'] . "',
						'" . $_POST['Address1'] . "',
						'" . $_POST['Address2'] . "',
						'" . $_POST['City'] . "',
						'" . $_POST['State'] . "',
						'" . $_POST['PostalCode'] . "',
						'" . $_POST['Country'] . "',
						" . $SQL_BirthDate . ",
						'" . $SQL_HireDate . "',
						" . $SQL_TermDate . ",
						" . (int)$_POST['DepartmentID'] . ",
						" . (int)$_POST['PositionID'] . ",
						" . ((int)$_POST['SupervisorID'] > 0 ? (int)$_POST['SupervisorID'] : 'NULL') . ",
						'" . $_POST['EmploymentStatus'] . "',
						'" . $_POST['EmploymentType'] . "',
						'" . $_POST['Location'] . "',
						'" . $_SESSION['UserID'] . "',
						NOW()
					)";

			$Result = DB_query($SQL);
			if ($Result) {
				$EmployeeID = DB_Last_Insert_ID();
				prnMsg(__('Employee has been created successfully'), 'success');
			}
		}
	}
}

// Load employee data if editing
$EmployeeNumber = '';
$FirstName = '';
$MiddleName = '';
$LastName = '';
$Email = '';
$Phone = '';
$MobilePhone = '';
$Address1 = '';
$Address2 = '';
$City = '';
$State = '';
$PostalCode = '';
$Country = '';
$BirthDate = '';
$HireDate = Date($_SESSION['DefaultDateFormat']);
$TerminationDate = '';
$DepartmentID = 0;
$PositionID = 0;
$SupervisorID = 0;
$EmploymentStatus = 'Active';
$EmploymentType = 'Full-Time';
$Location = '';

if ($EmployeeID > 0) {
	$SQL = "SELECT * FROM hremployees WHERE employeeid = " . $EmployeeID;
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_array($Result);
		$EmployeeNumber = $Row['employeenumber'];
		$FirstName = $Row['firstname'];
		$MiddleName = $Row['middlename'];
		$LastName = $Row['lastname'];
		$Email = $Row['email'];
		$Phone = $Row['phone'];
		$MobilePhone = $Row['mobilephone'];
		$Address1 = $Row['address1'];
		$Address2 = $Row['address2'];
		$City = $Row['city'];
		$State = $Row['state'];
		$PostalCode = $Row['postalcode'];
		$Country = $Row['country'];
		$BirthDate = ConvertSQLDate($Row['birthdate']);
		$HireDate = ConvertSQLDate($Row['hiredate']);
		$TerminationDate = ConvertSQLDate($Row['terminationdate']);
		$DepartmentID = $Row['departmentid'];
		$PositionID = $Row['positionid'];
		$SupervisorID = $Row['supervisorid'];
		$EmploymentStatus = $Row['employmentstatus'];
		$EmploymentType = $Row['employmenttype'];
		$Location = $Row['location'];
	}
}

// Display form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if ($EmployeeID > 0) {
	echo '<input type="hidden" name="EmployeeID" value="' . $EmployeeID . '" />';
}

echo '<fieldset>
		<legend>' . __('Employee Information') . '</legend>';

echo '<field>
		<label for="EmployeeNumber">' . __('Employee Number') . ':</label>
		<input type="text" name="EmployeeNumber" value="' . $EmployeeNumber . '" size="20" maxlength="20" required="required" />
	</field>';

echo '<field>
		<label for="FirstName">' . __('First Name') . ':</label>
		<input type="text" name="FirstName" value="' . $FirstName . '" size="30" maxlength="50" required="required" />
	</field>';

echo '<field>
		<label for="MiddleName">' . __('Middle Name') . ':</label>
		<input type="text" name="MiddleName" value="' . $MiddleName . '" size="30" maxlength="50" />
	</field>';

echo '<field>
		<label for="LastName">' . __('Last Name') . ':</label>
		<input type="text" name="LastName" value="' . $LastName . '" size="30" maxlength="50" required="required" />
	</field>';

echo '<field>
		<label for="Email">' . __('Email') . ':</label>
		<input type="email" name="Email" value="' . $Email . '" size="50" maxlength="100" />
	</field>';

echo '<field>
		<label for="Phone">' . __('Phone') . ':</label>
		<input type="tel" name="Phone" value="' . $Phone . '" size="20" maxlength="20" />
	</field>';

echo '<field>
		<label for="MobilePhone">' . __('Mobile Phone') . ':</label>
		<input type="tel" name="MobilePhone" value="' . $MobilePhone . '" size="20" maxlength="20" />
	</field>';

echo '<field>
		<label for="BirthDate">' . __('Birth Date') . ':</label>
		<input type="date" name="BirthDate" class="date" value="' . $BirthDate . '" />
	</field>';

echo '<field>
		<label for="HireDate">' . __('Hire Date') . ':</label>
		<input type="date" name="HireDate" class="date" value="' . $HireDate . '" required="required" />
		<br /><em>' . __('Probation period') . ': ' . $ProbationPeriod . ' ' . __('days') . '</em>
	</field>';

echo '</fieldset>';

echo '<fieldset>
		<legend>' . __('Address Information') . '</legend>';

echo '<field>
		<label for="Address1">' . __('Address Line 1') . ':</label>
		<input type="text" name="Address1" value="' . $Address1 . '" size="50" maxlength="100" />
	</field>';

echo '<field>
		<label for="Address2">' . __('Address Line 2') . ':</label>
		<input type="text" name="Address2" value="' . $Address2 . '" size="50" maxlength="100" />
	</field>';

echo '<field>
		<label for="City">' . __('City') . ':</label>
		<input type="text" name="City" value="' . $City . '" size="30" maxlength="50" />
	</field>';

echo '<field>
		<label for="State">' . __('State/Province') . ':</label>
		<td><input type="text" name="State" value="' . $State . '" size="20" maxlength="50" /></td>
	</field>';

echo '<field>
		<label for="PostalCode">' . __('Postal Code') . ':</label>
		<td><input type="text" name="PostalCode" value="' . $PostalCode . '" size="15" maxlength="20" /></td>
	</field>';

include(__DIR__ . '/includes/CountriesArray.php');
echo '<field>
		<label for="Country">' . __('Country') . ':</label>
		<select name="Country">';
foreach ($CountriesArray as $CountryEntry => $CountryName){
	if (isset($_POST['Address6']) AND (strtoupper($_POST['Address6']) == strtoupper($CountryName))){
		echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName  . '</option>';
	} else {
		echo '<option value="' . $CountryName . '">' . $CountryName  . '</option>';
	}
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<fieldset>
		<legend>' . __('Employment Details') . '</legend>';

// Department dropdown
echo '<field>
		<label for="DepartmentID">' . __('Department') . ':</label>
		<select name="DepartmentID" required="required">';
echo '<option value="0">' . __('Select Department') . '</option>';
$SQL = "SELECT departmentid, description FROM departments WHERE active = 1 ORDER BY description";
$Result = DB_query($SQL);
while ($Row = DB_fetch_array($Result)) {
	echo '<option value="' . $Row['departmentid'] . '"' . ($DepartmentID == $Row['departmentid'] ? ' selected="selected"' : '') . '>' . $Row['description'] . '</option>';
}
echo '</select>
	</field>';

// Position dropdown
echo '<field>
		<label for="PositionID">' . __('Position') . ':</label>
		<select name="PositionID" required="required">';
echo '<option value="0">' . __('Select Position') . '</option>';
$SQL = "SELECT positionid, positiontitle FROM hrpositions WHERE positionstatus = 'Open' OR positionstatus = 'Filled' ORDER BY positiontitle";
$Result = DB_query($SQL);
while ($Row = DB_fetch_array($Result)) {
	echo '<option value="' . $Row['positionid'] . '"' . ($PositionID == $Row['positionid'] ? ' selected="selected"' : '') . '>' . $Row['positiontitle'] . '</option>';
}
echo '</select>
	</field>';

// Supervisor dropdown
echo '<field>
		<label for="SupervisorID">' . __('Supervisor') . ':</label>
		<select name="SupervisorID">';
echo '<option value="0">' . __('None') . '</option>';
$SQL = "SELECT employeeid, CONCAT(firstname, ' ', lastname) as fullname
		FROM hremployees
		WHERE employmentstatus = 'Active' AND employeeid != " . $EmployeeID . "
		ORDER BY firstname, lastname";
$Result = DB_query($SQL);
while ($Row = DB_fetch_array($Result)) {
	echo '<option value="' . $Row['employeeid'] . '"' . ($SupervisorID == $Row['employeeid'] ? ' selected="selected"' : '') . '>' . $Row['fullname'] . '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="EmploymentStatus">' . __('Employment Status') . ':</label>
		<select name="EmploymentStatus">';
$Statuses = array('Active', 'On Leave', 'Suspended', 'Terminated');
foreach ($Statuses as $Status) {
	echo '<option value="' . $Status . '"' . ($EmploymentStatus == $Status ? ' selected="selected"' : '') . '>' . __($Status) . '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="EmploymentType">' . __('Employment Type') . ':</label>
		<select name="EmploymentType">';
$Types = array('Full-Time', 'Part-Time', 'Contract', 'Temporary', 'Intern');
foreach ($Types as $Type) {
	echo '<option value="' . $Type . '"' . ($EmploymentType == $Type ? ' selected="selected"' : '') . '>' . __($Type) . '</option>';
}
echo '</select>
	</field>';

// Location dropdown
echo '<field>
		<label for="Location">' . __('Location') . ':</label>
		<select name="Location">';
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

if ($EmployeeID > 0) {
	echo '<field>
			<label for="TerminationDate">' . __('Termination Date') . ':</label>
			<input type="date" name="TerminationDate" class="date" value="' . $TerminationDate . '" />
		</field>';
}

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Save Employee') . '" />
	</div>';

echo '</form>';

include(__DIR__ . '/includes/footer.php');

?>
