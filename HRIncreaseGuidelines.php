<?php

/* HR Increase Guidelines Configuration */

require(__DIR__ . '/includes/session.php');

$Title = __('Increase Guidelines');
$ViewTopic = 'HumanResources';
$BookMark = 'HRIncreaseGuidelines';

include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/award.png" title="' . __('Increase Guidelines') . '" /> ' .
		__('Salary Increase Guidelines Configuration') . '
	</p>';

// Handle form submission
if (isset($_POST['Submit'])) {
	$InputError = 0;

	if (trim($_POST['GuidelineCode']) == '') {
		$InputError = 1;
		prnMsg(__('The guideline code must not be empty'), 'error');
	}
	if (trim($_POST['IncreaseType']) == '') {
		$InputError = 1;
		prnMsg(__('The increase type must not be empty'), 'error');
	}

	if ($InputError != 1) {
		if (isset($_POST['GuidelineID']) && $_POST['GuidelineID'] > 0) {
			// Update existing guideline
			$SQL = "UPDATE hrincreaseguidelines SET
						increasetype = '" . $_POST['IncreaseType'] . "',
						guidelinecode = '" . $_POST['GuidelineCode'] . "',
						description = '" . $_POST['Description'] . "',
						minpercentage = " . filter_var($_POST['MinPercentage'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						maxpercentage = " . filter_var($_POST['MaxPercentage'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						targetpercentage = " . filter_var($_POST['TargetPercentage'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						serviceyearsfrom = " . (int)$_POST['ServiceYearsFrom'] . ",
						serviceyearsto = " . (int)$_POST['ServiceYearsTo'] . ",
						performanceratingfrom = " . (int)$_POST['PerformanceRatingFrom'] . ",
						performanceratingto = " . (int)$_POST['PerformanceRatingTo'] . ",
						active = " . (isset($_POST['Active']) ? 1 : 0) . "
					WHERE guidelineid = " . (int)$_POST['GuidelineID'];

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Guideline has been updated successfully'), 'success');
			}
		} else {
			// Insert new guideline
			$SQL = "INSERT INTO hrincreaseguidelines (
						increasetype, guidelinecode, description,
						minpercentage, maxpercentage, targetpercentage,
						serviceyearsfrom, serviceyearsto,
						performanceratingfrom, performanceratingto, active
					) VALUES (
						'" . $_POST['IncreaseType'] . "',
						'" . $_POST['GuidelineCode'] . "',
						'" . $_POST['Description'] . "',
						" . filter_var($_POST['MinPercentage'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						" . filter_var($_POST['MaxPercentage'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						" . filter_var($_POST['TargetPercentage'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						" . (int)$_POST['ServiceYearsFrom'] . ",
						" . (int)$_POST['ServiceYearsTo'] . ",
						" . (int)$_POST['PerformanceRatingFrom'] . ",
						" . (int)$_POST['PerformanceRatingTo'] . ",
						" . (isset($_POST['Active']) ? 1 : 0) . "
					)";

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Guideline has been created successfully'), 'success');
			}
		}
		unset($_POST['GuidelineID']);
	}
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['GuidelineID'])) {
	$SQL = "DELETE FROM hrincreaseguidelines WHERE guidelineid = " . (int)$_GET['GuidelineID'];
	$Result = DB_query($SQL);
	if ($Result) {
		prnMsg(__('Guideline has been deleted successfully'), 'success');
	}
}

// Display information about increase guidelines
echo '<div class="page_help_text">
		<h3>' . __('About Salary Increase Guidelines') . '</h3>
		<p>' . __('Increase guidelines help determine appropriate salary increases based on performance ratings and years of service. Guidelines specify percentage ranges for different combinations of these factors.') . '</p>
		<p>' . __('Use these guidelines during compensation review cycles to ensure fair and consistent salary decisions across the organization.') . '</p>
	</div>';

// Add/Edit form - show by default
$GuidelineID = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$IncreaseType = '';
$GuidelineCode = '';
$Description = '';
$MinPercentage = 0;
$MaxPercentage = 0;
$TargetPercentage = 0;
$ServiceYearsFrom = 0;
$ServiceYearsTo = 0;
$PerformanceRatingFrom = 1;
$PerformanceRatingTo = 5;
$Active = 1;

if ($GuidelineID > 0) {
	$SQL = "SELECT * FROM hrincreaseguidelines WHERE guidelineid = " . $GuidelineID;
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_array($Result);
		$IncreaseType = $Row['increasetype'];
		$GuidelineCode = $Row['guidelinecode'];
		$Description = $Row['description'];
		$MinPercentage = $Row['minpercentage'];
		$MaxPercentage = $Row['maxpercentage'];
		$TargetPercentage = $Row['targetpercentage'];
		$ServiceYearsFrom = $Row['serviceyearsfrom'];
		$ServiceYearsTo = $Row['serviceyearsto'];
		$PerformanceRatingFrom = $Row['performanceratingfrom'];
		$PerformanceRatingTo = $Row['performanceratingto'];
		$Active = $Row['active'];
	}
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
		<fieldset>
		<legend>' . ($GuidelineID > 0 ? __('Edit Guideline') : __('Add Guideline')) . '</legend>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if ($GuidelineID > 0) {
	echo '<input type="hidden" name="GuidelineID" value="' . $GuidelineID . '" />';
}

echo '<field>
			<label for="IncreaseType">' . __('Increase Type') . ':</label>
			<select name="IncreaseType" required="required">
				<option value="">' . __('Select Type') . '</option>
				<option value="Merit"' . ($IncreaseType == 'Merit' ? ' selected="selected"' : '') . '>' . __('Merit Increase') . '</option>
				<option value="Promotion"' . ($IncreaseType == 'Promotion' ? ' selected="selected"' : '') . '>' . __('Promotion') . '</option>
				<option value="Market"' . ($IncreaseType == 'Market' ? ' selected="selected"' : '') . '>' . __('Market Adjustment') . '</option>
				<option value="Cost of Living"' . ($IncreaseType == 'Cost of Living' ? ' selected="selected"' : '') . '>' . __('Cost of Living') . '</option>
			</select>
		</field>

		<field>
			<label for="GuidelineCode">' . __('Guideline Code') . ':</label>
			<input type="text" name="GuidelineCode" value="' . $GuidelineCode . '" size="20" maxlength="20" required="required" />
		</field>

		<field>
			<label for="Description">' . __('Description') . ':</label>
			<input type="text" name="Description" value="' . $Description . '" size="50" maxlength="200" />
		</field>

		<field>
			<label for="ServiceYearsFrom">' . __('Service Years From') . ':</label>
			<input type="number" name="ServiceYearsFrom" value="' . $ServiceYearsFrom . '" min="0" max="50" />
		</field>

		<field>
			<label for="ServiceYearsTo">' . __('Service Years To') . ':</label>
			<input type="number" name="ServiceYearsTo" value="' . $ServiceYearsTo . '" min="0" max="50" />
		</field>

		<field>
			<label for="PerformanceRatingFrom">' . __('Performance Rating From (1-5)') . ':</label>
			<input type="number" name="PerformanceRatingFrom" value="' . $PerformanceRatingFrom . '" min="1" max="5" />
		</field>

		<field>
			<label for="PerformanceRatingTo">' . __('Performance Rating To (1-5)') . ':</label>
			<input type="number" name="PerformanceRatingTo" value="' . $PerformanceRatingTo . '" min="1" max="5" />
		</field>

		<field>
			<label for="MinPercentage">' . __('Minimum Increase %') . ':</label>
			<input type="number" name="MinPercentage" value="' . $MinPercentage . '" step="0.01" required="required" />
		</field>

		<field>
			<label for="MaxPercentage">' . __('Maximum Increase %') . ':</label>
			<input type="number" name="MaxPercentage" value="' . $MaxPercentage . '" step="0.01" required="required" />
		</field>

		<field>
			<label for="TargetPercentage">' . __('Target Increase %') . ':</label>
			<input type="number" name="TargetPercentage" value="' . $TargetPercentage . '" step="0.01" required="required" />
		</field>

		<field>
			<label for="Active">' . __('Active') . ':</label>
			<input type="checkbox" name="Active" value="1"' . ($Active ? ' checked="checked"' : '') . ' />
		</field>
	</fieldset>';
echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Save') . '" />
		<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Cancel') . '</a>
	</div>
	</form>';

// Display guidelines list
echo '<table class="selection">
		<tr>
			<th>' . __('Increase Type') . '</th>
			<th>' . __('Guideline Code') . '</th>
			<th>' . __('Service Years') . '</th>
			<th>' . __('Performance Rating') . '</th>
			<th>' . __('Increase %') . '</th>
			<th>' . __('Active') . '</th>
			<th>' . __('Actions') . '</th>
		</tr>';

$SQL = "SELECT * FROM hrincreaseguidelines ORDER BY increasetype, serviceyearsfrom, performanceratingfrom";
$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	echo '<tr><td colspan="7">' . __('No guidelines defined') . '</td></tr>';
} else {
	while ($Row = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . $Row['increasetype'] . '</td>
				<td>' . $Row['guidelinecode'] . '</td>
				<td>' . $Row['serviceyearsfrom'] . ' - ' . $Row['serviceyearsto'] . '</td>
				<td>' . $Row['performanceratingfrom'] . ' - ' . $Row['performanceratingto'] . '</td>
				<td class="number">' . number_format($Row['minpercentage'], 2) . '% - ' . number_format($Row['maxpercentage'], 2) . '% (' . __('Target') . ': ' . number_format($Row['targetpercentage'], 2) . '%)</td>
				<td>' . ($Row['active'] ? __('Yes') : __('No')) . '</td>
				<td>
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?edit=' . $Row['guidelineid'] . '">' . __('Edit') . '</a> |
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?delete=1&GuidelineID=' . $Row['guidelineid'] . '" onclick="return confirm(\'' . __('Are you sure you want to delete this guideline?') . '\');">' . __('Delete') . '</a>
				</td>
			</tr>';
	}
}

echo '</table>';

include(__DIR__ . '/includes/footer.php');

?>
