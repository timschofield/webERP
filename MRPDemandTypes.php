<?php
/* $Revision: 1.3 $ */

$PageSecurity=9;

include('includes/session.inc');
$title = _('MRP Demand Types');
include('includes/header.inc');

//SelectedDT is the Selected mrpdemandtype
if (isset($_POST['SelectedDT'])){
	$SelectedDT = trim(strtoupper($_POST['SelectedDT']));
} elseif (isset($_GET['SelectedDT'])){
	$SelectedDT = trim(strtoupper($_GET['SelectedDT']));
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (trim(strtoupper($_POST['mrpdemandtype']) == "WO") or 
	   trim(strtoupper($_POST['mrpdemandtype']) == "SO")) {
		$InputError = 1;
		prnMsg(_('The Demand Type is reserved for the system'),'error');
	} 

	if (strlen($_POST['mrpdemandtype']) < 1) {
		$InputError = 1;
		prnMsg(_('The Demand Type code must be at least 1 character long'),'error');
	} 
	if (strlen($_POST['Description'])<3) {
		$InputError = 1;
		prnMsg(_('The Demand Type description must be at least 3 characters long'),'error');
	}

	if (isset($SelectedDT) AND $InputError !=1) {

		/*SelectedDT could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE mrpdemandtypes SET description = '" . $_POST['Description'] . "'
				WHERE mrpdemandtype = '" . $SelectedDT . "'";
		$msg = _('The demand type record has been updated');
	} elseif ($InputError !=1) {

	//Selected demand type is null cos no item selected on first time round so must be adding a	
	//record must be submitting new entries in the new work centre form 

		$sql = "INSERT INTO mrpdemandtypes (mrpdemandtype,
						description)
					VALUES ('" . trim(strtoupper($_POST['mrpdemandtype'])) . "',
						'" . $_POST['Description'] . "'
						)";
		$msg = _('The new demand type has been added to the database');
	}
	//run the SQL from either of the above possibilites

	if ($InputError !=1){
		$result = DB_query($sql,$db,_('The update/addition of the demand type failed because'));
		prnMsg($msg,'success');
		unset ($_POST['Description']);
		unset ($_POST['mrpdemandtype']);
		unset ($SelectedDT);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'MRPDemands'

	$sql= "SELECT COUNT(*) FROM mrpdemands 
	         WHERE mrpdemands.mrpdemandtype='$SelectedDT'
	         GROUP BY mrpdemandtype";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this demand type because MRP Demand records exist for this type') . '<br>' . _('There are') . ' ' . $myrow[0] . ' ' ._('MRP Demands referring to this type'),'warn');
    } else {
			$sql="DELETE FROM mrpdemandtypes WHERE mrpdemandtype='$SelectedDT'";
			$result = DB_query($sql,$db);
			prnMsg(_('The selected demand type record has been deleted'),'succes');
	} // end of MRPDemands test
}

if (!isset($SelectedDT)) {

//It could still be the second time the page has been run and a record has been selected 
//for modification SelectedDT will exist because it was sent with the new call. If its  
//the first time the page has been displayed with no parameters
//then none of the above are true and the list of demand types will be displayed with
//links to delete or edit each. These will call the same page again and allow update/input
//or deletion of the records

	$sql = 'SELECT mrpdemandtype,
	        description
		FROM mrpdemandtypes';

	$result = DB_query($sql,$db);

	echo "<table border=1>
		<tr BGCOLOR =#800000><th>" . _('Demand Type') . "</th>
				<th>" . _('Description') . "</th>
		</tr></font>";

	while ($myrow = DB_fetch_row($result)) {

		printf("<tr><td>%s</td>
				<td>%s</td>
				<td><a href=\"%s&SelectedDT=%s\">" . _('Edit') . "</td>
				<td><a href=\"%s&SelectedDT=%s&delete=yes\">" . _('Delete') ."</td>
				</tr>",
				$myrow[0],
				$myrow[1],
				$_SERVER['PHP_SELF'] . '?' . SID,
				$myrow[0], $_SERVER['PHP_SELF'] . '?' . SID,
				$myrow[0]);
	}

	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectedDT)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>" . _('Show all Demand Types') . '</a></div>';
}

echo "<p><form method='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";

if (isset($SelectedDT)) {
	//editing an existing demand type

	$sql = "SELECT mrpdemandtype,
	        description
		FROM mrpdemandtypes
		WHERE mrpdemandtype='$SelectedDT'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['mrpdemandtype'] = $myrow['mrpdemandtype'];
	$_POST['Description'] = $myrow['description'];

	echo '<input type=hidden name=SelectedDT value=' . $SelectedDT . '>';
	echo "<input type=hidden name=mrpdemandtype value='" . $_POST['mrpdemandtype'] . "'>";
	echo '<table><tr><td>' ._('Demand Type') . ':</td><td>' . $_POST['mrpdemandtype'] . '</td></tr>';

} else { //end of if $SelectedDT only do the else when a new record is being entered
	if (!isset($_POST['mrpdemandtype'])) {
		$_POST['mrpdemandtype'] = '';
	}
	echo '<table><tr>
			<td>' . _('Demand Type') . ":</td>
			<td><input type='Text' name='mrpdemandtype' size=6 maxlength=5 value='" . $_POST['mrpdemandtype'] . "'></td>
			</tr>" ;
}

if (!isset($_POST['Description'])) {
	$_POST['Description'] = '';
}

echo '<tr><td>' . _('Demand Type Description') . ":</td>
	<td><input type='Text' name='Description' size=31 maxlength=30 value='" . $_POST['Description'] . "'></td>
	</tr>
	</table>";

echo "<div class='centre'><input type='Submit' name='submit' value='" . _('Enter Information') . "'></div>";

echo '</form>';
include('includes/footer.inc');
?>
