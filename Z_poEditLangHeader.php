<?php
/* $Revision: 1.2 $ */

/* Steve Kitchen */

$PageSecurity = 15;

include ('includes/session.inc');

$title = _('UTILITY PAGE') . ' ' ._('to edit a language file header');

include('includes/header.inc');

/* Your webserver user MUST have read/write access to here, 
	otherwise you'll be wasting your time */

if (isset($_POST['language'])) {	
	$PathToLanguage		= './locale/' . $_POST['language'] . '/LC_MESSAGES/messages.po';
	$PathToNewLanguage	= './locale/' . $_POST['language'] . '/LC_MESSAGES/messages.po.new';

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

		echo "<CENTER><A HREF='" . $rootpath . "/Z_poAdmin.php'>" . _('Back to the menu') . "</A></CENTER>";
		echo '</FORM>';
		echo '</TD></TR></TABLE>';
		echo '</CENTER>';
	
	} else {

		$Result = fclose($fpIn);

		echo '<CENTER>';
		echo '<BR>';
		prnMsg (_('Your existing messages.po will be saved as messages.po.old') . '<BR><BR>' . 
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
		prnMsg (_('Once you click on the Enter Information button the file will be rewritten') . '. ' . _('You will not get a second chance'), 'warn', _('WARNING'));
		echo '<CENTER><INPUT TYPE="Submit" NAME="submit" VALUE="' . _('Enter Information') . '">&nbsp;&nbsp;';
		echo "<A HREF='" . $rootpath . "/Z_poAdmin.php'>" . _('Back to the menu') . "</A>";
		echo '<INPUT TYPE="hidden" NAME="language" VALUE="' . $_POST['language'] . '"></CENTER>';
		echo '</FORM>';
		echo '</CENTER>';
	}

} else {

/* This is a messy way of producing a directory listing of ./locale to fish out */
/* the language directories that have been set up */
/* The other option would be to define an array of the languages you want */
/* and check for the existance of the directory */

/* $ListDirCmd should probably be defined in config.php as a global value */
/* You'll need to change it if you are running a Windows server - sorry !! */

	$ListDirCmd = '/bin/ls';

	$PathToLocale = $ListDirCmd . ' ./locale';
	$fpIn = popen($PathToLocale, 'r');
	while (!feof($fpIn)) {
		$AvailableLanguages[] = fgets($fpIn);
	}
	$NumberOfLanguages = sizeof($AvailableLanguages) - 1;
	$Result = pclose($fpIn);

	echo '<CENTER>';
	echo '<BR><TABLE><TR><TD>';
	echo '<FORM METHOD="post" ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	echo '<TABLE><TR>';
	echo '<TD>' . _('Select the language header to edit') . '</TD>';
	echo '<TD><SELECT NAME="language">';

/* start from 1 to skip the CVS directory - not safe or subtle */
/* and it assumes sorted directory listings */

	for ($i=1; $i<$NumberOfLanguages; $i++) {
			echo '<OPTION>' . $AvailableLanguages[$i] . '</OPTION>';
	}

	echo '</SELECT>';
	echo '</TD></TR></TABLE>';
	echo '<BR>';
	echo '<INPUT TYPE="Submit" NAME="proceed" VALUE="' . _('Proceed') . '">&nbsp;&nbsp;';
	echo "<A HREF='" . $rootpath . "/Z_poAdmin.php'>" . _('Back to the menu') . "</A>";
	echo '</FORM>';
	echo '</TD></TR></TABLE>';
	echo '</CENTER>';

}

include('includes/footer.inc');

?>
