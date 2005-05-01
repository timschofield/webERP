<?php
/* $Revision: 1.7 $ */

/* Steve Kitchen */

/* This code is really ugly ... */

$PageSecurity = 15;

include ('includes/session.inc');

$title = _('Edit Module');

include('includes/header.inc');

/* Your webserver user MUST have read/write access to here, 
	otherwise you'll be wasting your time */
	
$PathToLanguage		= './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po';
$PathToNewLanguage	= './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po.new';
	
echo "<BR>&nbsp;<A HREF='" . $rootpath . "/Z_poAdmin.php'>" . _('Back to the translation menu') . "</A>";
echo '<BR><BR>&nbsp;' . _('Utility to edit a language file module');
echo '<BR>&nbsp;' . _('Current language is') . ' ' . $_SESSION['Language'];
echo '<BR><BR>&nbsp;' . _('To change language click on the user name at the top left, change to language desired and click Modify');
echo '<BR>&nbsp;' . _('Make sure you have selected the correct language to translate!');

if (isset($_POST['ReMergePO'])){

/*update the messages.po file with any new strings */	

/*first rebuild the en_GB default with xgettext */

	$PathToDefault = './locale/en_GB/LC_MESSAGES/messages.po';
	$FilesToInclude	= '*php includes/*.php includes/*.inc';
	$xgettextCmd		= 'xgettext --no-wrap -L php -o ' . $PathToDefault . ' ' . $FilesToInclude;

	system($xgettextCmd);
/*now merge the translated file with the new template to get new strings*/
	
	$msgMergeCmd = 'msgmerge --no-wrap --update ' . $PathToLanguage . ' ' . $PathToDefault;
	
	system($msgMergeCmd);
	//$Result = rename($PathToNewLanguage, $PathToLanguage);
	exit;
}
	
if (isset($_POST['module'])) {	
  // a module has been selected and is being modified
  
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

		$Result = rename($PathToLanguage, $PathToLanguage . '.old');
		$Result = rename($PathToNewLanguage, $PathToLanguage);
		
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
		echo '<TR><TD CLASS="tableheader" ALIGN="center">' . _('Language File for') . ' "' . $_POST['language'] . '"</TD></TR>';
		echo '<TR><TD ALIGN="center">' . _('Module') . ' "' . $_POST['module'] . '"</TD></TR>';
		echo '<TR><TD></TD></TR>';
		echo '<TR><TD>';

		echo '<TABLE WIDTH="100%">';
		echo '<TR>';
		echo '<TD CLASS="tableheader">' . _('Default text') . '</TD>';
		echo '<TD CLASS="tableheader">' . _('Translation') . '</TD>';
		echo '<TD CLASS="tableheader">' . _('Exists in') . '</TD>';
		echo '</TR>' . "\n";

		for ($i=1; $i<=$TotalLines; $i++) {

			$b = strpos($AlsoIn[$i], $_POST['module']);

			if ($b === False) {
/* skip it */

			} else {
				echo '<TR>';
				echo '<TD VALIGN="top"><I>' . $DefaultText[$i] . '</I></TD>';
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

} else {

/* get available modules */

/* This is a messy way of producing a directory listing of ./locale to fish out */
/* the language directories that have been set up */
/* The other option would be to define an array of the languages you want */
/* and check for the existance of the directory */

/* $ListDirCmd should probably be defined in config.php as a global value */
/* You'll need to change it if you are running a Windows server - sorry !! */

	$ListDirCmd = '/bin/ls';

	$PathToModules = $ListDirCmd . ' *.php includes/*.php includes/*.inc';
	$fpIn = popen($PathToModules, 'r');
	while (!feof($fpIn)) {
		$AvailableModules[] = fgets($fpIn);
	}
	$NumberOfModules = sizeof($AvailableModules) - 1;
	$Result = pclose($fpIn);

	echo '<CENTER>';
	echo '<BR><TABLE><TR><TD>';
	echo '<FORM METHOD="post" ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	echo '<TABLE>';

	echo '<TR><TD>' . _('Select the module to edit') . '</TD>';
	echo '<TD><SELECT NAME="module">';
	for ($i=0; $i<$NumberOfModules; $i++) {
			echo '<OPTION>' . $AvailableModules[$i] . '</OPTION>';
	}
	echo '</SELECT></TD>';

	echo '</TR></TABLE>';
	echo '<BR>';
	echo '<CENTER>';
	echo '<INPUT TYPE="Submit" NAME="proceed" VALUE="' . _('Proceed') . '">&nbsp;&nbsp;';
	echo '<BR><BR><INPUT TYPE="Submit" NAME="ReMergePO" VALUE="' . _('Refresh messages with latest strings') . '">';
	echo '</CENTER>';
	echo '</FORM>';
	echo '</TD></TR></TABLE>';
	echo '</CENTER>';

}

include('includes/footer.inc');

?>
