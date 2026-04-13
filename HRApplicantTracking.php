<?php

/* Applicant Tracking */

require(__DIR__ . '/includes/session.php');

$Title = __('Applicant Tracking');
$ViewTopic = 'HumanResources';
$BookMark = 'ApplicantTracking';

include(__DIR__ . '/includes/header.php');

if (!isset($_GET['ApplicantID'])) {
	prnMsg(__('No applicant specified'), 'error');
	echo '<p class="centre"><a href="' . $RootPath . '/HRApplicants.php">' . __('Go to Applicants List') . '</a></p>';
	include(__DIR__ . '/includes/footer.php');
	exit;
}

$ApplicantID = (int)$_GET['ApplicantID'];

// Get applicant details
$SQL = "SELECT a.*, p.positioncode, p.positiontitle, ar.requisitionid, r.status
		FROM hrapplicants a
		LEFT JOIN hrapplicantreqs ar ON a.applicantid = ar.applicantid
		LEFT JOIN hrrequisitions r ON ar.requisitionid = r.requisitionid
		LEFT JOIN hrpositions p ON r.positionid = p.positionid
		WHERE a.applicantid = " . $ApplicantID;
$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	prnMsg(__('Applicant not found'), 'error');
	echo '<p class="centre"><a href="' . $RootPath . '/HRApplicants.php">' . __('Go to Applicants List') . '</a></p>';
	include(__DIR__ . '/includes/footer.php');
	exit;
}

$ApplicantDetails = DB_fetch_array($Result);

echo '<a class="toplink"; href="' . $RootPath . '/HRApplicants.php">' . __('Return to Applicants List') . '</a><br /><br />';
echo '<a class="toplink" href="' . $RootPath . '/HRDashboard.php">' . __('HR Dashboard') . '</a>';

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Tracking') . '" /> ' .
		__('Applicant Tracking') . ' - ' . htmlspecialchars($ApplicantDetails['firstname'] . ' ' . $ApplicantDetails['lastname'], ENT_QUOTES, 'UTF-8') . '
	</p>';

// Display applicant summary
echo '<div style="border: 1px solid #ccc; padding: 15px; margin: 10px auto; width: 90%; background-color: #f9f9f9;">
		<h3>' . __('Applicant Information') . '</h3>
		<table style="width: 100%;">
			<tr>
				<td style="width: 20%;"><strong>' . __('Name') . ':</strong></td>
				<td>' . htmlspecialchars($ApplicantDetails['firstname'] . ' ' . $ApplicantDetails['lastname'], ENT_QUOTES, 'UTF-8') . '</td>
				<td style="width: 20%;"><strong>' . __('Email') . ':</strong></td>
				<td>' . htmlspecialchars($ApplicantDetails['email'], ENT_QUOTES, 'UTF-8') . '</td>
			</tr>
			<tr>
				<td><strong>' . __('Phone') . ':</strong></td>
				<td>' . htmlspecialchars($ApplicantDetails['phone'], ENT_QUOTES, 'UTF-8') . '</td>
				<td><strong>' . __('Position') . ':</strong></td>
				<td>' . ($ApplicantDetails['positiontitle'] ? htmlspecialchars($ApplicantDetails['positioncode'] . ' - ' . $ApplicantDetails['positiontitle'], ENT_QUOTES, 'UTF-8') : '-') . '</td>
			</tr>
			<tr>
				<td><strong>' . __('Application Date') . ':</strong></td>
				<td>' . ConvertSQLDate($ApplicantDetails['applicationdate']) . '</td>
				<td><strong>' . __('Status') . ':</strong></td>
				<td><strong>' . htmlspecialchars($ApplicantDetails['status'] ?? 'New', ENT_QUOTES, 'UTF-8') . '</strong></td>
			</tr>
		</table>
	</div>';

// Handle adding activity
if (isset($_POST['AddActivity'])) {
	$ActivityDate = FormatDateForSQL($_POST['ActivityDate']);
	$ActivityType = $_POST['ActivityType'];
	$Description = $_POST['Description'];
	$Interviewer = $_POST['Interviewer'];
	$Rating = isset($_POST['Rating']) && $_POST['Rating'] != '' ? (int)$_POST['Rating'] : 'NULL';
	$NextSteps = $_POST['NextSteps'];

	$SQL = "INSERT INTO hrapplicantactivities (
			applicantid,
			activitydate,
			activitytype,
			description,
			interviewer,
			rating,
			nextsteps,
			createdby,
			createddate
		) VALUES (
			" . $ApplicantID . ",
			'" . $ActivityDate . "',
			'" . $ActivityType . "',
			'" . $Description . "',
			'" . $Interviewer . "',
			" . $Rating . ",
			'" . $NextSteps . "',
			'" . $_SESSION['UserID'] . "',
			NOW()
		)";

	$Result = DB_query($SQL);
	prnMsg(__('Activity has been recorded'), 'success');
}

// Add activity form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?ApplicantID=' . $ApplicantID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Add Activity') . '</legend>';

echo '<field>
		<label for="ActivityDate">' . __('Date') . ':</label>
		<input type="date" name="ActivityDate" value="' . date('Y-m-d') . '" required />
	</field>';

echo '<field>
		<label for="ActivityType">' . __('Activity Type') . ':</label>
		<select name="ActivityType" required>
			<option value="Phone Screen">' . __('Phone Screen') . '</option>
			<option value="Interview">' . __('Interview') . '</option>
			<option value="Reference Check">' . __('Reference Check') . '</option>
			<option value="Assessment">' . __('Assessment') . '</option>
			<option value="Background Check">' . __('Background Check') . '</option>
			<option value="Offer">' . __('Offer') . '</option>
			<option value="Note">' . __('Note') . '</option>
		</select>
	</field>';

echo '<field>
		<label for="Description">' . __('Description') . ':</label>
		<textarea name="Description" rows="3" cols="60" required></textarea>
	</field>';

echo '<field>
		<label for="Interviewer">' . __('Interviewer/Contact') . ':</label>
		<input type="text" name="Interviewer" size="40" value="' . $_SESSION['UsersRealName'] . '" />
	</field>';

echo '<field>
		<label for="Rating">' . __('Rating') . ' (1-5):</label>
		<input type="number" name="Rating" min="1" max="5" />
	</field>';

echo '<field>
		<label for="NextSteps">' . __('Next Steps') . ':</label>
		<textarea name="NextSteps" rows="2" cols="60"></textarea>
	</field>';

echo '</fieldset>';

echo '<div class="centre">
			<input type="submit" name="AddActivity" value="' . __('Add Activity') . '" />
		</div>';

echo '</form>';

// Display activity timeline
$SQL = "SELECT * FROM hrapplicantactivities
		WHERE applicantid = " . $ApplicantID . "
		ORDER BY activitydate DESC, createddate DESC";

$Result = DB_query($SQL);


if (DB_num_rows($Result) > 0) {
	echo '<table class="selection">
			<thead>
				<tr>
					<th colspan="6"><h3>' . __('Activity Timeline') . '</h3></th
				</tr>
				<tr>
					<th>' . __('Date') . '</th>
					<th>' . __('Activity Type') . '</th>
					<th>' . __('Description') . '</th>
					<th>' . __('Interviewer') . '</th>
					<th>' . __('Rating') . '</th>
					<th>' . __('Next Steps') . '</th>
				</tr>
			</thead>
			<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>' . ConvertSQLDate($MyRow['activitydate']) . '</td>
				<td>' . htmlspecialchars($MyRow['activitytype'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . nl2br(htmlspecialchars($MyRow['description'], ENT_QUOTES, 'UTF-8')) . '</td>
				<td>' . htmlspecialchars($MyRow['interviewer'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . ($MyRow['rating'] ? $MyRow['rating'] . '/5' : '-') . '</td>
				<td>' . nl2br(htmlspecialchars($MyRow['nextsteps'], ENT_QUOTES, 'UTF-8')) . '</td>
			</tr>';
	}
} else {
	echo '<div class="centre">' . __('No activities recorded yet') . '</div>';
}

echo '</tbody>
	</table>';

include(__DIR__ . '/includes/footer.php');

?>
