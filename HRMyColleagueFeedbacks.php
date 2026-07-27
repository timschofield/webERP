<?php

/* My Colleague Feedbacks - Feedbacks assigned to me as reviewer (from employee) */

require(__DIR__ . '/includes/session.php');

$Title = __('My Colleague Feedbacks');
$ViewTopic = 'HumanResources';
$BookMark = 'HRMyColleagueFeedbacks';

include(__DIR__ . '/includes/header.php');

require_once(__DIR__ . '/includes/HRPerformanceHelper.php');

$RatingLabels = GetRatingLabels();

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('My Colleague Feedbacks') . '" /> ' .
		__('My Colleague Feedbacks') . '
	</p>';

$MyEmployeeID = GetEmployeeIDFromUserID((string)$_SESSION['UserID']);
if ($MyEmployeeID <= 0) {
	prnMsg(__('Your webERP user account is not linked to an employee record'), 'warn');
	include(__DIR__ . '/includes/footer.php');
	exit;
}

$SQL = "SELECT
		f.feedbackid,
		f.feedbackperiodstart,
		f.feedbackperiodend,
		f.feedbacktype,
		f.duedate,
		f.status,
		f.overallrating,
		f.comments,
		CONCAT(ae.firstname, ' ', ae.lastname) AS aboutname,
		ae.employeenumber AS aboutemployeenumber
	FROM hrcolleaguefeedback f
	INNER JOIN hremployees ae ON f.aboutemployeeid = ae.employeeid
	WHERE f.fromemployeeid = " . (int)$MyEmployeeID . "
	ORDER BY f.feedbackperiodstart DESC, f.feedbackid DESC";
$Result = DB_query($SQL);

echo '<table class="selection">
		<thead>
			<tr>
				<th>' . __('Feedback ID') . '</th>
				<th>' . __('About Employee') . '</th>
				<th>' . __('Feedback Period') . '</th>
				<th>' . __('Type') . '</th>
				<th>' . __('Due Date') . '</th>
				<th>' . __('Status') . '</th>
				<th>' . __('Rating') . '</th>
				<th>' . __('Actions') . '</th>
			</tr>
		</thead>
		<tbody>';

if (DB_num_rows($Result) > 0) {
	while ($MyRow = DB_fetch_array($Result)) {
		$RowClass = '';
		if ($MyRow['status'] != 'Completed' && $MyRow['status'] != 'Cancelled' && $MyRow['duedate'] != null && $MyRow['duedate'] < date('Y-m-d')) {
			$RowClass = ' class="warn"';
		}

		$RatingLabel = '-';
		if (isset($MyRow['overallrating']) && $MyRow['overallrating'] !== null && $MyRow['overallrating'] !== '') {
			$RatingInt = (int)$MyRow['overallrating'];
			$RatingLabel = isset($RatingLabels[$RatingInt]) ? $RatingLabels[$RatingInt] : (string)$RatingInt;
		}

		echo '<tr' . $RowClass . '>
				<td>' . (int)$MyRow['feedbackid'] . '</td>
				<td>' . htmlspecialchars((string)$MyRow['aboutname'], ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars(PadEmployeeNumber((string)$MyRow['aboutemployeenumber']), ENT_QUOTES, 'UTF-8') . ')</td>
				<td>' . ConvertSQLDate($MyRow['feedbackperiodstart']) . ' - ' . ConvertSQLDate($MyRow['feedbackperiodend']) . '</td>
				<td>' . htmlspecialchars((string)$MyRow['feedbacktype'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . ($MyRow['duedate'] ? ConvertSQLDate($MyRow['duedate']) : '-') . '</td>
				<td>' . htmlspecialchars((string)$MyRow['status'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars((string)$RatingLabel, ENT_QUOTES, 'UTF-8') . '</td>
				<td class="centre">
					<a href="' . $RootPath . '/HRColleagueFeedbackEntry.php?FeedbackID=' . urlencode($MyRow['feedbackid']) . '&amp;From=HRMyColleagueFeedbacks">' . __('Edit') . '</a>
				</td>
			</tr>';

		if ($MyRow['comments']) {
			echo '<tr' . $RowClass . '>
					<td colspan="8" style="padding-left: 30px;">
						<strong>' . __('Comments') . ':</strong> ' . nl2br(htmlspecialchars($MyRow['comments'], ENT_QUOTES, 'UTF-8')) . '
					</td>
				</tr>';
		}
	}
} else {
	echo '<tr><td colspan="8" class="centre">' . __('You are not assigned to any colleague feedback records as reviewer') . '</td></tr>';
}

echo '</tbody>
	</table>';

include(__DIR__ . '/includes/footer.php');

?>
