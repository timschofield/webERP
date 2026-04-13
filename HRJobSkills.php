<?php

/* Job Skills Mapping */

require(__DIR__ . '/includes/session.php');

$Title = __('Job Skills');
$ViewTopic = 'HumanResources';
$BookMark = 'HRJobSkills';

include(__DIR__ . '/includes/header.php');

echo '<a class="toplink" href="' . $RootPath . '/HRDashboard.php">' . __('Return to HR Dashboard') . '</a>';

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/award.png" title="' . __('Job Skills') . '" /> ' .
		__('Job Skills Mapping') . '
	</p>';

// Handle form submission
if (isset($_POST['Submit'])) {

	$InputError = 0;

	// Validation
	if (!isset($_POST['PositionID']) || $_POST['PositionID'] == '') {
		$InputError = 1;
		prnMsg(__('Position must be selected'), 'error');
	}
	if (!isset($_POST['SkillID']) || $_POST['SkillID'] == '') {
		$InputError = 1;
		prnMsg(__('Skill must be selected'), 'error');
	}

	if ($InputError == 0) {

		$PositionID = (int)$_POST['PositionID'];
		$SkillID = (int)$_POST['SkillID'];
		$RequiredLevel = (int)$_POST['RequiredLevel'];
		$Importance = $_POST['Importance'];

		// Check for duplicate
		$SQL = "SELECT * FROM hrjobskills
				WHERE positionid = " . $PositionID . "
				AND skillid = " . $SkillID;
		$Result = DB_query($SQL);

		if (DB_num_rows($Result) > 0) {
			prnMsg(__('This skill is already mapped to this position'), 'error');
		} else {
			// Insert new mapping
			$SQL = "INSERT INTO hrjobskills (
					positionid,
					skillid,
					requiredlevel,
					importance
				) VALUES (
					" . $PositionID . ",
					" . $SkillID . ",
					" . $RequiredLevel . ",
					'" . $Importance . "'
				)";

			$ErrMsg = __('Failed to map skill');
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(__('Skill has been mapped to position'), 'success');
		}
	}
}

// Handle delete
if (isset($_GET['Delete']) && isset($_GET['MapID'])) {
	$MapID = (int)$_GET['MapID'];

	$SQL = "DELETE FROM hrjobskills WHERE jobskillid = " . $MapID;
	$Result = DB_query($SQL);

	prnMsg(__('Skill mapping has been removed'), 'success');
}

// Position selection form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Select Position') . '</legend>
		<field>
			<label for="ViewPositionID">' . __('Position') . ':</label>
			<select name="ViewPositionID" onchange="this.form.submit()">
				<option value="">' . __('Select Position') . '</option>';

$SQL = "SELECT positionid, positioncode, positiontitle FROM hrpositions ORDER BY positioncode";
$Result = DB_query($SQL);
while ($PosRow = DB_fetch_array($Result)) {
	$Selected = (isset($_POST['ViewPositionID']) && $_POST['ViewPositionID'] == $PosRow['positionid']) ? ' selected' : '';
	echo '<option value="' . $PosRow['positionid'] . '"' . $Selected . '>' .
		htmlspecialchars($PosRow['positioncode'] . ' - ' . $PosRow['positiontitle'], ENT_QUOTES, 'UTF-8') . '</option>';
}

echo '</select>
		</field>
	</fieldset>';
echo '</form>';

// If position is selected, show skills and add form
if (isset($_POST['ViewPositionID']) && $_POST['ViewPositionID'] != '') {

	$ViewPositionID = (int)$_POST['ViewPositionID'];

	// Get position details
	$SQL = "SELECT * FROM hrpositions WHERE positionid = " . $ViewPositionID;
	$Result = DB_query($SQL);
	$PosDetails = DB_fetch_array($Result);

	echo '<h3>' . __('Skills for') . ': ' . htmlspecialchars($PosDetails['positiontitle'], ENT_QUOTES, 'UTF-8') . '</h3>';

	// Add skill form
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="ViewPositionID" value="' . $ViewPositionID . '" />';
	echo '<input type="hidden" name="PositionID" value="' . $ViewPositionID . '" />';

	echo '<fieldset>
			<legend>' . __('Add Skill') . '</legend>';

	echo '<field>
			<label for="SkillID">' . __('Skill') . ':</label>
			<select name="SkillID" required>
				<option value="">' . __('Select Skill') . '</option>';

	$SQL = "SELECT skillid, skillcode, skillname
			FROM hrskills
			ORDER BY skillname";
	$Result = DB_query($SQL);
	while ($CompRow = DB_fetch_array($Result)) {
		echo '<option value="' . $CompRow['skillid'] . '">' .
			htmlspecialchars($CompRow['skillcode'] . ' - ' . $CompRow['skillname'], ENT_QUOTES, 'UTF-8') . '</option>';
	}

	echo '</select>
		</field>';

	echo '<field>
			<label for="RequiredLevel">' . __('Required Level') . ' (1-5):</label>
			<input type="number" name="RequiredLevel" min="1" max="5" value="3" required />
		</field>';

	echo '<field>
			<label for="Importance">' . __('Importance') . ':</label>
			<select name="Importance" required>
				<option value="Essential">' . __('Essential') . '</option>
				<option value="Important" selected>' . __('Important') . '</option>
				<option value="Desired">' . __('Desired') . '</option>
			</select>
		</field>';

	echo '</fieldset>';
	echo '<div class="centre">
				<input type="submit" name="Submit" value="' . __('Add Skill') . '" />
			</div>';
	echo '</form>';

	// List existing skills for this position
	$SQL = "SELECT
			jc.jobskillid,
			jc.requiredlevel,
			jc.importance,
			c.skillcode,
			c.skillname,
			c.skillcategory
		FROM hrjobskills jc
		INNER JOIN hrskills c ON jc.skillid = c.skillid
		WHERE jc.positionid = " . $ViewPositionID . "
		ORDER BY c.skillname";

	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {

		echo '<table class="selection">
				<thead>
					<tr>
						<th>' . __('Code') . '</th>
						<th>' . __('Skill') . '</th>
						<th>' . __('Category') . '</th>
						<th>' . __('Required Level') . '</th>
						<th>' . __('Importance') . '</th>
						<th>' . __('Actions') . '</th>
					</tr>
				</thead>
				<tbody>';

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td>' . htmlspecialchars($MyRow['skillcode'], ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . htmlspecialchars($MyRow['skillname'], ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . htmlspecialchars($MyRow['skillcategory'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . $MyRow['requiredlevel'] . '</td>
					<td>' . htmlspecialchars($MyRow['importance'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
					<td class="centre">
						<a href="' . $_SERVER['PHP_SELF'] . '?MapID=' . urlencode($MyRow['jobskillid']) . '&Delete=1" onclick="return confirm(\'' . __('Remove this skill from the position?') . '\');">' . __('Remove') . '</a>
					</td>
				</tr>';
		}
	} else {
		echo '<div class="centre">' . __('No skills mapped to this position yet') . '</div>';
	}

	echo '</tbody>
		</table>';
}

include(__DIR__ . '/includes/footer.php');

?>
