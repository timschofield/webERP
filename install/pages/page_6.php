<?php
$Host = $_SESSION['Installer']['HostName'];
$DBUser = $_SESSION['Installer']['UserName'];
$DBPassword = $_SESSION['Installer']['Password'];
$DBType = $_SESSION['Installer']['DBMS'];
$_SESSION['DatabaseName'] = $_SESSION['Installer']['Database'];
$DefaultDatabase = 'default';

ob_start();

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

$DB = @mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password']);

if (!$DB) {
	$Errors[] = _('Failed to connect the database management system');
} else {
	$Result = @mysqli_query($DB, 'SET SQL_MODE=""');
	$Result = @mysqli_query($DB, 'SET SESSION SQL_MODE=""');
}

$DBExistsSql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $_SESSION['Installer']['Database'] . "'";
$PrivilegesSql = "SELECT * FROM INFORMATION_SCHEMA.USER_PRIVILEGES WHERE GRANTEE=" . '"' . "'" . $_SESSION['Installer']['UserName'] . "'@'" . $_SESSION['Installer']['HostName'] . "'" . '"' . " AND PRIVILEGE_TYPE='CREATE'";

$DBExistsResult = @mysqli_query($DB, $DBExistsSql);
$PrivilegesResult = @mysqli_query($DB, $PrivilegesSql);
$rows = @mysqli_num_rows($DBExistsResult);
$Privileges = @mysqli_num_rows($PrivilegesResult);

if ($rows == 0) { /* Then the database does not exist */
	if ($Privileges == 0) {
		$Errors[] = _('The database does not exist, and this database user does not have privileges to create it');
	} else { /* Then we can create the database */
		$SQL = "CREATE DATABASE " . $_SESSION['Installer']['Database'];
		$Result = @mysqli_query($DB, $SQL);
	}
} else { /* Need to make sure any data is removed from existing DB */
	$SQL = "SELECT 'TRUNCATE TABLE ' + table_name + ';' FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $_SESSION['Installer']['Database'] . "'";
	$Result = @mysqli_query($DB, $SQL);
}

include ('../includes/ConnectDB_' . $_SESSION['Installer']['DBMS'] . '.inc');
include ('../includes/UpgradeDB_' . $_SESSION['Installer']['DBMS'] . '.php');

$DB = @mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password'], $_SESSION['DatabaseName']);

include ('../includes/DateFunctions.inc');
date_default_timezone_set($_SESSION['Installer']['TimeZone']);
$Path_To_Root = '..';
$Config_File = $Path_To_Root . '/config.php';
if (!file_exists($Path_To_Root . '/companies/' . $_SESSION['Installer']['Database'])) {
	$CompanyDir = $Path_To_Root . '/companies/' . $_SESSION['Installer']['Database'];
	$Result = mkdir($CompanyDir);
	$Result = mkdir($CompanyDir . '/part_pics');
	$Result = mkdir($CompanyDir . '/EDI_Incoming_Orders');
	$Result = mkdir($CompanyDir . '/reports');
	$Result = mkdir($CompanyDir . '/EDI_Sent');
	$Result = mkdir($CompanyDir . '/EDI_Pending');
	$Result = mkdir($CompanyDir . '/reportwriter');
	$Result = mkdir($CompanyDir . '/pdf_append');
	$Result = mkdir($CompanyDir . '/FormDesigns');
	copy($Path_To_Root . '/companies/weberpdemo/FormDesigns/GoodsReceived.xml', $CompanyDir . '/FormDesigns/GoodsReceived.xml');
	copy($Path_To_Root . '/companies/weberpdemo/FormDesigns/PickingList.xml', $CompanyDir . '/FormDesigns/PickingList.xml');
	copy($Path_To_Root . '/companies/weberpdemo/FormDesigns/PurchaseOrder.xml', $CompanyDir . '/FormDesigns/PurchaseOrder.xml');
	copy($Path_To_Root . '/companies/weberpdemo/FormDesigns/Journal.xml', $CompanyDir . '/FormDesigns/Journal.xml');
	echo '<div class="success">' . _('The companies directory has been successfully created') . '</div>';
	ob_flush();

	/* Upload logo file */
	$TargetDir = $Path_To_Root . '/companies/' . $_SESSION['Installer']['Database'] . '/';
	$TargetFile = $TargetDir . basename($_FILES["LogoFile"]["name"]);
	$UploadOK = 1;
	$ImageFileType = strtolower(pathinfo($TargetFile,PATHINFO_EXTENSION));

	if ($_FILES["LogoFile"]["tmp_name"] != '') {
		// Check if image file is a actual image or fake image
		if(isset($_POST["install"])) {
			$check = getimagesize($_FILES["LogoFile"]["tmp_name"]);
			if($check !== false) {
				$UploadOK = 1;
			} else {
				echo '<div class="error">' . _('Logo file is not an image.') . '</div>';
				$UploadOK = 0;
			}
		}

		// Check if file already exists
		if (file_exists($TargetFile)) {
			echo '<div class="error">' . _('Sorry, logo file already exists.') . '</div>';
			$UploadOK = 0;
		}

		// Check file size
		if ($_FILES["LogoFile"]["size"] > 500000) {
			echo '<div class="error">' . _('Sorry, your logo file is too large.') . '</div>';
			$UploadOK = 0;
		}

		// Allow certain file formats
		if($ImageFileType != "jpg" && $ImageFileType != "png" && $ImageFileType != "jpeg" && $ImageFileType != "gif" ) {
			echo '<div class="error">' . _('Sorry, only JPG, JPEG, PNG & GIF logo files are allowed.') . '</div>';
			$UploadOK = 0;
		}

		// Check if $UploadOK is set to 0 by an error
		if ($UploadOK == 0) {
			echo '<div class="error">' . _('Sorry, your logo file was not uploaded.') . '</div>';
			// if everything is ok, try to upload file
		} else {
			if (move_uploaded_file($_FILES["LogoFile"]["tmp_name"], $TargetFile)) {
				echo '<div class="success">' . _('Your logo has been successfully uploaded') . '</div>';
			} else {
				echo '<div class="warn">' . _('Your logo could not be uploaded. You must copy this to your companies directory later.') . '</div>';
			}
		}
		ob_flush();
	} else {
		$Result = copy($Path_To_Root . '/logo_server.jpg', $CompanyDir . '/logo.jpg');
	}
}

//$Msg holds the text of the new config.php file
$Msg = "<?php\n\n";
$Msg.= "// User configurable variables\n";
$Msg.= "//---------------------------------------------------\n\n";
$Msg.= "//DefaultLanguage to use for the login screen and the setup of new users.\n";
$Msg.= "\$DefaultLanguage = '" . $_SESSION['Installer']['Language'] . "';\n\n";
$Msg.= "// Whether to display the demo login and password or not on the login screen\n";
$Msg.= "\$AllowDemoMode = FALSE;\n\n";
$Msg.= "// Connection information for the database\n";
$Msg.= "// \$Host is the computer ip address or name where the database is located\n";
$Msg.= "// assuming that the webserver is also the sql server\n";
$Msg.= "\$Host = '" . $_SESSION['Installer']['HostName'] . "';\n\n";
$Msg.= "// assuming that the web server is also the sql server\n";
$Msg.= "\$DBType = '" . $_SESSION['Installer']['DBMS'] . "';\n";
$Msg.= "//assuming that the web server is also the sql server\n";
$Msg.= "\$DBUser = '" . $_SESSION['Installer']['UserName'] . "';\n";
$Msg.= "\$DBPassword = '" . $_SESSION['Installer']['Password'] . "';\n";
$Msg.= "// The timezone of the business - this allows the possibility of having;\n";
$Msg.= "define('TIMEZONE', '" . $_SESSION['Installer']['TimeZone'] . "');\n";
$Msg.= "date_default_timezone_set(TIMEZONE);\n";
$Msg.= "\$AllowCompanySelectionBox = 'ShowSelectionBox';\n";
$Msg.= "//The system administrator name use the user input mail;\n";
if (strtolower($_SESSION['Installer']['Email']) != 'admin@kwamoja.com') {
	$Msg.= "\$SysAdminEmail = '" . $_SESSION['Installer']['Email'] . "';\n";
}
if (isset($NewCompany)) {
	$Msg.= "\$DefaultCompany = '" . $_SESSION['Installer']['Database'] . "';\n";
} else {
	$Msg.= "\$DefaultCompany = '" . $_SESSION['Installer']['Database'] . "';\n";
}
$Msg.= "\$SessionLifeTime = 3600;\n";
$Msg.= "\$MaximumExecutionTime = 120;\n";
$Msg.= "\$DefaultClock = 12;\n";
$Msg.= "\$RootPath = dirname(htmlspecialchars(\basename(__FILE__),ENT_QUOTES,'UTF-8'));\n";
$Msg.= "if (isset(\$DirectoryLevelsDeep)){\n";
$Msg.= "   for (\$i=0;\$i<\$DirectoryLevelsDeep;\$i++){\n";
$Msg.= "		\$RootPath = mb_substr(\$RootPath,0, strrpos(\$RootPath,'/'));\n";
$Msg.= "	}\n";
$Msg.= "}\n";

$Msg.= "if (\$RootPath == '/' OR \$RootPath == '\\\') {\n";
$Msg.= "	\$RootPath = '';\n";
$Msg.= "}\n";
$Msg.= "error_reporting(E_ALL && ~E_NOTICE);\n";
$Msg.= "\$Debug = 0;\n";
$Msg.= "/* Make sure there is nothing - not even spaces after this last ?> */\n";
$Msg.= "?>";

//write the config.php file since we have test the writability of the root path and companies,
//there is little possibility that it will fail here. So just an warn if it is failed.
if (!$zp = fopen($Path_To_Root . '/config.php', 'w')) {
	echo '<div class="error">' . _("Cannot open the configuration file") . $Config_File . '</div>';
} else {
	if (!fwrite($zp, $Msg)) {
		fclose($zp);
		echo '<div class="error">' . _("Cannot write to the configuration file") . $Config_File . '</div>';
	}
	//close file
	fclose($zp);
}
echo '<div class="success">' . _('The config.php file has been created based on your settings.') . '</div>';
ob_flush();

$DBErrors = 0;
foreach (glob($Path_To_Root . "/install/tables/*.sql") as $filename) {
	$SQLScriptFile = file_get_contents($filename);
	DB_IgnoreForeignKeys();
	$Result = DB_query($SQLScriptFile);
	$DBErrors += DB_error_no($Result);
}
if ($DBErrors > 0) {
	echo '<div class="error">' . _("Database tables could not be created") . '</div>';
} else {
	echo '<div class="success">' . _("All database tables have been created") . '</div>';
}
ob_flush();

/* Now we uploade the chosen chart of accounts */
if (isset($_SESSION['Installer']['Demo']) and $_SESSION['Installer']['Demo'] != 'Yes') {
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
									'" . $_SESSION['Installer']['AdminAccount'] . "',
									'" . CryptPass($_SESSION['Installer']['KwaMojaPassword']) . "',
									'" . _('Administrator') . "',
									'',
									'',
									'',
									'',
									'" . $_SESSION['Installer']['Email'] . "',
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
	$Result = DB_query($SQL);

	if (DB_error_no() == 0) {
		echo '<div class="success">' . _('The admin user has been inserted.') . '</div>';
	} else {
		echo '<div class="error">' . _('There was an error inserting the admin user') . ' - ' . DB_error_msg() . '</div>';
	}
	ob_flush();

	$COAScriptFile = file($_SESSION['Installer']['CoA']);
	$ScriptFileEntries = sizeof($COAScriptFile);
	$SQL = '';
	$InAFunction = false;
	DB_IgnoreForeignKeys();
	for ($i = 0;$i < $ScriptFileEntries;$i++) {

		$COAScriptFile[$i] = trim($COAScriptFile[$i]);
		//ignore lines that start with -- or USE or /*
		if (mb_substr($COAScriptFile[$i], 0, 2) != '--' and mb_strstr($COAScriptFile[$i], '/*') == false and mb_strlen($COAScriptFile[$i]) > 1) {

			$SQL.= ' ' . $COAScriptFile[$i];

			//check if this line kicks off a function definition - pg chokes otherwise
			if (mb_substr($COAScriptFile[$i], 0, 15) == 'CREATE FUNCTION') {
				$InAFunction = true;
			}
			//check if this line completes a function definition - pg chokes otherwise
			if (mb_substr($COAScriptFile[$i], 0, 8) == 'LANGUAGE') {
				$InAFunction = false;
			}
			if (mb_strpos($COAScriptFile[$i], ';') > 0 and !$InAFunction) {
				// Database created above with correct name.
				if (strncasecmp($SQL, ' CREATE DATABASE ', 17) and strncasecmp($SQL, ' USE ', 5)) {
					$SQL = mb_substr($SQL, 0, mb_strlen($SQL) - 1);
					DB_IgnoreForeignKeys();
					$Result = DB_query($SQL);
					if (DB_error_no($Result) != 0) {
						echo '<div class="error">' . _('Your chosen chart of accounts could not be uploaded') . '</div>';
					}
				}
				$SQL = '';
			}

		} //end if its a valid sql line not a comment

	} //end of for loop around the lines of the sql script
	echo '<div class="success">' . _('Your chosen chart of accounts has been uploaded') . '</div>';
	ob_flush();

	$SQL = "INSERT INTO glaccountusers SELECT accountcode, 'admin', 1, 1 FROM chartmaster";
	$Result = DB_query($SQL);
	if (DB_error_no() == 0) {
		echo '<div class="success">' . _('The admin user has been given permissions on all GL accounts.') . '</div>';
	} else {
		echo '<div class="error">' . _('There was an error with creating permission for the admin user') . ' - ' . DB_error_msg() . '</div>';
	}

	$DBErrors = 0;
	foreach (glob($Path_To_Root . "/install/sql/*.sql") as $filename) {
		$SQLScriptFile = file_get_contents($filename);
		DB_IgnoreForeignKeys();
		$Result = DB_query($SQLScriptFile);
		$DBErrors += DB_error_no($Result);
	}
	if ($DBErrors > 0) {
		echo '<div class="error">' . _("Database tables could not be created") . '</div>';
	} else {
		echo '<div class="success">' . _("All database tables have been created") . '</div>';
	}
	ob_flush();

	$SQL = "INSERT INTO config VALUES('DBUpdateNumber', " . HighestFileName('../') . ")";
	$Result = DB_query($SQL);

	if (DB_error_no() == 0) {
		echo '<div class="success">' . _('The database update revision has been inserted.') . '</div>';
	} else {
		echo '<div class="error">' . _('There was an error inserting the DB revision number') . ' - ' . DB_error_msg() . '</div>';
	}
	ob_flush();

	$SQL ="INSERT INTO `companies` VALUES (1,
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
										1,
										1,
										1,
										'5600'
									)";
	$Result = DB_query($SQL);

	if (DB_error_no() == 0) {
		echo '<div class="success">' . _('The company record has been inserted.') . '</div>';
	} else {
		echo '<div class="error">' . _('There was an error inserting the DB revision number') . ' - ' . DB_error_msg() . '</div>';
	}
	ob_flush();

	/* Create the admin user */
} else {
	echo '<div class="success">' . _('Populating the database with demo data.') . '</div>';
	PopulateSQLDataBySQL('demo.sql', $DB, $DBType, false, $_SESSION['Installer']['Database']);
	$SQL = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('FirstLogIn','0')";
	$Result = DB_query($SQL);
	$CompanyDir = $PathPrefix . 'companies/' . $_SESSION['Installer']['Database'];
	foreach (glob($PathPrefix . "companies/default/part_pics/*.jp*") as $JpegFile) {
		copy("../companies/default/part_pics/" . basename($JpegFile), $CompanyDir . '/part_pics/' . basename($JpegFile));
	}
	copy("../companies/weberpdemo/logo.png", $CompanyDir . '/logo.png');
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
									'" . $_SESSION['Installer']['AdminAccount'] . "',
									'" . CryptPass($_SESSION['Installer']['KwaMojaPassword']) . "',
									'" . _('Administrator') . "',
									'',
									'',
									'',
									'',
									'" . $_SESSION['Installer']['Email'] . "',
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
	$Result = DB_query($SQL);

	if (DB_error_no() == 0) {
		echo '<div class="success">' . _('The admin user has been inserted.') . '</div>';
	} else {
		echo '<div class="error">' . _('There was an error inserting the admin user') . ' - ' . DB_error_msg() . '</div>';
	}
	ob_flush();
	echo '<div class="success">' . _('Database now contains the demo data.') . '</div>';
}

$SQL = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('part_pics_dir','companies/" . $_SESSION['DatabaseName'] . "/part_pics')";
$Result = DB_query($SQL);

$CompanyFileHandler = fopen($Path_To_Root . '/companies/' . $_SESSION['DatabaseName'] . '/Companies.php', 'w');
$Contents = "<?php\n\n";
$Contents.= "\$CompanyName['" . $_SESSION['DatabaseName'] . "'] = '" . $_SESSION['CompanyRecord']['coyname'] . "';\n";
$Contents.= "?>";

if (!fwrite($CompanyFileHandler, $Contents)) {
	fclose($CompanyFileHandler);
	echo '<div class="error">' . _("Cannot write to the Companies.php file") . '</div>';
}
//close file
fclose($CompanyFileHandler);

function HighestFileName($PathPrefix) {
	$files = glob($PathPrefix . 'sql/updates/*.php');
	natsort($files);
	return basename(array_pop($files), ".php");
}

function CryptPass($Password) {
	if (PHP_VERSION_ID < 50500) {
		$Salt = base64_encode(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));
		$Salt = str_replace('+', '.', $Salt);
		$Hash = crypt($Password, '$2y$10$' . $Salt . '$');
	} else {
		$Hash = password_hash($Password, PASSWORD_DEFAULT);
	}
	return $Hash;
}
function PopulateSQLDataBySQL($File, $DB, $DBType, $NewDB = false, $DemoDB = 'kwamojademo') {
	$DBName = ($NewDB) ? $NewDB : $DemoDB;
	($DBType == 'mysql') ? mysql_select_db($DBName, $DB) : mysqli_select_db($DB, $DBName);
	$SQLScriptFile = file($File);
	$ScriptFileEntries = sizeof($SQLScriptFile);
	$SQL = '';
	$InAFunction = false;
	for ($i = 1;$i <= $ScriptFileEntries;$i++) {

		$SQLScriptFile[$i - 1] = trim($SQLScriptFile[$i - 1]);
		//ignore lines that start with -- or USE or /*
		$SQL.= ' ' . $SQLScriptFile[$i - 1];

		//check if this line kicks off a function definition - pg chokes otherwise
		if (mb_substr($SQLScriptFile[$i - 1], 0, 15) == 'CREATE FUNCTION') {
			$InAFunction = true;
		}
		//check if this line completes a function definition - pg chokes otherwise
		if (mb_substr($SQLScriptFile[$i - 1], 0, 8) == 'LANGUAGE') {
			$InAFunction = false;
		}
		if (mb_strpos($SQLScriptFile[$i - 1], ';') > 0 and !$InAFunction) {
			// Database created above with correct name.
			$Result = ($DBType == 'mysql') ? mysql_query($SQL, $DB) : mysqli_query($DB, $SQL);
			$SQL = '';
		}
		flush();

	} //end of for loop around the lines of the sql script

}

?>
