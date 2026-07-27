<?php

/* Employees Due for Colleague Feedback Report */

require(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/HRPerformanceHelper.php');

$Title = __('Employees Due for Colleague Feedback');
$ViewTopic = 'HumanResources';
$BookMark = 'HRColleagueFeedbackDue';

include(__DIR__ . '/includes/header.php');

$SQL = "SELECT optionvalue
		FROM hrsystemoptions
		WHERE optionname = 'ColleagueFeedbackFrequency'";
$OptionsResult = DB_query($SQL, '', '', false, false);
$ColleagueFeedbackFrequency = 365;
if (DB_num_rows($OptionsResult) > 0) {
	$OptionRow = DB_fetch_array($OptionsResult);
	$ColleagueFeedbackFrequency = (int)$OptionRow['optionvalue'];
}

$MyEmployeeID = GetEmployeeIDFromUserID((string)$_SESSION['UserID']);

echo '<a class="toplink" href="' . $RootPath . '/HRDashboard.php">' . __('Return to HR Dashboard') . '</a>';

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('Employees Due for Colleague Feedback') . '" /> ' .
		__('Employees Due for Colleague Feedback') . '
	</p>';

echo '<div class="centre">
		<p>' . __('Colleague feedback frequency') . ': ' . (int)$ColleagueFeedbackFrequency . ' ' . __('days') . '</p>
	</div>';

$SQL = "SELECT
		e.employeeid,
		e.employeenumber,
		e.firstname,
		e.lastname,
		e.email,
		d.description AS department,
		p.positiontitle,
		f.lastfeedback,
		DATEDIFF(CURDATE(), COALESCE(f.lastfeedback, e.hiredate)) AS dayssince
	FROM hremployees e
	LEFT JOIN departments d ON e.departmentid = d.departmentid
	LEFT JOIN hrpositions p ON e.positionid = p.positionid
	LEFT JOIN (
		SELECT aboutemployeeid, MAX(feedbackperiodend) AS lastfeedback
		FROM hrcolleaguefeedback
		WHERE status = 'Completed'
		GROUP BY aboutemployeeid
	) f ON e.employeeid = f.aboutemployeeid
	WHERE e.employmentstatus = 'Active'
		AND (f.lastfeedback IS NULL
			OR DATEDIFF(CURDATE(), f.lastfeedback) >= " . (int)$ColleagueFeedbackFrequency . ")
	ORDER BY dayssince DESC, e.lastname, e.firstname";

$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	echo '<div class="centre">
			<p>' . __('No employees are currently due for colleague feedback') . '</p>
		</div>';
} else {
	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . __('Employee Number') . '</th>
					<th class="SortedColumn">' . __('Employee Name') . '</th>
					<th class="SortedColumn">' . __('Department') . '</th>
					<th class="SortedColumn">' . __('Position') . '</th>
					<th class="SortedColumn">' . __('Last Feedback') . '</th>
					<th class="SortedColumn">' . __('Days Since') . '</th>
					<th class="SortedColumn">' . __('Actions') . '</th>
				</tr>
			</thead>
			<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		$CreateLink = $RootPath . '/HRColleagueFeedbackEntry.php?AboutEmployeeID=' . urlencode($MyRow['employeeid']) . '&From=HRColleagueFeedbackDue';
		if ($MyEmployeeID > 0) {
			$CreateLink .= '&FromEmployeeID=' . urlencode($MyEmployeeID);
		}

		echo '<tr class="striped_row">
				<td>' . htmlspecialchars(PadEmployeeNumber((string)$MyRow['employeenumber']), ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars((string)$MyRow['firstname'] . ' ' . $MyRow['lastname'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars((string)$MyRow['department'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars((string)$MyRow['positiontitle'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . ($MyRow['lastfeedback'] ? ConvertSQLDate($MyRow['lastfeedback']) : __('Never')) . '</td>
				<td>' . number_format((float)$MyRow['dayssince']) . '</td>
				<td>
					<a href="' . $CreateLink . '">' . __('Create Feedback') . '</a>
				</td>
			</tr>';
	}

	echo '</tbody>
		</table>';
}

include(__DIR__ . '/includes/footer.php');

?>
