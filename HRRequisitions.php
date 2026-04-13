<?php

/* Job Requisitions Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Job Requisitions');
$ViewTopic = 'HumanResources';
$BookMark = 'HRRequisitions';

include(__DIR__ . '/includes/header.php');

echo '<a class="toplink" href="' . $RootPath . '/HRDashboard.php">' . __('Return to HR Dashboard') . '</a>';

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Requisitions') . '" /> ' .
		__('Job Requisitions Management') . '
	</p>';

// Handle form submission
if (isset($_POST['Submit'])) {

	$InputError = 0;

	if (!isset($_POST['PositionID']) || $_POST['PositionID'] == '') {
		$InputError = 1;
		prnMsg(__('Position must be selected'), 'error');
	}
	if (!isset($_POST['RequestedBy']) || $_POST['RequestedBy'] == '') {
		$InputError = 1;
		prnMsg(__('Requested by is required'), 'error');
	}

	if ($InputError == 0) {

		$PositionID = (int)$_POST['PositionID'];
		$DepartmentID = isset($_POST['DepartmentID']) && $_POST['DepartmentID'] != '' ? (int)$_POST['DepartmentID'] : 'NULL';
		$NumberOfPositions = (int)$_POST['NumberOfPositions'];
		$RequestedBy = (int)$_POST['RequestedBy'];
		$RequestDate = FormatDateForSQL($_POST['RequestDate']);
		$TargetStartDate = isset($_POST['TargetStartDate']) && $_POST['TargetStartDate'] != '' ? "'" . FormatDateForSQL($_POST['TargetStartDate']) . "'" : 'NULL';
		$Status = $_POST['Status'];
		$Priority = $_POST['Priority'];
		$Justification = $_POST['Justification'];

		if (isset($_GET['RequisitionID']) && $_GET['RequisitionID'] != '') {
			// Update
			$RequisitionID = (int)$_GET['RequisitionID'];

			$SQL = "UPDATE hrrequisitions SET
					positionid = " . $PositionID . ",
					departmentid = " . $DepartmentID . ",
					numberofpositions = " . $NumberOfPositions . ",
					requestedby = " . $RequestedBy . ",
					requestdate = '" . $RequestDate . "',
					targetstartdate = " . $TargetStartDate . ",
					status = '" . $Status . "',
					priority = '" . $Priority . "',
					justification = '" . $Justification . "'
				WHERE requisitionid = " . $RequisitionID;

			$Result = DB_query($SQL);
			prnMsg(__('Requisition has been updated'), 'success');

		} else {
			// Insert - generate requisition number
			$RequisitionNumber = 'REQ-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

			$SQL = "INSERT INTO hrrequisitions (
					requisitionnumber,
					positionid,
					departmentid,
					numberofpositions,
					requestedby,
					requestdate,
					targetstartdate,
					status,
					priority,
					justification,
					createdby,
					createddate
				) VALUES (
					'" . $RequisitionNumber . "',
					'" . $PositionID . "',
					'" . $DepartmentID . "',
					'" . $NumberOfPositions . "',
					'" . $RequestedBy . "',
					'" . $RequestDate . "',
					" . $TargetStartDate . ",
					'" . $Status . "',
					'" . $Priority . "',
					'" . $Justification . "',
					'" . $_SESSION['UserID'] . "',
					NOW()
				)";

			$Result = DB_query($SQL);
			prnMsg(__('Requisition has been created'), 'success');
		}
	}
}

// Handle delete
if (isset($_GET['Delete']) && isset($_GET['RequisitionID'])) {
	$RequisitionID = (int)$_GET['RequisitionID'];

	$SQL = "DELETE FROM hrrequisitions WHERE requisitionid = " . $RequisitionID;
	$Result = DB_query($SQL);

	prnMsg(__('Requisition has been deleted'), 'success');
	unset($_GET['RequisitionID']);
}

// Load for edit
if (isset($_GET['RequisitionID'])) {
	$RequisitionID = (int)$_GET['RequisitionID'];

	$SQL = "SELECT * FROM hrrequisitions WHERE requisitionid = " . $RequisitionID;
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);

		$PositionID = $MyRow['positionid'];
		$DepartmentID = $MyRow['departmentid'];
		$NumberOfPositions = $MyRow['numberofpositions'];
		$RequestedBy = $MyRow['requestedby'];
		$RequestDate = ConvertSQLDate($MyRow['requestdate']);
		$TargetStartDate = $MyRow['targetstartdate'] ? ConvertSQLDate($MyRow['targetstartdate']) : '';
		$Status = $MyRow['status'];
		$Priority = $MyRow['priority'];
		$Justification = $MyRow['justification'];
	}
}

// Entry form
if (!isset($PositionID)) {
	$PositionID = '';
	$DepartmentID = '';
	$NumberOfPositions = 1;
	$RequestedBy = $_SESSION['UserID'];
	$RequestDate = date($_SESSION['DefaultDateFormat']);
	$TargetStartDate = '';
	$Status = 'Pending Approval';
	$Priority = 'Medium';
	$Justification = '';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . (isset($_GET['RequisitionID']) ? '?RequisitionID=' . urlencode($_GET['RequisitionID']) : '') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . (isset($_GET['RequisitionID']) ? __('Edit Requisition') : __('Create New Requisition')) . '</legend>';

echo '<field>
		<label for="PositionID">' . __('Position') . ':</label>
		<select name="PositionID" required>
			<option value="">' . __('Select Position') . '</option>';

$SQL = "SELECT positionid, positioncode, positiontitle FROM hrpositions ORDER BY positioncode";
$Result = DB_query($SQL);
while ($PosRow = DB_fetch_array($Result)) {
	echo '<option value="' . $PosRow['positionid'] . '"' . ($PositionID == $PosRow['positionid'] ? ' selected' : '') . '>' .
		htmlspecialchars($PosRow['positioncode'] . ' - ' . $PosRow['positiontitle'], ENT_QUOTES, 'UTF-8') . '</option>';
}

echo '</select>
	</field>';

echo '<field>
		<label for="DepartmentID">' . __('Department') . ':</label>
		<select name="DepartmentID">
			<option value="">' . __('Select Department') . '</option>';

$SQL = "SELECT departmentid, departmentcode, description FROM departments ORDER BY departmentcode";
$Result = DB_query($SQL);
while ($DeptRow = DB_fetch_array($Result)) {
	echo '<option value="' . $DeptRow['departmentid'] . '"' . ($DepartmentID == $DeptRow['departmentid'] ? ' selected' : '') . '>' .
		htmlspecialchars($DeptRow['departmentcode'] . ' - ' . $DeptRow['description'], ENT_QUOTES, 'UTF-8') . '</option>';
}

echo '</select>
	</field>';

echo '<field>
		<label for="NumberOfPositions">' . __('Number of Positions') . ':</label>
		<input type="number" name="NumberOfPositions" min="1" value="' . $NumberOfPositions . '" required />
	</field>';

echo '<field>
		<label for="RequestedBy">' . __('Requested By') . ':</label>
		<select name="RequestedBy" required>
			<option value="">' . __('Select User') . '</option>';

$SQL = "SELECT userid, realname FROM www_users WHERE blocked = 0 ORDER BY realname";
$Result = DB_query($SQL);
while ($UserRow = DB_fetch_array($Result)) {
	echo '<option value="' . $UserRow['userid'] . '"' . ($RequestedBy == $UserRow['userid'] ? ' selected' : '') . '>' .
		htmlspecialchars($UserRow['realname'], ENT_QUOTES, 'UTF-8') . '</option>';
}

echo '</select>
	</field>';

echo '<field>
		<label for="RequestDate">' . __('Request Date') . ':</label>
		<input type="date" name="RequestDate" value="' . FormatDateForSQL($RequestDate) . '" required />
	</field>';

echo '<field>
		<label for="TargetStartDate">' . __('Target Start Date') . ':</label>
		<input type="date" name="TargetStartDate" value="' . ($TargetStartDate ? FormatDateForSQL($TargetStartDate) : '') . '" />
	</field>';

echo '<field>
		<label for="Status">' . __('Status') . ':</label>
		<select name="Status" required>
			<option value="Pending Approval"' . ($Status == 'Pending Approval' ? ' selected' : '') . '>' . __('Pending Approval') . '</option>
			<option value="Approved"' . ($Status == 'Approved' ? ' selected' : '') . '>' . __('Approved') . '</option>
			<option value="In Progress"' . ($Status == 'In Progress' ? ' selected' : '') . '>' . __('In Progress') . '</option>
			<option value="Filled"' . ($Status == 'Filled' ? ' selected' : '') . '>' . __('Filled') . '</option>
			<option value="Cancelled"' . ($Status == 'Cancelled' ? ' selected' : '') . '>' . __('Cancelled') . '</option>
			<option value="On Hold"' . ($Status == 'On Hold' ? ' selected' : '') . '>' . __('On Hold') . '</option>
		</select>
	</field>';

echo '<field>
		<label for="Priority">' . __('Priority') . ':</label>
		<select name="Priority" required>
			<option value="Low"' . ($Priority == 'Low' ? ' selected' : '') . '>' . __('Low') . '</option>
			<option value="Medium"' . ($Priority == 'Medium' ? ' selected' : '') . '>' . __('Medium') . '</option>
			<option value="High"' . ($Priority == 'High' ? ' selected' : '') . '>' . __('High') . '</option>
			<option value="Urgent"' . ($Priority == 'Urgent' ? ' selected' : '') . '>' . __('Urgent') . '</option>
		</select>
	</field>';

echo '<field>
		<label for="Justification">' . __('Justification') . ':</label>
		<textarea name="Justification" rows="4" cols="60" required>' . htmlspecialchars($Justification, ENT_QUOTES, 'UTF-8') . '</textarea>
	</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Save Requisition') . '" />
	</div>';

echo '</form>';

// List requisitions
$SQL = "SELECT
		r.requisitionid,
		r.numberofpositions,
		r.requestdate,
		r.targetstartdate,
		r.status,
		r.priority,
		p.positioncode,
		p.positiontitle,
		d.description
	FROM hrrequisitions r
	INNER JOIN hrpositions p ON r.positionid = p.positionid
	LEFT JOIN departments d ON r.departmentid = d.departmentid
	ORDER BY r.requestdate DESC";

$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {

	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . __('Req ID') . '</th>
					<th>' . __('Position') . '</th>
					<th>' . __('Department') . '</th>
					<th>' . __('# Positions') . '</th>
					<th>' . __('Request Date') . '</th>
					<th>' . __('Target Start') . '</th>
					<th>' . __('Status') . '</th>
					<th>' . __('Priority') . '</th>
					<th>' . __('Actions') . '</th>
				</tr>
			</thead>
			<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr class="striped_row">
				<td>' . $MyRow['requisitionid'] . '</td>
				<td>' . htmlspecialchars($MyRow['positioncode'] . ' - ' . $MyRow['positiontitle'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($MyRow['description'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . $MyRow['numberofpositions'] . '</td>
				<td>' . ConvertSQLDate($MyRow['requestdate']) . '</td>
				<td>' . ($MyRow['targetstartdate'] ? ConvertSQLDate($MyRow['targetstartdate']) : '-') . '</td>
				<td>' . htmlspecialchars($MyRow['status'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($MyRow['priority'], ENT_QUOTES, 'UTF-8') . '</td>
				<td class="centre">
					<a href="' . $_SERVER['PHP_SELF'] . '?RequisitionID=' . urlencode($MyRow['requisitionid']) . '">' . __('Edit') . '</a> |
					<a href="' . $_SERVER['PHP_SELF'] . '?RequisitionID=' . urlencode($MyRow['requisitionid']) . '&Delete=1" onclick="return confirm(\'' . __('Delete this requisition?') . '\');">' . __('Delete') . '</a>
				</td>
			</tr>';
	}
} else {
	echo '<div class="centre">' . __('No requisitions found') . '</div>';
}

echo '</tbody>
	</table>';

include(__DIR__ . '/includes/footer.php');

?>
