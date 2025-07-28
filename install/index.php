<?php

require __DIR__.'/../vendor/autoload.php';

ini_set('max_execution_time', "6000");
session_name('weberp_installation');
session_start();

if (!extension_loaded('mbstring')) {
	echo 'The php-mbstring extension has not been installed or loaded, please correct your php configuration first';
	exit();
}

$PathPrefix = __DIR__ . '/../';

if (isset($_GET['Page'])) {
	/// @todo check: if $_SESSION['Installer'] is not set or not an array, redirect to `/install/index.php`
	$_SESSION['Installer']['CurrentPage'] = $_GET['Page'];
} else {
	unset($_SESSION['Installer']);
	$_SESSION['Installer']['CurrentPage'] = 0;
	$_SESSION['Installer']['License_Agreed'] = False;
	$_SESSION['Installer']['Port'] = 3306;
	$_SESSION['Installer']['HostName'] = '';
	$_SESSION['Installer']['Database'] = '';
	$_SESSION['Installer']['UserName'] = '';
	$_SESSION['Installer']['Password'] = '';
	$_SESSION['Installer']['DBMS'] = 'mysqli';
	$_SESSION['Installer']['AdminUser'] = 'admin';
	$_SESSION['Installer']['AdminPassword'] = 'weberp';
	$_SESSION['Installer']['AdminEmail'] = '';
	$_SESSION['Installer']['AdminUser'] = 'admin';
	$_SESSION['Installer']['AdminEmail'] = '';
	$_SESSION['Installer']['AdminPassword'] = 'weberp';
	$_SESSION['Installer']['Language'] = 'en_GB.utf8';
	$_SESSION['Installer']['CoA'] = 'en_GB.utf8';
	$_SESSION['CompanyRecord']['coyname'] = '';
	$_SESSION['Installer']['TimeZone'] = 'Europe/London';
	$_SESSION['Installer']['Email'] = 'info@example.com';
	$_SESSION['Installer']['AdminAccount'] = 'admin';
	$_SESSION['Installer']['AdminPassword'] = 'weberp';
	$_SESSION['Installer']['Demo'] = 'No';
}

if (isset($_GET['Agreed'])) {
	$_SESSION['Installer']['License_Agreed'] = True;
}

include ($PathPrefix . 'includes/MiscFunctions.php');
include ($PathPrefix . 'includes/LanguagesArray.php');
$DefaultLanguage = $_SESSION['Installer']['Language']; // Need the language in this variable as this is the variable used elsewhere in webERP
include ($PathPrefix . 'includes/LanguageSetup.php');

/*
 * Web ERP Installer
 * Step 0: Choose Language and Introduction
 * Step 1: Licence acknowledgement
 * Step 2: Check requirements
 * Step 3: Database connection
 * Step 4: Company details
 * Step 5: Administrator account details
 * Step 6: Finalise
**/

/// @todo move to html5, as the rest of the app
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

echo '<html xmlns="http://www.w3.org/1999/xhtml">';

$Title = _('WebERP Installation Wizard');

echo '<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>', $Title, '</title>
		<link rel="stylesheet" type="text/css" href="installer.css" />
		<script src="misc_functions.js"></script>
	</head>';

echo '<body>';

echo '<div class="wizard">
		<header>', $Title, '</header>
		<img id="main_icon" src="images/installer.png" />';

include('pages/page_' . $_SESSION['Installer']['CurrentPage'] . '.php');

echo '<footer>';

if (isset($_SESSION['Installer']['License_Agreed']) and !$_SESSION['Installer']['License_Agreed'] and $_SESSION['Installer']['CurrentPage'] == 1) {
	echo '<div class="nav_button">
			<a id="next" class="is_disabled" href="">
				', _('Next'), '
			</a>
				<img src="images/right.png" style="float:right" />
		</div>';
} elseif ($_SESSION['Installer']['CurrentPage'] == 3 and ($Result != 'valid')) {
	echo '<div class="nav_button">
			<a id="next" class="is_disabled" href="">
				', _('Next'), '
			</a>
				<img src="images/right.png" style="float:right" />
		</div>';
} elseif ($_SESSION['Installer']['CurrentPage'] == 4 and ($DataSaved != 'yes')) {
	echo '<div class="nav_button">
			<a id="next" class="is_disabled" href="">
				', _('Next'), '
			</a>
				<img src="images/right.png" style="float:right" />
		</div>';
} elseif ($_SESSION['Installer']['CurrentPage'] == 5) {
	echo '<input type="submit" class="install nav_button" name="install" value="', _('Install'), '" />';
} elseif ($_SESSION['Installer']['CurrentPage'] == 6) {
	echo '<div class="nav_button">
			<a href="../Logout.php?Installed=Yes">', _('Restart webERP'), '</a>
				<img src="images/restart.png"  style="float:right; width:24px;">
		</div>';
} else {
	echo '<div class="nav_button">
			<a href="index.php?Page=', ($_SESSION['Installer']['CurrentPage'] + 1), '">', _('Next'), '</a>
				<img src="images/right.png"  style="float:right">
		</div>';
}

if ($_SESSION['Installer']['CurrentPage'] != 0 and $_SESSION['Installer']['CurrentPage'] != 6) {
	echo '<div class="nav_button">
			<a href="index.php?Page=', ($_SESSION['Installer']['CurrentPage'] - 1), '">', _('Previous'), '</a>
				<img src="images/left.png" style="float:left">
		</div>';
}

echo '</footer>
	</div>
</form>';

echo '</body>
	</html>';
