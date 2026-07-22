<?php

/* HR Colleague Feedback Criteria Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Colleague Feedback Criteria');
$ViewTopic = 'HumanResources';
$BookMark = 'HRColleagueFeedbackCriteria';

include(__DIR__ . '/includes/header.php');

function ShowFeedbackWeightWarning(float $WeightSum) {
	echo '<div class="centre">
			<span class="WeightWarning">' .
			__('Warning: The sum of active criteria weights is') . ' ' . number_format($WeightSum, 1) . '%, ' . __('not 100%!') . '
			</span>
		</div>';
}

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/award.png" title="' . __('Colleague Feedback Criteria') . '" /> ' .
		__('Colleague Feedback Criteria') . '
	</p>';

/* Handle form submission */
if (isset($_POST['Submit'])) {
	$InputError = 0;

	if (!isset($_POST['CriteriaName']) || trim($_POST['CriteriaName']) == '') {
		$InputError = 1;
		prnMsg(__('The criteria name must not be empty'), 'error');
	}

	$CriteriaName = isset($_POST['CriteriaName']) ? DB_escape_string(trim($_POST['CriteriaName'])) : '';
	$Description = isset($_POST['Description']) ? DB_escape_string($_POST['Description']) : '';
	$Weight = isset($_POST['Weight']) ? filter_var($_POST['Weight'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
	$DisplayOrder = isset($_POST['DisplayOrder']) ? (int)$_POST['DisplayOrder'] : 0;
	$IsActive = isset($_POST['Active']) ? 1 : 0;

	if ($InputError != 1) {
		if (isset($_POST['CriteriaID']) && (int)$_POST['CriteriaID'] > 0) {
			$SQL = "UPDATE hrfeedbackcriteria SET
						criterianame = '" . $CriteriaName . "',
						description = '" . $Description . "',
						weight = " . $Weight . ",
						displayorder = " . $DisplayOrder . ",
						isactive = " . $IsActive . ",
						modifiedby = '" . DB_escape_string($_SESSION['UserID']) . "',
						modifieddate = NOW()
					WHERE criteriaid = " . (int)$_POST['CriteriaID'];

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Colleague feedback criteria has been updated successfully'), 'success');
			}
		} else {
			$SQL = "INSERT INTO hrfeedbackcriteria (
						criterianame,
						description,
						weight,
						displayorder,
						isactive,
						createdby,
						createddate
					) VALUES (
						'" . $CriteriaName . "',
						'" . $Description . "',
						" . $Weight . ",
						" . $DisplayOrder . ",
						" . $IsActive . ",
						'" . DB_escape_string($_SESSION['UserID']) . "',
						NOW()
					)";

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Colleague feedback criteria has been created successfully'), 'success');
			}
		}
		unset($_POST['CriteriaID']);
	}
}

/* Handle delete */
if (isset($_GET['delete']) && isset($_GET['CriteriaID'])) {
	$SQL = "DELETE FROM hrfeedbackcriteria WHERE criteriaid = " . (int)$_GET['CriteriaID'];
	$Result = DB_query($SQL);
	if ($Result) {
		prnMsg(__('Colleague feedback criteria has been deleted successfully'), 'success');
	}
}

/* Add/Edit form */
$CriteriaID = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$CriteriaName = '';
$Description = '';
$Weight = 0;
$DisplayOrder = 0;
$Active = 1;

if ($CriteriaID > 0) {
	$SQL = "SELECT * FROM hrfeedbackcriteria WHERE criteriaid = " . $CriteriaID;
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_array($Result);
		$CriteriaName = $Row['criterianame'];
		$Description = $Row['description'];
		$Weight = $Row['weight'];
		$DisplayOrder = $Row['displayorder'];
		$Active = $Row['isactive'];
	}
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br /><fieldset>
		<legend>' . ($CriteriaID > 0 ? __('Edit Colleague Feedback Criteria') : __('Add Colleague Feedback Criteria')) . '</legend>';

if ($CriteriaID > 0) {
	echo '<input type="hidden" name="CriteriaID" value="' . $CriteriaID . '" />';
}

echo '<field>
			<label for="CriteriaName">' . __('Criteria Name') . ':</label>
			<input type="text" name="CriteriaName" value="' . htmlspecialchars($CriteriaName, ENT_QUOTES, 'UTF-8') . '" size="50" maxlength="100" required="required" />
		</field>

		<field>
			<label for="Description">' . __('Description') . ':</label>
			<textarea name="Description" rows="3" cols="50">' . htmlspecialchars($Description, ENT_QUOTES, 'UTF-8') . '</textarea>
		</field>

		<field>
			<label for="Weight">' . __('Weight (%)') . ':</label>
			<input type="number" name="Weight" value="' . (float)$Weight . '" step="0.1" min="0" max="100" />
			<small>' . __('Used for weighted scoring calculations') . '</small>
		</field>

		<field>
			<label for="DisplayOrder">' . __('Display Order') . ':</label>
			<input type="number" name="DisplayOrder" value="' . (int)$DisplayOrder . '" min="0" />
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

/* Display criteria list */
$SQL = "SELECT *
		FROM hrfeedbackcriteria
		ORDER BY displayorder,
			weight DESC,
			criterianame";
$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	echo '<p>' . __('No colleague feedback criteria defined') . '</p>';
} else {
	$WeightSum = 0;
	echo '<table class="selection">
			<tr>
				<th>' . __('Order') . '</th>
				<th>' . __('Criteria Name') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('Weight') . '</th>
				<th>' . __('Active') . '</th>
				<th>' . __('Actions') . '</th>
			</tr>';

	while ($Row = DB_fetch_array($Result)) {
		echo '<tr' . (!$Row['isactive'] ? ' style="background-color: #f0f0f0; opacity: 0.7;"' : '') . '>
				<td>' . (int)$Row['displayorder'] . '</td>
				<td><strong>' . htmlspecialchars($Row['criterianame'], ENT_QUOTES, 'UTF-8') . '</strong></td>
				<td>' . htmlspecialchars((string)$Row['description'], ENT_QUOTES, 'UTF-8') . '</td>
				<td class="number">' . number_format((float)$Row['weight'], 1) . '%</td>
				<td>' . ($Row['isactive'] ? __('Yes') : __('No')) . '</td>
				<td>
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?edit=' . (int)$Row['criteriaid'] . '">' . __('Edit') . '</a> |
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?delete=1&CriteriaID=' . (int)$Row['criteriaid'] . '" onclick="return confirm(\'' . __('Are you sure you want to delete this criteria?') . '\');">' . __('Delete') . '</a>
				</td>
			</tr>';
		if ($Row['isactive']) {
			$WeightSum += (float)$Row['weight'];
		}
	}
	echo '</table>';
	if ($WeightSum != 100) {
		ShowFeedbackWeightWarning($WeightSum);
	}
}

include(__DIR__ . '/includes/footer.php');

?>
