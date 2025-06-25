<?php

echo '<h1>', _('System Checks'), '</h1>';

//set the default time zone
if (!empty($_SESSION['Installer']['TimeZone'])) {
	date_default_timezone_set($_SESSION['Installer']['TimeZone']);
}

/// @todo Check if cookies are allowed
if (false) {
	$InputError = 1;
	echo '<div class="error">' . _('Please set Cookies allowed in your web brower, otherwise webERP cannot run properly') . '</div>';
} else {
	echo '<div class="success">' . _('Cookies are properly enabled in your browser') . '</div>';
}

//It's time to check the php version. The version should be run greater or equal to 8.1
if (version_compare(PHP_VERSION, '8.1.0') < 0) {
	$InputError = 1;
	echo '<div class="error">' . _('You PHP version should be greater than 8.1') . '</div>';
} else {
	echo '<div class="success">' . _('Your PHP version is suitable for webERP') . '</div>';
}

//Check write access to the root path
$RootPath = '..';
if (!is_writable($RootPath)) {
	$InputError = 1;
	//get the directory where webERP live
	$webERPHome = dirname(__FILE__, 2);
	echo '<div class="error">' . _('The directory') . ' ' . $webERPHome . ' ' . _('must be writable by web server') . '</div>';
} else {
	echo '<div class="success">' . _('The base webERP directory is writable') . '</div>';
}

//Check write access to the companies path
$Companies = $RootPath . '/companies';
if (!is_writable($Companies)) {
	$InputError = 1;
	$webERPHome = dirname(__FILE__, 2);
	echo '<div class="error">' . _('The directory') . ' ' . $webERPHome . '/companies/' . ' ' . ('must be writable by web server') . '</div>';
} else {
	echo '<div class="success">' . _('The companies/ directory is writable') . '</div>';
}

//get the list of installed extensions
$Extensions = get_loaded_extensions();

//First check the gd extension
if (!in_array('gd', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The GD extension should be installed in your PHP configuration') . '</div>';
} else {
	echo '<div class="success">' . _('The GD extension is correctly installed') . '</div>';
}

//Check the gettext extension, it's a selectable
/// @todo is this really required? We bundle php_gettext as an alternative...
if (!in_array('gettext', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The gettext extension is not available in your PHP') . '</div>';
} else {
	echo '<div class="success">' . _('The gettext extension is correctly installed') . '</div>';
}

//Check the mbstring extension, it must exist
if (!in_array('mbstring', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The mbstring extension is not availble in your PHP') . '</div>';
} else {
	echo '<div class="success">' . _('The mbstring extension is correctly installed') . '</div>';
}

//Check the libxml extension
if (!in_array('libxml', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The libxml extension is not available in your PHP') . '</div>';
} else {
	echo '<div class="success">' . _('The libxml extension is correctly installed') . '</div>';
}

//Check that the DBMS driver is installed
if (!in_array('mysqli', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('You do not have the correct database extension installed for PHP') . '</div>';
} else {
	echo '<div class="success">' . _('The database extension is installed') . '</div>';
}

?>
