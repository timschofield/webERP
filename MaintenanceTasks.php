<?php

/* $Id: MaintenanceTasks.php 5231 2012-04-07 18:10:09Z daitnree $*/

include('includes/session.php');

$Title = _('Fixed Asset Maintenance Tasks');

$ViewTopic = 'FixedAssets';
$BookMark = 'AssetMaintenance';

include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/group_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';


if (isset($_POST['Submit'])) {
	if (!is_numeric(filter_number_format($_POST['FrequencyDays'])) OR filter_number_format($_POST['FrequencyDays']) < 0){
		prnMsg(_('The days before a task falls due is expected to be a postive'),'error');
	} else {
		$sql="INSERT INTO fixedassettasks (assetid,
											taskdescription,
											frequencydays,
											userresponsible,
											manager,
											lastcompleted)
						VALUES( '" . $_POST['AssetID'] . "',
								'" . $_POST['TaskDescription'] . "',
								'" . filter_number_format($_POST['FrequencyDays']) . "',
								'" . $_POST['UserResponsible'] . "',
								'" . $_POST['Manager'] . "',
								'" . Date('Y-m-d') . "' )";
		$ErrMsg = _('The authentication details cannot be inserted because');
		$Result=DB_query($sql,$ErrMsg);
		unset($_POST['AssetID']);
		unset($_POST['TaskDescription']);
		unset($_POST['FrequencyDays']);
		unset($_POST['Manager']);
		unset($_POST['UserResponsible']);
	}
}

if (isset($_POST['Update'])) {
	if (!is_numeric(filter_number_format($_POST['FrequencyDays'])) OR filter_number_format($_POST['FrequencyDays']) < 0){
		prnMsg(_('The days before a task falls due is expected to be a postive'),'error');
	} else {
		$sql="UPDATE fixedassettasks SET
				assetid = '" . $_POST['AssetID'] . "',
				taskdescription='".$_POST['TaskDescription'] ."',
				frequencydays='" . filter_number_format($_POST['FrequencyDays'])."',
				userresponsible='" . $_POST['UserResponsible'] . "',
				manager='" . $_POST['Manager'] . "'
				WHERE taskid='".$_POST['TaskID']."'";

		$ErrMsg = _('The task details cannot be updated because');
		$Result=DB_query($sql,$ErrMsg);
		unset($_POST['AssetID']);
		unset($_POST['TaskDescription']);
		unset($_POST['FrequencyDays']);
		unset($_POST['Manager']);
		unset($_POST['UserResponsible']);
	}
}

if (isset($_GET['Delete'])) {
	$sql="DELETE FROM fixedassettasks
		WHERE taskid='".$_GET['TaskID']."'";

	$ErrMsg = _('The maintenance task cannot be deleted because');
	$Result=DB_query($sql,$ErrMsg);
}

$sql="SELECT taskid,
				fixedassettasks.assetid,
				description,
				taskdescription,
				frequencydays,
				lastcompleted,
				userresponsible,
				realname,
				manager
		FROM fixedassettasks
		INNER JOIN fixedassets
		ON fixedassettasks.assetid=fixedassets.assetid
		INNER JOIN www_users
		ON fixedassettasks.userresponsible=www_users.userid";

$ErrMsg = _('The maintenance task details cannot be retrieved because');
$Result=DB_query($sql,$ErrMsg);

echo '<table class="selection">
     <tr>
		<th>' . _('Task ID') . '</th>
		<th>' . _('Asset') . '</th>
		<th>' . _('Description') . '</th>
		<th>' . _('Last Completed') . '</th>
		<th>' . _('Person') . '</th>
		<th>' . _('Manager') . '</th>
    </tr>';

while ($myrow=DB_fetch_array($Result)) {

	if ($myrow['manager']!=''){
		$ManagerResult = DB_query("SELECT realname FROM www_users WHERE userid='" . $myrow['manager'] . "'");
		$ManagerRow = DB_fetch_array($ManagerResult);
		$ManagerName = $ManagerRow['realname'];
	} else {
		$ManagerName = _('No Manager Set');
	}

	echo '<tr>
			<td>' . $myrow['taskid'] . '</td>
			<td>' . $myrow['description'] . '</td>
			<td>' . $myrow['taskdescription'] . '</td>
			<td>' . ConvertSQLDate($myrow['lastcompleted']) . '</td>
			<td>' . $myrow['realname'] . '</td>
			<td>' . $ManagerName . '</td>
			<td><a href="'.$RootPath.'/MaintenanceTasks.php?Edit=Yes&amp;TaskID=' . $myrow['taskid'] .'">' . _('Edit') . '</a></td>
			<td><a href="'.$RootPath.'/MaintenanceTasks.php?Delete=Yes&amp;TaskID=' . $myrow['taskid'] .'" onclick="return confirm(\'' . _('Are you sure you wish to delete this maintenance task?') . '\');">' . _('Delete') . '</a></td>
		</tr>';
}

echo '</table><br /><br />';


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" id="form1">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';

if (isset($_GET['Edit'])) {
	echo '<tr>
			<td>' . _('Task ID') . '</td>
                        <td>' . $_GET['TaskID'] . '</td>
		</tr>';
	echo '<input type="hidden" name="TaskID" value="'.$_GET['TaskID'].'" />';
	$sql="SELECT assetid,
				taskdescription,
				frequencydays,
				lastcompleted,
				userresponsible,
				manager
			FROM fixedassettasks
			WHERE taskid='".$_GET['TaskID']."'";
	$ErrMsg = _('The maintenance task details cannot be retrieved because');
	$result=DB_query($sql,$ErrMsg);
	$myrow=DB_fetch_array($result);
	$_POST['TaskDescription'] = $myrow['taskdescription'];
	$_POST['FrequencyDays'] = $myrow['frequencydays'];
	$_POST['UserResponsible'] = $myrow['userresponsible'];
	$_POST['Manager'] = $myrow['manager'];
	$_POST['AssetID'] = $myrow['assetid'];
}

if (!isset($_POST['TaskDescription'])){
	$_POST['TaskDescription']='';
}
if (!isset($_POST['FrequencyDays'])){
	$_POST['FrequencyDays']='';
}
if (!isset($_POST['UserResponsible'])){
	 $_POST['UserResponsible']= '';
}
if (!isset($_POST['Manager'])){
	$_POST['Manager']='';
}
if (!isset($_POST['AssetID'])){
	$_POST['AssetID']='';
}

echo '<tr>
		<td>' . _('Asset to Maintain').':</td>
		<td><select required="required" name="AssetID">';
$AssetSQL="SELECT assetid, description FROM fixedassets";
$AssetResult=DB_query($AssetSQL);
while ($myrow=DB_fetch_array($AssetResult)) {
	if ($myrow['assetid']==$_POST['AssetID']) {
		echo '<option selected="selected" value="'.$myrow['assetid'].'">' . $myrow['assetid'] . ' - ' . $myrow['description']  . '</option>';
	} else {
		echo '<option value="'.$myrow['assetid'].'">' . $myrow['assetid'] . ' - ' . $myrow['description']  . '</option>';
	}
}
echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Task Description').':</td>
		<td><textarea name="TaskDescription" required="required" cols="40" rows="3">' . $_POST['TaskDescription'] . '</textarea></td>
	</tr>';

echo '<tr>
		<td>' . _('Days Before Task Due').':</td>
		<td><input type="text" class="integer" required="required" name="FrequencyDays" size="5" maxlength="5" value="' . $_POST['FrequencyDays'] . '" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Responsible') . ':</td>
		<td><select required="required" name="UserResponsible">';
$UserSQL="SELECT userid FROM www_users";
$UserResult=DB_query($UserSQL);
while ($myrow=DB_fetch_array($UserResult)) {
	if ($myrow['userid']==$_POST['UserResponsible']) {
		echo '<option selected="selected" value="'.$myrow['userid'].'">' . $myrow['userid'] . '</option>';
	} else {
		echo '<option value="'.$myrow['userid'].'">' . $myrow['userid'] . '</option>';
	}
}
echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Manager').':</td>
		<td><select required="required" name="Manager">';
if ($_POST['Manager']==''){
	echo '<option selected="selected" value="">' . _('No Manager') . '</option>';
} else {
	echo '<option value="">' . _('No Manager') . '</option>';
}
$ManagerSQL="SELECT userid FROM www_users";
$ManagerResult=DB_query($UserSQL);
while ($myrow=DB_fetch_array($ManagerResult)) {
	if ($myrow['userid']==$_POST['Manager']) {
		echo '<option selected="selected" value="'.$myrow['userid'].'">' . $myrow['userid'] . '</option>';
	} else {
		echo '<option value="'.$myrow['userid'].'">' . $myrow['userid'] . '</option>';
	}
}
echo '</select></td>
	</tr>
	</table>';

if (isset($_GET['Edit'])) {
	echo '<br />
			<div class="centre">
				<input type="submit" name="Update" value="'._('Update Task').'" />
			</div>';
} else {
	echo '<br />
		<div class="centre">
			<input type="submit" name="Submit" value="'._('Enter New Task').'" />
		</div>';
}
echo '</div>
        </form>';
include('includes/footer.php');
?>