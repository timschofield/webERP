<?php

/* Employees Due for Appraisal Report */

require(__DIR__ . '/includes/session.php');

$Title = __('Employees Due for Appraisal');
$ViewTopic = 'HumanResources';
$BookMark = 'HRAppraisalsDue';

include(__DIR__ . '/includes/header.php');

// Get system options
$SQL = "SELECT optionname, optionvalue FROM hrsystemoptions WHERE optionname = 'AppraisalFrequency'";
$OptionsResult = DB_query($SQL);
$AppraisalFrequency = 365; // Default to annual
if (DB_num_rows($OptionsResult) > 0) {
	$OptionRow = DB_fetch_array($OptionsResult);
	$AppraisalFrequency = $OptionRow['optionvalue'];
}

echo '<a class="toplink" href="' . $RootPath . '/HRDashboard.php">' . __('Return to HR Dashboard') . '</a>';

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('Employees Due for Appraisal') . '" /> ' .
		__('Employees Due for Appraisal') . '
	</p>';

echo '<div class="centre">
		<p>' . __('Appraisal frequency') . ': ' . $AppraisalFrequency . ' ' . __('days') . '</p>
	</div>';

// Find employees due for appraisal
$SQL = "SELECT
		e.employeeid,
		e.employeenumber,
		e.firstname,
		e.lastname,
		e.email,
		d.description as department,
		p.positiontitle,
		a.lastappraisal,
		DATEDIFF(CURDATE(), COALESCE(a.lastappraisal, e.hiredate)) as dayssince
	FROM hremployees e
	LEFT JOIN departments d ON e.departmentid = d.departmentid
	LEFT JOIN hrpositions p ON e.positionid = p.positionid
	LEFT JOIN (
		SELECT employeeid, MAX(reviewperiodend) as lastappraisal
		FROM hrperfappraisals
		WHERE status = 'Completed'
		GROUP BY employeeid
	) a ON e.employeeid = a.employeeid
	WHERE e.employmentstatus = 'Active'
	AND (a.lastappraisal IS NULL
		OR DATEDIFF(CURDATE(), a.lastappraisal) >= " . $AppraisalFrequency . ")
	ORDER BY dayssince DESC, e.lastname, e.firstname";

$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	echo '<div class="centre">
			<p>' . __('No employees are currently due for appraisal') . '</p>
		</div>';
} else {
	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . __('Employee Number') . '</th>
					<th class="SortedColumn">' . __('Employee Name') . '</th>
					<th class="SortedColumn">' . __('Department') . '</th>
					<th class="SortedColumn">' . __('Position') . '</th>
					<th class="SortedColumn">' . __('Last Appraisal') . '</th>
					<th class="SortedColumn">' . __('Days Since') . '</th>
					<th class="SortedColumn">' . __('Actions') . '</th>
				</tr>
			</thead>
			<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>' . $MyRow['employeenumber'] . '</td>
				<td>' . $MyRow['firstname'] . ' ' . $MyRow['lastname'] . '</td>
				<td>' . $MyRow['department'] . '</td>
				<td>' . $MyRow['positiontitle'] . '</td>
				<td>' . ($MyRow['lastappraisal'] ? ConvertSQLDate($MyRow['lastappraisal']) : __('Never')) . '</td>
				<td>' . number_format($MyRow['dayssince']) . '</td>
				<td>
					<a href="' . $RootPath . '/HRAppraisalEntry.php?EmployeeNumber=' . urlencode($MyRow['employeenumber']) . '">' . __('Create Appraisal') . '</a>
				</td>
			</tr>';
	}

	echo '</tbody>
		</table>';
}

include(__DIR__ . '/includes/footer.php');

?>
