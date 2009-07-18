<?php
/* $Revision: 1.22 $ */
$PageSecurity = 3;

include('includes/session.inc');

$title = _('Sales Area Maintenance');

include('includes/header.inc');


if (isset($_GET['SelectedArea'])){
	$SelectedArea = strtoupper($_GET['SelectedArea']);
} elseif (isset($_POST['SelectedArea'])){
	$SelectedArea = strtoupper($_POST['SelectedArea']);
}

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	$i=1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$_POST['AreaCode'] = strtoupper($_POST['AreaCode']);
	$sql = "SELECT count(areacode) from areas WHERE areacode='".$_POST['AreaCode']."'";
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_row($result);
	// mod to handle 3 char area codes
	if (strlen($_POST['AreaCode']) > 3) {
		$InputError = 1;
		prnMsg(_('The area code must be three characters or less long'),'error');
		$Errors[$i] = 'AreaCode';
		$i++;
	} elseif ($myrow[0]>0 and !isset($SelectedArea)){
		$InputError = 1;
		prnMsg(_('The area code entered already exists'),'error');		
		$Errors[$i] = 'AreaCode';
		$i++;
	} elseif (strlen($_POST['AreaDescription']) >25) {
		$InputError = 1;
		prnMsg(_('The area description must be twenty five characters or less long'),'error');
		$Errors[$i] = 'AreaDescription';
		$i++;
	} elseif ( trim($_POST['AreaCode']) == '' ) {
		$InputError = 1;
		prnMsg(_('The area code may not be empty'),'error');
		$Errors[$i] = 'AreaCode';
		$i++;
	} elseif ( trim($_POST['AreaDescription']) == '' ) {
		$InputError = 1;
		prnMsg(_('The area description may not be empty'),'error');
		$Errors[$i] = 'AreaDescription';
		$i++;
	}
	
	if (isset($SelectedArea) AND $InputError !=1) {

		/*SelectedArea could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$sql = "UPDATE areas SET
				areadescription='" . $_POST['AreaDescription'] . "'
			WHERE areacode = '$SelectedArea'";

		$msg = _('Area code') . ' ' . $SelectedArea  . ' ' . _('has been updated');

	} elseif ($InputError !=1) {

	/*Selectedarea is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new area form */

		$sql = "INSERT INTO areas (areacode,
						areadescription)
				VALUES (
					'" . $_POST['AreaCode'] . "',
					'" . $_POST['AreaDescription'] . "'
					)";

		$SelectedArea =$_POST['AreaCode'];
		$msg = _('New area code') . ' ' . $_POST['AreaCode'] . ' ' . _('has been inserted');
	} else {
		$msg='';
	}

	//run the SQL from either of the above possibilites
	if ($InputError !=1) {
		$ErrMsg = _('The area could not be added or updated because');
		$DbgMsg = _('The SQL that failed was');
		$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
		unset($SelectedArea);
		unset($_POST['AreaCode']);
		unset($_POST['AreaDescription']);
		prnMsg($msg,'success');
	}
	
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorsMaster'

	$sql= "SELECT COUNT(*) FROM custbranch WHERE custbranch.area='$SelectedArea'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg( _('Cannot delete this area because customer branches have been created using this area'),'warn');
		echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('branches using this area code');

	} else {
		$sql= "SELECT COUNT(*) FROM salesanalysis WHERE salesanalysis.area ='$SelectedArea'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			prnMsg( _('Cannot delete this area because sales analysis records exist that use this area'),'warn');
			echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('sales analysis records referring this area code');
		}
	}

	if ($CancelDelete==0) {
		$sql="DELETE FROM areas WHERE areacode='" . $SelectedArea . "'";
		$result = DB_query($sql,$db);
		prnMsg(_('Area Code') . ' ' . $SelectedArea . ' ' . _('has been deleted') .' !','success');
	} //end if Delete area
	unset($SelectedArea);
	unset($_GET['delete']);
} 

if (!isset($SelectedArea)) {

	$sql = 'SELECT * FROM areas';
	$result = DB_query($sql,$db);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('Area Code') . "</th>
		<th>" . _('Area Name') . '</th>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		echo '<td>' . $myrow[0] . '</td>';
		echo '<td>' . $myrow[1] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedArea=' . $myrow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedArea=' . $myrow[0] . '&delete=yes">' . _('Delete') . '</a></td>';

	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectedArea)) {
	echo '<div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Review Areas Defined') . '</a></div>';
}


if (!isset($_GET['delete'])) {

	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if (isset($SelectedArea)) {
		//editing an existing area

		$sql = "SELECT areacode,
				areadescription
			FROM areas
			WHERE areacode='$SelectedArea'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['AreaCode'] = $myrow['areacode'];
		$_POST['AreaDescription']  = $myrow['areadescription'];

		echo '<input type=hidden name=SelectedArea VALUE=' . $SelectedArea . '>';
		echo '<input type=hidden name=AreaCode VALUE=' .$_POST['AreaCode'] . '>';
		echo '<table><tr><td>' . _('Area Code') . ':</td><td>' . $_POST['AreaCode'] . '</td></tr>';

	} else {
		if (!isset($_POST['AreaCode'])) {
			$_POST['AreaCode'] = '';
		}
		if (!isset($_POST['AreaDescription'])) {
			$_POST['AreaDescription'] = '';
		}
		echo '<table>
			<tr>
				<td>' . _('Area Code') . ':</td>
				<td><input tabindex="1" ' . (in_array('AreaCode',$Errors) ?  'class="inputerror"' : '' ) .'   type="Text" name="AreaCode" value="' . $_POST['AreaCode'] . '" size=3 maxlength=3></td>
			</tr>';
	}

	echo '<tr><td>' . _('Area Name') . ':</td>
		<td><input tabindex="2" ' . (in_array('AreaDescription',$Errors) ?  'class="inputerror"' : '' ) .'  type="text" name="AreaDescription" value="' . $_POST['AreaDescription'] .'" size=26 maxlength=25></td>
		</tr>
	</table>';

	echo '<br><div class="centre"><input tabindex="3" type="Submit" name="submit" value=' . _('Enter Information') .'></div></form>';

 } //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
