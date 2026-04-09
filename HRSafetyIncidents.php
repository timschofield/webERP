<?php

/* Safety Incidents Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Safety Incidents');
$ViewTopic = 'HumanResources';
$BookMark = 'HRSafetyIncidents';

include(__DIR__ . '/includes/header.php');

if (isset($_GET['IncidentID'])) {
	$IncidentID = $_GET['IncidentID'];
} elseif (isset($_POST['IncidentID'])) {
	$IncidentID = $_POST['IncidentID'];
}

echo '<a class="toplink" href="' . $RootPath . '/HRDashboard.php">' . __('Return to HR Dashboard') . '</a>';

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/warning.png" title="' . __('Safety') . '" /> ' .
		__('Safety Incidents Management') . '
	</p>';

// Handle form submission
if (isset($_POST['Submit'])) {

	$InputError = 0;

	if (!isset($_POST['IncidentDate']) || $_POST['IncidentDate'] == '') {
		$InputError = 1;
		prnMsg(__('Incident date is required'), 'error');
	}

	if ($InputError == 0) {

		$EmployeeID = isset($_POST['EmployeeID']) && $_POST['EmployeeID'] != '' ? (int)$_POST['EmployeeID'] : 'NULL';
		$IncidentDate = FormatDateForSQL($_POST['IncidentDate']) . ' ' . $_POST['IncidentTime'] . ':00';
		$IncidentTime = $_POST['IncidentTime'] . ' ' . $_POST['IncidentTime'] . ':00';
		$LocationID = isset($_POST['LocationID']) && $_POST['LocationID'] != '' ? (int)$_POST['LocationID'] : 'NULL';
		$IncidentType = $_POST['IncidentType'];
		$Severity = $_POST['Severity'];
		$Description = $_POST['Description'];
		$ImmediateAction = $_POST['ImmediateAction'];
		$RootCause = $_POST['RootCause'];
		$CorrectiveAction = $_POST['CorrectiveAction'];
		$Status = $_POST['Status'];
		$DaysLost = isset($_POST['DaysLost']) ? (int)$_POST['DaysLost'] : 0;

		if (isset($_POST['IncidentID']) && $_POST['IncidentID'] != '') {
			// Update
			$IncidentID = (int)$_POST['IncidentID'];

			$SQL = "UPDATE hrsafetyincidents SET
					employeeid = " . $EmployeeID . ",
					incidentdate = '" . $IncidentDate . "',
					locationid = " . $LocationID . ",
					incidenttype = '" . $IncidentType . "',
					severity = '" . $Severity . "',
					description = '" . $Description . "',
					immediateaction = '" . $ImmediateAction . "',
					rootcause = '" . $RootCause . "',
					correctiveaction = '" . $CorrectiveAction . "',
					status = '" . $Status . "',
					dayslost = " . $DaysLost . "
				WHERE incidentid = " . $IncidentID;

			$Result = DB_query($SQL);
			prnMsg(__('Safety incident has been updated'), 'success');

		} else {
			// Insert - get next incident number
			$IncidentNumber = $_POST['IncidentNumber'];

			$SQL = "SELECT MAX(CAST(SUBSTRING(incidentnumber, 4) AS UNSIGNED)) as maxnum
					FROM hrsafetyincidents
					WHERE incidentnumber LIKE 'INC%'";
			$Result = DB_query($SQL);
			$Row = DB_fetch_array($Result);
			$NextNum = isset($Row['maxnum']) ? $Row['maxnum'] + 1 : 1;
			$AutoIncidentNumber = 'INC' . str_pad($NextNum, 6, '0', STR_PAD_LEFT);

			if (empty($IncidentNumber)) {
				$IncidentNumber = $AutoIncidentNumber;
			}

			$SQL = "INSERT INTO hrsafetyincidents (
					incidentnumber,
					incidentdate,
					employeeid,
					locationid,
					incidenttype,
					severity,
					description,
					immediateaction,
					rootcause,
					correctiveaction,
					reportedby,
					reporteddate,
					status,
					dayslost
				) VALUES (
					'" . $IncidentNumber . "',
					'" . $IncidentDate . "',
					" . $EmployeeID . ",
					" . $LocationID . ",
					'" . $IncidentType . "',
					'" . $Severity . "',
					'" . $Description . "',
					'" . $ImmediateAction . "',
					'" . $RootCause . "',
					'" . $CorrectiveAction . "',
					(SELECT employeeid FROM hremployees WHERE userid = '" . $_SESSION['UserID'] . "' LIMIT 1),
					CURDATE(),
					'" . $Status . "',
					" . $DaysLost . "
				)";
			$Result = DB_query($SQL);
			prnMsg(__('Safety incident has been recorded'), 'success');
		}
		unset($_POST['EmployeeID']);
		unset($_POST['IncidentNumber']);
		unset($_POST['IncidentDate']);
		unset($_POST['IncidentTime']);
		unset($_POST['LocationID']);
		unset($_POST['IncidentType']);
		unset($_POST['Severity']);
		unset($_POST['Description']);
		unset($_POST['ImmediateAction']);
		unset($_POST['RootCause']);
		unset($_POST['CorrectiveAction']);
		unset($_POST['Status']);
		unset($_POST['DaysLost']);
		unset($_POST['Submit']);
	}
}

// Handle delete
if (isset($_GET['Delete']) && isset($_GET['IncidentID'])) {
	$IncidentID = (int)$_GET['IncidentID'];

	$SQL = "DELETE FROM hrsafetyincidents WHERE incidentid = " . $IncidentID;
	$Result = DB_query($SQL);

	prnMsg(__('Safety incident has been deleted'), 'success');
	unset($_GET['IncidentID']);
}

// Load for edit
if (isset($_GET['IncidentID'])) {
	$IncidentID = (int)$_GET['IncidentID'];

	$SQL = "SELECT * FROM hrsafetyincidents WHERE incidentid = " . $IncidentID;
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);

		$EmployeeID = $MyRow['employeeid'];
		$IncidentNumber = $MyRow['incidentnumber'];
		$IncidentDateTime = $MyRow['incidentdate'];
		$IncidentDate = ConvertSQLDate(substr($IncidentDateTime, 0, 10));
		$IncidentTime = substr($IncidentDateTime, 11, 5);
		$LocationID = $MyRow['locationid'];
		$IncidentType = $MyRow['incidenttype'];
		$Severity = $MyRow['severity'];
		$Description = $MyRow['description'];
		$ImmediateAction = $MyRow['immediateaction'];
		$RootCause = $MyRow['rootcause'];
		$CorrectiveAction = $MyRow['correctiveaction'];
		$Status = $MyRow['status'];
		$DaysLost = $MyRow['dayslost'];
	}
}

// Entry form - initialize defaults for new records
if (!isset($IncidentDate)) {
	$EmployeeID = '';
	$IncidentNumber = '';
	$IncidentDate = date($_SESSION['DefaultDateFormat']);
	$IncidentTime = date('H:i');
	$LocationID = '';
	$IncidentType = 'Injury';
	$Severity = 'Minor';
	$Description = '';
	$ImmediateAction = '';
	$RootCause = '';
	$CorrectiveAction = '';
	$Status = 'Reported';
	$DaysLost = 0;
}

// Ensure all variables are set to avoid undefined variable warnings
if (!isset($IncidentNumber)) $IncidentNumber = '';
if (!isset($EmployeeID)) $EmployeeID = '';
if (!isset($LocationID)) $LocationID = '';
if (!isset($IncidentType)) $IncidentType = 'Injury';
if (!isset($Severity)) $Severity = 'Minor';
if (!isset($Description)) $Description = '';
if (!isset($ImmediateAction)) $ImmediateAction = '';
if (!isset($RootCause)) $RootCause = '';
if (!isset($CorrectiveAction)) $CorrectiveAction = '';
if (!isset($Status)) $Status = 'Reported';
if (!isset($DaysLost)) $DaysLost = 0;

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($IncidentID)) {
	echo '<input type="hidden" name="IncidentID" value="' . $IncidentID . '" />';
}

echo '<fieldset>
		<legend>' . (isset($IncidentID) ? __('Edit Safety Incident') : __('Report Safety Incident')) . '</legend>';

echo '<field>
		<label for="IncidentNumber">' . __('Incident Number') . ':</label>
		<input type="text" name="IncidentNumber" size="20" maxlength="20" value="' . htmlspecialchars($IncidentNumber, ENT_QUOTES, 'UTF-8') . '" ' . (isset($_GET['IncidentID']) ? 'readonly' : '') . ' />
		<field-help>' . __('Leave blank to auto-generate') . '</field-help>
	</field>';

echo '<field>
		<label for="EmployeeID">' . __('Employee (if applicable)') . ':</label>
		<select name="EmployeeID">
			<option value="">' . __('Not employee-related') . '</option>';

$SQL = "SELECT employeeid, CONCAT(firstname, ' ', lastname) as name
		FROM hremployees
		WHERE employmentstatus = 'Active'
		ORDER BY lastname, firstname";
$Result = DB_query($SQL);
while ($EmpRow = DB_fetch_array($Result)) {
	echo '<option value="' . $EmpRow['employeeid'] . '"' . ($EmployeeID == $EmpRow['employeeid'] ? ' selected' : '') . '>' .
		htmlspecialchars($EmpRow['name'], ENT_QUOTES, 'UTF-8') . '</option>';
}

echo '</select>
	</field>';

echo '<field>
		<label for="IncidentDate">' . __('Incident Date') . ':</label>
		<input type="date" name="IncidentDate" value="' . FormatDateForSQL($IncidentDate) . '" required />
	</field>';

echo '<field>
		<label for="IncidentTime">' . __('Incident Time') . ':</label>
		<input type="time" name="IncidentTime" value="' . htmlspecialchars($IncidentTime, ENT_QUOTES, 'UTF-8') . '" />
	</field>';

echo '<field>
		<label for="LocationID">' . __('Location') . ':</label>
		<select name="LocationID">
			<option value="">' . __('Select Location') . '</option>';

$SQL = "SELECT loccode, locationname FROM locations ORDER BY locationname";
$Result = DB_query($SQL);
while ($LocRow = DB_fetch_array($Result)) {
	echo '<option value="' . $LocRow['loccode'] . '"' . ($LocationID == $LocRow['loccode'] ? ' selected' : '') . '>' .
		htmlspecialchars($LocRow['locationname'], ENT_QUOTES, 'UTF-8') . '</option>';
}

echo '</select>
	</field>';

echo '<field>
		<label for="IncidentType">' . __('Incident Type') . ':</label>
		<select name="IncidentType" required>
			<option value="Injury"' . ($IncidentType == 'Injury' ? ' selected' : '') . '>' . __('Injury') . '</option>
			<option value="Near Miss"' . ($IncidentType == 'Near Miss' ? ' selected' : '') . '>' . __('Near Miss') . '</option>
			<option value="Property Damage"' . ($IncidentType == 'Property Damage' ? ' selected' : '') . '>' . __('Property Damage') . '</option>
			<option value="Environmental"' . ($IncidentType == 'Environmental' ? ' selected' : '') . '>' . __('Environmental') . '</option>
			<option value="Other"' . ($IncidentType == 'Other' ? ' selected' : '') . '>' . __('Other') . '</option>
		</select>
	</field>';

echo '<field>
		<label for="Severity">' . __('Severity') . ':</label>
		<select name="Severity" required>
			<option value="Minor"' . ($Severity == 'Minor' ? ' selected' : '') . '>' . __('Minor') . '</option>
			<option value="Moderate"' . ($Severity == 'Moderate' ? ' selected' : '') . '>' . __('Moderate') . '</option>
			<option value="Serious"' . ($Severity == 'Serious' ? ' selected' : '') . '>' . __('Serious') . '</option>
			<option value="Critical"' . ($Severity == 'Critical' ? ' selected' : '') . '>' . __('Critical') . '</option>
			<option value="Fatal"' . ($Severity == 'Fatal' ? ' selected' : '') . '>' . __('Fatal') . '</option>
		</select>
	</field>';

echo '<field>
		<label for="Description">' . __('Incident Description') . ':</label>
		<textarea name="Description" rows="4" cols="60" required>' . htmlspecialchars($Description, ENT_QUOTES, 'UTF-8') . '</textarea>
	</field>';

echo '<field>
		<label for="ImmediateAction">' . __('Immediate Action Taken') . ':</label>
		<textarea name="ImmediateAction" rows="3" cols="60">' . htmlspecialchars($ImmediateAction, ENT_QUOTES, 'UTF-8') . '</textarea>
	</field>';

echo '<field>
		<label for="RootCause">' . __('Root Cause Analysis') . ':</label>
		<textarea name="RootCause" rows="3" cols="60">' . htmlspecialchars($RootCause, ENT_QUOTES, 'UTF-8') . '</textarea>
	</field>';

echo '<field>
		<label for="CorrectiveAction">' . __('Corrective Action') . ':</label>
		<textarea name="CorrectiveAction" rows="3" cols="60">' . htmlspecialchars($CorrectiveAction, ENT_QUOTES, 'UTF-8') . '</textarea>
	</field>';

echo '<field>
		<label for="Status">' . __('Status') . ':</label>
		<select name="Status" required>
			<option value="Reported"' . ($Status == 'Reported' ? ' selected' : '') . '>' . __('Reported') . '</option>
			<option value="Under Investigation"' . ($Status == 'Under Investigation' ? ' selected' : '') . '>' . __('Under Investigation') . '</option>
			<option value="Resolved"' . ($Status == 'Resolved' ? ' selected' : '') . '>' . __('Resolved') . '</option>
			<option value="Closed"' . ($Status == 'Closed' ? ' selected' : '') . '>' . __('Closed') . '</option>
		</select>
	</field>';

echo '<field>
		<label for="DaysLost">' . __('Days Lost') . ':</label>
		<input type="number" name="DaysLost" min="0" value="' . $DaysLost . '" />
		<field-help>' . __('Number of work days lost due to this incident') . '</field-help>
	</field>';
echo '</fieldset>';

echo '<div class="centre">
			<input type="submit" name="Submit" value="' . __('Save Incident') . '" />
		</div>';

echo '</form>';

// List incidents
$SQL = "SELECT
		i.*,
		e.firstname,
		e.lastname,
		l.locationname
	FROM hrsafetyincidents i
	LEFT JOIN hremployees e ON i.employeeid = e.employeeid
	LEFT JOIN locations l ON i.locationid = l.loccode
	ORDER BY i.incidentdate DESC";

$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {

	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . __('Incident #') . '</th>
					<th>' . __('Date/Time') . '</th>
					<th>' . __('Employee') . '</th>
					<th>' . __('Location') . '</th>
					<th>' . __('Type') . '</th>
					<th>' . __('Severity') . '</th>
					<th>' . __('Status') . '</th>
					<th>' . __('Days Lost') . '</th>
					<th>' . __('Actions') . '</th>
				</tr>
			</thead>
			<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		$RowClass = ' class="striped_row"';
		if ($MyRow['severity'] == 'Critical' || $MyRow['severity'] == 'Fatal') {
			$RowClass = ' style="color:red;background:white"';
		}

		$MyRow['employeename'] = $MyRow['firstname'] . ' ' . $MyRow['lastname'];

		echo '<tr' . $RowClass . '>
				<td>' . htmlspecialchars($MyRow['incidentnumber'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . ConvertSQLDate(substr($MyRow['incidentdate'], 0, 10)) . ' ' . substr($MyRow['incidentdate'], 11, 5) . '</td>
				<td>' . ($MyRow['employeename'] ? htmlspecialchars($MyRow['employeename'], ENT_QUOTES, 'UTF-8') : '-') . '</td>
				<td>' . ($MyRow['locationname'] ? htmlspecialchars($MyRow['locationname'], ENT_QUOTES, 'UTF-8') : '-') . '</td>
				<td>' . htmlspecialchars($MyRow['incidenttype'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($MyRow['severity'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($MyRow['status'], ENT_QUOTES, 'UTF-8') . '</td>
				<td class="number">' . $MyRow['dayslost'] . '</td>
				<td class="centre">
					<a href="' . $_SERVER['PHP_SELF'] . '?IncidentID=' . urlencode($MyRow['incidentid']) . '">' . __('Edit') . '</a> |
					<a href="' . $_SERVER['PHP_SELF'] . '?IncidentID=' . urlencode($MyRow['incidentid']) . '&Delete=1" onclick="return confirm(\'' . __('Delete this incident?') . '\');">' . __('Delete') . '</a>
				</td>
			</tr>';
	}

	echo '</tbody>
		</table>';

} else {
	echo '<div class="centre">' . __('No safety incidents recorded') . '</div>';
}

include(__DIR__ . '/includes/footer.php');

?>
