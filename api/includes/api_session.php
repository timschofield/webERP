<?php

/**
 * "api" equivalent of session.php
 */

if (!isset($PathPrefix)) {
	$PathPrefix = __DIR__ . '/../../';
}

require($PathPrefix.'vendor/autoload.php');

// error out if config.php does not yet exist
if (!file_exists($PathPrefix . 'config.php')) {
	// gg: there is no need for htmlspecialchars here, as we never output $RootPath into html
	// assumes the api entrypoint script is inside the /api folder is
	$RootPath = dirname(dirname($_SERVER['PHP_SELF']));
	if ($RootPath == '/' or $RootPath == "\\") {
		$RootPath = '';
	}
	header('Location:' . $RootPath . '/install/index.php');
	exit();
}

$DefaultDatabase = 'weberpdemo';

include($PathPrefix . 'config.php');

// an upgrade issue - mysql php extension is not available anymore, unless users are on obsolete php versions
if ($DBType === 'mysql' && !extension_loaded('mysql')) {
	/// @todo we should attempt to update the config.php file...
	$DBType = 'mysqli';
}

// another upgrade issue
if (isset($MySQLPort) && !isset($DBPort)) {
	/// @todo we should attempt to update the config.php file...
	$DBPort = $MySQLPort;
	unset($MySQLPort);
}

if (isset($SessionSavePath)) {
	session_save_path($SessionSavePath);
}

if (!isset($SysAdminEmail)) {
	$SysAdminEmail = '';
}

ini_set('session.gc_Maxlifetime', $SessionLifeTime);
ini_set('max_execution_time', $MaximumExecutionTime);

session_write_close(); //in case a previous session is not closed
ini_set('session.cookie_httponly', 1);

session_name('webERPapi');
session_start();

include($PathPrefix . 'includes/LanguageSetup.php');
//  Establish a DB connection, if possible. NOTE that this connection
//  may not have the same 'value' as any previous connection, so
//  save the new one in the session variable.
if (isset($_SESSION['DatabaseName']) AND $_SESSION['DatabaseName'] != '') {
	include($PathPrefix . 'includes/ConnectDB.php');
	/// @todo handle case where $db is null
	$_SESSION['db'] = $db;
}
include_once($PathPrefix . 'includes/DateFunctions.php');

// Un comment to turn off attempts counter
//$_SESSION['AttemptsCounter'] = 0;

if (!isset($_SESSION['AttemptsCounter'])) {
	$_SESSION['AttemptsCounter'] = 0;
}

if (isset($_SESSION['HTTPS_Only']) AND $_SESSION['HTTPS_Only']==1) {
	if ($_SERVER['HTTPS']!='on') {
		prnMsg(__('webERP is configured to allow only secure socket connections. Pages must be called with https://') . ' .....','error');
		exit();
	}
}

/// @todo handle the need for DB updates as in session.php

// Now check that the user as logged in has access to the page being called. The $PageSecurity
// value must be set in the script before header.php is included. $SecurityGroups is an array of
// arrays defining access for each group of users. These definitions can be modified by a system admin under setup

/// @todo move to LoginFunctions.php
if (! function_exists('CryptPass')) {
	function CryptPass($Password)
	{
		$hash = password_hash($Password, PASSWORD_DEFAULT);
		return $hash;
	}
}

/// @todo move to LoginFunctions.php
if (! function_exists('VerifyPass')) {
	function VerifyPass($Password, $Hash)
	{
		return password_verify($Password, $Hash);
	}
}
