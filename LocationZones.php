<?php
/* $Id: locationzones.php 6998 2014-11-22 02:28:56Z daintree $*/

include('includes/session.php');
$Title = _('Location Zones Maintenance');
include('includes/header.php');

if (isset($_POST['SelectedCode'])){
	$SelectedCode = mb_strtoupper($_POST['SelectedCode']);
} elseif (isset($_GET['SelectedCode'])){
	$SelectedCode = mb_strtoupper($_GET['SelectedCode']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	if (mb_strlen($_POST['code']) > 10) {
		$InputError = 1;
		prnMsg(_('The zone code must be 10 characters or less long'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	} elseif ($_POST['code']=='') {
		$InputError = 1;
		prnMsg( _('The zone code cannot be an empty string'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	} elseif( trim($_POST['description'])==''){
		$InputError = 1;
		prnMsg (_('The zone description cannot be empty'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	} elseif (mb_strlen($_POST['description']) >50) {
		$InputError = 1;
		echo prnMsg(_('The zone description must be fifty characters or less long'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	}

	if (isset($SelectedCode) AND $InputError !=1) {

		$sql = "UPDATE locationzones
			SET description = '" . $_POST['description'] . "',
				smarttransferonweekday0 = '" . $_POST['smarttransferonweekday0'] . "',
				smarttransferonweekday1 = '" . $_POST['smarttransferonweekday1'] . "',
				smarttransferonweekday2 = '" . $_POST['smarttransferonweekday2'] . "',
				smarttransferonweekday3 = '" . $_POST['smarttransferonweekday3'] . "',
				smarttransferonweekday4 = '" . $_POST['smarttransferonweekday4'] . "',
				smarttransferonweekday5 = '" . $_POST['smarttransferonweekday5'] . "',
				smarttransferonweekday6 = '" . $_POST['smarttransferonweekday6'] . "'
			WHERE code = '".$SelectedCode."'";

		$msg = _('The location zone') . ' ' . $SelectedCode . ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM locationzones
			     WHERE code = '" . $_POST['code'] . "'";

		$CheckResult = DB_query($checkSql);
		$CheckRow = DB_fetch_row($CheckResult);

		if ( $CheckRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The location zone ') . $_POST['code'] . _(' already exist.'),'error');
		} else {

			// Add new record on submit

			$sql = "INSERT INTO locationzones (code,
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

			$msg = _('Location zone') . ' ' . $_POST['description'] .  ' ' . _('has been created');
			$checkSql = "SELECT count(code)
						FROM locationzones";
			$result = DB_query($checkSql);
			$row = DB_fetch_row($result);

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql);

		prnMsg($msg,'success');

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

	$sql= "SELECT COUNT(*)
	       FROM locations
	       WHERE locations.zone='".$SelectedCode."'";

	$ErrMsg = _('The number of locations using this zone could not be retrieved');
	$result = DB_query($sql,$ErrMsg);

	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this zone because locations have been created using this zone') . '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('locations using this zone code'),'error');

	} else {

		$sql="DELETE FROM locationzones WHERE code='" . $SelectedCode . "'";
		$ErrMsg = _('The Location Zone record could not be deleted because');
		$result = DB_query($sql,$ErrMsg);
		prnMsg(_('Location zone') . ' ' . $SelectedCode  . ' ' . _('has been deleted') ,'success');

		unset ($SelectedCode);
		unset($_GET['delete']);

	} //end if sales type used in debtor transactions or in customers set up
}


if(isset($_POST['Cancel'])){
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

	$sql = "SELECT code,
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
	$result = DB_query($sql);

	echo '<table class="selection">
		<tr>
				<th>' . '' . '</th>
				<th>' . '' . '</th>
				<th colspan="7">' . 'Allow KL Smart Daily Transfers on' . '</th>
		</tr>
		<tr>
				<th class="ascending">' . _('Zone Code') . '</th>
				<th class="ascending">' . _('Zone Name') . '</th>
				<th class="ascending">' . _('Sunday') . '</th>
				<th class="ascending">' . _('Monday') . '</th>
				<th class="ascending">' . _('Tuesday') . '</th>
				<th class="ascending">' . _('Wednesday') . '</th>
				<th class="ascending">' . _('Thusrday') . '</th>
				<th class="ascending">' . _('Friday') . '</th>
				<th class="ascending">' . _('Saturday') . '</th>
		</tr>';

$k=0; //row colour counter

while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	if($myrow['smarttransferonweekday0'] == 1) {
		$TransferOn0 = 'Yes';
	} else {
		$TransferOn0 = '';
	}
	if($myrow['smarttransferonweekday1'] == 1) {
		$TransferOn1 = 'Yes';
	} else {
		$TransferOn1 = '';
	}
	if($myrow['smarttransferonweekday2'] == 1) {
		$TransferOn2 = 'Yes';
	} else {
		$TransferOn2 = '';
	}
	if($myrow['smarttransferonweekday3'] == 1) {
		$TransferOn3 = 'Yes';
	} else {
		$TransferOn3 = '';
	}
	if($myrow['smarttransferonweekday4'] == 1) {
		$TransferOn4 = 'Yes';
	} else {
		$TransferOn4 = '';
	}
	if($myrow['smarttransferonweekday5'] == 1) {
		$TransferOn5 = 'Yes';
	} else {
		$TransferOn5 = '';
	}
	if($myrow['smarttransferonweekday6'] == 1) {
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
		<td><a href="%sSelectedCode=%s">' . _('Edit') . '</a></td>
		<td><a href="%sSelectedCode=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this zone?') . '\');">' . _('Delete') . '</a></td>
		</tr>',
		$myrow['code'],
		$myrow['description'],
		$TransferOn0,
		$TransferOn1,
		$TransferOn2,
		$TransferOn3,
		$TransferOn4,
		$TransferOn5,
		$TransferOn6,
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['code'],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['code']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedCode)) {

	echo '<br />
			<div class="centre">
				<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">' . _('Show All Location Zones Defined') . '</a>
			</div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" >
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />';


	// The user wish to EDIT an existing type
	if ( isset($SelectedCode) AND $SelectedCode!='' ) {

		$sql = "SELECT code,
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

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['code'] = $myrow['code'];
		$_POST['description']  = $myrow['description'];
		$_POST['smarttransferonweekday0']  = $myrow['smarttransferonweekday0'];
		$_POST['smarttransferonweekday1']  = $myrow['smarttransferonweekday1'];
		$_POST['smarttransferonweekday2']  = $myrow['smarttransferonweekday2'];
		$_POST['smarttransferonweekday3']  = $myrow['smarttransferonweekday3'];
		$_POST['smarttransferonweekday4']  = $myrow['smarttransferonweekday4'];
		$_POST['smarttransferonweekday5']  = $myrow['smarttransferonweekday5'];
		$_POST['smarttransferonweekday6']  = $myrow['smarttransferonweekday6'];

		echo '<input type="hidden" name="SelectedCode" value="' . $SelectedCode . '" />
			<input type="hidden" name="code" value="' . $_POST['code'] . '" />
			<table class="selection">
			<tr>
				<th colspan="4"><b>' . _('Location Zones Setup') . '</b></th>
			</tr>
			<tr>
				<td>' . _('Type Code') . ':</td>
				<td>' . $_POST['code'] . '</td>
			</tr>';

	} else 	{

		// This is a new type so the user may volunteer a type code

		echo '<table class="selection">
				<tr>
					<th colspan="4"><b>' . _('Location Zone Setup') . '</b></th>
				</tr>
				<tr>
					<td>' . _('Type Code') . ':</td>
					<td><input type="text" ' . (in_array('LocationZone',$Errors) ? 'class="inputerror"' : '' ) .' size="11" maxlength="10" name="code" /></td>
				</tr>';
	}

	if (!isset($_POST['description'])) {
		$_POST['description']='';
	}
	if(!isset($_POST['smarttransferonweekday0'])) {
		$_POST['smarttransferonweekday0'] = 0;
	}
	if(!isset($_POST['smarttransferonweekday1'])) {
		$_POST['smarttransferonweekday1'] = 1;
	}
	if(!isset($_POST['smarttransferonweekday2'])) {
		$_POST['smarttransferonweekday2'] = 1;
	}
	if(!isset($_POST['smarttransferonweekday3'])) {
		$_POST['smarttransferonweekday3'] = 1;
	}
	if(!isset($_POST['smarttransferonweekday4'])) {
		$_POST['smarttransferonweekday4'] = 1;
	}
	if(!isset($_POST['smarttransferonweekday5'])) {
		$_POST['smarttransferonweekday5'] = 1;
	}
	if(!isset($_POST['smarttransferonweekday6'])) {
		$_POST['smarttransferonweekday6'] = 1;
	}

	echo '<tr>
			<td>' . _('Location Zone Name') . ':</td>
			<td><input type="text" name="description" value="' . $_POST['description'] . '" /></td>
		</tr>';


	echo '<tr>
			<td>' . _('Allow KL Smart Transfers on Sunday?') . ':</td>
			<td><select name="smarttransferonweekday0">';
	if($_POST['smarttransferonweekday0']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['smarttransferonweekday0']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr>
			<td>' . _('Allow KL Smart Transfers on Monday?') . ':</td>
			<td><select name="smarttransferonweekday1">';
	if($_POST['smarttransferonweekday1']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['smarttransferonweekday1']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr>
			<td>' . _('Allow KL Smart Transfers on Tuesday?') . ':</td>
			<td><select name="smarttransferonweekday2">';
	if($_POST['smarttransferonweekday2']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['smarttransferonweekday2']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr>
			<td>' . _('Allow KL Smart Transfers on Wednesday?') . ':</td>
			<td><select name="smarttransferonweekday3">';
	if($_POST['smarttransferonweekday3']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['smarttransferonweekday3']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr>
			<td>' . _('Allow KL Smart Transfers on Thursday?') . ':</td>
			<td><select name="smarttransferonweekday4">';
	if($_POST['smarttransferonweekday4']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['smarttransferonweekday4']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Allow KL Smart Transfers on Friday?') . ':</td>
			<td><select name="smarttransferonweekday5">';
	if($_POST['smarttransferonweekday5']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['smarttransferonweekday5']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr>
			<td>' . _('Allow KL Smart Transfers on Saturday?') . ':</td>
			<td><select name="smarttransferonweekday6">';
	if($_POST['smarttransferonweekday6']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['smarttransferonweekday6']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';
	
	echo '</table>'; // close main table
	echo '<br /><div class="centre"><input type="submit" name="submit" value="' . _('Accept') . '" /><input type="submit" name="Cancel" value="' . _('Cancel') . '" /></div>
			</div>
          </form>';

} // end if user wish to delete

include('includes/footer.php');
?>