<?php

/*
 * Performance Appraisal Entry/Edit
 *
 * Allows HR managers to create new performance appraisals for employees
 * and edit existing appraisal records. The script has two modes:
 *
 *   (no params)         -- blank form to create a new appraisal
 *   AppraisalID=N       -- edit form pre-filled from the database
 *   AppraisalID=N&Delete=1 -- delete the appraisal and redirect
 */

require(__DIR__ . '/includes/session.php');

$Title     = __('Performance Appraisal Entry');
$ViewTopic = 'HumanResources';
$BookMark  = 'AppraisalEntry';

include(__DIR__ . '/includes/header.php');

require_once(__DIR__ . '/includes/HRPerformanceHelper.php');

/*
 * Rating labels map the integer stored in hrperfappraisals.overallrating (INT)
 * to human-readable labels. 5 = highest, 1 = lowest, keyed in descending order
 * so they appear in that order in the select element.
 */
$RatingLabels = GetRatingLabels();

/* Edit mode: an AppraisalID is present in the URL */
$EditMode = (isset($_GET['AppraisalID']) AND (int)$_GET['AppraisalID'] > 0);

$From = '';
if (isset($_GET['From'])) {
	if ($_GET['From'] === 'HRPerformanceAppraisals') {
		$From = 'HRPerformanceAppraisals';
	} elseif ($_GET['From'] === 'HRMyAppraisalsAsReviewer') {
		$From = 'HRMyAppraisalsAsReviewer';
	}
} elseif (isset($_POST['From'])) {
	if ($_POST['From'] === 'HRPerformanceAppraisals') {
		$From = 'HRPerformanceAppraisals';
	} elseif ($_POST['From'] === 'HRMyAppraisalsAsReviewer') {
		$From = 'HRMyAppraisalsAsReviewer';
	}
}

if ($From === 'HRMyAppraisalsAsReviewer') {
	echo '<a class="toplink" href="' . $RootPath . '/HRMyAppraisalsAsReviewer.php">' . __('Return to Appraisals to Review') . '</a>';
} else {
	echo '<a class="toplink" href="' . $RootPath . '/HRPerformanceAppraisals.php">' . __('Return to Appraisals List') . '</a>';
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
	if (!isset($_POST['ReviewerID']) OR $_POST['ReviewerID'] == '') {
		$InputError = 1;
		prnMsg(__('Reviewer/Manager must be selected'), 'error');
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

		/* Change status dynamically from Not Started to In Progress if criteria has rating score */
		if ($Status == 'Not Started' and isset($_POST['CriteriaRating']) and is_array($_POST['CriteriaRating'])) {
			foreach ($_POST['CriteriaRating'] as $cid => $rating) {
				if ($rating !== '') {
					$Status = 'In Progress';
					$_POST['Status'] = 'In Progress';
					break;
				}
			}
		}

		$OverallRating     = (isset($_POST['OverallRating']) AND $_POST['OverallRating'] != '') ? (float)$_POST['OverallRating'] : '0.00';
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

			/* Process per-criterion scores submitted from the form */
			if (isset($_POST['CriteriaRating']) && is_array($_POST['CriteriaRating'])) {
				foreach ($_POST['CriteriaRating'] as $cid => $rating) {
					$comments = isset($_POST['CriteriaComments'][$cid]) ? $_POST['CriteriaComments'][$cid] : '';
					SaveCriteriaScoreAdvanced($AppraisalID, (int)$cid, $rating === '' ? null : (int)$rating, $comments);
				}
				$calc = CalculateWeightedScoreForAppraisal($AppraisalID, 0);
				$SQL = "UPDATE hrperfappraisals SET calculatedoverallrating = " . (isset($calc['weightedscore']) && $calc['weightedscore'] !== null ? (float)$calc['weightedscore'] : '0.00') . " WHERE appraisalid = " . $AppraisalID;
				DB_query($SQL);
				if (isset($_POST['UseCalculatedRating']) && $_POST['UseCalculatedRating']) {
					$SQL = "UPDATE hrperfappraisals SET overallrating = " . (isset($calc['weightedscore']) && $calc['weightedscore'] !== null ? (float)$calc['weightedscore'] : '0.00') . " WHERE appraisalid = " . $AppraisalID;
					DB_query($SQL);
				}
			}

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
					comments
				) VALUES (
					" . $EmployeeID . ",
					'" . $ReviewPeriodStart . "',
					'" . $ReviewPeriodEnd . "',
					'" . $DueDate . "',
					" . $ReviewerID . ",
					'" . $Status . "',
					" . $OverallRating . ",
					'" . $Comments . "'
				)";
			DB_query($SQL, __('Failed to create appraisal'));
			$AppraisalID = DB_Last_Insert_ID('hrperfappraisals', 'appraisalid');

			/* Process per-criterion scores submitted from the form for the newly created appraisal */
			if (isset($_POST['CriteriaRating']) && is_array($_POST['CriteriaRating'])) {
				foreach ($_POST['CriteriaRating'] as $cid => $rating) {
					$comments = isset($_POST['CriteriaComments'][$cid]) ? $_POST['CriteriaComments'][$cid] : '';
					SaveCriteriaScoreAdvanced($AppraisalID, (int)$cid, $rating === '' ? null : (int)$rating, $comments);
				}
				$calc = CalculateWeightedScoreForAppraisal($AppraisalID, 0);
				$SQL = "UPDATE hrperfappraisals SET calculatedoverallrating = " . (isset($calc['weightedscore']) && $calc['weightedscore'] !== null ? (float)$calc['weightedscore'] : '0.00') . " WHERE appraisalid = " . $AppraisalID;
				DB_query($SQL);
				if (isset($_POST['UseCalculatedRating']) && $_POST['UseCalculatedRating']) {
					$SQL = "UPDATE hrperfappraisals SET overallrating = " . (isset($calc['weightedscore']) && $calc['weightedscore'] !== null ? (float)$calc['weightedscore'] : '0.00') . " WHERE appraisalid = " . $AppraisalID;
					DB_query($SQL);
				}
			}
			prnMsg(__('Appraisal has been created'), 'success');
			echo '<p class="centre">
					<a href="' . $RootPath . '/HRAppraisalEntry.php?AppraisalID=' . $AppraisalID . '">' . __('Continue editing this appraisal') . '</a>
				</p>';
		}
	}
}

/* Handle delete before attempting any form rendering */
if ((isset($_GET['Delete']) OR isset($_POST['Delete'])) AND isset($_GET['AppraisalID'])) {

	$AppraisalID = (int)$_GET['AppraisalID'];

	/* Delete related goal assessments and criterion scores before removing the appraisal header */
	$SQL = "DELETE FROM hrperfgoals WHERE appraisalid = " . $AppraisalID;
	DB_query($SQL);

	/* Delete per-appraisal criterion scores */
	DeleteAppraisalCriteria($AppraisalID);

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
			e.employeenumber,
			e.positionid
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
	$DbStatus            = isset($_POST['Status']) ? $_POST['Status'] : $MyRow['status'];
	$DbOverallRating     = $MyRow['overallrating'];
	$DbComments          = $MyRow['comments'];

	/* Load criteria and scores for this appraisal */
	$Criteria = GetAppraisalCriteria($AppraisalID, $MyRow['positionid']);
	$DbCriteriaScores = GetCriteriaScores($AppraisalID);
} else {
	$DbEmployeeName      = '';
	if (isset($_POST['EmployeeNumber'])) {
		$DbEmployeeNumber = $_POST['EmployeeNumber'];
	} elseif (isset($_GET['EmployeeNumber'])) {
		$DbEmployeeNumber = $_GET['EmployeeNumber'];
	} else {
		$DbEmployeeNumber = '';
	}
	$DbReviewPeriodStart = isset($_POST['ReviewPeriodStart']) ? ConvertSQLDate($_POST['ReviewPeriodStart']) : DateAdd(date($_SESSION['DefaultDateFormat']), 'y', -1);
	$DbReviewPeriodEnd   = isset($_POST['ReviewPeriodEnd']) ? ConvertSQLDate($_POST['ReviewPeriodEnd']) : DateAdd(date($_SESSION['DefaultDateFormat']), 'd', -1);
	$DbDueDate           = isset($_POST['DueDate']) ? ConvertSQLDate($_POST['DueDate']) : date($_SESSION['DefaultDateFormat']);
	$DbReviewerID        = isset($_POST['ReviewerID']) ? $_POST['ReviewerID'] : '';
	$DbStatus            = isset($_POST['Status']) ? $_POST['Status'] : 'Not Started';
	$DbOverallRating     = isset($_POST['OverallRating']) ? $_POST['OverallRating'] : '';
	$DbComments          = isset($_POST['Comments']) ? $_POST['Comments'] : '';

	/* Load criteria for new appraisal (no scores yet) */
	$PositionID = 0;
	if ($DbEmployeeNumber != '') {
		$PositionID = GetPositionIDFromEmployeeNumber((string)$DbEmployeeNumber);
	}
	$Criteria = GetAppraisalCriteria(0, $PositionID);
	$DbCriteriaScores = array();
}

/* Page title varies by mode */
if ($EditMode) {

	echo '<p class="page_title_text">
			<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('Edit Appraisal') . '" /> ' .
			__('Edit Performance Appraisal') . ' - ' . __('ID') . ': ' . $AppraisalID . '
		</p>';
	$FormAction = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8')
		. '?AppraisalID=' . $AppraisalID;

	echo '<form method="post" action="' . $FormAction . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if ($From !== '') {
		echo '<input type="hidden" name="From" value="' . htmlspecialchars($From, ENT_QUOTES, 'UTF-8') . '" />';
	}

	echo '<fieldset>
			<legend>' . __('Edit Performance Appraisal') . '</legend>';

	/* Employee is read-only on an existing appraisal -- pass number via hidden field */
	echo '<field>
			<label>' . __('Employee') . ':</label>
			<div>' . htmlspecialchars($DbEmployeeName, ENT_QUOTES, 'UTF-8') .
				' (' . htmlspecialchars(PadEmployeeNumber($DbEmployeeNumber), ENT_QUOTES, 'UTF-8') . ')</div>
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
			<select name="EmployeeNumber" required onchange="this.form.submit()">';
	echo '<option value="">' . __('Select Employee') . '</option>';
	$SQL    = "SELECT employeenumber,
				CONCAT(firstname, ' ', lastname) AS name
			FROM hremployees
			WHERE employmentstatus = 'Active'
			ORDER BY lastname, firstname";
	$Result = DB_query($SQL);
	while ($EmpRow = DB_fetch_array($Result)) {
		echo '<option value="' . htmlspecialchars($EmpRow['employeenumber'], ENT_QUOTES, 'UTF-8') . '"' .
			($DbEmployeeNumber == $EmpRow['employeenumber'] ? ' selected="selected"' : '') . '>' .
			htmlspecialchars($EmpRow['name'], ENT_QUOTES, 'UTF-8') . '</option>';
	}
	echo '</select>
		</field>';
}

/*
 * Edit appraisal section: fields pre-filled from the database.
 * Shared fields below use the $Db-prefixed variables loaded above.
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

/* Performance criteria scoring */
echo '<fieldset>
		<legend>' . __('Performance Criteria Scores') . '</legend>';

echo '<table class="selection">
		<tr>
			<th>' . __('Criteria') . '</th>
			<th>' . __('Weight') . '</th>
			<th>' . __('Rating') . '</th>
			<th>' . __('Comments') . '</th>
		</tr>';

if (!empty($Criteria)) {
	foreach ($Criteria as $cid => $c) {
		$existingRating = isset($DbCriteriaScores[$cid]) ? $DbCriteriaScores[$cid]['rating'] : '';
		$existingComments = isset($DbCriteriaScores[$cid]) ? $DbCriteriaScores[$cid]['comments'] : '';
		echo '<tr data-criteriaid="' . $cid . '" data-weight="' . $c['weight'] . '">';
		echo '<td>' . htmlspecialchars($c['criterianame'], ENT_QUOTES, 'UTF-8') . '</td>';
		echo '<td class="number">' . number_format($c['weight'], 1) . '%<input type="hidden" name="CriteriaWeight[' . $cid . ']" value="' . $c['weight'] . '" /></td>';
		echo '<td><select name="CriteriaRating[' . $cid . ']" class="criteria-rating"><option value="">' . __('Not Rated') . '</option>';
		foreach ($RatingLabels as $rv => $rl) {
			echo '<option value="' . $rv . '"' . ($existingRating == $rv ? ' selected' : '') . '>' . $rl . '</option>';
		}
		echo '</select></td>';
		echo '<td><input type="text" name="CriteriaComments[' . $cid . ']" value="' . htmlspecialchars($existingComments, ENT_QUOTES, 'UTF-8') . '" size="50" /></td>';
		echo '</tr>';
	}
} else {
	echo '<tr><td colspan="4">' . __('No active performance criteria defined. Please define criteria first.') . '</td></tr>';
}

echo '</table>';

echo '<p><strong>' . __('Calculated Weighted Score') . ':</strong> <span id="weightedScoreDisplay">N/A</span> ' . __('Mapped Rating') . ': <span id="mappedRatingDisplay">N/A</span></p>';

$useCalculatedRatingChecked = (!isset($_POST['FormID']) OR (isset($_POST['UseCalculatedRating']) AND $_POST['UseCalculatedRating'])) ? ' checked="checked"' : '';
echo '<p><label><input type="checkbox" name="UseCalculatedRating" value="1"' . $useCalculatedRatingChecked . ' /> ' . __('Use calculated rating as overall rating') . '</label></p>';

echo '</fieldset>';

if ($EditMode) {
	echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Update Appraisal') . '" />&nbsp;
		<input type="submit" name="Delete" value="' . __('Delete Appraisal') . '"
			onclick="return confirm(\'' . __('Are you sure you want to delete this appraisal?') . '\');" />
	</div>';
} else {
	echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Create Appraisal') . '" />
	</div>';
}
echo '</form>';
	echo '<script src="' . $RootPath . '/javascripts/HRAppraisalScoring.js"></script>';

include(__DIR__ . '/includes/footer.php');
