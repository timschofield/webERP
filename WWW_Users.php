<?php

/* $Id$*/

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
					_('Asset Manager'),
					_('Petty Cash'),
					_('Setup'),
					_('Utilities')
					);

$PDFLanguages = array(_('Latin Western Languages'),
						_('Eastern European Russian Japanese Korean Vietnamese Hebrew Arabic Thai'),
						_('Chinese'));

$title = _('User Maintenance');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/group_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $title.'</p>
	<br />';

// Make an array of the security roles
$sql = "SELECT secroleid,
				secrolename
		FROM securityroles
		ORDER BY secrolename";

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
	if (mb_strlen($_POST['UserID'])<4){
		$InputError = 1;
		prnMsg(_('The user ID entered must be at least 4 characters long'),'error');
	} elseif (ContainsIllegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(_('User names cannot contain any of the following characters') . " - ' & + \" \\ " . _('or a space'),'error');
	} elseif (mb_strlen($_POST['Password'])<5){
		if (!$SelectedUser){
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'),'error');
		}
	} elseif (mb_strstr($_POST['Password'],$_POST['UserID'])!= False){
		$InputError = 1;
		prnMsg(_('The password cannot contain the user id'),'error');
	} elseif ((mb_strlen($_POST['Cust'])>0)
				AND (mb_strlen($_POST['BranchCode'])==0)) {
		$InputError = 1;
		prnMsg(_('If you enter a Customer Code you must also enter a Branch Code valid for this Customer'),'error');
	}
	//comment out except for demo!  Do not want anyone modifying demo user.
	/*
	  elseif ($_POST['UserID'] == 'admin') {
		prnMsg(_('The demonstration user called demo cannot be modified.'),'error');
		$InputError = 1;
	}
	*/
	if (!isset($SelectedUser)){
		/* check to ensure the user id is not already entered */
		$result = DB_query("SELECT userid FROM www_users WHERE userid='" . $_POST['UserID'] . "'",$db);
		if (DB_num_rows($result)==1){
			$InputError =1;
			prnMsg(_('The user ID') . ' ' . $_POST['UserID'] . ' ' . _('already exists and cannot be used again'),'error');
		}
	}

	if ((mb_strlen($_POST['BranchCode'])>0) AND ($InputError !=1)) {
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
		$FormVbl = 'Module_' . $i;
		$ModulesAllowed .= $_POST[($FormVbl)] . ',';
		$i++;
	}
	$_POST['ModulesAllowed']= $ModulesAllowed;

	if (isset($SelectedUser) AND $InputError !=1) {

/*SelectedUser could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		if (!isset($_POST['Cust'])
			OR $_POST['Cust']==NULL
			OR $_POST['Cust']==''){

			$_POST['Cust']='';
			$_POST['BranchCode']='';
		}
		$UpdatePassword = '';
		if ($_POST['Password'] != ''){
			$UpdatePassword = "password='" . CryptPass($_POST['Password']) . "',";
		}

		$sql = "UPDATE www_users SET realname='" . $_POST['RealName'] . "',
						customerid='" . $_POST['Cust'] ."',
						phone='" . $_POST['Phone'] ."',
						email='" . $_POST['Email'] ."',
						" . $UpdatePassword . "
						branchcode='" . $_POST['BranchCode'] . "',
						supplierid='" . $_POST['SupplierID'] . "',
						salesman='" . $_POST['Salesman'] . "',
						pagesize='" . $_POST['PageSize'] . "',
						fullaccess='" . $_POST['Access'] . "',
						cancreatetender='" . $_POST['CanCreateTender'] . "',
						theme='" . $_POST['Theme'] . "',
						language ='" . $_POST['UserLanguage'] . "',
						defaultlocation='" . $_POST['DefaultLocation'] ."',
						modulesallowed='" . $ModulesAllowed . "',
						blocked='" . $_POST['Blocked'] . "',
						pdflanguage='" . $_POST['PDFLanguage'] . "'
					WHERE userid = '". $SelectedUser . "'";

		prnMsg( _('The selected user record has been updated'), 'success' );
	} elseif ($InputError !=1) {

		$sql = "INSERT INTO www_users (userid,
						realname,
						customerid,
						branchcode,
						supplierid,
						salesman,
						password,
						phone,
						email,
						pagesize,
						fullaccess,
						cancreatetender,
						defaultlocation,
						modulesallowed,
						displayrecordsmax,
						theme,
						language,
						pdflanguage)
					VALUES ('" . $_POST['UserID'] . "',
						'" . $_POST['RealName'] ."',
						'" . $_POST['Cust'] ."',
						'" . $_POST['BranchCode'] ."',
						'" . $_POST['SupplierID'] ."',
						'" . $_POST['Salesman'] . "',
						'" . CryptPass($_POST['Password']) ."',
						'" . $_POST['Phone'] . "',
						'" . $_POST['Email'] ."',
						'" . $_POST['PageSize'] ."',
						'" . $_POST['Access'] . "',
						'" . $_POST['CanCreateTender'] . "',
						'" . $_POST['DefaultLocation'] ."',
						'" . $ModulesAllowed . "',
						'" . $_SESSION['DefaultDisplayRecordsMax'] . "',
						'" . $_POST['Theme'] . "',
						'". $_POST['UserLanguage'] ."',
						'" . $_POST['PDFLanguage'] . "')";
		prnMsg( _('A new user record has been inserted'), 'success' );
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
		unset($_POST['SupplierID']);
		unset($_POST['Salesman']);
		unset($_POST['Phone']);
		unset($_POST['Email']);
		unset($_POST['Password']);
		unset($_POST['PageSize']);
		unset($_POST['Access']);
		unset($_POST['CanCreateTender']);
		unset($_POST['DefaultLocation']);
		unset($_POST['ModulesAllowed']);
		unset($_POST['Blocked']);
		unset($_POST['Theme']);
		unset($_POST['UserLanguage']);
		unset($_POST['PDFLanguage']);
		unset($SelectedUser);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	// comment out except for demo!  Do not want anyopne deleting demo user.
	/*
	if ($SelectedUser == 'admin') {
		prnMsg(_('The demonstration user called demo cannot be deleted'),'error');
	} else {
	*/
		$sql="SELECT userid FROM audittrail where userid='" . $SelectedUser ."'";
		$result=DB_query($sql, $db);
		if (DB_num_rows($result)!=0) {
			prnMsg(_('Cannot delete user as entries already exist in the audit trail'), 'warn');
		} else {

			$sql="DELETE FROM www_users WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The User could not be deleted because');;
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('User Deleted'),'info');
		}
		unset($SelectedUser);
	// }

}

if (!isset($SelectedUser)) {

/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT userid,
					realname,
					phone,
					email,
					customerid,
					branchcode,
					supplierid,
					salesman,
					lastvisitdate,
					fullaccess,
					cancreatetender,
					pagesize,
					theme,
					language
				FROM www_users";
	$result = DB_query($sql,$db);

	echo '<table class="selection">';
	echo '<tr><th>' . _('User Login') . '</th>
				<th>' . _('Full Name') . '</th>
				<th>' . _('Telephone') . '</th>
				<th>' . _('Email') . '</th>
				<th>' . _('Customer Code') . '</th>
				<th>' . _('Branch Code') . '</th>
				<th>' . _('Supplier Code') . '</th>
				<th>' . _('Salesperson') . '</th>
				<th>' . _('Last Visit') . '</th>
				<th>' . _('Security Role') .'</th>
				<th>' . _('Report Size') .'</th>
				<th>' . _('Theme') .'</th>
				<th>' . _('Language') .'</th>
			</tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

	if ($myrow[8]=='') {
		$LastVisitDate = Date($_SESSION['DefaultDateFormat']);
	} else {
		$LastVisitDate = ConvertSQLDate($myrow[8]);
	}

		/*The SecurityHeadings array is defined in config.php */

		printf('<td>%s</td>
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
					<td>%s</td>
					<td><a href="%s&SelectedUser=%s">' . _('Edit') . '</a></td>
					<td><a href="%s&SelectedUser=%s&delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this user?') . '\');">' . _('Delete') . '</a></td>
					</tr>',
					$myrow['userid'],
					$myrow['realname'],
					$myrow['phone'],
					$myrow['email'],
					$myrow['customerid'],
					$myrow['branchcode'],
					$myrow['supplierid'],
					$myrow['salesman'],
					$LastVisitDate,
					$SecurityRoles[($myrow['fullaccess'])],
					$myrow['pagesize'],
					$myrow['theme'],
					$LanguagesArray[$myrow['language']]['LanguageName'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '?',
					$myrow['userid'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
					$myrow['userid']);

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!


if (isset($SelectedUser)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '">' . _('Review Existing Users') . '</a></div><br />';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedUser)) {
	//editing an existing User

	$sql = "SELECT userid,
			realname,
			phone,
			email,
			customerid,
			password,
			branchcode,
			supplierid,
			salesman,
			pagesize,
			fullaccess,
			cancreatetender,
			defaultlocation,
			modulesallowed,
			blocked,
			theme,
			language,
			pdflanguage
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
	$_POST['SupplierID'] = $myrow['supplierid'];
	$_POST['Salesman'] = $myrow['salesman'];
	$_POST['PageSize'] = $myrow['pagesize'];
	$_POST['Access'] = $myrow['fullaccess'];
	$_POST['CanCreateTender'] = $myrow['cancreatetender'];
	$_POST['DefaultLocation'] = $myrow['defaultlocation'];
	$_POST['ModulesAllowed'] = $myrow['modulesallowed'];
	$_POST['Theme'] = $myrow['theme'];
	$_POST['UserLanguage'] = $myrow['language'];
	$_POST['Blocked'] = $myrow['blocked'];
	$_POST['PDFLanguage'] = $myrow['pdflanguage'];

	echo '<input type="hidden" name="SelectedUser" value="' . $SelectedUser . '" />';
	echo '<input type="hidden" name="UserID" value="' . $_POST['UserID'] . '" />';
	echo '<input type="hidden" name="ModulesAllowed" value="' . $_POST['ModulesAllowed'] . '" />';

	echo '<table class="selection">
			<tr>
				<td>' . _('User code') . ':</td>
				<td>' . $_POST['UserID'] . '</td>
			</tr>';

} else { //end of if $SelectedUser only do the else when a new record is being entered

	echo '<table class="selection">
			<tr>
				<td>' . _('User Login') . ':</td>
				<td><input type="text" name="UserID" size="22" maxlength="20" /></td>
			</tr>';

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
echo '<tr>
		<td>' . _('Password') . ':</td>
		<td><input type="password" name="Password" size="22" maxlength="20" value="' . $_POST['Password'] . '" />
	</tr>';
echo '<tr>
		<td>' . _('Full Name') . ':</td>
		<td><input type="text" name="RealName" value="' . $_POST['RealName'] . '" size="36" maxlength="35" /></td>
	</tr>';
echo '<tr>
		<td>' . _('Telephone No') . ':</td>
		<td><input type="text" name="Phone" value="' . $_POST['Phone'] . '" size="32" maxlength="30" /></td>
	</tr>';
echo '<tr>
		<td>' . _('Email Address') .':</td>
		<td><input type="text" name="Email" value="' . $_POST['Email'] .'" size="32" maxlength="55" /></td>
	</tr>';
echo '<tr>
		<td>' . _('Security Role') . ':</td>
		<td><select name="Access">';

foreach ($SecurityRoles as $SecKey => $SecVal) {
	if (isset($_POST['Access']) and $SecKey == $_POST['Access']){
		echo '<option selected="selected" value="' . $SecKey . '">' . $SecVal .'</option>';
	} else {
		echo '<option value="' . $SecKey . '">' . $SecVal .'</option>';
	}
}
echo '</select></td>
	</tr>';
echo '<input type="hidden" name="ID" value="'.$_SESSION['UserID'].'" />';

echo '<tr><td>' . _('User Can Create Tenders') . ':</td><td><select name="CanCreateTender">';

if ($_POST['CanCreateTender']==0){
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
} else {
 	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
}
echo '</select></td></tr>';

echo '<tr>
		<td>' . _('Default Location') . ':</td>
		<td><select name="DefaultLocation">';

$sql = "SELECT loccode, locationname FROM locations";
$result = DB_query($sql,$db);

while ($myrow=DB_fetch_array($result)){
	if (isset($_POST['DefaultLocation']) AND $myrow['loccode'] == $_POST['DefaultLocation']){
		echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] .'</option>';
	} else {
		echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] .'</option>';
	}
}

echo '</select></td>
	</tr>';

if (!isset($_POST['Cust'])) {
	$_POST['Cust']='';
}
if (!isset($_POST['BranchCode'])) {
	$_POST['BranchCode']='';
}
if (!isset($_POST['SupplierID'])) {
	$_POST['SupplierID']='';
}
echo '<tr>
		<td>' . _('Customer Code') . ':</td>
		<td><input type="text" name="Cust" size="10" maxlength="10" value="' . $_POST['Cust'] . '" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Branch Code') . ':</td>
		<td><input type="text" name="BranchCode" size="10" maxlength="10" value="' . $_POST['BranchCode'] .'" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Supplier Code') . ':</td>
		<td><input type="text" name="SupplierID" size="10" maxlength="10" value="' . $_POST['SupplierID'] .'" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Restrict to Sales Person') . ':</td>
		<td><select name="Salesman">';

$sql = "SELECT salesmancode, salesmanname FROM salesman WHERE current = 1";
$result = DB_query($sql,$db);
if ((isset($_POST['Salesman']) AND $_POST['Salesman']=='') OR !isset($_POST['Salesman'])){
	echo '<option selected="selected" value="">' .  _('Not a salesperson only login') . '</option>';
} else {
	echo '<option value="">' . _('Not a salesperson only login') . '</option>';
}
while ($myrow=DB_fetch_array($result)){

	if (isset($_POST['Salesman']) AND $myrow['salesmancode'] == $_POST['Salesman']){
		echo '<option selected="selected" value="' . $myrow['salesmancode'] . '">' . $myrow['salesmanname'] . '</option>';
	} else {
		echo '<option value="' . $myrow['salesmancode'] . '">' . $myrow['salesmanname'] . '</option>';
	}

}

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Reports Page Size') .':</td>
		<td><select name="PageSize">';

if(isset($_POST['PageSize']) AND $_POST['PageSize']=='A4'){
	echo '<option selected="selected" value="A4">' . _('A4') .'</option>';
} else {
	echo '<option value="A4">' . _('A4') . '</option>';
}

if(isset($_POST['PageSize']) AND $_POST['PageSize']=='A3'){
	echo '<option selected="selected" value="A3">' . _('A3') .'</option>';
} else {
	echo '<option value="A3">' . _('A3') .'</option>';
}

if(isset($_POST['PageSize']) AND $_POST['PageSize']=='A3_landscape'){
	echo '<option selected="selected" value="A3_landscape">' . _('A3') . ' ' . _('landscape') .'</option>';
} else {
	echo '<option value="A3_landscape">' . _('A3') . ' ' . _('landscape') .'</option>';
}

if(isset($_POST['PageSize']) AND $_POST['PageSize']=='letter'){
	echo '<option selected="selected" value="letter">' . _('Letter') .'</option>';
} else {
	echo '<option value="letter">' . _('Letter') .'</option>';
}

if(isset($_POST['PageSize']) AND $_POST['PageSize']=='letter_landscape'){
	echo '<option selected="selected" value="letter_landscape">' . _('Letter') . ' ' . _('landscape') .'</option>';
} else {
	echo '<option value="letter_landscape">' . _('Letter') . ' ' . _('landscape') .'</option>';
}

if(isset($_POST['PageSize']) AND $_POST['PageSize']=='legal'){
	echo '<option selected="selected" value="legal">' . _('Legal') .'</option>';
} else {
	echo '<option value="legal">' . _('Legal') .'</option>';
}
if(isset($_POST['PageSize']) AND $_POST['PageSize']=='legal_landscape'){
	echo '<option selected="selected" value="legal_landscape">' . _('Legal') . ' ' . _('landscape') .'</option>';
} else {
	echo '<option value="legal_landscape">' . _('Legal') . ' ' . _('landscape') .'</option>';
}

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Theme') . ':</td>
		<td><select name="Theme">';

$ThemeDirectory = dir('css/');


while (false != ($ThemeName = $ThemeDirectory->read())){

	if (is_dir('css/' . $ThemeName) AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != '.svn'){

		if (isset($_POST['Theme']) AND $_POST['Theme'] == $ThemeName){
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName .'</option>';
		} else if (!isset($_POST['Theme']) AND ($_SESSION['DefaultTheme']==$ThemeName)) {
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName .'</option>';
		} else {
			echo '<option value="' . $ThemeName . '">' . $ThemeName .'</option>';
		}
	}
}

echo '</select></td>
	</tr>';


echo '<tr>
		<td>' . _('Language') . ':</td>
		<td><select name="UserLanguage">';

foreach ($LanguagesArray as $LanguageEntry => $LanguageName){
	if (isset($_POST['UserLanguage']) AND $_POST['UserLanguage'] == $LanguageEntry){
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName'] .'</option>';
	} elseif (!isset($_POST['UserLanguage']) AND $LanguageEntry == $DefaultLanguage) {
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName'] .'</option>';
	} else {
		echo '<option value="' . $LanguageEntry . '">' . $LanguageName['LanguageName'] .'</option>';
	}
}
echo '</select></td>
	</tr>';

/*Make an array out of the comma separated list of modules allowed*/
$ModulesAllowed = explode(',',$_POST['ModulesAllowed']);

$i=0;
foreach($ModuleList as $ModuleName){

	echo '<tr>
			<td>' . _('Display') . ' ' . $ModuleName . ' ' . _('module') . ': </td>
			<td><select name="Module_' . $i . '">';
	if ($ModulesAllowed[$i]==0){
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
		echo '<option value="1">' . _('Yes') . '</option>';
	} else {
	 	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td>
		</tr>';
	$i++;
}
if (!isset($_POST['PDFLanguage'])){
	$_POST['PDFLanguage']=0;
}

echo '<tr>
		<td>' . _('PDF Language Support') . ': </td>
		<td><select name="PDFLanguage">';
for($i=0;$i<count($PDFLanguages);$i++){
	if ($_POST['PDFLanguage']==$i){
		echo '<option selected="selected" value="' . $i .'">' . $PDFLanguages[$i] . '</option>';
	} else {
		echo '<option value="' . $i .'">' . $PDFLanguages[$i]. '</option>';
	}
}
echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Account Status') . ':</td>
		<td><select name="Blocked">';
if ($_POST['Blocked']==0){
	echo '<option selected="selected" value="0">' . _('Open') . '</option>';
	echo '<option value="1">' . _('Blocked') . '</option>';
} else {
 	echo '<option selected="selected" value="1">' . _('Blocked') . '</option>';
	echo '<option value="0">' . _('Open') . '</option>';
}
echo '</select></td>
	</tr>';

echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>
	</form>';

if (isset($_GET['SelectedUser'])) {
	echo '<script  type="text/javascript">defaultControl(document.forms[0].Password);</script>';
} else {
	echo '<script  type="text/javascript">defaultControl(document.forms[0].UserID);</script>';
}
include('includes/footer.inc');
?>