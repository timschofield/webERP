<?php

/* HR Performance Goals Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Performance Goals');
$ViewTopic = 'HumanResources';
$BookMark = 'HRPerformanceGoals';

include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/task.png" title="' . __('Performance Goals') . '" /> ' .
		__('Employee Performance Goals') . '
	</p>';

// Handle form submission
if (isset($_POST['Submit'])) {
	$InputError = 0;

	if (!isset($_POST['EmployeeID']) || $_POST['EmployeeID'] <= 0) {
		$InputError = 1;
		prnMsg(__('Please select an employee'), 'error');
	}
	if (trim($_POST['GoalDescription']) == '') {
		$InputError = 1;
		prnMsg(__('The goal description must not be empty'), 'error');
	}
	if (!is_date($_POST['TargetDate'])) {
		$InputError = 1;
		prnMsg(__('The target date must be a valid date'), 'error');
	}

	if ($InputError != 1) {
		$TargetDate = FormatDateForSQL($_POST['TargetDate']);
		$CompletionDate = !empty($_POST['CompletionDate']) && is_date($_POST['CompletionDate']) ? "'" . FormatDateForSQL($_POST['CompletionDate']) . "'" : 'NULL';

		if (isset($_POST['GoalID']) && $_POST['GoalID'] > 0) {
			// Update existing goal
			$SQL = "UPDATE hrperformancegoals SET
						employeeid = " . (int)$_POST['EmployeeID'] . ",
						goaldescription = '" . $_POST['GoalDescription'] . "',
						goalcategory = '" . $_POST['GoalCategory'] . "',
						targetdate = '" . $TargetDate . "',
						status = '" . $_POST['Status'] . "',
						progress = " . (int)$_POST['Progress'] . ",
						completiondate = " . $CompletionDate . ",
						notes = '" . $_POST['Notes'] . "',
						weight = " . filter_var($_POST['Weight'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						modifiedby = '" . $_SESSION['UserID'] . "',
						modifieddate = NOW()
					WHERE goalid = " . (int)$_POST['GoalID'];

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Performance goal has been updated successfully'), 'success');
			}
		} else {
			// Insert new goal
			$SQL = "INSERT INTO hrperformancegoals (
						employeeid, goaldescription, goalcategory, targetdate,
						status, progress, completiondate, notes, weight,
						createdby, createddate
					) VALUES (
						" . (int)$_POST['EmployeeID'] . ",
						'" . $_POST['GoalDescription'] . "',
						'" . $_POST['GoalCategory'] . "',
						'" . $TargetDate . "',
						'" . $_POST['Status'] . "',
						" . (int)$_POST['Progress'] . ",
						" . $CompletionDate . ",
						'" . $_POST['Notes'] . "',
						" . filter_var($_POST['Weight'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						'" . $_SESSION['UserID'] . "',
						NOW()
					)";

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Performance goal has been created successfully'), 'success');
			}
		}
		unset($_POST['GoalID']);
	}
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['GoalID'])) {
	$SQL = "DELETE FROM hrperformancegoals WHERE goalid = " . (int)$_GET['GoalID'];
	$Result = DB_query($SQL);
	if ($Result) {
		prnMsg(__('Performance goal has been deleted successfully'), 'success');
	}
}

// Add/Edit form at top
if (isset($_GET['edit']) || !isset($_GET['edit']) && !isset($_GET['delete'])) {
	$GoalID = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
	$EmployeeID = 0;
	$GoalDescription = '';
	$GoalCategory = 'Performance';
	$TargetDate = '';
	$Status = 'Not Started';
	$Progress = 0;
	$CompletionDate = '';
	$Notes = '';
	$Weight = 0;

	if ($GoalID > 0) {
		$SQL = "SELECT * FROM hrperformancegoals WHERE goalid = " . $GoalID;
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) > 0) {
			$Row = DB_fetch_array($Result);
			$EmployeeID = $Row['employeeid'];
			$GoalDescription = $Row['goaldescription'];
			$GoalCategory = $Row['goalcategory'];
			$TargetDate = ConvertSQLDate($Row['targetdate']);
			$Status = $Row['status'];
			$Progress = $Row['progress'];
			$CompletionDate = ConvertSQLDate($Row['completiondate']);
			$Notes = $Row['notes'];
			$Weight = $Row['weight'];
		}
	}

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>' . ($GoalID > 0 ? __('Edit Performance Goal') : __('Add Performance Goal')) . '</legend>';

	if ($GoalID > 0) {
		echo '<input type="hidden" name="GoalID" value="' . $GoalID . '" />';
	}

	echo '<field>
				<label for="EmployeeID">' . __('Employee') . ':</label>
				<select name="EmployeeID" required="required">
					<option value="">' . __('Select Employee') . '</option>';

	$SQL = "SELECT employeeid, employeenumber, firstname, lastname
			FROM hremployees
			WHERE employmentstatus = 'Active'
			ORDER BY lastname, firstname";
	$EmployeeResult = DB_query($SQL);
	while ($Row = DB_fetch_array($EmployeeResult)) {
		echo '<option value="' . $Row['employeeid'] . '"' .
			($EmployeeID == $Row['employeeid'] ? ' selected="selected"' : '') .
			'>' . $Row['employeenumber'] . ' - ' . $Row['firstname'] . ' ' . $Row['lastname'] . '</option>';
	}

	echo '</select>
			</field>

			<field>
				<label for="GoalDescription">' . __('Goal Description') . ':</label>
				<textarea name="GoalDescription" rows="3" cols="60" required="required">' . htmlspecialchars($GoalDescription) . '</textarea>
			</field>

			<field>
				<label for="GoalCategory">' . __('Goal Category') . ':</label>
				<select name="GoalCategory">
					<option value="Performance"' . ($GoalCategory == 'Performance' ? ' selected="selected"' : '') . '>' . __('Performance') . '</option>
					<option value="Development"' . ($GoalCategory == 'Development' ? ' selected="selected"' : '') . '>' . __('Development') . '</option>
					<option value="Behavioral"' . ($GoalCategory == 'Behavioral' ? ' selected="selected"' : '') . '>' . __('Behavioral') . '</option>
					<option value="Project"' . ($GoalCategory == 'Project' ? ' selected="selected"' : '') . '>' . __('Project') . '</option>
				</select>
			</field>

			<field>
				<label for="TargetDate">' . __('Target Date') . ':</label>
				<input type="date" name="TargetDate" class="date" value="' . $TargetDate . '" required="required" />
			</field>

			<field>
				<label for="Status">' . __('Status') . ':</label>
				<select name="Status">
					<option value="Not Started"' . ($Status == 'Not Started' ? ' selected="selected"' : '') . '>' . __('Not Started') . '</option>
					<option value="In Progress"' . ($Status == 'In Progress' ? ' selected="selected"' : '') . '>' . __('In Progress') . '</option>
					<option value="Completed"' . ($Status == 'Completed' ? ' selected="selected"' : '') . '>' . __('Completed') . '</option>
					<option value="Deferred"' . ($Status == 'Deferred' ? ' selected="selected"' : '') . '>' . __('Deferred') . '</option>
					<option value="Cancelled"' . ($Status == 'Cancelled' ? ' selected="selected"' : '') . '>' . __('Cancelled') . '</option>
				</select>
			</field>

			<field>
				<label for="Progress">' . __('Progress (%)') . ':</label>
				<input type="number" name="Progress" value="' . $Progress . '" min="0" max="100" /> %
			</field>

			<field>
				<label for="CompletionDate">' . __('Completion Date') . ':</label>
				<input type="date" name="CompletionDate" class="date" value="' . $CompletionDate . '" />
			</field>

			<field>
				<label for="Weight">' . __('Weight (%)') . ':</label>
				<input type="number" name="Weight" value="' . $Weight . '" step="0.1" min="0" max="100" />
				<small>' . __('Used for goal-based performance calculations') . '</small>
			</field>

			<field>
				<label for="Notes">' . __('Notes') . ':</label>
				<textarea name="Notes" rows="4" cols="60">' . htmlspecialchars($Notes) . '</textarea>
			</field>
		<div class="centre">
			<input type="submit" name="Submit" value="' . __('Save') . '" />
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Cancel') . '</a>
		</div>
		</form>
		</fieldset>';
}

// Filter options
echo '<br /><form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<table class="selection">
		<tr>
			<td>' . __('Filter by Employee') . ':</td>
			<td><select name="FilterEmployee" onchange="this.form.submit()">
				<option value="0">' . __('All Employees') . '</option>';

$SQL = "SELECT employeeid, employeenumber, firstname, lastname
		FROM hremployees
		WHERE employmentstatus = 'Active'
		ORDER BY lastname, firstname";
$Result = DB_query($SQL);
while ($Row = DB_fetch_array($Result)) {
	echo '<option value="' . $Row['employeeid'] . '"' .
		($FilterEmployee == $Row['employeeid'] ? ' selected="selected"' : '') .
		'>' . $Row['employeenumber'] . ' - ' . $Row['firstname'] . ' ' . $Row['lastname'] . '</option>';
}

echo '</select></td>
			<td>' . __('Status') . ':</td>
			<td><select name="FilterStatus" onchange="this.form.submit()">
				<option value="">' . __('All Statuses') . '</option>
				<option value="Not Started"' . ($FilterStatus == 'Not Started' ? ' selected="selected"' : '') . '>' . __('Not Started') . '</option>
				<option value="In Progress"' . ($FilterStatus == 'In Progress' ? ' selected="selected"' : '') . '>' . __('In Progress') . '</option>
				<option value="Completed"' . ($FilterStatus == 'Completed' ? ' selected="selected"' : '') . '>' . __('Completed') . '</option>
				<option value="Deferred"' . ($FilterStatus == 'Deferred' ? ' selected="selected"' : '') . '>' . __('Deferred') . '</option>
				<option value="Cancelled"' . ($FilterStatus == 'Cancelled' ? ' selected="selected"' : '') . '>' . __('Cancelled') . '</option>
			</select></td>
			<td>' . __('Category') . ':</td>
			<td><select name="FilterCategory" onchange="this.form.submit()">
				<option value="">' . __('All Categories') . '</option>
				<option value="Performance"' . ($FilterCategory == 'Performance' ? ' selected="selected"' : '') . '>' . __('Performance') . '</option>
				<option value="Development"' . ($FilterCategory == 'Development' ? ' selected="selected"' : '') . '>' . __('Development') . '</option>
				<option value="Behavioral"' . ($FilterCategory == 'Behavioral' ? ' selected="selected"' : '') . '>' . __('Behavioral') . '</option>
				<option value="Project"' . ($FilterCategory == 'Project' ? ' selected="selected"' : '') . '>' . __('Project') . '</option>
			</select></td>
		</tr>
		</table>
	</form>';

// Display goals list
echo '<br /><table class="selection">
		<tr>
			<th>' . __('Employee') . '</th>
			<th>' . __('Goal Description') . '</th>
			<th>' . __('Category') . '</th>
			<th>' . __('Target Date') . '</th>
			<th>' . __('Status') . '</th>
			<th>' . __('Progress') . '</th>
			<th>' . __('Weight') . '</th>
			<th>' . __('Actions') . '</th>
		</tr>';

$FilterEmployee = isset($_POST['FilterEmployee']) ? (int)$_POST['FilterEmployee'] : 0;
$FilterStatus = isset($_POST['FilterStatus']) ? $_POST['FilterStatus'] : '';
$FilterCategory = isset($_POST['FilterCategory']) ? $_POST['FilterCategory'] : '';

$WhereClause = "1=1";
if ($FilterEmployee > 0) {
	$WhereClause .= " AND g.employeeid = " . $FilterEmployee;
}
if ($FilterStatus != '') {
	$WhereClause .= " AND g.status = '" . DB_escape_string($FilterStatus) . "'";
}
if ($FilterCategory != '') {
	$WhereClause .= " AND g.goalcategory = '" . DB_escape_string($FilterCategory) . "'";
}

$SQL = "SELECT g.*,
			e.firstname, e.lastname, e.employeenumber
		FROM hrperformancegoals g
		INNER JOIN hremployees e ON g.employeeid = e.employeeid
		WHERE " . $WhereClause . "
		ORDER BY g.targetdate, e.lastname, e.firstname";

$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	echo '<tr><td colspan="8">' . __('No performance goals found') . '</td></tr>';
} else {
	while ($Row = DB_fetch_array($Result)) {
		$StatusColor = '';
		$IsOverdue = (strtotime($Row['targetdate']) < time() && $Row['status'] != 'Completed');

		switch ($Row['status']) {
			case 'Not Started':
				$StatusColor = 'style="background-color: #FFF9C4;"';
				break;
			case 'In Progress':
				$StatusColor = 'style="background-color: #B3E5FC;"';
				break;
			case 'Completed':
				$StatusColor = 'style="background-color: #C8E6C9;"';
				break;
			case 'Deferred':
			case 'Cancelled':
				$StatusColor = 'style="background-color: #E0E0E0;"';
				break;
		}

		if ($IsOverdue) {
			$StatusColor = 'style="background-color: #FFCDD2;"';
		}

		echo '<tr ' . $StatusColor . '>
				<td>' . $Row['employeenumber'] . ' - ' . $Row['firstname'] . ' ' . $Row['lastname'] . '</td>
				<td><strong>' . htmlspecialchars($Row['goaldescription']) . '</strong></td>
				<td>' . __($Row['goalcategory']) . '</td>
				<td>' . ConvertSQLDate($Row['targetdate']) . ($IsOverdue ? ' <span style="color: red;">(' . __('Overdue') . ')</span>' : '') . '</td>
				<td>' . __($Row['status']) . '</td>
				<td>
					<div style="width: 100px; background-color: #e0e0e0; border: 1px solid #999;">
						<div style="width: ' . $Row['progress'] . '%; background-color: #4caf50; height: 20px; text-align: center; color: white;">
							' . $Row['progress'] . '%
						</div>
					</div>
				</td>
				<td class="number">' . number_format($Row['weight'], 1) . '%</td>
				<td>
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?edit=' . $Row['goalid'] . '">' . __('Edit') . '</a> |
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?delete=1&GoalID=' . $Row['goalid'] . '" onclick="return confirm(\'' . __('Are you sure you want to delete this goal?') . '\');">' . __('Delete') . '</a>
				</td>
			</tr>';
	}
}

echo '</table>';

include(__DIR__ . '/includes/footer.php');

?>
