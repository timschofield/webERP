<?php

/* Employee Skills Assessment */

require(__DIR__ . '/includes/session.php');

$Title = __('Employee Skills');
$ViewTopic = 'HumanResources';
$BookMark = 'HREmployeeSkills';

include(__DIR__ . '/includes/header.php');

echo '<a class="toplink" href="' . $RootPath . '/HRDashboard.php">' . __('Return to HR Dashboard') . '</a>';

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/award.png" title="' . __('Employee Skills') . '" /> ' .
		__('Employee Skills Assessment') . '
	</p>';

// Handle form submission
if (isset($_POST['Submit'])) {

	$InputError = 0;

	if (!isset($_POST['EmployeeNumber']) || $_POST['EmployeeNumber'] == '') {
		$InputError = 1;
		prnMsg(__('Employee must be selected'), 'error');
	}
	if (!isset($_POST['SkillID']) || $_POST['SkillID'] == '') {
		$InputError = 1;
		prnMsg(__('Skill must be selected'), 'error');
	}

	if ($InputError == 0) {

		$EmployeeNumber = $_POST['EmployeeNumber'];
		$SkillID = (int)$_POST['SkillID'];
		$CurrentLevel = (int)$_POST['CurrentLevel'];
		$AssessmentDate = FormatDateForSQL($_POST['AssessmentDate']);
		$AssessedBy = $_SESSION['UserID'];
		$Notes = $_POST['Notes'];

		// Get employeeid from employeenumber
		$SQL = "SELECT employeeid FROM hremployees WHERE employeenumber = '" . $EmployeeNumber . "'";
		$Result = DB_query($SQL);
		$EmpRow = DB_fetch_array($Result);
		$EmployeeID = $EmpRow['employeeid'];

		// Check for existing assessment
		$SQL = "SELECT * FROM hrempskills
				WHERE employeeid = " . $EmployeeID . "
				AND skillid = " . $SkillID;
		$Result = DB_query($SQL);

		if (DB_num_rows($Result) > 0) {
			// Update existing
			$SQL = "UPDATE hrempskills SET
					currentlevel = " . $CurrentLevel . ",
					assessmentdate = '" . $AssessmentDate . "',
					assessedby = '" . $AssessedBy . "',
					notes = '" . $Notes . "'
				WHERE employeeid = " . $EmployeeID . "
				AND skillid = " . $SkillID;

			$ErrMsg = __('Failed to update skill assessment');
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(__('Skill assessment has been updated'), 'success');
		} else {
			// Insert new
			$SQL = "INSERT INTO hrempskills (
					employeeid,
					skillid,
					currentlevel,
					assessmentdate,
					assessedby,
					notes
				) VALUES (
					" . $EmployeeID . ",
					" . $SkillID . ",
					" . $CurrentLevel . ",
					'" . $AssessmentDate . "',
					'" . $AssessedBy . "',
					'" . $Notes . "'
				)";

			$ErrMsg = __('Failed to insert skill assessment');
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(__('Skill assessment has been recorded'), 'success');
		}
	}
}

// Handle delete
if (isset($_GET['Delete']) && isset($_GET['EmpSkillID'])) {
	$EmpSkillID = (int)$_GET['EmpSkillID'];

	$SQL = "DELETE FROM hrempskills WHERE empskillid = " . $EmpSkillID;
	$Result = DB_query($SQL);

	prnMsg(__('Skill assessment has been deleted'), 'success');
}

// Employee selection form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Select Employee') . '</legend>
		<field>
			<label for="ViewEmployeeNumber">' . __('Employee') . ':</label>
			<select name="ViewEmployeeNumber" onchange="this.form.submit()">
				<option value="">' . __('Select Employee') . '</option>';

$SQL = "SELECT employeenumber, CONCAT(firstname, ' ', lastname) as name
		FROM hremployees
		WHERE employmentstatus = 'Active'
		ORDER BY lastname, firstname";
$Result = DB_query($SQL);
while ($EmpRow = DB_fetch_array($Result)) {
	$Selected = (isset($_POST['ViewEmployeeNumber']) && $_POST['ViewEmployeeNumber'] == $EmpRow['employeenumber']) ? ' selected' : '';
	echo '<option value="' . $EmpRow['employeenumber'] . '"' . $Selected . '>' .
		htmlspecialchars($EmpRow['name'] . ' (' . $EmpRow['employeenumber'] . ')', ENT_QUOTES, 'UTF-8') . '</option>';
}

echo '</select>
		</field>
	</fieldset>';
echo '</form>';

// If employee is selected, show skills and add form
if (isset($_POST['ViewEmployeeNumber']) && $_POST['ViewEmployeeNumber'] != '') {

	$ViewEmployeeNumber = $_POST['ViewEmployeeNumber'];

	// Get employee details
	$SQL = "SELECT * FROM hremployees WHERE employeenumber = '" . $ViewEmployeeNumber . "'";
	$Result = DB_query($SQL);
	$EmpDetails = DB_fetch_array($Result);

	echo '<h3>' . __('Skills for') . ': ' . htmlspecialchars($EmpDetails['firstname'] . ' ' . $EmpDetails['lastname'], ENT_QUOTES, 'UTF-8') . '</h3>';

	// Add/Edit skill assessment form
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="ViewEmployeeNumber" value="' . $ViewEmployeeNumber . '" />';
	echo '<input type="hidden" name="EmployeeNumber" value="' . $ViewEmployeeNumber . '" />';

	echo '<fieldset>
			<legend>' . __('Record Skill Assessment') . '</legend>';

	echo '<field>
			<label for="SkillID">' . __('Skill') . ':</label>
			<select name="SkillID" required>
				<option value="">' . __('Select Skill') . '</option>';

	$SQL = "SELECT skillid, skillcode, skillname
			FROM hrskills
			WHERE active = 1
			ORDER BY skillname";
	$Result = DB_query($SQL);
	while ($CompRow = DB_fetch_array($Result)) {
		echo '<option value="' . $CompRow['skillid'] . '">' .
			htmlspecialchars($CompRow['skillcode'] . ' - ' . $CompRow['skillname'], ENT_QUOTES, 'UTF-8') . '</option>';
	}

	echo '</select>
		</field>';

	echo '<field>
			<label for="CurrentLevel">' . __('Current Level') . ' (1-5):</label>
			<input type="number" name="CurrentLevel" min="1" max="5" value="3" required />
		</field>';

	echo '<field>
			<label for="AssessmentDate">' . __('Assessment Date') . ':</label>
			<input type="date" name="AssessmentDate" value="' . date('Y-m-d') . '" required />
		</field>';

	echo '<field>
			<label for="Notes">' . __('Notes') . ':</label>
			<textarea name="Notes" rows="3" cols="60"></textarea>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Submit" value="' . __('Save Assessment') . '" />
		</div>';

	echo '</form>';

	// List existing skill assessments
	$SQL = "SELECT
			ec.empskillid,
			ec.currentlevel,
			ec.assessmentdate,
			ec.assessedby,
			ec.notes,
			c.skillcode,
			c.skillname,
			c.skillcategory
		FROM hrempskills ec
		INNER JOIN hrskills c ON ec.skillid = c.skillid
		INNER JOIN hremployees e ON ec.employeeid = e.employeeid
		WHERE e.employeenumber = '" . $ViewEmployeeNumber . "'
		ORDER BY c.skillname";

	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {

		echo '<table class="selection">
				<thead>
					<tr>
						<th>' . __('Code') . '</th>
						<th>' . __('Skill') . '</th>
						<th>' . __('Category') . '</th>
						<th>' . __('Current Level') . '</th>
						<th>' . __('Assessment Date') . '</th>
						<th>' . __('Assessed By') . '</th>
						<th>' . __('Actions') . '</th>
					</tr>
				</thead>
				<tbody>';

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr>
					<td>' . htmlspecialchars($MyRow['skillcode'], ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . htmlspecialchars($MyRow['skillname'], ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . htmlspecialchars($MyRow['skillcategory'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . $MyRow['currentlevel'] . '</td>
					<td>' . ConvertSQLDate($MyRow['assessmentdate']) . '</td>
					<td>' . htmlspecialchars($MyRow['assessedby'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
					<td class="centre">
						<a href="' . $_SERVER['PHP_SELF'] . '?EmpSkillID=' . urlencode($MyRow['empskillid']) . '&Delete=1" onclick="return confirm(\'' . __('Delete this assessment?') . '\');">' . __('Delete') . '</a>
					</td>
				</tr>';

			if ($MyRow['notes']) {
				echo '<tr>
						<td colspan="7" style="padding-left: 30px; font-style: italic;">
							' . htmlspecialchars($MyRow['notes'], ENT_QUOTES, 'UTF-8') . '
						</td>
					</tr>';
			}
		}
	} else {
		echo '<div class="centre">' . __('No skill assessments recorded for this employee') . '</div>';
	}

	echo '</tbody>
		</table>';
}

include(__DIR__ . '/includes/footer.php');

?>
