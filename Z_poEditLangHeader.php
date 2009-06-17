<?php
/* $Revision: 1.7 $ */

/* Steve Kitchen */

$PageSecurity = 15;

include ('includes/session.inc');

$title = _('Edit Header');

include('includes/header.inc');

/* Your webserver user MUST have read/write access to here, 
	otherwise you'll be wasting your time */

echo "<br>&nbsp;<a href='" . $rootpath . "/Z_poAdmin.php'>" . _('Back to the translation menu') . "</a>";
echo '<br><br>&nbsp;' . _('Utility to edit a language file header');
echo '<br>&nbsp;' . _('Current language is') . ' ' . $_SESSION['Language'];
  
$PathToLanguage		= './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po';
$PathToNewLanguage	= './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po.new';

$fpIn = fopen($PathToLanguage, 'r');

for ($i=1; $i<=17; $i++){	/* message.po header is 17 lines long - this is easily broken */
	$LanguageHeader[$i] = htmlspecialchars(fgets($fpIn));
}

if (isset($_POST['submit'])) {

	echo '<br><table><tr><td>';
	echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

/* write the new header then the rest of the language file to a new file */

	prnMsg (_('Writing the language file header') . '.....<br>', 'info', ' ');
	
	$fpOut = fopen($PathToNewLanguage, 'w');

	for ($i=1; $i<=17; $i++) {
		$Result = fputs($fpOut, stripslashes($_POST['Header_'.$i])."\n");
	}

	prnMsg (_('Writing the rest of the language file') . '.....<br>', 'info', ' ');

	while (!feof($fpIn)) {
		$FileContents = fgets($fpIn);
		$Result = fputs($fpOut, $FileContents);
	}

	$Result = fclose($fpIn);
	$Result = fclose($fpOut);

/* Done writing, now move the original file to a .old */
/* and the new one to the default */

		if (file_exists($PathToLanguage . '.old')) {
			$Result = rename($PathToLanguage . '.old', $PathToLanguage . '.bak');
		}
		$Result = rename($PathToLanguage, $PathToLanguage . '.old');
		$Result = rename($PathToNewLanguage, $PathToLanguage);
		if (file_exists($PathToLanguage . '.bak')) {
			$Result = unlink($PathToLanguage . '.bak');
		}

	prnMsg (_('Done') . '<br>', 'info', ' ');

	echo '</form>';
	echo '</td></tr></table>';

} else {

	$Result = fclose($fpIn);

if (!is_writable('./locale/' . $_SESSION['Language'])) {
	prnMsg(_('You do not have write access to the required files please contact your system administrator'),'error');
}
else
{
  echo '<br><br>&nbsp;' . _('To change language click on the user name at the top left, change to language desired and click Modify');
  echo '<br>&nbsp;' . _('Make sure you have selected the correct language to translate!');
  echo '<br>&nbsp;' . _('When finished modifying you must click on Modify at the bottom in order to save changes');
	echo '<div class="centre">';
	echo '<br>';
	prnMsg (_('Your existing translation file (messages.po) will be backed up as messages.po.old') . '<br><br>' . 
				_('Make sure you know what you are doing BEFORE you edit the header'), 'info', _('PLEASE NOTE'));
	echo '<br></div>';
	echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	echo '<table><tr><th" colspan="2" ALIGN="center">'. _('Language File Header for') . ' "' . $_POST['language'] . '"</th></tr>';
	echo '<tr><td colspan="2"></td></tr>';

	for ($i=1; $i<=17; $i++) {

		echo '<tr>';
		echo '<td>' . _('Header Line') . ' # ' . $i . '</td>';
		echo '<td><input type="text" size="80" name="Header_' . $i . '" VALUE="' . $LanguageHeader[$i] . '"></td>';
		echo '</tr>';
	}

	echo '</table>';
	echo '<br><div class="centre"><input type="Submit" name="submit" VALUE="' . _('Modify') . '">&nbsp;&nbsp;';
	echo '<input type="hidden" name="language" VALUE="' . $_POST['language'] . '"></div>';
	echo '</form>';
}
}
include('includes/footer.inc');

?>