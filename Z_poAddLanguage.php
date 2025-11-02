<?php

/* Allows a new language po file to be created */

/* Steve Kitchen/Kaill */

//$PageSecurity = 15;

require(__DIR__ . '/includes/session.php');

/* Was the Cancel button pressed the last time through ? */
if (isset($_POST['cancel'])) {
	header ('Location:' . $RootPath . '/Z_poAdmin.php');
	exit();
}

$Title = __('New Language');// __('Add a New Language to the System')
$ViewTopic = "SpecialUtilities";
$BookMark = "Z_poAddLanguage";// Anchor's id in the manual's html document.
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('Add a New Language to the System') . '" />' . ' ' .
		__('Add a New Language to the System') . '</p>';

/* Your webserver user MUST have read/write access to here,	otherwise you'll be wasting your time */

echo '<br />&nbsp;<a href="' . $RootPath . '/Z_poAdmin.php">' . __('Back to the translation menu') . '</a>';
echo '<br /><br />&nbsp;' . __('Utility to create a new language file');
echo '<br />&nbsp;' . __('Current language is') . ' ' . $_SESSION['Language'];

$DefaultLanguage = 'en_GB';// The default language is English-United Kingdom (British English).
$PathToDefault = './locale/' . $DefaultLanguage . '.utf8/LC_MESSAGES/messages.pot';

if (isset($_POST['submit']) AND isset($_POST['NewLanguage'])) {

	if(mb_strlen($_POST['NewLanguage'])<5
		OR mb_strlen($_POST['NewLanguage'])>5
		OR mb_substr($_POST['NewLanguage'],2,1)!='_'){

		prnMsg(__('Languages must be in the format of a two character country code an underscore _ and a two character language code in upper case'),'error');
	} else {

		/*Make sure the language characters are in upper case*/

		$_POST['NewLanguage'] = mb_substr($_POST['NewLanguage'],0,3) . mb_strtoupper(mb_substr($_POST['NewLanguage'],3,2));

		echo '<div class="centre">';
		echo '<br />';
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


		/* check for directory existence */

		if (!file_exists('./locale/' . $_POST['NewLanguage'])) {
			prnMsg(__('Attempting to create the new language file') . '.....<br />', 'info', ' ');
			$Result = mkdir('./locale/' . $_POST['NewLanguage'] . '.utf8');
			$Result = mkdir('./locale/' . $_POST['NewLanguage'] . '.utf8/LC_MESSAGES');
		} else {
			prnMsg(__('This language cannot be added because it already exists!'),'error');
  			echo '</form>';
	  		echo '</div>';
			include('includes/footer.php');
			exit();
		}

		$PathToNewLanguage = './locale/' . $_POST['NewLanguage'] . '.utf8/LC_MESSAGES/messages.po';
		$Result = copy($PathToDefault, $PathToNewLanguage);

		prnMsg(__('Done. You should now change to your newly created language from the user settings link above. Then you can edit the new language file header and use the language module editor to translate the system strings'), 'info');

		echo '</form>';
		echo '</div>';
		include('includes/footer.php');
		exit();
	}

}


echo '<div class="centre">';
echo '<br />';
prnMsg(__('This utility will create a new language and a new language translation file for it from the system default') . '<br /><br />' .
		__('If the language already exists then you cannot recreate it'), 'info', __('PLEASE NOTE'));
echo '<br /></div>';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table><tr>';
echo '<td>' . __('Full code of the new language in the format en_US') . '</td>';
echo '<td><input type="text" size="5" name="NewLanguage" />';
echo '</td></tr></table>';

echo '<br /><input type="submit" name="submit" value="' . __('Proceed') . '" />&nbsp;&nbsp;&nbsp;&nbsp;';
echo '</form>';

include('includes/footer.php');
