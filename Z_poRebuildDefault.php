<?php
/*	$Id: Z_poRebuildDefault.php 6810 2014-08-13 02:55:08Z rchacon $*/
/*	This script runs xgettext on the sources to produce a .pot (Portable Object 
	Template) file, which contains a list of all the translatable strings 
	extracted from the sources. The resultant system default language file 
	(.pot file) is saved in the .../locale/en_GB.utf8/LC_MESSAGES/messages.po 
	path. Note: Comments (starting with ///) placed directly before strings 
	thus marked are made available as hints to translators by helper programs.*/

/* Steve Kitchen */

//$PageSecurity = 15;

include ('includes/session.inc');
$Title = _('Rebuild the System Default Language File');
$ViewTopic = 'SpecialUtilities';// Filename in ManualContents.php's TOC.
$BookMark = 'Z_poRebuildDefault';// Anchor's id in the manual's html document.
include('includes/header.inc');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme . 
		'/images/maintenance.png" title="' . 
		_('Rebuild the System Default Language File') . '" />' . ' ' . 
		_('Rebuild the System Default Language File') . '</p>';

/* Your webserver user MUST have read/write access to here,	otherwise you'll be wasting your time */

echo '<br />&nbsp;<a href="' . $RootPath . '/Z_poAdmin.php">' . _('Back to the translation menu') . '</a>';
echo '<br /><br />&nbsp;' . _('Utility page to rebuild the system default language file');

$PathToDefault = './locale/en_GB.utf8/LC_MESSAGES/messages.pot';
$FilesToInclude = '*.php api/*.php includes/*.inc includes/*.php install/*.php reportwriter/languages/en_US/reports.php';
$xgettextCmd = 'xgettext --no-wrap --from-code=utf-8 -L php -o ' . $PathToDefault . ' ' . $FilesToInclude;

if (isset($_POST['submit'])) {
	echo '<br /><table><tr><td>';
	echo '<form method="post" action=' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?' . SID . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	prnMsg (_('Rebuilding the default language file ') . '.....<br />', 'info', ' ');
	$Result = rename($PathToDefault, $PathToDefault . '.old');// Renames pot file to bak.
	system($xgettextCmd);// Runs xgettext to recreate the default message.po language file.
	prnMsg (_('Done') .  '. ' . _('You should now edit the default language file header') . '<br />', 'info', ' ');
	echo "<div class='centre'><a href='" . $RootPath . "/Z_poAdmin.php'>" . _('Back to the menu') . "</a></div>";
	echo '</form>';
	echo '</td></tr></table>';

} else {
	/* set up the page for editing */
	echo '<div class="centre">';
	echo '<br />';
	prnMsg (_('Every new language creates a new translation file from the system default one') . '.<br />' .
          _('This utility will recreate the system default language file by going through all the script files to get all the strings') . '.<br />' .
          _('This is not usually necessary but if done before a new language is created then that language will have any new or recently modified strings') . '.<br />' .
          _('Existing languages are not affected.') . '.', 'info', _('PLEASE NOTE'));
	echo '<br />';
	echo '<form method="post" action=' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?' . SID . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="submit" name="submit" value="' . _('Proceed') . '" />&nbsp;&nbsp;';
	echo '</form>';
	echo '</div>';
}

include('includes/footer.inc');
?>
