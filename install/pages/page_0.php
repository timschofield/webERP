<?php

if (!isset($PathPrefix)) {
	header('Location: ../');
	exit();
}

echo '<h1>', __('Welcome to the webERP installer'), '</h1>';

echo '<section class="installer_about">';

/* Get the php-gettext function.
 * When users have not select the language, we guess user's language via
 * the http header information. Once the user has selected their language,
 * use the language user selected
*/

if (!isset($_POST['Language']) and !isset($_SESSION['Installer']['Language'])) {
	if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) { //get users preferred language
		$ClientLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		switch ($ClientLang) {
			case 'ar':
				$Language = 'ar_EG.utf8';
			break;
			case 'cs':
				$Language = 'cs_CZ.utf8';
			break;
			case 'de':
				$Language = 'de_DE.utf8';
			break;
			case 'el':
				$Language = 'el_GR.utf8';
			break;
			case 'en':
				$Language = 'en_GB.utf8';
			break;
			case 'es':
				$Language = 'es_ES.utf8';
			break;
			case 'et':
				$Language = 'et_EE.utf8';
			break;
			case 'fa':
				$Language = 'fa_IR.utf8';
			break;
			case 'fr':
				$Langauge = 'fr_CA.utf8';
			break;
			case 'hi':
				$Language = 'hi_IN.utf8';
			break;
			case 'hr':
				$Language = 'hr_HR.utf8';
			break;
			case 'hu':
				$Language = 'hu_HU.utf8';
			break;
			case 'id':
				$Language = 'id_ID.utf8';
			break;
			case 'it':
				$Language = 'it_IT.utf8';
			break;
			case 'ja':
				$Language = 'ja_JP.utf8';
			break;
			case 'lv':
				$Language = 'lv_LV.utf8';
			break;
			case 'nl':
				$Language = 'nl_NL.utf8';
			break;
			case 'pl':
				$Language = 'pl_PL.utf8';
			break;
			case 'pt':
				$Language = 'pt-PT.utf8';
			break;
			case 'ro':
				$Language = 'ro_RO.utf8';
			break;
			case 'ru':
				$Language = 'ru_RU.utf8';
			break;
			case 'sq':
				$Language = 'sq_AL.utf8';
			break;
			case 'sv':
				$Language = 'sv_SE.utf8';
			break;
			case 'sw':
				$Language = 'sw_KE.utf8';
			break;
			case 'tr':
				$Language = 'tr_TR.utf8';
			break;
			case 'vi':
				$Language = 'vi_VN.utf8';
			break;
			case 'zh':
				$Language = 'zh_CN.utf8';
			break;
			default:
				$Language = 'en_GB.utf8';

		}
		$_SESSION['Installer']['Language'] = $Language;
		if (isset($_SESSION['Language'])) {
			unset($_SESSION['Language']);
		}
	}
} elseif (isset($_POST['Language'])) {
	$_SESSION['Installer']['Language'] = $_POST['Language'];
}

echo '<form id="installation" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
		<label for="Language">' . __('Language:') . '&#160;</label>
		<select id="Language" name="Language" onchange="document.getElementById(\'installation\').submit()">';
foreach ($LanguagesArray as $Key => $Language1) {
	if (isset($_SESSION['Installer']['Language']) and $Key == $_SESSION['Installer']['Language']) {
		echo '<option value="' . $Key . '" selected="selected">' . $Language1['LanguageName'] . '</option>';
	} else {
		echo '<option value="' . $Key . '" >' . $Language1['LanguageName'] . '</option>';
	}
}

echo '</select>
	</form>';

echo '<p>', __('For this installation to work, you need to be running PHP and mysql on your server'), '</p>';

echo '<p>', __('You will also need the following PHP extensions to be installed'), '</p>';

echo '<ol>
		<li>php-gd - ', __('A graphics extension'), '</li>
		<li>php-intl - ', __('For translations to work'), '</li>
		<li>php-mbstring - ', __('An extension to provide multi-byte string functionality'), '</li>
		<li>php-mysql - ', __('Extension to provide connectivity with the database'), '</li>
		<li>php-xml - ', __('Used to decode xml files'), '</li>
		<li>php-zip - ', __('For compression functionality'), '</li>
	</ol>';

echo '<p>', __('These are all standard extensions, but if you are using an external web hosting company then check with them that they have the correct extensions installed.'), '</p>';

echo '</section>';
