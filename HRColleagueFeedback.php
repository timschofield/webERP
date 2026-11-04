<?php

/* Colleague Feedback Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Colleague Feedback');
$ViewTopic = 'HumanResources';
$BookMark = 'HRColleagueFeedback';

include(__DIR__ . '/includes/header.php');
require_once(__DIR__ . '/includes/HRPerformanceHelper.php');
require_once(__DIR__ . '/includes/HRGeneralFunctions.php');

$RatingLabels = GetRatingLabels();

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('Colleague Feedback') . '" /> ' .
		__('Colleague Feedback Management') . '
	</p>';

/* Search and filter form */
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Search & Filter') . '</legend>
		<field>
			<label for="Keywords">' . __('Employee Name or Number') . ':</label>
			<input type="text" name="Keywords" size="20" maxlength="50" value="' . (isset($_POST['Keywords']) ? htmlspecialchars($_POST['Keywords'], ENT_QUOTES, 'UTF-8') : '') . '" />
		</field>';

echo '<field>
			<label for="Status">' . __('Status') . ':</label>
			<select name="Status">
				<option value="">' . __('All Statuses') . '</option>
				<option value="Not Started"' . (isset($_POST['Status']) and $_POST['Status'] == 'Not Started' ? ' selected="selected"' : '') . '>' . __('Not Started') . '</option>
				<option value="In Progress"' . (isset($_POST['Status']) and $_POST['Status'] == 'In Progress' ? ' selected="selected"' : '') . '>' . __('In Progress') . '</option>
				<option value="Completed"' . (isset($_POST['Status']) and $_POST['Status'] == 'Completed' ? ' selected="selected"' : '') . '>' . __('Completed') . '</option>
				<option value="Cancelled"' . (isset($_POST['Status']) and $_POST['Status'] == 'Cancelled' ? ' selected="selected"' : '') . '>' . __('Cancelled') . '</option>
			</select>
		</field>';

echo '<field>
			<label for="FeedbackType">' . __('Feedback Type') . ':</label>
			<select name="FeedbackType">
				<option value="">' . __('All Types') . '</option>
				<option value="Annual"' . (isset($_POST['FeedbackType']) and $_POST['FeedbackType'] == 'Annual' ? ' selected="selected"' : '') . '>' . __('Annual') . '</option>
				<option value="Mid-Year"' . (isset($_POST['FeedbackType']) and $_POST['FeedbackType'] == 'Mid-Year' ? ' selected="selected"' : '') . '>' . __('Mid-Year') . '</option>
				<option value="Probation"' . (isset($_POST['FeedbackType']) and $_POST['FeedbackType'] == 'Probation' ? ' selected="selected"' : '') . '>' . __('Probation') . '</option>
				<option value="90-Day"' . (isset($_POST['FeedbackType']) and $_POST['FeedbackType'] == '90-Day' ? ' selected="selected"' : '') . '>' . __('90-Day') . '</option>
				<option value="Project"' . (isset($_POST['FeedbackType']) and $_POST['FeedbackType'] == 'Project' ? ' selected="selected"' : '') . '>' . __('Project') . '</option>
			</select>
		</field>';

echo '<field>
			<label for="DepartmentID">' . __('Department') . ':</label>
			<select name="DepartmentID">
				<option value="">' . __('All Departments') . '</option>';
$DepartmentResult = DB_query("SELECT departmentid, description FROM departments ORDER BY description");
while ($DepartmentRow = DB_fetch_array($DepartmentResult)) {
	echo '<option value="' . $DepartmentRow['departmentid'] . '"' .
		(isset($_POST['DepartmentID']) and $_POST['DepartmentID'] == $DepartmentRow['departmentid'] ? ' selected="selected"' : '') . '>' .
		htmlspecialchars($DepartmentRow['description'], ENT_QUOTES, 'UTF-8') . '</option>';
}
echo '</select>
		</field>';

echo '<field>
			<label for="PeriodYear">' . __('Feedback Period Starts on Year') . ':</label>
			<input type="number" name="PeriodYear" size="4" maxlength="4" value="' . (isset($_POST['PeriodYear']) ? (int)$_POST['PeriodYear'] : (int)date('Y')) . '" />
		</field>';

echo '</fieldset>';

echo '<div class="centre">
			<input type="submit" name="Search" value="' . __('Search') . '" />
			<input type="submit" name="CreateNew" value="' . __('Create New Feedback') . '" onclick="window.location=\'' . $RootPath . '/HRColleagueFeedbackEntry.php\'; return false;" />
		</div>';

echo '</form>';

$SQL = "SELECT
		f.feedbackid,
		f.feedbackperiodstart,
		f.feedbackperiodend,
		f.feedbacktype,
		f.overallrating,
		f.status,
		f.duedate,
		f.completiondate,
		f.comments,
		CONCAT(fe.firstname, ' ', fe.lastname) AS fromname,
		fe.employeenumber AS fromemployeenumber,
		CONCAT(ae.firstname, ' ', ae.lastname) AS aboutname,
		ae.employeenumber AS aboutemployeenumber,
		CONCAT(ce.firstname, ' ', ce.lastname) AS createdbyname
	FROM hrcolleaguefeedback f
	INNER JOIN hremployees fe ON f.fromemployeeid = fe.employeeid
	INNER JOIN hremployees ae ON f.aboutemployeeid = ae.employeeid
	LEFT JOIN hremployees ce ON f.createdbyid = ce.employeeid
	WHERE 1=1";

if (isset($_POST['Keywords']) and trim($_POST['Keywords']) != '') {
	$Keywords = DB_escape_string(trim($_POST['Keywords']));
	$SQL .= " AND (
			fe.firstname LIKE '%" . $Keywords . "%'
			OR fe.lastname LIKE '%" . $Keywords . "%'
			OR fe.employeenumber LIKE '%" . $Keywords . "%'
			OR ae.firstname LIKE '%" . $Keywords . "%'
			OR ae.lastname LIKE '%" . $Keywords . "%'
			OR ae.employeenumber LIKE '%" . $Keywords . "%'
		)";
}

if (isset($_POST['Status']) and $_POST['Status'] != '') {
	$SQL .= " AND f.status = '" . DB_escape_string($_POST['Status']) . "'";
}

if (isset($_POST['FeedbackType']) and $_POST['FeedbackType'] != '') {
	$SQL .= " AND f.feedbacktype = '" . DB_escape_string($_POST['FeedbackType']) . "'";
}

if (isset($_POST['DepartmentID']) and $_POST['DepartmentID'] != '') {
	/* Employee the feedback is about: selected department plus all its sub-departments */
	$DepartmentIdList = explode(',', GetDepartmentListBelow($_POST['DepartmentID']));
	$SQL .= " AND ae.departmentid IN ('" . implode("','", array_map('DB_escape_string', $DepartmentIdList)) . "')";
}

if (isset($_POST['PeriodYear']) and $_POST['PeriodYear'] != '') {
	$SQL .= " AND YEAR(f.feedbackperiodstart) = " . (int)$_POST['PeriodYear'];
}

$SQL .= " ORDER BY f.duedate DESC, f.feedbackid DESC";

$Result = DB_query($SQL);

echo '<table class="selection">
		<thead>
			<tr>
				<th>' . __('Feedback ID') . '</th>
				<th>' . __('From Employee') . '</th>
				<th>' . __('About Employee') . '</th>
				<th>' . __('Feedback Period') . '</th>
				<th>' . __('Type') . '</th>
				<th>' . __('Due Date') . '</th>
				<th>' . __('Status') . '</th>
				<th>' . __('Rating') . '</th>
				<th>' . __('Created By') . '</th>
				<th>' . __('Comments') . '</th>
				<th></th>
			</tr>
		</thead>
		<tbody>';

if (DB_num_rows($Result) > 0) {
	while ($MyRow = DB_fetch_array($Result)) {
		$RowClass = '';
		if ($MyRow['status'] != 'Completed' and $MyRow['status'] != 'Cancelled' and $MyRow['duedate'] != null and $MyRow['duedate'] < date('Y-m-d')) {
			$RowClass = ' class="warn"';
		}

		$RatingLabel = '-';
		if (isset($MyRow['overallrating']) and $MyRow['overallrating'] !== null and $MyRow['overallrating'] !== '') {
			$RatingInt = (int)$MyRow['overallrating'];
			$RatingLabel = isset($RatingLabels[$RatingInt]) ? $RatingLabels[$RatingInt] : (string)$RatingInt;
		}

		echo '<tr class="striped_row"' . $RowClass . '>
				<td>' . (int)$MyRow['feedbackid'] . '</td>
				<td>' . htmlspecialchars((string)$MyRow['fromname'], ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars(PadEmployeeNumber((string)$MyRow['fromemployeenumber']), ENT_QUOTES, 'UTF-8') . ')</td>
				<td>' . htmlspecialchars((string)$MyRow['aboutname'], ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars(PadEmployeeNumber((string)$MyRow['aboutemployeenumber']), ENT_QUOTES, 'UTF-8') . ')</td>
				<td>' . ConvertSQLDate($MyRow['feedbackperiodstart']) . ' - ' . ConvertSQLDate($MyRow['feedbackperiodend']) . '</td>
				<td>' . htmlspecialchars((string)$MyRow['feedbacktype'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . ($MyRow['duedate'] ? ConvertSQLDate($MyRow['duedate']) : '-') . '</td>
				<td>' . htmlspecialchars((string)$MyRow['status'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars((string)$RatingLabel, ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars((string)$MyRow['createdbyname'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . nl2br(htmlspecialchars((string)$MyRow['comments'], ENT_QUOTES, 'UTF-8')) . '</td>
				<td class="centre">
					<a href="' . $RootPath . '/HRColleagueFeedbackEntry.php?FeedbackID=' . urlencode($MyRow['feedbackid']) . '&amp;From=HRColleagueFeedback">' . __('Edit') . '</a>
				</td>
			</tr>';
	}
} else {
	echo '<tr><td colspan="11" class="centre">' . __('No colleague feedback records found') . '</td></tr>';
}

echo '</tbody>
	</table>';

include(__DIR__ . '/includes/footer.php');

?>
