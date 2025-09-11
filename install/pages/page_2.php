<?php

if (!isset($PathPrefix)) {
	header('Location: ../');
	exit();
}

echo '<h1>', __('System Checks'), '</h1>';

// set the default time zone
// gg: no need for this on page 2... also, the tz is only set on page 6
//if (!empty($_SESSION['Installer']['TimeZone'])) {
//	date_default_timezone_set($_SESSION['Installer']['TimeZone']);
//}

// Check if cookies are allowed...
// We just presume that if a user reaches here it means she has a session active (verified by the acceptance of the license), and hence cookies?
if (false) {
	$InputError = 1;
	echo '<div class="error">' . __('Please set Cookies allowed in your web browser, otherwise webERP cannot run properly') . '</div>';
} else {
	echo '<div class="success">' . __('Cookies are properly enabled in your browser') . '</div>';
}

//It's time to check the php version. The version should be 8.1 or above
/// @todo grab the version to check for from parsing composer.json
if (version_compare(PHP_VERSION, '8.1.0') < 0) {
	$InputError = 1;
	echo '<div class="error">' . __('You PHP version should be equal or greater than') . ' 8.1</div>';
} else {
	echo '<div class="success">' . __('Your PHP version is suitable for webERP') . '</div>';
}

//Check the write access of the root directory
$RootDir = substr(dirname(__DIR__), 0, -8);
if (!is_writable($RootDir)) {
	$InputError = 1;
	echo '<div class="success">' . __('The base webERP directory must be writable by the web server') . '</div>';
} else {
	echo '<div class="success">' . __('The base webERP directory is writable') . '</div>';
}

//Check the write access of the companies directory
$CompaniesDir = $RootDir . '/companies';
if (!is_writable($CompaniesDir)) {
	$InputError = 1;
	echo '<div class="error">' . __('The directory') . ' ' . $CompaniesDir . '/companies/' . ' ' . __('must be writable by the web server') . '</div>';
} else {
	echo '<div class="success">' . __('The "companies/" directory is writable') . '</div>';
}

//get the list of installed extensions
$Extensions = get_loaded_extensions();

/// @todo grab the list of required and recommended extensions from parsing composer.json

// First check the gd extension
/// @todo gd is, strictly speaking, not required. Make this a warning?
if (!in_array('gd', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . __('The GD extension should be installed in your PHP configuration') . '</div>';
} else {
	echo '<div class="success">' . __('The GD extension is correctly installed') . '</div>';
}

if (!in_array('gettext', $Extensions)) {
	echo '<div class="warning">' . __('The gettext extension is not available in your PHP') . '</div>';
} else {
	echo '<div class="success">' . __('The gettext extension is correctly installed') . '</div>';
}

// Check the mbstring extension, it must exist
// NB: if mbstring was not enabled, we would never reach here (the check is done in index.php)
if (!in_array('mbstring', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . __('The mbstring extension is not available in your PHP') . '</div>';
} else {
	echo '<div class="success">' . __('The mbstring extension is correctly installed') . '</div>';
}

// Check the libxml extension
if (!in_array('libxml', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . __('The libxml extension is not available in your PHP') . '</div>';
} else {
	echo '<div class="success">' . __('The libxml extension is correctly installed') . '</div>';
}

// Check that the extension used for DBMS connections is installed
if (!in_array('mysqli', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . __('You do not have the correct database extension installed for PHP (mysqli)') . '</div>';
} else {
	echo '<div class="success">' . __('The database extension is correctly installed') . '</div>';
}
