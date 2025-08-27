<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Customer Login Configuration');
$ViewTopic = 'Setup';// Filename in ManualContents.php's TOC.
$BookMark = '';// Anchor's id in the manual's html document.
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
//include('includes/LanguagesArray.php');

if (!isset($_SESSION['CustomerID'])){
	echo '<br />
		<br />';
	prnMsg(__('A customer must first be selected before logins can be defined for it') . '<br /><br /><a href="' . $RootPath . '/SelectCustomer.php">' . __('Select A Customer') . '</a>','info');
	include('includes/footer.php');
	exit();
}


echo '<a href="' . $RootPath . '/SelectCustomer.php">' . __('Back to Customers') . '</a><br />';

$SQL="SELECT name
		FROM debtorsmaster
		WHERE debtorno='".$_SESSION['CustomerID']."'";

$Result = DB_query($SQL);
$MyRow=DB_fetch_array($Result);
$CustomerName=$MyRow['name'];

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . __('Customer') . '" alt="" />' . ' ' . __('Customer') . ' : ' . $_SESSION['CustomerID'] . ' - ' . $CustomerName. __(' has been selected') .
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
		prnMsg(__('The user ID entered must be at least 4 characters long'),'error');
	} elseif (ContainsIllegalCharacters($_POST['UserID']) OR mb_strstr($_POST['UserID'],' ')) {
		$InputError = 1;
		prnMsg(__('User names cannot contain any of the following characters') . " - ' &amp; + \" \\ " . __('or a space'),'error');
	} elseif (mb_strlen($_POST['Password'])<5){
		if (!$SelectedUser){
			$InputError = 1;
			prnMsg(__('The password entered must be at least 5 characters long'),'error');
		}
	} elseif (mb_strstr($_POST['Password'],$_POST['UserID'])!= false){
		$InputError = 1;
		prnMsg(__('The password cannot contain the user id'),'error');
	} elseif ((mb_strlen($_SESSION['CustomerID'])>0) AND (mb_strlen($_POST['BranchCode'])==0)) {
		$InputError = 1;
		prnMsg(__('If you enter a Customer Code you must also enter a Branch Code valid for this Customer'),'error');
	}

	if ((mb_strlen($_POST['BranchCode'])>0) AND ($InputError !=1)) {
		// check that the entered branch is valid for the customer code
		$SQL = "SELECT defaultlocation
				FROM custbranch
				WHERE debtorno='" . $_SESSION['CustomerID'] . "'
				AND branchcode='" . $_POST['BranchCode'] . "'";

		$ErrMsg = __('The check on validity of the customer code and branch failed because');
		$Result = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($Result)==0){
			prnMsg(__('The entered Branch Code is not valid for the entered Customer Code'),'error');
			$InputError = 1;
		} else {
			$MyRow = DB_fetch_row($Result);
			$InventoryLocation = $MyRow[0];
	}

	if ($InputError !=1) {

		$SQL = "INSERT INTO www_users (userid,
										realname,
										customerid,
										salesman,
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
											'',
											'" . $_POST['BranchCode'] ."',
											'" . CryptPass($_POST['Password']) ."',
											'" . $_POST['Phone'] . "',
											'" . $_POST['Email'] ."',
											'" . $_POST['PageSize'] ."',
											'7',
											'" . $InventoryLocation ."',
											'1,1,0,0,0,0,0,0,0,0,0,',
											'" . $_SESSION['DefaultDisplayRecordsMax'] . "',
											'" . $_POST['Theme'] . "',
											'". $_POST['UserLanguage'] ."')";

			$ErrMsg = __('The user could not be added because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg( __('A new customer login has been created'), 'success' );
			include('includes/footer.php');
			exit();
		}
	}

}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', __('Login details for customer'), ' ', $CustomerName, '</legend>
		<field>
			<label for="UserID">' . __('User Login') . ':</label>
			<input type="text" name="UserID" required="required" ' . (isset($_GET['SelectedUser']) ? '':'autofocus="autofocus"') . 'title="" size="22" maxlength="20" />
			<fieldhelp>' . __('Enter a userid for this customer login') . '</fieldhelp>
		</field>';

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

echo '<input type="hidden" name="Access" value="1" />';
echo '<field>
		<label for="Password">' . __('Password') . ':</label>
		<input type="password" name="Password" required="required" ' . (isset($_GET['SelectedUser']) ? 'autofocus="autofocus"':'') . ' title="" size="22" maxlength="20" value="' . $_POST['Password'] . '" />
		<fieldhelp>' . __('Enter a password for this customer login') . '</fieldhelp>
	</field>
	<field>
		<label for="RealName">' . __('Full Name') . ':</label>
		<input type="text" name="RealName" value="' . $_POST['RealName'] . '" required="required" title="" size="36" maxlength="35" />
		<fieldhelp>' . __('Enter the user\'s real name') . '</fieldhelp>
	</field>
	<field>
		<label for="Phone">' . __('Telephone No') . ':</label>
		<input type="tel" name="Phone" value="' . $_POST['Phone'] . '" size="32" maxlength="30" />
	</field>
	<field>
		<label for="Email">' . __('Email Address') .':</label>
		<input type="email" name="Email" value="' . $_POST['Email'] .'" required="required" title="" size="32" maxlength="55" />
		<fieldhelp>' . __('Enter the user\'s email address') . '</fieldhelp>
	</field>
    <field>
		<label for="BranchCode">' . __('Branch Code') . ':</label>
		<select name="BranchCode">';

$SQL = "SELECT branchcode FROM custbranch WHERE debtorno = '" . $_SESSION['CustomerID'] . "'";
$Result = DB_query($SQL);

while ($MyRow=DB_fetch_array($Result)){

	//Set the first available branch as default value when nothing is selected
	if (!isset($_POST['BranchCode'])) {
		$_POST['BranchCode']= $MyRow['branchcode'];
	}

	if (isset($_POST['BranchCode']) and $MyRow['branchcode'] == $_POST['BranchCode']){
		echo '<option selected="selected" value="' . $MyRow['branchcode'] . '">' . $MyRow['branchcode'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['branchcode'] . '">' . $MyRow['branchcode'] . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="PageSize">' . __('Reports Page Size') .':</label>
		<select name="PageSize">';

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A4'){
	echo '<option selected="selected" value="A4">' . __('A4')  . '</option>';
} else {
	echo '<option value="A4">' . __('A4') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3'){
	echo '<option selected="selected" value="A3">' . __('A3')  . '</option>';
} else {
	echo '<option value="A3">' . __('A3')  . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3_landscape'){
	echo '<option selected="selected" value="A3_landscape">' . __('A3') . ' ' . __('landscape')  . '</option>';
} else {
	echo '<option value="A3_landscape">' . __('A3') . ' ' . __('landscape')  . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter'){
	echo '<option selected="selected" value="letter">' . __('Letter')  . '</option>';
} else {
	echo '<option value="letter">' . __('Letter')  . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter_landscape'){
	echo '<option selected="selected" value="letter_landscape">' . __('Letter') . ' ' . __('landscape')  . '</option>';
} else {
	echo '<option value="letter_landscape">' . __('Letter') . ' ' . __('landscape')  . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal'){
	echo '<option selected="selected" value="legal">' . __('Legal')  . '</option>';
} else {
	echo '<option value="legal">' . __('Legal')  . '</option>';
}
if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal_landscape'){
	echo '<option selected="selected" value="legal_landscape">' . __('Legal') . ' ' . __('landscape')  . '</option>';
} else {
	echo '<option value="legal_landscape">' . __('Legal') . ' ' . __('landscape')  . '</option>';
}

echo '</select>
	</field>';

echo '<field>
		<label for="Theme">' . __('Theme') . ':</label>
		<select name="Theme">';

$ThemeDirectory = dir('css/');

while (false != ($ThemeName = $ThemeDirectory->read())){

	if (is_dir('css/' . $ThemeName) AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != '.svn'){

		if (isset($_POST['Theme']) and $_POST['Theme'] == $ThemeName){
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName  . '</option>';
		} else if (!isset($_POST['Theme']) and ($Theme==$ThemeName)) {
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName  . '</option>';
		} else {
			echo '<option value="' . $ThemeName . '">' . $ThemeName  . '</option>';
		}
	}
}

echo '</select>
	</field>';

echo '<field>
		<label for="UserLanguage">' . __('Language') . ':</label>
		<select name="UserLanguage">';

foreach ($LanguagesArray as $LanguageEntry => $LanguageName){
	if (isset($_POST['UserLanguage']) and $_POST['UserLanguage'] == $LanguageEntry){
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	} elseif (!isset($_POST['UserLanguage']) AND $LanguageEntry == $DefaultLanguage) {
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	} else {
		echo '<option value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	}
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="submit" value="' . __('Enter Information') . '" />
    </div>
	</form>';

include('includes/footer.php');
