<?php

/* Skill Gap Analysis */

require(__DIR__ . '/includes/session.php');

$Title = __('Skill Gap Analysis');
$ViewTopic = 'HumanResources';
$BookMark = 'HRSkillGapAnalysis';

include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/award.png" title="' . __('Gap Analysis') . '" /> ' .
		__('Skill Gap Analysis') . '
	</p>';

// Employee selection form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Select Employee') . '</legend>
		<field>
			<label for="EmployeeNumber">' . __('Employee') . ':</label>
			<select name="EmployeeNumber" onchange="this.form.submit()">
				<option value="">' . __('Select Employee') . '</option>';

$SQL = "SELECT e.employeenumber, CONCAT(e.firstname, ' ', e.lastname) as name,
		p.positiontitle
		FROM hremployees e
		LEFT JOIN hrpositions p ON e.positionid = p.positionid
		WHERE e.employmentstatus = 'Active'
		ORDER BY e.lastname, e.firstname";
$Result = DB_query($SQL);
while ($EmpRow = DB_fetch_array($Result)) {
	$Selected = (isset($_POST['EmployeeNumber']) && $_POST['EmployeeNumber'] == $EmpRow['employeenumber']) ? ' selected' : '';
	$DisplayText = $EmpRow['name'];
	if ($EmpRow['positiontitle']) {
		$DisplayText .= ' - ' . $EmpRow['positiontitle'];
	}
	echo '<option value="' . $EmpRow['employeenumber'] . '"' . $Selected . '>' .
		htmlspecialchars($DisplayText, ENT_QUOTES, 'UTF-8') . '</option>';
}

echo '</select>
		</field>
	</fieldset>';
echo '</form>';

// If employee is selected, show gap analysis
if (isset($_POST['EmployeeNumber']) && $_POST['EmployeeNumber'] != '') {

	$EmployeeNumber = $_POST['EmployeeNumber'];

	// Get employee details
	$SQL = "SELECT e.*, p.positionid, p.positiontitle
			FROM hremployees e
			LEFT JOIN hrpositions p ON e.positionid = p.positionid
			WHERE e.employeenumber = '" . $EmployeeNumber . "'";
	$Result = DB_query($SQL);
	$EmpDetails = DB_fetch_array($Result);

	echo '<h3>' . __('Gap Analysis for') . ': ' . htmlspecialchars($EmpDetails['firstname'] . ' ' . $EmpDetails['lastname'], ENT_QUOTES, 'UTF-8') . '</h3>';
	echo '<p><strong>' . __('Position') . ':</strong> ' . htmlspecialchars($EmpDetails['positiontitle'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';

	if (!$EmpDetails['positionid']) {
		prnMsg(__('This employee is not assigned to a position'), 'warn');
	} else {

		// Get required skills for the position
		$SQL = "SELECT
				jc.requiredlevel,
				jc.importance,
				c.skillid,
				c.skillcode,
				c.skillname,
				c.skillcategory,
				ec.currentlevel,
				ec.assessmentdate
			FROM hrjobskills jc
			INNER JOIN hrskills c ON jc.skillid = c.skillid
			LEFT JOIN hremployeeskills ec ON (
				ec.skillid = c.skillid
				AND ec.employeeid = " . (int)$EmpDetails['employeeid'] . "
			)
			WHERE jc.positionid = " . $EmpDetails['positionid'] . "
			ORDER BY c.skillcategory, c.skillname";

		$Result = DB_query($SQL);

		if (DB_num_rows($Result) == 0) {
			prnMsg(__('No skills defined for this position'), 'warn');
		} else {

			echo '<table class="selection">
					<thead>
						<tr>
							<th>' . __('Code') . '</th>
							<th>' . __('Skill') . '</th>
							<th>' . __('Category') . '</th>
							<th>' . __('Required Level') . '</th>
							<th>' . __('Current Level') . '</th>
							<th>' . __('Gap') . '</th>
							<th>' . __('Importance') . '</th>
							<th>' . __('Last Assessed') . '</th>
						</tr>
					</thead>
					<tbody>';

			$TotalGap = 0;
			$CompCount = 0;
			$MandatoryGaps = 0;

			while ($MyRow = DB_fetch_array($Result)) {

				$CurrentLevel = $MyRow['currentlevel'] ? $MyRow['currentlevel'] : 0;
				$Gap = $MyRow['requiredlevel'] - $CurrentLevel;

				$TotalGap += abs($Gap);
				$CompCount++;

				if ($Gap > 0 && $MyRow['importance'] == 'Essential') {
					$MandatoryGaps++;
				}

				// Color code the gap
				$GapClass = '';
				if ($Gap > 0) {
					$GapClass = ' class="warn"';
				} elseif ($Gap < 0) {
					$GapClass = ' style="background-color: #e8f5e9;"';
				} else {
					$GapClass = ' style="background-color: #f1f8e9;"';
				}

				echo '<tr class="striped_row">
						<td>' . htmlspecialchars($MyRow['skillcode'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
						<td>' . htmlspecialchars($MyRow['skillname'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
						<td>' . htmlspecialchars($MyRow['skillcategory'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
						<td>' . $MyRow['requiredlevel'] . '</td>
						<td>' . $CurrentLevel . '</td>
						<td' . $GapClass . '><strong>' . ($Gap > 0 ? '-' . $Gap : ($Gap < 0 ? '+' . abs($Gap) : '0')) . '</strong></td>
						<td>' . htmlspecialchars($MyRow['importance'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
						<td>' . ($MyRow['assessmentdate'] ? ConvertSQLDate($MyRow['assessmentdate']) : __('Never')) . '</td>
					</tr>';
			}

			echo '</tbody>
				</table>';

			// Summary
			echo '<div style="margin: 20px auto; padding: 15px; border: 1px solid #ccc; background-color: #f5f5f5; width: 90%;">
					<h3>' . __('Gap Analysis Summary') . '</h3>
					<ul>
						<li><strong>' . __('Total Skills Required') . ':</strong> ' . $CompCount . '</li>
						<li><strong>' . __('Average Gap') . ':</strong> ' . ($CompCount > 0 ? number_format($TotalGap / $CompCount, 2) : '0') . '</li>
						<li><strong>' . __('Mandatory Skills with Gaps') . ':</strong> <span style="color: ' . ($MandatoryGaps > 0 ? 'red' : 'green') . ';">' . $MandatoryGaps . '</span></li>
					</ul>';

			if ($MandatoryGaps > 0) {
				echo '<p style="color: red;"><strong>' . __('Action Required') . ':</strong> ' .
					__('This employee has gaps in mandatory skills that should be addressed through training or development.') . '</p>';
			} else {
				echo '<p style="color: green;"><strong>' . __('Status') . ':</strong> ' .
					__('This employee meets all mandatory skill requirements for their position.') . '</p>';
			}

			echo '</div>';
		}
	}
}

echo '<p class="centre">
		<a href="' . $RootPath . '/HRDashboard.php">' . __('Return to HR Dashboard') . '</a>
	</p>';

include(__DIR__ . '/includes/footer.php');

?>
