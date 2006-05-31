<?php
/* $Revision: 1.10 $ */
$PageSecurity = 3;

include('includes/session.inc');

$title = _('Sales Area Maintenance');

include('includes/header.inc');


if (isset($_GET['SelectedArea'])){
	$SelectedArea = strtoupper($_GET['SelectedArea']);
} elseif (isset($_POST['SelectedArea'])){
	$SelectedArea = strtoupper($_POST['SelectedArea']);
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$_POST['AreaCode'] = strtoupper($_POST['AreaCode']);
	// mod to handle 3 char area codes
	if (strlen($_POST['AreaCode']) > 3) {
		$InputError = 1;
		prnMsg(_('The area code must be three characters or less long'),'error');
	} elseif (strlen($_POST['AreaDescription']) >25) {
		$InputError = 1;
		prnMsg(_('The area description must be twenty five characters or less long'),'error');
	} elseif ( trim($_POST['AreaCode']) == '' ) {
		$InputError = 1;
		prnMsg(_('The area code may not be empty'),'error');
	} elseif ( trim($_POST['AreaDescription']) == '' ) {
		$InputError = 1;
		prnMsg(_('The area description may not be empty'),'error');
	}
	
	if ($SelectedArea AND $InputError !=1) {

		/*SelectedArea could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$sql = "UPDATE areas SET
				areacode='" . $_POST['AreaCode'] . "',
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
	}

	//run the SQL from either of the above possibilites
	$ErrMsg = _('The area could not be added or updated because');
	$DbgMsg = _('The SQL that failed was');
	$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
	if ($InputError !=1) {
		unset($SelectedArea);
		unset($_POST['AreaCode']);
		unset($_POST['AreaDescription']);
	}
	
	prnMsg($msg,'success');

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
			prnMsg( _('Cannot delete this area because sales analysis ecords exist that use this area'),'warn');
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

	echo '<CENTER><table border=1>';
	echo "<tr>
		<td class='tableheader'>" . _('Area Code') . "</td>
		<td class='tableheader'>" . _('Area Name') . '</td>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		echo '<TD>' . $myrow[0] . '</TD>';
		echo '<TD>' . $myrow[1] . '</TD>';
		echo '<TD><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedArea=' . $myrow[0] . '">' . _('Edit') . '</A></TD>';
		echo '<TD><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedArea=' . $myrow[0] . '&delete=yes">' . _('Delete') . '</A></TD>';

	}
	//END WHILE LIST LOOP
	echo '</TABLE></CENTER>';
}

//end of ifs and buts!

if (isset($SelectedArea)) {
	echo "<CENTER><A HREF='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>" . _('Review Areas Defined') . '</A></CENTER>';
}


if (!isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if ($SelectedArea) {
		//editing an existing area

		$sql = "SELECT areacode,
				areadescription
			FROM areas
			WHERE areacode='$SelectedArea'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['AreaCode'] = $myrow['areacode'];
		$_POST['AreaDescription']  = $myrow['areadescription'];

		echo "<INPUT TYPE=HIDDEN NAME=SelectedArea VALUE=" . $SelectedArea . '>';
		echo '<INPUT TYPE=HIDDEN NAME=AreaCode VALUE=' .$_POST['AreaCode'] . '>';
		echo '<CENTER><TABLE><TR><TD>' . _('Area Code') . ':</TD><TD>' . $_POST['AreaCode'] . '</TD></TR>';

	} else {
		echo '<CENTER><TABLE>
			<TR>
				<TD>' . _('Area Code') . ":</TD>
				<TD><input type='Text' name='AreaCode' value='" . $_POST['AreaCode'] . "' SIZE=3 MAXLENGTH=3></TD>
			</TR>";
	}

	echo '<TR><TD>' . _('Area Name') . ":</TD>
		<TD><input type='Text' name='AreaDescription' value='" . $_POST['AreaDescription'] ."' SIZE=26 MAXLENGTH=25></TD>
		</TR>
	</TABLE>";

	echo "<CENTER><input type='Submit' name='submit' value=" . _('Enter Information') .">
		</FORM>";

 } //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
