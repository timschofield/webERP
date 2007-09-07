<?php
/* $Revision: 1.1 $ */

/* Steve Kitchen */

/* This code is really ugly ... */

$PageSecurity = 15;

include ('includes/session.inc');

$title = _('Edit Remaining Items');

include('includes/header.inc');

/* Your webserver user MUST have read/write access to here, 
	otherwise you'll be wasting your time */
	
$PathToLanguage		= './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po';
$PathToNewLanguage	= './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po.new';
	
echo "<BR>&nbsp;<A HREF='" . $rootpath . "/Z_poAdmin.php'>" . _('Back to the translation menu') . "</A>";
echo '<BR><BR>&nbsp;' . _('Utility to edit a language file module');
echo '<BR>&nbsp;' . _('Current language is') . ' ' . $_SESSION['Language'];

  
	$PathToLanguage_mo = substr($PathToLanguage,0,strrpos($PathToLanguage,'.')) . '.mo';

  /* now read in the language file */

	$LangFile = file($PathToLanguage);
	$LangFileEntries = sizeof($LangFile);

	if (isset($_POST['submit'])) {
    // save the modifications
    
		echo '<CENTER>';
		echo '<BR><TABLE><TR><TD>';
		echo '<FORM METHOD="post" ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

    /* write the new language file */

		prnMsg (_('Writing the language file') . '.....<BR>', 'info', ' ');

		for ($i=17; $i<=$LangFileEntries; $i++) {
			if (isset($_POST['msgstr_'.$i])) {
				$LangFile[$i] = 'msgstr "' . htmlentities($_POST['moduletext_'.$i]) . '"' . "\n";
			}
		}
		$fpOut = fopen($PathToNewLanguage, 'w');
		for ($i=0; $i<=$LangFileEntries; $i++) {
			$Result = fputs($fpOut, $LangFile[$i]);
		}
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
		
    /*now need to create the .mo file from the .po file */
		$msgfmtCommand = 'msgfmt ' . $PathToLanguage . ' -o ' . $PathToLanguage_mo;
		system($msgfmtCommand);

		prnMsg (_('Done') . '<BR>', 'info', ' ');

		echo '</FORM>';
		echo '</TD></TR></TABLE>';
		echo '</CENTER>';
	
	/* End of Submit block */
	} else {

    /* now we need to parse the resulting array into something we can show the user */

		$j = 1;

		for ($i=17; $i<=$LangFileEntries; $i++) {			/* start at line 18 to skip the header */
			if (substr($LangFile[$i], 0, 2) == '#:') {		/* it's a module reference */
				$AlsoIn[$j] .= str_replace(' ','<BR>', substr($LangFile[$i],3)) . '<BR>';
			} elseif (substr($LangFile[$i], 0 , 5) == 'msgid') {
				$DefaultText[$j] = substr($LangFile[$i], 7, strlen($LangFile[$i])-9);
			} elseif (substr($LangFile[$i], 0 , 6) == 'msgstr') {
				$ModuleText[$j] = substr($LangFile[$i], 8, strlen($LangFile[$i])-10);
				$msgstr[$j] = $i;
				$j++;
			}
		}
		$TotalLines = $j - 1;
		
						
/* stick it on the screen */

    echo '<BR>&nbsp;' . _('When finished modifying you must click on Modify at the bottom in order to save changes');
		echo '<CENTER>';
		echo '<BR>';
		prnMsg (_('Your existing translation file (messages.po) will be saved as messages.po.old') . '<BR>', 'info', _('PLEASE NOTE'));
		echo '<BR>';
		echo '<FORM METHOD="post" ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

		echo '<TABLE>';
		echo '<TR><TD CLASS="tableheader" ALIGN="center">' . _('Language File for') . ' "' . $_SESSION['Language'] . '"</TD></TR>';
		echo '<TR><TD></TD></TR>';
		echo '<TR><TD>';

		echo '<TABLE WIDTH="100%">';
		echo '<TR>';
		echo '<TD CLASS="tableheader">' . _('Default text') . '</TD>';
		echo '<TD CLASS="tableheader">' . _('Translation') . '</TD>';
		echo '<TD CLASS="tableheader">' . _('Exists in') . '</TD>';
		echo '</TR>' . "\n";

		for ($i=1; $i<=$TotalLines; $i++) {
			if ($ModuleText[$i] == "") {
				echo '<TR>';
				echo '<TD VALIGN="top"><I>'. $DefaultText[$i] . '</I></TD>';
				echo '<TD VALIGN="top"><INPUT TYPE="text" SIZE="60" NAME="moduletext_' . $msgstr[$i] . '" VALUE="' . $ModuleText[$i] . '"></TD>';
				echo '<TD VALIGN="top">' . $AlsoIn[$i] . '<INPUT TYPE="hidden" NAME="msgstr_' . $msgstr[$i] . '" VALUE="' . $msgstr[$i] . '"></TD>';
				echo '</TR>';
				echo '<TR><TD CLASS="tableheader" COLSPAN="3"></TD></TR>';
			}
		}

		echo '</TABLE>';

		echo '</TD></TR>';
		echo '</TABLE>';
		echo '<BR><CENTER>';
		echo '<INPUT TYPE="Submit" NAME="submit" VALUE="' . _('Modify') . '">&nbsp;&nbsp;';
		echo '<INPUT TYPE="hidden" NAME="module" VALUE="' . $_POST['module'] . '">';
		
		echo '</FORM>';
		echo '</CENTER>';
	}



include('includes/footer.inc');

?>
