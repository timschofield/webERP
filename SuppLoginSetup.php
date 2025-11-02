<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Supplier Login Configuration');
$ViewTopic = 'Setup';
$BookMark = '';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/LanguagesArray.php');

if (!isset($_SESSION['SupplierID'])){
	echo '<br />
		<br />';
	prnMsg(__('A supplier must first be selected before logins can be defined for it') . '<br /><br /><a href="' . $RootPath . '/SelectSupplier.php">' . __('Select a supplier') . '</a>','info');
	include('includes/footer.php');
	exit();
}

$ModuleList = array(__('Orders'),
					__('Receivables'),
					__('Payables'),
					__('Purchasing'),
					__('Inventory'),
					__('Manufacturing'),
					__('General Ledger'),
					__('Asset Manager'),
					__('Petty Cash'),
					__('Setup'));

echo '<a href="' . $RootPath . '/SelectSupplier.php?" class="toplink">' . __('Back to Suppliers') . '</a><br />';

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . __('Supplier') . '" alt="" />' . ' ' . __('Supplier') . ' : ' . $_SESSION['SupplierID'] . __(' has been selected') . '
	</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['UserID'])<4){
		$InputError = 1;
		prnMsg(__('The user ID entered must be at least 4 characters long'),'error');
	} elseif (ContainsIllegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(__('User names cannot contain any of the following characters') . " - ' & + \" \\ " . __('or a space'),'error');
	} elseif (mb_strlen($_POST['Password'])<5){
			$InputError = 1;
			prnMsg(__('The password entered must be at least 5 characters long'),'error');
	} elseif (mb_strstr($_POST['Password'],$_POST['UserID'])!= false){
		$InputError = 1;
		prnMsg(__('The password cannot contain the user id'),'error');
	}

	/* Make a comma separated list of modules allowed ready to update the database*/
	$i=0;
	$ModulesAllowed = '';
	while ($i < count($ModuleList)){
		$ModulesAllowed .= ' '. ',';//no any modules allowed for the suppliers
		$i++;
	}


	if ($InputError !=1) {

		$SQL = "INSERT INTO www_users (userid,
										realname,
										supplierid,
										salesman,
										password,
										phone,
										email,
										pagesize,
										fullaccess,
										defaultlocation,
										lastvisitdate,
										modulesallowed,
										displayrecordsmax,
										theme,
										language)
						VALUES ('" . $_POST['UserID'] . "',
							'" . $_POST['RealName'] ."',
							'" . $_SESSION['SupplierID'] ."',
							'',
							'" . CryptPass($_POST['Password']) ."',
							'" . $_POST['Phone'] . "',
							'" . $_POST['Email'] ."',
							'" . $_POST['PageSize'] ."',
							'" . $_POST['Access'] . "',
							'" . $_POST['DefaultLocation'] ."',
							NOW(),
							'0,0,1,1,0,0,0,0,0,0,0,',
							'" . $_SESSION['DefaultDisplayRecordsMax'] . "',
							'" . $_POST['Theme'] . "',
							'". $_POST['UserLanguage'] ."')";
		$ErrMsg = __('The user could not be added because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg( __('A new supplier login has been created'), 'success' );
		include('includes/footer.php');
		exit();
	}
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', __('Supplier Login Details'), '</legend>
		<field>
			<label for="UserID">' . __('User Login') . ':</label>
			<input type="text" pattern="[^><+-]{4,20}" title="" required="required" placeholder="'.__('More than 4 characters').'" name="UserID" size="22" maxlength="20" />
			<fieldhelp>'.__('The user ID must has more than 4 legal characters').'</fieldhelp>
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
echo '<field>
		<label for="Password">' . __('Password') . ':</label>
		<input type="password" pattern=".{5,20}" placeholder="'.__('More than 5 characters').'" required="required" title=""  name="Password" size="22" maxlength="20" value="' . $_POST['Password'] . '" />
		<fieldhelp>'.__('Password must be more than 5 characters').'</fieldhelp>
	</field>
	<field>
		<label for="RealName">' . __('Full Name') . ':</label>
		<input type="text" pattern=".{0,35}" title="" placeholder="'.__('User name').'" name="RealName" value="' . $_POST['RealName'] . '" size="36" maxlength="35" />
		<fieldhelp>'.__('Must be less than 35 characters').'</fieldhelp>
	</field>
	<field>
		<label for="Phone">' . __('Telephone No') . ':</label>
		<input type="tel" pattern="[\s+()-\d]{1,30}" title="" placeholder="'.__('number and allowed charactrs').'" name="Phone" value="' . $_POST['Phone'] . '" size="32" maxlength="30" />
		<fieldhelp>'.__('The input must be phone number').'</fieldhelp>
	</field>
	<field>
		<label for="Email">' . __('Email Address') .':</label>
		<input type="email" name="Email" title="" placeholder="'.__('email address format').'" value="' . $_POST['Email'] .'" size="32" maxlength="55" />
		<fieldhelp>'.__('The input must be email address').'</fieldhelp>
	</field>';

//Make an array of the security roles where only one role is active and is ID 1

//For the security role selection box, we will only show roles that have:
//- Only one entry in securitygroups AND the tokenid of this entry == 9

//First get all available security role ID's'
$RolesResult = DB_query("SELECT secroleid FROM securityroles");
$FoundTheSupplierRole = false;
while ($MyRoles = DB_fetch_array($RolesResult)){
	//Now look to find the tokens for the role - we just wnat the role that has just one token i.e. token 9
	$TokensResult = DB_query("SELECT tokenid
								FROM securitygroups
								WHERE secroleid = '" . $MyRoles['secroleid'] ."'");

	while ($MyToken = DB_fetch_row($TokensResult)) {
		if ($MyToken[0]==9){
			echo'<input type="hidden" name="Access" value ="' . $MyRoles['secroleid'] . '" />';
			$FoundTheSupplierRole = true;
			break;
		}
	}
}

if (!$FoundTheSupplierRole){
	echo '</fieldset>
		  </form>';
	prnMsg(__('The supplier login role is expected to contain just one token - number 9. There is no such role currently defined - so a supplier login cannot be set up until this role is defined'),'error');
	include('includes/footer.php');
	exit();
}


echo '<field>
		<label for="DefaultLocation">' . __('Default Location') . ':</label>
		<select name="DefaultLocation">';

$SQL = "SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1";
$Result = DB_query($SQL);

while ($MyRow=DB_fetch_array($Result)){

	if (isset($_POST['DefaultLocation'])
		AND $MyRow['loccode'] == $_POST['DefaultLocation']){

		echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="PageSize">' . __('Reports Page Size') .':</label>
		<select name="PageSize">';

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A4'){
	echo '<option selected="selected" value="A4">' . __('A4') . '</option>';
} else {
	echo '<option value="A4">' . __('A4') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3'){
	echo '<option selected="selected" value="A3">' . __('A3') . '</option>';
} else {
	echo '<option value="A3">' . __('A3') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3_landscape'){
	echo '<option selected="selected" value="A3_landscape">' . __('A3') . ' ' . __('landscape') . '</option>';
} else {
	echo '<option value="A3_landscape">' . __('A3') . ' ' . __('landscape') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter'){
	echo '<option selected="selected" value="letter">' . __('Letter') . '</option>';
} else {
	echo '<option value="letter">' . __('Letter') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter_landscape'){
	echo '<option selected="selected" value="letter_landscape">' . __('Letter') . ' ' . __('landscape') . '</option>';
} else {
	echo '<option value="letter_landscape">' . __('Letter') . ' ' . __('landscape') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal'){
	echo '<option selected="selected" value="legal">' . __('Legal') . '</option>';
} else {
	echo '<option value="legal">' . __('Legal') . '</option>';
}
if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal_landscape'){
	echo '<option selected="selected" value="legal_landscape">' . __('Legal') . ' ' . __('landscape') . '</option>';
} else {
	echo '<option value="legal_landscape">' . __('Legal') . ' ' . __('landscape') . '</option>';
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
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName . '</option>';
		} else if (!isset($_POST['Theme']) and ($Theme==$ThemeName)) {
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName . '</option>';
		} else {
			echo '<option value="' . $ThemeName . '">' . $ThemeName . '</option>';
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
	} elseif (!isset($_POST['UserLanguage']) and $LanguageEntry == $DefaultLanguage) {
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	} else {
		echo '<option value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	}
}
echo '</select>
	</field>';

echo '</fieldset>
	<div class="centre">
		<input type="submit" name="submit" value="' . __('Enter Information') . '" />
	</div>
	</form>';

echo '<script>defaultControl(document.forms[0].UserID);</script>';

include('includes/footer.php');
