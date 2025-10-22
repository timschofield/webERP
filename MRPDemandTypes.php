<?php

require(__DIR__ . '/includes/session.php');

$Title = __('MRP Demand Types');
$ViewTopic = 'MRP';
$BookMark = '';
include('includes/header.php');

//SelectedDT is the Selected MRPDemandType
if (isset($_POST['SelectedDT'])){
	$SelectedDT = trim(mb_strtoupper($_POST['SelectedDT']));
} elseif (isset($_GET['SelectedDT'])){
	$SelectedDT = trim(mb_strtoupper($_GET['SelectedDT']));
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' .
		__('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (trim(mb_strtoupper($_POST['MRPDemandType']) == 'WO') or
	   trim(mb_strtoupper($_POST['MRPDemandType']) == 'SO')) {
		$InputError = 1;
		prnMsg(__('The Demand Type is reserved for the system'),'error');
	}

	if (mb_strlen($_POST['MRPDemandType']) < 1) {
		$InputError = 1;
		prnMsg(__('The Demand Type code must be at least 1 character long'),'error');
	}
	if (mb_strlen($_POST['Description'])<3) {
		$InputError = 1;
		prnMsg(__('The Demand Type description must be at least 3 characters long'),'error');
	}

	if (isset($SelectedDT) AND $InputError !=1) {

		/*SelectedDT could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$SQL = "UPDATE mrpdemandtypes SET description = '" . $_POST['Description'] . "'
				WHERE mrpdemandtype = '" . $SelectedDT . "'";
		$Msg = __('The demand type record has been updated');
	} elseif ($InputError !=1) {

	//Selected demand type is null cos no item selected on first time round so must be adding a
	//record must be submitting new entries in the new work centre form

		$SQL = "INSERT INTO mrpdemandtypes (mrpdemandtype,
						description)
					VALUES ('" . trim(mb_strtoupper($_POST['MRPDemandType'])) . "',
						'" . $_POST['Description'] . "'
						)";
		$Msg = __('The new demand type has been added to the database');
	}
	//run the SQL from either of the above possibilites

	if ($InputError !=1){
		$Result = DB_query($SQL,__('The update/addition of the demand type failed because'));
		prnMsg($Msg,'success');
		echo '<br />';
		unset ($_POST['Description']);
		unset ($_POST['MRPDemandType']);
		unset ($SelectedDT);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'MRPDemands'

	$SQL= "SELECT COUNT(*) FROM mrpdemands
	         WHERE mrpdemands.mrpdemandtype='" . $SelectedDT . "'
	         GROUP BY mrpdemandtype";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg(__('Cannot delete this demand type because MRP Demand records exist for this type') . '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' .__('MRP Demands referring to this type'),'warn');
    } else {
			$SQL="DELETE FROM mrpdemandtypes WHERE mrpdemandtype='" . $SelectedDT . "'";
			$Result = DB_query($SQL);
			prnMsg(__('The selected demand type record has been deleted'),'succes');
			echo '<br />';
	} // end of MRPDemands test
}

if (!isset($SelectedDT) or isset($_GET['delete'])) {

//It could still be the second time the page has been run and a record has been selected
//for modification SelectedDT will exist because it was sent with the new call. If its
//the first time the page has been displayed with no parameters
//then none of the above are true and the list of demand types will be displayed with
//links to delete or edit each. These will call the same page again and allow update/input
//or deletion of the records

	$SQL = "SELECT mrpdemandtype,
					description
			FROM mrpdemandtypes";

	$Result = DB_query($SQL);

	echo '<table class="selection">
			<tr><th>' . __('Demand Type') . '</th>
				<th>' . __('Description') . '</th>
				<th colspan="2"></th>
			</tr>';

	while ($MyRow = DB_fetch_row($Result)) {

		echo '<tr class="striped_row">
				<td>', $MyRow[0], '</td>
				<td>', $MyRow[1], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedDT=', $MyRow[0], '">' . __('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedDT=', $MyRow[0], '&amp;delete=yes">' . __('Delete')  . '</a></td>
			</tr>';
	}

	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectedDT) and !isset($_GET['delete'])) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Show all Demand Types') . '</a></div>';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedDT) and !isset($_GET['delete'])) {
	//editing an existing demand type

	$SQL = "SELECT mrpdemandtype,
	        description
		FROM mrpdemandtypes
		WHERE mrpdemandtype='" . $SelectedDT . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['MRPDemandType'] = $MyRow['mrpdemandtype'];
	$_POST['Description'] = $MyRow['description'];

	echo '<input type="hidden" name="SelectedDT" value="' . $SelectedDT . '" />';
	echo '<input type="hidden" name="MRPDemandType" value="' . $_POST['MRPDemandType'] . '" />';
	echo '<fieldset>
			<legend>', __('Edit Demand Type'), '</legend>
			<field>
				<label for="MRPDemandType">' .__('Demand Type') . ':</label>
				<fieldtext>' . $_POST['MRPDemandType'] . '</fieldtext>
			</field>';

} else { //end of if $SelectedDT only do the else when a new record is being entered
	if (!isset($_POST['MRPDemandType'])) {
		$_POST['MRPDemandType'] = '';
	}
	echo '<fieldset>
			<legend>', __('Create Demand Type'), '</legend>
			<field>
				<label for="MRPDemandType">' . __('Demand Type') . ':</label>
				<input type="text" name="MRPDemandType" size="6" maxlength="5" value="' . $_POST['MRPDemandType'] . '" />
			</field>' ;
}

if (!isset($_POST['Description'])) {
	$_POST['Description'] = '';
}

echo '<field>
		<label for="Description">' . __('Demand Type Description') . ':</label>
		<input type="text" name="Description" size="31" maxlength="30" value="' . $_POST['Description'] . '" />
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="submit" value="' . __('Enter Information') . '" />
    </div>
	</form>';

include('includes/footer.php');
