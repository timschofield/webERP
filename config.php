<?php
/*******************************************************************************
*
* KL RICARD: A lot of variables moved to KLConfig.php
*
********************************************************************************/

// User configurable variables

// type of webERP (TEST or Production)
// Moved to KLConfig.php 

// DefaultLanguage to use for the login screen and the setup of new users
// the users language selection will override
$DefaultLanguage = 'en_GB.utf8';

// Default theme to use for the login screen and the setup of new users.
// The users' theme selection will override
// $DefaultTheme = 'professional';
// $DefaultTheme = 'wood';
// $DefaultTheme = 'silverwolf';
$DefaultTheme = 'gel';
// $DefaultTheme = 'xenos';

// Whether to display the demo login and password or not on the login screen
$AllowDemoMode = false;

// Whether to display company logo in drop down list at login
$ShowLogoAtLogin = false;

// email address of the system administrator
$SysAdminEmail = 'webmaster@kapal-laut.com';

// The timezone of the business - this allows the possibility of having
// the web-server on a overseas machine but record local time
// this is not necessary if you have your own server locally
date_default_timezone_set('Asia/Singapore');

// Connection information for the database
// $Host is the computer ip address or name where the database is located
// assuming that the web server is also the sql server
// $Host Moved to KLConfig.php
// $Host = 'localhost';
$MySQLPort = 3306;

// The type of db server being used
// $DBType = 'postgres' - now DEPRECIATED;
// $DBType = 'mysql';
// $DBType = 'mysqli';
$DBType = 'mariadb';

// sql user & password
// Moved to KLConfig.php

// It would probably be inappropraite to allow selection of the company in a hosted envionment so this option can be switched
// to 'ShowInputBox' or 'Hide' depending if you allow the user to select the name of the company or must use the default one
// described at $DefaultCompany. If set to 'ShowSelectionBox' webERP examines each of the directories under the companies
// directory to determine all the companies that can be logged into a new company directory together with the necessary
// subdirectories is created each time a new company is created by Z_MakeNewCompany.php. It would also be inappropiate
// in some environments to show the name of the company (database name) --> Choose 'Hide'.
// Options:
// 	'ShowSelectionBox' (default)
// 	'ShowInputBox'
// 	'Hide'
$AllowCompanySelectionBox = 'Hide';

// If $AllowCompanySelectionBox is not 'ShowSelectionBox' above then the $DefaultCompany string is entered in the login screen
// as a default otherwise the user is expected to know the name of the company to log into.

// The maximum time that a login session can be idle before automatic logout
// time is in seconds  3600 seconds in an hour
$SessionLifeTime = 3600;

// The maximum time that a script can execute for before the web-server should terminate it
$MaximumExecutionTime = 720;

// The path to which session files should be stored in the server - useful for some multi-host web servers
// this can be left commented out
// $SessionSavePath = '/tmp';


// which encryption function should be used
// $CryptFunction = "md5"; // MD5 Hash
$CryptFunction = "sha1"; // SHA1 Hash
// $CryptFunction = ""; // Plain Text

// Setting to 12 or 24 determines the format of the clock display at the end of all screens
// $DefaultClock = 12;
$DefaultClock = 24;

// The $RootPath is used in most scripts to tell the script the installation details of the files.

$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));

// Report all errors except E_NOTICE
// Moved to KLConfig.php
