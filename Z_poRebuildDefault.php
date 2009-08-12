<?php
/* $Revision: 1.7 $ */

/* Steve Kitchen */

$PageSecurity = 15;

include ('includes/session.inc');

$title = _('Rebuild');

include('includes/header.inc');

/* Your webserver user MUST have read/write access to here, 
	otherwise you'll be wasting your time */
	
$PathToDefault		= './locale/en_GB/LC_MESSAGES/messages.po';
$FilesToInclude = '*.php includes/*.inc includes/*.php api/*.php reportwriter/languages/en_US/reports.php';
$xgettextCmd		= 'xgettext --no-wrap -L php -o ' . $PathToDefault . ' ' . $FilesToInclude;

echo "<br>&nbsp;<a href='" . $rootpath . "/Z_poAdmin.php'>" . _('Back to the translation menu') . "</a>";
echo '<br><br>&nbsp;' . _('Utility page to rebuild the system default language file');

if (isset($_POST['submit'])) {

	echo '<br><table><tr><td>';
	echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

/* Run xgettext to recreate the default message.po language file */

	prnMsg (_('Rebuilding the default language file ') . '.....<br>', 'info', ' ');

	system($xgettextCmd);	

	prnMsg (_('Done') .  '. ' . _('You should now edit the default language file header') . '<br>', 'info', ' ');

	echo "<div class='centre'><a href='" . $rootpath . "/Z_poAdmin.php'>" . _('Back to the menu') . "</a></div>";
	echo '</form>';
	echo '</td></tr></table>';
	
} else {		/* set up the page for editing */

	echo '<div class="centre">';
	echo '<br>';
	prnMsg (_('Every new language creates a new translation file from the system default one') . '.<br>' .
          _('This utility will recreate the system default language file by going through all the script files to get all the strings') . '.<br>' .
          _('This is not usually necessary but if done before a new language is created then that language will have any new or recently modified strings') . '.<br>' .
          _('Existing languages are not affected.') . '.', 'info', _('PLEASE NOTE'));
	echo '<br>';
	echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	echo '<input type="Submit" name="submit" VALUE="' . _('Proceed') . '">&nbsp;&nbsp;';
	echo '</form>';
	echo '</div>';

}

include('includes/footer.inc');

?>
