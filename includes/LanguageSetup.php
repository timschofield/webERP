<?php

/* Set internal character encoding to UTF-8 */
mb_internal_encoding('UTF-8');

/* This file is included in session.php or PDFStarter.php or a report script that does not use PDFStarter.php to check
   for the existence of gettext function and setting up the necessary environment to allow for automatic translation.

   Set language - defined in config.php or user variable when logging in (session.php)
   NB: this language must also exist in the locale on the web-server
   Normally the lower case two character language code underscore uppercase 2 character country code does the trick,
  except for en !! */

/*
 * Improve language check to avoid potential LFI issue.
 * Reported by: https://lyhinslab.org
 */
if (isset($_POST['Language']) && checkLanguageChoice($_POST['Language'])) {
	$_SESSION['Language'] = $_POST['Language'];
} elseif (!isset($_SESSION['Language'])) {
	$_SESSION['Language'] = $DefaultLanguage;
}

$Language = $_SESSION['Language'];

// Check users' locale format via their language
// Then pass this information to the js for number validation purpose

$Collect = array(
	'US'=>array('en_US.utf8','en_GB.utf8','ja_JP.utf8','hi_IN.utf8','mr_IN.utf8','sw_KE.utf8','tr_TR.utf8','vi_VN.utf8','zh_CN.utf8','zh_HK.utf8','zh_TW.utf8'),
	'IN'=>array('en_IN.utf8','hi_IN.utf8','mr_IN.utf8'),
	'EE'=>array('ar_EG.utf8','cz_CZ.utf8','fr_CA.utf8','fr_FR.utf8','hr_HR.utf8','pl_PL.utf8','ru_RU.utf8','sq_AL.utf8','sv_SE.utf8'),
	'FR'=>array('ar_EG.utf8','cz_CZ.utf8','fr_CA.utf8','fr_FR.utf8','hr_HR.utf8','pl_PL.utf8','ru_RU.utf8','sq_AL.utf8','sv_SE.utf8'),
	'GM'=>array('de_DE.utf8','el_GR.utf8','es_ES.utf8','fa_IR.utf8','id_ID.utf8','it_IT.utf8','ro_RO.utf8','lv_LV.utf8','nl_NL.utf8','pt_BR.utf8','pt_PT.utf8')
);

foreach($Collect as $Key => $Value) {
	if (in_array($Language, $Value)) {
		$Lang = $Key;
		$_SESSION['Lang'] = $Lang;
	}
}

/*
 Since this file is always loaded after session.php has been loaded, and session.php sets up the composer autoloading,
 when we get here the 'gettext' function is always defined. It can be either the function from the php native
 extension, or the one from the polyfill-gettext php package.
 Was:
   Since LanguagesArray requires the function _() to translate the language names - we must provide a substitute if
   it doesn't exist already before we include includes/LanguagesArray.php
   PHPGettext integration by Braian Gomez - http://www.vairux.com/
*/
/*
if (!function_exists('gettext')) {
	if (isset($_SESSION['Language'])) {
		$Locale = $_SESSION['Language'];
	} else {
		$Locale = $DefaultLanguage;
	}

	if (isset($PathPrefix)) {
		$LangFile = $PathPrefix . 'locale/' . $Locale . '/LC_MESSAGES/messages.mo';
	} else {
		$LangFile = __DIR__ . '/../locale/' . $Locale . '/LC_MESSAGES/messages.mo';
	}

	if (file_exists($LangFile)){
		$input = new FileReader($LangFile);
		$PhpGettext = new gettext_reader($input);

		if (!function_exists('_')){
			function _($Text) {
				global $PhpGettext;
				return $PhpGettext->translate($Text);
			}
		}
	} elseif (!function_exists('_')) {
		function _($Text){
			return $Text;
		}
	}
	include($PathPrefix . 'includes/LanguagesArray.php');
} else {
*/
	include($PathPrefix . 'includes/LanguagesArray.php');

	$LocaleSetAll = setlocale(LC_ALL, $_SESSION['Language'], $LanguagesArray[$_SESSION['Language']]['WindowsLocale']);
	$LocaleSetNumeric = setlocale(LC_NUMERIC, 'C', 'en_GB.utf8', 'en_GB', 'en_US', 'english-us');

	// NB: this is always true now, because of polyfill-gettext. Was: "it's a unix/linux server"
	if (defined('LC_MESSAGES')) {
		$LocaleSetMessages = setlocale(LC_MESSAGES, $_SESSION['Language'], $LanguagesArray[$_SESSION['Language']]['WindowsLocale']);
	}
	// Turkish seems to be a special case
	if ($_SESSION['Language'] == 'tr_TR.utf8') {
		$LocaleSetCtype = setlocale(LC_CTYPE, 'C');
	}

	// possibly even if locale fails the language will still switch by using Language instead of locale variable
	/// @todo make this work better with polyfill gettext: besides the putenv call, if setlocale failed call PGettext\T::setlocale
	putenv('LANG=' . $_SESSION['Language']);
	putenv('LANGUAGE=' . $_SESSION['Language']);

	bindtextdomain ('messages', $PathPrefix . 'locale');
	textdomain ('messages');
	bind_textdomain_codeset('messages', 'UTF-8');
/*}*/

$DecimalPoint = $LanguagesArray[$_SESSION['Language']]['DecimalPoint'];
$ThousandsSeparator = $LanguagesArray[$_SESSION['Language']]['ThousandsSeparator'];
