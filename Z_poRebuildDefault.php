<?php
/* $Revision: 1.1 $ */

/* Steve Kitchen */

$PageSecurity = 15;

include ('includes/session.inc');

/* Was the Cancel button pressed the last time through ? */

if (isset($_POST['cancel'])) {

	header ('Location:' . $rootpath . '/Z_poAdmin.php?' . SID);
	exit;

}

$title = _('UTILITY PAGE') . ' ' . _('to rebuild the default language file');

include('includes/header.inc');

$DefaultLanguage = 'en';		// the default language IS English ...

/* Your webserver user MUST have read/write access to here, 
	otherwise you'll be wasting your time */
	
$PathToDefault		= './locale/' . $DefaultLanguage . '/LC_MESSAGES/messages.po';
$FilesToInclude	= '*php includes/*.php includes/*.inc';
$xgettextCmd		= 'xgettext -s --no-wrap -L php -o ' . $PathToDefault . ' ' . $FilesToInclude;

if (isset($_POST['submit'])) {

	echo '<CENTER>';
	echo '<BR><TABLE><TR><TD>';
	echo '<FORM METHOD="post" ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

/* Run xgettext to recreate the default message.po language file */

	prnMsg (_('Rebuilding the default language file ') . '.....<BR>', 'info', ' ');

	system($xgettextCmd);	

	prnMsg (_('Done') .  '. ' . _('You should now edit the default language file header') . '<BR>', 'info', ' ');

	echo '<CENTER><INPUT TYPE="Submit" NAME="cancel" VALUE="' . _('Back to the menu') . '"></CENTER>';
	echo '</FORM>';
	echo '</TD></TR></TABLE>';
	echo '</CENTER>';
	
} else {		/* set up the page for editing */

	echo '<CENTER>';
	echo '<BR>';
	prnMsg (_('This utility will recreate the default messages.po file') . '<BR><BR>' . 
			_('Make sure you know what you are doing BEFORE you run this procedure'), 'info', _('PLEASE NOTE'));
	echo '<BR>';
	echo '<FORM METHOD="post" ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	prnMsg (_('Once you click on the Proceed button the file will be recreated') . '. ' . _('You will not get a second warning'), 'warn', _('WARNING'));
	echo '<INPUT TYPE="Submit" NAME="submit" VALUE="' . _('Proceed') . '">&nbsp;&nbsp;';
	echo '<INPUT TYPE="Submit" NAME="cancel" VALUE="' . _('Back to the menu') . '">';
	echo '</FORM>';
	echo '</CENTER>';

}

include('includes/footer.inc');

?>
