<?php

/* $Revision: 1.19 $ */


$PageSecurity=15;
include('includes/session.inc');
$title = _('Tax Authorities');
include('includes/header.inc');


if (isset($_POST['SelectedTaxAuthID'])){
	$SelectedTaxAuthID =$_POST['SelectedTaxAuthID'];
} elseif(isset($_GET['SelectedTaxAuthID'])){
	$SelectedTaxAuthID =$_GET['SelectedTaxAuthID'];
}


if (isset($_POST['submit'])) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	if ( trim( $_POST['Description'] ) == '' ) {
		$InputError = 1;
		prnMsg( _('The tax type description may not be empty'), 'error');
	}

	if (isset($SelectedTaxAuthID)) {

		/*SelectedTaxAuthID could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = 'UPDATE taxauthorities
				SET taxglcode =' . $_POST['TaxGLCode'] . ',
				purchtaxglaccount =' . $_POST['PurchTaxGLCode'] . ",
				description = '" . $_POST['Description'] . "',
				bank = '". $_POST['Bank']."',
				bankacctype = '". $_POST['BankAccType']."',
				bankacc = '". $_POST['BankAcc']."',
				bankswift = '". $_POST['BankSwift']."'
			WHERE taxid = " . $SelectedTaxAuthID;

		$ErrMsg = _('The update of this tax authority failed because');
		$result = DB_query($sql,$db,$ErrMsg);

		$msg = _('The tax authority for record has been updated');

	} elseif ($InputError !=1) {

	/*Selected tax authority is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new tax authority form */

		$sql = "INSERT INTO taxauthorities (
						taxglcode,
						purchtaxglaccount,
						description,
						bank,
						bankacctype,
						bankacc,
						bankswift) 
			VALUES (
				" . $_POST['TaxGLCode'] . ",
				" . $_POST['PurchTaxGLCode'] . ",
				'" .$_POST['Description'] . "',
				'" .$_POST['Bank'] . "',
				'" .$_POST['BankAccType'] . "',
				'" .$_POST['BankAcc'] . "',
				'" .$_POST['BankSwift'] . "'
				)";

		$Errmsg = _('The addition of this tax authority failed because');
		$result = DB_query($sql,$db,$ErrMsg);

		$msg = _('The new tax authority record has been added to the database');

		$NewTaxID = DB_Last_Insert_ID($db,'taxauthorities','taxid');

		$sql = 'INSERT INTO taxauthrates (
					taxauthority,
					dispatchtaxprovince,
					taxcatid
					)
				SELECT 
					' . $NewTaxID  . ',
					taxprovinces.taxprovinceid,
					taxcategories.taxcatid
				FROM taxprovinces, 
					taxcategories';
							
			$InsertResult = DB_query($sql,$db);
	}
	//run the SQL from either of the above possibilites
	if (isset($InputError) and $InputError !=1) {
		unset( $_POST['TaxGLCode']);
		unset( $_POST['PurchTaxGLCode']);
		unset( $_POST['Description']);
		unset( $SelectedTaxID );
	}
	
	prnMsg($msg);
		
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN OTHER TABLES

	$sql= 'SELECT COUNT(*) 
			FROM taxgrouptaxes 
		WHERE taxauthid=' . $SelectedTaxAuthID;
		
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnmsg(_('Cannot delete this tax authority because there are tax groups defined that use it'),'warn');
	} else {
		/*Cascade deletes in TaxAuthLevels */
		$result = DB_query('DELETE FROM taxauthrates WHERE taxauthority= ' . $SelectedTaxAuthID,$db);
		$result = DB_query('DELETE FROM taxauthorities WHERE taxid= ' . $SelectedTaxAuthID,$db);
		prnMsg(_('The selected tax authority record has been deleted'),'success');
		unset ($SelectedTaxAuthID);
	} // end of related records testing
}

if (!isset($SelectedTaxAuthID)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTaxAuthID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of tax authorities will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = 'SELECT taxid,
			description,
			taxglcode, 
			purchtaxglaccount,
			bank,
			bankacc,
			bankacctype,
			bankswift
		FROM taxauthorities';

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The defined tax authorities could not be retrieved because');
	$DbgMsg = _('The following SQL to retrieve the tax authorities was used');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('ID') . "</th>
		<th>" . _('Description') . "</th>
		<th>" . _('Input Tax') . '<br>' . _('GL Account') . "</th>
		<th>" . _('Output Tax') . '<br>' . _('GL Account') . "</th>
		<th>" . _('Bank') . "</th>
		<th>" . _('Bank Account') . "</th>
		<th>" . _('Bank Act Type') . "</th>
		<th>" . _('Bank Swift') . "</th>
		</tr></font>";

	while ($myrow = DB_fetch_row($result)) {

		printf("<tr><td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><td><a href=\"%s&TaxAuthority=%s\">" . _('Edit Rates') . "</a></td>
				<td><a href=\"%s&SelectedTaxAuthID=%s\">" . _('Edit') . "</a></td>
				<td><a href=\"%s&SelectedTaxAuthID=%s&delete=yes\">" . _('Delete') . '</a></td>
			</tr>',
			$myrow[0],
			$myrow[1],
			$myrow[3],
			$myrow[2],
			$myrow[4],
			$myrow[5],
			$myrow[6],
			$myrow[7],
			$rootpath . '/TaxAuthorityRates.php?' . SID,
			$myrow[0],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow[0],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow[0]);

	}
	//END WHILE LIST LOOP

	//end of ifs and buts!

	echo '</table><p>';
}



if (isset($SelectedTaxAuthID)) {
	echo "<div class='centre'><a href='" .  $_SERVER['PHP_SELF'] . '?' . SID ."'>" . _('Review all defined tax authority records') . '</a></div>';
 }


echo "<p><form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID .'>';

if (isset($SelectedTaxAuthID)) {
	//editing an existing tax authority

	$sql = 'SELECT taxglcode, 
			purchtaxglaccount, 
			description,
			bank,
			bankacc,
			bankacctype,
			bankswift 
		FROM taxauthorities 
		WHERE taxid=' . $SelectedTaxAuthID;

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['TaxGLCode']	= $myrow['taxglcode'];
	$_POST['PurchTaxGLCode']= $myrow['purchtaxglaccount'];
	$_POST['Description']	= $myrow['description'];
	$_POST['Bank']		= $myrow['bank'];
	$_POST['BankAccType']	= $myrow['bankacctype'];
	$_POST['BankAcc'] 	= $myrow['bankacc'];
	$_POST['BankSwift']	= $myrow['bankswift'];
 

	echo "<input type=hidden name='SelectedTaxAuthID' VALUE=" . $SelectedTaxAuthID . '>';

}  //end of if $SelectedTaxAuthID only do the else when a new record is being entered


$SQL = 'SELECT accountcode,
		accountname
	FROM chartmaster,
		accountgroups
	WHERE chartmaster.group_=accountgroups.groupname
	AND accountgroups.pandl=0 
	ORDER BY accountcode';
$result = DB_query($SQL,$db);

if (!isset($_POST['Description'])) {
	$_POST['Description']='';
}
echo '<table>
<tr><td>' . _('Tax Type Description') . ":</td>
<td><input type=Text name='Description' size=21 maxlength=20 value='" . $_POST['Description'] . "'></td></tr>";


echo '<tr><td>' . _('Input tax GL Account') . ':</td>
	<td><select name=PurchTaxGLCode>';

while ($myrow = DB_fetch_array($result)) {
	if (isset($_POST['PurchTaxGLCode']) and $myrow['accountcode']==$_POST['PurchTaxGLCode']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

} //end while loop

echo '</select></td></tr>';

DB_data_seek($result,0);

echo '<tr><td>' . _('Output tax GL Account') . ':</td>
	<td><select name=TaxGLCode>';


while ($myrow = DB_fetch_array($result)) {
	if (isset($_POST['TaxGLCode']) and $myrow['accountcode']==$_POST['TaxGLCode']) {
		echo "<option selected VALUE='";
	} else {
		echo "<option VALUE='";
	}
	echo $myrow['accountcode'] . "'>" . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

} //end while loop

if (!isset($_POST['Bank'])) {
	$_POST['Bank']='';
}
if (!isset($_POST['BankAccType'])) {
	$_POST['BankAccType']='';
}
if (!isset($_POST['BankAcc'])) {
	$_POST['BankAcc']='';
}
if (!isset($_POST['BankSwift'])) {
	$_POST['BankSwift']='';
}

echo '</select></td></tr>';
echo '<tr><td>' . _('Bank Name') . ':</td>';
echo '<td><input type=Text name="Bank" size=41 maxlength=40 value="' . $_POST['Bank'] . '"></td></tr>';
echo '<tr><td>' . _('Bank Account Type') . ':</td>';
echo '<td><input type=Text name="BankAccType" size=15 maxlength=20 value="' . $_POST['BankAccType'] . '"></td></tr>';
echo '<tr><td>' . _('Bank Account') . ':</td>';
echo '<td><input type=Text name="BankAcc" size=21 maxlength=20 value="' . $_POST['BankAcc'] . '"></td></tr>';
echo '<tr><td>' . _('Bank Swift No') . ':</td>';
echo '<td><input type=Text name="BankSwift" size=15 maxlength=14 value="' . $_POST['BankSwift'] . '"></td></tr>';

echo '</table>';

echo '<div class="centre"><input type=submit name=submit value=' . _('Enter Information') . '></div></form>';

include('includes/footer.inc');

?>