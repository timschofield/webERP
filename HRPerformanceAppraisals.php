<?php

/* Performance Appraisals Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Performance Appraisals');
$ViewTopic = 'HumanResources';
$BookMark = 'HRPerformanceAppraisals';

include(__DIR__ . '/includes/header.php');

/* Rating labels for hrperfappraisals.overallrating (INT 1-5) */
$RatingLabels = array(
	5 => __('Outstanding'),
	4 => __('Exceeds Expectations'),
	3 => __('Meets Expectations'),
	2 => __('Needs Improvement'),
	1 => __('Unsatisfactory'),
);

// Get system options
$SQL = "SELECT optionname, optionvalue FROM hrsystemoptions WHERE optionname = 'AppraisalFrequency'";
$OptionsResult = DB_query($SQL);
$AppraisalFrequency = 365; // Default to annual
if (DB_num_rows($OptionsResult) > 0) {
	$OptionRow = DB_fetch_array($OptionsResult);
	$AppraisalFrequency = $OptionRow['optionvalue'];
}

echo '<a class="toplink" href="' . $RootPath . '/HRAppraisalEntry.php">' . __('Create New Appraisal') . '</a>';

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('Performance Appraisals') . '" /> ' .
		__('Performance Appraisals Management') . '
	</p>';

// Search and filter form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Search & Filter') . '</legend>
		<field>
			<label for="Keywords">' . __('Search') . ':</label>
			<input type="text" name="Keywords" size="20" maxlength="50" value="' . (isset($_POST['Keywords']) ? $_POST['Keywords'] : '') . '" />
		</field>';

echo '<field>
			<label for="Status">' . __('Status') . ':</label>
			<select name="Status">
				<option value="">' . __('All Statuses') . '</option>
				<option value="Not Started"' . (isset($_POST['Status']) && $_POST['Status'] == 'Not Started' ? ' selected' : '') . '>' . __('Not Started') . '</option>
				<option value="In Progress"' . (isset($_POST['Status']) && $_POST['Status'] == 'In Progress' ? ' selected' : '') . '>' . __('In Progress') . '</option>
				<option value="Completed"' . (isset($_POST['Status']) && $_POST['Status'] == 'Completed' ? ' selected' : '') . '>' . __('Completed') . '</option>
				<option value="Cancelled"' . (isset($_POST['Status']) && $_POST['Status'] == 'Cancelled' ? ' selected' : '') . '>' . __('Cancelled') . '</option>
			</select>
		</field>';

echo '<field>
			<label for="PeriodYear">' . __('Period Year') . ':</label>
			<input type="number" name="PeriodYear" size="4" maxlength="4" value="' . (isset($_POST['PeriodYear']) ? $_POST['PeriodYear'] : date('Y')) . '" />
		</field>';

echo '</fieldset>';

echo '<div class="centre">
				<input type="submit" name="Search" value="' . __('Search') . '" />
			</div>';

echo '</form>';

// Build query
$SQL = "SELECT
		a.appraisalid,
		a.employeeid,
		e.employeenumber,
		CONCAT(e.firstname, ' ', e.lastname) as employeename,
		a.reviewperiodstart,
		a.reviewperiodend,
		a.duedate,
		a.status,
		a.overallrating,
		CONCAT(m.firstname, ' ', m.lastname) as managername
	FROM hrperfappraisals a
	INNER JOIN hremployees e ON a.employeeid = e.employeeid
	LEFT JOIN hremployees m ON a.reviewerid = m.employeeid
	WHERE 1=1";

if (isset($_POST['Keywords']) && $_POST['Keywords'] != '') {
	$Keywords = $_POST['Keywords'];
	$SQL .= " AND (e.firstname LIKE '%" . $Keywords . "%'
			OR e.lastname LIKE '%" . $Keywords . "%'
			OR e.employeeid LIKE '%" . $Keywords . "%')";
}

if (isset($_POST['Status']) && $_POST['Status'] != '') {
	$Status = $_POST['Status'];
	$SQL .= " AND a.status = '" . $Status . "'";
}

if (isset($_POST['PeriodYear']) && $_POST['PeriodYear'] != '') {
	$Year = $_POST['PeriodYear'];
	$SQL .= " AND YEAR(a.reviewperiodstart) = '" . $Year . "'";
}

$SQL .= " ORDER BY a.duedate DESC, a.appraisalid DESC";

$Result = DB_query($SQL);

echo '<table class="selection">
		<thead>
			<tr>
				<th colspan="8">
					<h3>
						<em>' . __('System appraisal frequency') . ': ' . ($AppraisalFrequency == 365 ? __('Annual') : $AppraisalFrequency . ' ' . __('days')) . '</em>
					</h3>
				</th>
			</tr>
			<tr>
				<th>' . __('Appraisal ID') . '</th>
				<th>' . __('Employee') . '</th>
				<th>' . __('Review Period') . '</th>
				<th>' . __('Due Date') . '</th>
				<th>' . __('Status') . '</th>
				<th>' . __('Rating') . '</th>
				<th>' . __('Manager') . '</th>
				<th></th>
			</tr>
		</thead>
		<tbody>';

if (DB_num_rows($Result) > 0) {
	while ($MyRow = DB_fetch_array($Result)) {

		// Highlight overdue appraisals
		$RowClass = '';
		if ($MyRow['status'] != 'Completed' && $MyRow['status'] != 'Cancelled' && $MyRow['duedate'] < date('Y-m-d')) {
			$RowClass = ' class="warn"';
		}

		echo '<tr class="striped_row"' . $RowClass . '>
				<td>' . $MyRow['appraisalid'] . '</td>
				<td><a href="' . $RootPath . '/HREmployees.php?EmployeeNumber=' . urlencode($MyRow['employeenumber']) . '">' . htmlspecialchars($MyRow['employeename'], ENT_QUOTES, 'UTF-8') . '</a></td>
				<td>' . ConvertSQLDate($MyRow['reviewperiodstart']) . ' - ' . ConvertSQLDate($MyRow['reviewperiodend']) . '</td>
				<td>' . ConvertSQLDate($MyRow['duedate']) . '</td>
				<td>' . htmlspecialchars($MyRow['status'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . (isset($RatingLabels[$MyRow['overallrating']]) ? htmlspecialchars($RatingLabels[$MyRow['overallrating']], ENT_QUOTES, 'UTF-8') : '-') . '</td>
				<td>' . htmlspecialchars($MyRow['managername'], ENT_QUOTES, 'UTF-8') . '</td>
				<td class="centre">
					<a href="' . $RootPath . '/HRAppraisalEntry.php?AppraisalID=' . urlencode($MyRow['appraisalid']) . '">' . __('Edit') . '</a>
				</td>
			</tr>';
	}
} else {
	echo '<tr><td colspan="8" class="centre">' . __('No appraisals found') . '</td></tr>';
}

echo '</tbody>
	</table>';

include(__DIR__ . '/includes/footer.php');

?>
