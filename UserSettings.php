<?php
/* $Revision: 1.1 $ */
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

	if ($InputError != 1) {
		// no errors

		$sql = "UPDATE WWW_Users
				SET DisplayRecordsMax=" . $_POST['DisplayRecordsMax'] . ",
					Theme='" . $_POST['Theme'] . "',
					Language='" . $_POST['Language'] . "'
				WHERE UserID = '" . $_SESSION['UserID'] . "'";

		$ErrMsg =  _("The user alterations could not be processed because");
		$DbgMsg = _("The SQL that was used to update the user and failed was");

		$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);


		echo "<BR>" . _("The user settings have been updated.");

	  // update the session variables to reflect user changes on-the-fly
		$_SESSION['DisplayRecordsMax'] = $_POST['DisplayRecordsMax'];
		$_SESSION['Theme'] = trim($_POST['Theme']); /*already set by session.inc but for completeness */
		$theme = $_SESSION['Theme'];
		$_SESSION['Language'] = trim($_POST['Language']);

	}
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

If (!isset($_POST['DisplayRecordsMax'])) {

  $_POST['DisplayRecordsMax'] = $DefaultDisplayRecordsMax;

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

?>

</SELECT></TD></TR>
</TABLE>
<CENTER><input type="Submit" name="Modify" value="Modify">

</FORM>

<?php include("includes/footer.inc"); ?>
