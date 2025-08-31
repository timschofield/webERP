<?php

require(__DIR__ . '/includes/session.php');

//$Title = __('My Maintenance Jobs');
$Title = __('Fixed Assets Maintenance Schedule');
$ViewTopic = 'FixedAssets';
$BookMark = 'AssetMaintenance';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/group_add.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

if (isset($_GET['Complete'])) {
	$Result = DB_query("UPDATE fixedassettasks
						SET lastcompleted = CURRENT_DATE
						WHERE taskid='" . $_GET['TaskID'] . "'");
}

$SQL="SELECT taskid,
				fixedassettasks.assetid,
				description,
				taskdescription,
				frequencydays,
				lastcompleted,
				ADDDATE(lastcompleted,frequencydays) AS duedate,
				userresponsible,
				realname,
				manager
		FROM fixedassettasks
		INNER JOIN fixedassets
		ON fixedassettasks.assetid=fixedassets.assetid
		INNER JOIN www_users
		ON fixedassettasks.userresponsible=www_users.userid
		WHERE userresponsible='" . $_SESSION['UserID'] . "'
		OR manager = '" . $_SESSION['UserID'] . "'
		ORDER BY ADDDATE(lastcompleted,frequencydays) DESC";

$ErrMsg = __('The maintenance schedule cannot be retrieved because');
$Result = DB_query($SQL, $ErrMsg);

echo '<table class="selection">
     <tr>
		<th>' . __('Task ID') . '</th>
		<th>' . __('Asset') . '</th>
		<th>' . __('Description') . '</th>
		<th>' . __('Last Completed') . '</th>
		<th>' . __('Due By') . '</th>
		<th>' . __('Person') . '</th>
		<th>' . __('Manager') . '</th>
		<th>' . __('Now Complete') . '</th>
    </tr>';

while ($MyRow=DB_fetch_array($Result)) {

	if ($MyRow['manager']!=''){
		$ManagerResult = DB_query("SELECT realname FROM www_users WHERE userid='" . $MyRow['manager'] . "'");
		$ManagerRow = DB_fetch_array($ManagerResult);
		$ManagerName = $ManagerRow['realname'];
	} else {
		$ManagerName = __('No Manager Set');
	}

	echo '<tr>
			<td>' . $MyRow['taskid'] . '</td>
			<td>' . $MyRow['description'] . '</td>
			<td>' . $MyRow['taskdescription'] . '</td>
			<td>' . ConvertSQLDate($MyRow['lastcompleted']) . '</td>
			<td>' . ConvertSQLDate($MyRow['duedate']) . '</td>
			<td>' . $MyRow['realname'] . '</td>
			<td>' . $ManagerName . '</td>
			<td><a href="'.$RootPath.'/MaintenanceUserSchedule.php?Complete=Yes&amp;TaskID=' . $MyRow['taskid'] .'" onclick="return confirm(\'' . __('Are you sure you wish to mark this maintenance task as completed?') . '\');">' . __('Mark Completed') . '</a></td>
		</tr>';
}

echo '</table><br /><br />';

include('includes/footer.php');
