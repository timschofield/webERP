<?php
/* $Id$ */

$PageSecurity = 15;

include('includes/session.inc');
$title = _('Maintenance Of Petty Cash Type of Tabs');
include('includes/header.inc');

if (isset($_POST['SelectedTab'])){
	$SelectedTab = strtoupper($_POST['SelectedTab']);
} elseif (isset($_GET['SelectedTab'])){
	$SelectedTab = strtoupper($_GET['SelectedTab']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	if ($_POST['typetabcode']=='' OR $_POST['typetabcode']==' ' OR $_POST['typetabcode']=='  ') {
		$InputError = 1;
		prnMsg('<br>' . _('The Tabs type code cannot be an empty string or spaces'),'error');
		$Errors[$i] = 'TypeTabCode';
		$i++;
	} elseif (strlen($_POST['typetabcode']) >20) {
		$InputError = 1;
		echo prnMsg(_('The tab code must be twenty characters or less long'),'error');
		$Errors[$i] = 'TypeTabCode';
		$i++;
	}elseif (strlen($_POST['typetabdescription']) >50) {
		$InputError = 1;
		echo prnMsg(_('The tab code must be Fifty characters or less long'),'error');
		$Errors[$i] = 'TypeTabCode';
		$i++;
	}

	if (isset($SelectedTab) AND $InputError !=1) {

		$sql = "UPDATE pctypetabs
			SET typetabdescription = '" . $_POST['typetabdescription'] . "'
			WHERE typetabcode = '$SelectedTab'";

		$msg = _('The Tabs type') . ' ' . $SelectedTab . ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
				 FROM pctypetabs
				 WHERE typetabcode = '" . $_POST['typetabcode'] . "'";

		$checkresult = DB_query($checkSql,$db);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The Tab type ') . $_POST['TypeAbbrev'] . _(' already exist.'),'error');
		} else {

			// Add new record on submit

			$sql = "INSERT INTO pctypetabs
						(typetabcode,
			 			 typetabdescription)
				VALUES ('" . $_POST['typetabcode'] . "',
					'" . $_POST['typetabdescription'] . "')";

			$msg = _('Tabs type') . ' ' . $_POST["typetabcode"] .  ' ' . _('has been created');

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');

		unset($SelectedTab);
		unset($_POST['typetabcode']);
		unset($_POST['typetabdescription']);
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'PcTabExpenses'

	$sqlpctabexpenses= "SELECT COUNT(*)
		FROM pctabexpenses
		WHERE typetabcode='$SelectedTab'";

	$ErrMsg = _('The number of tabs using this Tab type could not be retrieved');
	$resultpctabexpenses = DB_query($sqlpctabexpenses,$db,$ErrMsg);

	$myrowpctabexpenses = DB_fetch_row($resultpctabexpenses);

	$sqlpctabs= "SELECT COUNT(*)
		FROM pctabs
		WHERE typetabcode='$SelectedTab'";

	$ErrMsg = _('The number of tabs using this Tab type could not be retrieved');
	$resultpctabs = DB_query($sqlpctabs,$db,$ErrMsg);

	$myrowpctabs = DB_fetch_row($resultpctabs);
	if ($myrowpctabexpenses[0]>0 or $myrowpctabs[0]>0) {
		echo '<br>';
		prnMsg(_('Cannot delete this tab type because tabs have been created using this tab type'),'error');
		echo '<br>';
		echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<p><div class="centre"><input type=submit name=return VALUE="' . _('Return to list of tab types') . '"></div>';
		echo '</form>';
		include('includes/footer.inc');
		exit;
	} else {

			$sql="DELETE FROM pctypetabs WHERE typetabcode='$SelectedTab'";
			$ErrMsg = _('The Tab Type record could not be deleted because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('Tab type') .  ' ' . $SelectedTab  . ' ' . _('has been deleted') ,'success');
			unset ($SelectedTab);
			unset($_GET['delete']);


	} //end if tab type used in transactions
}

if (!isset($SelectedTab)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTab will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = 'SELECT * FROM pctypetabs';
	$result = DB_query($sql,$db);

	echo '<br><table BORDER=1>';
	echo "<tr>
		<th>" . _('Type Of Tab') . "</th>
		<th>" . _('Description') . "</th>
	</tr>";

$k=0; //row colour counter

while ($myrow = DB_fetch_row($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	printf("<td>%s</td>
		<td>%s</td>
		<td><a href='%sSelectedTab=%s'>" . _('Edit') . "</td>
		<td><a href='%sSelectedTab=%s&delete=yes' onclick=\"return confirm('" . _('Are you sure you wish to delete this code and all the description it may have set up?') . "');\">" . _('Delete') . "</td>
		</tr>",
		$myrow['0'],
		$myrow['1'],
		$_SERVER['PHP_SELF'] . '?' . SID, $myrow['0'],
		$_SERVER['PHP_SELF'] . '?' . SID, $myrow['0']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedTab)) {

	echo '<p><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Show All Types Tabs Defined') . '</a></div><p>';
}
if (! isset($_GET['delete'])) {

	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p><table border=1>'; //Main table
	echo '<td><table>'; // First column


	if ( isset($SelectedTab) AND $SelectedTab!='' )
	{

		$sql = "SELECT typetabcode,
						typetabdescription
				FROM pctypetabs
				WHERE typetabcode='$SelectedTab'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['typetabcode'] = $myrow['typetabcode'];
		$_POST['typetabdescription']  = $myrow['typetabdescription'];

		echo "<input type=hidden name='SelectedTab' VALUE=" . $SelectedTab . ">";
		echo "<input type=hidden name='typetabcode' VALUE=" . $_POST['typetabcode']. ">";
		echo "<table> <tr><td>" . _('Code Of Type Of Tab') . ":</td><td>";

		// We dont allow the user to change an existing type code

		echo $_POST['typetabcode'] . '</td></tr>';

	} else 	{

		// This is a new type so the user may volunteer a type code

		echo "<table><tr><td>" . _('Code Of Type Of Tab') . ":</td><td><input type='Text'
				" . (in_array('TypeTabCode',$Errors) ? 'class="inputerror"' : '' ) ." name='typetabcode'></td></tr>";

	}

	if (!isset($_POST['typetabdescription'])) {
		$_POST['typetabdescription']='';
	}
	echo "<tr><td>" . _('Description Of Type of Tab') . ":</td><td><input type='Text' name='typetabdescription' size=50 maxlength=49 value='" . $_POST['typetabdescription'] . "'></td></tr>";

	echo '</table>'; // close table in first column
	echo '</td></tr></table>'; // close main table

	echo '<p><div class="centre"><input type=submit name=submit VALUE="' . _('Accept') . '"><input type=submit name=Cancel VALUE="' . _('Cancel') . '"></div>';

	echo '</form>';

} // end if user wish to delete

include('includes/footer.inc');
?>