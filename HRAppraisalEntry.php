<?php

/* Performance Appraisal Entry/Edit */

require(__DIR__ . '/includes/session.php');

$Title = __('Performance Appraisal Entry');
$ViewTopic = 'HumanResources';
$BookMark = 'HRAppraisalEntry';

include(__DIR__ . '/includes/header.php');

// Get system options
$SQL = "SELECT optionname, optionvalue FROM hrsystemoptions WHERE optionname = 'AppraisalFrequency'";
$OptionsResult = DB_query($SQL);
$AppraisalFrequency = 365; // Default to annual
if (DB_num_rows($OptionsResult) > 0) {
	$OptionRow = DB_fetch_array($OptionsResult);
	$AppraisalFrequency = $OptionRow['optionvalue'];
}

// Handle form submission
if (isset($_POST['Submit'])) {

	$InputError = 0;

	// Validation
	if (!isset($_POST['EmployeeNumber']) || $_POST['EmployeeNumber'] == '') {
		$InputError = 1;
		prnMsg(__('Employee must be selected'), 'error');
	}
	if (!isset($_POST['ReviewPeriodStart']) || $_POST['ReviewPeriodStart'] == '') {
		$InputError = 1;
		prnMsg(__('Review period start date is required'), 'error');
	}
	if (!isset($_POST['ReviewPeriodEnd']) || $_POST['ReviewPeriodEnd'] == '') {
		$InputError = 1;
		prnMsg(__('Review period end date is required'), 'error');
	}
	if (!isset($_POST['DueDate']) || $_POST['DueDate'] == '') {
		$InputError = 1;
		prnMsg(__('Due date is required'), 'error');
	}

	if ($InputError == 0) {

		// Get the actual employee ID from the employee number
		$EmployeeNumber = $_POST['EmployeeNumber'];
		$EmpSQL = "SELECT employeeid,employeenumber FROM hremployees WHERE employeeid = '" . $EmployeeNumber . "'";
		$EmpResult = DB_query($EmpSQL);
		if (DB_num_rows($EmpResult) == 0) {
			prnMsg(__('Invalid employee selected'), 'error');
			$InputError = 1;
		} else {
			$EmpRow = DB_fetch_array($EmpResult);
			$EmployeeID = $EmpRow['employeeid'];
		}

		$ReviewPeriodStart = FormatDateForSQL($_POST['ReviewPeriodStart']);
		$ReviewPeriodEnd = FormatDateForSQL($_POST['ReviewPeriodEnd']);
		$DueDate = FormatDateForSQL($_POST['DueDate']);
		$ReviewerID = (isset($_POST['ReviewerID']) && $_POST['ReviewerID'] != '') ? $_POST['ReviewerID'] : null;
		$Status = $_POST['Status'];
		$OverallRating = (isset($_POST['OverallRating']) && $_POST['OverallRating'] != '') ? $_POST['OverallRating'] : null;
		$Comments = $_POST['Comments'];

		if ($InputError == 0 && isset($_GET['AppraisalID']) && $_GET['AppraisalID'] != '') {
			// Update existing appraisal
			$AppraisalID = (int)$_GET['AppraisalID'];

			$SQL = "UPDATE hrperfappraisals SET
					employeeid = " . $EmployeeID . ",
					reviewperiodstart = '" . $ReviewPeriodStart . "',
					reviewperiodend = '" . $ReviewPeriodEnd . "',
					duedate = '" . $DueDate . "',
					reviewerid = " . ($ReviewerID === null ? 'NULL' : "'" . $ReviewerID . "'") . ",
					status = '" . $Status . "',
					overallrating = " . ($OverallRating === null ? 'NULL' : "'" . $OverallRating . "'") . ",
					comments = '" . $Comments . "',
					modifieddate = NOW()
				WHERE appraisalid = " . $AppraisalID;

			$ErrMsg = __('Failed to update appraisal');
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(__('Appraisal has been updated'), 'success');

		} elseif ($InputError == 0) {
			// Insert new appraisal
			$SQL = "INSERT INTO hrperfappraisals (
					employeeid,
					reviewperiodstart,
					reviewperiodend,
					duedate,
					reviewerid,
					status,
					overallrating,
					comments,
					appraisaltype
				) VALUES (
					" . $EmployeeID . ",
					'" . $ReviewPeriodStart . "',
					'" . $ReviewPeriodEnd . "',
					'" . $DueDate . "',
					" . ($ReviewerID === null ? 'NULL' : "'" . $ReviewerID . "'") . ",
					'" . $Status . "',
					" . ($OverallRating === null ? 'NULL' : "'" . $OverallRating . "'") . ",
					'" . $Comments . "',
					'Annual'
				)";

			$ErrMsg = __('Failed to insert appraisal');
			$Result = DB_query($SQL, $ErrMsg);

			$AppraisalID = DB_Last_Insert_ID('hrperformanceappraisals', 'appraisalid');

			prnMsg(__('Appraisal has been created'), 'success');

			echo '<p class="centre"><a href="' . $RootPath . '/HRAppraisalEntry.php?AppraisalID=' . $AppraisalID . '">' . __('Continue editing this appraisal') . '</a></p>';
		}
	}
}

echo '<a class="toplink" href="' . $RootPath . '/HRPerformanceAppraisals.php">' . __('Return to Appraisals List') . '</a></p>';

// Handle delete
if (isset($_GET['Delete']) && isset($_GET['AppraisalID'])) {
	$AppraisalID = (int)$_GET['AppraisalID'];

	// Delete related goal assessments first
	$SQL = "DELETE FROM hrperfgoalassessments WHERE appraisalid = " . $AppraisalID;
	DB_query($SQL);

	// Delete the appraisal
	$SQL = "DELETE FROM hrperfappraisals WHERE appraisalid = " . $AppraisalID;
	$Result = DB_query($SQL);

	prnMsg(__('Appraisal has been deleted'), 'success');

	echo '<p class="centre"><a href="' . $RootPath . '/HRPerformanceAppraisals.php">' . __('Return to appraisals list') . '</a></p>';

	include(__DIR__ . '/includes/footer.php');
	exit;
}

// Load existing data for edit
if (isset($_GET['AppraisalID'])) {
	$AppraisalID = (int)$_GET['AppraisalID'];

	$SQL = "SELECT * FROM hrperfappraisals WHERE appraisalid = " . $AppraisalID;
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		prnMsg(__('Appraisal not found'), 'error');
		include(__DIR__ . '/includes/footer.php');
		exit;
	}

	$MyRow = DB_fetch_array($Result);

	$EmployeeNumber = $MyRow['employeeid'];
	$ReviewPeriodStart = ConvertSQLDate($MyRow['reviewperiodstart']);
	$ReviewPeriodEnd = ConvertSQLDate($MyRow['reviewperiodend']);
	$DueDate = ConvertSQLDate($MyRow['duedate']);
	$ReviewerID = $MyRow['reviewerid'];
	$Status = $MyRow['status'];
	$OverallRating = $MyRow['overallrating'];
	$Comments = $MyRow['comments'];
	echo '<p class="page_title_text">
			<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('Edit Appraisal') . '" /> ' .
			__('Edit Performance Appraisal') . ' - ' . __('ID') . ': ' . $AppraisalID . '
		</p>';

	if (!isset($_GET['View'])) {
		echo '<p class="centre">
				<a href="' . $RootPath . '/HRAppraisalEntry.php?AppraisalID=' . $AppraisalID . '&Delete=1" onclick="return confirm(\'' . __('Are you sure you want to delete this appraisal?') . '\');">' . __('Delete This Appraisal') . '</a>
			</p>';
	}

} else {
	echo '<p class="page_title_text">
			<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('New Appraisal') . '" /> ' .
			__('Create New Performance Appraisal') . '
		</p>';

	$EmployeeNumber = '';
	$ReviewPeriodStart = date($_SESSION['DefaultDateFormat']);
	$ReviewPeriodEnd = date($_SESSION['DefaultDateFormat']);
	$DueDate = date($_SESSION['DefaultDateFormat']);
	$ReviewerID = '';
	$Status = 'Not Started';
	$OverallRating = '';
	$Comments = '';
}

// View mode check
$ViewMode = isset($_GET['View']) ? true : false;

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . (isset($_GET['AppraisalID']) ? '?AppraisalID=' . urlencode($_GET['AppraisalID']) : '') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Appraisal Details') . '</legend>';

// Employee selection
echo '<field>
		<label for="EmployeeNumber">' . __('Employee') . ':</label>';
if ($ViewMode || (isset($_GET['AppraisalID']) && $_GET['AppraisalID'] != '')) {
	$SQL = "SELECT CONCAT(firstname, ' ', lastname) as name FROM hremployees WHERE employeeid = '" . DB_escape_string($EmployeeNumber) . "'";
	$Result = DB_query($SQL);
	$EmpRow = DB_fetch_array($Result);
	echo '<div>' . htmlspecialchars($EmpRow['name'], ENT_QUOTES, 'UTF-8') . ' (' . $EmployeeNumber . ')</div>';
	echo '<input type="hidden" name="EmployeeNumber" value="' . htmlspecialchars($EmployeeNumber, ENT_QUOTES, 'UTF-8') . '" />';
} else {
	echo '<select name="EmployeeNumber" required>';
	echo '<option value="">' . __('Select Employee') . '</option>';
	$SQL = "SELECT employeenumber, CONCAT(firstname, ' ', lastname) as name
			FROM hremployees
			WHERE employmentstatus = 'Active'
			ORDER BY lastname, firstname";
	$Result = DB_query($SQL);
	while ($EmpRow = DB_fetch_array($Result)) {
		echo '<option value="' . $EmpRow['employeenumber'] . '"' . ($EmployeeNumber == $EmpRow['employeeid'] ? ' selected' : '') . '>' .
			htmlspecialchars($EmpRow['name'], ENT_QUOTES, 'UTF-8') . ' (' . $EmpRow['employeeid'] . ')</option>';
	}
	echo '</select>';
}
echo '</field>';

echo '<field>
		<label for="ReviewPeriodStart">' . __('Review Period Start') . ':</label>';
if ($ViewMode) {
	echo '<div>' . $ReviewPeriodStart . '</div>';
} else {
	echo '<input type="date" name="ReviewPeriodStart" required value="' . FormatDateForSQL($ReviewPeriodStart) . '" />';
}
echo '</field>';

echo '<field>
		<label for="ReviewPeriodEnd">' . __('Review Period End') . ':</label>';
if ($ViewMode) {
	echo '<div>' . $ReviewPeriodEnd . '</div>';
} else {
	echo '<input type="date" name="ReviewPeriodEnd" required value="' . FormatDateForSQL($ReviewPeriodEnd) . '" />';
}
echo '</field>';

echo '<field>
		<label for="DueDate">' . __('Due Date') . ':</label>';
if ($ViewMode) {
	echo '<div>' . $DueDate . '</div>';
} else {
	echo '<input type="date" name="DueDate" required value="' . FormatDateForSQL($DueDate) . '" />';
}
echo '</field>';

// Manager selection
echo '<field>
		<label for="ReviewerID">' . __('Reviewer/Manager') . ':</label>';
if ($ViewMode) {
	if ($ReviewerID) {
		$SQL = "SELECT CONCAT(firstname, ' ', lastname) as name FROM hremployees WHERE employeeid = '" . DB_escape_string($ReviewerID) . "'";
		$Result = DB_query($SQL);
		$MgrRow = DB_fetch_array($Result);
		echo '<div>' . htmlspecialchars($MgrRow['name'], ENT_QUOTES, 'UTF-8') . '</div>';
	} else {
		echo '<div>-</div>';
	}
} else {
	echo '<select name="ReviewerID">';
	echo '<option value="">' . __('Select Reviewer') . '</option>';
	$SQL = "SELECT employeeid, CONCAT(firstname, ' ', lastname) as name
			FROM hremployees
			WHERE employmentstatus = 'Active'
			ORDER BY lastname, firstname";
	$Result = DB_query($SQL);
	while ($MgrRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MgrRow['employeeid'] . '"' . ($ReviewerID == $MgrRow['employeeid'] ? ' selected' : '') . '>' .
			htmlspecialchars($MgrRow['name'], ENT_QUOTES, 'UTF-8') . '</option>';
	}
	echo '</select>';
}
echo '</field>';

echo '<field>
		<label for="Status">' . __('Status') . ':</label>';
if ($ViewMode) {
	echo '<div>' . htmlspecialchars($Status, ENT_QUOTES, 'UTF-8') . '</div>';
} else {
	echo '<select name="Status" required>
			<option value="Not Started"' . ($Status == 'Not Started' ? ' selected' : '') . '>' . __('Not Started') . '</option>
			<option value="In Progress"' . ($Status == 'In Progress' ? ' selected' : '') . '>' . __('In Progress') . '</option>
			<option value="Completed"' . ($Status == 'Completed' ? ' selected' : '') . '>' . __('Completed') . '</option>
			<option value="Cancelled"' . ($Status == 'Cancelled' ? ' selected' : '') . '>' . __('Cancelled') . '</option>
		</select>';
}
echo '</field>';

echo '<field>
		<label for="OverallRating">' . __('Overall Rating') . ':</label>';
if ($ViewMode) {
	echo '<div>' . ($OverallRating ? htmlspecialchars($OverallRating, ENT_QUOTES, 'UTF-8') : '-') . '</div>';
} else {
	echo '<select name="OverallRating">
			<option value="">' . __('Not Rated') . '</option>
			<option value="Outstanding"' . ($OverallRating == 'Outstanding' ? ' selected' : '') . '>' . __('Outstanding') . '</option>
			<option value="Exceeds Expectations"' . ($OverallRating == 'Exceeds Expectations' ? ' selected' : '') . '>' . __('Exceeds Expectations') . '</option>
			<option value="Meets Expectations"' . ($OverallRating == 'Meets Expectations' ? ' selected' : '') . '>' . __('Meets Expectations') . '</option>
			<option value="Needs Improvement"' . ($OverallRating == 'Needs Improvement' ? ' selected' : '') . '>' . __('Needs Improvement') . '</option>
			<option value="Unsatisfactory"' . ($OverallRating == 'Unsatisfactory' ? ' selected' : '') . '>' . __('Unsatisfactory') . '</option>
		</select>';
}
echo '</field>';

echo '<field>
		<label for="Comments">' . __('Comments') . ':</label>';
if ($ViewMode) {
	echo '<div>' . nl2br(htmlspecialchars($Comments, ENT_QUOTES, 'UTF-8')) . '</div>';
} else {
	echo '<textarea name="Comments" rows="5" cols="60">' . htmlspecialchars($Comments, ENT_QUOTES, 'UTF-8') . '</textarea>';
}
echo '</field>';

echo '</fieldset>';

if (!$ViewMode) {
	echo '<div class="centre">
				<input type="submit" name="Submit" value="' . __('Save Appraisal') . '" />
			</div>';
}
echo '</form>';

include(__DIR__ . '/includes/footer.php');

?>
