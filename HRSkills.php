<?php

/* Skill Catalog Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Skill Catalog');
$ViewTopic = 'HumanResources';
$BookMark = 'HRSkills';

include(__DIR__ . '/includes/header.php');

echo '<a class="toplink" href="' . $RootPath . '/HRDashboard.php">' . __('Return to HR Dashboard') . '</a>';

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/award.png" title="' . __('Skills') . '" /> ' .
		__('Skill Catalog') . '
	</p>';

// Handle form submission
if (isset($_POST['Submit'])) {

	$InputError = 0;

	// Validation
	if (!isset($_POST['SkillCode']) || $_POST['SkillCode'] == '') {
		$InputError = 1;
		prnMsg(__('Skill code is required'), 'error');
	}
	if (!isset($_POST['SkillName']) || $_POST['SkillName'] == '') {
		$InputError = 1;
		prnMsg(__('Skill name is required'), 'error');
	}

	if ($InputError == 0) {

		$SkillCode = $_POST['SkillCode'];
		$SkillName = $_POST['SkillName'];
		$Description = $_POST['Description'];
		$Category = $_POST['Category'];
		$IsActive = isset($_POST['IsActive']) ? 1 : 0;

		if (isset($_GET['SkillID']) && $_GET['SkillID'] != '') {
			// Update existing skill
			$SkillID = (int)$_GET['SkillID'];

			$SQL = "UPDATE hrskills SET
					skillcode = '" . $SkillCode . "',
					skillname = '" . $SkillName . "',
					description = '" . $Description . "',
					skillcategory = '" . $Category . "',
					active = " . $IsActive . "
				WHERE skillid = " . $SkillID;

			$ErrMsg = __('Failed to update skill');
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(__('Skill has been updated'), 'success');

			unset($_GET['SkillID']);

		} else {
			// Check for duplicate code
			$SQL = "SELECT skillid FROM hrskills WHERE skillcode = '" . $SkillCode . "'";
			$Result = DB_query($SQL);

			if (DB_num_rows($Result) > 0) {
				prnMsg(__('A skill with this code already exists'), 'error');
				$InputError = 1;
			}

			if ($InputError == 0) {
				// Insert new skill
				$SQL = "INSERT INTO hrskills (
						skillcode,
						skillname,
						description,
						skillcategory,
						active
					) VALUES (
						'" . $SkillCode . "',
						'" . $SkillName . "',
						'" . $Description . "',
						'" . $Category . "',
						" . $IsActive . "
					)";

				$ErrMsg = __('Failed to insert skill');
				$Result = DB_query($SQL, $ErrMsg);

				prnMsg(__('Skill has been created'), 'success');
			}
		}
	}
}

// Handle delete
if (isset($_GET['Delete']) && isset($_GET['SkillID'])) {
	$SkillID = (int)$_GET['SkillID'];

	$SQL = "DELETE FROM hrskills WHERE skillid = " . $SkillID;
	$Result = DB_query($SQL);

	prnMsg(__('Skill has been deleted'), 'success');

	unset($_GET['SkillID']);
}

// Load existing data for edit
if (isset($_GET['SkillID'])) {
	$SkillID = (int)$_GET['SkillID'];

	$SQL = "SELECT * FROM hrskills WHERE skillid = " . $SkillID;
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		prnMsg(__('Skill not found'), 'error');
		unset($_GET['SkillID']);
	} else {
		$MyRow = DB_fetch_array($Result);

		$SkillCode = $MyRow['skillcode'];
		$SkillName = $MyRow['skillname'];
		$Description = $MyRow['description'];
		$Category = $MyRow['skillcategory'];
		$IsActive = $MyRow['active'];
	}
}

// Entry form
if (!isset($SkillCode)) {
	$SkillCode = '';
	$SkillName = '';
	$Description = '';
	$Category = '';
	$IsActive = 1;
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . (isset($_GET['SkillID']) ? '?SkillID=' . urlencode($_GET['SkillID']) : '') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . (isset($_GET['SkillID']) ? __('Edit Skill') : __('Add New Skill')) . '</legend>';

echo '<field>
		<label for="SkillCode">' . __('Skill Code') . ':</label>
		<input type="text" name="SkillCode" required size="20" maxlength="20" value="' . htmlspecialchars($SkillCode, ENT_QUOTES, 'UTF-8') . '"' . (isset($_GET['SkillID']) ? ' readonly' : '') . ' />
	</field>';

echo '<field>
		<label for="SkillName">' . __('Skill Name') . ':</label>
		<input type="text" name="SkillName" required size="60" maxlength="200" value="' . htmlspecialchars($SkillName, ENT_QUOTES, 'UTF-8') . '" />
	</field>';

echo '<field>
		<label for="Description">' . __('Description') . ':</label>
		<textarea name="Description" rows="4" cols="60">' . htmlspecialchars($Description, ENT_QUOTES, 'UTF-8') . '</textarea>
	</field>';

echo '<field>
		<label for="Category">' . __('Category') . ':</label>
		<select name="Category">
			<option value="">' . __('Select Category') . '</option>
			<option value="Technical"' . ($Category == 'Technical' ? ' selected' : '') . '>' . __('Technical') . '</option>
			<option value="Leadership"' . ($Category == 'Leadership' ? ' selected' : '') . '>' . __('Leadership') . '</option>
			<option value="Communication"' . ($Category == 'Communication' ? ' selected' : '') . '>' . __('Communication') . '</option>
			<option value="Problem Solving"' . ($Category == 'Problem Solving' ? ' selected' : '') . '>' . __('Problem Solving') . '</option>
			<option value="Teamwork"' . ($Category == 'Teamwork' ? ' selected' : '') . '>' . __('Teamwork') . '</option>
			<option value="Customer Service"' . ($Category == 'Customer Service' ? ' selected' : '') . '>' . __('Customer Service') . '</option>
			<option value="Other"' . ($Category == 'Other' ? ' selected' : '') . '>' . __('Other') . '</option>
		</select>
	</field>';

echo '<field>
		<label for="IsActive">' . __('Active') . ':</label>
		<input type="checkbox" name="IsActive" value="1"' . ($IsActive ? ' checked' : '') . ' />
	</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Save Skill') . '" />
	</div>';

echo '</form>';

// Search and filter
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Search Skills') . '</legend>
		<field>
			<label for="Keywords">' . __('Keywords') . ':</label>
			<input type="text" name="Keywords" size="30" value="' . (isset($_POST['Keywords']) ? $_POST['Keywords'] : '') . '" />
		</field>
		<field>
			<label for="CategoryFilter">' . __('Category') . ':</label>
			<select name="CategoryFilter">
				<option value="">' . __('All Categories') . '</option>
				<option value="Technical"' . (isset($_POST['CategoryFilter']) && $_POST['CategoryFilter'] == 'Technical' ? ' selected' : '') . '>' . __('Technical') . '</option>
				<option value="Leadership"' . (isset($_POST['CategoryFilter']) && $_POST['CategoryFilter'] == 'Leadership' ? ' selected' : '') . '>' . __('Leadership') . '</option>
				<option value="Communication"' . (isset($_POST['CategoryFilter']) && $_POST['CategoryFilter'] == 'Communication' ? ' selected' : '') . '>' . __('Communication') . '</option>
				<option value="Problem Solving"' . (isset($_POST['CategoryFilter']) && $_POST['CategoryFilter'] == 'Problem Solving' ? ' selected' : '') . '>' . __('Problem Solving') . '</option>
				<option value="Teamwork"' . (isset($_POST['CategoryFilter']) && $_POST['CategoryFilter'] == 'Teamwork' ? ' selected' : '') . '>' . __('Teamwork') . '</option>
				<option value="Customer Service"' . (isset($_POST['CategoryFilter']) && $_POST['CategoryFilter'] == 'Customer Service' ? ' selected' : '') . '>' . __('Customer Service') . '</option>
				<option value="Other"' . (isset($_POST['CategoryFilter']) && $_POST['CategoryFilter'] == 'Other' ? ' selected' : '') . '>' . __('Other') . '</option>
			</select>
		</field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Search" value="' . __('Search') . '" />
		</div>';

echo '</form>';

// Build query
$SQL = "SELECT * FROM hrskills WHERE 1=1";

if (isset($_POST['Keywords']) && $_POST['Keywords'] != '') {
	$Keywords = $_POST['Keywords'];
	$SQL .= " AND (skillcode LIKE '%" . $Keywords . "%'
			OR skillname LIKE '%" . $Keywords . "%'
			OR description LIKE '%" . $Keywords . "%')";
}

if (isset($_POST['CategoryFilter']) && $_POST['CategoryFilter'] != '') {
	$Category = $_POST['CategoryFilter'];
	$SQL .= " AND skillcategory = '" . $Category . "'";
}

$SQL .= " ORDER BY skillcategory, skillname";

$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {

	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . __('Code') . '</th>
					<th>' . __('Skill Name') . '</th>
					<th>' . __('Category') . '</th>
					<th>' . __('Description') . '</th>
					<th>' . __('Active') . '</th>
					<th>' . __('Actions') . '</th>
				</tr>
			</thead>
			<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>' . htmlspecialchars($MyRow['skillcode'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($MyRow['skillname'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($MyRow['skillcategory'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars(mb_substr($MyRow['description'], 0, 100), ENT_QUOTES, 'UTF-8') . (mb_strlen($MyRow['description']) > 100 ? '...' : '') . '</td>
				<td>' . ($MyRow['active'] ? __('Yes') : __('No')) . '</td>
				<td class="centre">
					<a href="' . $_SERVER['PHP_SELF'] . '?SkillID=' . urlencode($MyRow['skillid']) . '">' . __('Edit') . '</a> |
					<a href="' . $_SERVER['PHP_SELF'] . '?SkillID=' . urlencode($MyRow['skillid']) . '&Delete=1" onclick="return confirm(\'' . __('Are you sure you want to delete this skill?') . '\');">' . __('Delete') . '</a>
				</td>
			</tr>';
	}
} else {
	echo '<div class="centre">' . __('No skills found') . '</div>';
}

echo '</tbody>
	</table>';

include(__DIR__ . '/includes/footer.php');

?>
