<?php

/* HR Departments Maintenance */

require(__DIR__ . '/includes/session.php');

$Title = __('HR Departments');
$ViewTopic = 'HumanResources';
$BookMark = 'HRDepartments';

include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/building.png" title="' . __('Departments') . '" /> ' .
		__('HR Departments Maintenance') . '
	</p>';

if (isset($_GET['SelectedDepartment'])) {
	$SelectedDepartment = $_GET['SelectedDepartment'];
} elseif (isset($_POST['SelectedDepartment'])) {
	$SelectedDepartment = $_POST['SelectedDepartment'];
}

if (isset($_POST['Submit'])) {
	$InputError = 0;

	if (ContainsIllegalCharacters($_POST['DepartmentName'])) {
		$InputError = 1;
		prnMsg(__('The department name must not contain illegal characters') . " '&amp;' " . __('or') . " '", 'error');
	}
	if (trim($_POST['DepartmentName']) == '') {
		$InputError = 1;
		prnMsg(__('The department name must not be empty'), 'error');
	}
	if (trim($_POST['DepartmentCode']) == '') {
		$InputError = 1;
		prnMsg(__('The department code must not be empty'), 'error');
	}

	if (isset($SelectedDepartment) AND $InputError != 1) {
		// Update existing department
		$SQL = "SELECT departmentid FROM departments WHERE departmentcode = '" . $_POST['DepartmentCode'] . "' and departmentid<>'" . $SelectedDepartment . "'";
		$Result = DB_query($SQL);

		if (DB_num_rows($Result) == 0) {
			$SQL = "UPDATE departments SET
						departmentcode = '" . $_POST['DepartmentCode'] . "',
						description = '" . $_POST['DepartmentName'] . "',
						authoriser = '" . $_POST['Authoriser'] . "',
						parentdepartmentid = " . (int)$_POST['ParentDepartmentID'] . ",
						managerid = " . (int)$_POST['ManagerID'] . ",
						locationid = '" . $_POST['LocationID'] . "',
						active = '" . (int)$_POST['Active'] . "'
					WHERE departmentid = '" . $SelectedDepartment . "'";

			$ErrMsg = __('Could not update the department because');
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(__('Department') . ' ' . $_POST['DepartmentName'] . ' ' . __('has been updated'), 'success');
		} else {
			$InputError = 1;
			prnMsg(__('A department with that code already exists'), 'error');
		}
	} elseif ($InputError != 1) {
		// Insert new department
		$SQL = "SELECT COUNT(*) FROM departments WHERE departmentcode = '" . $_POST['DepartmentCode'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);

		if ($MyRow[0] > 0) {
			$InputError = 1;
			prnMsg(__('A department with this code already exists'), 'error');
		} else {
			$SQL = "INSERT INTO departments (
						departmentcode,
						description,
						authoriser,
						parentdepartmentid,
						managerid,
						locationid,
						active
					) VALUES (
						'" . $_POST['DepartmentCode'] . "',
						'" . $_POST['DepartmentName'] . "',
						'" . $_POST['Authoriser'] . "',
						" . (int)$_POST['ParentDepartmentID'] . ",
						" . (int)$_POST['ManagerID'] . ",
						'" . $_POST['LocationID'] . "',
						1
					)";

			$ErrMsg = __('Could not add the department because');
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(__('Department') . ' ' . $_POST['DepartmentName'] . ' ' . __('has been added'), 'success');
		}
	}

	if ($InputError != 1) {
		unset($SelectedDepartment);
		unset($_POST['DepartmentCode']);
		unset($_POST['DepartmentName']);
		unset($_POST['ParentDepartmentID']);
		unset($_POST['ManagerID']);
		unset($_POST['LocationID']);
	}

} elseif (isset($_GET['delete'])) {
	$CancelDelete = 0;

	// Check if department has employees
	$SQL = "SELECT COUNT(*) FROM hremployees WHERE departmentid = '" . $SelectedDepartment . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);

	if ($MyRow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(__('Cannot delete this department because there are') . ' ' . $MyRow[0] . ' ' . __('employees assigned to it'), 'error');
	}

	// Check if department has positions
	$SQL = "SELECT COUNT(*) FROM hrpositions WHERE departmentid = '" . $SelectedDepartment . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);

	if ($MyRow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(__('Cannot delete this department because there are') . ' ' . $MyRow[0] . ' ' . __('positions assigned to it'), 'error');
	}

	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM departments WHERE departmentid = '" . $SelectedDepartment . "'";
		$ErrMsg = __('Could not delete the department because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('Department has been deleted'), 'success');
		unset($SelectedDepartment);
	}
}

if (!isset($SelectedDepartment)) {
	// Display list of departments
	$SQL = "SELECT d.departmentid,
				d.departmentcode,
				d.description,
				pd.description as parentname,
				CONCAT(e.firstname, ' ', e.lastname) as managername,
				d.active,
				d.authoriser,
				d.locationid
			FROM departments d
			LEFT JOIN departments pd ON d.parentdepartmentid = pd.departmentid
			LEFT JOIN hremployees e ON d.managerid = e.employeeid
			ORDER BY d.departmentcode";

	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th class="SortedColumn">' . __('Code') . '</th>
				<th class="SortedColumn">' . __('Department Name') . '</th>
				<th class="SortedColumn">' . __('Parent Department') . '</th>
				<th class="SortedColumn">' . __('Manager') . '</th>
				<th class="SortedColumn">' . __('Authoriser') . '</th>
				<th class="SortedColumn">' . __('Location') . '</th>
				<th class="SortedColumn">' . __('Active') . '</th>
				<th colspan="2">&nbsp;</th>
			</tr>
		</thead>
		<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['locationid'] != '' and $MyRow['locationid'] != 0) {
			$LocationSQL = "SELECT locationname FROM locations WHERE loccode='" . $MyRow['locationid'] . "'";
			$LocationResult = DB_query($LocationSQL);
			$LocationRow = DB_fetch_array($LocationResult);
		} else {
			$LocationRow['locationname'] = '';
		}
		if ($MyRow['authoriser'] != '' and $MyRow['authoriser'] != 0) {
			$AuthoriserSQL = "SELECT realname FROM www_users WHERE userid='" . $MyRow['authoriser'] . "'";
			$AuthoriserResult = DB_query($AuthoriserSQL);
			$AuthoriserRow = DB_fetch_array($AuthoriserResult);
		} else {
			$AuthoriserRow['locationname'] = '';
		}
		echo '<tr class="striped_row">
				<td>' . $MyRow['departmentcode'] . '</td>
				<td>' . $MyRow['description'] . '</td>
				<td>' . $MyRow['parentname'] . '</td>
				<td>' . $MyRow['managername'] . '</td>
				<td>' . $AuthoriserRow['realname'] . ' - ' . $MyRow['authoriser'] . '</td>
				<td>' . $LocationRow['locationname'] . '</td>
				<td>' . ($MyRow['active'] ? __('Yes') : __('No')) . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedDepartment=' . $MyRow['departmentid'] . '">' . __('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedDepartment=' . $MyRow['departmentid'] . '&amp;delete=yes" onclick="return confirm(\'' . __('Are you sure you wish to delete this department?') . '\');">' . __('Delete') . '</a></td>
			</tr>';
	}

	echo '</tbody>
		</table>';
}

// Entry form for adding/editing department
if (isset($SelectedDepartment)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Show All Departments') . '</a></div>';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedDepartment)) {
	$SQL = "SELECT * FROM departments WHERE departmentid = '" . $SelectedDepartment . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['DepartmentID'] = $MyRow['departmentid'];
	$_POST['DepartmentCode'] = $MyRow['departmentcode'];
	$_POST['DepartmentName'] = $MyRow['description'];
	$_POST['ParentDepartmentID'] = $MyRow['parentdepartmentid'];
	$_POST['ManagerID'] = $MyRow['managerid'];
	$_POST['LocationID'] = $MyRow['locationid'];
	$_POST['Authoriser'] = $MyRow['authoriser'];
	$_POST['Active'] = $MyRow['active'];

	echo '<input type="hidden" name="SelectedDepartment" value="' . $SelectedDepartment . '" />';
	echo '<input type="hidden" name="DepartmentID" value="' . $_POST['DepartmentID'] . '" />';
	echo '<input type="hidden" name="DepartmentCode" value="' . $_POST['DepartmentCode'] . '" />';
	echo '<fieldset>
			<legend>' . __('Edit Department') . '</legend>';
	echo '<field>
			<label>' . __('Department Code') . ':</label>
			<input type="text" name="DepartmentCode" required="required" size="15" maxlength="20" value="' . (isset($_POST['DepartmentCode']) ? $_POST['DepartmentCode'] : '') . '" />
		</field>';
} else {
	echo '<fieldset>';
	echo '<legend>' . __('Add New Department') . '</legend>';
	echo '<field>
			<label for="DepartmentCode">' . __('Department Code') . ':</label>
			<input type="text" name="DepartmentCode" required="required" size="15" maxlength="20" value="' . (isset($_POST['DepartmentCode']) ? $_POST['DepartmentCode'] : '') . '" />
		</field>';
}

echo '<field>
		<label for="DepartmentName">' . __('Department Name') . ':</label>
		<input type="text" name="DepartmentName" required="required" size="40" maxlength="100" value="' . (isset($_POST['DepartmentName']) ? $_POST['DepartmentName'] : '') . '" />
	</field>';

// Parent Department dropdown
echo '<field>
		<label for="ParentDepartmentID">' . __('Parent Department') . ':</label>
		<select name="ParentDepartmentID">';
echo '<option value="0">' . __('None') . '</option>';
$SQL = "SELECT departmentid, description FROM departments WHERE active = 1 ORDER BY description";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['ParentDepartmentID'] )AND $_POST['ParentDepartmentID'] == $MyRow['departmentid']) {
		echo '<option selected="selected" value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
	}
}
echo '</select>
	</field>';

// Manager dropdown
echo '<field>
		<Label for="ManagerID">' . __('Department Manager') . ':</label>
		<select name="ManagerID">';
echo '<option value="0">' . __('None') . '</option>';
$SQL = "SELECT employeeid, CONCAT(firstname, ' ', lastname) as fullname
		FROM hremployees
		WHERE employmentstatus = 'Active'
		ORDER BY lastname, firstname";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['ManagerID']) AND $_POST['ManagerID'] == $MyRow['employeeid']) {
		echo '<option selected="selected" value="' . $MyRow['employeeid'] . '">' . $MyRow['fullname'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['employeeid'] . '">' . $MyRow['fullname'] . '</option>';
	}
}
echo '</select>
	</field>';

// Location dropdown
echo '<field>
		<label for="LocationID">' . __('Location') . ':</label>
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

// Authoriser dropdown
echo '<field>
		<label for="Authoriser">' . __('Internal Requests Authoriser') . ':</label>
		<select name="Authoriser">';
echo '<option value="">' . __('None') . '</option>';
$SQL = "SELECT userid, realname FROM www_users ORDER BY realname";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['Authoriser']) AND $_POST['Authoriser'] == $MyRow['userid']) {
		echo '<option selected="selected" value="' . $MyRow['userid'] . '">' . $MyRow['realname'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['userid'] . '">' . $MyRow['realname'] . '</option>';
	}
}
echo '</select>
	</field>';

if (isset($SelectedDepartment)) {
	echo '<field>
			<label for="Active">' . __('Active') . ':</label>
			<input type="checkbox" name="Active" value="1"' . (isset($_POST['Active']) && $_POST['Active'] == 1 ? ' checked="checked"' : '') . ' />
		</field>';
}

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Submit') . '" />
	</div>
	</form>';

include(__DIR__ . '/includes/footer.php');

?>
