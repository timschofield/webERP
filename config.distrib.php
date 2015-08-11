<?php

/* $Id: config.distrib.php 7326 2015-06-28 01:09:00Z daintree $ */

// User configurable variables
//---------------------------------------------------

// Default language to use for the login screen and the setup of new users.
//The users' language selection will override
$DefaultLanguage = 'en_GB.utf8';

// Default theme to use for the login screen and the setup of new users.
//The users' theme selection will override
//$DefaultTheme = 'professional';
//$DefaultTheme = 'wood';
//$DefaultTheme = 'silverwolf';
//$DefaultTheme = 'gel';
$DefaultTheme = 'xenos';

// Whether to display the demo login and password or not on the login screen
$AllowDemoMode = True;

// email address of the system administrator
$SysAdminEmail = 'admin@mydomain.com';

// The timezone of the business - this allows the possibility of having
// the web-server on a overseas machine but record local time
// this is not necessary if you have your own server locally
//date_default_timezone_set('Europe/London');
//date_default_timezone_set('America/Los_Angeles');
date_default_timezone_set('Asia/Shanghai');
//date_default_timezone_set('Australia/Melbourne');
//date_default_timezone_set('Australia/Sydney');
//date_default_timezone_set('Pacific/Auckland');

// Connection information for the database
// $host is the computer ip address or name where the database is located
// if the web server is also the database server then 'locahost'
$host = 'localhost';
$mysqlport=3306;
//The type of db server being used
//$DBType = 'postgres' - now DEPRECIATED;
//$DBType = 'mysql';
//$DBType = 'mysqli'; for PHP 5 and mysql > 4.1
$DBType = 'mysqli';


// sql user & password
$DBUser = 'weberp_db_user';
$DBPassword = 'weberp_db_pwd';

// It would probably be inappropraite to allow selection of the company in a hosted envionment so this option can be switched to 'ShowInputBox' or 'Hide'
// depending if you allow the user to select the name of the company or must use the default one described at $DefaultCompany
// If set to 'ShowSelectionBox' webERP examines each of the directories under the companies directory to determine all the companies that can be logged into
// a new company directory together with the necessary subdirectories is created each time a new company is created by Z_MakeNewCompany.php
// It would also be inappropiate in some environments to show the name of the company (database name) --> Choose 'Hide'.
// Options:
// 	'ShowSelectionBox' (default)
//	'ShowInputBox'
//	'Hide'

//$AllowCompanySelectionBox = 'Hide';
//$AllowCompanySelectionBox = 'ShowInputBox';
$AllowCompanySelectionBox = 'ShowSelectionBox';

//If $AllowCompanySelectionBox is not 'ShowSelectionBox' above then the $DefaultDatabase string is used to determined the default Company
//entered in the login screen as a default, otherwise the user is expected to know the name of the company to log into.
$DefaultDatabase = 'weberpdemo';

//The maximum time that a login session can be idle before automatic logout
//time is in seconds  3600 seconds in an hour
$SessionLifeTime = 3600;

//The maximum time that a script can execute for before the web-server should terminate it
$MaximumExecutionTime =120;

/*The path to which session files should be stored in the server - useful for some multi-host web servers where pages are serviced using load balancing servers - when the load picks a different server then the session can be lost unless this option is used - which tells the server explicitly where to find the session file.
It is also useful where there are several webERP installs where the code is in two different paths on the same server and being used by the same client browser. It is possible in this scenario for the session to be over-written by the two different webERP installations. The solution is to specify different $SessionSavePath in each installations config.php
If there is only one installation of webERP on the web-server - which can be used with many company databases (and there is no load balancing difficulties to circumvent then this can be left commented out
*/
//$SessionSavePath = '/tmp';

//Setting to 12 or 24 determines the format of the clock display at the end of all screens
$DefaultClock = 12;
//$DefaultClock = 24;



// END OF USER CONFIGURABLE VARIABLES



//The $RootPath is used in most scripts to tell the script the installation details of the files.
//NOTE: In some windows installation this command doesn't work and the administrator must set this to the path of the installation manually:
//eg. if the files are under the webserver root directory then rootpath ='';
//if they are under webERP then webERP is the rootpath - notice no additional slashes are necessary.

$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'));
if (isset($DirectoryLevelsDeep)){
	for ($i=0;$i<$DirectoryLevelsDeep;$i++){
		$RootPath = mb_substr($RootPath,0, strrpos($RootPath,'/'));
	}
}

if ($RootPath == "/" OR $RootPath == "\\") {
	$RootPath = "";
}


//Report all errors except E_NOTICE
//This is the default value set in php.ini for most installations
//but just to be sure it is forced here
//turning on NOTICES destroys things */
error_reporting(E_ALL && ~E_NOTICE && E_WARNING);
/* For Development Use */
//error_reporting (-1);

//Installed companies
$CompanyList[0] = array('database'=>'weberpdemo' ,'company'=>'WebERP Demo Company' );
$CompanyList[1] = array('database'=>'your_db' ,'company'=>'Your Company inc' );
/*Make sure there is nothing - not even spaces after this last ?> */
?>
