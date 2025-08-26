<?php

/*
 * This file is included in session.php to check for the existence of gettext functions and
 * setting up the necessary environment to allow for automatic translation.
 */

use PGettext\T;

/* Set internal character encoding to UTF-8 */
mb_internal_encoding('UTF-8');

/*
 * Set language - defined in config.php or user variable when logging in (session.php)
 * NB: this language must also exist in the locale on the web-server
 * Normally the lower case 2 character language code underscore uppercase 2 character country code does the trick,
 * except for en !!
 */
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

include_once($PathPrefix . 'includes/LanguagesArray.php');

$LocaleSetOk = setlocale(LC_ALL, $_SESSION['Language'], $LanguagesArray[$_SESSION['Language']]['WindowsLocale']);
if ($LocaleSetOk === false) {
	// make sure we still enable translations via polyfill-gettext, even if the locale is not installed in the system
	/* NB: LC_MESSAGES is always defined now, because of polyfill-gettext */
	$LocaleSetOk = T::setlocale(LC_MESSAGES, $_SESSION['Language']);
}
// avoid polluting the global namespace
unset($LocaleSetOk);
// number formatting localization is not carried out using php functions, but using $DecimalPoint and $ThousandsSeparator
setlocale(LC_NUMERIC, 'C', 'en_GB.utf8', 'en_GB', 'en_US', 'english-us');
// Turkish seems to be a special case
if ($_SESSION['Language'] == 'tr_TR.utf8') {
	setlocale(LC_CTYPE, 'C');
}

// "even if setlocale fails the language will possibly still switch by using env vars"
/// @todo to be confirmed...
putenv('LANG=' . $_SESSION['Language']);
putenv('LANGUAGE=' . $_SESSION['Language']);

textdomain ('messages');
bindtextdomain ('messages', $PathPrefix . 'locale');
bind_textdomain_codeset('messages', 'UTF-8');

if (!function_exists('__')) {
	/**
	 * We define (yet another) shortcut for T:__gettext, to avoid having to add a `use PGettext/T` line to every file with translations
	 * @param string $message
	 * @return string
	 */
	function __($message) {
		return T::gettext($message);
	}
}

$DecimalPoint = $LanguagesArray[$_SESSION['Language']]['DecimalPoint'];
$ThousandsSeparator = $LanguagesArray[$_SESSION['Language']]['ThousandsSeparator'];
