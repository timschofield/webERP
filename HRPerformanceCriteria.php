<?php

/* HR Performance Criteria Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Performance Criteria');
$ViewTopic = 'HumanResources';
$BookMark = 'HRPerformanceCriteria';

include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/award.png" title="' . __('Performance Criteria') . '" /> ' .
		__('Performance Evaluation Criteria') . '
	</p>';

// Handle form submission
if (isset($_POST['Submit'])) {
	$InputError = 0;

	if (trim($_POST['CriteriaName']) == '') {
		$InputError = 1;
		prnMsg(__('The criteria name must not be empty'), 'error');
	}

	if ($InputError != 1) {
		if (isset($_POST['CriteriaID']) && $_POST['CriteriaID'] > 0) {
			// Update existing criteria
			$SQL = "UPDATE hrperformancecriteria SET
						criterianame = '" . $_POST['CriteriaName'] . "',
						description = '" . $_POST['Description'] . "',
						category = '" . $_POST['Category'] . "',
						weight = " . filter_var($_POST['Weight'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						displayorder = " . (int)$_POST['DisplayOrder'] . ",
						isactive = " . (isset($_POST['Active']) ? 1 : 0) . ",
						modifiedby = '" . $_SESSION['UserID'] . "',
						modifieddate = NOW()
					WHERE criteriaid = " . (int)$_POST['CriteriaID'];

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Performance criteria has been updated successfully'), 'success');
			}
		} else {
			// Insert new criteria
			$SQL = "INSERT INTO hrperformancecriteria (
						criterianame, description, category, weight, displayorder, isactive,
						createdby, createddate
					) VALUES (
						'" . $_POST['CriteriaName'] . "',
						'" . $_POST['Description'] . "',
						'" . $_POST['Category'] . "',
						" . filter_var($_POST['Weight'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						" . (int)$_POST['DisplayOrder'] . ",
						" . (isset($_POST['Active']) ? 1 : 0) . ",
						'" . $_SESSION['UserID'] . "',
						NOW()
					)";

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Performance criteria has been created successfully'), 'success');
			}
		}
		unset($_POST['CriteriaID']);
	}
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['CriteriaID'])) {
	$SQL = "DELETE FROM hrperformancecriteria WHERE criteriaid = " . (int)$_GET['CriteriaID'];
	$Result = DB_query($SQL);
	if ($Result) {
		prnMsg(__('Performance criteria has been deleted successfully'), 'success');
	}
}

// Add/Edit form - show first
$CriteriaID = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
if (true) {
	$CriteriaName = '';
	$Description = '';
	$Category = 'Core Skills';
	$Weight = 0;
	$DisplayOrder = 0;
	$Active = 1;

	if ($CriteriaID > 0) {
		$SQL = "SELECT * FROM hrperformancecriteria WHERE criteriaid = " . $CriteriaID;
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) > 0) {
			$Row = DB_fetch_array($Result);
			$CriteriaName = $Row['criterianame'];
			$Description = $Row['description'];
			$Category = $Row['category'];
			$Weight = $Row['weight'];
			$DisplayOrder = $Row['displayorder'];
			$Active = $Row['isactive'];
		}
	}

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br /><fieldset>
			<legend>' . ($CriteriaID > 0 ? __('Edit Performance Criteria') : __('Add Performance Criteria')) . '</legend>';

	if ($CriteriaID > 0) {
		echo '<input type="hidden" name="CriteriaID" value="' . $CriteriaID . '" />';
	}

	echo '<field>
				<label for="CriteriaName">' . __('Criteria Name') . ':</label>
				<input type="text" name="CriteriaName" value="' . htmlspecialchars($CriteriaName) . '" size="50" maxlength="100" required="required" />
			</field>

			<field>
				<label for="Description">' . __('Description') . ':</label>
				<textarea name="Description" rows="3" cols="50">' . htmlspecialchars($Description) . '</textarea>
			</field>

			<field>
				<label for="Category">' . __('Category') . ':</label>
				<select name="Category">
					<option value="Core Skills"' . ($Category == 'Core Skills' ? ' selected="selected"' : '') . '>' . __('Core Skills') . '</option>
					<option value="Technical Skills"' . ($Category == 'Technical Skills' ? ' selected="selected"' : '') . '>' . __('Technical Skills') . '</option>
					<option value="Leadership"' . ($Category == 'Leadership' ? ' selected="selected"' : '') . '>' . __('Leadership') . '</option>
					<option value="Communication"' . ($Category == 'Communication' ? ' selected="selected"' : '') . '>' . __('Communication') . '</option>
					<option value="Teamwork"' . ($Category == 'Teamwork' ? ' selected="selected"' : '') . '>' . __('Teamwork') . '</option>
					<option value="Job-Specific"' . ($Category == 'Job-Specific' ? ' selected="selected"' : '') . '>' . __('Job-Specific') . '</option>
					<option value="Other"' . ($Category == 'Other' ? ' selected="selected"' : '') . '>' . __('Other') . '</option>
				</select>
			</field>

			<field>
				<label for="Weight">' . __('Weight (%)') . ':</label>
				<input type="number" name="Weight" value="' . $Weight . '" step="0.1" min="0" max="100" />
				<small>' . __('Used for weighted scoring calculations') . '</small>
			</field>

			<field>
				<label for="DisplayOrder">' . __('Display Order') . ':</label>
				<input type="number" name="DisplayOrder" value="' . $DisplayOrder . '" min="0" />
			</field>

			<field>
				<label for="Active">' . __('Active') . ':</label>
				<input type="checkbox" name="Active" value="1"' . ($Active ? ' checked="checked"' : '') . ' />
			</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="Submit" value="' . __('Save') . '" />
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Cancel') . '</a>
		</div>
		</form>';
}

// Display criteria list by category
$SQL = "SELECT * FROM hrperformancecriteria ORDER BY category, displayorder, criterianame";
$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	echo '<p>' . __('No performance criteria defined') . '</p>';
} else {
	$CurrentCategory = '';
	while ($Row = DB_fetch_array($Result)) {
		if ($Row['category'] != $CurrentCategory) {
			if ($CurrentCategory != '') {
				echo '</table><br />';
			}
			$CurrentCategory = $Row['category'];
			echo '<h3>' . __($CurrentCategory) . '</h3>';
			echo '<table class="selection">
					<tr>
						<th>' . __('Order') . '</th>
						<th>' . __('Criteria Name') . '</th>
						<th>' . __('Description') . '</th>
						<th>' . __('Weight') . '</th>
						<th>' . __('Active') . '</th>
						<th>' . __('Actions') . '</th>
					</tr>';
		}

		echo '<tr' . (!$Row['isactive'] ? ' style="background-color: #f0f0f0; opacity: 0.7;"' : '') . '>
				<td>' . $Row['displayorder'] . '</td>
				<td><strong>' . $Row['criterianame'] . '</strong></td>
				<td>' . $Row['description'] . '</td>
				<td class="number">' . number_format($Row['weight'], 1) . '%</td>
				<td>' . ($Row['isactive'] ? __('Yes') : __('No')) . '</td>
				<td>
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?edit=' . $Row['criteriaid'] . '">' . __('Edit') . '</a> |
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?delete=1&CriteriaID=' . $Row['criteriaid'] . '" onclick="return confirm(\'' . __('Are you sure you want to delete this criteria?') . '\');">' . __('Delete') . '</a>
				</td>
			</tr>';
	}
	echo '</table>';
}

include(__DIR__ . '/includes/footer.php');

?>
