<?php
/* $Revision: 1.45 $ */
	/*--------------------------------------------------\
	| 		|               | config.php        |
	|---------------------------------------------------|
	| Web-ERP - http://web-erp.sourceforge.net          |
	| by Logic Works Ltd                                |
	|---------------------------------------------------|
	|                                                   |
	\--------------------------------------------------*/

// User configurable variables
//---------------------------------------------------

//DefaultLanguage
$DefaultLanguage ='en_GB';

// Whether to display the demo login and password or not 
$allow_demo_mode = True;

// Application version
$Version = '2.9c';

// The timezone of the business - this allows the possibility of having
// the web-server on a overseas machine but record local time
// this is not necessary if you have your own server locally
// putenv('TZ=Europe/London');
// putenv('Australia/Melbourne');
// putenv('Australia/Sydney');
// putenv('TZ=Pacific/Auckland');

// Connection information for the database
// $host is the computer ip address or name where the database is located
// assuming that the web server is also the sql server
$host = 'localhost';

//The type of db server being used - currently only postgres or mysql
$dbType = 'mysql';
//$dbType = 'postgres';
//$dbType = 'mysql';

$DatabaseName = 'weberp';

// sql user & password
$dbuser = 'weberp_db_user';
$dbpassword = 'weberp_db_pwd';

//The maximum time that a login session can be idle before automatic logout
//time is in seconds  3600 seconds in an hour
$SessionLifeTime = 3600;


// END OF USER CONFIGURABLE VARIABLES

/*The $rootpath is used in most scripts to tell the script the installation details of the files.

NOTE: In some windows installation this command doesn't work and the administrator must set this to the path of the installation manually:
eg. if the files are under the webserver root directory then rootpath =''; if they are under weberp then weberp is the rootpath - notice no additional slashes are necessary.
*/

$rootpath = dirname($_SERVER['PHP_SELF']);
//$rootpath = '/web-erp';

/* Report all errors except E_NOTICE
This is the default value set in php.ini for most installations but just to be sure it is forced here
turning on NOTICES destroys things */

error_reporting (E_ALL & ~E_NOTICE);

/*Dont modify this bit
function required if gettext is not installed */

if (!function_exists('_')){
	function _($text){
		return ($text);
	}
}
/*Make sure there is nothing -not even spaces after this last ?> */
?>
