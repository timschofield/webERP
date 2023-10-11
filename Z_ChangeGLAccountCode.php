<?php
/* $Id: Z_ChangeGLAccountCode.php 6946 2014-10-27 07:30:11Z daintree $*/
/* Utility to change a GL account code in all webERP. */

/**************************************************************************************
KL RICARD MODIFICATIONS:
- change the account code also in KL tables using this field
***************************************************************************************/

include ('includes/session.php');
$Title = _('UTILITY PAGE Change A GL Account Code');// Screen identificator.
$ViewTopic = 'SpecialUtilities';// Filename's id in ManualContents.php's TOC.
$BookMark = 'Z_ChangeGLAccountCode';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/gl.png" title="',// Icon image.
	_('Change A GL Account Code'), '" /> ',// Icon title.
	_('Change A GL Account Code'), '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');

if(isset($_POST['ProcessGLAccountCode'])) {
	$InputError =0;
	$_POST['NewAccountCode'] = mb_strtoupper($_POST['NewAccountCode']);
	ChangeGLAcoountCode ($_POST['NewAccountCode'], $_POST['OldAccountCode']);

}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br />
    <table>
	<tr>
		<td>' . _('Existing GL Account Code') . ':</td>
		<td><input type="text" name="OldAccountCode" size="20" maxlength="20" /></td>
	</tr>
	<tr>
		<td>' . _('New GL Account Code') . ':</td>
		<td><input type="text" name="NewAccountCode" size="20" maxlength="20" /></td>
	</tr>
	</table>

		<input type="submit" name="ProcessGLAccountCode" value="' . _('Process') . '" />
	</div>
	</form>';

include('includes/footer.php');
?>
