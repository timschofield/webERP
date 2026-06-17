<?php

/* My Performance Appraisals as Reviewer - Appraisals Assigned to Review */

require(__DIR__ . '/includes/session.php');

$Title = __('Appraisals to Review');
$ViewTopic = 'HumanResources';
$BookMark = 'HRMyAppraisalsAsReviewer';

include(__DIR__ . '/includes/header.php');

require_once(__DIR__ . '/includes/HRPerformanceHelper.php');

/* Rating labels for hrperfappraisals.overallrating (INT 1-5) */
$RatingLabels = GetRatingLabels();

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' .
		__('Appraisals to Review') . '" /> ' .
		__('Performance Appraisals as Reviewer') . '
	</p>';

// Get current user's employee ID
$SQL = "SELECT employeeid FROM hremployees WHERE userid = '" . DB_escape_string($_SESSION['UserID']) . "'";
$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	// it should never happen that a user is not linked to an employee record, but just in case
	prnMsg(__('Your webERP user account is not linked to an employee record'), 'warn');
	include(__DIR__ . '/includes/footer.php');
	exit;
}

$MyEmpRow = DB_fetch_array($Result);
$MyEmployeeID = $MyEmpRow['employeeid'];

// Get appraisals where current user is the reviewer
$SQL = "SELECT
		a.appraisalid,
		a.reviewperiodstart,
		a.reviewperiodend,
		a.duedate,
		a.status,
		a.overallrating,
		a.comments,
		CONCAT(e.firstname, ' ', e.lastname) as employeename
	FROM hrperfappraisals a
	LEFT JOIN hremployees e ON a.employeeid = e.employeeid
	WHERE a.reviewerid = " . (int)$MyEmployeeID . "
	ORDER BY CASE a.status
		WHEN 'Not Started' THEN 1
		WHEN 'In Progress' THEN 2
		WHEN 'Completed' THEN 3
		WHEN 'Cancelled' THEN 4
		ELSE 5
	END, a.duedate ASC";

$Result = DB_query($SQL);

echo '<table class="selection">
		<thead>
			<tr>
				<th>' . __('Appraisal ID') . '</th>
				<th>' . __('Employee') . '</th>
				<th>' . __('Review Period') . '</th>
				<th>' . __('Due Date') . '</th>
				<th>' . __('Status') . '</th>
				<th>' . __('Rating') . '</th>
				<th>' . __('Actions') . '</th>
			</tr>
		</thead>
		<tbody>';

if (DB_num_rows($Result) > 0) {
	while ($MyRow = DB_fetch_array($Result)) {

		// Highlight overdue appraisals
		$RowClass = '';
		if ($MyRow['status'] != 'Completed'
			AND $MyRow['status'] != 'Cancelled'
			AND $MyRow['duedate'] < date('Y-m-d')) {
			$RowClass = ' class="warn"';
		}

		echo '<tr' . $RowClass . '>
				<td>' . $MyRow['appraisalid'] . '</td>
				<td>' . htmlspecialchars($MyRow['employeename'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . ConvertSQLDate($MyRow['reviewperiodstart']) . ' - ' .
					ConvertSQLDate($MyRow['reviewperiodend']) . '</td>
				<td>' . ConvertSQLDate($MyRow['duedate']) . '</td>
				<td>' . htmlspecialchars($MyRow['status'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . (isset($RatingLabels[$MyRow['overallrating']]) ?
					htmlspecialchars($RatingLabels[$MyRow['overallrating']], ENT_QUOTES, 'UTF-8') :
					'-') . '</td>
				<td class="centre">
					<a href="' . $RootPath . '/HRAppraisalEntry.php?AppraisalID=' .
						urlencode($MyRow['appraisalid']) . '&amp;From=HRMyAppraisalsAsReviewer">' . __('Edit') . '</a>
				</td>
			</tr>';

		// Show comments if any
		if ($MyRow['comments']) {
			echo '<tr' . $RowClass . '>
					<td colspan="7" style="padding-left: 30px;">
						<strong>' . __('Comments') . ':</strong> ' .
						nl2br(htmlspecialchars($MyRow['comments'], ENT_QUOTES, 'UTF-8')) . '
					</td>
				</tr>';
		}
	}
} else {
	echo '<tr><td colspan="7" class="centre">' . __('You are not assigned to review any performance appraisals') . '</td></tr>';
}

echo '</tbody>
	</table>';

include(__DIR__ . '/includes/footer.php');

?>
