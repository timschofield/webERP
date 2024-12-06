<?php

include('includes/session.php');
$Title = _('Open Location Maintenance Tasks');
include('includes/header.php');
include('includes/KLGeneralFunctions.php');

if (isset($_GET['SelectedIndex'])){
	$SelectedIndex = $_GET['SelectedIndex'];
} elseif(isset($_POST['SelectedIndex'])){
	$SelectedIndex = $_POST['SelectedIndex'];
}

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = FALSE;
echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title.'
	</p>
	<br />';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs are sensible

	$sql=	"SELECT COUNT(*)
			FROM klmaintenancetasks 
			WHERE loccode = '". $_POST['LocCode']. "'
				AND maintenancetype  = '". $_POST['MaintenanceType']. "'
				AND closed = 0";
	$result=DB_query($sql);
	$myrow=DB_fetch_row($result);

	$i=1;
//	if ($myrow[0]!=0 and !isset($SelectedIndex)) {
//		$InputError = TRUE;
//		prnMsg( _('Already exists an open maintenance task for the location and type of maintenace in the database. If you need, you can UPDATE the existing one.'),'error');
//		$Errors[$i] = 'CounterIndex';
//		$i++;
//	}

	$msg='';

	if (!$InputError){
		if (isset($SelectedIndex)) {
			/*SelectedIndex could also exist if submit had not been clicked this code would not run in this case cos submit is false of course	see the close code below*/
			if (isset($_POST['Description']) AND ($_POST['Description'] != '')){
				$sql = "INSERT INTO klmaintenancetaskupdates 
							(taskcounter,
							description,
							updateuser,
							updatedate)
						VALUES ('" . $SelectedIndex . "',
							'". $_POST['Description'] . "',
							'".$_SESSION['UserID'] . "',
							NOW())";

				$msg = 'The maintenance task '. $SelectedIndex .' has been updated';
				$result = DB_query($sql);
				prnMsg($msg,'success');
			}else{
				prnMsg("Trask description update was empty, so no update was recroded",'warn');
			}
		}else{
			/*SelectedIndex is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new status code form */
			$sql = "INSERT INTO klmaintenancetasks 
						(loccode,
						maintenancetype,
						description,
						closed,
						creationuser,
						creationdate)
					VALUES ('" .$_POST['LocCode'] . "',
						'".$_POST['MaintenanceType'] . "',
						'".$_POST['Description'] . "',
						'0',
						'".$_SESSION['UserID'] . "',
						NOW())";

			$msg = _('A new maintenance task has been created');
			$result = DB_query($sql);
			prnMsg($msg,'success');
			unset ($SelectedIndex);
			unset ($_POST['LocCode']);
		}
	}
} elseif (isset($_GET['close'])) {
	//the link to close a selected record was clicked instead of the submit button

	$sql = "UPDATE klmaintenancetasks SET
					closed = 1,
					closeuser = '" . $_SESSION['UserID'] . "',
					closedate = NOW() 
			WHERE counterindex = '".$SelectedIndex."'";
	$msg = 'The maintenance task '. $SelectedIndex .' has been closed';
	$result = DB_query($sql);
	prnMsg($msg,'success');
	
	//end if status code used in customer or supplier accounts
	unset ($_GET['close']);
	unset ($SelectedIndex);
}

if (!isset($SelectedIndex)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedIndex will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of status codes will be displayed with
links to close or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT klmaintenancetasks.counterindex, 
				klmaintenancetasks.loccode,
				locations.locationname,
				klmaintenancetasks.maintenancetype,
				klmaintenancetypes.description AS typedescription,
				klmaintenancetasks.description AS taskdescription,
				klmaintenancetasks.creationuser,
				klmaintenancetasks.creationdate
			FROM klmaintenancetasks
				INNER JOIN locations 
					ON locations.loccode=klmaintenancetasks.loccode 
				INNER JOIN klmaintenancetypes 
					ON klmaintenancetypes.maintenancetype=klmaintenancetasks.maintenancetype 
				INNER JOIN locationusers 
					ON locationusers.loccode=klmaintenancetasks.loccode 
						AND locationusers.userid='" .  $_SESSION['UserID'] . "'
						AND locationusers.canupd=1
			WHERE klmaintenancetasks.closed = 0
			ORDER BY klmaintenancetasks.counterindex";
	$result = DB_query($sql);

	echo '<table class="selection">
		<tr>
			<th>' .  '# Task'  . '</th>
			<th>' .  'Location'  . '</th>
			<th>' .  'Type'  . '</th>
			<th>' .  'User'  . '</th>
			<th>' .  'Date'  . '</th>
			<th>' .  'Description'  . '</th>
        </tr>';

	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($result)) {

		$k = StartEvenOrOddRow($k);
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="%s?SelectedIndex=%s">' . _('Update') . '</a></td>
				<td><a href="%s?SelectedIndex=%s&amp;close=1" onclick="return confirm(\'' . _('Are you sure you wish to close this maintenance task?') . '\');">' .  _('Close')  . '</a></td>
				</tr>',
				$myrow['counterindex'],
				$myrow['locationname'],
				$myrow['typedescription'],
				$myrow['creationuser'],
				ConvertSQLDateTime($myrow['creationdate']),
				$myrow['taskdescription'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
				$myrow['counterindex'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
				$myrow['counterindex']);

		// check if there are any updates to show
		$sqlupdates = "SELECT klmaintenancetaskupdates.counterindex, 
							klmaintenancetaskupdates.description AS updatedescription,
							klmaintenancetaskupdates.updateuser,
							klmaintenancetaskupdates.updatedate
						FROM klmaintenancetaskupdates
						WHERE klmaintenancetaskupdates.taskcounter = '".$myrow['counterindex']."'
						ORDER BY klmaintenancetaskupdates.counterindex";
		$resultupdates = DB_query($sqlupdates);
		while ($myupdates=DB_fetch_array($resultupdates)) {
			$k = StartSameColourRow($k);
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>',
					'',
					'',
					'',
					$myupdates['updateuser'],
					ConvertSQLDateTime($myupdates['updatedate']),
					$myupdates['updatedescription'],
					'',
					'',
					'',
					'');
		}
	} //END WHILE LIST LOOP
	echo '</table>';

} //end of ifs and buts!

if (isset($SelectedIndex)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show Open Maintenance Tasks') . '</a>
		</div>';
}

if (!isset($_GET['close'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedIndex) and ($InputError!=1)) {
		//editing an existing status code
		$ButtonText = "Update Task";

		$sql = "SELECT klmaintenancetasks.counterindex,
					klmaintenancetasks.loccode,
					locations.locationname,
					klmaintenancetasks.creationuser,
					klmaintenancetasks.creationdate,
					klmaintenancetasks.maintenancetype,
					klmaintenancetypes.description AS typedescription,
					klmaintenancetasks.description
				FROM klmaintenancetasks,locations,klmaintenancetypes
				WHERE locations.loccode = klmaintenancetasks.loccode
					AND klmaintenancetypes.maintenancetype = klmaintenancetasks.maintenancetype
					AND counterindex='".$SelectedIndex."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['CounterIndex'] = $myrow['counterindex'];
		$_POST['LocCode']  = $myrow['loccode'];
		$_POST['MaintenanceType']  = $myrow['maintenancetype'];
		$_POST['Description']  = $myrow['description'];

		echo '<input type="hidden" name="SelectedIndex" value="' . $SelectedIndex . '" />';
		echo '<input type="hidden" name="CounterIndex" value="' . $_POST['CounterIndex'] . '" />';
		echo '<table class="selection">
				<tr>
					<td>' .  _('# Task') .':</td>
					<td>' . $_POST['CounterIndex'] . '</td>
				</tr>';
		echo '	<tr>
					<td>' .  _('Location') .':</td>
					<td>' . $myrow['locationname'] . '</td>
				</tr>';
		echo '	<tr>
					<td>' .  _('Maintenance Type') .':</td>
					<td>' . $myrow['typedescription'] . '</td>
				</tr>';
		echo '	<tr>
					<td>' .  _('Description') .':</td>
					<td>' . $myrow['creationuser']. " @ " . ConvertSQLDateTime($myrow['creationdate']) . ": " . $myrow['description'] . '</td>
				</tr>';
		// check if there are any updates to show
		$sqlupdates = "SELECT klmaintenancetaskupdates.counterindex, 
							klmaintenancetaskupdates.description AS updatedescription,
							klmaintenancetaskupdates.updateuser,
							klmaintenancetaskupdates.updatedate
						FROM klmaintenancetaskupdates
						WHERE klmaintenancetaskupdates.taskcounter = '".$SelectedIndex."'
						ORDER BY klmaintenancetaskupdates.counterindex";
		$resultupdates = DB_query($sqlupdates);
		while ($myupdates=DB_fetch_array($resultupdates)) {
			echo '	<tr>
						<td></td>
						<td>' . $myupdates['updateuser']. " @ " . ConvertSQLDateTime($myupdates['updatedate']) . ": " .$myupdates['updatedescription'] . '</td>
					</tr>';
		}

	} else { //end of if $SelectedIndex only do the else when a new record is being entered
		$ButtonText = "Add Task";
		if (!isset($_POST['CounterIndex'])) {
			$_POST['CounterIndex'] = '';
		}
		if (!isset($_POST['LocCode'])) {
			$_POST['LocCode'] = '';
		}
		if (!isset($_POST['MaintenanceType'])) {
		$_POST['MaintenanceType'] = '';
		}
		
		echo '<br />
			<table class="selection">';

		$sql = "SELECT locations.loccode, 
					locations.locationname 
				FROM locations 
					INNER JOIN locationusers 
						ON locationusers.loccode=locations.loccode 
							AND locationusers.userid='" .  $_SESSION['UserID'] . "' 
							AND locationusers.canupd=1
				ORDER BY locationname";
		$resultStkLocs = DB_query($sql);

		echo '<tr>
				<td>' . _('Location') . ':</td>
				<td><select name="LocCode">';

		while ($myrow=DB_fetch_array($resultStkLocs)){
			if (isset($_POST['LocCode'])){
				if ($myrow['loccode'] == $_POST['LocCode']){
					echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname']. '</option>';
				} else {
					echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
				}
			} else {
				echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
			}
		}
		echo '</select></td>';

		$sql = "SELECT maintenancetype,
					description
				FROM klmaintenancetypes 
				ORDER BY description";
		$resultTypes = DB_query($sql);

		echo '<tr>
				<td>' . _('Maintenance Type') . ':</td>
				<td><select name="MaintenanceType">';

		while ($myrow=DB_fetch_array($resultTypes)){
			if (isset($_POST['MaintenanceType'])){
				if ($myrow['maintenancetype'] == $_POST['MaintenanceType']){
					echo '<option selected="selected" value="' . $myrow['maintenancetype'] . '">' . $myrow['description']. '</option>';
				} else {
					echo '<option value="' . $myrow['maintenancetype'] . '">' . $myrow['description'] . '</option>';
				}
			} else {
				echo '<option value="' . $myrow['maintenancetype'] . '">' . $myrow['description'] . '</option>';
			}
		}
		echo '</select></td>';
	}

	$_POST['Description'] = '';

	if (isset($_POST['Description'])) {
		$Description = AddCarriageReturns($_POST['Description']);
	} else {
		$Description ='';
	}
	echo '<tr>
			<td>' . _('Task Description') . '):</td>
			<td><textarea ' . (in_array('Description',$Errors) ?  'class="texterror"' : '' ) .'  name="Description" cols="60" rows="5">' . stripslashes($Description) . '</textarea></td>
		</tr>';

	echo '</tr>
			</table>
			<br />
			<div class="centre">
				<input tabindex="4" type="submit" name="submit" value="' . $ButtonText . '" />
			</div>
            </div>
			</form>';
} //end if record deleted no point displaying form to add record
include('includes/footer.php');
?>