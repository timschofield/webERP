<?php

/*
 * Colleague Feedback Entry/Edit
 *
 * (no params)       -- blank form to create a new feedback record
 * FeedbackID=N      -- edit form pre-filled from the database
 * FeedbackID=N&Delete=1 -- delete the feedback and redirect
 */

require(__DIR__ . '/includes/session.php');

$Title = __('Colleague Feedback Entry');
$ViewTopic = 'HumanResources';
$BookMark = 'HRColleagueFeedbackEntry';

include(__DIR__ . '/includes/header.php');
require_once(__DIR__ . '/includes/HRPerformanceHelper.php');

$RatingLabels = GetRatingLabels();
$EditMode = (isset($_GET['FeedbackID']) && (int)$_GET['FeedbackID'] > 0);

$From = '';
if (isset($_GET['From']) && $_GET['From'] === 'HRColleagueFeedback') {
	$From = 'HRColleagueFeedback';
	
} elseif (isset($_GET['From']) && $_GET['From'] === 'HRMyColleagueFeedbacks') {
	$From = 'HRMyColleagueFeedbacks';

} elseif (isset($_GET['From']) && $_GET['From'] === 'HRColleagueFeedbackDue') {
	$From = 'HRColleagueFeedbackDue';
} elseif (isset($_POST['From']) && $_POST['From'] === 'HRColleagueFeedback') {
	$From = 'HRColleagueFeedback';

} elseif (isset($_POST['From']) && $_POST['From'] === 'HRMyColleagueFeedbacks') {
	$From = 'HRMyColleagueFeedbacks';

} elseif (isset($_POST['From']) && $_POST['From'] === 'HRColleagueFeedbackDue') {
	$From = 'HRColleagueFeedbackDue';
}

if ($From === 'HRMyColleagueFeedbacks') {
	echo '<a class="toplink" href="' . $RootPath . '/HRMyColleagueFeedbacks.php">' . __('Return to My Colleague Feedbacks') . '</a>';

} elseif ($From === 'HRColleagueFeedbackDue') {
	echo '<a class="toplink" href="' . $RootPath . '/HRColleagueFeedbackDue.php">' . __('Return to Colleague Feedback Due') . '</a>';
} else {
	echo '<a class="toplink" href="' . $RootPath . '/HRColleagueFeedback.php">' . __('Return to Colleague Feedback List') . '</a>';
}

if (isset($_POST['Submit'])) {
	$InputError = 0;
	$CreatedByID = 0;

	if (!isset($_POST['FromEmployeeID']) || (int)$_POST['FromEmployeeID'] <= 0) {
		$InputError = 1;
		prnMsg(__('From employee must be selected'), 'error');
	}
	if (!isset($_POST['AboutEmployeeID']) || (int)$_POST['AboutEmployeeID'] <= 0) {
		$InputError = 1;
		prnMsg(__('About employee must be selected'), 'error');
	}
	if (isset($_POST['FromEmployeeID'], $_POST['AboutEmployeeID']) && (int)$_POST['FromEmployeeID'] === (int)$_POST['AboutEmployeeID']) {
		$InputError = 1;
		prnMsg(__('From employee and about employee must be different'), 'error');
	}
	if (!isset($_POST['FeedbackPeriodStart']) || $_POST['FeedbackPeriodStart'] == '') {
		$InputError = 1;
		prnMsg(__('Feedback period start date is required'), 'error');
	}
	if (!isset($_POST['FeedbackPeriodEnd']) || $_POST['FeedbackPeriodEnd'] == '') {
		$InputError = 1;
		prnMsg(__('Feedback period end date is required'), 'error');
	}
	if (!isset($_POST['FeedbackType']) || $_POST['FeedbackType'] == '') {
		$InputError = 1;
		prnMsg(__('Feedback type is required'), 'error');
	}
	if (!isset($_POST['Status']) || $_POST['Status'] == '') {
		$InputError = 1;
		prnMsg(__('Status is required'), 'error');
	}

	$AllowedTypes = array('Annual', 'Mid-Year', 'Probation', '90-Day', 'Project');
	$AllowedStatuses = array('Not Started', 'In Progress', 'Completed', 'Cancelled');

	$FeedbackType = isset($_POST['FeedbackType']) ? $_POST['FeedbackType'] : '';
	$Status = isset($_POST['Status']) ? $_POST['Status'] : '';

	if (!in_array($FeedbackType, $AllowedTypes)) {
		$InputError = 1;
		prnMsg(__('Invalid feedback type selected'), 'error');
	}
	if (!in_array($Status, $AllowedStatuses)) {
		$InputError = 1;
		prnMsg(__('Invalid status selected'), 'error');
	}

	if ($InputError == 0) {
		$CreatedByID = GetEmployeeIDFromUserID((string)$_SESSION['UserID']);
		if ($CreatedByID <= 0) {
			$InputError = 1;
			prnMsg(__('Your webERP user account is not linked to an employee record'), 'error');
		}
	}

	if ($InputError == 0) {
		$FromEmployeeID = (int)$_POST['FromEmployeeID'];
		$AboutEmployeeID = (int)$_POST['AboutEmployeeID'];
		$FeedbackPeriodStart = FormatDateForSQL($_POST['FeedbackPeriodStart']);
		$FeedbackPeriodEnd = FormatDateForSQL($_POST['FeedbackPeriodEnd']);
		$DueDate = (isset($_POST['DueDate']) && $_POST['DueDate'] != '') ? "'" . FormatDateForSQL($_POST['DueDate']) . "'" : 'NULL';
		$CompletionDate = (isset($_POST['CompletionDate']) && $_POST['CompletionDate'] != '') ? "'" . FormatDateForSQL($_POST['CompletionDate']) . "'" : 'NULL';

		/* Change status dynamically from Not Started to In Progress if criteria has rating score */
		if ($Status == 'Not Started' && isset($_POST['CriteriaRating']) && is_array($_POST['CriteriaRating'])) {
			foreach ($_POST['CriteriaRating'] as $rating) {
				if ($rating !== '') {
					$Status = 'In Progress';
					$_POST['Status'] = 'In Progress';
					break;
				}
			}
		}

		$OverallRating = (isset($_POST['OverallRating']) && $_POST['OverallRating'] !== '') ? (int)$_POST['OverallRating'] : 'NULL';
		$Comments = isset($_POST['Comments']) ? DB_escape_string($_POST['Comments']) : '';

		if ($EditMode) {
			$FeedbackID = (int)$_GET['FeedbackID'];
			$SQL = "UPDATE hrcolleaguefeedback SET
					fromemployeeid = " . $FromEmployeeID . ",
					aboutemployeeid = " . $AboutEmployeeID . ",
					feedbackperiodstart = '" . $FeedbackPeriodStart . "',
					feedbackperiodend = '" . $FeedbackPeriodEnd . "',
					feedbacktype = '" . DB_escape_string($FeedbackType) . "',
					overallrating = " . $OverallRating . ",
					status = '" . DB_escape_string($Status) . "',
					duedate = " . $DueDate . ",
					completiondate = " . $CompletionDate . ",
					comments = '" . $Comments . "',
					modifieddate = NOW()
				WHERE feedbackid = " . $FeedbackID;
			DB_query($SQL, __('Failed to update colleague feedback'));

			if (isset($_POST['CriteriaRating']) && is_array($_POST['CriteriaRating'])) {
				foreach ($_POST['CriteriaRating'] as $cid => $rating) {
					$criteriaComments = isset($_POST['CriteriaComments'][$cid]) ? $_POST['CriteriaComments'][$cid] : '';
					SaveFeedbackCriteriaScoreAdvanced($FeedbackID, (int)$cid, $rating === '' ? null : (int)$rating, $criteriaComments);
				}

				$calc = CalculateWeightedScoreForFeedback($FeedbackID);
				if (isset($_POST['UseCalculatedRating']) && $_POST['UseCalculatedRating']) {
					$SQL = "UPDATE hrcolleaguefeedback
							SET overallrating = " . (isset($calc['mappedrating']) && $calc['mappedrating'] !== null ? (int)$calc['mappedrating'] : 'NULL') . "
							WHERE feedbackid = " . $FeedbackID;
					DB_query($SQL);
				}
			}

			prnMsg(__('Colleague feedback has been updated'), 'success');
		} else {
			$SQL = "INSERT INTO hrcolleaguefeedback (
					fromemployeeid,
					aboutemployeeid,
					createdbyid,
					feedbackperiodstart,
					feedbackperiodend,
					feedbacktype,
					overallrating,
					status,
					duedate,
					completiondate,
					comments
				) VALUES (
					" . $FromEmployeeID . ",
					" . $AboutEmployeeID . ",
					" . (int)$CreatedByID . ",
					'" . $FeedbackPeriodStart . "',
					'" . $FeedbackPeriodEnd . "',
					'" . DB_escape_string($FeedbackType) . "',
					" . $OverallRating . ",
					'" . DB_escape_string($Status) . "',
					" . $DueDate . ",
					" . $CompletionDate . ",
					'" . $Comments . "'
				)";
			DB_query($SQL, __('Failed to create colleague feedback'));
			$FeedbackID = DB_Last_Insert_ID('hrcolleaguefeedback', 'feedbackid');

			if (isset($_POST['CriteriaRating']) && is_array($_POST['CriteriaRating'])) {
				foreach ($_POST['CriteriaRating'] as $cid => $rating) {
					$criteriaComments = isset($_POST['CriteriaComments'][$cid]) ? $_POST['CriteriaComments'][$cid] : '';
					SaveFeedbackCriteriaScoreAdvanced($FeedbackID, (int)$cid, $rating === '' ? null : (int)$rating, $criteriaComments);
				}

				$calc = CalculateWeightedScoreForFeedback($FeedbackID);
				if (isset($_POST['UseCalculatedRating']) && $_POST['UseCalculatedRating']) {
					$SQL = "UPDATE hrcolleaguefeedback
							SET overallrating = " . (isset($calc['mappedrating']) && $calc['mappedrating'] !== null ? (int)$calc['mappedrating'] : 'NULL') . "
							WHERE feedbackid = " . $FeedbackID;
					DB_query($SQL);
				}
			}

			prnMsg(__('Colleague feedback has been created'), 'success');
			echo '<p class="centre">
					<a href="' . $RootPath . '/HRColleagueFeedbackEntry.php?FeedbackID=' . $FeedbackID . '">' . __('Continue editing this feedback') . '</a>
				</p>';
		}
	}
}

if ((isset($_GET['Delete']) || isset($_POST['Delete'])) && isset($_GET['FeedbackID'])) {
	$FeedbackID = (int)$_GET['FeedbackID'];

	DeleteFeedbackCriteria($FeedbackID);

	$SQL = "DELETE FROM hrcolleaguefeedback WHERE feedbackid = " . $FeedbackID;
	DB_query($SQL);

	prnMsg(__('Colleague feedback has been deleted'), 'success');
	echo '<p class="centre">
			<a href="' . $RootPath . '/HRColleagueFeedback.php">' . __('Return to colleague feedback list') . '</a>
		</p>';
	include(__DIR__ . '/includes/footer.php');
	exit;
}

if (isset($_GET['FeedbackID'])) {
	$FeedbackID = (int)$_GET['FeedbackID'];

	$SQL = "SELECT f.*,
			CONCAT(fe.firstname, ' ', fe.lastname) AS fromemployeename,
			fe.employeenumber AS fromemployeenumber,
			CONCAT(ae.firstname, ' ', ae.lastname) AS aboutemployeename,
			ae.employeenumber AS aboutemployeenumber,
			CONCAT(ce.firstname, ' ', ce.lastname) AS createdbyname
		FROM hrcolleaguefeedback f
		INNER JOIN hremployees fe ON f.fromemployeeid = fe.employeeid
		INNER JOIN hremployees ae ON f.aboutemployeeid = ae.employeeid
		LEFT JOIN hremployees ce ON f.createdbyid = ce.employeeid
		WHERE f.feedbackid = " . $FeedbackID;
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		prnMsg(__('Colleague feedback not found'), 'error');
		include(__DIR__ . '/includes/footer.php');
		exit;
	}

	$MyRow = DB_fetch_array($Result);
	$DbFromEmployeeID = $MyRow['fromemployeeid'];
	$DbAboutEmployeeID = $MyRow['aboutemployeeid'];
	$DbFromEmployeeName = $MyRow['fromemployeename'];
	$DbFromEmployeeNumber = $MyRow['fromemployeenumber'];
	$DbAboutEmployeeName = $MyRow['aboutemployeename'];
	$DbAboutEmployeeNumber = $MyRow['aboutemployeenumber'];
	$DbCreatedByName = $MyRow['createdbyname'];
	$DbFeedbackPeriodStart = ConvertSQLDate($MyRow['feedbackperiodstart']);
	$DbFeedbackPeriodEnd = ConvertSQLDate($MyRow['feedbackperiodend']);
	$DbFeedbackType = isset($_POST['FeedbackType']) ? $_POST['FeedbackType'] : $MyRow['feedbacktype'];
	$DbOverallRating = isset($_POST['OverallRating']) ? $_POST['OverallRating'] : $MyRow['overallrating'];
	$DbStatus = isset($_POST['Status']) ? $_POST['Status'] : $MyRow['status'];
	$DbDueDate = $MyRow['duedate'] ? ConvertSQLDate($MyRow['duedate']) : '';
	$DbCompletionDate = $MyRow['completiondate'] ? ConvertSQLDate($MyRow['completiondate']) : '';
	$DbComments = isset($_POST['Comments']) ? $_POST['Comments'] : $MyRow['comments'];

	$Criteria = GetFeedbackCriteria();
	$DbCriteriaScores = GetFeedbackCriteriaScores($FeedbackID);
} else {
	if (isset($_POST['FromEmployeeID'])) {
		$DbFromEmployeeID = (int)$_POST['FromEmployeeID'];
	} elseif (isset($_GET['FromEmployeeID'])) {
		$DbFromEmployeeID = (int)$_GET['FromEmployeeID'];
	} else {
		$DbFromEmployeeID = 0;
	}

	if (isset($_POST['AboutEmployeeID'])) {
		$DbAboutEmployeeID = (int)$_POST['AboutEmployeeID'];
	} elseif (isset($_GET['AboutEmployeeID'])) {
		$DbAboutEmployeeID = (int)$_GET['AboutEmployeeID'];
	} else {
		$DbAboutEmployeeID = 0;
	}
	$DbFromEmployeeName = '';
	$DbFromEmployeeNumber = '';
	$DbAboutEmployeeName = '';
	$DbAboutEmployeeNumber = '';
	$DbCreatedByName = '';
	$DbFeedbackPeriodStart = isset($_POST['FeedbackPeriodStart']) ? ConvertSQLDate($_POST['FeedbackPeriodStart']) : DateAdd(date($_SESSION['DefaultDateFormat']), 'y', -1);
	$DbFeedbackPeriodEnd = isset($_POST['FeedbackPeriodEnd']) ? ConvertSQLDate($_POST['FeedbackPeriodEnd']) : DateAdd(date($_SESSION['DefaultDateFormat']), 'd', -1);
	$DbFeedbackType = isset($_POST['FeedbackType']) ? $_POST['FeedbackType'] : 'Annual';
	$DbOverallRating = isset($_POST['OverallRating']) ? $_POST['OverallRating'] : '';
	$DbStatus = isset($_POST['Status']) ? $_POST['Status'] : 'Not Started';
	$DbDueDate = isset($_POST['DueDate']) ? ConvertSQLDate($_POST['DueDate']) : date($_SESSION['DefaultDateFormat']);
	$DbCompletionDate = isset($_POST['CompletionDate']) ? ConvertSQLDate($_POST['CompletionDate']) : '';
	$DbComments = isset($_POST['Comments']) ? $_POST['Comments'] : '';

	$Criteria = GetFeedbackCriteria();
	$DbCriteriaScores = array();
}

if ($EditMode) {
	echo '<p class="page_title_text">
			<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('Edit Feedback') . '" /> ' .
			__('Edit Colleague Feedback') . ' - ' . __('ID') . ': ' . $FeedbackID . '
		</p>';
	$FormAction = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?FeedbackID=' . $FeedbackID;
} else {
	echo '<p class="page_title_text">
			<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('New Feedback') . '" /> ' .
			__('Create New Colleague Feedback') . '
		</p>';
	$FormAction = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
}

echo '<form method="post" action="' . $FormAction . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if ($From !== '') {
	echo '<input type="hidden" name="From" value="' . htmlspecialchars($From, ENT_QUOTES, 'UTF-8') . '" />';
}

echo '<fieldset>
		<legend>' . ($EditMode ? __('Edit Colleague Feedback') : __('New Colleague Feedback')) . '</legend>';

if ($EditMode) {
	echo '<field>
			<label>' . __('From Employee') . ':</label>
			<div>' . htmlspecialchars($DbFromEmployeeName, ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars(PadEmployeeNumber((string)$DbFromEmployeeNumber), ENT_QUOTES, 'UTF-8') . ')</div>
			<input type="hidden" name="FromEmployeeID" value="' . (int)$DbFromEmployeeID . '" />
		</field>';
	echo '<field>
			<label>' . __('About Employee') . ':</label>
			<div>' . htmlspecialchars($DbAboutEmployeeName, ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars(PadEmployeeNumber((string)$DbAboutEmployeeNumber), ENT_QUOTES, 'UTF-8') . ')</div>
			<input type="hidden" name="AboutEmployeeID" value="' . (int)$DbAboutEmployeeID . '" />
		</field>';
	echo '<field>
			<label>' . __('Created By') . ':</label>
			<div>' . htmlspecialchars((string)$DbCreatedByName, ENT_QUOTES, 'UTF-8') . '</div>
		</field>';
} else {
	echo '<field>
			<label for="FromEmployeeID">' . __('From Employee') . ':</label>
			<select name="FromEmployeeID" required="required">';
	echo '<option value="0">' . __('Select Employee') . '</option>';
	$SQL = "SELECT employeeid, employeenumber, CONCAT(firstname, ' ', lastname) AS name
			FROM hremployees
			WHERE employmentstatus = 'Active'
			ORDER BY firstname, lastname";
	$Result = DB_query($SQL);
	while ($EmpRow = DB_fetch_array($Result)) {
		echo '<option value="' . (int)$EmpRow['employeeid'] . '"' . ($DbFromEmployeeID == $EmpRow['employeeid'] ? ' selected="selected"' : '') . '>' . htmlspecialchars($EmpRow['name'], ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars(PadEmployeeNumber((string)$EmpRow['employeenumber']), ENT_QUOTES, 'UTF-8') . ')</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="AboutEmployeeID">' . __('About Employee') . ':</label>
			<select name="AboutEmployeeID" required="required">';
	echo '<option value="0">' . __('Select Employee') . '</option>';
	$SQL = "SELECT employeeid, employeenumber, CONCAT(firstname, ' ', lastname) AS name
			FROM hremployees
			WHERE employmentstatus = 'Active'
			ORDER BY firstname, lastname";
	$Result = DB_query($SQL);
	while ($EmpRow = DB_fetch_array($Result)) {
		echo '<option value="' . (int)$EmpRow['employeeid'] . '"' . ($DbAboutEmployeeID == $EmpRow['employeeid'] ? ' selected="selected"' : '') . '>' . htmlspecialchars($EmpRow['name'], ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars(PadEmployeeNumber((string)$EmpRow['employeenumber']), ENT_QUOTES, 'UTF-8') . ')</option>';
	}
	echo '</select>
		</field>';
}

echo '<field>
		<label for="FeedbackPeriodStart">' . __('Feedback Period Start') . ':</label>
		<input type="date" name="FeedbackPeriodStart" required="required" value="' . FormatDateForSQL($DbFeedbackPeriodStart) . '" />
	</field>
	<field>
		<label for="FeedbackPeriodEnd">' . __('Feedback Period End') . ':</label>
		<input type="date" name="FeedbackPeriodEnd" required="required" value="' . FormatDateForSQL($DbFeedbackPeriodEnd) . '" />
	</field>
	<field>
		<label for="FeedbackType">' . __('Feedback Type') . ':</label>
		<select name="FeedbackType" required="required">
			<option value="Annual"' . ($DbFeedbackType == 'Annual' ? ' selected="selected"' : '') . '>' . __('Annual') . '</option>
			<option value="Mid-Year"' . ($DbFeedbackType == 'Mid-Year' ? ' selected="selected"' : '') . '>' . __('Mid-Year') . '</option>
			<option value="Probation"' . ($DbFeedbackType == 'Probation' ? ' selected="selected"' : '') . '>' . __('Probation') . '</option>
			<option value="90-Day"' . ($DbFeedbackType == '90-Day' ? ' selected="selected"' : '') . '>' . __('90-Day') . '</option>
			<option value="Project"' . ($DbFeedbackType == 'Project' ? ' selected="selected"' : '') . '>' . __('Project') . '</option>
		</select>
	</field>
	<field>
		<label for="DueDate">' . __('Due Date') . ':</label>
		<input type="date" name="DueDate" value="' . ($DbDueDate !== '' ? FormatDateForSQL($DbDueDate) : '') . '" />
	</field>
	<field>
		<label for="CompletionDate">' . __('Completion Date') . ':</label>
		<input type="date" name="CompletionDate" value="' . ($DbCompletionDate !== '' ? FormatDateForSQL($DbCompletionDate) : '') . '" />
	</field>';

echo '<field>
		<label for="Status">' . __('Status') . ':</label>
		<select name="Status" required="required">
			<option value="Not Started"' . ($DbStatus == 'Not Started' ? ' selected="selected"' : '') . '>' . __('Not Started') . '</option>
			<option value="In Progress"' . ($DbStatus == 'In Progress' ? ' selected="selected"' : '') . '>' . __('In Progress') . '</option>
			<option value="Completed"' . ($DbStatus == 'Completed' ? ' selected="selected"' : '') . '>' . __('Completed') . '</option>
			<option value="Cancelled"' . ($DbStatus == 'Cancelled' ? ' selected="selected"' : '') . '>' . __('Cancelled') . '</option>
		</select>
	</field>';

echo '<field>
		<label for="OverallRating">' . __('Overall Rating') . ':</label>
		<select name="OverallRating">
			<option value="">' . __('Not Rated') . '</option>';
foreach ($RatingLabels as $RatingValue => $RatingLabel) {
	echo '<option value="' . $RatingValue . '"' . ($DbOverallRating == $RatingValue ? ' selected="selected"' : '') . '>' . $RatingLabel . '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="Comments">' . __('Comments') . ':</label>
		<textarea name="Comments" rows="5" cols="60">' . htmlspecialchars((string)$DbComments, ENT_QUOTES, 'UTF-8') . '</textarea>
	</field>';

echo '</fieldset>';

echo '<fieldset>
		<legend>' . __('Feedback Criteria Scores') . '</legend>';

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
	echo '<tr><td colspan="4">' . __('No active feedback criteria defined. Please define criteria first.') . '</td></tr>';
}

echo '</table>';

echo '<p><strong>' . __('Calculated Weighted Score') . ':</strong> <span id="weightedScoreDisplay">N/A</span> ' . __('Mapped Rating') . ': <span id="mappedRatingDisplay">N/A</span></p>';

$useCalculatedRatingChecked = (!isset($_POST['FormID']) || (isset($_POST['UseCalculatedRating']) && $_POST['UseCalculatedRating'])) ? ' checked="checked"' : '';
echo '<p><label><input type="checkbox" name="UseCalculatedRating" value="1"' . $useCalculatedRatingChecked . ' /> ' . __('Use calculated rating as overall rating') . '</label></p>';

echo '</fieldset>';

if ($EditMode) {
	echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Update Feedback') . '" />&nbsp;
		<input type="submit" name="Delete" value="' . __('Delete Feedback') . '"
			onclick="return confirm(\'' . __('Are you sure you want to delete this feedback?') . '\');" />
	</div>';
} else {
	echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Create Feedback') . '" />
	</div>';
}

echo '</form>';
echo '<script src="' . $RootPath . '/javascripts/HRAppraisalScoring.js"></script>';

include(__DIR__ . '/includes/footer.php');

?>
