<?php
/* $Revision: 1.8 $ */

$PageSecurity = 15;

include('includes/session.inc');

$title = _('Units Of Measure');

include('includes/header.inc');

if ( isset($_GET['SelectedMeasureID']) )
	$SelectedMeasureID = $_GET['SelectedMeasureID'];
elseif (isset($_POST['SelectedMeasureID']))
	$SelectedMeasureID = $_POST['SelectedMeasureID'];

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strpos($_POST['MeasureName'],'&')>0 OR strpos($_POST['MeasureName'],"'")>0) {
		$InputError = 1;
		prnMsg( _('The unit of measure cannot contain the character') . " '&' " . _('or the character') ." '",'error');
	}
	if (trim($_POST['MeasureName']) == '') {
		$InputError = 1;
		prnMsg( _('The unit of measure may not be empty'), 'error');
	}

	if ($_POST['SelectedMeasureID']!='' AND $InputError !=1) {

		/*SelectedMeasureID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM unitsofmeasure
				WHERE unitid <> " . $SelectedMeasureID ."
				AND unitname ".LIKE." '" . $_POST['MeasureName'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The unit of measure can not be renamed because another with the same name already exist.'),'error');
		} else {
			// Get the old name and check that the record still exist neet to be very carefull here
			// idealy this is one of those sets that should be in a stored procedure simce even the checks are
			// relavant
			$sql = "SELECT unitname FROM unitsofmeasure
				WHERE unitid = " . $SelectedMeasureID;
			$result = DB_query($sql,$db);
			if ( DB_num_rows($result) != 0 ) {
				// This is probably the safest way there is
				$myrow = DB_fetch_row($result);
				$OldMeasureName = $myrow[0];
				$sql = array();
				$sql[] = "UPDATE unitsofmeasure
					SET unitname='" . $_POST['MeasureName'] . "'
					WHERE unitname ".LIKE." '".$OldMeasureName."'";
				$sql[] = "UPDATE stockmaster
					SET units='" . $_POST['MeasureName'] . "'
					WHERE units ".LIKE." '" . $OldMeasureName . "'";
				$sql[] = "UPDATE contracts
					SET units='" . $_POST['MeasureName'] . "'
					WHERE units ".LIKE." '" . $OldMeasureName . "'";
			} else {
				$InputError = 1;
				prnMsg( _('The unit of measure no longer exist.'),'error');
			}
		}
		$msg = _('Unit of measure changed');
	} elseif ($InputError !=1) {
		/*SelectedMeasureID is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM unitsofmeasure
				WHERE unitname " .LIKE. " '".$_POST['MeasureName'] ."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The unit of measure can not be created because another with the same name already exists.'),'error');
		} else {
			$sql = "INSERT INTO unitsofmeasure (
						unitname )
				VALUES (
					'" . $_POST['MeasureName'] ."'
					)";
		}
		$msg = _('New unit of measure added');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		if (is_array($sql)) {
			$result = DB_Txn_Begin($db);
			$tmpErr = _('Could not update unit of measure');
			$tmpDbg = _('The sql that failed was') . ':';
			foreach ($sql as $stmt ) {
				$result = DB_query($stmt,$db, $tmpErr,$tmpDbg,true);
				if(!$result) {
					$InputError = 1;
					break;
				}
			}
			if ($InputError!=1){
				$result = DB_Txn_Commit($db);
			} else {
				$result = DB_Txn_Rollback($db);
			}
		} else {
			$result = DB_query($sql,$db);
		}
		prnMsg($msg,'success');
	}
	unset ($SelectedMeasureID);
	unset ($_POST['SelectedMeasureID']);
	unset ($_POST['MeasureName']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'stockmaster'
	// Get the original name of the unit of measure the ID is just a secure way to find the unit of measure
	$sql = "SELECT unitname FROM unitsofmeasure
		WHERE unitid = " . $SelectedMeasureID;
	$result = DB_query($sql,$db);
	if ( DB_num_rows($result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( _('Cannot delete this unit of measure because it no longer exist'),'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$OldMeasureName = $myrow[0];
		$sql= "SELECT COUNT(*) FROM stockmaster WHERE units ".LIKE." '" . $OldMeasureName . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('Cannot delete this unit of measure because inventory items have been created using this unit of measure'),'warn');
			echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('inventory items that refer to this unit of measure') . '</font>';
		} else {
			$sql= "SELECT COUNT(*) FROM contracts WHERE units ".LIKE." '" . $OldMeasureName . "'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				prnMsg( _('Cannot delete this unit of measure because contracts have been created using this unit of measure'),'warn');
				echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('contracts that refer to this unit of measure') . '</font>';
			} else {
				$sql="DELETE FROM unitsofmeasure WHERE unitname ".LIKE."'" . $OldMeasureName . "'";
				$result = DB_query($sql,$db);
				prnMsg( $OldMeasureName . ' ' . _('unit of measure has been deleted') . '!','success');
			}
		}

	} //end if account group used in GL accounts
	unset ($SelectedMeasureID);
	unset ($_GET['SelectedMeasureID']);
	unset($_GET['delete']);
	unset ($_POST['SelectedMeasureID']);
	unset ($_POST['MeasureID']);
	unset ($_POST['MeasureName']);
}

 if (!isset($SelectedMeasureID)) {

/* An unit of measure could be posted when one has been edited and is being updated
  or GOT when selected for modification
  SelectedMeasureID will exist because it was sent with the page in a GET .
  If its the first time the page has been displayed with no parameters
  then none of the above are true and the list of account groups will be displayed with
  links to delete or edit each. These will call the same page again and allow update/input
  or deletion of the records*/

	$sql = "SELECT unitid,
			unitname
			FROM unitsofmeasure
			ORDER BY unitid";

	$ErrMsg = _('Could not get unit of measures because');
	$result = DB_query($sql,$db,$ErrMsg);

	echo "<table>
		<tr>
		<th>" . _('Units of Measure') . "</th>
		</tr>";

	$k=0; //row colour counter
	while ($myrow = DB_fetch_row($result)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		echo '<td>' . $myrow[1] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedMeasureID=' . $myrow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedMeasureID=' . $myrow[0] . '&delete=1">' . _('Delete') .'</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><p>';
} //end of ifs and buts!


if (isset($SelectedMeasureID)) {
	echo '<div class="centre"><a href=' . $_SERVER['PHP_SELF'] . '?' . SID .'>' . _('Review Units of Measure') . '</a></div>';
}

echo '<p>';

if (! isset($_GET['delete'])) {

	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if (isset($SelectedMeasureID)) {
		//editing an existing section

		$sql = "SELECT unitid,
				unitname
				FROM unitsofmeasure
				WHERE unitid=" . $SelectedMeasureID;

		$result = DB_query($sql, $db);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('Could not retrieve the requested unit of measure, please try again.'),'warn');
			unset($SelectedMeasureID);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['MeasureID'] = $myrow['unitid'];
			$_POST['MeasureName']  = $myrow['unitname'];

			echo "<input type=hidden name='SelectedMeasureID' VALUE='" . $_POST['MeasureID'] . "'>";
			echo "<table>";
		}

	}  else {
		$_POST['MeasureName']='';
		echo "<table>";
	}
	echo "<tr>
		<td>" . _('Unit of Measure') . ':' . "</td>
		<td><input type='Text' name='MeasureName' size=30 maxlength=30 value='" . $_POST['MeasureName'] . "'></td>
		</tr>";
	echo '</table>';

	echo '<div class="centre"><input type=Submit name=submit value=' . _('Enter Information') . '></div>';

	echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>