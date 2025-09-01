<?php

/**
 * @param $CompanyName
 * @param $Path_To_Root
 * @param $CompanyDir
 * @return bool true on success
 */
function CreateCompanyLogo($CompanyName, $Path_To_Root, $CompanyDir) {
	if (extension_loaded('gd')) {
		// generate an image, based on company name

		$Font = 3;

		$im = imagecreatefrompng($Path_To_Root . '/images/logo_background.png');
		imagealphablending($im, false);

		//$BackgroundColour = imagecolorallocate($im, 119, 119, 119); // #777777, same as default color theme
		$TextColour = imagecolorallocate($im, 255, 255, 255);

		$fw = imagefontwidth($Font);
		$fh = imagefontheight($Font);
		$TextWidth = $fw * mb_strlen($CompanyName);
		$px = (imagesx($im) - $TextWidth) / 2;
		$py = (imagesy($im) - ($fh)) / 2;
		//imagefill($im, 0, 0, $BackgroundColour);
		imagestring($im, $Font, $px, $py, $CompanyName, $TextColour);

		imagesavealpha($im, true);

		$Result = true;
		if (!imagepng($im, $CompanyDir . '/logo.png')) {
			$Result = copy($Path_To_Root . '/images/default_logo.jpg', $CompanyDir . '/logo.jpg');
		}
		imagedestroy($im);

	} else {
		$Result = copy($Path_To_Root . '/images/default_logo.jpg', $CompanyDir . '/logo.jpg');
	}

	if ($Result) {
		echo '<div class="success">' . __('A default company logo has been generated') . '</div>';
	} else {
		echo '<div class="warning">' . __('Failed generating default company logo.') . '</div>';
	}
	flush();

	return $Result;
}

/**
 * @param $DatabaseName
 * @param $Path_To_Root
 * @return bool true on success
 */
function SaveUploadedCompanyLogo($DatabaseName, $Path_To_Root)
{
	/* Upload logo file */
	$UploadOK = 1;

	$TargetDir = $Path_To_Root . '/companies/' . $DatabaseName . '/';
	$TargetFile = $TargetDir . basename($_FILES["LogoFile"]["name"]);
	$ImageFileType = strtolower(pathinfo($TargetFile, PATHINFO_EXTENSION));

	// Check if image file is an actual image or fake image
	if(isset($_POST["install"])) {
		$check = getimagesize($_FILES["LogoFile"]["tmp_name"]);
		if($check !== false) {
			$UploadOK = 1;
		} else {
			echo '<div class="warning">' . __('Logo file is not an image.') . '</div>';
			$UploadOK = 0;
		}
	}

	// Check if file already exists
	if (file_exists($TargetFile)) {
		echo '<div class="warning">' . __('Sorry, logo file already exists.') . '</div>';
		$UploadOK = 0;
	}

	// Check file size
	if ($_FILES["LogoFile"]["size"] > 500000) {
		echo '<div class="warning">' . __('Sorry, your logo file is too large.') . '</div>';
		$UploadOK = 0;
	}

	// Allow certain file formats
	if ($ImageFileType != "jpg" && $ImageFileType != "png" && $ImageFileType != "jpeg" && $ImageFileType != "gif" ) {
		echo '<div class="warning">' . __('Sorry, only JPG, JPEG, PNG & GIF logo files are allowed.') . '</div>';
		$UploadOK = 0;
	}

	// Check if $UploadOK is set to 0 by an error
	if ($UploadOK == 0) {
		echo '<div class="warning">' . __('Sorry, your logo file was not uploaded.') . '</div>';
	} else {
		// if everything is ok, try to upload file
		if (move_uploaded_file($_FILES["LogoFile"]["tmp_name"], $TargetFile)) {
			echo '<div class="success">' . __('Your logo has been successfully uploaded') . '</div>';
		} else {
			echo '<div class="warning">' . __('Your logo could not be uploaded. You must copy this to your companies directory later.') . '</div>';
		}
	}
	flush();

	return (bool)$UploadOK;
}

/**
 * @return bool false when a fatal error happened
 */
function CreateDataBase($HostName, $UserName, $Password, $DataBaseName, $DBPort, $Path_To_Root) {

	// avoid exceptions being thrown on query errors
	mysqli_report(MYSQLI_REPORT_ERROR);

	$DB = @mysqli_connect($HostName, $UserName, $Password, null, $DBPort);
	if (!$DB) {
		echo '<div class="error">' . __('Unable to connect to the database to create the schema.') . '</div>';
		flush();
		return false;
	}

	$Errors = [];

	mysqli_set_charset($DB, 'utf8');

	// gg: we only use this db connection for creating the database, as we use separate one for creating tables etc.
	//     So this seems useless/overkill
	//$Result = @mysqli_query($DB, 'SET SQL_MODE=""');
	//$Result = @mysqli_query($DB, 'SET SESSION SQL_MODE=""');

	/// @todo move checking for permissions and for existing tables into UpgradeDB_$dbtype.php, to give it a chance
	///       at being db-agnostic

	$DBExistsSql = "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '" . mysqli_real_escape_string($DB, $DataBaseName) . "'";
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
			/// @todo add utf8-mb4 as default charset (check 1st that the db supports it. If not, utf8mb3)
			$SQL = "CREATE DATABASE " . $DataBaseName;
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
			foreach($Rows as $i => $Row) {
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

	return true;
}

function CreateCompanyFolder($DatabaseName, $Path_To_Root) {
	$CompanyDir = $Path_To_Root . '/companies/' . $DatabaseName;

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
			SaveUploadedCompanyLogo($DatabaseName, $Path_To_Root);
		} else {
			CreateCompanyLogo($DatabaseName, $Path_To_Root, $CompanyDir);
		}
	}

	return $Result;
}

/**
 * @param $Path_To_Root
 * @return bool
 */
function CreateTables($Path_To_Root) {
	$DBErrors = 0;
	foreach (glob($Path_To_Root . '/install/sql/tables/*.sql') as $FileName) {
		$SQLScriptFile = file_get_contents($FileName);
		DB_IgnoreForeignKeys();
		// avoid the standard error-handling kicking in
		$Result = DB_query($SQLScriptFile, '', '', false, false);
		$DBErrors += DB_error_no($Result);
	}
	if ($DBErrors > 0) {
		echo '<div class="error">' . __('Database tables could not be created') . '</div>';
	} else {
		echo '<div class="success">' . __('All database tables have been created') . '</div>';
	}
	flush();

	return ($DBErrors == 0);
}

function UploadData($Demo, $AdminPassword, $AdminUser, $Email, $Language, $CoA, $CompanyName, $Path_To_Root, $DataBaseName) {
	if ($Demo != 'Yes') {
		DB_IgnoreForeignKeys();
		/* Create the admin user */
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
										'" . $AdminUser . "',
										'" . CryptPass($AdminPassword) . "',
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
										'" . $Language . "',
										0,
										0,
										0
									)";
		$Result = DB_query($SQL);
		if (DB_error_no() == 0) {
			echo '<div class="success">' . __('The admin user has been inserted') . '</div>';
		} else {
			echo '<div class="error">' . __('There was an error inserting the admin user') . ' - ' . DB_error_msg() . '</div>';
		}
		flush();

		$COAScriptFile = file($CoA);
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
							echo '<div class="error">' . __('Your chosen chart of accounts could not be uploaded') . '</div>';
						}
					}
					$SQL = '';
				}

			} //end if its a valid sql line not a comment

		} //end of for loop around the lines of the sql script
		echo '<div class="success">' . __('Your chosen chart of accounts has been uploaded') . '</div>';
		flush();

		$SQL = "INSERT INTO glaccountusers SELECT accountcode, 'admin', 1, 1 FROM chartmaster";
		$Result = DB_query($SQL);
		if (DB_error_no() == 0) {
			echo '<div class="success">' . __('The admin user has been given permissions on all GL accounts') . '</div>';
		} else {
			echo '<div class="error">' . __('There was an error with creating permission for the admin user') . ' - ' . DB_error_msg() . '</div>';
		}
		flush();

		$SQL = "INSERT INTO tags VALUES(0, 'None')";
		$Result = DB_query($SQL);
		if (DB_error_no() == 0) {
			echo '<div class="success">' . __('The default GL tag has been inserted') . '</div>';
		} else {
			echo '<div class="error">' . __('There was an error inserting the default GL tag') . ' - ' . DB_error_msg() . '</div>';
		}
		flush();

		$DBErrors = 0;
		foreach (glob($Path_To_Root . '/install/sql/data/*.sql') as $FileName) {
			$SQLScriptFile = file_get_contents($FileName);
			DB_IgnoreForeignKeys();
			$Result = DB_query($SQLScriptFile);
			$DBErrors += DB_error_no($Result);
		}
		if ($DBErrors > 0) {
			echo '<div class="error">' . __('Database tables could not be populated') . '</div>';
		} else {
			echo '<div class="success">' . __('All database tables have been populated') . '</div>';
		}
		flush();

		/// @todo there is no guarantee that all the db updates have been applied to the single SQL files making up
		///       the installer - that is left to the person preparing the release to verify...
		$SQL = "INSERT INTO config VALUES('DBUpdateNumber', " . HighestFileName($Path_To_Root) . ")";
		$Result = DB_query($SQL);
		if (DB_error_no() == 0) {
			echo '<div class="success">' . __('The database update revision has been inserted') . '</div>';
		} else {
			echo '<div class="error">' . __('There was an error inserting the DB revision number') . ' - ' . DB_error_msg() . '</div>';
		}
		flush();

		$SQL ="INSERT INTO `companies` VALUES (1,
											'" . $CompanyName . "',
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
			echo '<div class="success">' . __('The company record has been inserted') . '</div>';
		} else {
			echo '<div class="error">' . __('There was an error inserting the DB revision number') . ' - ' . DB_error_msg() . '</div>';
		}
		flush();

	} else {
		/// @todo do not use a 'success' formatting for this line...
		echo '<div class="success">' . __('Populating the database with demo data.') . '</div>';
		flush();

		PopulateSQLDataBySQL($Path_To_Root. '/install/sql/demo.sql');

		$SQL = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('FirstLogIn','0')";
		$Result = DB_query($SQL);
		/// @todo echo error (warning?) if failure

		/// @todo there is no /companies/default folder atm...
		$CompanyDir = $Path_To_Root . '/companies/' . $DataBaseName;
		foreach (glob($Path_To_Root . '/companies/default/part_pics/*.jp*') as $JpegFile) {
			$Result = copy("../companies/default/part_pics/" . basename($JpegFile), $CompanyDir . '/part_pics/' . basename($JpegFile));
		}

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
										'" . $AdminUser . "',
										'" . CryptPass($AdminPassword) . "',
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
										'" . $Language . "',
										0,
										0,
										0
									)";
		$Result = DB_query($SQL);

		if (DB_error_no() == 0) {
			echo '<div class="success">' . __('The admin user has been inserted.') . '</div>';
		} else {
			echo '<div class="error">' . __('There was an error inserting the admin user') . ' - ' . DB_error_msg() . '</div>';
		}
		flush();

		/// @todo display a warning message if there was any query failure
		echo '<div class="success">' . __('Database now contains the demo data.') . '</div>';
	}

	/// @todo do not always return true
	return true;
}

function HighestFileName($Path_To_Root) {
	$files = glob($Path_To_Root . '/sql/updates/*.php');
	natsort($files);
	$LastFile = array_pop($files);
	return $LastFile ? basename($LastFile, ".php") : '';
}

function CryptPass($Password) {
	$Hash = password_hash($Password, PASSWORD_DEFAULT);
	return $Hash;
}

function PopulateSQLDataBySQL($File) {
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
			$Result = DB_query($SQL);
/*			if (DB_error_no() == 0) {
				echo '<div class="success">' . __('The admin user has been inserted.') . '</div>';
			}*/
			$SQL = '';
		}
		flush();

	} //end of for loop around the lines of the sql script
}

/**
 * @param $Path_To_Root
 * @return bool
 */
function CreateGLTriggers($Path_To_Root)
{
	$DBErrors = 0;
	foreach (glob($Path_To_Root . '/install/sql/triggers/*.sql') as $FileName) {
		$SQLScriptFile = file_get_contents($FileName);
		DB_IgnoreForeignKeys();
		$Result = DB_query($SQLScriptFile);
		$DBErrors += DB_error_no();
	}
	if ($DBErrors > 0) {
		echo '<div class="error">' . __('Database triggers could not be created') . '</div>';
	} else {
		echo '<div class="success">' . __('All database triggers have been created') . '</div>';
	}
	flush();

	return ($DBErrors == 0);
}

function CreateConfigFile($Path_To_Root, $configArray) {
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
					$Line = "date_default_timezone_set('".$NewValue."');\n";
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
		return false;
	}

	// Write the updated content to the new config file
	$NewConfigContent = implode($NewLines);
	$Result = file_put_contents($NewConfigFile, $NewConfigContent);

	if ($Result) {
		echo '<div class="success">' . __('The config.php file has been created based on your settings') . '</div>';
	} else {
		echo '<div class="error">' . __('Cannot write to the configuration file') . ' ' . $NewConfigFile . '</div>';
	}
	flush();

	return $Result;
}

/**
 * @param $Path_To_Root
 * @return bool
 */
function CreateCompaniesFile($Path_To_Root) {
	$Contents = "<?php\n\n";
	$Contents.= "\$CompanyName['" . $_SESSION['DatabaseName'] . "'] = '" . $_SESSION['CompanyRecord']['coyname'] . "';\n";

	$Result = false;
	$CompaniesFile = $Path_To_Root . '/companies/' . $_SESSION['DatabaseName'] . '/Companies.php';

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

	return (bool)$Result;
}
