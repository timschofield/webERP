<?php
/* $Revision: 1.4 $ */

/* Steve Kitchen */

$PageSecurity = 15;

include ('includes/session.inc');

$title = _('Edit Header');

include('includes/header.inc');

/* Your webserver user MUST have read/write access to here, 
	otherwise you'll be wasting your time */

echo "<BR>&nbsp;<A HREF='" . $rootpath . "/Z_poAdmin.php'>" . _('Back to the translation menu') . "</A>";
echo '<BR><BR>&nbsp;' . _('Utility to edit a language file header');
echo '<BR>&nbsp;' . _('Current language is') . ' ' . $_SESSION['Language'];
  
$PathToLanguage		= './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po';
$PathToNewLanguage	= './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po.new';

$fpIn = fopen($PathToLanguage, 'r');

for ($i=1; $i<=17; $i++){	/* message.po header is 17 lines long - this is easily broken */
	$LanguageHeader[$i] = htmlspecialchars(fgets($fpIn));
}

if (isset($_POST['submit'])) {

	echo '<CENTER>';
	echo '<BR><TABLE><TR><TD>';
	echo '<FORM METHOD="post" ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

/* write the new header then the rest of the language file to a new file */

	prnMsg (_('Writing the language file header') . '.....<BR>', 'info', ' ');
	
	$fpOut = fopen($PathToNewLanguage, 'w');

	for ($i=1; $i<=17; $i++) {
		$Result = fputs($fpOut, stripslashes($_POST['Header_'.$i])."\n");
	}

	prnMsg (_('Writing the rest of the language file') . '.....<BR>', 'info', ' ');

	while (!feof($fpIn)) {
		$FileContents = fgets($fpIn);
		$Result = fputs($fpOut, $FileContents);
	}

	$Result = fclose($fpIn);
	$Result = fclose($fpOut);

/* Done writing, now move the original file to a .old */
/* and the new one to the default */

	$Result = rename($PathToLanguage, $PathToLanguage . '.old');
	$Result = rename($PathToNewLanguage, $PathToLanguage);

	prnMsg (_('Done') . '<BR>', 'info', ' ');

	echo '</FORM>';
	echo '</TD></TR></TABLE>';
	echo '</CENTER>';
	
} else {

	$Result = fclose($fpIn);

  echo '<BR><BR>&nbsp;' . _('To change language click on the user name at the top left, change to language desired and click Modify');
  echo '<BR>&nbsp;' . _('Make sure you have selected the correct language to translate!');
  echo '<BR>&nbsp;' . _('When finished modifying you must click on Modify at the bottom in order to save changes');
	echo '<CENTER>';
	echo '<BR>';
	prnMsg (_('Your existing translation file (messages.po) will be backed up as messages.po.old') . '<BR><BR>' . 
				_('Make sure you know what you are doing BEFORE you edit the header'), 'info', _('PLEASE NOTE'));
	echo '<BR>';
	echo '<FORM METHOD="post" ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	echo '<TABLE><TR><TD CLASS="tableheader" COLSPAN="2" ALIGN="center">'. _('Language File Header for') . ' "' . $_POST['language'] . '"</TD></TR>';
	echo '<TR><TD COLSPAN="2"></TD></TR>';

	for ($i=1; $i<=17; $i++) {

		echo '<TR>';
		echo '<TD>' . _('Header Line') . ' # ' . $i . '</TD>';
		echo '<TD><INPUT TYPE="text" SIZE="80" NAME="Header_' . $i . '" VALUE="' . $LanguageHeader[$i] . '"></TD>';
		echo '</TR>';
	}

	echo '</TABLE>';
	echo '<BR><CENTER><INPUT TYPE="Submit" NAME="submit" VALUE="' . _('Modify') . '">&nbsp;&nbsp;';
	echo '<INPUT TYPE="hidden" NAME="language" VALUE="' . $_POST['language'] . '"></CENTER>';
	echo '</FORM>';
	echo '</CENTER>';
}

include('includes/footer.inc');

?>