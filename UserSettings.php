<?php
/* $Revision: 1.6 $ */
$title = "User Settings";

$PageSecurity=1;

include("includes/session.inc");
include("includes/header.inc");

if ($_POST['Modify']) {
	// no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if ($_POST['DisplayRecordsMax'] < 0){
		$InputError = 1;
		echo "<BR>" . _("The Maximum Number of Records on Display entered must not be negative.  0 will default to system setting");
	}
	if ($_POST['pass'] != ""){
		if ($_POST['pass'] != $_POST['passcheck']){
			$InputError = 1;
			echo "<BR>" ._("Sorry the passwords you entered do not match");
		}else{
			$update_pw = "Y";
		}
	}
		if ($_POST['passcheck'] != ""){
		if ($_POST['pass'] != $_POST['passcheck']){
			$InputError = 1;
			echo "<BR>" ._("Sorry the passwords you entered do not match");
		}else{
			$update_pw = "Y";
		}
	}
		
	

	if ($InputError != 1) {
		// no errors
		if ($update_pw != "Y"){
		$sql = "UPDATE WWW_Users
				SET DisplayRecordsMax=" . $_POST['DisplayRecordsMax'] . ",
					Theme='" . $_POST['Theme'] . "',
					Language='" . $_POST['Language'] . "',
					Email='". $_POST['email'] ."'
				WHERE UserID = '" . $_SESSION['UserID'] . "'";

		$ErrMsg =  _("The user alterations could not be processed because");
		$DbgMsg = _("The SQL that was used to update the user and failed was");

		$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);


		echo "<BR>" . _("The user settings have been updated.  Be Sure to Remeber your Password for the next time you LogIn!");
		}else{
				$sql = "UPDATE WWW_Users
				SET DisplayRecordsMax=" . $_POST['DisplayRecordsMax'] . ",
					Theme='" . $_POST['Theme'] . "',
					Language='" . $_POST['Language'] . "',
					Email='". $_POST['email'] ."',
					Password='" . $_POST['pass'] . "'
				WHERE UserID = '" . $_SESSION['UserID'] . "'";

		$ErrMsg =  _("The user alterations could not be processed because");
		$DbgMsg = _("The SQL that was used to update the user and failed was");

		$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);


		echo "<BR>" . _("The user settings have been updated.");
		}
	  // update the session variables to reflect user changes on-the-fly
		$_SESSION['DisplayRecordsMax'] = $_POST['DisplayRecordsMax'];
		$_SESSION['Theme'] = trim($_POST['Theme']); /*already set by session.inc but for completeness */
		$theme = $_SESSION['Theme'];
		$_SESSION['Language'] = trim($_POST['Language']);

	}
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

If (!isset($_POST['DisplayRecordsMax'])) {

  $_POST['DisplayRecordsMax'] = $_SESSION['DisplayRecordsMax'];

}

echo "<CENTER><TABLE><TR><TD>" . _("User ID:") . "</TD><TD>";
echo $_SESSION['UserID'] . "</TD></TR>";

echo "<TR><TD>" . _("User Name:") . "</TD><TD>";
echo $_SESSION['UsersRealName'] . "</TD></TR>";

echo "<TR>
	<TD>" . _("Maximum Number of Records to Display:") . "</TD>
	<TD><INPUT TYPE='Text' name='DisplayRecordsMax' VALUE=" . $_POST['DisplayRecordsMax'] . " SIZE=32 MAXLENGTH=30></TD>
	</TR>
	<TR>
	<TD>" . _("Language:") . "</TD>
	<TD><SELECT name='Language'>";


$LangDirHandle = dir("locale/");


while (false != ($LanguageEntry = $LangDirHandle->read())){

	if (is_dir("locale/" . $LanguageEntry) AND $LanguageEntry != ".." AND $LanguageEntry != "CVS" AND $LanguageEntry!="."){

		if ($_SESSION['Language'] == $LanguageEntry){
			echo "<OPTION SELECTED VALUE='$LanguageEntry'>$LanguageEntry";
		} else {
			echo "<OPTION VALUE='$LanguageEntry'>$LanguageEntry";
		}
	}
}

echo "</SELECT></TD>
</TR>
<TR>
	<TD>" . _("Theme:") . "</TD>
	<TD><SELECT name='Theme'>";

$ThemeDirectory = dir("css/");


while (false != ($ThemeName = $ThemeDirectory->read())){

	if (is_dir("css/$ThemeName") AND $ThemeName != "." AND $ThemeName != ".." AND $ThemeName != "CVS"){

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
	<tr><td colspan=2 align='center'>" . _('If you leave the password boxes empty, your password will not change') . '</td></tr>
	<TR><TD>' . _('Email') . ':</TD>';

$sql = "SELECT Email from WWW_Users WHERE UserID = '" . $_SESSION['UserID'] . "'";
$result = DB_query($sql,$db);
$myrow = DB_fetch_array($result);
if(!isset($_POST['email'])){
	$_POST['email'] = $myrow['Email'];
}

echo "<TD><input type=text name='email' size=20 value='" . $_POST['email'] . "'></TD></TR>
	</TABLE>
	<CENTER><input type='Submit' name='Modify' value=" . _('Modify') . '>
	</FORM>';

include("includes/footer.inc");

?>