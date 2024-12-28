<?php

include ('includes/session.php');
$Title = _('UTILITY PAGE Change ALL GL Account Code SUFFIX');// Screen identificator.
$ViewTopic = 'SpecialUtilities';// Filename's id in ManualContents.php's TOC.
$BookMark = 'Z_ChangePTGLAccountCode';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/gl.png" title="',// Icon image.
	_('Change ALL GL Account Code SUFFIX'), '" /> ',// Icon title.
	_('Change ALL GL Account Code SUFFIX'), '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');

if(isset($_POST['ProcessGLAccountCode'])) {
	$InputError =0;
	$_POST['NewSuffix'] = mb_strtoupper($_POST['NewSuffix']);
	$LenghtOldSuffix = strlen($_POST['OldSuffix']);
	
	$SQL = "SELECT accountcode
			FROM chartmaster
			WHERE accountcode LIKE '%" . $_POST['OldSuffix'] . "'";
	
	$Result = DB_query($SQL);		
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			$OldGL = $MyRow['accountcode'];
			$NewGL = substr($OldGL, 0, strlen($OldGL) - $LenghtOldSuffix) . $_POST['NewSuffix'];
			prnMsg("Old GL: " . $OldGL . " New GL: " . $NewGL);
			ChangeGLAcoountCode ($NewGL, $OldGL);
		}
	}
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br />
    <table>
	<tr>
		<td>' . _('GL Account Code Suffix') . ':</td>
		<td><input type="text" name="OldSuffix" size="2" maxlength="2" /></td>
	</tr>
	<tr>
		<td>' . _('New Account Code Suffix') . ':</td>
		<td><input type="text" name="NewSuffix" size="2" maxlength="2" /></td>
	</tr>
	</table>

		<input type="submit" name="ProcessGLAccountCode" value="' . _('Process') . '" />
	</div>
	</form>';

include('includes/footer.php');
?>
