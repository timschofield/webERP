<?php

// User configurable variables
//---------------------------------------------------

// Default language to use for the login screen and the setup of new users.
// The users' language selection will override this
$DefaultLanguage = 'en_GB.utf8';

// Default theme to use for the login screen and the setup of new users.
// The users' theme selection will override this
//$DefaultTheme = 'professional';
//$DefaultTheme = 'wood';
//$DefaultTheme = 'silverwolf';
//$DefaultTheme = 'gel';
$DefaultTheme = 'xenos';

// Whether to display the demo login and password or not on the login screen
$AllowDemoMode = False;

// Whether to display company logo in drop down list at login
$ShowLogoAtLogin = True;

// email address of the system administrator
$SysAdminEmail = 'admin@mydomain.com';

// The timezone of the business - this allows the possibility of having
// the web-server in a different timezone than the business but in general
// should always be set correctly (and PHP used to complain if not set)
//date_default_timezone_set('Europe/London');
//date_default_timezone_set('America/Los_Angeles');
//date_default_timezone_set('Asia/Shanghai');
//date_default_timezone_set('Australia/Melbourne');
//date_default_timezone_set('Australia/Sydney');
date_default_timezone_set('Pacific/Auckland');

// Connection information for the database
//
// $Host is the computer ip address or name where the database is located
// If the web server is also the database server then use 'localhost' or '127.0.0.1'
$Host = 'localhost';
$MySQLPort = 3306;
// The type of db server being used
//$DBType = 'mysql'; // DEPRECATED
//$DBType = 'mysqli';
//$DBType = 'mariadb';
//$DBType = 'postgres'; // UNSUPPORTED
$DBType = 'mysqli';

// Database user & password
$DBUser = 'weberp_db_user';
$DBPassword = 'weberp_db_pwd';

// Login company selection
//
// If allowing selection of the company in the login window is not desired (e.g. in a hosted environment and seeing other
// users would be distracting, create confusion or violate privacy expectations), change this option to 'ShowInputBox'
// to have the user enter the company name, or 'Hide' to force using $DefaultCompany.
//
// If set to 'ShowSelectionBox', webERP examines each sub-directory in companies/ to determine all the companies
// that can be logged into.
//$AllowCompanySelectionBox = 'Hide';
//$AllowCompanySelectionBox = 'ShowInputBox';
$AllowCompanySelectionBox = 'ShowSelectionBox';

// If $AllowCompanySelectionBox is not 'ShowSelectionBox' use $DefaultDatabase as the default Company (will be
// default in the login screen, otherwise the user must know the name of the company to log into).
$DefaultDatabase = 'weberpdemo';

// The maximum time that a login session can be idle before automatic logout
// time is in seconds  3600 seconds in an hour
$SessionLifeTime = 3600;

// The maximum time that a script can execute for before the web-server should terminate it
$MaximumExecutionTime = 120;

// Setting to 12 or 24 determines the format of the clock display at the end of all screens
$DefaultClock = 12;
//$DefaultClock = 24;

// Path to session files on server
// This can be useful for multi-host web servers where pages are serviced using load balancing servers, otherwise
// the session can be lost when the load balancer picks a different server. To prevent this, this option tells the
// server explicitly where to find the session file.
//
// This option is also useful when a server has multiple webERP installs (e.g. to serve multiple webERP sites using
// customized or different releases of code) and those can be accessed by the same client browser (e.g. by a user of
// both webERP sites, or a common administrator of them). The solution is to specify different $SessionSavePath in
// each installation's config.php
//
// If there is only one installation of webERP on the web-server - which can be used with many company databases (and
// there is no load balancing difficulties to circumvent), this can be left commented.
//$SessionSavePath = '/tmp';

// Set a specific session_name to avoid potential default session_name conflicts
// with other apps using the same host.
$SessionName = 'PHPSESSIDwebERPteam';

// END OF USER CONFIGURABLE VARIABLES

// $RootPath is used in most scripts to build links to assets and other scripts.
// It is the path to the webERP directory as "seen" from the user's browser (excluding the hostname and port).
// NOTE: In some windows installation the following line doesn't work and the administrator must set this to the
// path of the installation manually
// E.g. 1 if the files are under the webserver root directory, then rootpath ='';
// E.g. 2 if the files are under webERP, then webERP is the rootpath (note no additional slashes are necessary).
// NOTE: this value is outputted as-is as part of the html of the produced web pages, this is why we have to escape it
// here with the htmlspecialchars call
$RootPath = htmlspecialchars(dirname($_SERVER['PHP_SELF']), ENT_QUOTES, 'UTF-8');

if (isset($DirectoryLevelsDeep)) {
	for ($i = 0; $i < $DirectoryLevelsDeep; $i++) {
		$RootPath = mb_substr($RootPath, 0, strrpos($RootPath,'/'));
	}
}

if ($RootPath == "/" or $RootPath == "\\") {
	$RootPath = "";
}

// Report all errors except E_NOTICE
// (default value in php.ini for most installations, but it is forced here to be sure)
// Note: turning on NOTICES will destroy things
error_reporting(E_ALL && ~E_NOTICE && E_WARNING);
/* For Development Use */
//error_reporting(-1);

// Installed companies
// - see companies/dbname/Companies.php
