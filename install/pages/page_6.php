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

include ($PathPrefix . 'includes/InstallFunctions.php');
include ($PathPrefix . 'includes/DateFunctions.php');

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

// avoid exceptions being thrown on query errors
mysqli_report(MYSQLI_REPORT_ERROR);

$DB = @mysqli_connect($Host, $DBUser, $DBPassword, null, $DBPort);
if (!$DB) {
	echo '<div class="error">' . __('Unable to connect to the database to create the schema.') . '</div>';
	flush();
	return false;
}

$Errors = [];

/// @todo move this to a shared function (eg. in UpgradeDB_$DBType.php)
/// @todo allow to pick/suggest a preferred charset - possibly leave the choice hidden in the installer, so that
///       end users do not get confused, but allow the test suite to force the code path which uses utf8mb3
$ListCharsetsSQL = "SHOW COLLATION WHERE Charset = 'utf8mb4'";
$ListCharsetsResult = @mysqli_query($DB, $ListCharsetsSQL);
if ($ListCharsetsResult) {
	$Rows = mysqli_num_rows($ListCharsetsResult);
} else {
	echo '<div class="warning">' . __('Failed to check for the available database character sets.') . '</div>';
	$Rows = 0;
}

if ($Rows > 0) {
	$_SESSION['Installer']['DBCharset'] = 'utf8mb4';
} else {
	// NB: utf8 is an alias for utf8mb3, up to Mysql < 8.4, and mariadb < 10.6.1.
	// After that, it becomes an alias for utf8mb4.
	// A great win for BC! ;-)
	// Ideally, the value we pick here should be an utf8 encoding supported by all db-versions/php-versions.
	// The string 'utf8mb3' is a good candidate, but it was tested on both mysql 8.3 and mariadb 10.5, and it raised
	// php warnings in both cases :-(
	// Using 'utf8' instead worked, and got us a 3-bytes charset (tested with `mysqli_get_charset($DB)`).
	// So be it.
	// Final note: both mariadb and mysql support utf8mb4 since rev 5.5, which is less-or-equal to the minimum
	// DB version we support. So this whole block (and the above test) could in fact be removed...
	$_SESSION['Installer']['DBCharset'] = 'utf8';
}

if (!mysqli_set_charset($DB, $_SESSION['Installer']['DBCharset'])) {
	echo '<div class="warning">' . __('Failed setting the database connection character set to') . ' ' . htmlspecialchars($_SESSION['Installer']['DBCharset']) . '</div>';
}

// gg: we only use this db connection for creating the database, as we use separate one for creating tables etc.
//     So this seems useless/overkill
//$Result = @mysqli_query($DB, 'SET SQL_MODE=""');
//$Result = @mysqli_query($DB, 'SET SESSION SQL_MODE=""');
/// @todo move checking for permissions and for existing tables into UpgradeDB_$DBType.php, to give it a chance
///       at being db-agnostic
$DBExistsSql = "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '" . mysqli_real_escape_string($DB, $Database) . "'";
$DBExistsResult = @mysqli_query($DB, $DBExistsSql);
if ($DBExistsResult) {
	$Rows = mysqli_num_rows($DBExistsResult);
} else {
	echo '<div class="warning">' . __('Failed to check for the presence of the database schema.') . '</div>';
	$Rows = 0;
}

// this query is not failsafe (it does not work on every db type) - it is just as good not to check permissions and just try to create the db...
//$PrivilegesSql = "SELECT * FROM information_schema.USER_PRIVILEGES WHERE GRANTEE=" . '"' . "'" . mysqli_real_escape_string($DB, $UserName) . "'@'" . mysqli_real_escape_string($DB, $HostName) . "'" . '"' . " AND PRIVILEGE_TYPE='CREATE'";
//$PrivilegesResult = @mysqli_query($DB, $PrivilegesSql);
//$Privileges = @mysqli_num_rows($PrivilegesResult);
/// @todo exit with errors if any of the above failed?
if ($Rows == 0) { /* Then the database does not exist */
	//if ($Privileges == 0) {
	//	$Errors[] = __('The database does not exist, and this database user does not have privileges to create it');
	//} else { /* Then we can create the database */
	$SQL = "CREATE DATABASE " . $Database . " CHARACTER SET = " . $_SESSION['Installer']['DBCharset'];
	if (!@mysqli_query($DB, $SQL)) {
		$Errors[] = __('Failed creating the database');
	}
	//}

} else {

	/* Need to make sure NO data is removed from the existing DB */

	$SQL = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . $DataBaseName . "'";
	if (!($Result = @mysqli_query($DB, $SQL))) {
		$Errors[] = __('Failed enumerating existing database tables');
	} else {
		// only check tables which we will recreate
		$Rows = @mysqli_fetch_all($Result, MYSQLI_ASSOC);
		$ExistingTablesNum = count($Rows);
		$TableNames = array();
		foreach (glob($Path_To_Root . '/install/sql/tables/*.sql') as $FileName) {
			$SQLScriptFile = file_get_contents($FileName);
			if (preg_match('/^CREATE +TABLE +`?([^ (`]+)`?/', $SQLScriptFile, $matches)) {
				$TableNames[] = str_replace('`', '', $matches[1]);
			}
		}
		foreach ($Rows as $i => $Row) {
			if (!in_array($Row['TABLE_NAME'], $TableNames)) {
				unset($Rows[$i]);
			}
		}
		if (count($Rows)) {
			$Errors[] = __('Would overwrite' . ' ' . count($Rows) . ' ' . 'existing database tables');
		} else {
			if ($ExistingTablesNum > 0) {
				echo '<div class="warning">' . __('Found') . ' ' . $ExistingTablesNum . ' ' . __('unrelated tables in the existing schema') . '</div>';
			}
		}
	}
}

if ($Errors) {
	echo '<div class="error">' . __('Unable to create the database schema.') . '</div>';

	// display the errors
	echo '<div class="error">';
	foreach ($Errors as $error) {
		echo '<p>' . htmlspecialchars($error) . "</p>\n";
	}
	echo '</div>';

	return false;
}

// this session value is set within CreateDataBase and has to be set before including ConnectDB_xxx
$DBCharset = $_SESSION['Installer']['DBCharset'];

/// @todo we could change ConnectDB so that it does not create a new DB connection, and reuse the one we just opened...
include ($PathPrefix . 'includes/ConnectDB_' . $DBType . '.php');
include ($PathPrefix . 'includes/UpgradeDB_' . $DBType . '.php');

// gg: unused variable?
//$DB = @mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password'], $_SESSION['DatabaseName']);
$CompanyDir = $Path_To_Root . '/companies/' . $Database;

if (is_file($CompanyDir)) {
	echo '<div class="error">' . __('The company config directory can not be created as a file exists at its place') . '</div>';
	return false;
}

if (is_dir($CompanyDir)) {
	/// @todo allow more flexibility? Check if $database.bak does not exist, rename the current dir and go on
	$files = glob($CompanyDir . '/*');
	if (count($files)) {
		echo '<div class="error">' . __('The company config directory exists and is not empty') . '</div>';
		flush();
		return false;
	}
	$Result = true;
} else {
	$Result = mkdir($CompanyDir);
}

if ($Result) {
	$Result = $Result && mkdir($CompanyDir . '/part_pics');
	$Result = $Result && mkdir($CompanyDir . '/EDI_Incoming_Orders');
	$Result = $Result && mkdir($CompanyDir . '/reports');
	$Result = $Result && mkdir($CompanyDir . '/EDI_Sent');
	$Result = $Result && mkdir($CompanyDir . '/EDI_Pending');
	$Result = $Result && mkdir($CompanyDir . '/reportwriter');
	$Result = $Result && mkdir($CompanyDir . '/pdf_append');
	$Result = $Result && mkdir($CompanyDir . '/FormDesigns');
	$Result = $Result && mkdir($CompanyDir . '/logs');

	$Result = $Result && copy($Path_To_Root . '/companies/weberpdemo/FormDesigns/GoodsReceived.xml', $CompanyDir . '/FormDesigns/GoodsReceived.xml');
	$Result = $Result && copy($Path_To_Root . '/companies/weberpdemo/FormDesigns/PickingList.xml', $CompanyDir . '/FormDesigns/PickingList.xml');
	$Result = $Result && copy($Path_To_Root . '/companies/weberpdemo/FormDesigns/PurchaseOrder.xml', $CompanyDir . '/FormDesigns/PurchaseOrder.xml');
	$Result = $Result && copy($Path_To_Root . '/companies/weberpdemo/FormDesigns/Journal.xml', $CompanyDir . '/FormDesigns/Journal.xml');
}

if ($Result) {
	echo '<div class="success">' . __('The company config directory has been successfully created') . '</div>';
} else {
	echo '<div class="error">' . __('The company config directory was not created successfully') . '</div>';
}
flush();

// failure to save the logo is not fatal for the installer
if (is_dir($CompanyDir)) {
	if (isset($_FILES["LogoFile"]) && $_FILES["LogoFile"]["tmp_name"] != '') {
		SaveUploadedCompanyLogo($Database, $Path_To_Root);
	} else {
		CreateCompanyLogo($Database, $Path_To_Root, $CompanyDir);
	}
}

// Make installer options compatible with config.distrib.php options.

/**
 * IMPORTANT!!
 * Must match the variables found inside config.distrib.php.
 */
$configArray = $_SESSION['Installer'];
$configArray += ['Host' => $Host, 'DBUser' => $DBUser, 'DBPassword' => $DBPassword, 'DBPort' => $DBPort, 'DBType' => $DBType, 'DBCharset' => $_SESSION['Installer']['DBCharset'], 'DefaultLanguage' => $_SESSION['Installer']['Language'], 'DefaultDatabase' => $Database, 'SysAdminEmail' => $_SESSION['Installer']['AdminEmail']];

// The config files are in the main directory
$SampleConfigFile = $Path_To_Root . '/config.distrib.php';
$NewConfigFile = $Path_To_Root . '/config.php';

// Read the content of the sample config file
if (!file_exists($SampleConfigFile)) {
	echo '<div class="error">' . __('The sample configuration file does not exist.') . '</div>';
	return false;
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
				$Line = "date_default_timezone_set('" . $NewValue . "');\n";
			}
		}
		// Append the line to the new content
		$NewLines[] = $Line;
	}

	fclose($SampleHandle);
} else {
	echo '<div class="error">' . __('Unable to read the sample configuration file.') . '</div>';
	return false;
}

/// @todo allow more flexibility? Check if config.bak.php does not exist, rename the current config file and go on
if (file_exists($NewConfigFile)) {
	echo '<div class="error">' . __('The configuration file exists and has not been overwritten') . ' ' . $NewConfigFile . '</div>';
	flush();
	$Result = true;

} else {
	// Write the updated content to the new config file
	$NewConfigContent = implode($NewLines);
	$Result = file_put_contents($NewConfigFile, $NewConfigContent);

	if ($Result) {
		echo '<div class="success">' . __('The config.php file has been created based on your settings') . '</div>';
	}
	else {
		echo '<div class="error">' . __('Cannot write to the configuration file') . ' ' . $NewConfigFile . '</div>';
	}
	flush();
	$Result = true;
}

$DBErrors = 0;
foreach (glob($Path_To_Root . '/install/sql/tables/*.sql') as $FileName) {
	$SQLScriptFile = file_get_contents($FileName);

	if ($DBType == 'mariadb') {
		// mariadb 5.5 chokes on STORED
		$SQLScriptFile = preg_replace('/([) ])STORED([, \n])/', ' $1PERSISTENT$2', $SQLScriptFile);
	}

	if ($_SESSION['Installer']['DBCharset'] != 'utf8mb4') {
		$SQLScriptFile = preg_replace('/ CHARSET *= *utf8mb4/', ' CHARSET = utf8mb3', $SQLScriptFile);
	}

	// we disable FKs for each script, in case the previous script re-enabled them
	/// @todo do we need to disable FKs while creating tables and inserting no data?
	DB_IgnoreForeignKeys();
	// avoid the standard error-handling kicking in
	$Result = DB_query($SQLScriptFile, '', '', false, false);
	$DBErrors += DB_error_no($Result);
}
DB_ReinstateForeignKeys();
if ($DBErrors > 0) {
	echo '<div class="error">' . __('Database tables could not be created') . '</div>';
} else {
	echo '<div class="success">' . __('All database tables have been created') . '</div>';
}
flush();

/// @todo the trigger code will not work with postgres
/// @todo why not use PopulateSQLDataBySQLFile?
$DBErrors = 0;
foreach (glob($Path_To_Root . '/install/sql/triggers/*.sql') as $FileName) {
	$SQLScriptFile = file_get_contents($FileName);
	// we disable FKs for each script, in case the previous script re-enabled them
	/// @todo do we need to disable FKs while creating  triggers?
	DB_IgnoreForeignKeys();
	$Result = DB_query($SQLScriptFile, '', '', false, false);
	$DBErrors += DB_error_no();
}
DB_ReinstateForeignKeys();
if ($DBErrors > 0) {
	echo '<div class="error">' . __('Database triggers could not be created') . '</div>';
} else {
	echo '<div class="success">' . __('All database triggers have been created') . '</div>';
}
flush();

$CompanyDir = $Path_To_Root . '/companies/' . $Database;
$Errors = 0;
if ($_SESSION['Installer']['Demo'] != 'Yes') {
	/* Create the admin user */
	/// @todo is this needed for this insert?
	DB_IgnoreForeignKeys();
	$SQL = "INSERT INTO www_users  (userid,
										password,
										realname,
										customerid,
										supplierid,
										salesman,
										phone,
										email,
										defaultlocation,
										fullaccess,
										cancreatetender,
										lastvisitdate,
										branchcode,
										pagesize,
										timeout,
										modulesallowed,
										showdashboard,
										showpagehelp,
										showfieldhelp,
										blocked,
										displayrecordsmax,
										theme,
										language,
										pdflanguage,
										fontsize,
										department
									) VALUES (
										'" . $_SESSION['Installer']['AdminUser'] . "',
										'" . CryptPass($_SESSION['Installer']['AdminPassword']) . "',
										'" . __('Administrator') . "',
										'',
										'',
										'',
										'',
										'" . $_SESSION['Installer']['AdminEmail'] . "',
										'',
										8,
										1,
										'2024-10-24 18:38:24',
										'',
										'A4',
										10,
										'1,1,1,1,1,1,1,1,1,1,1,1,',
										0,
										1,
										1,
										0,
										50,
										'default',
										'" . $_SESSION['Installer']['Language'] . "',
										0,
										0,
										0
									)";
	$Result = DB_query($SQL, '', '', false, false);
	if (DB_error_no() == 0) {
		echo '<div class="success">' . __('The admin user has been inserted') . '</div>';
	} else {
		$Errors++;
		echo '<div class="error">' . __('There was an error inserting the admin user') . ' - ' . DB_error_msg() . '</div>';
	}
	DB_ReinstateForeignKeys();
	flush();

	/* insert COA data */
	/// @todo use function PopulateSQLDataBySQLFile and avoid code duplication!
	$COAScriptFile = file($_SESSION['Installer']['CoA']);
	$ScriptFileEntries = sizeof($COAScriptFile);
	$SQL = '';
	$InAFunction = false;
	DB_IgnoreForeignKeys();
	for ($i = 0;$i < $ScriptFileEntries;$i++) {

		$COAScriptFile[$i] = trim($COAScriptFile[$i]);
		// ignore lines that start with -- or USE or /*
		/// @todo use a regexp to account for initial spaces
		if (mb_substr($COAScriptFile[$i], 0, 2) != '--' and mb_strstr($COAScriptFile[$i], '/*') == false and mb_strlen($COAScriptFile[$i]) > 1) {

			$SQL .= ' ' . $COAScriptFile[$i];

			// check if this line kicks off a function definition - pg chokes otherwise
			/// @todo we can disable this filter if on mysql/mariadb
			/// @todo use a regexp
			if (mb_substr($COAScriptFile[$i], 0, 15) == 'CREATE FUNCTION') {
				$InAFunction = true;
			}
			// check if this line completes a function definition - pg chokes otherwise
			/// @todo use a regexp
			if (mb_substr($COAScriptFile[$i], 0, 8) == 'LANGUAGE') {
				$InAFunction = false;
			}
			if (mb_strpos($COAScriptFile[$i], ';') > 0 and !$InAFunction) {
				// Database created above with correct name.
				if (strncasecmp($SQL, ' CREATE DATABASE ', 17) and strncasecmp($SQL, ' USE ', 5)) {
					$SQL = mb_substr($SQL, 0, mb_strlen($SQL) - 1);
					// gg: no need to disable FKs for each statement - we did that just before starting the whole script
					//DB_IgnoreForeignKeys();
					$Result = DB_query($SQL, '', '', false, false);
					if (DB_error_no($Result) != 0) {
						$Errors++;
						echo '<div class="error">' . __('Your chosen chart of accounts could not be uploaded') . '</div>';
					}
				} else {
					/// @todo log a warning, so that developers can know that they should fix the sql...

				}
				$SQL = '';
			}

		} //end if its a valid sql line not a comment

	} //end of for loop around the lines of the sql script
	DB_ReinstateForeignKeys();
	echo '<div class="success">' . __('Your chosen chart of accounts has been uploaded') . '</div>';
	flush();

	$SQL = "INSERT INTO glaccountusers SELECT accountcode, 'admin', 1, 1 FROM chartmaster";
	$Result = DB_query($SQL, '', '', false, false);
	if (DB_error_no() == 0) {
		echo '<div class="success">' . __('The admin user has been given permissions on all GL accounts') . '</div>';
	} else {
		$Errors++;
		echo '<div class="error">' . __('There was an error with creating permission for the admin user') . ' - ' . DB_error_msg() . '</div>';
	}
	flush();

	$SQL = "INSERT INTO tags VALUES(0, 'None')";
	$Result = DB_query($SQL, '', '', false, false);
	if (DB_error_no() == 0) {
		echo '<div class="success">' . __('The default GL tag has been inserted') . '</div>';
	} else {
		$Errors++;
		echo '<div class="error">' . __('There was an error inserting the default GL tag') . ' - ' . DB_error_msg() . '</div>';
	}
	flush();

	$DBErrors = 0;
	foreach (glob($Path_To_Root . '/install/sql/data/*.sql') as $FileName) {
		$SQLScriptFile = file_get_contents($FileName);
		// we disable FKs for each script, in case the previous script re-enabled them
		DB_IgnoreForeignKeys();

		// Use mysqli_multi_query to execute multiple statements
		global $db;
		if (mysqli_multi_query($db, $SQLScriptFile)) {
			do {
				// Store first result set
				if ($Result = mysqli_store_result($db)) {
					mysqli_free_result($Result);
				}
				// Check for errors
				if (mysqli_errno($db)) {
					$DBErrors++;
				}
			} while (mysqli_more_results($db) && mysqli_next_result($db));
		} else {
			$DBErrors++;
		}
	} DB_ReinstateForeignKeys();
	if ($DBErrors > 0) {
		$Errors++;
		echo '<div class="error">' . __('Database tables could not be populated') . '</div>';
	} else {
		echo '<div class="success">' . __('All database tables have been populated') . '</div>';
	}
	flush();

	/// @todo there is no guarantee that all the db updates have been applied to the single SQL files making up
	///       the installer - that is left to the person preparing the release to verify...
	$SQL = "INSERT INTO config (confname, confvalue) VALUES('DBUpdateNumber', " . HighestFileName($Path_To_Root) . ")";
	$Result = DB_query($SQL, '', '', false, false);
	if (DB_error_no() == 0) {
		echo '<div class="success">' . __('The database update revision has been inserted') . '</div>';
	} else {
		$Errors++;
		echo '<div class="error">' . __('There was an error inserting the DB revision number') . ' - ' . DB_error_msg() . '</div>';
	}
	flush();

	$SQL = "INSERT INTO `companies` VALUES (1,
											'" . $_SESSION['CompanyRecord']['coyname'] . "',
											'not entered yet',
											'',
											'',
											'',
											'',
											'',
											'',
											'',
											'',
											'',
											'info@weberp.com',
											'GBP',
											'1100',
											'4900',
											'2100',
											'2400',
											'2150',
											'2150',
											'4200',
											'5200',
											'3500',
											'3500',
											'90000',
											1,
											1,
											1,
											'5600'
										)";
	$Result = DB_query($SQL, '', '', false, false);
	if (DB_error_no() == 0) {
		echo '<div class="success">' . __('The company record has been inserted') . '</div>';
	} else {
		$Errors++;
		echo '<div class="error">' . __('There was an error inserting the company record') . ' - ' . DB_error_msg() . '</div>';
	}
	flush();

	DB_ReinstateForeignKeys();

} else {
	echo '<div class="info">' . __('Populating the database with demo data.') . '</div>';
	flush();

	DB_IgnoreForeignKeys();

	/// @todo is there need to disable foreign keys for this insert?
	DB_IgnoreForeignKeys();
	$SQL = "INSERT INTO www_users  (userid,
										password,
										realname,
										customerid,
										supplierid,
										salesman,
										phone,
										email,
										defaultlocation,
										fullaccess,
										cancreatetender,
										lastvisitdate,
										branchcode,
										pagesize,
										timeout,
										modulesallowed,
										showdashboard,
										showpagehelp,
										showfieldhelp,
										blocked,
										displayrecordsmax,
										theme,
										language,
										pdflanguage,
										fontsize,
										department
									) VALUES (
										'" . $_SESSION['Installer']['AdminUser'] . "',
										'" . CryptPass($_SESSION['Installer']['AdminPassword']) . "',
										'" . __('Administrator') . "',
										'',
										'',
										'',
										'',
										'" . $_SESSION['Installer']['AdminEmail'] . "',
										'',
										8,
										1,
										'2024-10-24 18:38:24',
										'',
										'A4',
										10,
										'1,1,1,1,1,1,1,1,1,1,1,1,',
										0,
										1,
										1,
										0,
										50,
										'default',
										'" . $_SESSION['Installer']['Language'] . "',
										0,
										0,
										0
									)";
	$Result = DB_query($SQL, '', '', false, false);
	if (DB_error_no() == 0) {
		echo '<div class="success">' . __('The admin user has been inserted.') . '</div>';
	} else {
		$Errors++;
		echo '<div class="error">' . __('There was an error inserting the admin user') . ' - ' . DB_error_msg() . '</div>';
	}
	DB_ReinstateForeignKeys();

	$Errors = 0;
	$SQLScriptFile = file($Path_To_Root . '/install/sql/demo.sql');
	$ScriptFileEntries = sizeof($SQLScriptFile);

	$Errors = 0;
	$SQL = '';
	$InAFunction = false;
	for ($i = 1; $i <= $ScriptFileEntries; $i++) {

		$SQLScriptFile[$i - 1] = trim($SQLScriptFile[$i - 1]);

//		if (mb_substr($SQLScriptFile[$i - 1], 0 ,2) != '--') {

		/// @todo ignore lines that start with `--` or USE or /* or CREATE DATABASE

		$SQL.= ' ' . $SQLScriptFile[$i - 1];
		// check if this line kicks off a function definition - pg chokes otherwise
		/// @todo we can disable this filter if on mysql/mariadb
		/// @todo use a regexp
		if (!$InAFunction && mb_substr($SQLScriptFile[$i - 1], 0, 15) == 'CREATE FUNCTION') {
			$InAFunction = true;
		}
		// check if this line completes a function definition - pg chokes otherwise
		/// @todo use a regexp
		if ($InAFunction && mb_substr($SQLScriptFile[$i - 1], 0, 8) == 'LANGUAGE') {
			$InAFunction = false;
		}
		/// @todo run last statement found even if not not comma terminated
		if (mb_strpos($SQLScriptFile[$i - 1], ';') > 0 and !$InAFunction) {
			// Database created above with correct name.
			$Result = DB_query($SQL, '', '', false, false);
			if (DB_error_no() != 0) {
				$Errors++;
			}
			$SQL = '';
		}
		flush();
//		}

	} //end of for loop around the lines of the sql script
//	if (!PopulateSQLDataBySQLFile($Path_To_Root . '/install/sql/demo.sql', $DBType)) {
	if ($Errors != 0) {
		echo '<div class="error">' . __('There was an error populating the database with demo data') . '</div>';
	}

	/// @todo this could just be pushed into demo.sql - and checked for presence by the scripts in /build
	/// @todo also, it should be an UPSERT
	$SQL = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('FirstLogIn','0')";
	$Result = DB_query($SQL, '', '', false, false);
	/// @todo echo error (warning?) if failure
	if (DB_error_no() == 0) {
		//echo '<div class="success">' . __('...') . '</div>';

	} else {
		$Errors++;
		echo '<div class="error">' . __('There was an error updating the FirstLogIn setting') . ' - ' . DB_error_msg() . '</div>';
	}
	DB_ReinstateForeignKeys();

	/// @todo there is no guarantee that all the db updates have been applied to the single SQL files making up
	///       the installer - that is left to the person preparing the release to verify...
	$SQL = "INSERT INTO config (confname, confvalue) VALUES('DBUpdateNumber', " . HighestFileName($Path_To_Root) . ")";
	$Result = DB_query($SQL, '', '', false, false);
	if (DB_error_no() == 0) {
		echo '<div class="success">' . __('The database update revision has been inserted') . '</div>';
	} else {
		$Errors++;
		echo '<div class="error">' . __('There was an error inserting the DB revision number') . ' - ' . DB_error_msg() . '</div>';
	}

	foreach (glob($Path_To_Root . '/companies/weberpdemo/part_pics/*.jp*') as $JpegFile) {
		copy($Path_To_Root . "/companies/weberpdemo/part_pics/" . basename($JpegFile), $CompanyDir . '/part_pics/' . basename($JpegFile));
	}
	flush();

	/// @todo display a warning message if there was any query failure
	echo '<div class="success">' . __('Database now contains the demo data.') . '</div>';
}

/// @todo check for errors
$SQL = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('part_pics_dir', 'companies/" . $Database . "/part_pics')";
$Result = DB_query($SQL);

/// @todo check for errors
$SQL = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('LogPath', 'companies/" . $Database . "/logs')";
$Result = DB_query($SQL);

copy($Path_To_Root . '/companies/weberpdemo/part_pics/webERPsmall.png', $CompanyDir . '/part_pics/webERPsmall.png');

$Contents = "<?php\n\n";
$Contents .= "\$CompanyName['" . $Database . "'] = '" . $_SESSION['CompanyRecord']['coyname'] . "';\n";

$Result = false;
$CompaniesFile = $Path_To_Root . '/companies/' . $Database . '/Companies.php';

// give at least a warning if the files exists already
$FileExists = file_exists($CompaniesFile);

$CompanyFileHandle = fopen($CompaniesFile, 'w');
if ($CompanyFileHandle) {
	$Result = @fwrite($CompanyFileHandle, $Contents);
	@fclose($CompanyFileHandle);
}

if ($FileExists) {
	echo '<div class="warning">' . __('The Companies.php file already exists') . '</div>';
}

if ($Result) {
	echo '<div class="success">' . __('The Companies.php file has been created') . '</div>';
} else {
	echo '<div class="error">' . __('Could not write the Companies.php file') . '</div>';
}
flush();

$Installed = true;
