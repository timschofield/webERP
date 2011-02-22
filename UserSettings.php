<?php

/* $Id$*/

include('includes/session.inc');
$title = _('User Settings');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/user.png" title="' .
	_('User Settings') . '" alt="" />' . ' ' . _('User Settings') . '</p>';

$PDFLanguages = array(_('Latin Western Languages'),
											_('Eastern European Russian Japanese Korean Hebrew Arabic Thai'),
											_('Chinese'));


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
 /*
	if ($_POST['pass'] != ''){
		$InputError = 1;
		prnMsg(_('Cannot change password in the demo or others would be locked out!'),'warn');
	}
 */
 	$update_pw = 'N';
	if ($_POST['pass'] != ''){
		if ($_POST['pass'] != $_POST['passcheck']){
			$InputError = 1;
			prnMsg(_('The password and password confirmation fields entered do not match'),'error');
		}else{
			$update_pw = 'Y';
		}
	}
	if ($_POST['passcheck'] != ''){
		if ($_POST['pass'] != $_POST['passcheck']){
			$InputError = 1;
			prnMsg(_('The password and password confirmation fields entered do not match'),'error');
		}else{
			$update_pw = 'Y';
		}
	}

	if ($InputError != 1) {
		// no errors
		if ($update_pw != 'Y'){
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
					password='" . CryptPass($_POST['pass']) . "'
				WHERE userid = '" . $_SESSION['UserID'] . "'";

			$ErrMsg =  _('The user alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

			prnMsg(_('The user settings have been updated'),'success');
		}
	  // update the session variables to reflect user changes on-the-fly
		$_SESSION['DisplayRecordsMax'] = $_POST['DisplayRecordsMax'];
		$_SESSION['Theme'] = trim($_POST['Theme']); /*already set by session.inc but for completeness */
		$theme = $_SESSION['Theme'];
		$_SESSION['Language'] = trim($_POST['Language']);
		$_SESSION['PDFLanguage'] = $_POST['PDFLanguage'];
		include ('includes/LanguageSetup.php');

	}
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

If (!isset($_POST['DisplayRecordsMax']) OR $_POST['DisplayRecordsMax']=='') {

  $_POST['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax'];

}

echo '<table class=selection><tr><td>' . _('User ID') . ':</td><td>';
echo $_SESSION['UserID'] . '</td></tr>';

echo '<tr><td>' . _('User Name') . ':</td><td>';
echo $_SESSION['UsersRealName'] . '</td>
		<input type="hidden" name="RealName" VALUE="'.$_SESSION['UsersRealName'].'"<td></tr>';

echo '<tr>
	<td>' . _('Maximum Number of Records to Display') . ":</td>
	<td><input type='Text' class='number' name='DisplayRecordsMax' size=3 maxlength=3 VALUE=" . $_POST['DisplayRecordsMax'] . " ></td>
	</tr>";


echo '<tr>
	<td>' . _('Language') . ":</td>
	<td><select name='Language'>";

	$LangDirHandle = dir('locale/');


	while (false != ($LanguageEntry = $LangDirHandle->read())){

		if (is_dir('locale/' . $LanguageEntry)
				AND $LanguageEntry != '..'
				AND $LanguageEntry != 'CVS'
				AND $LanguageEntry != '.svn'
				AND $LanguageEntry!='.'){

			if ($_SESSION['Language'] == $LanguageEntry){
				echo '<option selected value="' . $LanguageEntry . '">' . $LanguageEntry . '</option>';
			} else {
				echo '<option value="' . $LanguageEntry . '">' . $LanguageEntry . '</option>';
			}
		}
	}

	echo '</select></td></tr>';


echo '<tr>
	<td>' . _('Theme') . ":</td>
	<td><select name='Theme'>";

$ThemeDirectory = dir('css/');


while (false != ($ThemeName = $ThemeDirectory->read())){

	if (is_dir("css/$ThemeName") AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != '.svn'){

		if ($_SESSION['Theme'] == $ThemeName){
			echo '<option selected value="' . $ThemeName . '">' . $ThemeName . '</option>';
		} else {
			echo '<option value="' . $ThemeName . '">' . $ThemeName . '</option>';
		}
	}
}

if (!isset($_POST['passcheck'])) {
	$_POST['passcheck']='';
}
if (!isset($_POST['pass'])) {
	$_POST['pass']='';
}
echo '</select></td></tr>
	<tr><td>' . _('New Password') . ":</td>
	<td><input type='password' name='pass' size=20 value='" .  $_POST['pass'] . "'></td></tr>
	<tr><td>" . _('Confirm Password') . ":</td>
	<td><input type='password' name='passcheck' size=20  value='" . $_POST['passcheck'] . "'></td></tr>
	<tr><td colspan=2 align='center'><i>" . _('If you leave the password boxes empty your password will not change') . '</i></td></tr>
	<tr><td>' . _('Email') . ':</td>';

$sql = "SELECT email from www_users WHERE userid = '" . $_SESSION['UserID'] . "'";
$result = DB_query($sql,$db);
$myrow = DB_fetch_array($result);
if(!isset($_POST['email'])){
	$_POST['email'] = $myrow['email'];
}

echo "<td><input type=text name='email' size=40 value='" . $_POST['email'] . "'></td></tr>";

if (!isset($_POST['PDFLanguage'])){
	$_POST['PDFLanguage']=$_SESSION['PDFLanguage'];
}

echo '<tr><td>' . _('PDF Language Support') . ': </td><td><select name="PDFLanguage">';
for($i=0;$i<count($PDFLanguages);$i++){
	if ($_POST['PDFLanguage']==$i){
		echo '<option selected value=' . $i .'>' . $PDFLanguages[$i] . '</option>';
	} else {
		echo '<option value=' . $i .'>' . $PDFLanguages[$i]. '</option>';
	}
}
echo "</select></td></tr></table>
	<br /><div class='centre'><input type='Submit' name='Modify' value=" . _('Modify') . '></div>
	</form>';

include('includes/footer.inc');

?>