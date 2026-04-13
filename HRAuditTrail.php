<?php

/* HR Audit Trail */

require(__DIR__ . '/includes/session.php');

$Title = __('HR Audit Trail');
$ViewTopic = 'HumanResources';
$BookMark = 'HRAuditTrail';

include(__DIR__ . '/includes/header.php');

echo '<a class="toplink" href="' . $RootPath . '/HRDashboard.php">' . __('Return to HR Dashboard') . '</a>';

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/reports.png" title="' . __('Audit Trail') . '" /> ' .
		__('HR System Audit Trail') . '
	</p>';

// Search and filter form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Filter Audit Trail') . '</legend>';

echo '<field>
		<label for="StartDate">' . __('Start Date') . ':</label>
		<input type="date" name="StartDate" value="' . (isset($_POST['StartDate']) ? $_POST['StartDate'] : date('Y-m-01')) . '" />
	</field>';

echo '<field>
		<label for="EndDate">' . __('End Date') . ':</label>
		<input type="date" name="EndDate" value="' . (isset($_POST['EndDate']) ? $_POST['EndDate'] : date('Y-m-d')) . '" />
	</field>';

echo '<field>
		<label for="TableName">' . __('Table') . ':</label>
		<select name="TableName">
			<option value="">' . __('All Tables') . '</option>
			<option value="hremployees"' . (isset($_POST['TableName']) && $_POST['TableName'] == 'hremployees' ? ' selected' : '') . '>hremployees</option>
			<option value="hrpositions"' . (isset($_POST['TableName']) && $_POST['TableName'] == 'hrpositions' ? ' selected' : '') . '>hrpositions</option>
			<option value="departments"' . (isset($_POST['TableName']) && $_POST['TableName'] == 'departments' ? ' selected' : '') . '>departments</option>
			<option value="hrpayhistory"' . (isset($_POST['TableName']) && $_POST['TableName'] == 'hrpayhistory' ? ' selected' : '') . '>hrpayhistory</option>
			<option value="hrperfappraisals"' . (isset($_POST['TableName']) && $_POST['TableName'] == 'hrperfappraisals' ? ' selected' : '') . '>hrperfappraisals</option>
			<option value="hrrequisitions"' . (isset($_POST['TableName']) && $_POST['TableName'] == 'hrrequisitions' ? ' selected' : '') . '>hrrequisitions</option>
			<option value="hrapplicants"' . (isset($_POST['TableName']) && $_POST['TableName'] == 'hrapplicants' ? ' selected' : '') . '>hrapplicants</option>
		</select>
	</field>';

echo '<field>
		<label for="ActionType">' . __('Action') . ':</label>
		<select name="ActionType">
			<option value="">' . __('All Actions') . '</option>
			<option value="INSERT"' . (isset($_POST['ActionType']) && $_POST['ActionType'] == 'INSERT' ? ' selected' : '') . '>' . __('Insert') . '</option>
			<option value="UPDATE"' . (isset($_POST['ActionType']) && $_POST['ActionType'] == 'UPDATE' ? ' selected' : '') . '>' . __('Update') . '</option>
			<option value="DELETE"' . (isset($_POST['ActionType']) && $_POST['ActionType'] == 'DELETE' ? ' selected' : '') . '>' . __('Delete') . '</option>
		</select>
	</field>';

echo '<field>
		<label for="UserID">' . __('User') . ':</label>
		<input type="text" name="UserID" size="20" value="' . (isset($_POST['UserID']) ? $_POST['UserID'] : '') . '" />
	</field>';

echo '</fieldset>';

echo '<div class="centre">
			<input type="submit" name="Search" value="' . __('Search') . '" />
		</div>';
echo '</form>';

// Build query
$SQL = "SELECT * FROM hraudittrail WHERE 1=1";

if (isset($_POST['StartDate']) && $_POST['StartDate'] != '') {
	$StartDate = FormatDateForSQL($_POST['StartDate']);
	$SQL .= " AND changeddate >= '" . $StartDate . "'";
}

if (isset($_POST['EndDate']) && $_POST['EndDate'] != '') {
	$EndDate = FormatDateForSQL($_POST['EndDate']);
	$SQL .= " AND changeddate <= '" . $EndDate . " 23:59:59'";
}

if (isset($_POST['TableName']) && $_POST['TableName'] != '') {
	$TableName = $_POST['TableName'];
	$SQL .= " AND tablename = '" . $TableName . "'";
}

if (isset($_POST['ActionType']) && $_POST['ActionType'] != '') {
	$ActionType = $_POST['ActionType'];
	$SQL .= " AND actiontype = '" . $ActionType . "'";
}

if (isset($_POST['UserID']) && $_POST['UserID'] != '') {
	$UserID = $_POST['UserID'];
	$SQL .= " AND changedby LIKE '%" . $UserID . "%'";
}

$SQL .= " ORDER BY changeddate DESC LIMIT 500";

$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {

	echo '<table class="selection">
			<thead>
				<tr>
					<th colspan="6">' . __('Showing most recent 500 records matching criteria') . '</th>
				</tr>
				<tr>
					<th>' . __('Date/Time') . '</th>
					<th>' . __('User') . '</th>
					<th>' . __('Table') . '</th>
					<th>' . __('Action') . '</th>
					<th>' . __('Record ID') . '</th>
					<th>' . __('Details') . '</th>
				</tr>
			</thead>
			<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {

		$RowClass = '';
		if ($MyRow['actiontype'] == 'DELETE') {
			$RowClass = ' class="warn"';
		}

		echo '<tr' . $RowClass . '>
				<td>' . $MyRow['changeddate'] . '</td>
				<td>' . htmlspecialchars($MyRow['changedby'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars($MyRow['tablename'], ENT_QUOTES, 'UTF-8') . '</td>
				<td><strong>' . htmlspecialchars($MyRow['actiontype'], ENT_QUOTES, 'UTF-8') . '</strong></td>
				<td>' . htmlspecialchars($MyRow['recordid'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . htmlspecialchars(mb_substr($MyRow['oldvalues'], 0, 100), ENT_QUOTES, 'UTF-8') . (mb_strlen($MyRow['oldvalues']) > 100 ? '...' : '') . '</td>
			</tr>';
	}
} else {
	echo '<div class="centre">' . __('No audit records found') . '</divr>';
}

echo '</tbody>
	</table>';

include(__DIR__ . '/includes/footer.php');

?>
