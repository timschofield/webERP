<?php

/* Steve Kitchen */

//$PageSecurity = 15;

require(__DIR__ . '/includes/session.php');

$Title = __('Edit Header');// __('Edit a Language File Header')
$ViewTopic = "SpecialUtilities";
$BookMark = "Z_poEditLangHeader";// Anchor's id in the manual's html document.
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('Edit a Language File Header') . '" />' . ' ' .
		__('Edit a Language File Header') . '</p>';

/* Your webserver user MUST have read/write access to here,	otherwise you'll be wasting your time */

echo '<br />&nbsp;<a href="' . $RootPath . '/Z_poAdmin.php">' . __('Back to the translation menu') . '</a>';
echo '<br /><br />&nbsp;' . __('Utility to edit a language file header');
echo '<br />&nbsp;' . __('Current language is') . ' ' . $_SESSION['Language'];

$PathToLanguage		= './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po';
$PathToNewLanguage	= './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po.new';

$fpIn = fopen($PathToLanguage, 'r');

for ($i=1; $i<=17; $i++){	/* message.po header is 17 lines long - this is easily broken */
	$LanguageHeader[$i] = fgets($fpIn);
}

if (isset($_POST['submit'])) {

	echo '<br /><table><tr><td>';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

/* write the new header then the rest of the language file to a new file */

	prnMsg(__('Writing the language file header') . '.....<br />', 'info', ' ');

	$fpOut = fopen($PathToNewLanguage, 'w');

	for ($i=1; $i<=17; $i++) {
		$Result = fputs($fpOut, stripslashes(html_entity_decode($_POST['Header_'.$i]))."\n");
	}

	prnMsg(__('Writing the rest of the language file') . '.....<br />', 'info', ' ');

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

	prnMsg(__('Done') . '<br />', 'info', ' ');

	echo '</form>';
	echo '</td></tr></table>';

} else {

	$Result = fclose($fpIn);

if (!is_writable('./locale/' . $_SESSION['Language'])) {
	prnMsg(__('You do not have write access to the required files please contact your system administrator'),'error');
}
else
{
  echo '<br /><br />&nbsp;' . __('To change language click on the user name at the top left, change to language desired and click Modify');
  echo '<br />&nbsp;' . __('Make sure you have selected the correct language to translate!');
  echo '<br />&nbsp;' . __('When finished modifying you must click on Modify at the bottom in order to save changes');
	echo '<div class="centre">';
	echo '<br />';
	prnMsg(__('Your existing translation file (messages.po) will be backed up as messages.po.old') . '<br /><br />' .
				__('Make sure you know what you are doing BEFORE you edit the header'), 'info', __('PLEASE NOTE'));
	echo '<br /></div>';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table><tr><th" colspan="2" ALIGN="center">' .  __('Language File Header for') . ' "' . $_POST['language'] . '"</th></tr>';
	echo '<tr><td colspan="2"></td></tr>';

	for ($i=1; $i<=17; $i++) {

		echo '<tr>';
		echo '<td>' . __('Header Line') . ' # ' . $i . '</td>';
		echo '<td><input type="text" size="80" name="Header_' . $i . '" value="' . htmlspecialchars($LanguageHeader[$i]) . '" /></td>';
		echo '</tr>';
	}

	echo '</table>';
	echo '<br /><div class="centre"><input type="submit" name="submit" value="' . __('Modify') . '" />&nbsp;&nbsp;';
	echo '<input type="hidden" name="language" value="' . $_POST['language'] . '" /></div>';
	echo '</form>';
}
}
include('includes/footer.php');
