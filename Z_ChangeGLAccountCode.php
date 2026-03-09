<?php

/* Utility to change a GL account code in all webERP. */

/**************************************************************************************
KL RICARD MODIFICATIONS:
- Use the function ChangeGLAccountCode
- change the account code also in KL tables using this field (regular and archive DB)
***************************************************************************************/

require(__DIR__ . '/includes/session.php');

$Title = __('UTILITY PAGE Change A GL Account Code');
$ViewTopic = 'SpecialUtilities';
$BookMark = 'Z_ChangeGLAccountCode';
include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/gl.png" title="',// Icon image.
	__('Change A GL Account Code'), '" /> ',// Icon title.
	__('Change A GL Account Code'), '</p>';// Page title.

include(__DIR__ . '/includes/SQL_CommonFunctions.php');

// RICARD KL: Use the function ChangeGLAccountCode and connection to Archive DB
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/ArchiveConnectDB.php');
// RICARD KL END: Use the function ChangeGLAccountCode and connection to Archive DB

if (isset($_POST['ProcessGLAccountCode'])) {

	$InputError =0;

	$_POST['NewAccountCode'] = mb_strtoupper($_POST['NewAccountCode']);
	DB_Txn_Begin();
	ChangeGLAccountCode($_POST['NewAccountCode'], $_POST['OldAccountCode']);
	DB_Txn_Commit();

}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
	<legend>', __('General Ledger Code To Change'), '</legend>
	<field>
		<label>' . __('Existing GL Account Code') . ':</label>
		<input type="text" name="OldAccountCode" size="20" maxlength="20" />
	</field>
	<field>
		<label>' . __('New GL Account Code') . ':</label>
		<input type="text" name="NewAccountCode" size="20" maxlength="20" />
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="ProcessGLAccountCode" value="' . __('Process') . '" />
	</div>
	</form>';

include(__DIR__ . '/includes/footer.php');
