<?php

require(__DIR__ . '/includes/session.php');
$Title = __('Location Types Maintenance');
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

	if (mb_strlen($_POST['code']) > 6) {
		$InputError = 1;
		prnMsg(__('The type code must be six characters or less long'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	} elseif ($_POST['code']=='') {
		$InputError = 1;
		prnMsg( __('The type code cannot be an empty string'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	} elseif( trim($_POST['description'])==''){
		$InputError = 1;
		prnMsg(__('The type description cannot be empty'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	} elseif (mb_strlen($_POST['description']) >50) {
		$InputError = 1;
		echo prnMsg(__('The type description must be fifty characters or less long'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	}

	if (isset($SelectedCode) AND $InputError !=1) {

		$SQL = "UPDATE locationtypes
			SET description = '" . $_POST['description'] . "'
			WHERE code = '".$SelectedCode."'";

		$Msg = __('The Location Type') . ' ' . $SelectedCode . ' ' .  __('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$CheckSQL = "SELECT count(*)
			     FROM locationtypes
			     WHERE code = '" . $_POST['code'] . "'";

		$CheckResult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($CheckResult);

		if ( $CheckRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( __('The Location Type ') . $_POST['code'] . __(' already exist.'),'error');
		} else {

			// Add new record on submit

			$SQL = "INSERT INTO locationtypes (code,
											description)
							VALUES ('" . str_replace(' ', '', $_POST['code']) . "',
									'" . $_POST['description'] . "')";

			$Msg = __('Location Type') . ' ' . $_POST['description'] .  ' ' . __('has been created');
			$CheckSQL = "SELECT count(code)
						FROM locationtypes";
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
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'Locations'
	// Prevent delete if Location Type exist in customer transactions

	$SQL= "SELECT COUNT(*)
	       FROM locations
	       WHERE locations.zone='".$SelectedCode."'";

	$ErrMsg = __('The number of locations using this type could not be retrieved');
	$Result = DB_query($SQL,$ErrMsg);

	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg(__('Cannot delete this type because locations have been created using this zone') . '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('locations using this type code'),'error');

	} else {

		$SQL="DELETE FROM locationtypes WHERE code='" . $SelectedCode . "'";
		$ErrMsg = __('The Location Type record could not be deleted because');
		$Result = DB_query($SQL,$ErrMsg);
		prnMsg(__('Location Type') . ' ' . $SelectedCode  . ' ' . __('has been deleted') ,'success');

		unset ($SelectedCode);
		unset($_GET['delete']);

	} //end if sales type used in debtor transactions or in customers set up
}


if(isset($_POST['Cancel'])){
	unset($SelectedCode);
	unset($_POST['code']);
	unset($_POST['description']);
}

if (!isset($SelectedCode)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCode will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT code,description FROM locationtypes ORDER BY code";
	$Result = DB_query($SQL);

	echo '<table class="selection">
		<tr>
				<th class="ascending">' . __('Type Code') . '</th>
				<th class="ascending">' . __('Type Name') . '</th>
		</tr>';

$k=0; //row colour counter

while ($MyRow = DB_fetch_row($Result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	printf('<td>%s</td>
		<td>%s</td>
		<td><a href="%sSelectedCode=%s">' . __('Edit') . '</a></td>
		<td><a href="%sSelectedCode=%s&amp;delete=yes" onclick="return confirm(\'' . __('Are you sure you wish to delete this type?') . '\');">' . __('Delete') . '</a></td>
		</tr>',
		$MyRow[0],
		$MyRow[1],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $MyRow[0],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $MyRow[0]);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedCode)) {

	echo '<br />
			<div class="centre">
				<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">' . __('Show All Location Types Defined') . '</a>
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
			       description
		        FROM locationtypes
		        WHERE code='" . $SelectedCode . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['code'] = $MyRow['code'];
		$_POST['description']  = $MyRow['description'];

		echo '<input type="hidden" name="SelectedCode" value="' . $SelectedCode . '" />
			<input type="hidden" name="code" value="' . $_POST['code'] . '" />
			<table class="selection">
			<tr>
				<th colspan="4"><b>' . __('Location Types Setup') . '</b></th>
			</tr>
			<tr>
				<td>' . __('Type Code') . ':</td>
				<td>' . $_POST['code'] . '</td>
			</tr>';

	} else 	{

		// This is a new type so the user may volunteer a type code

		echo '<table class="selection">
				<tr>
					<th colspan="4"><b>' . __('Location Type Setup') . '</b></th>
				</tr>
				<tr>
					<td>' . __('Type Code') . ':</td>
					<td><input type="text" ' . (in_array('LocationZone',$Errors) ? 'class="inputerror"' : '' ) .' size="7" maxlength="6" name="code" /></td>
				</tr>';
	}

	if (!isset($_POST['description'])) {
		$_POST['description']='';
	}
	echo '<tr>
			<td>' . __('Location Type Name') . ':</td>
			<td><input type="text" name="description" value="' . $_POST['description'] . '" /></td>
		</tr>
		</table>'; // close main table

	echo '<br /><div class="centre"><input type="submit" name="submit" value="' . __('Accept') . '" /><input type="reset" name="Cancel" value="' . __('Cancel') . '" /></div>
			</div>
          </form>';

} // end if user wish to delete

include('includes/footer.php');
