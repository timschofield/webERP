<?php
/* $Revision: 1.2 $ */

/* Steve Kitchen */

$PageSecurity = 15;

include ('includes/session.inc');

/* Was the Cancel button pressed the last time through ? */

if (isset($_POST['cancel'])) {

	header ('Location:' . $rootpath . '/Z_poAdmin.php?' . SID);
	exit;

}

$title = _('UTILITY PAGE') . ' ' . _('To Create A New Language File');

include('includes/header.inc');

$DefaultLanguage = 'en_GB';		// the default language IS English ...

/* Your webserver user MUST have read/write access to here,
	otherwise you'll be wasting your time */

$PathToDefault		= './locale/' . $DefaultLanguage . '/LC_MESSAGES/messages.po';

if (isset($_POST['submit']) AND isset($_POST['NewLanguage'])) {
	
	if(strlen($_POST['NewLanguage'])<5 OR
		substr($_POST['NewLanguage'],2,1)!='_'){
		prnMsg(_('Languages must be in the format of a two character country code an underscore _ and a two character language code in upper case'),'error');
	} else {
		
		/*Make sure the language characters are in upper case*/
		
		$_POST['NewLanguage'] = substr($_POST['NewLanguage'],0,3) . strtoupper(substr($_POST['NewLanguage'],3,2));
		
		echo '<CENTER>';
		echo '<BR><TABLE><TR><TD>';
		echo '<FORM METHOD="post" ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	
		prnMsg (_('Creating the new language file') . '.....<BR>', 'info', ' ');
	
	/* check for directory existence */
	
		if (!file_exists('./locale/' . $_POST['NewLanguage'])) {
			$Result = mkdir('./locale/' . $_POST['NewLanguage']);
		}
		if (!file_exists('./locale/' . $_POST['NewLanguage'] . '/LC_MESSAGES')) {
			$Result = mkdir('./locale/' . $_POST['NewLanguage'] . '/LC_MESSAGES');
		}
	
		$PathToNewLanguage = './locale/' . $_POST['NewLanguage'] . '/LC_MESSAGES/messages.po';
		$Result = copy($PathToDefault, $PathToNewLanguage);
	
		prnMsg (_('Done') . '. ' . _('You should now edit the new language file header') . '<BR>' .
				_('Then use the language module editor to provide the content'), 'info', ' ');
	
		echo '<CENTER><INPUT TYPE="Submit" NAME="cancel" VALUE="' . _('Back to the menu') . '"></CENTER>';
		echo '</FORM>';
		echo '</TD></TR></TABLE>';
		echo '</CENTER>';
		include('includes/footer.inc');
		exit;
	}

}


echo '<CENTER>';
echo '<BR>';
prnMsg (_('This utility will create a new language file from your default messages.po') . '<BR><BR>' .
		_('If the file already exists it will be overwritten'), 'info', _('PLEASE NOTE'));
echo '<BR>';
echo '<FORM METHOD="post" ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

echo '<TABLE><TR>';
echo '<TD>' . _('Full code of the new language in the format en_US') . '</TD>';
echo '<TD><INPUT TYPE="text" SIZE="5" NAME="NewLanguage">';
echo '</TD></TR></TABLE>';

prnMsg (_('Once you click on the Proceed button the file will be written') . '. ' . _('You will not get a second chance'), 'warn', _('WARNING'));
echo '<INPUT TYPE="Submit" NAME="submit" VALUE="' . _('Proceed') . '">&nbsp;&nbsp;';
echo '<INPUT TYPE="Submit" NAME="cancel" VALUE="' . _('Back to the menu') . '">';
echo '</FORM>';
echo '</CENTER>';


include('includes/footer.inc');

?>
