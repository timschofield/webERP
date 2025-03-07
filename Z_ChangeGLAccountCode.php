<?php
/* Utility to change a GL account code in all webERP. */

/**************************************************************************************
KL RICARD MODIFICATIONS:
- Use the fucntion ChangeGLAcoountCode
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
		DB_Txn_Begin();
	ChangeGLAcoountCode ($_POST['NewAccountCode'], $_POST['OldAccountCode']);

		DB_Txn_Commit();

}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
	<legend>', _('GEneral Ledger Code To Change'), '</legend>
	<field>
		<label>' . _('Existing GL Account Code') . ':</label>
		<input type="text" name="OldAccountCode" size="20" maxlength="20" />
	</field>
	<field>
		<label>' . _('New GL Account Code') . ':</label>
		<input type="text" name="NewAccountCode" size="20" maxlength="20" />
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="ProcessGLAccountCode" value="' . _('Process') . '" />
	</div>
	</form>';

include('includes/footer.php');
?>