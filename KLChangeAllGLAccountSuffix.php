<?php

require(__DIR__ . '/includes/session.php');

$Title = __('UTILITY PAGE Change ALL GL Account Code SUFFIX');// Screen identificator.
$ViewTopic = 'SpecialUtilities';// Filename's id in ManualContents.php's TOC.
$BookMark = ''; // Anchor's id in the manual's html document.
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/SQL_CommonFunctions.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/gl.png" title="',// Icon image.
	__('Change ALL GL Account Code SUFFIX'), '" /> ',// Icon title.
	__('Change ALL GL Account Code SUFFIX'), '</p>';// Page title.

if (isset($_POST['ProcessGLAccountCode'])) {
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
			ChangeGLAccountCode ($NewGL, $OldGL);
		}
	}
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<fieldset>
        <legend>' . __('GL Account Code Changes') . '</legend>
        <field>
            <label for="OldSuffix">' . __('GL Account Code Suffix') . ':</label>
            <input type="text" name="OldSuffix" size="2" maxlength="2" />
            <fieldhelp>' . __('Enter the existing GL account code suffix to change') . '</fieldhelp>
        </field>
        <field>
            <label for="NewSuffix">' . __('New Account Code Suffix') . ':</label>
            <input type="text" name="NewSuffix" size="2" maxlength="2" />
            <fieldhelp>' . __('Enter the new GL account code suffix') . '</fieldhelp>
        </field>
    </fieldset>

    <input type="submit" name="ProcessGLAccountCode" value="' . __('Process') . '" />
	</div>
	</form>';

include(__DIR__ . '/includes/footer.php');

