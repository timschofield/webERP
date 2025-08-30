<?php

if (!isset($PathPrefix)) {
	header('Location: ../');
	exit();
}

$Host = $_SESSION['Installer']['HostName'];
$DBUser = $_SESSION['Installer']['UserName'];
$DBPassword = $_SESSION['Installer']['Password'];
$DBType = $_SESSION['Installer']['DBMS'];
$DBPort = $_SESSION['Installer']['Port'];
$_SESSION['DatabaseName'] = $_SESSION['Installer']['Database'];
$DefaultDatabase = 'default';

//ob_start();

if (isset($_POST['install'])) {
	$_SESSION['CompanyRecord']['coyname'] = $_POST['CompanyName'];
	$_SESSION['Installer']['CoA'] = $_POST['COA'];
	$_SESSION['Installer']['TimeZone'] = $_POST['TimeZone'];
	if (isset($_POST['Demo'])) {
		$_SESSION['Installer']['Demo'] = $_POST['Demo'];
	} else {
		$_SESSION['Installer']['Demo'] = 'No';
	}
}

include($PathPrefix . 'includes/InstallFunctions.php');

$Errors = CreateDataBase($Host, $DBUser, $DBPassword, $_SESSION['Installer']['Database'], $DBPort);

if (count($Errors)) {
	echo '<div class="error">' . __('Unable to create the database schema.') . '</div>';

	// display the errors
	echo '<div class="error">';
	foreach ($Errors as $error) {
		echo '<p>' . htmlspecialchars($error) . "</p>\n";
	}
	echo '</div>';

	return;
}

include($PathPrefix . 'includes/ConnectDB_' . $DBType . '.php');
include($PathPrefix . 'includes/UpgradeDB_' . $DBType . '.php');

// gg: unused variable?
//$DB = @mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password'], $_SESSION['DatabaseName']);

include($PathPrefix . 'includes/DateFunctions.php');
date_default_timezone_set($_SESSION['Installer']['TimeZone']);
$Path_To_Root = rtrim($PathPrefix, '/');
$Config_File = $Path_To_Root . '/config.php';

CreateCompanyFolder($_SESSION['Installer']['Database'], $Path_To_Root);

// Make installer options compatible with config.distrib.php options.
/**
 * IMPORTANT!!
 * Must match the variables found inside config.distrib.php.
 */
$configArray = $_SESSION['Installer'];
$configArray += [
	'Host'            => $_SESSION['Installer']['HostName'],
	'DBUser'          => $_SESSION['Installer']['UserName'],
	'DBPassword'      => $_SESSION['Installer']['Password'],
	'DBPort'          => $_SESSION['Installer']['Port'],
	'DBType'          => $_SESSION['Installer']['DBMS'],
	'DefaultLanguage' => $_SESSION['Installer']['Language'],
	'DefaultDatabase' => $_SESSION['Installer']['Database'],
	'SysAdminEmail'   => $_SESSION['Installer']['AdminEmail']
];

// The config files are in the main directory
$SampleConfigFile = $Path_To_Root . '/config.distrib.php';
$NewConfigFile = $Path_To_Root . '/config.php';

// Read the content of the sample config file
if (!file_exists($SampleConfigFile)) {
	echo '<div class="error">' . __('The sample configuration file does not exist.') . '</div>';
}

// Open the sample file for reading and create the new config file for writing
$SampleHandle = fopen($SampleConfigFile, 'r');
$NewLines = [];

if ($SampleHandle) {
	while (($Line = fgets($SampleHandle)) !== false) {
		// Check if the line is commented (starting with //, #, or within /* */)
		$isComment = preg_match('/^\s*(\/\/|#|\/\*|\*\/)/', $Line);

		// Skip replacements on comment lines, otherwise process a config line.
		if (!$isComment) {
			// Loop Installer Data
			foreach ($configArray as $key => $Value) {
				// if (strpos($Line, $key) !== false) {
				if (preg_match('/\$\b' . preg_quote($key, '/') . '\b/', $Line)) {
					$NewValue = addslashes($Value);
					$Line = "\$$key = '$NewValue';\n";
					unset($configArray[$key]);
				}
			}
			// Replace date_default_timezone_set
			if (strpos($Line, 'date_default_timezone_set') !== false) {
				$NewValue = addslashes($_SESSION['Installer']['TimeZone']);
				$Line = "date_default_timezone_set('".$NewValue."');\n";
			}
		}
		// Append the line to the new content
		$NewLines[] = $Line;
	}

	fclose($SampleHandle);
} else {
	echo '<div class="error">' . __('Unable to read the sample configuration file.') . '</div>';
}

// Write the updated content to the new config file
$NewConfigContent = implode($NewLines);
if (file_put_contents($NewConfigFile, $NewConfigContent)) {
	echo '<div class="success">' . __('The config.php file has been created based on your settings.') . '</div>';
} else {
	echo '<div class="error">' . __('Cannot write to the configuration file') . $Config_File . '</div>';
}

CreateTables($Path_To_Root);

CreateGLTriggers($Path_To_Root);

UploadData($_SESSION['Installer']['Demo'],
			$_SESSION['Installer']['AdminPassword'],
			$_SESSION['Installer']['AdminUser'],
			$_SESSION['Installer']['Email'],
			$_SESSION['Installer']['Language'],
			$_SESSION['Installer']['CoA'],
			$_SESSION['CompanyRecord']['coyname'],
			$Path_To_Root,
			$_SESSION['Installer']['Database']);

$SQL = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('part_pics_dir','companies/" . $_SESSION['DatabaseName'] . "/part_pics')";
$Result = DB_query($SQL);

$CompanyFileHandler = fopen($Path_To_Root . '/companies/' . $_SESSION['DatabaseName'] . '/Companies.php', 'w');
$Contents = "<?php\n\n";
$Contents.= "\$CompanyName['" . $_SESSION['DatabaseName'] . "'] = '" . $_SESSION['CompanyRecord']['coyname'] . "';\n";

if (!fwrite($CompanyFileHandler, $Contents)) {
	fclose($CompanyFileHandler);
	echo '<div class="error">' . __('Cannot write to the Companies.php file') . '</div>';
}
//close file
fclose($CompanyFileHandler);

$Installed = true;
