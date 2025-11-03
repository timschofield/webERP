<?php
/* $Id: locationzones.php 6998 2014-11-22 02:28:56Z daintree $*/

require(__DIR__ . '/includes/session.php');
$Title = __('Location Zones Maintenance');
include('includes/header.php');

if (isset($_POST['SelectedCode'])){
	$SelectedCode = mb_strtoupper($_POST['SelectedCode']);
} elseif (isset($_GET['SelectedCode'])){
	$SelectedCode = mb_strtoupper($_GET['SelectedCode']);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	if (mb_strlen($_POST['code']) > 10) {
		$InputError = 1;
		prnMsg(__('The zone code must be 10 characters or less long'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	} elseif ($_POST['code']=='') {
		$InputError = 1;
		prnMsg( __('The zone code cannot be an empty string'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	} elseif ( trim($_POST['description'])==''){
		$InputError = 1;
		prnMsg(__('The zone description cannot be empty'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	} elseif (mb_strlen($_POST['description']) >50) {
		$InputError = 1;
		echo prnMsg(__('The zone description must be fifty characters or less long'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	}

	if (isset($SelectedCode) AND $InputError !=1) {

		$SQL = "UPDATE locationzones
			SET description = '" . $_POST['description'] . "',
				smarttransferonweekday0 = '" . $_POST['smarttransferonweekday0'] . "',
				smarttransferonweekday1 = '" . $_POST['smarttransferonweekday1'] . "',
				smarttransferonweekday2 = '" . $_POST['smarttransferonweekday2'] . "',
				smarttransferonweekday3 = '" . $_POST['smarttransferonweekday3'] . "',
				smarttransferonweekday4 = '" . $_POST['smarttransferonweekday4'] . "',
				smarttransferonweekday5 = '" . $_POST['smarttransferonweekday5'] . "',
				smarttransferonweekday6 = '" . $_POST['smarttransferonweekday6'] . "'
			WHERE code = '".$SelectedCode."'";

		$Msg = __('The location zone') . ' ' . $SelectedCode . ' ' .  __('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$CheckSQL = "SELECT count(*)
			     FROM locationzones
			     WHERE code = '" . $_POST['code'] . "'";

		$CheckResult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($CheckResult);

		if ( $CheckRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( __('The location zone ') . $_POST['code'] . __(' already exist.'),'error');
		} else {

			// Add new record on submit

			$SQL = "INSERT INTO locationzones (code,
											description,
											smarttransferonweekday0,
											smarttransferonweekday1,
											smarttransferonweekday2,
											smarttransferonweekday3,
											smarttransferonweekday4,
											smarttransferonweekday5,
											smarttransferonweekday6
											)
							VALUES ('" . str_replace(' ', '', $_POST['code']) . "',
									'" . $_POST['description'] . "', 
									'" . $_POST['smarttransferonweekday0'] . "', 
									'" . $_POST['smarttransferonweekday1'] . "', 
									'" . $_POST['smarttransferonweekday2'] . "', 
									'" . $_POST['smarttransferonweekday3'] . "', 
									'" . $_POST['smarttransferonweekday4'] . "', 
									'" . $_POST['smarttransferonweekday5'] . "', 
									'" . $_POST['smarttransferonweekday6'] . "')";

			$Msg = __('Location zone') . ' ' . $_POST['description'] .  ' ' . __('has been created');
			$CheckSQL = "SELECT count(code)
						FROM locationzones";
			$Result = DB_query($CheckSQL);
			$Row = DB_fetch_row($Result);

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);

		prnMsg($Msg,'success');

		unset($SelectedCode);
		unset($_POST['code']);
		unset($_POST['description']);
		unset($_POST['smarttransferonweekday0']);
		unset($_POST['smarttransferonweekday1']);
		unset($_POST['smarttransferonweekday2']);
		unset($_POST['smarttransferonweekday3']);
		unset($_POST['smarttransferonweekday4']);
		unset($_POST['smarttransferonweekday5']);
		unset($_POST['smarttransferonweekday6']);
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'Locations'
	// Prevent delete if location zone exist in customer transactions

	$SQL= "SELECT COUNT(*)
	       FROM locations
	       WHERE locations.zone='".$SelectedCode."'";

	$ErrMsg = __('The number of locations using this zone could not be retrieved');
	$Result = DB_query($SQL,$ErrMsg);

	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg(__('Cannot delete this zone because locations have been created using this zone') . '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('locations using this zone code'),'error');

	} else {

		$SQL="DELETE FROM locationzones WHERE code='" . $SelectedCode . "'";
		$ErrMsg = __('The Location Zone record could not be deleted because');
		$Result = DB_query($SQL,$ErrMsg);
		prnMsg(__('Location zone') . ' ' . $SelectedCode  . ' ' . __('has been deleted') ,'success');

		unset ($SelectedCode);
		unset($_GET['delete']);

	} //end if sales type used in debtor transactions or in customers set up
}


if (isset($_POST['Cancel'])){
	unset($SelectedCode);
	unset($_POST['code']);
	unset($_POST['description']);
	unset($_POST['smarttransferonweekday0']);
	unset($_POST['smarttransferonweekday1']);
	unset($_POST['smarttransferonweekday2']);
	unset($_POST['smarttransferonweekday3']);
	unset($_POST['smarttransferonweekday4']);
	unset($_POST['smarttransferonweekday5']);
	unset($_POST['smarttransferonweekday6']);
}

if (!isset($SelectedCode)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCode will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT code,
				description,
				smarttransferonweekday0,
				smarttransferonweekday1,
				smarttransferonweekday2,
				smarttransferonweekday3,
				smarttransferonweekday4,
				smarttransferonweekday5,
				smarttransferonweekday6
			FROM locationzones 
			ORDER BY code";
	$Result = DB_query($SQL);

	echo '<table class="selection">
		<tr>
				<th>' . '' . '</th>
				<th>' . '' . '</th>
				<th colspan="7">' . 'Allow KL Smart Daily Transfers on' . '</th>
		</tr>
		<tr>
				<th class="ascending">' . __('Zone Code') . '</th>
				<th class="ascending">' . __('Zone Name') . '</th>
				<th class="ascending">' . __('Sunday') . '</th>
				<th class="ascending">' . __('Monday') . '</th>
				<th class="ascending">' . __('Tuesday') . '</th>
				<th class="ascending">' . __('Wednesday') . '</th>
				<th class="ascending">' . __('Thusrday') . '</th>
				<th class="ascending">' . __('Friday') . '</th>
				<th class="ascending">' . __('Saturday') . '</th>
		</tr>';

$k=0; //row colour counter

while ($MyRow = DB_fetch_array($Result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	if ($MyRow['smarttransferonweekday0'] == 1) {
		$TransferOn0 = 'Yes';
	} else {
		$TransferOn0 = '';
	}
	if ($MyRow['smarttransferonweekday1'] == 1) {
		$TransferOn1 = 'Yes';
	} else {
		$TransferOn1 = '';
	}
	if ($MyRow['smarttransferonweekday2'] == 1) {
		$TransferOn2 = 'Yes';
	} else {
		$TransferOn2 = '';
	}
	if ($MyRow['smarttransferonweekday3'] == 1) {
		$TransferOn3 = 'Yes';
	} else {
		$TransferOn3 = '';
	}
	if ($MyRow['smarttransferonweekday4'] == 1) {
		$TransferOn4 = 'Yes';
	} else {
		$TransferOn4 = '';
	}
	if ($MyRow['smarttransferonweekday5'] == 1) {
		$TransferOn5 = 'Yes';
	} else {
		$TransferOn5 = '';
	}
	if ($MyRow['smarttransferonweekday6'] == 1) {
		$TransferOn6 = 'Yes';
	} else {
		$TransferOn6 = '';
	}

	printf('<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href="%sSelectedCode=%s">' . __('Edit') . '</a></td>
		<td><a href="%sSelectedCode=%s&amp;delete=yes" onclick="return confirm(\'' . __('Are you sure you wish to delete this zone?') . '\');">' . __('Delete') . '</a></td>
		</tr>',
		$MyRow['code'],
		$MyRow['description'],
		$TransferOn0,
		$TransferOn1,
		$TransferOn2,
		$TransferOn3,
		$TransferOn4,
		$TransferOn5,
		$TransferOn6,
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $MyRow['code'],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $MyRow['code']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedCode)) {

	echo '<br />
			<div class="centre">
				<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">' . __('Show All Location Zones Defined') . '</a>
			</div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" >
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />';


	// The user wish to EDIT an existing type
	if ( isset($SelectedCode) AND $SelectedCode!='' ) {

		$SQL = "SELECT code,
					description,
					smarttransferonweekday0,
					smarttransferonweekday1,
					smarttransferonweekday2,
					smarttransferonweekday3,
					smarttransferonweekday4,
					smarttransferonweekday5,
					smarttransferonweekday6
		        FROM locationzones
		        WHERE code='" . $SelectedCode . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['code'] = $MyRow['code'];
		$_POST['description']  = $MyRow['description'];
		$_POST['smarttransferonweekday0']  = $MyRow['smarttransferonweekday0'];
		$_POST['smarttransferonweekday1']  = $MyRow['smarttransferonweekday1'];
		$_POST['smarttransferonweekday2']  = $MyRow['smarttransferonweekday2'];
		$_POST['smarttransferonweekday3']  = $MyRow['smarttransferonweekday3'];
		$_POST['smarttransferonweekday4']  = $MyRow['smarttransferonweekday4'];
		$_POST['smarttransferonweekday5']  = $MyRow['smarttransferonweekday5'];
		$_POST['smarttransferonweekday6']  = $MyRow['smarttransferonweekday6'];

		echo '<input type="hidden" name="SelectedCode" value="' . $SelectedCode . '" />
			<input type="hidden" name="code" value="' . $_POST['code'] . '" />
			<table class="selection">
			<tr>
				<th colspan="4"><b>' . __('Location Zones Setup') . '</b></th>
			</tr>
			<tr>
				<td>' . __('Type Code') . ':</td>
				<td>' . $_POST['code'] . '</td>
			</tr>';

	} else 	{

		// This is a new type so the user may volunteer a type code

		echo '<table class="selection">
				<tr>
					<th colspan="4"><b>' . __('Location Zone Setup') . '</b></th>
				</tr>
				<tr>
					<td>' . __('Type Code') . ':</td>
					<td><input type="text" ' . (in_array('LocationZone',$Errors) ? 'class="inputerror"' : '' ) .' size="11" maxlength="10" name="code" /></td>
				</tr>';
	}

	if (!isset($_POST['description'])) {
		$_POST['description']='';
	}
	if (!isset($_POST['smarttransferonweekday0'])) {
		$_POST['smarttransferonweekday0'] = 0;
	}
	if (!isset($_POST['smarttransferonweekday1'])) {
		$_POST['smarttransferonweekday1'] = 1;
	}
	if (!isset($_POST['smarttransferonweekday2'])) {
		$_POST['smarttransferonweekday2'] = 1;
	}
	if (!isset($_POST['smarttransferonweekday3'])) {
		$_POST['smarttransferonweekday3'] = 1;
	}
	if (!isset($_POST['smarttransferonweekday4'])) {
		$_POST['smarttransferonweekday4'] = 1;
	}
	if (!isset($_POST['smarttransferonweekday5'])) {
		$_POST['smarttransferonweekday5'] = 1;
	}
	if (!isset($_POST['smarttransferonweekday6'])) {
		$_POST['smarttransferonweekday6'] = 1;
	}

	echo '<tr>
			<td>' . __('Location Zone Name') . ':</td>
			<td><input type="text" name="description" value="' . $_POST['description'] . '" /></td>
		</tr>';


	echo '<tr>
			<td>' . __('Allow KL Smart Transfers on Sunday?') . ':</td>
			<td><select name="smarttransferonweekday0">';
	if ($_POST['smarttransferonweekday0']==1) {
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if ($_POST['smarttransferonweekday0']==0) {
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr>
			<td>' . __('Allow KL Smart Transfers on Monday?') . ':</td>
			<td><select name="smarttransferonweekday1">';
	if ($_POST['smarttransferonweekday1']==1) {
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if ($_POST['smarttransferonweekday1']==0) {
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr>
			<td>' . __('Allow KL Smart Transfers on Tuesday?') . ':</td>
			<td><select name="smarttransferonweekday2">';
	if ($_POST['smarttransferonweekday2']==1) {
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if ($_POST['smarttransferonweekday2']==0) {
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr>
			<td>' . __('Allow KL Smart Transfers on Wednesday?') . ':</td>
			<td><select name="smarttransferonweekday3">';
	if ($_POST['smarttransferonweekday3']==1) {
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if ($_POST['smarttransferonweekday3']==0) {
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr>
			<td>' . __('Allow KL Smart Transfers on Thursday?') . ':</td>
			<td><select name="smarttransferonweekday4">';
	if ($_POST['smarttransferonweekday4']==1) {
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if ($_POST['smarttransferonweekday4']==0) {
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . __('Allow KL Smart Transfers on Friday?') . ':</td>
			<td><select name="smarttransferonweekday5">';
	if ($_POST['smarttransferonweekday5']==1) {
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if ($_POST['smarttransferonweekday5']==0) {
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr>
			<td>' . __('Allow KL Smart Transfers on Saturday?') . ':</td>
			<td><select name="smarttransferonweekday6">';
	if ($_POST['smarttransferonweekday6']==1) {
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if ($_POST['smarttransferonweekday6']==0) {
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select></td></tr>';
	
	echo '</table>'; // close main table
	echo '<br /><div class="centre"><input type="submit" name="submit" value="' . __('Accept') . '" /><input type="reset" name="Cancel" value="' . __('Cancel') . '" /></div>
			</div>
          </form>';

} // end if user wish to delete

include('includes/footer.php');
