<?php

/* $Id$*/

include('includes/session.inc');
$title = _('Supplier Login Configuration');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include ('includes/LanguagesArray.php');

$ModuleList = array(_('Orders'),
					_('Receivables'),
					_('Payables'),
					_('Purchasing'),
					_('Inventory'),
					_('Manufacturing'),
					_('General Ledger'),
					_('Asset Manager'),
					_('Petty Cash'),
					_('Setup'));

echo '<a href="' . $rootpath . '/SelectSupplier.php?">' . _('Back to Suppliers') . '</a><br />';

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . _('Supplier') . '" alt="" />' . ' ' . _('Supplier') . ' : ' . $_SESSION['SupplierID'] . _(' has been selected') . '</p><br />';



if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['UserID'])<3){
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
	}
	
	/* Make a comma separated list of modules allowed ready to update the database*/
	$i=0;
	$ModulesAllowed = '';
	while ($i < count($ModuleList)){
		$FormVbl = 'Module_' . $i;
		$ModulesAllowed .= $_POST[($FormVbl)] . ',';
		$i++;
	}
	

	if ($InputError !=1) {

		$sql = "INSERT INTO www_users (userid,
										realname,
										supplierid,
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
							'" . CryptPass($_POST['Password']) ."',
							'" . $_POST['Phone'] . "',
							'" . $_POST['Email'] ."',
							'" . $_POST['PageSize'] ."',
							'" . $_POST['Access'] . "',
							'" . $_POST['DefaultLocation'] ."',
							'" . date($_SESSION['DefaultDateFormat']) ."',
							'" . $ModulesAllowed . "',
							'" . $_SESSION['DefaultDisplayRecordsMax'] . "',
							'" . $_POST['Theme'] . "',
							'". $_POST['UserLanguage'] ."')";
		$ErrMsg = _('The user could not be added because');
		$DbgMsg = _('The SQL that was used to insert the new user and failed was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		prnMsg( _('A new supplier login has been created'), 'success' );
		include('includes/footer.inc');
		exit;
	}
} 

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


echo '<table class="selection">
		<tr>
			<td>' . _('User Login') . ':</td>
			<td><input type="text" name="UserID" size="22" maxlength="20" /></td>
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
		<td><input type="password" name="Password" size="22" maxlength="20" value="' . $_POST['Password'] . '">
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





//Make an array of the security roles where only one role is active and is ID 1

//For the security role selection box, we will only show roles that have:
//- Only one entry in securitygroups AND the tokenid of this entry == 9

//First get all available security role ID's'
$RolesResult = DB_query("SELECT secroleid FROM securityroles", $db);
$FoundTheSupplierRole = false;
while ($myroles = DB_fetch_array($RolesResult)){
	//Now look to find the tokens for the role - we just wnat the role that has just one token i.e. token 9
	$TokensResult = DB_query("SELECT tokenid 
								FROM securitygroups 
								WHERE secroleid = '" . $myroles['secroleid'] ."'",
								$db);

	if (DB_num_rows($TokensResult) == 1 ) {
		$mytoken = DB_fetch_row($TokensResult);
		if ($mytoken[0]==9){
			echo'<input type="hidden" name="Access" value ="' . $myroles['secroleid'] . '" />';
			$FoundTheSupplierRole = true;
			break;
		}
	}
}

if (!$FoundTheSupplierRole){
	prnMsg(_('The supplier login role is expected to contain just one token - number 9. There is no such role currently defined - so a supplier login cannot be set up until this role is defined'),'error');
	echo '</table>';
	include('includes/footer.inc');
	exit;
}


echo '<tr><td>' . _('Default Location') . ':</td>
	<td><select name="DefaultLocation">';

$sql = "SELECT loccode, locationname FROM locations";
$result = DB_query($sql,$db);

while ($myrow=DB_fetch_array($result)){

	if (isset($_POST['DefaultLocation']) 
		AND $myrow['loccode'] == $_POST['DefaultLocation']){

		echo '<option selected value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	} else {
		echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	}
}

echo '<tr><td>' . _('Reports Page Size') .':</td>
	<td><select name="PageSize">';

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A4'){
	echo '<option selected value="A4">' . _('A4') . '</option>';
} else {
	echo '<option value="A4">' . _('A4') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3'){
	echo '<option selected value="A3">' . _('A3') . '</option>';
} else {
	echo '<option value="A3">' . _('A3') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3_landscape'){
	echo '<option selected value="A3_landscape">' . _('A3') . ' ' . _('landscape') . '</option>';
} else {
	echo '<option value="A3_landscape">' . _('A3') . ' ' . _('landscape') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter'){
	echo '<option selected value="letter">' . _('Letter') . '</option>';
} else {
	echo '<option value="letter">' . _('Letter') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter_landscape'){
	echo '<option selected value="letter_landscape">' . _('Letter') . ' ' . _('landscape') . '</option>';
} else {
	echo '<option value="letter_landscape">' . _('Letter') . ' ' . _('landscape') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal'){
	echo '<option selected value="legal">' . _('Legal') . '</option>';
} else {
	echo '<option Value="legal">' . _('Legal') . '</option>';
}
if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal_landscape'){
	echo '<option selected value="legal_landscape">' . _('Legal') . ' ' . _('landscape') . '</option>';
} else {
	echo '<option value="legal_landscape">' . _('Legal') . ' ' . _('landscape') . '</option>';
}

echo '</select></td></tr>';

echo '<tr>
	<td>' . _('Theme') . ':</td>
	<td><select name="Theme">';

$ThemeDirectory = dir('css/');


while (false != ($ThemeName = $ThemeDirectory->read())){

	if (is_dir('css/' . $ThemeName) AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != '.svn'){

		if (isset($_POST['Theme']) and $_POST['Theme'] == $ThemeName){
			echo '<option selected value="' . $ThemeName . '">' . $ThemeName . '</option>';
		} else if (!isset($_POST['Theme']) and ($_SESSION['DefaultTheme']==$ThemeName)) {
			echo '<option selected value="' . $ThemeName . '">' . $ThemeName . '</option>';
		} else {
			echo '<option value="' . $ThemeName . '">' . $ThemeName . '</option>';
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


echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>
	</form>';

echo '<script  type="text/javascript">defaultControl(document.forms[0].UserID);</script>';


include('includes/footer.inc');

?>