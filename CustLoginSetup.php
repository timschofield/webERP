<?php

/* $Id$*/

include('includes/session.inc');
$title = _('Customer Login Configuration');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include ('includes/LanguagesArray.php');

echo '<a href="' . $rootpath . '/SelectCustomer.php">' . _('Back to Customers') . '</a><br />';

$sql="SELECT name
		FROM debtorsmaster
		WHERE debtorno='".$_SESSION['CustomerID']."'";

$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$CustomerName=$myrow['name'];

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' . _('Customer') .
	'" alt="" />' . ' ' . _('Customer') . ' : ' . $_SESSION['CustomerID'] . ' - ' . $CustomerName. _(' has been selected') .
		'</p><br />';

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
	if (mb_strlen($_POST['UserID'])<3){
		$InputError = 1;
		prnMsg(_('The user ID entered must be at least 4 characters long'),'error');
	} elseif (ContainsIllegalCharacters($_POST['UserID']) OR mb_strstr($_POST['UserID'],' ')) {
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
	} elseif ((mb_strlen($_POST['Cust'])>0) AND (mb_strlen($_POST['BranchCode'])==0)) {
		$InputError = 1;
		prnMsg(_('If you enter a Customer Code you must also enter a Branch Code valid for this Customer'),'error');
	}
	
	if ((mb_strlen($_POST['BranchCode'])>0) AND ($InputError !=1)) {
		// check that the entered branch is valid for the customer code
		$sql = "SELECT defaultlocation
				FROM custbranch
				WHERE debtorno='" . $_POST['Cust'] . "'
				AND branchcode='" . $_POST['BranchCode'] . "'";

		$ErrMsg = _('The check on validity of the customer code and branch failed because');
		$DbgMsg = _('The SQL that was used to check the customer code and branch was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		if (DB_num_rows($result)==0){
			prnMsg(_('The entered Branch Code is not valid for the entered Customer Code'),'error');
			$InputError = 1;
		} else {
			$myrow = DB_fetch_row($result);
			$InventoryLocation = $myrow[0];
	}
	
	if (isset($SelectedUser) AND $InputError !=1) {

		$UpdatePassword = '';
		if ($_POST['Password'] != ""){
			$UpdatePassword = "password='" . CryptPass($_POST['Password']) . "',";
		}

		$sql = "UPDATE www_users SET realname='" . $_POST['RealName'] . "',
						phone='" . $_POST['Phone'] ."',
						email='" . $_POST['Email'] ."',
						".$UpdatePassword."
						branchcode='" . $_POST['BranchCode'] . "',
						pagesize='" . $_POST['PageSize'] . "',
						theme='" . $_POST['Theme'] . "',
						language ='" . $_POST['UserLanguage'] . "',
						defaultlocation='" . $InventoryLocation ."',
						blocked='" . $_POST['Blocked'] . "'
					WHERE userid = '".$SelectedUser."'";

		prnMsg( _('The selected user record has been updated'), 'success' );
			
			
		} else { //no selected user so it's an insert of new user
			
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
											'" . $_POST['RealName'] ."',
											'" . $_POST['Cust'] ."',
											'" . $_POST['BranchCode'] ."',
											'" . CryptPass($_POST['Password']) ."',
											'" . $_POST['Phone'] . "',
											'" . $_POST['Email'] ."',
											'" . $_POST['PageSize'] ."',
											'7',
											'" . $InventoryLocation ."',
											'1,1,0,0,0,0,0,0',
											'" . $_SESSION['DefaultDisplayRecordsMax'] . "',
											'" . $_POST['Theme'] . "',
											'". $_POST['UserLanguage'] ."')";
			prnMsg( _('A new user record has been inserted'), 'success' );
		}
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
		unset($_POST['Theme']);
		unset($_POST['UserLanguage']);
		unset($_POST['Blocked']);
		unset($SelectedUser);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

		$sql="SELECT userid FROM audittrail where userid='". $SelectedUser ."'";
		$result=DB_query($sql, $db);
		if (DB_num_rows($result)!=0) {
			prnMsg(_('Cannot delete user as entries already exist in the audit trail'), 'warn');
		} else {

			$sql="DELETE FROM www_users WHERE userid='".$SelectedUser."'";
			$ErrMsg = _('The User could not be deleted because');;
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('User Deleted'),'info');
		}
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
					pagesize,
					theme,
					language
			FROM www_users WHERE customerid = '" . $_SESSION['CustomerID'] . "'";
	$result = DB_query($sql,$db);
	
	echo '<table class="selection>"';
	
	echo '<tr><th>' . _('User Login') . '</th>
			<th>' . _('Full Name') . '</th>
			<th>' . _('Telephone') . '</th>
			<th>' . _('Email') . '</th>
			<th>' . _('Last Visit') . '</th>
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

		printf('<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href="%s&SelectedUser=%s">' . _('Edit') . '</a></td>
			<td><a href="%s&SelectedUser=%s&delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this user login?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['userid'],
			$myrow['realname'],
			$myrow['phone'],
			$myrow['email'],
			$LastVisitDate,
			$myrow['pagesize'],
			$myrow['theme'],
			$LanguagesArray[$myrow['language']],
			$_SERVER['PHP_SELF']  . '?',
			$myrow['userid'],
			$_SERVER['PHP_SELF'] . '?',
			$myrow['userid']);

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of if there is no selected user


if (isset($SelectedUser)) {
	echo '<div class="centre"><a href="' . $_SERVER['PHP_SELF'] .'">' . _('Review Existing Users') . '</a></div><br />';
}
echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
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
				pagesize,
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
	$_POST['PageSize'] = $myrow['pagesize'];
	$_POST['Theme'] = $myrow['theme'];
	$_POST['UserLanguage'] = $myrow['language'];
	
	echo '<input type="hidden" name="SelectedUser" value="' . $SelectedUser . '">';
	echo '<input type="hidden" name="UserID" value="' . $_POST['UserID'] . '">';

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

echo '<tr><td>' . _('Password') . ':</td>
	<td><input type="password" name="Password" size=22 maxlength=20 value="' . $_POST['Password'] . '"></tr>';
echo '<tr><td>' . _('Full Name') . ':</td>
	<td><input type="text" name="RealName" value="' . $_POST['RealName'] . '" size=36 maxlength=35></td></tr>';
echo '<tr><td>' . _('Telephone No') . ':</td>
	<td><input type="text" name="Phone" value="' . $_POST['Phone'] . '" size=32 maxlength=30></td></tr>';
echo '<tr><td>' . _('Email Address') .':</td>
	<td><input type="text" name="Email" value="' . $_POST['Email'] .'" size=32 maxlength=55></td></tr>';
echo '<input type="hidden" name="Access" value="1">';


//Customer is fixed by selection of customer
$_POST['Cust']=$_SESSION['CustomerID'];
echo '<input type="hidden" name="Cust" value="' . $_POST['Cust'] . '">';
echo '<tr><td>'._('Customer Code').':</td>
	<td>' . $_POST['Cust'] . '</td></tr>';

echo '<tr><td>' . _('Branch Code') . ':</td>
	<td><select name="BranchCode">';

$sql = "SELECT branchcode FROM custbranch WHERE debtorno = '" . $_POST['Cust'] . "'";
$result = DB_query($sql,$db);

while ($myrow=DB_fetch_array($result)){

	//Set the first available branch as default value when nothing is selected
	if (!isset($_POST['BranchCode'])) {
		$_POST['BranchCode']= $myrow['branchcode'];
	}

	if (isset($_POST['BranchCode']) and $myrow['branchcode'] == $_POST['BranchCode']){
		echo '<option selected value="' . $myrow['branchcode'] . '">' . $myrow['branchcode'] . '</option>';
	} else {
		echo '<option Value="' . $myrow['branchcode'] . '">' . $myrow['branchcode'] . '</option>';
	}
}

echo '<tr><td>' . _('Reports Page Size') .':</td>
	<td><select name="PageSize">';

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A4'){
	echo '<option selected value="A4">' . _('A4') .'</option>';
} else {
	echo '<option value="A4">' . _('A4') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3'){
	echo '<option selected value="A3">' . _('A3') .'</option>';
} else {
	echo '<option value="A3">' . _('A3') .'</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3_landscape'){
	echo '<option selected value="A3_landscape">' . _('A3') . ' ' . _('landscape') .'</option>';
} else {
	echo '<option value="A3_landscape">' . _('A3') . ' ' . _('landscape') .'</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter'){
	echo '<option selected value="letter">' . _('Letter') .'</option>';
} else {
	echo '<option value="letter">' . _('Letter') .'</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter_landscape'){
	echo '<option selected value="letter_landscape">' . _('Letter') . ' ' . _('landscape') .'</option>';
} else {
	echo '<option value="letter_landscape">' . _('Letter') . ' ' . _('landscape') .'</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal'){
	echo '<option selected value="legal">' . _('Legal') .'</option>';
} else {
	echo '<option value="legal">' . _('Legal') .'</option>';
}
if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal_landscape'){
	echo '<option selected value="legal_landscape">' . _('Legal') . ' ' . _('landscape') .'</option>';
} else {
	echo '<option value="legal_landscape">' . _('Legal') . ' ' . _('landscape') .'</option>';
}

echo '</select></td></tr>';

echo '<tr>
	<td>' . _('Theme') . ':</td>
	<td><select name="Theme">';

$ThemeDirectory = dir('css/');


while (false != ($ThemeName = $ThemeDirectory->read())){

	if (is_dir('css/' . $ThemeName) AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != '.svn'){

		if (isset($_POST['Theme']) and $_POST['Theme'] == $ThemeName){
			echo '<option selected value="' . $ThemeName . '">' . $ThemeName .'</option>';
		} else if (!isset($_POST['Theme']) and ($_SESSION['DefaultTheme']==$ThemeName)) {
			echo '<option selected value="' . $ThemeName . '">' . $ThemeName .'</option>';
		} else {
			echo '<option value="' . $ThemeName . '">' . $ThemeName .'</option>';
		}
	}
}

echo '</select></td></tr>';


echo '<tr>
	<td>' . _('Language') . ':</td>
	<td><select name="UserLanguage">';

foreach ($LanguagesArray as $LanguageEntry => $LanguageName){
	if (isset($_POST['UserLanguage']) and $_POST['UserLanguage'] == $LanguageEntry){
		echo '<option selected value="' . $LanguageEntry . '">' . $LanguageName .'</option>';
	} elseif (!isset($_POST['UserLanguage']) and $LanguageEntry == $DefaultLanguage) {
		echo '<option selected value="' . $LanguageEntry . '">' . $LanguageName .'</option>';
	} else {
		echo '<option value="' . $LanguageEntry . '">' . $LanguageName .'</option>';
	}
}


echo '</select></td></tr>';

echo '<tr><td>' . _('Account Status') . ':</td>
		<td><select name="Blocked">';
if ($_POST['Blocked']==0){
	echo '<option selected value="0">' . _('Open') . '</option>
			<option value="1">' . _('Blocked') . '</option>';
} else {
 	echo '<option value="0">' . _('Open') . '</option>
			<option selected value="1">' . _('Blocked') . '</option>';
}
echo '</select></td></tr>';

echo '</table><br />
	<div class="centre"><input type="submit" name="submit" value="' . _('Enter Information') . '"></div>
	</form>';

if (isset($_GET['SelectedUser'])) {
	echo '<script  type="text/javascript">defaultControl(document.forms[0].Password);</script>';
} else {
	echo '<script  type="text/javascript">defaultControl(document.forms[0].UserID);</script>';
}

include('includes/footer.inc');
?>