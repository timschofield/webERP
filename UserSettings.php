<?php

/* $Revision: 1.23 $ */

$PageSecurity=1;

include('includes/session.inc');
$title = _('User Settings');
include('includes/header.inc');

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
				SET displayrecordsmax=" . $_POST['DisplayRecordsMax'] . ",
					theme='" . $_POST['Theme'] . "',
					language='" . $_POST['Language'] . "',
					email='". $_POST['email'] ."'
				WHERE userid = '" . $_SESSION['UserID'] . "'";

			$ErrMsg =  _('The user alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

			prnMsg( _('The user settings have been updated') . '. ' . _('Be sure to remember your password for the next time you login'),'success');
		} else {
			$sql = "UPDATE www_users
				SET displayrecordsmax=" . $_POST['DisplayRecordsMax'] . ",
					theme='" . $_POST['Theme'] . "',
					language='" . $_POST['Language'] . "',
					email='". $_POST['email'] ."',
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
		
		include ('includes/LanguageSetup.php');

	}
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

If (!isset($_POST['DisplayRecordsMax']) OR $_POST['DisplayRecordsMax']=='') {

  $_POST['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax'];

}

echo '<CENTER><TABLE><TR><TD>' . _('User ID') . ':</TD><TD>';
echo $_SESSION['UserID'] . '</TD></TR>';

echo '<TR><TD>' . _('User Name') . ':</TD><TD>';
echo $_SESSION['UsersRealName'] . '</TD></TR>';

echo '<TR>
	<TD>' . _('Maximum Number of Records to Display') . ":</TD>
	<TD><INPUT TYPE='Text' name='DisplayRecordsMax' SIZE=3 MAXLENGTH=3 VALUE=" . $_POST['DisplayRecordsMax'] . " ></TD>
	</TR>";
	
	
echo '<TR>
	<TD>' . _('Language') . ":</TD>
	<TD><SELECT name='Language'>";

	$LangDirHandle = dir('locale/');


	while (false != ($LanguageEntry = $LangDirHandle->read())){
	
		if (is_dir('locale/' . $LanguageEntry) AND $LanguageEntry != '..' AND $LanguageEntry != 'CVS' AND $LanguageEntry!='.'){
	
			if ($_SESSION['Language'] == $LanguageEntry){
				echo "<OPTION SELECTED VALUE='$LanguageEntry'>$LanguageEntry";
			} else {
				echo "<OPTION VALUE='$LanguageEntry'>$LanguageEntry";
			}
		}
	}
	
	echo '</SELECT></TD></TR>';

	
echo '<TR>
	<TD>' . _('Theme') . ":</TD>
	<TD><SELECT name='Theme'>";

$ThemeDirectory = dir('css/');


while (false != ($ThemeName = $ThemeDirectory->read())){

	if (is_dir("css/$ThemeName") AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != 'CVS'){

		if ($_SESSION['Theme'] == $ThemeName){
			echo "<OPTION SELECTED VALUE='$ThemeName'>$ThemeName";
		} else {
			echo "<OPTION VALUE='$ThemeName'>$ThemeName";
		}
	}
}

echo '</SELECT></TD></TR>
	<TR><TD>' . _('New Password') . ":</TD>
	<TD><input type='password' name='pass' size=20 value='" .  $_POST['pass'] . "'></TD></TR>
	<TR><TD>" . _('Confirm Password') . ":</TD>
	<TD><input type='password' name='passcheck' size=20  value='" . $_POST['passcheck'] . "'></TD></TR>
	<tr><td colspan=2 align='center'><i>" . _('If you leave the password boxes empty your password will not change') . '</i></td></tr>
	<TR><TD>' . _('Email') . ':</TD>';

$sql = "SELECT email from www_users WHERE userid = '" . $_SESSION['UserID'] . "'";
$result = DB_query($sql,$db);
$myrow = DB_fetch_array($result);
if(!isset($_POST['email'])){
	$_POST['email'] = $myrow['email'];
}

echo "<TD><input type=text name='email' size=40 value='" . $_POST['email'] . "'></TD></TR>
	</TABLE></CENTER>
	<CENTER><input type='Submit' name='Modify' value=" . _('Modify') . '></CENTER>
	</FORM>';

include('includes/footer.inc');

?>