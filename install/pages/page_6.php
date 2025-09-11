<?php

if (!isset($PathPrefix)) {
	header('Location: ../');
	exit();
}

//ob_start();

if (!isset($_POST['install'])) {
	header('Location: index.php');
	exit();
}

include($PathPrefix . 'includes/InstallFunctions.php');
include($PathPrefix . 'includes/DateFunctions.php');

$Path_To_Root = rtrim($PathPrefix, '/');

$Host = $_SESSION['Installer']['HostName'];
$DBUser = $_SESSION['Installer']['UserName'];
$DBPassword = $_SESSION['Installer']['Password'];
$DBType = $_SESSION['Installer']['DBMS'];
$DBPort = $_SESSION['Installer']['Port'];
$Database = $_SESSION['DatabaseName'] = $_SESSION['Installer']['Database'];
//$DefaultDatabase = 'default';

$_SESSION['CompanyRecord']['coyname'] = $_POST['CompanyName'];
$_SESSION['Installer']['CoA'] = $_POST['COA'];
$_SESSION['Installer']['TimeZone'] = $_POST['TimeZone'];
if (isset($_POST['Demo'])) {
	$_SESSION['Installer']['Demo'] = $_POST['Demo'];
} else {
	$_SESSION['Installer']['Demo'] = 'No';
}

date_default_timezone_set($_SESSION['Installer']['TimeZone']);

if (!CreateDataBase($Host, $DBUser, $DBPassword, $Database, $DBPort, $DBType, $Path_To_Root)) {
	return;
}

/// @todo we could change ConnectDB so that it does not create a new DB connection, and reuse the one we just opened...
include($PathPrefix . 'includes/ConnectDB_' . $DBType . '.php');
include($PathPrefix . 'includes/UpgradeDB_' . $DBType . '.php');

// gg: unused variable?
//$DB = @mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password'], $_SESSION['DatabaseName']);

if (!CreateCompanyFolder($Database, $Path_To_Root)) {
	return;
}

// Make installer options compatible with config.distrib.php options.
/**
 * IMPORTANT!!
 * Must match the variables found inside config.distrib.php.
 */
$configArray = $_SESSION['Installer'];
$configArray += [
	'Host'            => $Host,
	'DBUser'          => $DBUser,
	'DBPassword'      => $DBPassword,
	'DBPort'          => $DBPort,
	'DBType'          => $DBType,
	'DefaultLanguage' => $_SESSION['Installer']['Language'],
	'DefaultDatabase' => $Database,
	'SysAdminEmail'   => $_SESSION['Installer']['AdminEmail']
];

if (!CreateConfigFile($Path_To_Root, $configArray, $_SESSION['Installer']['TimeZone'])) {
	return;
}

if (!CreateTables($Path_To_Root, $DBType)) {
	return;
}

if (!CreateGLTriggers($Path_To_Root, $DBType)) {
	return;
}

if (!UploadData($_SESSION['Installer']['Demo'],
			$_SESSION['Installer']['AdminPassword'],
			$_SESSION['Installer']['AdminUser'],
			$_SESSION['Installer']['Email'],
			$_SESSION['Installer']['Language'],
			$_SESSION['Installer']['CoA'],
			$_SESSION['CompanyRecord']['coyname'],
			$Path_To_Root,
			$Database,
			$DBType)) {
	return;
};

/// @todo wouldn't it make more sense to have this be run as part of CreateCompanyFolder, or just after it?
if (!CreateCompaniesFile($Path_To_Root, $Database, $_SESSION['CompanyRecord']['coyname'])) {
	return;
}

$Installed = true;
