<?php

/* Performance Rating Scales Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Performance Rating Scales');
$ViewTopic = 'HumanResources';
$BookMark = 'HRRatingScales';

include(__DIR__ . '/includes/header.php');

echo '<a class="toplink" href="' . $RootPath . '/HRDashboard.php">' . __('Return to HR Dashboard') . '</a>';

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/star.png" title="' . __('Rating Scales') . '" /> ' .
		__('Performance Rating Scales') . '
	</p>';

// Handle form submission
if (isset($_POST['Submit'])) {

	$InputError = 0;

	// Validation
	if (!isset($_POST['ScaleName']) || $_POST['ScaleName'] == '') {
		$InputError = 1;
		prnMsg(__('Scale name is required'), 'error');
	}
	if (!isset($_POST['MinValue']) || $_POST['MinValue'] === '') {
		$InputError = 1;
		prnMsg(__('Minimum value is required'), 'error');
	}
	if (!isset($_POST['MaxValue']) || $_POST['MaxValue'] === '') {
		$InputError = 1;
		prnMsg(__('Maximum value is required'), 'error');
	}

	if ($InputError == 0) {

		$ScaleName = $_POST['ScaleName'];
		$Description = $_POST['Description'];
		$MinValue = (int)$_POST['MinValue'];
		$MaxValue = (int)$_POST['MaxValue'];
		$IsActive = isset($_POST['IsActive']) ? 1 : 0;

		if (isset($_GET['ScaleID']) && $_GET['ScaleID'] != '') {
			// Update existing scale
			$ScaleID = (int)$_GET['ScaleID'];

			$SQL = "UPDATE hrratingscales SET
					scalename = '" . $ScaleName . "',
					description = '" . $Description . "',
					`minvalue` = " . $MinValue . ",
					`maxvalue` = " . $MaxValue . ",
					active = " . $IsActive . "
				WHERE scaleid = " . $ScaleID;

			$ErrMsg = __('Failed to update rating scale');
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(__('Rating scale has been updated'), 'success');

			unset($_GET['ScaleID']);

		} else {
			// Insert new scale
			$SQL = "INSERT INTO hrratingscales (
					scalename,
					description,
					`minvalue`,
					`maxvalue`,
					active
				) VALUES (
					'" . $ScaleName . "',
					'" . $Description . "',
					" . $MinValue . ",
					" . $MaxValue . ",
					" . $IsActive . "
				)";

			$ErrMsg = __('Failed to insert rating scale');
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(__('Rating scale has been created'), 'success');
		}
	}
}

// Handle delete
if (isset($_GET['Delete']) && isset($_GET['ScaleID'])) {
	$ScaleID = (int)$_GET['ScaleID'];

	$SQL = "DELETE FROM hrratingscales WHERE scaleid = " . $ScaleID;
	$Result = DB_query($SQL);

	prnMsg(__('Rating scale has been deleted'), 'success');

	unset($_GET['ScaleID']);
}

// Load existing data for edit
if (isset($_GET['ScaleID'])) {
	$ScaleID = (int)$_GET['ScaleID'];

	$SQL = "SELECT * FROM hrratingscales WHERE scaleid = " . $ScaleID;
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		prnMsg(__('Rating scale not found'), 'error');
		unset($_GET['ScaleID']);
	} else {
		$MyRow = DB_fetch_array($Result);

		$ScaleName = $MyRow['scalename'];
		$Description = $MyRow['description'];
		$MinValue = $MyRow['minvalue'];
		$MaxValue = $MyRow['maxvalue'];
		$IsActive = $MyRow['active'];
	}
}

// Entry form
if (!isset($ScaleName)) {
	$ScaleName = '';
	$Description = '';
	$MinValue = 1;
	$MaxValue = 5;
	$IsActive = 1;
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . (isset($_GET['ScaleID']) ? '?ScaleID=' . urlencode($_GET['ScaleID']) : '') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . (isset($_GET['ScaleID']) ? __('Edit Rating Scale') : __('Add New Rating Scale')) . '</legend>';

echo '<field>
		<label for="ScaleName">' . __('Scale Name') . ':</label>
		<input type="text" name="ScaleName" required size="40" maxlength="100" value="' . htmlspecialchars($ScaleName, ENT_QUOTES, 'UTF-8') . '" />
	</field>';

echo '<field>
		<label for="Description">' . __('Description') . ':</label>
		<textarea name="Description" rows="3" cols="60">' . htmlspecialchars($Description, ENT_QUOTES, 'UTF-8') . '</textarea>
	</field>';

echo '<field>
		<label for="MinValue">' . __('Minimum Value') . ':</label>
		<input type="number" name="MinValue" required value="' . $MinValue . '" />
	</field>';

echo '<field>
		<label for="MaxValue">' . __('Maximum Value') . ':</label>
		<input type="number" name="MaxValue" required value="' . $MaxValue . '" />
	</field>';

echo '<field>
		<label for="IsActive">' . __('Active') . ':</label>
		<input type="checkbox" name="IsActive" value="1"' . ($IsActive ? ' checked' : '') . ' />
	</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Save Rating Scale') . '" />
	</div>';

echo '</form>';

// List existing rating scales
$SQL = "SELECT * FROM hrratingscales ORDER BY scalename";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {

echo '<table class="selection">
		<thead>
			<tr>
				<th>' . __('Scale Name') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('Range') . '</th>
				<th>' . __('Active') . '</th>
				<th>' . __('Actions') . '</th>
			</tr>
		</thead>
		<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>' . htmlspecialchars($MyRow['scalename'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($MyRow['description'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . $MyRow['minvalue'] . ' - ' . $MyRow['maxvalue'] . '</td>
				<td>' . ($MyRow['active'] ? __('Yes') : __('No')) . '</td>
				<td class="centre">
					<a href="' . $_SERVER['PHP_SELF'] . '?ScaleID=' . urlencode($MyRow['scaleid']) . '">' . __('Edit') . '</a> |
					<a href="' . $_SERVER['PHP_SELF'] . '?ScaleID=' . urlencode($MyRow['scaleid']) . '&Delete=1" onclick="return confirm(\'' . __('Are you sure you want to delete this rating scale?') . '\');">' . __('Delete') . '</a>
				</td>
			</tr>';
	}

	echo '</tbody>
		</table>';
} else {
	echo '<div class="centre">' . __('No rating scales defined yet') . '</div>';
}

include(__DIR__ . '/includes/footer.php');

?>
