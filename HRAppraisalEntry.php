<?php

/*
 * Performance Appraisal Entry/Edit
 *
 * Allows HR managers to create new performance appraisals for employees
 * and edit existing appraisal records. Three URL modes are supported:
 *
 *   (no params)               -- blank form to create a new appraisal
 *   AppraisalID=N&Edit=Yes    -- edit form pre-filled from the database
 *   AppraisalID=N             -- read-only view with a link to switch to edit
 *   AppraisalID=N&Delete=1    -- delete the appraisal and redirect
 */

require(__DIR__ . '/includes/session.php');

$Title     = __('Performance Appraisal Entry');
$ViewTopic = 'HumanResources';
$BookMark  = 'AppraisalEntry';

include(__DIR__ . '/includes/header.php');

/*
 * Rating labels map the integer stored in hrperfappraisals.overallrating (INT)
 * to human-readable labels. 5 = highest, 1 = lowest, keyed in descending order
 * so they appear in that order in the select element.
 */
$RatingLabels = array(
	5 => __('Outstanding'),
	4 => __('Exceeds Expectations'),
	3 => __('Meets Expectations'),
	2 => __('Needs Improvement'),
	1 => __('Unsatisfactory'),
);

echo '<a class="toplink" href="' . $RootPath . '/HRPerformanceAppraisals.php">' . __('Return to Appraisals List') . '</a>';

/* Edit mode: an AppraisalID is present in the URL */
$EditMode = (isset($_GET['AppraisalID']) AND (int)$_GET['AppraisalID'] > 0);

/* Get appraisal frequency from system options */
$SQL = "SELECT optionvalue
	FROM hrsystemoptions
	WHERE optionname = 'AppraisalFrequency'";
$OptionsResult  = DB_query($SQL);
$AppraisalFrequency = 365;
if (DB_num_rows($OptionsResult) > 0) {
	$OptionRow          = DB_fetch_array($OptionsResult);
	$AppraisalFrequency = $OptionRow['optionvalue'];
}

/* Handle form submission (both new and edit) */
if (isset($_POST['Submit'])) {

	$InputError = 0;

	if (!isset($_POST['EmployeeNumber']) OR $_POST['EmployeeNumber'] == '') {
		$InputError = 1;
		prnMsg(__('Employee must be selected'), 'error');
	}
	if (!isset($_POST['ReviewPeriodStart']) OR $_POST['ReviewPeriodStart'] == '') {
		$InputError = 1;
		prnMsg(__('Review period start date is required'), 'error');
	}
	if (!isset($_POST['ReviewPeriodEnd']) OR $_POST['ReviewPeriodEnd'] == '') {
		$InputError = 1;
		prnMsg(__('Review period end date is required'), 'error');
	}
	if (!isset($_POST['DueDate']) OR $_POST['DueDate'] == '') {
		$InputError = 1;
		prnMsg(__('Due date is required'), 'error');
	}

	if ($InputError == 0) {

		/* Resolve the employee number to the internal employee ID */
		$EmpSQL    = "SELECT employeeid
					FROM hremployees
					WHERE employeenumber = '" . DB_escape_string($_POST['EmployeeNumber']) . "'";
		$EmpResult = DB_query($EmpSQL);
		if (DB_num_rows($EmpResult) == 0) {
			prnMsg(__('Invalid employee selected'), 'error');
			$InputError = 1;
		} else {
			$EmpRow     = DB_fetch_array($EmpResult);
			$EmployeeID = $EmpRow['employeeid'];
		}
	}

	if ($InputError == 0) {

		$ReviewPeriodStart = FormatDateForSQL($_POST['ReviewPeriodStart']);
		$ReviewPeriodEnd   = FormatDateForSQL($_POST['ReviewPeriodEnd']);
		$DueDate           = FormatDateForSQL($_POST['DueDate']);
		$ReviewerID        = (isset($_POST['ReviewerID']) AND $_POST['ReviewerID'] != '') ? (int)$_POST['ReviewerID'] : 'NULL';
		$Status            = DB_escape_string($_POST['Status']);
		$OverallRating     = (isset($_POST['OverallRating']) AND $_POST['OverallRating'] != '') ? (int)$_POST['OverallRating'] : 'NULL';
		$Comments          = DB_escape_string($_POST['Comments']);

		if ($EditMode) {

			/* Update existing record */
			$AppraisalID = (int)$_GET['AppraisalID'];
			$SQL = "UPDATE hrperfappraisals SET
					employeeid        = " . $EmployeeID . ",
					reviewperiodstart = '" . $ReviewPeriodStart . "',
					reviewperiodend   = '" . $ReviewPeriodEnd . "',
					duedate           = '" . $DueDate . "',
					reviewerid        = " . $ReviewerID . ",
					status            = '" . $Status . "',
					overallrating     = " . $OverallRating . ",
					comments          = '" . $Comments . "',
					modifieddate      = NOW()
				WHERE appraisalid = " . $AppraisalID;
			DB_query($SQL, __('Failed to update appraisal'));
			prnMsg(__('Appraisal has been updated'), 'success');

		} else {

			/* Insert new record */
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
					" . $ReviewerID . ",
					'" . $Status . "',
					" . $OverallRating . ",
					'" . $Comments . "',
					'Annual'
				)";
			DB_query($SQL, __('Failed to create appraisal'));
			$AppraisalID = DB_Last_Insert_ID('hrperfappraisals', 'appraisalid');
			prnMsg(__('Appraisal has been created'), 'success');
			echo '<p class="centre">
					<a href="' . $RootPath . '/HRAppraisalEntry.php?AppraisalID=' . $AppraisalID . '">' . __('Continue editing this appraisal') . '</a>
				</p>';
		}
	}
}

/* Handle delete before attempting any form rendering */
if (isset($_GET['Delete']) AND isset($_GET['AppraisalID'])) {

	$AppraisalID = (int)$_GET['AppraisalID'];

	/* Delete related goal assessments before removing the appraisal header */
	$SQL = "DELETE FROM hrperfgoals WHERE appraisalid = " . $AppraisalID;
	DB_query($SQL);

	$SQL = "DELETE FROM hrperfappraisals WHERE appraisalid = " . $AppraisalID;
	DB_query($SQL);

	prnMsg(__('Appraisal has been deleted'), 'success');
	echo '<p class="centre">
			<a href="' . $RootPath . '/HRPerformanceAppraisals.php">' . __('Return to appraisals list') . '</a>
		</p>';
	include(__DIR__ . '/includes/footer.php');
	exit;
}

/* Load existing record when an AppraisalID is present in the URL */
if (isset($_GET['AppraisalID'])) {

	$AppraisalID = (int)$_GET['AppraisalID'];

	$SQL = "SELECT a.*,
			CONCAT(e.firstname, ' ', e.lastname) AS employeename,
			e.employeenumber
		FROM hrperfappraisals a
		INNER JOIN hremployees e ON a.employeeid = e.employeeid
		WHERE a.appraisalid = " . $AppraisalID;
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		prnMsg(__('Appraisal not found'), 'error');
		include(__DIR__ . '/includes/footer.php');
		exit;
	}

	$MyRow = DB_fetch_array($Result);

	$DbEmployeeName      = $MyRow['employeename'];
	$DbEmployeeNumber    = $MyRow['employeenumber'];
	$DbReviewPeriodStart = ConvertSQLDate($MyRow['reviewperiodstart']);
	$DbReviewPeriodEnd   = ConvertSQLDate($MyRow['reviewperiodend']);
	$DbDueDate           = ConvertSQLDate($MyRow['duedate']);
	$DbReviewerID        = $MyRow['reviewerid'];
	$DbStatus            = $MyRow['status'];
	$DbOverallRating     = $MyRow['overallrating'];
	$DbComments          = $MyRow['comments'];
} else {
	$DbEmployeeName      = '';
	$DbEmployeeNumber    = '';
	$DbReviewPeriodStart = DateAdd(date($_SESSION['DefaultDateFormat']), 'y', -1);
	$DbReviewPeriodEnd   = DateAdd($DbReviewPeriodStart, 'y', 1);
	$DbDueDate           = date($_SESSION['DefaultDateFormat']);
	$DbReviewerID        = '';
	$DbStatus            = '';
	$DbOverallRating     = '';
	$DbComments          = '';
}

/* Page title varies by mode */
if ($EditMode) {

	echo '<p class="page_title_text">
			<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('Edit Appraisal') . '" /> ' .
			__('Edit Performance Appraisal') . ' - ' . __('ID') . ': ' . $AppraisalID . '
		</p>';
	echo '<p class="centre">
			<a href="' . $RootPath . '/HRAppraisalEntry.php?AppraisalID=' . $AppraisalID . '&Delete=1"
				onclick="return confirm(\'' . __('Are you sure you want to delete this appraisal?') . '\');">' .
				__('Delete This Appraisal') . '
			</a>
		</p>';
	$FormAction = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8')
		. '?AppraisalID=' . $AppraisalID;

	echo '<form method="post" action="' . $FormAction . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>' . __('Edit Performance Appraisal') . '</legend>';

	/* Employee is read-only on an existing appraisal -- pass number via hidden field */
	echo '<field>
			<label>' . __('Employee') . ':</label>
			<div>' . htmlspecialchars($DbEmployeeName, ENT_QUOTES, 'UTF-8') .
				' (' . htmlspecialchars($DbEmployeeNumber, ENT_QUOTES, 'UTF-8') . ')</div>
			<input type="hidden" name="EmployeeNumber" value="' . htmlspecialchars($DbEmployeeNumber, ENT_QUOTES, 'UTF-8') . '" />
		</field>';

} else {

	echo '<p class="page_title_text">
			<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('New Appraisal') . '" /> ' .
			__('Create New Performance Appraisal') . '
		</p>';
	$FormAction = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');

	echo '<form method="post" action="' . $FormAction . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>' . __('New Performance Appraisal') . '</legend>';

	echo '<field>
			<label for="EmployeeNumber">' . __('Employee') . ':</label>
			<select name="EmployeeNumber" required>';
	echo '<option value="">' . __('Select Employee') . '</option>';
	$SQL    = "SELECT employeenumber,
				CONCAT(firstname, ' ', lastname) AS name
			FROM hremployees
			WHERE employmentstatus = 'Active'
			ORDER BY lastname, firstname";
	$Result = DB_query($SQL);
	while ($EmpRow = DB_fetch_array($Result)) {
		echo '<option value="' . htmlspecialchars($EmpRow['employeenumber'], ENT_QUOTES, 'UTF-8') . '">' .
			htmlspecialchars($EmpRow['name'], ENT_QUOTES, 'UTF-8') . '</option>';
	}
	echo '</select>
		</field>';
}

/*
 * Edit appraisal section: fields pre-filled from the database.
 * Only rendered when AppraisalID and Edit=Yes are both present in the URL.
 */

echo '<field>
		<label for="ReviewPeriodStart">' . __('Review Period Start') . ':</label>
		<input type="date" name="ReviewPeriodStart" required value="' . FormatDateForSQL($DbReviewPeriodStart) . '" />
	</field>
	<field>
		<label for="ReviewPeriodEnd">' . __('Review Period End') . ':</label>
		<input type="date" name="ReviewPeriodEnd" required value="' . FormatDateForSQL($DbReviewPeriodEnd) . '" />
	</field>
	<field>
		<label for="DueDate">' . __('Due Date') . ':</label>
		<input type="date" name="DueDate" required value="' . FormatDateForSQL($DbDueDate) . '" />
	</field>';

echo '<field>
		<label for="ReviewerID">' . __('Reviewer/Manager') . ':</label>
		<select name="ReviewerID">';
echo '<option value="">' . __('Select Reviewer') . '</option>';
$SQL    = "SELECT employeeid,
			CONCAT(firstname, ' ', lastname) AS name
		FROM hremployees
		WHERE employmentstatus = 'Active'
		ORDER BY lastname, firstname";
$Result = DB_query($SQL);
while ($MgrRow = DB_fetch_array($Result)) {
	echo '<option value="' . $MgrRow['employeeid'] . '"' .
		($DbReviewerID == $MgrRow['employeeid'] ? ' selected' : '') . '>' .
	htmlspecialchars($MgrRow['name'], ENT_QUOTES, 'UTF-8') . '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="Status">' . __('Status') . ':</label>
		<select name="Status" required>
			<option value="Not Started"' . ($DbStatus == 'Not Started' ? ' selected' : '') . '>' . __('Not Started') . '</option>
			<option value="In Progress"' . ($DbStatus == 'In Progress' ? ' selected' : '') . '>' . __('In Progress') . '</option>
			<option value="Completed"' . ($DbStatus == 'Completed' ? ' selected' : '') . '>' . __('Completed') . '</option>
			<option value="Cancelled"' . ($DbStatus == 'Cancelled' ? ' selected' : '') . '>' . __('Cancelled') . '</option>
		</select>
	</field>';

echo '<field>
		<label for="OverallRating">' . __('Overall Rating') . ':</label>
		<select name="OverallRating">
			<option value="">' . __('Not Rated') . '</option>';
foreach ($RatingLabels as $RatingValue => $RatingLabel) {
	echo '<option value="' . $RatingValue . '"' .
		($DbOverallRating == $RatingValue ? ' selected' : '') . '>' . $RatingLabel . '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="Comments">' . __('Comments') . ':</label>
		<textarea name="Comments" rows="5" cols="60">' .
		htmlspecialchars($DbComments, ENT_QUOTES, 'UTF-8') . '</textarea>
	</field>';

echo '</fieldset>';

if ($EditMode) {
	echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Update Appraisal') . '" />
	</div>';
} else {
	echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Create Appraisal') . '" />
	</div>';
}
echo '</form>';

include(__DIR__ . '/includes/footer.php');
