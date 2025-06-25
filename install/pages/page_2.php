<?php

echo '<h1>', _('System Checks'), '</h1>';

//set the default time zone
if (!empty($_SESSION['Installer']['TimeZone'])) {
	date_default_timezone_set($_SESSION['Installer']['TimeZone']);
}

//Check if the cookie is allowed
if (false) {
	$InputError = 1;
	echo '<div class="error">' . _('Please set Cookies allowed in your web brower, otherwise webERP cannot run properly') . '</div>';
} else {
	echo '<div class="success">' . _('Cookies are properly enabled in your browser') . '</div>';
}

//Check if it's in safe model, safe mode has been deprecated at 5.3.0 and removed at 5.4
//Please refer to here for more details http://hk2.php.net/manual/en/features.safe-mode.php
if (ini_get('safe_mode')) {
	$SafeModeWarning = _('You php is running in safe mode, it will force a maximum script execution time of 30 seconds') . ' ' . _('This can sometimes mean that the installation cannot be completed in time.') . ' ' . _('It is better to turn this function off');
	$InputWarn = 1;
	$WarnMsg.= '<p>' . _($_POST['SafeModeWarning']) . '</p>';
}

//It's time to check the php version. The version should be run greater than 5.1
if (version_compare(PHP_VERSION, '5.1.0') < 0) {
	$InputError = 1;
	echo '<div class="error">' . _('You PHP version should be greater than 5.1') . '</div>';
} else {
	echo '<div class="success">' . _('Your PHP version is suitable for webERP') . '</div>';
}

//Check the write access of the root directory
$RootDir = __DIR__;
if (!is_writable($RootDir)) {
	$InputError = 1;
	//get the directory where webERP live
	$webERPHome = dirname(__FILE__, 2);
	echo '<div class="error">' . _('The directory') . ' ' . $webERPHome . ' ' . _('must be writable by web server') . '</div>';
} else {
	echo '<div class="success">' . _('The base webERP directory is writable') . '</div>';
}

//Check the write access of the companies directory
$CompaniesDir = $RootDir . '/companies';
if (!is_writable($CompaniesDir)) {
	$InputError = 1;
	$webERPHome = dirname(__FILE__, 2);
	echo '<div class="error">' . _('The directory') . ' ' . $webERPHome . '/companies/' . ' ' . ('must be writable by web server') . '</div>';
} else {
	echo '<div class="success">' . _('The companies/ directory is writable') . '</div>';
}

//get the list of installed extensions
$Extensions = get_loaded_extensions();

//First check the gd module
if (!in_array('gd', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The GD extension should be installed in your PHP configuration') . '</div>';
} else {
	echo '<div class="success">' . _('The GD extension is correctly installed') . '</div>';
}

//Check the gettext module, it's a selectable
if (!in_array('gettext', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The gettext extension is not availble in your PHP') . '</div>';
} else {
	echo '<div class="success">' . _('The gettext extension is correctly installed') . '</div>';
}

//Check the mbstring module, it must be exist
if (!in_array('mbstring', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The mbstring extension is not availble in your PHP') . '</div>';
} else {
	echo '<div class="success">' . _('The mbstring extension is correctly installed') . '</div>';
}

//Check the libxml module
if (!in_array('libxml', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The libxml extension is not available in your PHP') . '</div>';
} else {
	echo '<div class="success">' . _('The libxml extension is correctly installed') . '</div>';
}

//Check that the DBMS driver is installed
if (!in_array('mbstring', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('You do not have the correct database extension installed for PHP') . '</div>';
} else {
	echo '<div class="success">' . _('The database extension is installed') . '</div>';
}

?>
