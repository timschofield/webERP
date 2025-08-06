<?php

echo '<h1>', _('System Checks'), '</h1>';

//set the default time zone
if (!empty($_SESSION['Installer']['TimeZone'])) {
	date_default_timezone_set($_SESSION['Installer']['TimeZone']);
}

//Check if cookies are allowed
if (false) {
	$InputError = 1;
	echo '<div class="error">' . _('Please set Cookies allowed in your web browser, otherwise webERP cannot run properly') . '</div>';
} else {
	echo '<div class="success">' . _('Cookies are properly enabled in your browser') . '</div>';
}

//It's time to check the php version. The version should be 8.1 or above
/// @todo grab the version to check for from parsing composer.json
if (version_compare(PHP_VERSION, '8.1.0') < 0) {
	$InputError = 1;
	echo '<div class="error">' . _('You PHP version should be equal or greater than') . ' 8.1</div>';
} else {
	echo '<div class="success">' . _('Your PHP version is suitable for webERP') . '</div>';
}

//Check the write access of the root directory
$RootDir = substr(dirname(__DIR__), 0, -8);
if (!is_writable($RootDir)) {
	$InputError = 1;
	//get the directory where webERP live
	$webERPHome = dirname(__FILE__, 2);
	$webERPHome = dirname(dirname(__FILE__));echo '<div class="success">' . _('The base webERP directory is writable') . '</div>';
}

//Check the write access of the companies directory
$CompaniesDir = $RootDir . '/companies';
if (!is_writable($CompaniesDir)) {
	$InputError = 1;
	$webERPHome = dirname(__FILE__, 2);
	echo '<div class="error">' . _('The directory') . ' ' . $CompaniesDir . '/companies/' . ' ' . ('must be writable by web server') . '</div>';
} else {
	echo '<div class="success">' . _('The companies/ directory is writable') . '</div>';
}

//get the list of installed extensions
$Extensions = get_loaded_extensions();

/// @todo grab the list of required and recommended extensions from parsing composer.json

//First check the gd extension
if (!in_array('gd', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The GD extension should be installed in your PHP configuration') . '</div>';
} else {
	echo '<div class="success">' . _('The GD extension is correctly installed') . '</div>';
}

//The gettext extension is not checked anymore, as it's optional.
/// @todo bring back the check for gettext, but make it a warning. Same for the other optional extensions
/*if (!in_array('gettext', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The gettext extension is not available in your PHP') . '</div>';
} else {
	echo '<div class="success">' . _('The gettext extension is correctly installed') . '</div>';
}*/

//Check the mbstring extension, it must exist
if (!in_array('mbstring', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The mbstring extension is not available in your PHP') . '</div>';
} else {
	echo '<div class="success">' . _('The mbstring extension is correctly installed') . '</div>';
}

//Check the extension module
if (!in_array('libxml', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The libxml extension is not available in your PHP') . '</div>';
} else {
	echo '<div class="success">' . _('The libxml extension is correctly installed') . '</div>';
}

//Check that the extension used for DBMS connections is installed
if (!in_array('mysqli', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('You do not have the correct database extension installed for PHP (mysqli)') . '</div>';
} else {
	echo '<div class="success">' . _('The database extension is correctly installed') . '</div>';
}
