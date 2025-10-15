<?php

/* Utility to change a GL account code in all webERP. */

/**************************************************************************************
KL RICARD MODIFICATIONS:
- Use the fucntion ChangeGLAcoountCode
- change the account code also in KL tables using this field
***************************************************************************************/

require(__DIR__ . '/includes/session.php');

$Title = __('UTILITY PAGE Change A GL Account Code');
$ViewTopic = 'SpecialUtilities';
$BookMark = 'Z_ChangeGLAccountCode';
include('includes/header.php');

echo '<p class = "page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/gl.png" title = "',// Icon image.
	__('Change A GL Account Code'), '" /> ',// Icon title.
	__('Change A GL Account Code'), '</p>';// Page title.

include('includes/SQL_CommonFunctions.php');

// RICARD KL: Use the function ChangeGLAcoountCode
include('includes/KLGeneralFunctions.php');
// RICARD KL END: Use the function ChangeGLAcoountCode

if (isset($_POST['ProcessGLAccountCode'])) {

	$InputError =0;

	$_POST['NewAccountCode'] = mb_strtoupper($_POST['NewAccountCode']);
		DB_Txn_Begin();
	ChangeGLAcoountCode($_POST['NewAccountCode'], $_POST['OldAccountCode']);

		DB_Txn_Commit();

}

echo '<form action = "' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method = "post">';
echo '<div class = "centre">';
echo '<input type = "hidden" name = "FormID" value = "' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
	<legend>', __('General Ledger Code To Change'), '</legend>
	<field>
		<label>' . __('Existing GL Account Code') . ':</label>
		<input type = "text" name = "OldAccountCode" size = "20" maxlength = "20" />
	</field>
	<field>
		<label>' . __('New GL Account Code') . ':</label>
		<input type = "text" name = "NewAccountCode" size = "20" maxlength = "20" />
	</field>
	</fieldset>
	<div class = "centre">
		<input type = "submit" name = "ProcessGLAccountCode" value = "' . __('Process') . '" />
	</div>
	</form>';

include('includes/footer.php');
