<?php
/* $Revision: 1.9 $ */


$PageSecurity=15;
include('includes/session.inc');
$title = _('Tax Authorities');
include('includes/header.inc');


if (isset($_POST['SelectedTaxID'])){
	$SelectedTaxID =$_POST['SelectedTaxID'];
} elseif(isset($_GET['SelectedTaxID'])){
	$SelectedTaxID =$_GET['SelectedTaxID'];
}


if (isset($_POST['submit'])) {


	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	if ( trim( $_POST['Description'] ) == '' ) {
		$InputError = 1;
		prnMsg( _('The tax type description may not be empty'), 'error');
	}

	if ($InputError !=1 && isset($SelectedTaxID)) {

		/*SelectedTaxID could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = 'UPDATE taxauthorities
				SET taxglcode =' . $_POST['TaxGLCode'] . ',
				purchtaxglaccount =' . $_POST['PurchTaxGLCode'] . ",
				description = '" . DB_escape_string($_POST['Description']) . "'
			WHERE taxid = " . $SelectedTaxID;

		$ErrMsg = _('The update of this tax authority failed because');
		$result = DB_query($sql,$db,$ErrMsg);

		$msg = _('The tax authority for record has been updated');

	} elseif ($InputError !=1) {

	/*Selected tax authority is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new tax authority form */

		$sql = "INSERT INTO taxauthorities (
						taxglcode,
						purchtaxglaccount,
						description)
			VALUES (
				" . $_POST['TaxGLCode'] . ",
				" . $_POST['PurchTaxGLCode'] . ",
				'" .DB_escape_string($_POST['Description']) . "'
				)";


		$Errmsg = _('The addition of this tax authority failed because');
		$result = DB_query($sql,$db,$ErrMsg);

		$msg = _('The new tax authority record has been added to the database');

		$NewTaxID = DB_Last_Insert_ID($db,'taxauthorities','taxid');

		$ErrMsg = _('Could not retrieve the currently in use TaxLevels because');
		$TaxLevelResult = DB_query('SELECT taxlevel
						FROM stockmaster
						GROUP BY taxlevel',$db,$ErrMsg);


		$ErrMsg =  _('Could not retrieve the currently in use dispatch tax authorities from the inventory location records failed because');
		$DispTaxAuthResult = DB_query('SELECT taxauthority FROM locations GROUP BY taxauthority',$db,$ErrMsg);


		while ($DispTaxAuthRow = DB_fetch_array($DispTaxAuthResult)){
			while ($TaxLevelRow = DB_fetch_array($TaxLevelResult)){
				$sql = 'INSERT INTO taxauthlevels (
							taxauthority,
							dispatchtaxauthority,
							level)
						VALUES (
							' . $NewTaxID  . ',
							' . $DispTaxAuthRow['taxauthority'] . ',
							' . $TaxLevelRow['taxlevel'] . '
							)';
				$InsertResult = DB_query($sql,$db);
			}
			DB_data_seek($TaxLevelResult,0);
		}

	}
	//run the SQL from either of the above possibilites
	if ($InputError !=1) {
		unset( $_POST['TaxGLCode']);
		unset( $_POST['PurchTaxGLCode']);
		unset( $_POST['Description']);
		unset( $SelectedTaxID );
	}
	echo "<P>$msg<BR>";

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN OTHER TABLES

	$sql= 'SELECT COUNT(*) FROM custbranch WHERE custbranch.taxauthority=' . $SelectedTaxID;
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnmsg(_('Cannot delete this tax authority because there are customer branches created with this tax authority') . ' - ' . _('change these branches first'),'warn');
		echo '<BR>' . _('There are') . ' ' . $myrow[0] .  ' ' . _('customer branches referring to this authority');
	} else {
		// S.O add check if there are suppliers using this tax authority
		$sql= 'SELECT COUNT(*) FROM suppliers WHERE suppliers.taxauthority=' . $SelectedTaxID;
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg(_('Cannot delete this tax authority because there are suppliers created with this tax authority') . ' - ' . _('change these suppliers first'),'warn');
			echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('suppliers referring to this authority');
		} else {
			// S.O add check if there are suppliers using this tax authority
			$sql= 'SELECT count(*) FROM locations WHERE locations.taxauthority=' . $SelectedTaxID;
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0){
				prnMsg(_('Cannot delete this tax authority because there are inventory locations created with this tax authority') . ' - ' . _('change the inventory location record first'),'warn');
				echo '<br>' . _('There are') . ' ' . $myrow[0] .  ' ' . _('inventory locations referring to this authority');
			} else {

			/*Cascade deletes in TaxAuthLevels */
				$result = DB_query('DELETE FROM taxauthlevels WHERE taxauthority= ' . $SelectedTaxID,$db);
				$result = DB_query('DELETE FROM taxauthlevels WHERE dispatchtaxauthority= ' . $SelectedTaxID,$db);
				$result = DB_query('DELETE FROM taxauthorities WHERE taxid= ' . $SelectedTaxID,$db);
				prnMsg(_('The selected tax authority record has been deleted'),'success');
				unset ($SelectedTaxID);
			}
		}
	} // end of related records testing

}

if (!isset($SelectedTaxID)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTaxID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of tax authorities will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = 'SELECT taxauthorities.taxid,
			taxauthorities.description,
			taxglcode, purchtaxglaccount
		FROM taxauthorities';

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The defined tax authorities could not be retrieved because');
	$DbgMsg = _('The following SQL to retrieve the tax authorities was used');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	echo '<CENTER><table border=1>';
	echo "<tr>
		<td class='tableheader'>" . _('ID') . "</td>
		<td class='tableheader'>" . _('Description') . "</td>
		<td class='tableheader'>" . _('Output Tax') . '<BR>' . _('GL Account') . "</td>
		<td class='tableheader'>" . _('Input Tax') . '<BR>' . _('GL Account') . "</td>
		</tr></FONT>";

	while ($myrow = DB_fetch_row($result)) {

		$DisplayTaxRate	= number_format($myrow[2] * 100, 2) . '%';

		printf("<tr><td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><td><a href=\"%s&TaxAuthority=%s\">" . _('Edit Rates') . "</a></td>
				<td><a href=\"%s&SelectedTaxID=%s\">" . _('Edit') . "</a></td>
				<td><a href=\"%s&SelectedTaxID=%s&delete=yes\">" . _('Delete') . '</a></td>
			</tr>',
			$myrow[0],
			$myrow[1],
			$myrow[2],
			$myrow[3],
			$rootpath . '/TaxAuthorityRates.php?' . SID,
			$myrow[0],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow[0],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow[0]);

	}
	//END WHILE LIST LOOP

	//end of ifs and buts!

	echo '</table></CENTER><p>';
}



if (isset($SelectedTaxID)) {
	echo "<Center><a href='" .  $_SERVER['PHP_SELF'] . '?' . SID ."'>" . _('Reveiw all defined tax authority records') . '</a></Center>';
 }


echo "<P><FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID .'>';

if (isset($SelectedTaxID)) {
	//editing an existing tax authority

	$sql = 'SELECT taxglcode, 
			purchtaxglaccount, 
			description 
		FROM taxauthorities 
		WHERE taxid=' . $SelectedTaxID;

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['TaxGLCode']	= $myrow['taxglcode'];
	$_POST['PurchTaxGLCode']= $myrow['purchtaxglaccount'];
	$_POST['Description']	= $myrow['description'];

	echo "<INPUT TYPE=HIDDEN NAME='SelectedTaxID' VALUE=" . $SelectedTaxID . '>';

}  //end of if $SelectedTaxID only do the else when a new record is being entered


$SQL = 'SELECT accountcode,
		accountname
	FROM chartmaster,
		accountgroups
	WHERE chartmaster.group_=accountgroups.groupname
	AND accountgroups.pandl=0';
$result = DB_query($SQL,$db);

echo '<CENTER><TABLE>
<TR><TD>' . _('Tax Type Description') . ":</TD>
<TD><input type=Text name='Description' SIZE=21 MAXLENGTH=20 value='" . $_POST['Description'] . "'></TD></TR>";

echo '<TR><TD>' . _('Output tax GL Account') . ':</TD>
	<TD><SELECT name=TaxGLCode>';


while ($myrow = DB_fetch_array($result)) {
	if ($myrow['accountcode']==$_POST['TaxGLCode']) {
		echo "<OPTION SELECTED VALUE='";
	} else {
		echo "<OPTION VALUE='";
	}
	echo $myrow['accountcode'] . "'>" . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

} //end while loop

DB_data_seek($result,0);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Input tax GL Account') . ':</TD>
	<TD><SELECT name=PurchTaxGLCode>';

while ($myrow = DB_fetch_array($result)) {
	if ($myrow['accountcode']==$_POST['PurchTaxGLCode']) {
		echo '<OPTION SELECTED VALUE=';
	} else {
		echo '<OPTION VALUE=';
	}
	echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

} //end while loop


echo '</SELECT></TD></TR></TABLE>';

echo '<input type=submit name=submit value=' . _('Enter Information') . '></CENTER></FORM>';

include('includes/footer.inc');

?>