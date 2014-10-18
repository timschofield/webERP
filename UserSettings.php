<?php

/* $Id: UserSettings.php 6519 2013-12-26 18:45:22Z rchacon $*/

include('includes/session.inc');
$Title = _('User Settings');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/user.png" title="' .
	_('User Settings') . '" alt="" />' . ' ' . _('User Settings') . '</p>';

$PDFLanguages = array(_('Latin Western Languages - Times'),
					_('Eastern European Russian Japanese Korean Hebrew Arabic Thai'),
					_('Chinese'),
					_('Free Serif'));


if (isset($_POST['Modify'])) {
	// no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if ($_POST['DisplayRecordsMax'] <= 0){
		$InputError = 1;
		prnMsg(_('The Maximum Number of Records on Display entered must not be negative') . '. ' . _('0 will default to system setting'),'error');
	}

	//!!!for the demo only - enable this check so password is not changed
	if ($AllowDemoMode AND $_POST['Password'] != ''){
		$InputError = 1;
		prnMsg(_('Cannot change password in the demo or others would be locked out!'),'warn');
	}

 	$UpdatePassword = 'N';

	if ($_POST['PasswordCheck'] != ''){
		if (mb_strlen($_POST['Password'])<5){
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'),'error');
		} elseif (mb_strstr($_POST['Password'],$_SESSION['UserID'])!= False){
			$InputError = 1;
			prnMsg(_('The password cannot contain the user id'),'error');
		}
		if ($_POST['Password'] != $_POST['PasswordCheck']){
			$InputError = 1;
			prnMsg(_('The password and password confirmation fields entered do not match'),'error');
		}else{
			$UpdatePassword = 'Y';
		}
	}


	if ($InputError != 1) {
		// no errors
		if ($UpdatePassword != 'Y'){
			$sql = "UPDATE www_users
					SET displayrecordsmax='" . $_POST['DisplayRecordsMax'] . "',
						theme='" . $_POST['Theme'] . "',
						language='" . $_POST['Language'] . "',
						email='". $_POST['email'] ."',
						pdflanguage='" . $_POST['PDFLanguage'] . "'
					WHERE userid = '" . $_SESSION['UserID'] . "'";

			$ErrMsg =  _('The user alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

			prnMsg( _('The user settings have been updated') . '. ' . _('Be sure to remember your password for the next time you login'),'success');
		} else {
			$sql = "UPDATE www_users
				SET displayrecordsmax='" . $_POST['DisplayRecordsMax'] . "',
					theme='" . $_POST['Theme'] . "',
					language='" . $_POST['Language'] . "',
					email='". $_POST['email'] ."',
					pdflanguage='" . $_POST['PDFLanguage'] . "',
					password='" . CryptPass($_POST['Password']) . "'
				WHERE userid = '" . $_SESSION['UserID'] . "'";

			$ErrMsg =  _('The user alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

			prnMsg(_('The user settings have been updated'),'success');
		}
	  // update the session variables to reflect user changes on-the-fly
		$_SESSION['DisplayRecordsMax'] = $_POST['DisplayRecordsMax'];
		$_SESSION['Theme'] = trim($_POST['Theme']); /*already set by session.inc but for completeness */
		$Theme = $_SESSION['Theme'];
		$_SESSION['Language'] = trim($_POST['Language']);
		$_SESSION['PDFLanguage'] = $_POST['PDFLanguage'];
		include ('includes/LanguageSetup.php'); // After last changes in LanguageSetup.php, is it required to update?
	}
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

If (!isset($_POST['DisplayRecordsMax']) OR $_POST['DisplayRecordsMax']=='') {

  $_POST['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax'];

}

echo '<table class="selection">
		<tr>
			<td>' . _('User ID') . ':</td>
			<td>' . $_SESSION['UserID'] . '</td>
		</tr>';

echo '<tr>
		<td>' . _('User Name') . ':</td>
		<td>' . $_SESSION['UsersRealName'] . '
		<input type="hidden" name="RealName" value="'.$_SESSION['UsersRealName'].'" /></td></tr>';

echo '<tr>
	<td>' . _('Maximum Number of Records to Display') . ':</td>
	<td><input type="text" class="integer" required="required" title="'._('The input must be positive integer').'" name="DisplayRecordsMax" size="3" maxlength="3" value="' . $_POST['DisplayRecordsMax'] . '"  /></td>
	</tr>';


echo '<tr>
	<td>' . _('Language') . ':</td>
	<td><select name="Language">';

if (!isset($_POST['Language'])){
	$_POST['Language']=$_SESSION['Language'];
}

foreach ($LanguagesArray as $LanguageEntry => $LanguageName){
	if (isset($_POST['Language']) AND $_POST['Language'] == $LanguageEntry){
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	} elseif (!isset($_POST['Language']) AND $LanguageEntry == $DefaultLanguage) {
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	} else {
		echo '<option value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	}
}
echo '</select></td></tr>';

echo '<tr>
	<td>' . _('Theme') . ':</td>
	<td><select name="Theme">';

$ThemeDirectories = scandir('css/');


foreach ($ThemeDirectories as $ThemeName) {

	if (is_dir('css/' . $ThemeName) AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != '.svn'){

		if ($_SESSION['Theme'] == $ThemeName){
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName . '</option>';
		} else {
			echo '<option value="' . $ThemeName . '">' . $ThemeName . '</option>';
		}
	}
}

if (!isset($_POST['PasswordCheck'])) {
	$_POST['PasswordCheck']='';
}
if (!isset($_POST['Password'])) {
	$_POST['Password']='';
}
echo '</select></td></tr>
	<tr>
		<td>' . _('New Password') . ':</td>
		<td><input type="password" name="Password" pattern="(?!^'.$_SESSION['UserID'].'$).{5,}" title="'._('Must be more than 5 characters and cannot be as same as userid').'" placeholder="'._('More than 5 characters').'" size="20" value="' .  $_POST['Password'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Confirm Password') . ':</td>
		<td><input type="password" name="PasswordCheck" pattern="(?!^'.$_SESSION['UserID'].'$).{5,}" title="'._('Must be more than 5 characters and cannot be as same as userid').'" placeholder="'._('More than 5 characters').'" size="20"  value="' . $_POST['PasswordCheck'] . '" /></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><i>' . _('If you leave the password boxes empty your password will not change') . '</i></td>
	</tr>
	<tr>
		<td>' . _('Email') . ':</td>';

$sql = "SELECT email from www_users WHERE userid = '" . $_SESSION['UserID'] . "'";
$result = DB_query($sql,$db);
$myrow = DB_fetch_array($result);
if(!isset($_POST['email'])){
	$_POST['email'] = $myrow['email'];
}

echo '<td><input type="email" name="email" size="40" value="' . $_POST['email'] . '" /></td>
	</tr>';

if (!isset($_POST['PDFLanguage'])){
	$_POST['PDFLanguage']=$_SESSION['PDFLanguage'];
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
	</tr>
	</table>
	<br />
	<div class="centre"><input type="submit" name="Modify" value="' . _('Modify') . '" /></div>
    </div>
	</form>';

include('includes/footer.inc');
?>
