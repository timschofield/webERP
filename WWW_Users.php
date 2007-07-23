<?php

/* $Revision: 1.23 $ */

$PageSecurity=15;

include('includes/session.inc');

$ModuleList = array(_('Orders'), 
			_('Receivables'), 
			_('Payables'), 
			_('Purchasing'), 
			_('Inventory'), 
			_('Manufacturing'), 
			_('General Ledger'), 
			_('Setup'));

$title = _('User Maintenance');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

// Make an array of the security roles
$sql = 'SELECT secroleid, 
		secrolename 
	FROM securityroles ORDER BY secroleid';
$Sec_Result = DB_query($sql, $db);
$SecurityRoles = array();
// Now load it into an a ray using Key/Value pairs
while( $Sec_row = DB_fetch_row($Sec_Result) ) {
	$SecurityRoles[$Sec_row[0]] = $Sec_row[1];
}
DB_free_result($Sec_Result);

if (isset($_GET['SelectedUser'])){
	$SelectedUser = $_GET['SelectedUser'];
} elseif (isset($_POST['SelectedUser'])){
	$SelectedUser = $_POST['SelectedUser'];
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (strlen($_POST['UserID'])<4){
		$InputError = 1;
		prnMsg(_('The user ID entered must be at least 4 characters long'),'error');
	} elseif (ContainsIllegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(_('User names cannot contain any of the following characters') . " - ' & + \" \\ " . _('or a space'),'error');
	} elseif (strlen($_POST['Password'])<5){
		if (!$SelectedUser){
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'),'error');
		}
	} elseif (strstr($_POST['Password'],$_POST['UserID'])!= False){
		$InputError = 1;
		prnMsg(_('The password cannot contain the user id'),'error');
	} elseif ((strlen($_POST['Cust'])>0) AND (strlen($_POST['BranchCode'])==0)) {
		$InputError = 1;
		prnMsg(_('If you enter a Customer Code you must also enter a Branch Code valid for this Customer'),'error');
	}
	//comment out except for demo!  Do not want anyone modifying demo user.
	/*
	  elseif ($_POST['UserID'] == 'demo') {
		prnMsg(_('The demonstration user called demo cannot be modified.'),'error');
		$InputError = 1;
	}
	*/

	if ((strlen($_POST['BranchCode'])>0) AND ($InputError !=1)) {
		// check that the entered branch is valid for the customer code
		$sql = "SELECT custbranch.debtorno
				FROM custbranch
				WHERE custbranch.debtorno='" . $_POST['Cust'] . "'
				AND custbranch.branchcode='" . $_POST['BranchCode'] . "'";

		$ErrMsg = _('The check on validity of the customer code and branch failed  because');
		$DbgMsg = _('The SQL that was used to check the customer code and branch was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		if (DB_num_rows($result)==0){
			prnMsg(_('The entered Branch Code is not valid for the entered Customer Code'),'error');
			$InputError = 1;
		}
	}

	/* Make a comma seperated list of modules allowed ready to update the database*/
	$i=0;
	$ModulesAllowed = '';
	while ($i < count($ModuleList)){
		$FormVbl = "Module_" . $i;
		$ModulesAllowed .= $_POST[($FormVbl)] . ',';
		$i++;
	}
	$_POST['ModulesAllowed']= $ModulesAllowed;


	if ($SelectedUser AND $InputError !=1) {

/*SelectedUser could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		if (!isset($_POST['Cust']) OR $_POST['Cust']==NULL OR $_POST['Cust']==''){
			$_POST['Cust']='';
			$_POST['BranchCode']='';
		}
		$UpdatePassword = "";
		if ($_POST['Password'] != ""){
			$UpdatePassword = "password='" . CryptPass($_POST['Password']) . "',";
		}

		$sql = "UPDATE www_users SET realname='" . DB_escape_string($_POST['RealName']) . "',
						customerid='" . DB_escape_string($_POST['Cust']) ."',
						phone='" . DB_escape_string($_POST['Phone']) ."',
						email='" . DB_escape_string($_POST['Email']) ."',
						".$UpdatePassword."
						branchcode='" . DB_escape_string($_POST['BranchCode']) . "',
						pagesize='" . $_POST['PageSize'] . "',
						fullaccess=" . $_POST['Access'] . ",
						theme='" . $_POST['Theme'] . "',
						language ='" . $_POST['Language'] . "',
						defaultlocation='" . $_POST['DefaultLocation'] ."',
						modulesallowed='" . $ModulesAllowed . "',
						blocked=" . $_POST['Blocked'] . "
					WHERE userid = '$SelectedUser'";

		$msg = _('The selected user record has been updated');
	} elseif ($InputError !=1) {

		$sql = "INSERT INTO www_users (userid,
						realname,
						customerid,
						branchcode,
						password,
						phone,
						email,
						pagesize,
						fullaccess,
						defaultlocation,
						modulesallowed,
						displayrecordsmax,
						theme,
						language)
					VALUES ('" . $_POST['UserID'] . "',
						'" . DB_escape_string($_POST['RealName']) ."',
						'" . DB_escape_string($_POST['Cust']) ."',
						'" . DB_escape_string($_POST['BranchCode']) ."',
						'" . CryptPass($_POST['Password']) ."',
						'" . DB_escape_string($_POST['Phone']) . "',
						'" . DB_escape_string($_POST['Email']) ."',
						'" . $_POST['PageSize'] ."',
						" . $_POST['Access'] . ",
						'" . $_POST['DefaultLocation'] ."',
						'" . $ModulesAllowed . "',
						" . $_SESSION['DefaultDisplayRecordsMax'] . ",
						'" . $_POST['Theme'] . "',
						'". $_POST['Language'] ."')";
		$msg = _('A new user record has been inserted');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$ErrMsg = _('The user alterations could not be processed because');
		$DbgMsg = _('The SQL that was used to update the user and failed was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		unset($_POST['UserID']);
		unset($_POST['RealName']);
		unset($_POST['Cust']);
		unset($_POST['BranchCode']);
		unset($_POST['Phone']);
		unset($_POST['Email']);
		unset($_POST['Password']);
		unset($_POST['PageSize']);
		unset($_POST['Access']);
		unset($_POST['DefaultLocation']);
		unset($_POST['ModulesAllowed']);
		unset($_POST['Blocked']);
		unset($_POST['Theme']);
		unset($_POST['Language']);
		unset($SelectedUser);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	// comment out except for demo!  Do not want anyopne deleting demo user.
	/*
	if ($SelectedUser == 'demo') {
		prnMsg(_('The demonstration user called demo cannot be deleted'),'error');
	} else {
	*/

		$sql="DELETE FROM www_users WHERE userid='$SelectedUser'";
		$ErrMsg = _('The User could not be deleted because');;
		$result = DB_query($sql,$db,$ErrMsg);

	// }

	prnMsg(_('User Deleted'),'info');
	unset($SelectedUser);
}

if (!isset($SelectedUser)) {

/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT userid,
			realname,
			phone,
			email,
			customerid,
			branchcode,
			lastvisitdate,
			fullaccess,
			pagesize,
			theme,
			language
		FROM www_users";
	$result = DB_query($sql,$db);

	echo '<CENTER><table border=1>';
	echo "<tr><td class='tableheader'>" . _('User Login') . "</td>
		<td class='tableheader'>" . _('Full Name') . "</td>
		<td class='tableheader'>" . _('Telephone') . "</td>
		<td class='tableheader'>" . _('Email') . "</td>
		<td class='tableheader'>" . _('Customer Code') . "</td>
		<td class='tableheader'>" . _('Branch Code') . "</td>
		<td class='tableheader'>" . _('Last Visit') . "</td>
		<td class='tableheader'>" . _('Security Role') ."</td>
		<td class='tableheader'>" . _('Report Size') ."</td>
		<td class='tableheader'>" . _('Theme') ."</td>
		<td class='tableheader'>" . _('Language') ."</td>
	</tr>";

	$k=0; //row colour counter

	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}

		$LastVisitDate = ConvertSQLDate($myrow[6]);

		/*The SecurityHeadings array is defined in config.php */

		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%s&SelectedUser=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedUser=%s&delete=1\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow[0],
			$myrow[1],
			$myrow[2],
			$myrow[3],
			$myrow[4],
			$myrow[5],
			$LastVisitDate,
			$SecurityRoles[($myrow[7])],
			$myrow[8],
			$myrow[9],
			$myrow[10],
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow[0],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow[0]);

	} //END WHILE LIST LOOP
	echo '</TABLE></CENTER>';
} //end of ifs and buts!


if (isset($SelectedUser)) {
	echo "<Center><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Review Existing Users') . '</a></Center>';
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

if (isset($SelectedUser)) {
	//editing an existing User

	$sql = "SELECT userid,
			realname,
			phone,
			email,
			customerid,
			password,
			branchcode,
			pagesize,
			fullaccess,
			defaultlocation,
			modulesallowed,
			blocked,
			theme,
			language		
		FROM www_users
		WHERE userid='" . $SelectedUser . "'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['UserID'] = $myrow['userid'];
	$_POST['RealName'] = $myrow['realname'];
	$_POST['Phone'] = $myrow['phone'];
	$_POST['Email'] = $myrow['email'];
	$_POST['Cust']	= $myrow['customerid'];
	$_POST['BranchCode']  = $myrow['branchcode'];
	$_POST['PageSize'] = $myrow['pagesize'];
	$_POST['Access'] = $myrow['fullaccess'];
	$_POST['DefaultLocation'] = $myrow['defaultlocation'];
	$_POST['ModulesAllowed'] = $myrow['modulesallowed'];
	$_POST['Theme'] = $myrow['theme'];
	$_POST['Language'] = $myrow['language'];
	$_POST['Blocked'] = $myrow['blocked'];
	
	echo "<INPUT TYPE=HIDDEN NAME='SelectedUser' VALUE='" . $SelectedUser . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='UserID' VALUE='" . $_POST['UserID'] . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='ModulesAllowed' VALUE='" . $_POST['ModulesAllowed'] . "'>";

	echo '<CENTER><TABLE> <TR><TD>' . _('User code') . ':</TD><TD>';
	echo $_POST['UserID'] . '</TD></TR>';

} else { //end of if $SelectedUser only do the else when a new record is being entered

	echo '<CENTER><TABLE><TR><TD>' . _('User Login') . ":</TD><TD><input type='Text' name='UserID' SIZE=22 MAXLENGTH=20 Value='" . $_POST['UserID'] . "'></TD></TR>";

	/*set the default modules to show to all
	this had trapped a few people previously*/
	$i=0;
	foreach($ModuleList as $ModuleName){
		if ($i>0){
			$_POST['ModulesAllowed'] .=',';
		}
		$_POST['ModulesAllowed'] .= '1';
		$i++;
	}
}

echo '<TR><TD>' . _('Password') . ":</TD>
	<TD><INPUT TYPE='Password' name='Password' SIZE=22 MAXLENGTH=20 VALUE='" . $_POST['Password'] . "'></TR>";
echo '<TR><TD>' . _('Full Name') . ":</TD>
	<TD><INPUT TYPE='text' name='RealName' VALUE='" . $_POST['RealName'] . "' SIZE=36 MAXLENGTH=35></TD></TR>";
echo '<TR><TD>' . _('Telephone No') . ":</TD>
	<TD><INPUT TYPE='Text' name='Phone' VALUE='" . $_POST['Phone'] . "' SIZE=32 MAXLENGTH=30></TD></TR>";
echo '<TR><TD>' . _('Email Address') .":</TD>
	<TD><INPUT TYPE='Text' name='Email' VALUE='" . $_POST['Email'] ."' SIZE=32 MAXLENGTH=55></TD></TR>";
echo '<TR><TD>' . _('Security Role') . ":</TD><TD><SELECT NAME='Access'>";

foreach ($SecurityRoles as $SecKey => $SecVal) {
	if ($SecKey == $_POST['Access']){
		echo "<OPTION SELECTED VALUE=" . $SecKey . ">" . $SecVal;
	} else {
		echo "<OPTION VALUE=" . $SecKey . ">" . $SecVal;
	}
}

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Default Location') . ":</TD>
	<TD><SELECT name='DefaultLocation'>";

$sql = "SELECT loccode, locationname FROM locations";
$result = DB_query($sql,$db);

while ($myrow=DB_fetch_array($result)){

	if ($myrow['loccode'] == $_POST['DefaultLocation']){

		echo "<OPTION SELECTED Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];

	} else {
		echo "<OPTION Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];

	}

}

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Customer Code') . ":</TD>
	<TD><INPUT TYPE='Text' name='Cust' SIZE=10 MAXLENGTH=8 VALUE='" . $_POST['Cust'] . "'></TD></TR>";

echo '<TR><TD>' . _('Branch Code') . ":</TD>
	<TD><INPUT TYPE='Text' name='BranchCode' SIZE=10 MAXLENGTH=8 VALUE='" . $_POST['BranchCode'] ."'></TD></TR>";



echo '<TR><TD>' . _('Reports Page Size') .":</TD>
	<TD><SELECT name='PageSize'>";

if($_POST['PageSize']=='A4'){
	echo "<OPTION SELECTED Value='A4'>" . _('A4');
} else {
	echo "<OPTION Value='A4'>A4";
}

if($_POST['PageSize']=='A3'){
	echo "<OPTION SELECTED Value='A3'>" . _('A3');
} else {
	echo "<OPTION Value='A3'>A3";
}

if($_POST['PageSize']=='A3_landscape'){
	echo "<OPTION SELECTED Value='A3_landscape'>" . _('A3') . ' ' . _('landscape');
} else {
	echo "<OPTION Value='A3_landscape'>" . _('A3') . ' ' . _('landscape');
}

if($_POST['PageSize']=='letter'){
	echo "<OPTION SELECTED Value='letter'>" . _('Letter');
} else {
	echo "<OPTION Value='letter'>" . _('Letter');
}

if($_POST['PageSize']=='letter_landscape'){
	echo "<OPTION SELECTED Value='letter_landscape'>" . _('Letter') . ' ' . _('landscape');
} else {
	echo "<OPTION Value='letter_landscape'>" . _('Letter') . ' ' . _('landscape');
}

if($_POST['PageSize']=='legal'){
	echo "<OPTION SELECTED Value='legal'>" . _('Legal');
} else {
	echo "<OPTION Value='legal'>" . _('Legal');
}
if($_POST['PageSize']=='legal_landscape'){
	echo "<OPTION SELECTED Value='legal_landscape'>" . _('Legal') . ' ' . _('landscape');
} else {
	echo "<OPTION Value='legal_landscape'>" . _('Legal') . ' ' . _('landscape');
}

echo '</SELECT></TD></TR>';

echo '<TR>
	<TD>' . _('Theme') . ":</TD>
	<TD><SELECT name='Theme'>";

$ThemeDirectory = dir('css/');


while (false != ($ThemeName = $ThemeDirectory->read())){

	if (is_dir("css/$ThemeName") AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != 'CVS'){

		if ($_POST['Theme'] == $ThemeName){
			echo "<OPTION SELECTED VALUE='$ThemeName'>$ThemeName";
		} else {
			echo "<OPTION VALUE='$ThemeName'>$ThemeName";
		}
	}
}

echo '</SELECT></TD></TR>';


echo '<TR>
	<TD>' . _('Language') . ":</TD>
	<TD><SELECT name='Language'>";

$LangDirHandle = dir('locale/');


while (false != ($LanguageEntry = $LangDirHandle->read())){

	if (is_dir('locale/' . $LanguageEntry) AND $LanguageEntry != '..' AND $LanguageEntry != 'CVS' AND $LanguageEntry!='.'){

		if ($_POST['Language'] == $LanguageEntry){
			echo "<OPTION SELECTED VALUE='$LanguageEntry'>$LanguageEntry";
		} else {
			echo "<OPTION VALUE='$LanguageEntry'>$LanguageEntry";
		}
	}
}

echo '</SELECT></TD></TR>';


/*Make an array out of the comma seperated list of modules allowed*/
$ModulesAllowed = explode(',',$_POST['ModulesAllowed']);

$i=0;
foreach($ModuleList as $ModuleName){

	echo '<TR><TD>' . _('Display') . ' ' . $ModuleName . ' ' . _('options') . ": </TD><TD><SELECT name='Module_" . $i . "'>";
	if ($ModulesAllowed[$i]==0){
		echo '<OPTION SELECTED VALUE=0>' . _('No');
		echo '<OPTION VALUE=1>' . _('Yes');
	} else {
	 	echo '<OPTION SELECTED VALUE=1>' . _('Yes');
		echo '<OPTION VALUE=0>' . _('No');
	}
	echo '</SELECT></TD></TR>';
	$i++;
}

echo '<TR><TD>' . _('Account Status') . ":</TD><TD><SELECT name='Blocked'>";
if ($_POST['Blocked']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('Open');
	echo '<OPTION VALUE=1>' . _('Blocked');
} else {
 	echo '<OPTION SELECTED VALUE=1>' . _('Blocked');
	echo '<OPTION VALUE=0>' . _('Open');
}
echo '</SELECT></TD></TR>';


echo "</TABLE>
	<CENTER><input type='Submit' name='submit' value='" . _('Enter Information') . "'></CENTER></FORM>";

include('includes/footer.inc');

?>
