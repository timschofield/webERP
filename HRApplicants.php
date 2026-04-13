<?php

/* Applicants Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Applicants');
$ViewTopic = 'HumanResources';
$BookMark = 'Applicants';

include(__DIR__ . '/includes/header.php');

echo '<a class="toplink" href="' . $RootPath . '/HRDashboard.php">' . __('Return to HR Dashboard') . '</a>';

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/user.png" title="' . __('Applicants') . '" /> ' .
		__('Job Applicants Management') . '
	</p>';

// Handle form submission
if (isset($_POST['Submit'])) {

	$InputError = 0;

	if (!isset($_POST['FirstName']) || $_POST['FirstName'] == '') {
		$InputError = 1;
		prnMsg(__('First name is required'), 'error');
	}
	if (!isset($_POST['LastName']) || $_POST['LastName'] == '') {
		$InputError = 1;
		prnMsg(__('Last name is required'), 'error');
	}
	if (!isset($_POST['Email']) || $_POST['Email'] == '') {
		$InputError = 1;
		prnMsg(__('Email is required'), 'error');
	}

	if ($InputError == 0) {

		$FirstName = $_POST['FirstName'];
		$LastName = $_POST['LastName'];
		$Email = $_POST['Email'];
		$Phone = $_POST['Phone'];
		$ApplicationDate = FormatDateForSQL($_POST['ApplicationDate']);
		$Source = $_POST['Source'];
		$OverallStatus = $_POST['OverallStatus'];
		$Resume = $_POST['Resume'];
		$CoverLetter = $_POST['CoverLetter'];
		$Status = $_POST['Status'];

		if (isset($_GET['ApplicantID']) && $_GET['ApplicantID'] != '') {
			// Update
			$ApplicantID = (int)$_GET['ApplicantID'];

			$SQL = "UPDATE hrapplicants SET
					firstname = '" . $FirstName . "',
					lastname = '" . $LastName . "',
					email = '" . $Email . "',
					phone = '" . $Phone . "',
					applicationdate = '" . $ApplicationDate . "',
					source = '" . $Source . "',
					overallstatus = '" . $OverallStatus . "',
					resumefile = '" . $Resume . "',
					coverletter = '" . $CoverLetter . "'
				WHERE applicantid = " . $ApplicantID;

			$Result = DB_query($SQL);
			prnMsg(__('Applicant has been updated'), 'success');

		} else {
			// Insert
			$SQL = "INSERT INTO hrapplicants (
					firstname,
					lastname,
					email,
					phone,
					applicationdate,
					source,
					overallstatus,
					resumefile,
					coverletter
				) VALUES (
					'" . $FirstName . "',
					'" . $LastName . "',
					'" . $Email . "',
					'" . $Phone . "',
					'" . $ApplicationDate . "',
					'" . $Source . "',
					'" . $OverallStatus . "',
					'" . $Resume . "',
					'" . $CoverLetter . "'
				)";

			$Result = DB_query($SQL);
			prnMsg(__('Applicant has been created'), 'success');
		}
	}
}

// Handle delete
if (isset($_GET['Delete']) && isset($_GET['ApplicantID'])) {
	$ApplicantID = (int)$_GET['ApplicantID'];

	$SQL = "DELETE FROM hrapplicants WHERE applicantid = " . $ApplicantID;
	$Result = DB_query($SQL);

	prnMsg(__('Applicant has been deleted'), 'success');
	unset($_GET['ApplicantID']);
}

// Load for edit
if (isset($_GET['ApplicantID'])) {
	$ApplicantID = (int)$_GET['ApplicantID'];

	$SQL = "SELECT * FROM hrapplicants WHERE applicantid = " . $ApplicantID;
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);

		$FirstName = $MyRow['firstname'];
		$LastName = $MyRow['lastname'];
		$Email = $MyRow['email'];
		$Phone = $MyRow['phone'];
		$ApplicationDate = ConvertSQLDate($MyRow['applicationdate']);
		$Source = $MyRow['source'];
		$OverallStatus = $MyRow['overallstatus'];
		$Resume = $MyRow['resumefile'];
		$CoverLetter = $MyRow['coverletter'];
		$Status = $MyRow['status'];
	}
}

// Entry form
if (!isset($FirstName)) {
	$FirstName = '';
	$LastName = '';
	$Email = '';
	$Phone = '';
	$ApplicationDate = date($_SESSION['DefaultDateFormat']);
	$Source = '';
	$OverallStatus = 'New';
	$Resume = '';
	$CoverLetter = '';
	$Status = '';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . (isset($_GET['ApplicantID']) ? '?ApplicantID=' . urlencode($_GET['ApplicantID']) : '') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . (isset($_GET['ApplicantID']) ? __('Edit Applicant') : __('Add New Applicant')) . '</legend>';

echo '<field>
		<label for="FirstName">' . __('First Name') . ':</label>
		<input type="text" name="FirstName" size="30" maxlength="100" value="' . htmlspecialchars($FirstName, ENT_QUOTES, 'UTF-8') . '" required />
	</field>';

echo '<field>
		<label for="LastName">' . __('Last Name') . ':</label>
		<input type="text" name="LastName" size="30" maxlength="100" value="' . htmlspecialchars($LastName, ENT_QUOTES, 'UTF-8') . '" required />
	</field>';

echo '<field>
		<label for="Email">' . __('Email') . ':</label>
		<input type="email" name="Email" size="40" maxlength="200" value="' . htmlspecialchars($Email, ENT_QUOTES, 'UTF-8') . '" required />
	</field>';

echo '<field>
		<label for="Phone">' . __('Phone') . ':</label>
		<input type="tel" name="Phone" size="20" maxlength="50" value="' . htmlspecialchars($Phone, ENT_QUOTES, 'UTF-8') . '" />
	</field>';

echo '<field>
		<label for="ApplicationDate">' . __('Application Date') . ':</label>
		<input type="date" name="ApplicationDate" value="' . FormatDateForSQL($ApplicationDate) . '" required />
	</field>';

echo '<field>
		<label for="Source">' . __('Source') . ':</label>
		<select name="Source">
			<option value="">' . __('Select Source') . '</option>
			<option value="Company Website"' . ($Source == 'Company Website' ? ' selected' : '') . '>' . __('Company Website') . '</option>
			<option value="Job Board"' . ($Source == 'Job Board' ? ' selected' : '') . '>' . __('Job Board') . '</option>
			<option value="Referral"' . ($Source == 'Referral' ? ' selected' : '') . '>' . __('Referral') . '</option>
			<option value="Recruiter"' . ($Source == 'Recruiter' ? ' selected' : '') . '>' . __('Recruiter') . '</option>
			<option value="Social Media"' . ($Source == 'Social Media' ? ' selected' : '') . '>' . __('Social Media') . '</option>
			<option value="Other"' . ($Source == 'Other' ? ' selected' : '') . '>' . __('Other') . '</option>
		</select>
	</field>';

echo '<field>
		<label for="Status">' . __('Status') . ':</label>
		<select name="Status" required>
			<option value="Applied"' . ($Status == 'Applied' ? ' selected' : '') . '>' . __('Applied') . '</option>
			<option value="Screening"' . ($Status == 'Screening' ? ' selected' : '') . '>' . __('Screening') . '</option>
			<option value="Interview Scheduled"' . ($Status == 'Interview Scheduled' ? ' selected' : '') . '>' . __('Interview Scheduled') . '</option>
			<option value="Interviewed"' . ($Status == 'Interviewed' ? ' selected' : '') . '>' . __('Interviewed') . '</option>
			<option value="Offer Extended"' . ($Status == 'Offer Extended' ? ' selected' : '') . '>' . __('Offer Extended') . '</option>
			<option value="Offer Accepted"' . ($Status == 'Offer Accepted' ? ' selected' : '') . '>' . __('Offer Accepted') . '</option>
			<option value="Offer Declined"' . ($Status == 'Offer Declined' ? ' selected' : '') . '>' . __('Offer Declined') . '</option>
			<option value="Rejected"' . ($Status == 'Rejected' ? ' selected' : '') . '>' . __('Rejected') . '</option>
			<option value="Withdrawn"' . ($Status == 'Withdrawn' ? ' selected' : '') . '>' . __('Withdrawn') . '</option>
		</select>
	</field>';

echo '<field>
		<label for="Resume">' . __('Resume/CV Link') . ':</label>
		<input type="url" name="Resume" size="60" maxlength="500" value="' . htmlspecialchars($Resume, ENT_QUOTES, 'UTF-8') . '" />
	</field>';

echo '<field>
		<label for="CoverLetter">' . __('Cover Letter') . ':</label>
		<textarea name="CoverLetter" rows="4" cols="60">' . htmlspecialchars($CoverLetter, ENT_QUOTES, 'UTF-8') . '</textarea>
	</field>';

echo '</fieldset>';
echo '<div class="centre">
			<input type="submit" name="Submit" value="' . __('Save Applicant') . '" />
		</div>';

echo '</form>';

// List applicants
$SQL = "SELECT
		a.applicantid,
		a.firstname,
		a.lastname,
		a.email,
		a.phone,
		a.applicationdate,
		a.source,
		a.overallstatus
	FROM hrapplicants a
	ORDER BY a.applicationdate DESC";

$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {

	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . __('ID') . '</th>
					<th>' . __('Name') . '</th>
					<th>' . __('Email') . '</th>
					<th>' . __('Phone') . '</th>
					<th>' . __('Application Date') . '</th>
					<th>' . __('Source') . '</th>
					<th>' . __('Status') . '</th>
					<th>' . __('Actions') . '</th>
				</tr>
			</thead>
			<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>' . $MyRow['applicantid'] . '</td>
				<td>' . htmlspecialchars($MyRow['firstname'] . ' ' . $MyRow['lastname'], ENT_QUOTES, 'UTF-8') . '</td>
				<td><a href="mailto:' . htmlspecialchars($MyRow['email'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($MyRow['email'], ENT_QUOTES, 'UTF-8') . '</a></td>
				<td>' . htmlspecialchars($MyRow['phone'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . ConvertSQLDate($MyRow['applicationdate']) . '</td>
				<td>' . htmlspecialchars($MyRow['source'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($MyRow['overallstatus'], ENT_QUOTES, 'UTF-8') . '</td>
				<td class="centre">
					<a href="' . $_SERVER['PHP_SELF'] . '?ApplicantID=' . urlencode($MyRow['applicantid']) . '">' . __('Edit') . '</a> |
					<a href="' . $RootPath . '/HRApplicantTracking.php?ApplicantID=' . urlencode($MyRow['applicantid']) . '">' . __('Track') . '</a> |
					<a href="' . $_SERVER['PHP_SELF'] . '?ApplicantID=' . urlencode($MyRow['applicantid']) . '&Delete=1" onclick="return confirm(\'' . __('Delete this applicant?') . '\');">' . __('Delete') . '</a>
				</td>
			</tr>';
	}
} else {
	echo '<div class="centre">' . __('No applicants found') . '</div>';
}

echo '</tbody>
	</table>';

include(__DIR__ . '/includes/footer.php');

?>
