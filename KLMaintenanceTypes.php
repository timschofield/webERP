<?php

include('includes/session.php');
$Title = _('Maintenance Types') . ' / ' . _('Types of Maintenance ');
include('includes/header.php');

if (isset($_POST['SelectedType'])){
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])){
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
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

	if (mb_strlen($_POST['MaintenanceType']) > 10) {
		$InputError = 1;
		prnMsg(_('The maintenance code must be 10 characters or less long'),'error');
		$Errors[$i] = 'MaintenanceType';
		$i++;
	} elseif ($_POST['MaintenanceType']=='' OR $_POST['MaintenanceType']==' ' OR $_POST['MaintenanceType']=='  ') {
		$InputError = 1;
		prnMsg( _('The maintenance code cannot be an empty string or spaces'),'error');
		$Errors[$i] = 'MaintenanceType';
		$i++;
	} elseif( trim($_POST['Description'])==''){
		$InputError = 1;
		prnMsg (_('The maintenance description cannot be empty'),'error');
		$Errors[$i] = 'MaintenanceType';
		$i++;
	} elseif (mb_strlen($_POST['Description']) >50) {
		$InputError = 1;
		echo prnMsg(_('The maintenance description must be 50 characters or less long'),'error');
		$Errors[$i] = 'MaintenanceType';
		$i++;
	}

	if (isset($SelectedType) AND $InputError !=1) {

		$SQL = "UPDATE klmaintenancetypes
			SET description = '" . $_POST['Description'] . "'
			WHERE maintenancetype = '".$SelectedType."'";

		$Msg = _('The maintenance type') . ' ' . $SelectedType . ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$CheckSQL = "SELECT count(*)
			     FROM klmaintenancetypes
			     WHERE maintenancetype = '" . $_POST['MaintenanceType'] . "'";

		$CheckResult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($CheckResult);

		if ( $CheckRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The maintenance type ') . $_POST['MaintenanceType'] . _(' already exist.'),'error');
		} else {

			// Add new record on submit

			$SQL = "INSERT INTO klmaintenancetypes (maintenancetype,
											description)
							VALUES ('" . str_replace(' ', '', $_POST['MaintenanceType']) . "',
									'" . $_POST['Description'] . "')";

			$Msg = _('Maintenance type') . ' ' . $_POST['Description'] .  ' ' . _('has been created');
			$CheckSQL = "SELECT COUNT(maintenancetype)
						FROM klmaintenancetypes";
			$Result = DB_query($CheckSQL);
			$Row = DB_fetch_row($Result);
		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg,'success');
		unset($SelectedType);
		unset($_POST['MaintenanceType']);
		unset($_POST['Description']);
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'klmaintenancetasks'

	$SQL= "SELECT COUNT(*)
	       FROM klmaintenancetasks
	       WHERE klmaintenancetasks.maintenancetype='".$SelectedType."'";

	$ErrMsg = _('The number of maintenance tasks using this maintenance type could not be retrieved');
	$Result = DB_query($SQL,$ErrMsg);

	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg(_('Cannot delete this maintenance type because maintenance tasks have been created using this maintenance type') . '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('tasks using this maintenance type code'),'error');
	} else {
			$SQL="DELETE FROM klmaintenancetypes WHERE maintenancetype='" . $SelectedType . "'";
			$ErrMsg = _('The Maintenance Type record could not be deleted because');
			$Result = DB_query($SQL,$ErrMsg);
			prnMsg(_('Maintenance type') . ' ' . $SelectedType  . ' ' . _('has been deleted') ,'success');
			unset ($SelectedType);
			unset($_GET['delete']);
	} 
}

if(isset($_POST['Cancel'])){
	unset($SelectedType);
	unset($_POST['MaintenanceType']);
	unset($_POST['Description']);
}

if (!isset($SelectedType)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedType will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT maintenancetype,description FROM klmaintenancetypes ORDER BY maintenancetype";
	$Result = DB_query($SQL);

	echo '<table class="selection">
		<thead>
		<tr>
				<th class="SortedColumn">' . _('Type Code') . '</th>
				<th class="SortedColumn">' . _('Type Description') . '</th>
		</tr>
		</thead>
		<tbody>';

while ($MyRow = DB_fetch_row($Result)) {

	printf('<tr class="striped_row">
		<td>%s</td>
		<td>%s</td>
		<td><a href="%sSelectedType=%s">' . _('Edit') . '</a></td>
		<td><a href="%sSelectedType=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this maintenace type?') . '\');">' . _('Delete') . '</a></td>
		</tr>',
		$MyRow[0],
		$MyRow[1],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $MyRow[0],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $MyRow[0]);
	}
	//END WHILE LIST LOOP
	echo '</tbody>
		</table>';
}

//end of ifs and buts!
if (isset($SelectedType)) {

	echo '<br />
			<div class="centre">
				<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">' . _('Show All Maintenance Types Defined') . '</a>
			</div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" >
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />';


	// The user wish to EDIT an existing type
	if ( isset($SelectedType) AND $SelectedType!='' ) {

		$SQL = "SELECT maintenancetype,
			       description
		        FROM klmaintenancetypes
		        WHERE maintenancetype='" . $SelectedType . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['MaintenanceType'] = $MyRow['maintenancetype'];
		$_POST['Description']  = $MyRow['description'];

		echo '<input type="hidden" name="SelectedType" value="' . $SelectedType . '" />
			<input type="hidden" name="MaintenanceType" value="' . $_POST['MaintenanceType'] . '" />
			<table class="selection">
			<tr>
				<th colspan="4"><b>' . _('Maintenance Type Setup') . '</b></th>
			</tr>
			<tr>
				<td>' . _('Type Code') . ':</td>
				<td>' . $_POST['MaintenanceType'] . '</td>
			</tr>';

	} else 	{

		// This is a new type so the user may volunteer a type code

		echo '<table class="selection">
				<tr>
					<th colspan="4"><b>' . _('Maintenance Type List Setup') . '</b></th>
				</tr>
				<tr>
					<td>' . _('Type Code') . ':</td>
					<td><input type="text" ' . (in_array('MaintenanceType',$Errors) ? 'class="inputerror"' : '' ) .' size="11" maxlength="10" name="MaintenanceType" /></td>
				</tr>';
	}

	if (!isset($_POST['Description'])) {
		$_POST['Description']='';
	}
	echo '<tr>
			<td>' . _('Maintenance Type Name') . ':</td>
			<td><input type="text" name="Description" value="' . $_POST['Description'] . '" /></td>
		</tr>
		</table>'; // close main table

	echo '<br /><div class="centre"><input type="submit" name="submit" value="' . _('Accept') . '" /><input type="reset" name="Cancel" value="' . _('Cancel') . '" /></div>
			</div>
          </form>';

} // end if user wish to delete

include('includes/footer.php');
?>