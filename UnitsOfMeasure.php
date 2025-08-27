<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Units Of Measure');
$ViewTopic = 'Setup';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' .
		__('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if ( isset($_GET['SelectedMeasureID']) )
	$SelectedMeasureID = $_GET['SelectedMeasureID'];
elseif (isset($_POST['SelectedMeasureID']))
	$SelectedMeasureID = $_POST['SelectedMeasureID'];

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (ContainsIllegalCharacters($_POST['MeasureName'])) {
		$InputError = 1;
		prnMsg( __('The unit of measure cannot contain any of the illegal characters') . ' ' . '" \' - &amp; or a space' ,'error');
	}
	if (trim($_POST['MeasureName']) == '') {
		$InputError = 1;
		prnMsg( __('The unit of measure may not be empty'), 'error');
	}

	if (isset($_POST['SelectedMeasureID']) AND $_POST['SelectedMeasureID']!='' AND $InputError !=1) {


		/*SelectedMeasureID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$SQL = "SELECT count(*) FROM unitsofmeasure
				WHERE unitid <> '" . $SelectedMeasureID ."'
				AND unitname ".LIKE." '" . $_POST['MeasureName'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ( $MyRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( __('The unit of measure can not be renamed because another with the same name already exist.'),'error');
		} else {
			// Get the old name and check that the record still exist neet to be very carefull here
			// idealy this is one of those sets that should be in a stored procedure simce even the checks are
			// relavant
			$SQL = "SELECT unitname FROM unitsofmeasure
				WHERE unitid = '" . $SelectedMeasureID . "'";
			$Result = DB_query($SQL);
			if ( DB_num_rows($Result) != 0 ) {
				// This is probably the safest way there is
				$MyRow = DB_fetch_row($Result);
				$OldMeasureName = $MyRow[0];
				$SQL = array();
				$SQL[] = "UPDATE unitsofmeasure
					SET unitname='" . $_POST['MeasureName'] . "'
					WHERE unitname ".LIKE." '".$OldMeasureName."'";
				$SQL[] = "UPDATE stockmaster
					SET units='" . $_POST['MeasureName'] . "'
					WHERE units ".LIKE." '" . $OldMeasureName . "'";
			} else {
				$InputError = 1;
				prnMsg( __('The unit of measure no longer exist.'),'error');
			}
		}
		$Msg = __('Unit of measure changed');
	} elseif ($InputError !=1) {
		/*SelectedMeasureID is null cos no item selected on first time round so must be adding a record*/
		$SQL = "SELECT count(*) FROM unitsofmeasure
				WHERE unitname " .LIKE. " '".$_POST['MeasureName'] ."'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ( $MyRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( __('The unit of measure can not be created because another with the same name already exists.'),'error');
		} else {
			$SQL = "INSERT INTO unitsofmeasure (unitname )
					VALUES ('" . $_POST['MeasureName'] ."')";
		}
		$Msg = __('New unit of measure added');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		if (is_array($SQL)) {
			DB_Txn_Begin();
			$TmpErr = __('Could not update unit of measure');
			$tmpDbg = __('The sql that failed was') . ':';
			foreach ($SQL as $stmt ) {
				$Result = DB_query($stmt, $TmpErr,$tmpDbg,true);
				if(!$Result) {
					$InputError = 1;
					break;
				}
			}
			if ($InputError!=1){
				DB_Txn_Commit();
			} else {
				DB_Txn_Rollback();
			}
		} else {
			$Result = DB_query($SQL);
		}
		prnMsg($Msg,'success');
	}
	unset ($SelectedMeasureID);
	unset ($_POST['SelectedMeasureID']);
	unset ($_POST['MeasureName']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'stockmaster'
	// Get the original name of the unit of measure the ID is just a secure way to find the unit of measure
	$SQL = "SELECT unitname FROM unitsofmeasure
		WHERE unitid = '" . $SelectedMeasureID . "'";
	$Result = DB_query($SQL);
	if ( DB_num_rows($Result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( __('Cannot delete this unit of measure because it no longer exist'),'warn');
	} else {
		$MyRow = DB_fetch_row($Result);
		$OldMeasureName = $MyRow[0];
		$SQL= "SELECT COUNT(*) FROM stockmaster WHERE units ".LIKE." '" . $OldMeasureName . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0]>0) {
			prnMsg( __('Cannot delete this unit of measure because inventory items have been created using this unit of measure'),'warn');
			echo '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('inventory items that refer to this unit of measure') . '</font>';
		} else {
			$SQL="DELETE FROM unitsofmeasure WHERE unitname ".LIKE."'" . $OldMeasureName . "'";
			$Result = DB_query($SQL);
			prnMsg( $OldMeasureName . ' ' . __('unit of measure has been deleted') . '!','success');
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

	$SQL = "SELECT unitid,
			unitname
			FROM unitsofmeasure
			ORDER BY unitid";

	$ErrMsg = __('Could not get unit of measures because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">' . __('Units of Measure') . '</th>
			</tr>
		</thead>
		<tbody>';

	while ($MyRow = DB_fetch_row($Result)) {

		echo '<tr class="striped_row">
				<td>' . $MyRow[1] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedMeasureID=' . $MyRow[0] . '">' . __('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedMeasureID=' . $MyRow[0] . '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this unit of measure?') . '\');">' . __('Delete')  . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</tbody></table>';
} //end of ifs and buts!


if (isset($SelectedMeasureID)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Review Units of Measure') . '</a>
		</div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedMeasureID)) {
		//editing an existing section

		$SQL = "SELECT unitid,
				unitname
				FROM unitsofmeasure
				WHERE unitid='" . $SelectedMeasureID . "'";

		$Result = DB_query($SQL);
		if ( DB_num_rows($Result) == 0 ) {
			prnMsg( __('Could not retrieve the requested unit of measure, please try again.'),'warn');
			unset($SelectedMeasureID);
		} else {
			$MyRow = DB_fetch_array($Result);

			$_POST['MeasureID'] = $MyRow['unitid'];
			$_POST['MeasureName']  = $MyRow['unitname'];

			echo '<input type="hidden" name="SelectedMeasureID" value="' . $_POST['MeasureID'] . '" />';
			echo '<fieldset>
					<legend>', __('Edit Unit of Measure'), '</legend>';
		}

	}  else {
		$_POST['MeasureName']='';
		echo '<fieldset>
				<legend>', __('Create Unit of Measure'), '</legend>';
	}
	echo '<field>
		<td>' . __('Unit of Measure') . ':' . '</td>
		<td><input required="required" pattern="(?!^ *$)[^+<>-]{1,}" type="text" name="MeasureName" title="'.__('Cannot be blank or contains illegal characters').'" placeholder="'.__('More than one character').'" size="30" maxlength="30" value="' . $_POST['MeasureName'] . '" /></td>
		</field>';
	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Submit" value="' . __('Enter Information') . '" />
		</div>';

	echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
