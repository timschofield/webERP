<?php

/* Colleague Feedback Management */

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include(__DIR__ . '/includes/SetDomPDFOptions.php');

$Title = __('Colleague Feedback');
$ViewTopic = 'HumanResources';
$BookMark = 'HRColleagueFeedback';

require_once(__DIR__ . '/includes/HRPerformanceHelper.php');
require_once(__DIR__ . '/includes/HRGeneralFunctions.php');
require_once(__DIR__ . '/includes/UIGeneralFunctions.php');

$RatingLabels = GetRatingLabels();
$PrintPDF = isset($_POST['PrintPDF']);

$WhereSQL = " WHERE 1=1";

if (isset($_POST['FromEmployeeID']) and $_POST['FromEmployeeID'] != '') {
	$WhereSQL .= " AND f.fromemployeeid = '" . (int)$_POST['FromEmployeeID'] . "'";
}

if (isset($_POST['AboutEmployeeID']) and $_POST['AboutEmployeeID'] != '') {
	$WhereSQL .= " AND f.aboutemployeeid = '" . (int)$_POST['AboutEmployeeID'] . "'";
}

if (isset($_POST['Status']) and $_POST['Status'] != '') {
	$WhereSQL .= " AND f.status = '" . DB_escape_string($_POST['Status']) . "'";
}

if (isset($_POST['FeedbackType']) and $_POST['FeedbackType'] != '') {
	$WhereSQL .= " AND f.feedbacktype = '" . DB_escape_string($_POST['FeedbackType']) . "'";
}

if (isset($_POST['DepartmentID']) and $_POST['DepartmentID'] != '') {
	/* Employee the feedback is about: selected department plus all its sub-departments */
	$DepartmentIdList = explode(',', GetDepartmentListBelow($_POST['DepartmentID']));
	$WhereSQL .= " AND ae.departmentid IN ('" . implode("','", array_map('DB_escape_string', $DepartmentIdList)) . "')";
}

if (isset($_POST['PeriodYear']) and $_POST['PeriodYear'] != '') {
	$WhereSQL .= " AND YEAR(f.feedbackperiodstart) = " . (int)$_POST['PeriodYear'];
}

$CountSQL = "SELECT COUNT(*)
	FROM hrcolleaguefeedback f
	INNER JOIN hremployees fe ON f.fromemployeeid = fe.employeeid
	INNER JOIN hremployees ae ON f.aboutemployeeid = ae.employeeid
	LEFT JOIN hremployees ce ON f.createdbyid = ce.employeeid" . $WhereSQL;
$CountResult = DB_query($CountSQL);
$CountRow = DB_fetch_row($CountResult);
$ListCount = (int)$CountRow[0];
$DisplayRecordsMax = max(1, (int)$_SESSION['DisplayRecordsMax']);
$ListPageMax = max(1, (int)ceil($ListCount / $DisplayRecordsMax));

$PageOffset = 1;
$IsTopPageControl = isset($_POST['GoTop']) || isset($_POST['NextTop']) || isset($_POST['PreviousTop']);
$IsPageNavigation = $IsTopPageControl || isset($_POST['Go']) || isset($_POST['Next']) || isset($_POST['Previous']);
if (!$PrintPDF && $IsPageNavigation) {
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
	LEFT JOIN hremployees ce ON f.createdbyid = ce.employeeid" .
	$WhereSQL . "
	ORDER BY f.duedate DESC, f.feedbackid DESC";
if (!$PrintPDF) {
	$SQL .= " LIMIT " . $DisplayRecordsMax . " OFFSET " . (($PageOffset - 1) * $DisplayRecordsMax);
}

$Result = DB_query($SQL);

$HTML = '';

if ($PrintPDF) {
	$HTML .= '<html>
				<head>';
	$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	$HTML .= '</head>
				<body>';
}

$HTML .= '<table class="selection">
		<thead>';

if ($PrintPDF) {
	$HTML .= '<tr>
				<th colspan="10">
					<h2>' . __('Colleague Feedback') . ' - ' . ConvertSQLDate(date('Y-m-d')) . '</h2>
				</th>
			</tr>';
}

$HTML .= '<tr>
				<th>' . __('Feedback ID') . '</th>
				<th>' . __('From Employee') . '</th>
				<th>' . __('About Employee') . '</th>
				<th>' . __('Feedback Period') . '</th>
				<th>' . __('Type') . '</th>
				<th>' . __('Due Date') . '</th>
				<th>' . __('Status') . '</th>
				<th>' . __('Rating') . '</th>
				<th>' . __('Created By') . '</th>
				<th>' . __('Comments') . '</th>' .
				($PrintPDF ? '' : '<th></th>') . '
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

		$Comments = CleanCommentText((string)$MyRow['comments']);

		$HTML .= '<tr class="striped_row"' . $RowClass . '>
				<td>' . (int)$MyRow['feedbackid'] . '</td>
				<td>' . htmlspecialchars((string)$MyRow['fromname'], ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars(PadEmployeeNumber((string)$MyRow['fromemployeenumber']), ENT_QUOTES, 'UTF-8') . ')</td>
				<td>' . htmlspecialchars((string)$MyRow['aboutname'], ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars(PadEmployeeNumber((string)$MyRow['aboutemployeenumber']), ENT_QUOTES, 'UTF-8') . ')</td>
				<td>' . ConvertSQLDate($MyRow['feedbackperiodstart']) . ' - ' . ConvertSQLDate($MyRow['feedbackperiodend']) . '</td>
				<td>' . htmlspecialchars((string)$MyRow['feedbacktype'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . ($MyRow['duedate'] ? ConvertSQLDate($MyRow['duedate']) : '-') . '</td>
				<td>' . htmlspecialchars((string)$MyRow['status'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars((string)$RatingLabel, ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars((string)$MyRow['createdbyname'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . nl2br(htmlspecialchars($Comments, ENT_QUOTES, 'UTF-8')) . '</td>';
		if (!$PrintPDF) {
			$HTML .= '<td class="centre">
					<a href="' . $RootPath . '/HRColleagueFeedbackEntry.php?FeedbackID=' . urlencode($MyRow['feedbackid']) . '&amp;From=HRColleagueFeedback">' . __('Edit') . '</a>
				</td>';
		}
		$HTML .= '</tr>';
	}
} else {
	$HTML .= '<tr><td colspan="' . ($PrintPDF ? 10 : 11) . '" class="centre">' . __('No colleague feedback records found') . '</td></tr>';
}

$HTML .= '</tbody>
	</table>';

if ($PrintPDF) {
	$HTML .= '</body></html>';
	$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
	$DomPDF->loadHtml($HTML);

	$DomPDF->setPaper($_SESSION['PageSize'], 'landscape');

	// Render the HTML as PDF
	$DomPDF->render();

	// Output the generated PDF to Browser
	$PDFContent = $DomPDF->output();
	SendPDFToBrowser($PDFContent, $Title . date('Y-m-d') . '.pdf');
	exit();
}

include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('Colleague Feedback') . '" /> ' .
		__('Colleague Feedback Management') . '
	</p>';

/* Search and filter form */
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
	'FromEmployeeID', $_POST['FromEmployeeID'] ?? null, __('From Employee'), Required: false, Options: $EmployeeOptions
);
echo FieldToSelectOneEmployee(
	'AboutEmployeeID', $_POST['AboutEmployeeID'] ?? null, __('About Employee'), Required: false, Options: $EmployeeOptions
);

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
			<input type="submit" name="PrintPDF" value="' . __('PDF Colleague Feedbacks') . '" />
			<input type="submit" name="CreateNew" value="' . __('Create New Feedback') . '" onclick="window.location=\'' . $RootPath . '/HRColleagueFeedbackEntry.php\'; return false;" />
		</div>';

echo '</form>';

if ($ListPageMax > 1) {
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<input type="hidden" name="FromEmployeeID" value="' . htmlspecialchars((string)($_POST['FromEmployeeID'] ?? ''), ENT_QUOTES, 'UTF-8') . '" />
			<input type="hidden" name="AboutEmployeeID" value="' . htmlspecialchars((string)($_POST['AboutEmployeeID'] ?? ''), ENT_QUOTES, 'UTF-8') . '" />
			<input type="hidden" name="Status" value="' . htmlspecialchars((string)($_POST['Status'] ?? ''), ENT_QUOTES, 'UTF-8') . '" />
			<input type="hidden" name="FeedbackType" value="' . htmlspecialchars((string)($_POST['FeedbackType'] ?? ''), ENT_QUOTES, 'UTF-8') . '" />
			<input type="hidden" name="DepartmentID" value="' . htmlspecialchars((string)($_POST['DepartmentID'] ?? ''), ENT_QUOTES, 'UTF-8') . '" />';
	if (isset($_POST['PeriodYear'])) {
		echo '<input type="hidden" name="PeriodYear" value="' . htmlspecialchars((string)$_POST['PeriodYear'], ENT_QUOTES, 'UTF-8') . '" />';
	}
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

echo $HTML;

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
		</div>
	</form>';
}

include(__DIR__ . '/includes/footer.php');

?>
