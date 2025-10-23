<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Edit Module');
$ViewTopic = "SpecialUtilities";
$BookMark = "Z_poEditLangModule";
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('Edit a Language File Module') . '" />' . ' ' .
		__('Edit a Language File Module') . '</p>';

/* Your webserver user MUST have read/write access to here,	otherwise you'll be wasting your time */

echo '<br />&nbsp;<a href="' . $RootPath . '/Z_poAdmin.php">' . __('Back to the translation menu') . '</a>';
echo '<br /><br />&nbsp;' . __('Utility to edit a language file module');
echo '<br />&nbsp;' . __('Current language is') . ' ' . $_SESSION['Language'];
echo '<br /><br />&nbsp;' . __('To change language click on the user name at the top left, change to language desired and click Modify');
echo '<br />&nbsp;' . __('Make sure you have selected the correct language to translate!');

$PathToLanguage		= $PathPrefix . 'locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po';
$PathToNewLanguage	= $PathPrefix . 'locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po.new';

if (isset($_POST['ReMergePO'])){

/*update the messages.po file with any new strings */
	/// @bug we have to check that msgmerge is a cli command, not a php function!!!
	if (!function_exists('msgmerge')) {
		prnMsg(__('The gettext utilities must be present on your server for these language utilities to work'),'error');
		exit();
	} else {
/*first rebuild the en_GB default with xgettext */

		$PathToDefault = './locale/en_GB.utf8/LC_MESSAGES/messages.po';
		/// @todo review this list
		$FilesToInclude	= '*php includes/*.php includes/*.php';

		/// @todo add proper escaping to prevent shell injection

		$xgettextCmd		= 'xgettext --no-wrap -L php -o ' . $PathToDefault . ' ' . $FilesToInclude;

		exec($xgettextCmd, $output, $result);
	/*now merge the translated file with the new template to get new strings*/

		$MsgMergeCmd = 'msgmerge --no-wrap --update ' . $PathToLanguage . ' ' . $PathToDefault;

		/// @todo check for failures
		exec($MsgMergeCmd, $output, $result);
		//$Result = rename($PathToNewLanguage, $PathToLanguage);
		exit();
	}
}

if (isset($_POST['module'])) {
  // a module has been selected and is being modified

	$PathToLanguage_mo = mb_substr($PathToLanguage,0,strrpos($PathToLanguage,'.')) . '.mo';

  /* now read in the language file */

	$LangFile = file($PathToLanguage);
	$LangFileEntries = sizeof($LangFile);

	if (isset($_POST['submit'])) {
    // save the modifications

		echo '<br /><table><tr><td>';
		echo '<form method="post" action=' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

    /* write the new language file */

		prnMsg(__('Writing the language file') . '.....<br />', 'info', ' ');

		for ($i=17; $i<=$LangFileEntries; $i++) {
			if (isset($_POST['msgstr_'.$i])) {
				$LangFile[$i] = 'msgstr "' . $_POST['moduletext_'.$i] . '"' . "\n";
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
		$MsgfmtCommand = 'msgfmt ' . $PathToLanguage . ' -o ' . $PathToLanguage_mo;
		/// @todo check for failures
		exec($MsgfmtCommand, $output, $result);

		prnMsg(__('Done') . '<br />', 'info', ' ');

		echo '</form>';
		echo '</td></tr></table>';

	/* End of Submit block */
	} else {

    /* now we need to parse the resulting array into something we can show the user */

		$j = 1;
		$AlsoIn = array();
		for ($i=17; $i<=$LangFileEntries; $i++) {			/* start at line 18 to skip the header */
			if (mb_substr($LangFile[$i], 0, 2) == '#:') {		/* it's a module reference */
				$AlsoIn[$j] .= str_replace(' ','<br />', mb_substr($LangFile[$i],3)) . '<br />';
			} elseif (mb_substr($LangFile[$i], 0 , 5) == 'msgid') {
				$DefaultText[$j] = mb_substr($LangFile[$i], 7, mb_strlen($LangFile[$i])-9);
			} elseif (mb_substr($LangFile[$i], 0 , 6) == 'msgstr') {
				$ModuleText[$j] = mb_substr($LangFile[$i], 8, mb_strlen($LangFile[$i])-10);
				$Msgstr[$j] = $i;
				$j++;
			}
		}
		$TotalLines = $j - 1;

/* stick it on the screen */

    echo '<br />&nbsp;' . __('When finished modifying you must click on Modify at the bottom in order to save changes');
		echo '<div class="centre">';
		echo '<br />';
		prnMsg(__('Your existing translation file (messages.po) will be saved as messages.po.old') . '<br />', 'info', __('PLEASE NOTE'));
		echo '<br />';
		echo '<form method="post" action=' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '>
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
				</div>
				<table>
					<tr>
						<th align="center">' . __('Language File for') . ' "' . $_POST['language'] . '"</th>
					</tr>
					<tr>
						<th align="center">' . __('Module') . ' "' . $_POST['module'] . '"</th>
					</tr>
					<tr>
						<td></td>
					</tr>
					<tr>
						<td>';

		echo '<table width="100%">';
		echo '<tr>';
		echo '<th>' . __('Default text') . '</th>';
		echo '<th>' . __('Translation') . '</th>';
		echo '<th>' . __('Exists in') . '</th>';
		echo '</tr>' . "\n";

		for ($i=1; $i<=$TotalLines; $i++) {

			$b = mb_strpos($AlsoIn[$i], $_POST['module']);

			if ($b === false) {
/* skip it */

			} else {
				echo '<tr>';
				echo '<td valign="top"><i>' . $DefaultText[$i] . '</i></td>';
				echo '<td valign="top"><input type="text" size="60" name="moduletext_' . $Msgstr[$i] . '" value="' . $ModuleText[$i] . '" /></td>';
				echo '<td valign="top">' . $AlsoIn[$i] . '<input type="hidden" name="msgstr_' . $Msgstr[$i] . '" value="' . $Msgstr[$i] . '" /></td>';
				echo '</tr>';
				echo '<tr><th colspan="3"></th></tr>';
			}

		}

		echo '</td></table>';

		echo '</td></tr>';
		echo '</table>';
		echo '<br /><div class="centre">';
		echo '<input type="submit" name="submit" value="' . __('Modify') . '" />&nbsp;&nbsp;';
		echo '<input type="hidden" name="module" value="' . $_POST['module'] . '" />';

		echo '</form>';
		echo '</div>';
	}

} else {

/* get available modules */

/* This is a messy way of producing a directory listing of ./locale to fish out */
/* the language directories that have been set up */
/* The other option would be to define an array of the languages you want */
/* and check for the existence of the directory */

	if ($Handle = opendir('.')) {
    	$i=0;
    	while (false !== ($File = readdir($Handle))) {
        if ((mb_substr($File, 0, 1) != ".") && (!is_dir($File))) {
          $AvailableModules[$i] = $File;
        	$i += 1;
        }
    	}
  	  closedir($Handle);
	}

	if ($Handle = opendir(".//includes")) {
    	while (false !== ($File = readdir($Handle))) {
        if ((mb_substr($File, 0, 1) != ".") && (!is_dir($File))) {
          $AvailableModules[$i] = $File;
        	$i += 1;
        }
    	}
  	  closedir($Handle);
	}

	sort($AvailableModules);
	$NumberOfModules = sizeof($AvailableModules) - 1;

if (!is_writable('./locale/' . $_SESSION['Language'])) {
	prnMsg(__('You do not have write access to the required files please contact your system administrator'),'error');
}
else
{
	echo '<br />
		<table>
		<tr>
			<td>';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" >';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table>';

	echo '<tr><td>' . __('Select the module to edit') . '</td>';
	echo '<td><select name="module">';
	for ($i=0; $i<$NumberOfModules; $i++) {
			echo '<option>' . $AvailableModules[$i] . '</option>';
	}
	echo '</select></td>';

	echo '</tr></table>';
	echo '<br />';
	echo '<div class="centre">
			<input type="submit" name="proceed" value="' . __('Proceed') . '" />&nbsp;&nbsp;
			<br />
			<br />
			<input type="submit" name="ReMergePO" value="' . __('Refresh messages with latest strings') . '" />
		</div>
		</form>';
	echo '</td></tr></table>';
}
}

include('includes/footer.php');
