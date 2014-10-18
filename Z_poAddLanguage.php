<?php
/* $Id: Z_poAddLanguage.php 5784 2012-12-29 04:00:43Z daintree $*/

/* Steve Kitchen/Kaill */

//$PageSecurity = 15;

include ('includes/session.inc');

/* Was the Cancel button pressed the last time through ? */

if (isset($_POST['cancel'])) {

	header ('Location:' . $RootPath . '/Z_poAdmin.php?' . SID);
	exit;

}

$Title = _('New Language');

include('includes/header.inc');

$DefaultLanguage = 'en_GB';		// the default language IS English ...

/* Your webserver user MUST have read/write access to here,
	otherwise you'll be wasting your time */

$PathToDefault		= './locale/' . $DefaultLanguage . '/LC_MESSAGES/messages.po';

echo "<br />&nbsp;<a href='" . $RootPath . "/Z_poAdmin.php'>" . _('Back to the translation menu') . "</a>";
echo '<br /><br />&nbsp;' . _('Utility to create a new language file');
echo '<br />&nbsp;' . _('Current language is') . ' ' . $_SESSION['Language'];

if (isset($_POST['submit']) AND isset($_POST['NewLanguage'])) {

	if(mb_strlen($_POST['NewLanguage'])<5
		OR mb_strlen($_POST['NewLanguage'])>5
		OR mb_substr($_POST['NewLanguage'],2,1)!='_'){

		prnMsg(_('Languages must be in the format of a two character country code an underscore _ and a two character language code in upper case'),'error');
	} else {

		/*Make sure the language characters are in upper case*/

		$_POST['NewLanguage'] = mb_substr($_POST['NewLanguage'],0,3) . mb_strtoupper(mb_substr($_POST['NewLanguage'],3,2));

		echo '<div class="centre">';
		echo '<br />';
		echo '<form method="post" action=' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?' . SID . '>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


		/* check for directory existence */

		if (!file_exists('./locale/' . $_POST['NewLanguage'])) {
			prnMsg (_('Attempting to create the new language file') . '.....<br />', 'info', ' ');
			$Result = mkdir('./locale/' . $_POST['NewLanguage']);
			$Result = mkdir('./locale/' . $_POST['NewLanguage'] . '/LC_MESSAGES');
		} else {
			prnMsg(_('This language cannot be added because it already exists!'),'error');
  			echo '</form>';
	  		echo '</div>';
			include('includes/footer.inc');
			exit;
		}

		$PathToNewLanguage = './locale/' . $_POST['NewLanguage'] . '/LC_MESSAGES/messages.po';
		$Result = copy($PathToDefault, $PathToNewLanguage);

		prnMsg (_('Done. You should now change to your newly created language from the user settings link above. Then you can edit the new language file header and use the language module editor to translate the system strings'), 'info');

		echo '</form>';
		echo '</div>';
		include('includes/footer.inc');
		exit;
	}

}


echo '<div class="centre">';
echo '<br />';
prnMsg (_('This utility will create a new language and a new language translation file for it from the system default') . '<br /><br />' .
		_('If the language already exists then you cannot recreate it'), 'info', _('PLEASE NOTE'));
echo '<br /></div>';
echo '<form method="post" action=' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?' . SID . '>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table><tr>';
echo '<td>' . _('Full code of the new language in the format en_US') . '</td>';
echo '<td><input type="text" size="5" name="NewLanguage" />';
echo '</td></tr></table>';

echo '<br /><input type="submit" name="submit" value="' . _('Proceed') . '" />&nbsp;&nbsp;&nbsp;&nbsp;';
echo '</form>';

include('includes/footer.inc');

?>