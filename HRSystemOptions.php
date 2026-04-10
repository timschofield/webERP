<?php

/* HR System Options */

require(__DIR__ . '/includes/session.php');

$Title = __('HR System Options');
$ViewTopic = 'HumanResources';
$BookMark = 'HRSystemOptions';

include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/settings.png" title="' . __('System Options') . '" /> ' .
		__('HR System Options') . '
	</p>';

// Initialize default options if table is empty
$SQL = "SELECT COUNT(*) as optcount FROM hrsystemoptions";
$Result = DB_query($SQL);
$Row = DB_fetch_array($Result);
if ($Row['optcount'] == 0) {
	$DefaultOptions = array(
		array('ProbationPeriod', '90', 'Default probation period in days'),
		array('AppraisalFrequency', '365', 'Days between performance appraisals'),
		array('MaxSickDays', '10', 'Maximum sick days per year'),
		array('MaxVacationDays', '20', 'Maximum vacation days per year'),
		array('MinSalaryIncreasePercent', '0', 'Minimum salary increase percentage'),
		array('MaxSalaryIncreasePercent', '15', 'Maximum salary increase percentage')
	);
	foreach ($DefaultOptions as $Option) {
		$SQL = "INSERT INTO hrsystemoptions (optionname, optionvalue, optiondescription)
				VALUES ('" . DB_escape_string($Option[0]) . "',
						'" . DB_escape_string($Option[1]) . "',
						'" . DB_escape_string($Option[2]) . "')";
		DB_query($SQL);
	}
}

// Handle form submission
if (isset($_POST['UpdateOptions'])) {
	if (isset($_POST['OptionValue']) && is_array($_POST['OptionValue'])) {
		foreach ($_POST['OptionValue'] as $OptionName => $OptionValue) {
			$OptionName = DB_escape_string($OptionName);
			$OptionValue = DB_escape_string($OptionValue);

			$SQL = "UPDATE hrsystemoptions SET optionvalue = '" . $OptionValue . "'
					WHERE optionname = '" . $OptionName . "'";
			DB_query($SQL);
		}
		prnMsg(__('System options have been updated'), 'success');
	}
}

// Display and edit options
$SQL = "SELECT * FROM hrsystemoptions ORDER BY optionname";
$Result = DB_query($SQL);

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">
		<thead>
			<tr>
				<th>' . __('Description') . '</th>
				<th>' . __('Value') . '</th>
				<th>' . __('Setting') . '</th>
			</tr>
		</thead>
		<tbody>';

while ($MyRow = DB_fetch_array($Result)) {
	echo '<tr class="striped_row">
			<td>' . htmlspecialchars($MyRow['optiondescription'], ENT_QUOTES, 'UTF-8') . '</td>
			<td><input type="text" name="OptionValue[' . htmlspecialchars($MyRow['optionname'], ENT_QUOTES, 'UTF-8') . ']" size="10" value="' . htmlspecialchars($MyRow['optionvalue'], ENT_QUOTES, 'UTF-8') . '" /></td>
			<td><em>' . htmlspecialchars($MyRow['optionname'], ENT_QUOTES, 'UTF-8') . '</em></td>
		</tr>';
}

echo '</tbody>
	</table>';

echo '<div class="centre">
		<input type="submit" name="UpdateOptions" value="' . __('Update All Options') . '" />
	</div>';

echo '</form>';

echo '<p class="centre">
		<a href="' . $RootPath . '/HRDashboard.php">' . __('Return to HR Dashboard') . '</a>
	</p>';

include(__DIR__ . '/includes/footer.php');

?>
