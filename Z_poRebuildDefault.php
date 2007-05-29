<?php
/* $Revision: 1.5 $ */

/* Steve Kitchen */

$PageSecurity = 15;

include ('includes/session.inc');

$title = _('Rebuild');

include('includes/header.inc');

/* Your webserver user MUST have read/write access to here, 
	otherwise you'll be wasting your time */
	
$PathToDefault		= './locale/en_GB/LC_MESSAGES/messages.po';
$FilesToInclude	= '*php includes/*.php includes/*.inc';
$xgettextCmd		= 'xgettext --no-wrap -L php -o ' . $PathToDefault . ' ' . $FilesToInclude;

echo "<BR>&nbsp;<A HREF='" . $rootpath . "/Z_poAdmin.php'>" . _('Back to the translation menu') . "</A>";
echo '<BR><BR>&nbsp;' . _('Utility page to rebuild the system default language file');

if (isset($_POST['submit'])) {

	echo '<CENTER>';
	echo '<BR><TABLE><TR><TD>';
	echo '<FORM METHOD="post" ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

/* Run xgettext to recreate the default message.po language file */

	prnMsg (_('Rebuilding the default language file ') . '.....<BR>', 'info', ' ');

	system($xgettextCmd);	

	prnMsg (_('Done') .  '. ' . _('You should now edit the default language file header') . '<BR>', 'info', ' ');

	echo "<CENTER><A HREF='" . $rootpath . "/Z_poAdmin.php'>" . _('Back to the menu') . "</A></CENTER>";
	echo '</FORM>';
	echo '</TD></TR></TABLE>';
	echo '</CENTER>';
	
} else {		/* set up the page for editing */

	echo '<CENTER>';
	echo '<BR>';
	prnMsg (_('Every new language creates a new translation file from the system default one') . '.<BR>' .
          _('This utility will recreate the system default language file by going through all the script files to get all the strings') . '.<BR>' .
          _('This is not usually necessary but if done before a new language is created then that language will have any new or recently modified strings') . '.<BR>' .
          _('Existing languages are not affected.') . '.', 'info', _('PLEASE NOTE'));
	echo '<BR>';
	echo '<FORM METHOD="post" ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	echo '<INPUT TYPE="Submit" NAME="submit" VALUE="' . _('Proceed') . '">&nbsp;&nbsp;';
	echo '</FORM>';
	echo '</CENTER>';

}

include('includes/footer.inc');

?>
