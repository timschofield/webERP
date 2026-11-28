<?php

/* Performance Appraisals Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Performance Appraisals');
$ViewTopic = 'HumanResources';
$BookMark = 'HRPerformanceAppraisals';

include(__DIR__ . '/includes/header.php');
require_once(__DIR__ . '/includes/HRPerformanceHelper.php');
require_once(__DIR__ . '/includes/HRGeneralFunctions.php');
require_once(__DIR__ . '/includes/UIGeneralFunctions.php');

/* Rating labels for hrperfappraisals.overallrating (INT 1-5) */
$RatingLabels = GetRatingLabels();

$AllowedAppraisalTypes = array('Annual', 'Mid-Year', 'Probation', '90-Day', 'Project');
$AppraisalTypeTitle = __('All Types');
if (isset($_POST['AppraisalType']) and in_array($_POST['AppraisalType'], $AllowedAppraisalTypes)) {
	$AppraisalTypeTitle = __($_POST['AppraisalType']);
}

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('Performance Appraisals') . '" /> ' .
		__('Performance Appraisals Management') . '
	</p>';

// Search and filter form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Search & Filter') . '</legend>';

$EmployeeOptions = array(
	'AllEmployees' => true,
	'Display' => 'user-name-number',
	'Order' => 'userid',
	'Placeholder' => __('All Employees'),
);
echo FieldToSelectOneEmployee(
	'EmployeeID', $_POST['EmployeeID'] ?? null, __('Employee'), Required: false, Options: $EmployeeOptions
);
echo FieldToSelectOneEmployee(
	'ManagerID', $_POST['ManagerID'] ?? null, __('Manager'), Required: false, Options: $EmployeeOptions
);

echo '<field>
			<label for="Status">' . __('Status') . ':</label>
			<select name="Status">
				<option value="">' . __('All Statuses') . '</option>
				<option value="Not Started"' . (isset($_POST['Status']) and $_POST['Status'] == 'Not Started' ? ' selected' : '') . '>' . __('Not Started') . '</option>
				<option value="In Progress"' . (isset($_POST['Status']) and $_POST['Status'] == 'In Progress' ? ' selected' : '') . '>' . __('In Progress') . '</option>
				<option value="Completed"' . (isset($_POST['Status']) and $_POST['Status'] == 'Completed' ? ' selected' : '') . '>' . __('Completed') . '</option>
				<option value="Cancelled"' . (isset($_POST['Status']) and $_POST['Status'] == 'Cancelled' ? ' selected' : '') . '>' . __('Cancelled') . '</option>
			</select>
		</field>';

echo '<field>
			<label for="AppraisalType">' . __('Appraisal Type') . ':</label>
			<select name="AppraisalType">
				<option value="">' . __('All Types') . '</option>
				<option value="Annual"' . (isset($_POST['AppraisalType']) and $_POST['AppraisalType'] == 'Annual' ? ' selected' : '') . '>' . __('Annual') . '</option>
				<option value="Mid-Year"' . (isset($_POST['AppraisalType']) and $_POST['AppraisalType'] == 'Mid-Year' ? ' selected' : '') . '>' . __('Mid-Year') . '</option>
				<option value="Probation"' . (isset($_POST['AppraisalType']) and $_POST['AppraisalType'] == 'Probation' ? ' selected' : '') . '>' . __('Probation') . '</option>
				<option value="90-Day"' . (isset($_POST['AppraisalType']) and $_POST['AppraisalType'] == '90-Day' ? ' selected' : '') . '>' . __('90-Day') . '</option>
				<option value="Project"' . (isset($_POST['AppraisalType']) and $_POST['AppraisalType'] == 'Project' ? ' selected' : '') . '>' . __('Project') . '</option>
			</select>
		</field>';

echo '<field>
			<label for="DepartmentID">' . __('Department') . ':</label>
			<select name="DepartmentID">
				<option value="">' . __('All Departments') . '</option>';
$DepartmentResult = DB_query("SELECT departmentid, description FROM departments ORDER BY description");
while ($DepartmentRow = DB_fetch_array($DepartmentResult)) {
	echo '<option value="' . $DepartmentRow['departmentid'] . '"' .
		(isset($_POST['DepartmentID']) and $_POST['DepartmentID'] == $DepartmentRow['departmentid'] ? ' selected' : '') . '>' .
		htmlspecialchars($DepartmentRow['description'], ENT_QUOTES, 'UTF-8') . '</option>';
}
echo '</select>
		</field>';

echo '<field>
			<label for="PeriodYear">' . __('Review Period Starts on Year') . ':</label>
			<input type="number" name="PeriodYear" size="4" maxlength="4" value="' . (isset($_POST['PeriodYear']) ? $_POST['PeriodYear'] : date('Y')) . '" />
		</field>';

echo '</fieldset>';

echo '<div class="centre">
			<input type="submit" name="Search" value="' . __('Search') . '" />
			<input type="submit" name="CreateNew" value="' . __('Create New Appraisal') . '" onclick="window.location=\'' . $RootPath . '/HRAppraisalEntry.php\'; return false;" />
		</div>';

echo '</form>';

// Build query
$WhereSQL = " WHERE 1=1";

if (isset($_POST['EmployeeID']) and $_POST['EmployeeID'] != '') {
	$WhereSQL .= " AND a.employeeid = '" . (int)$_POST['EmployeeID'] . "'";
}

if (isset($_POST['ManagerID']) and $_POST['ManagerID'] != '') {
	$WhereSQL .= " AND a.reviewerid = '" . (int)$_POST['ManagerID'] . "'";
}

if (isset($_POST['Status']) and $_POST['Status'] != '') {
	$Status = $_POST['Status'];
	$WhereSQL .= " AND a.status = '" . $Status . "'";
}

if (isset($_POST['AppraisalType']) and $_POST['AppraisalType'] != '') {
	$WhereSQL .= " AND a.appraisaltype = '" . DB_escape_string($_POST['AppraisalType']) . "'";
}

if (isset($_POST['DepartmentID']) and $_POST['DepartmentID'] != '') {
	/* Selected department plus all its sub-departments */
	$DepartmentIdList = explode(',', GetDepartmentListBelow($_POST['DepartmentID']));
	$WhereSQL .= " AND e.departmentid IN ('" . implode("','", array_map('DB_escape_string', $DepartmentIdList)) . "')";
}

if (isset($_POST['PeriodYear']) and $_POST['PeriodYear'] != '') {
	$Year = $_POST['PeriodYear'];
	$WhereSQL .= " AND YEAR(a.reviewperiodstart) = '" . $Year . "'";
}

$CountSQL = "SELECT COUNT(*)
	FROM hrperfappraisals a
	INNER JOIN hremployees e ON a.employeeid = e.employeeid
	LEFT JOIN hremployees m ON a.reviewerid = m.employeeid" . $WhereSQL;
$CountResult = DB_query($CountSQL);
$CountRow = DB_fetch_row($CountResult);
$ListCount = (int)$CountRow[0];
$DisplayRecordsMax = max(1, (int)$_SESSION['DisplayRecordsMax']);
$ListPageMax = max(1, (int)ceil($ListCount / $DisplayRecordsMax));

$PageOffset = 1;
$IsTopPageControl = isset($_POST['GoTop']) || isset($_POST['NextTop']) || isset($_POST['PreviousTop']);
$IsPageNavigation = $IsTopPageControl || isset($_POST['Go']) || isset($_POST['Next']) || isset($_POST['Previous']);
if ($IsPageNavigation) {
	if ($IsTopPageControl) {
		$PageOffset = max(1, (int)($_POST['PageOffsetTop'] ?? 1));
	} else {
		$PageOffset = max(1, (int)($_POST['PageOffset'] ?? 1));
	}

	if (isset($_POST['NextTop']) || isset($_POST['Next'])) {
		$PageOffset++;
	} elseif (isset($_POST['PreviousTop']) || isset($_POST['Previous'])) {
		$PageOffset--;
	}
}
$PageOffset = max(1, min($PageOffset, $ListPageMax));

$SQL = "SELECT
		a.appraisalid,
		a.employeeid,
		e.employeenumber,
		CONCAT(e.firstname, ' ', e.lastname) as employeename,
		a.reviewperiodstart,
		a.reviewperiodend,
		a.appraisaltype,
		a.duedate,
		a.status,
		a.overallrating,
		a.calculatedoverallrating,
		a.strengths,
		a.areasforimprovement,
		a.goalsnextperiod,
		a.reviewercomments,
		CONCAT(m.firstname, ' ', m.lastname) as managername
	FROM hrperfappraisals a
	INNER JOIN hremployees e ON a.employeeid = e.employeeid
	LEFT JOIN hremployees m ON a.reviewerid = m.employeeid" .
	$WhereSQL . "
	ORDER BY a.duedate DESC, a.appraisalid DESC
	LIMIT " . $DisplayRecordsMax . " OFFSET " . (($PageOffset - 1) * $DisplayRecordsMax);

$Result = DB_query($SQL);

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" name="EmployeeID" value="' . htmlspecialchars((string)($_POST['EmployeeID'] ?? ''), ENT_QUOTES, 'UTF-8') . '" />
		<input type="hidden" name="ManagerID" value="' . htmlspecialchars((string)($_POST['ManagerID'] ?? ''), ENT_QUOTES, 'UTF-8') . '" />
		<input type="hidden" name="Status" value="' . htmlspecialchars((string)($_POST['Status'] ?? ''), ENT_QUOTES, 'UTF-8') . '" />
		<input type="hidden" name="AppraisalType" value="' . htmlspecialchars((string)($_POST['AppraisalType'] ?? ''), ENT_QUOTES, 'UTF-8') . '" />
		<input type="hidden" name="DepartmentID" value="' . htmlspecialchars((string)($_POST['DepartmentID'] ?? ''), ENT_QUOTES, 'UTF-8') . '" />';
if (isset($_POST['PeriodYear'])) {
	echo '<input type="hidden" name="PeriodYear" value="' . htmlspecialchars((string)$_POST['PeriodYear'], ENT_QUOTES, 'UTF-8') . '" />';
}
if ($ListPageMax > 1) {
	echo '<div class="centre"><br />' . $PageOffset . ' ' . __('of') . ' ' . $ListPageMax . ' ' . __('pages') . '. ' . __('Go to Page') . ': ';
	echo '<select name="PageOffsetTop">';
	$ListPage = 1;
	while ($ListPage <= $ListPageMax) {
		$Selected = $ListPage == $PageOffset ? ' selected="selected"' : '';
		echo '<option value="' . $ListPage . '"' . $Selected . '>' . $ListPage . '</option>';
		$ListPage++;
	}
	echo '</select>
		<input type="submit" name="GoTop" value="' . __('Go') . '" />
		<input type="submit" name="PreviousTop" value="' . __('Previous') . '" />
		<input type="submit" name="NextTop" value="' . __('Next') . '" />
		</div>';
}

echo '<table class="selection">
		<thead>
			<tr>
				<th colspan="15">
					<h3>' 
					. __('Performance Appraisal Type') . ': ' . htmlspecialchars($AppraisalTypeTitle, ENT_QUOTES, 'UTF-8') .
					'</h3>
				</th>
			</tr>
			<tr>
				<th>' . __('Appraisal ID') . '</th>
				<th>' . __('Employee') . '</th>
				<th>' . __('Review Period') . '</th>
				<th>' . __('Type') . '</th>
				<th>' . __('Due Date') . '</th>
				<th>' . __('Status') . '</th>
				<th>' . __('Rating') . '</th>
				<th>' . __('Calculated Rating') . '</th>
				<th>' . __('Rating Source') . '</th>
				<th>' . __('Manager') . '</th>
				<th>' . __('Key Strengths') . '</th>
				<th>' . __('Areas for Improvement') . '</th>
				<th>' . __('Goals for Next Period') . '</th>
				<th>' . __('Reviewer Comments') . '</th>
				<th></th>
			</tr>
		</thead>
		<tbody>';

if (DB_num_rows($Result) > 0) {
	while ($MyRow = DB_fetch_array($Result)) {

			// Highlight overdue appraisals
			$RowClass = '';

			$OverallRatingValue = (float)$MyRow['overallrating'];
			$OverallRatingInt   = ($OverallRatingValue > 0) ? MapScoreToRating($OverallRatingValue) : null;
			$OverallRatingLabel = ($OverallRatingInt !== null AND isset($RatingLabels[$OverallRatingInt])) ? $RatingLabels[$OverallRatingInt] : '-';

			$CalcRatingValue = (float)$MyRow['calculatedoverallrating'];
			$CalcRatingInt   = ($CalcRatingValue > 0) ? MapScoreToRating($CalcRatingValue) : null;
			$CalcLabel       = ($CalcRatingInt !== null AND isset($RatingLabels[$CalcRatingInt])) ? $RatingLabels[$CalcRatingInt] : '-';

			$RatingSource = '-';
			if ($CalcRatingValue > 0) {
				if (abs($CalcRatingValue - $OverallRatingValue) < 0.01) {
					$RatingSource = __('Calculated');
				} else {
					$RatingSource = __('Manual') . ' (' . __('Calculated') . ': ' . $CalcLabel . ')';
				}
			}

			if ($MyRow['status'] != 'Completed' AND $MyRow['status'] != 'Cancelled' AND $MyRow['duedate'] < date('Y-m-d')) {
				$RowClass = ' class="warn"';
			}

		echo '<tr class="striped_row"' . $RowClass . '>
				<td>' . $MyRow['appraisalid'] . '</td>
				<td><a href="' . $RootPath . '/HREmployees.php?EmployeeNumber=' . urlencode($MyRow['employeenumber']) . '">' . htmlspecialchars((string)$MyRow['employeename'], ENT_QUOTES, 'UTF-8') . '</a></td>
				<td>' . ConvertSQLDate($MyRow['reviewperiodstart']) . ' - ' . ConvertSQLDate($MyRow['reviewperiodend']) . '</td>
				<td>' . htmlspecialchars((string)$MyRow['appraisaltype'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . ConvertSQLDate($MyRow['duedate']) . '</td>
				<td>' . htmlspecialchars((string)$MyRow['status'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($OverallRatingLabel, ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($CalcLabel, ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($RatingSource, ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars((string)$MyRow['managername'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . nl2br(htmlspecialchars(CleanCommentText((string)$MyRow['strengths']), ENT_QUOTES, 'UTF-8')) . '</td>
				<td>' . nl2br(htmlspecialchars(CleanCommentText((string)$MyRow['areasforimprovement']), ENT_QUOTES, 'UTF-8')) . '</td>
				<td>' . nl2br(htmlspecialchars(CleanCommentText((string)$MyRow['goalsnextperiod']), ENT_QUOTES, 'UTF-8')) . '</td>
				<td>' . nl2br(htmlspecialchars(CleanCommentText((string)$MyRow['reviewercomments']), ENT_QUOTES, 'UTF-8')) . '</td>
				<td class="centre">
					<a href="' . $RootPath . '/HRAppraisalEntry.php?AppraisalID=' . 
						urlencode($MyRow['appraisalid']) . '&amp;From=HRPerformanceAppraisals">' . __('Edit') . '</a> |
					<a href="' . $RootPath . '/HRAppraisalCriteriaSummary.php?AppraisalID=' .
						urlencode($MyRow['appraisalid']) . '&amp;From=HRPerformanceAppraisals">' . __('Details') . '</a>
				</td>
			</tr>';
	}
} else {
	echo '<tr><td colspan="15" class="centre">' . __('No appraisals found') . '</td></tr>';
}

echo '</tbody>
	</table>';

if ($ListPageMax > 1) {
	echo '<div class="centre"><br />' . $PageOffset . ' ' . __('of') . ' ' . $ListPageMax . ' ' . __('pages') . '. ' . __('Go to Page') . ': ';
	echo '<select name="PageOffset">';
	$ListPage = 1;
	while ($ListPage <= $ListPageMax) {
		$Selected = $ListPage == $PageOffset ? ' selected="selected"' : '';
		echo '<option value="' . $ListPage . '"' . $Selected . '>' . $ListPage . '</option>';
		$ListPage++;
	}
	echo '</select>
		<input type="submit" name="Go" value="' . __('Go') . '" />
		<input type="submit" name="Previous" value="' . __('Previous') . '" />
		<input type="submit" name="Next" value="' . __('Next') . '" />
		</div>';
}
echo '</form>';

include(__DIR__ . '/includes/footer.php');

?>
