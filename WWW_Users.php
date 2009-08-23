<?php

/* $Revision: 1.35 $ */

$PageSecurity=15;

if (isset($_POST['UserID']) AND isset($_POST['ID'])){
	if ($_POST['UserID'] == $_POST['ID']) {
		$_POST['Language'] = $_POST['UserLanguage'];
	}
}
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

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/group_add.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'<br>';

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
	if (strlen($_POST['UserID'])<3){
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

		$ErrMsg = _('The check on validity of the customer code and branch failed because');
		$DbgMsg = _('The SQL that was used to check the customer code and branch was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		if (DB_num_rows($result)==0){
			prnMsg(_('The entered Branch Code is not valid for the entered Customer Code'),'error');
			$InputError = 1;
		}
	}

	/* Make a comma separated list of modules allowed ready to update the database*/
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

		$sql = "UPDATE www_users SET realname='" . $_POST['RealName'] . "',
						customerid='" . $_POST['Cust'] ."',
						phone='" . $_POST['Phone'] ."',
						email='" . $_POST['Email'] ."',
						" . $UpdatePassword . "
						branchcode='" . $_POST['BranchCode'] . "',
						salesman='" . $_POST['Salesman'] . "',
						pagesize='" . $_POST['PageSize'] . "',
						fullaccess=" . $_POST['Access'] . ",
						theme='" . $_POST['Theme'] . "',
						language ='" . $_POST['UserLanguage'] . "',
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
						salesman,
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
						'" . $_POST['RealName'] ."',
						'" . $_POST['Cust'] ."',
						'" . $_POST['BranchCode'] ."',
						'" . $_POST['Salesman'] . "',
						'" . CryptPass($_POST['Password']) ."',
						'" . $_POST['Phone'] . "',
						'" . $_POST['Email'] ."',
						'" . $_POST['PageSize'] ."',
						" . $_POST['Access'] . ",
						'" . $_POST['DefaultLocation'] ."',
						'" . $ModulesAllowed . "',
						" . $_SESSION['DefaultDisplayRecordsMax'] . ",
						'" . $_POST['Theme'] . "',
						'". $_POST['UserLanguage'] ."')";
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
		unset($_POST['Salesman']);
		unset($_POST['Phone']);
		unset($_POST['Email']);
		unset($_POST['Password']);
		unset($_POST['PageSize']);
		unset($_POST['Access']);
		unset($_POST['DefaultLocation']);
		unset($_POST['ModulesAllowed']);
		unset($_POST['Blocked']);
		unset($_POST['Theme']);
		unset($_POST['UserLanguage']);
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
		$sql='SELECT userid FROM audittrail where userid="'. $SelectedUser .'"';
		$result=DB_query($sql, $db);
		if (DB_num_rows($result)!=0) {
			prnMsg(_('Cannot delete user as entries already exist in the audit trail'), 'warn');
		} else {

			$sql="DELETE FROM www_users WHERE userid='$SelectedUser'";
			$ErrMsg = _('The User could not be deleted because');;
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('User Deleted'),'info');
		}
		unset($SelectedUser);
	// }

}

if (!isset($SelectedUser)) {

/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = 'SELECT 
			userid,
			realname,
			phone,
			email,
			customerid,
			branchcode,
			salesman,
			lastvisitdate,
			fullaccess,
			pagesize,
			theme,
			language
		FROM www_users';
	$result = DB_query($sql,$db);

	echo '<table border=1>';
	echo "<tr><th>" . _('User Login') . "</th>
		<th>" . _('Full Name') . "</th>
		<th>" . _('Telephone') . "</th>
		<th>" . _('Email') . "</th>
		<th>" . _('Customer Code') . "</th>
		<th>" . _('Branch Code') . "</th>
		<th>" . _('Salesperson') . "</th>
		<th>" . _('Last Visit') . "</th>
		<th>" . _('Security Role') ."</th>
		<th>" . _('Report Size') ."</th>
		<th>" . _('Theme') ."</th>
		<th>" . _('Language') ."</th>
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
					$myrow[6],
					$LastVisitDate,
					$SecurityRoles[($myrow[8])],
					$myrow[9],
					$myrow[10],
					$myrow[11],
					$_SERVER['PHP_SELF']  . "?" . SID,
					$myrow[0],
					$_SERVER['PHP_SELF'] . "?" . SID,
					$myrow[0]);

	} //END WHILE LIST LOOP
	echo '</table><br>';
} //end of ifs and buts!


if (isset($SelectedUser)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Review Existing Users') . '</a></div><br>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

if (isset($SelectedUser)) {
	//editing an existing User

	$sql = "SELECT userid,
			realname,
			phone,
			email,
			customerid,
			password,
			branchcode,
			salesman,
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
	$_POST['Salesman'] = $myrow['salesman'];
	$_POST['PageSize'] = $myrow['pagesize'];
	$_POST['Access'] = $myrow['fullaccess'];
	$_POST['DefaultLocation'] = $myrow['defaultlocation'];
	$_POST['ModulesAllowed'] = $myrow['modulesallowed'];
	$_POST['Theme'] = $myrow['theme'];
	$_POST['UserLanguage'] = $myrow['language'];
	$_POST['Blocked'] = $myrow['blocked'];
	
	echo "<input type='hidden' name='SelectedUser' value='" . $SelectedUser . "'>";
	echo "<input type='hidden' name='UserID' value='" . $_POST['UserID'] . "'>";
	echo "<input type='hidden' name='ModulesAllowed' value='" . $_POST['ModulesAllowed'] . "'>";

	echo '<table> <tr><td>' . _('User code') . ':</td><td>';
	echo $_POST['UserID'] . '</td></tr>';

} else { //end of if $SelectedUser only do the else when a new record is being entered

	echo '<table><tr><td>' . _('User Login') . ":</td><td><input type='text' name='UserID' size=22 maxlength=20 ></td></tr>";

	/*set the default modules to show to all
	this had trapped a few people previously*/
	$i=0;
	if (!isset($_POST['ModulesAllowed'])) {
		$_POST['ModulesAllowed']='';
	}
	foreach($ModuleList as $ModuleName){
		if ($i>0){
			$_POST['ModulesAllowed'] .=',';
		}
		$_POST['ModulesAllowed'] .= '1';
		$i++;
	}
}

if (!isset($_POST['Password'])) {
	$_POST['Password']='';
}
if (!isset($_POST['RealName'])) {
	$_POST['RealName']='';
}
if (!isset($_POST['Phone'])) {
	$_POST['Phone']='';
}
if (!isset($_POST['Email'])) {
	$_POST['Email']='';
}
echo '<tr><td>' . _('Password') . ":</td>
	<td><input type='password' name='Password' size=22 maxlength=20 value='" . $_POST['Password'] . "'></tr>";
echo '<tr><td>' . _('Full Name') . ":</td>
	<td><input type='text' name='RealName' value='" . $_POST['RealName'] . "' size=36 maxlength=35></td></tr>";
echo '<tr><td>' . _('Telephone No') . ":</td>
	<td><input type='text' name='Phone' value='" . $_POST['Phone'] . "' size=32 maxlength=30></td></tr>";
echo '<tr><td>' . _('Email Address') .":</td>
	<td><input type='text' name='Email' value='" . $_POST['Email'] ."' size=32 maxlength=55></td></tr>";
echo '<tr><td>' . _('Security Role') . ":</td><td><select name='Access'>";

foreach ($SecurityRoles as $SecKey => $SecVal) {
	if (isset($_POST['Access']) and $SecKey == $_POST['Access']){
		echo "<option selected value=" . $SecKey . ">" . $SecVal;
	} else {
		echo "<option value=" . $SecKey . ">" . $SecVal;
	}
}
echo '</select></td></tr>';
echo '<input type="hidden" name="ID" value="'.$_SESSION['UserID'].'">';

echo '<tr><td>' . _('Default Location') . ':</td>
	<td><select name="DefaultLocation">';

$sql = 'SELECT loccode, locationname FROM locations';
$result = DB_query($sql,$db);

while ($myrow=DB_fetch_array($result)){

	if (isset($_POST['DefaultLocation']) and $myrow['loccode'] == $_POST['DefaultLocation']){

		echo "<option selected value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];

	} else {
		echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];

	}

}

echo '</select></td></tr>';
if (!isset($_POST['Cust'])) {
	$_POST['Cust']='';
}
if (!isset($_POST['BranchCode'])) {
	$_POST['BranchCode']='';
}
echo '<tr><td>' . _('Customer Code') . ':</td>
	<td><input type="text" name="Cust" size=10 maxlength=8 value="' . $_POST['Cust'] . '"></td></tr>';

echo '<tr><td>' . _('Branch Code') . ':</td>
	<td><input type="text" name="BranchCode" size=10 maxlength=8 VALUE="' . $_POST['BranchCode'] .'"></td></tr>';

echo '<tr><td>' . _('Restrict to Sales Person') . ':</td>
	<td><select name="Salesman">';

$sql = 'SELECT salesmancode, salesmanname FROM salesman';
$result = DB_query($sql,$db);
if ((isset($_POST['Salesman']) and $_POST['Salesman']=='') OR !isset($_POST['Salesman'])){
	echo '<option selected value="">' .  _('Not a salesperson only login') . '</option>';
} else {
	echo '<option value="">' . _('Not a salesperson only login') . '</option>';
}
while ($myrow=DB_fetch_array($result)){
	
	if (isset($_POST['Salesman']) and $myrow['salesmancode'] == $_POST['Salesman']){
		echo '<option selected value="' . $myrow['salesmancode'] . '">' . $myrow['salesmanname'] . '</option>';
	} else {
		echo '<option value="' . $myrow['salesmancode'] . '">' . $myrow['salesmanname'] . '</option>';
	}

}

echo '</select></td></tr>';


echo '<tr><td>' . _('Reports Page Size') .":</td>
	<td><select name='PageSize'>";

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A4'){
	echo "<option selected value='A4'>" . _('A4');
} else {
	echo "<option value='A4'>A4";
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3'){
	echo "<option selected Value='A3'>" . _('A3');
} else {
	echo "<option value='A3'>A3";
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3_landscape'){
	echo "<option selected Value='A3_landscape'>" . _('A3') . ' ' . _('landscape');
} else {
	echo "<option value='A3_landscape'>" . _('A3') . ' ' . _('landscape');
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter'){
	echo "<option selected Value='letter'>" . _('Letter');
} else {
	echo "<option value='letter'>" . _('Letter');
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter_landscape'){
	echo "<option selected Value='letter_landscape'>" . _('Letter') . ' ' . _('landscape');
} else {
	echo "<option value='letter_landscape'>" . _('Letter') . ' ' . _('landscape');
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal'){
	echo "<option selected value='legal'>" . _('Legal');
} else {
	echo "<option Value='legal'>" . _('Legal');
}
if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal_landscape'){
	echo "<option selected value='legal_landscape'>" . _('Legal') . ' ' . _('landscape');
} else {
	echo "<option value='legal_landscape'>" . _('Legal') . ' ' . _('landscape');
}

echo '</select></td></tr>';

echo '<tr>
	<td>' . _('Theme') . ":</td>
	<td><select name='Theme'>";

$ThemeDirectory = dir('css/');


while (false != ($ThemeName = $ThemeDirectory->read())){

	if (is_dir("css/$ThemeName") AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != 'CVS'){

		if (isset($_POST['Theme']) and $_POST['Theme'] == $ThemeName){
			echo "<option selected value='$ThemeName'>$ThemeName";
		} else if (!isset($_POST['Theme']) and ($_SESSION['DefaultTheme']==$ThemeName)) {
			echo "<option selected value='$ThemeName'>$ThemeName";
		} else {
			echo "<option value='$ThemeName'>$ThemeName";
		}
	}
}

echo '</select></td></tr>';


echo '<tr>
	<td>' . _('Language') . ":</td>
	<td><select name='UserLanguage'>";

 $LangDirHandle = dir('locale/');


while (false != ($LanguageEntry = $LangDirHandle->read())){

	if (is_dir('locale/' . $LanguageEntry) AND $LanguageEntry != '..' AND $LanguageEntry != 'CVS' AND $LanguageEntry!='.'){

		if (isset($_POST['UserLanguage']) and $_POST['UserLanguage'] == $LanguageEntry){
			echo "<option selected value='$LanguageEntry'>$LanguageEntry";
		} elseif (!isset($_POST['UserLanguage']) and $LanguageEntry == $DefaultLanguage) {
			echo "<option selected value='$LanguageEntry'>$LanguageEntry";
		} else {
			echo "<option value='$LanguageEntry'>$LanguageEntry";			
		}
	}
}

echo '</select></td></tr>';


/*Make an array out of the comma separated list of modules allowed*/
$ModulesAllowed = explode(',',$_POST['ModulesAllowed']);

$i=0;
foreach($ModuleList as $ModuleName){

	echo '<tr><td>' . _('Display') . ' ' . $ModuleName . ' ' . _('options') . ": </td><td><select name='Module_" . $i . "'>";
	if ($ModulesAllowed[$i]==0){
		echo '<option selected value=0>' . _('No');
		echo '<option value=1>' . _('Yes');
	} else {
	 	echo '<option selected value=1>' . _('Yes');
		echo '<option value=0>' . _('No');
	}
	echo '</select></td></tr>';
	$i++;
}

echo '<tr><td>' . _('Account Status') . ":</td><td><select name='Blocked'>";
if ($_POST['Blocked']==0){
	echo '<option selected value=0>' . _('Open');
	echo '<option value=1>' . _('Blocked');
} else {
 	echo '<option selected value=1>' . _('Blocked');
	echo '<option value=0>' . _('Open');
}
echo '</select></td></tr>';

echo '</table><br>
	<div class="centre"><input type="submit" name="submit" value="' . _('Enter Information') . '"></div>
	</form>';

if (isset($_GET['SelectedUser'])) {
	echo '<script  type="text/javascript">defaultControl(document.forms[0].Password);</script>';
} else {
	echo '<script  type="text/javascript">defaultControl(document.forms[0].UserID);</script>';
}
include('includes/footer.inc');
?>
