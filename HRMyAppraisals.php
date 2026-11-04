<?php

/* My Performance Appraisals - Employee Self View */

require(__DIR__ . '/includes/session.php');

$Title = __('My Performance Appraisals');
$ViewTopic = 'HumanResources';
$BookMark = 'HRMyAppraisals';

include(__DIR__ . '/includes/header.php');

require_once(__DIR__ . '/includes/HRPerformanceHelper.php');

/* Rating labels for hrperfappraisals.overallrating (INT 1-5) */
$RatingLabels = GetRatingLabels();

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('View My Appraisals') . '" /> ' .
		__('My Performance Appraisals') . '
	</p>';

// Get current user's employee number
$SQL = "SELECT employeenumber FROM hremployees WHERE userid = '" . DB_escape_string($_SESSION['UserID']) . "'";
$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	// it should never happen that a user is not linked to an employee record, but just in case
	prnMsg(__('Your webERP user account is not linked to an employee record'), 'warn');
	include(__DIR__ . '/includes/footer.php');
	exit;
}

$MyEmpRow = DB_fetch_array($Result);
$MyEmployeeNumber = $MyEmpRow['employeenumber'];

// Get my appraisals
$SQL = "SELECT
		a.appraisalid,
		a.reviewperiodstart,
		a.reviewperiodend,
		a.duedate,
		a.status,
		a.overallrating,
		a.strengths,
		a.areasforimprovement,
		a.goalsnextperiod,
		a.reviewercomments,
		CONCAT(m.firstname, ' ', m.lastname) as managername
	FROM hrperfappraisals a
	LEFT JOIN hremployees e ON a.employeeid = e.employeeid
	LEFT JOIN hremployees m ON a.reviewerid = m.employeeid
	WHERE e.employeenumber = '" . DB_escape_string($MyEmployeeNumber) . "'
		AND a.status = 'Completed'
	ORDER BY a.reviewperiodstart DESC";

$Result = DB_query($SQL);

echo '<table class="selection">
		<thead>
			<tr>
				<th>' . __('Appraisal ID') . '</th>
				<th>' . __('Review Period') . '</th>
				<th>' . __('Due Date') . '</th>
				<th>' . __('Status') . '</th>
				<th>' . __('Rating') . '</th>
				<th>' . __('Manager') . '</th>
				<th>' . __('Key Strengths') . '</th>
				<th>' . __('Areas for Improvement') . '</th>
				<th>' . __('Goals for Next Period') . '</th>
				<th>' . __('Reviewer Comments') . '</th>
				<th>' . __('Actions') . '</th>
			</tr>
		</thead>
		<tbody>';

if (DB_num_rows($Result) > 0) {
	while ($MyRow = DB_fetch_array($Result)) {

		// Highlight overdue appraisals
		$RowClass = '';
		if ($MyRow['status'] != 'Completed' and $MyRow['status'] != 'Cancelled' and $MyRow['duedate'] < date('Y-m-d')) {
			$RowClass = ' class="warn"';
		}

		echo '<tr' . $RowClass . '>
				<td>' . $MyRow['appraisalid'] . '</td>
				<td>' . ConvertSQLDate($MyRow['reviewperiodstart']) . ' - ' . ConvertSQLDate($MyRow['reviewperiodend']) . '</td>
				<td>' . ConvertSQLDate($MyRow['duedate']) . '</td>
				<td>' . htmlspecialchars($MyRow['status'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . (isset($RatingLabels[$MyRow['overallrating']]) ? htmlspecialchars($RatingLabels[$MyRow['overallrating']], ENT_QUOTES, 'UTF-8') : '-') . '</td>
				<td>' . htmlspecialchars($MyRow['managername'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . nl2br(htmlspecialchars((string)$MyRow['strengths'], ENT_QUOTES, 'UTF-8')) . '</td>
				<td>' . nl2br(htmlspecialchars((string)$MyRow['areasforimprovement'], ENT_QUOTES, 'UTF-8')) . '</td>
				<td>' . nl2br(htmlspecialchars((string)$MyRow['goalsnextperiod'], ENT_QUOTES, 'UTF-8')) . '</td>
				<td>' . nl2br(htmlspecialchars((string)$MyRow['reviewercomments'], ENT_QUOTES, 'UTF-8')) . '</td>
				<td class="centre">
					<a href="' . $RootPath . '/HRAppraisalCriteriaSummary.php?AppraisalID=' .
						urlencode($MyRow['appraisalid']) . '&amp;From=HRMyAppraisals">' . __('View') . '</a>
				</td>
			</tr>';
	}
} else {
	echo '<tr><td colspan="11" class="centre">' . __('You have no performance appraisals on record') . '</td></tr>';
}

echo '</tbody>
	</table>';

include(__DIR__ . '/includes/footer.php');

?>
