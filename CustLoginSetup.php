<?php

/* $Id: CustLoginSetup.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');
$Title = _('Customer Login Configuration');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include ('includes/LanguagesArray.php');


if (!isset($_SESSION['CustomerID'])){
	echo '<br />
		<br />';
	prnMsg(_('A customer must first be selected before logins can be defined for it') . '<br /><br /><a href="' . $RootPath . '/SelectCustomer.php">' . _('Select A Customer') . '</a>','info');
	include('includes/footer.inc');
	exit;
}


echo '<a href="' . $RootPath . '/SelectCustomer.php">' . _('Back to Customers') . '</a><br />';

$sql="SELECT name
		FROM debtorsmaster
		WHERE debtorno='".$_SESSION['CustomerID']."'";

$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$CustomerName=$myrow['name'];

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . _('Customer') . '" alt="" />' . ' ' . _('Customer') . ' : ' . $_SESSION['CustomerID'] . ' - ' . $CustomerName. _(' has been selected') .
	'</p>
	<br />';


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['UserID'])<4){
		$InputError = 1;
		prnMsg(_('The user ID entered must be at least 4 characters long'),'error');
	} elseif (ContainsIllegalCharacters($_POST['UserID']) OR mb_strstr($_POST['UserID'],' ')) {
		$InputError = 1;
		prnMsg(_('User names cannot contain any of the following characters') . " - ' &amp; + \" \\ " . _('or a space'),'error');
	} elseif (mb_strlen($_POST['Password'])<5){
		if (!$SelectedUser){
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'),'error');
		}
	} elseif (mb_strstr($_POST['Password'],$_POST['UserID'])!= false){
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
				WHERE debtorno='" . $_SESSION['CustomerID'] . "'
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

	if ($InputError !=1) {

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
											'" . $_SESSION['CustomerID'] ."',
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

			$ErrMsg = _('The user could not be added because');
			$DbgMsg = _('The SQL that was used to insert the new user and failed was');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			prnMsg( _('A new customer login has been created'), 'success' );
			include('includes/footer.inc');
			exit;
		}
	}

}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">
		<tr>
			<td>' . _('User Login') . ':</td>
			<td><input type="text" name="UserID" required="required" ' . (isset($_GET['SelectedUser']) ? '':'autofocus="autofocus"') . 'title="' . _('Enter a userid for this customer login') . '" size="22" maxlength="20" /></td>
		</tr>';

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
		<td><input type="password" name="Password" required="required" ' . (isset($_GET['SelectedUser']) ? 'autofocus="autofocus"':'') . ' title="' . _('Enter a password for this customer login') . '" size="22" maxlength="20" value="' . $_POST['Password'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Full Name') . ':</td>
			<td><input type="text" name="RealName" value="' . $_POST['RealName'] . '" required="required" title="' . _('Enter the user\'s real name') . '" size="36" maxlength="35" /></td>
		</tr>
		<tr>
			<td>' . _('Telephone No') . ':</td>
			<td><input type="tel" name="Phone" value="' . $_POST['Phone'] . '" size="32" maxlength="30" /></td>
		</tr>
		<tr>
			<td>' . _('Email Address') .':</td>
			<td><input type="email" name="Email" value="' . $_POST['Email'] .'" required="required" title="' . _('Enter the user\'s email address') . '" size="32" maxlength="55" /></td>
		</tr>
        <tr>
		<td><input type="hidden" name="Access" value="1" />
			' . _('Branch Code') . ':</td>
			<td><select name="BranchCode">';

$sql = "SELECT branchcode FROM custbranch WHERE debtorno = '" . $_SESSION['CustomerID'] . "'";
$result = DB_query($sql,$db);

while ($myrow=DB_fetch_array($result)){

	//Set the first available branch as default value when nothing is selected
	if (!isset($_POST['BranchCode'])) {
		$_POST['BranchCode']= $myrow['branchcode'];
	}

	if (isset($_POST['BranchCode']) and $myrow['branchcode'] == $_POST['BranchCode']){
		echo '<option selected="selected" value="' . $myrow['branchcode'] . '">' . $myrow['branchcode'] . '</option>';
	} else {
		echo '<option value="' . $myrow['branchcode'] . '">' . $myrow['branchcode'] . '</option>';
	}
}
echo '</select></td></tr>';
echo '<tr><td>' . _('Reports Page Size') .':</td>
	<td><select name="PageSize">';

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A4'){
	echo '<option selected="selected" value="A4">' . _('A4')  . '</option>';
} else {
	echo '<option value="A4">' . _('A4') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3'){
	echo '<option selected="selected" value="A3">' . _('A3')  . '</option>';
} else {
	echo '<option value="A3">' . _('A3')  . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3_landscape'){
	echo '<option selected="selected" value="A3_landscape">' . _('A3') . ' ' . _('landscape')  . '</option>';
} else {
	echo '<option value="A3_landscape">' . _('A3') . ' ' . _('landscape')  . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter'){
	echo '<option selected="selected" value="letter">' . _('Letter')  . '</option>';
} else {
	echo '<option value="letter">' . _('Letter')  . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter_landscape'){
	echo '<option selected="selected" value="letter_landscape">' . _('Letter') . ' ' . _('landscape')  . '</option>';
} else {
	echo '<option value="letter_landscape">' . _('Letter') . ' ' . _('landscape')  . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal'){
	echo '<option selected="selected" value="legal">' . _('Legal')  . '</option>';
} else {
	echo '<option value="legal">' . _('Legal')  . '</option>';
}
if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal_landscape'){
	echo '<option selected="selected" value="legal_landscape">' . _('Legal') . ' ' . _('landscape')  . '</option>';
} else {
	echo '<option value="legal_landscape">' . _('Legal') . ' ' . _('landscape')  . '</option>';
}

echo '</select></td>
	</tr>
	<tr>
		<td>' . _('Theme') . ':</td>
		<td><select name="Theme">';

$ThemeDirectory = dir('css/');

while (false != ($ThemeName = $ThemeDirectory->read())){

	if (is_dir('css/' . $ThemeName) AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != '.svn'){

		if (isset($_POST['Theme']) and $_POST['Theme'] == $ThemeName){
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName  . '</option>';
		} else if (!isset($_POST['Theme']) and ($_SESSION['DefaultTheme']==$ThemeName)) {
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName  . '</option>';
		} else {
			echo '<option value="' . $ThemeName . '">' . $ThemeName  . '</option>';
		}
	}
}

echo '</select></td>
	</tr>
	<tr>
		<td>' . _('Language') . ':</td>
		<td><select name="UserLanguage">';

foreach ($LanguagesArray as $LanguageEntry => $LanguageName){
	if (isset($_POST['UserLanguage']) and $_POST['UserLanguage'] == $LanguageEntry){
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	} elseif (!isset($_POST['UserLanguage']) AND $LanguageEntry == $DefaultLanguage) {
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	} else {
		echo '<option value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	}
}
echo '</select></td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>
    </div>
	</form>';

include('includes/footer.inc');
?>