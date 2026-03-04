<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Open Location Maintenance Tasks');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLGeneralFunctions.php');

if (isset($_GET['SelectedIndex'])){
	$SelectedIndex = $_GET['SelectedIndex'];
} elseif (isset($_POST['SelectedIndex'])){
	$SelectedIndex = $_POST['SelectedIndex'];
}

$Errors = array();

$InputError = false;
echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title.'
	</p>
	<br />';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs are sensible
	
	// Check if the required POST variables exist before using them
	if (!isset($_POST['LocCode'])) {
		$_POST['LocCode'] = '';
	}
	
	if (!isset($_POST['MaintenanceType'])) {
		$_POST['MaintenanceType'] = '';
	}
	
	if (!isset($_POST['Description'])) {
		$_POST['Description'] = '';
	}

	$SQL = "SELECT COUNT(*)
			FROM klmaintenancetasks 
			WHERE loccode = '". $_POST['LocCode']. "'
				AND maintenancetype  = '". $_POST['MaintenanceType']. "'
				AND closed = 0";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);

	$i = 1;
//	if ($MyRow[0]!=0 and !isset($SelectedIndex)) {
//		$InputError = true;
//		prnMsg( __('Already exists an open maintenance task for the location and type of maintenace in the database. If you need, you can UPDATE the existing one.'),'error');
//		$Errors[$i] = 'CounterIndex';
//		$i++;
//	}

	$Msg = '';

	if (!$InputError){
		if (isset($SelectedIndex)) {
			/*SelectedIndex could also exist if submit had not been clicked this code would not run in this case cos submit is false of course	see the close code below*/
			if (isset($_POST['Description']) AND ($_POST['Description'] != '')){
				$SQL = "INSERT INTO klmaintenancetaskupdates 
							(taskcounter,
							description,
							updateuser,
							updatedate)
						VALUES ('" . $SelectedIndex . "',
							'". $_POST['Description'] . "',
							'".$_SESSION['UserID'] . "',
							NOW())";

				$Msg = 'The maintenance task '. $SelectedIndex .' has been updated';
				$Result = DB_query($SQL);
				prnMsg($Msg, 'success');
			} else {
				prnMsg("Trask description update was empty, so no update was recroded", 'warn');
			}
		} else {
			/*SelectedIndex is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new status code form */
			
			// Validate inputs before trying to insert
			if ($_POST['LocCode'] == '') {
				prnMsg(__('A location must be selected'), 'error');
				$InputError = true;
			}
			
			if ($_POST['MaintenanceType'] == '') {
				prnMsg(__('A maintenance type must be selected'), 'error');
				$InputError = true;
			}
			
			if ($_POST['Description'] == '') {
				prnMsg(__('A description must be entered'), 'error');
				$InputError = true;
			}
			
			if (!$InputError) {
				$SQL = "INSERT INTO klmaintenancetasks 
							(loccode,
							maintenancetype,
							description,
							closed,
							creationuser,
							creationdate)
						VALUES ('" . $_POST['LocCode'] . "',
							'" . $_POST['MaintenanceType'] . "',
							'" . $_POST['Description'] . "',
							'0',
							'" . $_SESSION['UserID'] . "',
							NOW())";

				$Msg = __('A new maintenance task has been created');
				$Result = DB_query($SQL);
				prnMsg($Msg, 'success');
				unset($SelectedIndex);
				unset($_POST['LocCode']);
			}
		}
	}
} elseif (isset($_GET['close'])) {
	//the link to close a selected record was clicked instead of the submit button

	$SQL = "UPDATE klmaintenancetasks SET
					closed = 1,
					closeuser = '" . $_SESSION['UserID'] . "',
					closedate = NOW() 
			WHERE counterindex = '" . $SelectedIndex . "'";
	$Msg = 'The maintenance task '. $SelectedIndex .' has been closed';
	$Result = DB_query($SQL);
	prnMsg($Msg, 'success');
	
	//end if status code used in customer or supplier accounts
	unset($_GET['close']);
	unset($SelectedIndex);
}

if (!isset($SelectedIndex)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedIndex will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of status codes will be displayed with
links to close or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT klmaintenancetasks.counterindex,
				klmaintenancetasks.loccode,
				locations.zone,
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
			ORDER BY locations.zone, locations.locationname, klmaintenancetasks.counterindex";
	$Result = DB_query($SQL);

	echo '<table class="selection">
		<tr>
			<th>' .  'Zone'  . '</th>
			<th>' .  'Location'  . '</th>
			<th>' .  '# Task'  . '</th>
			<th>' .  'Type'  . '</th>
			<th>' .  'User'  . '</th>
			<th>' .  'Date'  . '</th>
			<th>' .  'Description'  . '</th>
	       </tr>';

	$k=0; //row colour counter
	while ($MyRow=DB_fetch_array($Result)) {

		$k = StartEvenOrOddRow($k);
		echo '<td>' . $MyRow['zone'] . '</td>
				<td>' . $MyRow['locationname'] . '</td>
				<td class="number">' . $MyRow['counterindex'] . '</td>
				<td>' . $MyRow['typedescription'] . '</td>
				<td>' . $MyRow['creationuser'] . '</td>
				<td>' . ConvertSQLDateTime($MyRow['creationdate']) . '</td>
				<td>' . $MyRow['taskdescription'] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedIndex=' . $MyRow['counterindex'] . '">' . __('Update') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedIndex=' . $MyRow['counterindex'] . '&amp;close=1" onclick="return confirm(\'' . __('Are you sure you wish to close this maintenance task?') . '\');">' . __('Close') . '</a></td>
				</tr>';

		// check if there are any updates to show
		$SQLupdates = "SELECT klmaintenancetaskupdates.counterindex, 
							klmaintenancetaskupdates.description AS updatedescription,
							klmaintenancetaskupdates.updateuser,
							klmaintenancetaskupdates.updatedate
						FROM klmaintenancetaskupdates
						WHERE klmaintenancetaskupdates.taskcounter = '".$MyRow['counterindex']."'
						ORDER BY klmaintenancetaskupdates.counterindex";
		$Resultupdates = DB_query($SQLupdates);
		while ($MyUpdates=DB_fetch_array($Resultupdates)) {
			$k = StartSameColourRow($k);
			echo '<td>' . '' . '</td>
					<td>' . '' . '</td>
					<td>' . '' . '</td>
					<td>' . '' . '</td>
					<td>' . $MyUpdates['updateuser'] . '</td>
					<td>' . ConvertSQLDateTime($MyUpdates['updatedate']) . '</td>
					<td>' . $MyUpdates['updatedescription'] . '</td>
					<td>' . '' . '</td>
					<td>' . '' . '</td>
					</tr>';
		}
	} //END WHILE LIST LOOP
	echo '</table>';

} //end of ifs and buts!

if (isset($SelectedIndex)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Show Open Maintenance Tasks') . '</a>
		</div>';
}

if (!isset($_GET['close'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedIndex) and ($InputError!=1)) {
		//editing an existing status code
		$ButtonText = "Update Task";

		$SQL = "SELECT klmaintenancetasks.counterindex,
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

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['CounterIndex'] = $MyRow['counterindex'];
		$_POST['LocCode']  = $MyRow['loccode'];
		$_POST['MaintenanceType']  = $MyRow['maintenancetype'];
		$_POST['Description']  = $MyRow['description'];

		echo '<input type="hidden" name="SelectedIndex" value="' . $SelectedIndex . '" />';
		echo '<input type="hidden" name="CounterIndex" value="' . $_POST['CounterIndex'] . '" />';
		echo '<table class="selection">
				<tr>
					<td>' .  __('# Task') .':</td>
					<td>' . $_POST['CounterIndex'] . '</td>
				</tr>';
		echo '	<tr>
					<td>' .  __('Location') .':</td>
					<td>' . $MyRow['locationname'] . '</td>
				</tr>';
		echo '	<tr>
					<td>' .  __('Maintenance Type') .':</td>
					<td>' . $MyRow['typedescription'] . '</td>
				</tr>';
		echo '	<tr>
					<td>' .  __('Description') .':</td>
					<td>' . $MyRow['creationuser']. " @ " . ConvertSQLDateTime($MyRow['creationdate']) . ": " . $MyRow['description'] . '</td>
				</tr>';
		// check if there are any updates to show
		$SQLupdates = "SELECT klmaintenancetaskupdates.counterindex, 
							klmaintenancetaskupdates.description AS updatedescription,
							klmaintenancetaskupdates.updateuser,
							klmaintenancetaskupdates.updatedate
						FROM klmaintenancetaskupdates
						WHERE klmaintenancetaskupdates.taskcounter = '".$SelectedIndex."'
						ORDER BY klmaintenancetaskupdates.counterindex";
		$Resultupdates = DB_query($SQLupdates);
		while ($MyUpdates=DB_fetch_array($Resultupdates)) {
			echo '	<tr>
						<td></td>
						<td>' . $MyUpdates['updateuser']. " @ " . ConvertSQLDateTime($MyUpdates['updatedate']) . ": " .$MyUpdates['updatedescription'] . '</td>
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

		$SQL = "SELECT locations.loccode, 
					locations.locationname 
				FROM locations 
					INNER JOIN locationusers 
						ON locationusers.loccode=locations.loccode 
							AND locationusers.userid='" .  $_SESSION['UserID'] . "' 
							AND locationusers.canupd=1
				ORDER BY locationname";
		$ResultStkLocs = DB_query($SQL);

		echo '<tr>
				<td>' . __('Location') . ':</td>
				<td><select name="LocCode">';

		while ($MyRow=DB_fetch_array($ResultStkLocs)){
			if (isset($_POST['LocCode'])){
				if ($MyRow['loccode'] == $_POST['LocCode']){
					echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname']. '</option>';
				} else {
					echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
				}
			} else {
				echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			}
		}
		echo '</select></td>';

		$SQL = "SELECT maintenancetype,
					description
				FROM klmaintenancetypes 
				ORDER BY description";
		$ResultTypes = DB_query($SQL);

		echo '<tr>
				<td>' . __('Maintenance Type') . ':</td>
				<td><select name="MaintenanceType">';

		while ($MyRow=DB_fetch_array($ResultTypes)){
			if (isset($_POST['MaintenanceType'])){
				if ($MyRow['maintenancetype'] == $_POST['MaintenanceType']){
					echo '<option selected="selected" value="' . $MyRow['maintenancetype'] . '">' . $MyRow['description']. '</option>';
				} else {
					echo '<option value="' . $MyRow['maintenancetype'] . '">' . $MyRow['description'] . '</option>';
				}
			} else {
				echo '<option value="' . $MyRow['maintenancetype'] . '">' . $MyRow['description'] . '</option>';
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
			<td>' . __('Task Description') . '):</td>
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
include(__DIR__ . '/includes/footer.php');
