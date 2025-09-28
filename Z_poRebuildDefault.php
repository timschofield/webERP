<?php

/*	This script runs xgettext on the sources to produce a .pot (Portable Object
	Template) file, which contains a list of all the translatable strings
	extracted from the sources. The resultant system default language file
	(.pot file) is saved in the .../locale/en_GB.utf8/LC_MESSAGES/messages.po
	path. Note: Comments (starting with ///) placed directly before strings
	thus marked are made available as hints to translators by helper programs.*/

/* Steve Kitchen */

require(__DIR__ . '/includes/session.php');

$Title = __('Rebuild the System Default Language File');
$ViewTopic = 'SpecialUtilities';// Filename in ManualContents.php's TOC.
$BookMark = 'Z_poRebuildDefault';// Anchor's id in the manual's html document.
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('Rebuild the System Default Language File') . '" />' . ' ' .
		__('Rebuild the System Default Language File') . '</p>';

/* Your webserver user MUST have read/write access to here,	otherwise you'll be wasting your time */

echo '<br />&nbsp;<a href="' . $RootPath . '/Z_poAdmin.php">' . __('Back to the translation menu') . '</a>';
echo '<br /><br />&nbsp;' . __('Utility page to rebuild the system default language file');

$PathToDefault = './locale/en_GB.utf8/LC_MESSAGES/messages.pot';
/// @todo this list should be updated - look at the one in update_translations.sh
$FilesToInclude = '*.php api/*.php includes/*.php includes/*.php install/*.php reportwriter/languages/en_US/reports.php';
/// @todo escape args
$xgettextCmd = 'xgettext --no-wrap --from-code=utf-8 -L php -o ' . $PathToDefault . ' ' . $FilesToInclude;

if (isset($_POST['submit'])) {
	echo '<br /><table><tr><td>';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	prnMsg(__('Rebuilding the default language file ') . '.....<br />', 'info', ' ');
	$Result = rename($PathToDefault, $PathToDefault . '.old');// Renames pot file to bak.
	/// @todo check for failures
	exec($xgettextCmd, $output, $result);// Runs xgettext to recreate the default message.po language file.
	prnMsg(__('Done') .  '. ' . __('You should now edit the default language file header') . '<br />', 'info', ' ');
	echo "<div class='centre'><a href='" . $RootPath . "/Z_poAdmin.php'>" . __('Back to the menu') . "</a></div>";
	echo '</form>';
	echo '</td></tr></table>';

} else {
	/* set up the page for editing */
	echo '<div class="centre">';
	echo '<br />';
	prnMsg(__('Every new language creates a new translation file from the system default one') . '.<br />' .
          __('This utility will recreate the system default language file by going through all the script files to get all the strings') . '.<br />' .
          __('This is not usually necessary but if done before a new language is created then that language will have any new or recently modified strings') . '.<br />' .
          __('Existing languages are not affected.') . '.', 'info', __('PLEASE NOTE'));
	echo '<br />';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="submit" name="submit" value="' . __('Proceed') . '" />&nbsp;&nbsp;';
	echo '</form>';
	echo '</div>';
}

include('includes/footer.php');
