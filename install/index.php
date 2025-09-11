<?php

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

require(__DIR__.'/../vendor/autoload.php');

ini_set('max_execution_time', "6000");
session_name('weberp_installation');
session_start();

if (!extension_loaded('mbstring')) {
	echo 'The php-mbstring extension has not been installed or loaded, please correct your php configuration first';
	exit();
}

$PathPrefix = realpath(__DIR__ . '/../') . '/';

$SessionExpired = false;
if (isset($_GET['Page']) && $_GET['Page'] > 0 && $_GET['Page'] <= 6) {
	if (is_array($_SESSION['Installer'])) {
		$_SESSION['Installer']['CurrentPage'] = (int)$_GET['Page'];
	} else {
		$SessionExpired = true;
		/// @todo the code below will generate php warnings, as items in $_SESSION['Installer'] will be missing...
	}
} else {
	$_SESSION['Installer'] = array();
	$_SESSION['Installer']['CurrentPage'] = 0;
	$_SESSION['Installer']['License_Agreed'] = false;
	$_SESSION['Installer']['Port'] = 3306;
	$_SESSION['Installer']['HostName'] = '';
	$_SESSION['Installer']['Database'] = '';
	$_SESSION['Installer']['UserName'] = '';
	$_SESSION['Installer']['Password'] = '';
	$_SESSION['Installer']['DBMS'] = 'mysqli';
	$_SESSION['Installer']['AdminUser'] = 'admin';
	// discourage default passwords
	$_SESSION['Installer']['AdminPassword'] = '';
	$_SESSION['Installer']['AdminEmail'] = '';
	$_SESSION['Installer']['Language'] = 'en_GB.utf8';
	$_SESSION['Installer']['CoA'] = 'en_GB.utf8';
	/// @todo rename - why not use $_SESSION['Installer']['coyname'] ?
	$_SESSION['CompanyRecord']['coyname'] = '';
	$_SESSION['Installer']['TimeZone'] = 'Europe/London';
	$_SESSION['Installer']['Email'] = 'info@example.com';
	$_SESSION['Installer']['Demo'] = 'No';
}

if ($_SESSION['Installer']['CurrentPage'] == 1) {
	if (isset($_GET['Agreed']) && $_GET['Agreed'] == 'Yes') {
		$_SESSION['Installer']['License_Agreed'] = true;
	} else {
		$_SESSION['Installer']['License_Agreed'] = false;
	}
}

if (!$_SESSION['Installer']['License_Agreed'] && $_SESSION['Installer']['CurrentPage'] >=2) {
	header('Location: index.php?Page=1');
	exit();
}

/// @todo review - do we need MiscFunctions.php?
include($PathPrefix . 'includes/MiscFunctions.php');

// Need the language in this variable as this is the variable used elsewhere in webERP
/// @todo is that true? There seems to be no usage of $DefaultLanguage in the installer code, nor in any other functions it uses...
$DefaultLanguage = $_SESSION['Installer']['Language'];
include($PathPrefix . 'includes/LanguageSetup.php');

echo "<!DOCTYPE html>\n";

echo '<html lang="' . str_replace('_', '-', substr($_SESSION['Installer']['Language'], 0, 5)) . '">' . "\n";

$Title = __('WebERP Installation Wizard');

echo '<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>', $Title, '</title>
		<link rel="icon" href="../favicon.ico" type="image/x-icon" />
		<link rel="stylesheet" type="text/css" href="installer.css" />
	</head>' . "\n";

echo '<body>' . "\n";

echo '<div class="wizard">
		<header>', $Title, '</header>
		<img id="main_icon" src="images/installer.png" />' . "\n";

if ($SessionExpired) {
	/// @todo display a warning and a link to the starting page
} else {
	include($PathPrefix . 'install/pages/page_' . $_SESSION['Installer']['CurrentPage'] . '.php');
}

/// @todo why not move all of the code below in the single pages?

echo  "\n<footer>\n";

if (isset($_SESSION['Installer']['License_Agreed']) and !$_SESSION['Installer']['License_Agreed'] and $_SESSION['Installer']['CurrentPage'] == 1) {
	echo '<div class="nav_button">
			<a id="next" class="is_disabled" href="">
				', __('Next'), '
			</a>
				<img src="images/right.png" style="float:right" />
		</div>';
} elseif ($_SESSION['Installer']['CurrentPage'] == 3 and ($Result != 'valid')) {
	echo '<div class="nav_button">
			<a id="next" class="is_disabled" href="">
				', __('Next'), '
			</a>
				<img src="images/right.png" style="float:right" />
		</div>';
} elseif ($_SESSION['Installer']['CurrentPage'] == 4 and ($DataSaved != 'yes')) {
	echo '<div class="nav_button">
			<a id="next" class="is_disabled" href="">
				', __('Next'), '
			</a>
				<img src="images/right.png" style="float:right" />
		</div>';
} elseif ($_SESSION['Installer']['CurrentPage'] == 5) {
	/// @todo only enable the link via js after the form fields have been filled
	echo '<input type="submit" class="install nav_button" name="install" value="', __('Install'), '" />
</form>';
} elseif ($_SESSION['Installer']['CurrentPage'] == 6) {
	if (isset($Installed) && $Installed) {
		echo '<div class="nav_button">
			<a href="../Logout.php?Installed=Yes">', __('Restart webERP'), '</a>
				<img src="images/restart.png"  style="float:right; width:24px;">
		</div>';
	} else {
		echo '<div class="nav_button">
			<a href="index.php?Page=0">', __('Restart the install wizard'), '</a>
				<img src="images/restart.png"  style="float:right; width:24px;">
		</div>';
	}
} else {
	echo '<div class="nav_button">
			<a href="index.php?Page=', ($_SESSION['Installer']['CurrentPage'] + 1), '">', __('Next'), '</a>
				<img src="images/right.png"  style="float:right">
		</div>';
}

if ($_SESSION['Installer']['CurrentPage'] != 0 and $_SESSION['Installer']['CurrentPage'] != 6) {
	echo '<div class="nav_button">
			<a href="index.php?Page=', ($_SESSION['Installer']['CurrentPage'] - 1), '">', __('Previous'), '</a>
				<img src="images/left.png" style="float:left">
		</div>';
}

echo '			</footer>
		</div>
	</body>
</html>';
