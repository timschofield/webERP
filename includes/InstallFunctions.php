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
		imagestring($im, $Font, (int)$px, (int)$py, $CompanyName, $TextColour);

		imagesavealpha($im, true);

		$Result = true;
		if (!imagepng($im, $CompanyDir . '/logo.png')) {
			$Result = copy($Path_To_Root . '/images/default_logo.jpg', $CompanyDir . '/logo.jpg');
		}

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
function CreateDataBase($HostName, $UserName, $Password, $DataBaseName, $DBPort, $DBType, $Path_To_Root) {

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
function CreateTables($Path_To_Root, $DBType) {
	$DBErrors = 0;
	foreach (glob($Path_To_Root . '/install/sql/tables/*.sql') as $FileName) {
		$SQLScriptFile = file_get_contents($FileName);

		if ($DBType == 'mariadb') {
			// mariadb 5.5 chokes on STORED
			$SQLScriptFile = preg_replace('/([) ])STORED([, \n])/', ' $1PERSISTENT$2', $SQLScriptFile);
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

	return ($DBErrors == 0);
}

function UploadData($Demo, $AdminPassword, $AdminUser, $Email, $Language, $CoA, $CompanyName, $Path_To_Root,
	$DataBaseName, $DBType) {
	$Errors = 0;
	if ($Demo != 'Yes') {
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
										'" . $AdminUser . "',
										'" . CryptPass($AdminPassword) . "',
										'" . __('Administrator') . "',
										'',
										'',
										'',
										'',
										'" . $Email . "',
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
		$COAScriptFile = file($CoA);
		$ScriptFileEntries = sizeof($COAScriptFile);
		$SQL = '';
		$InAFunction = false;
		DB_IgnoreForeignKeys();
		for ($i = 0;$i < $ScriptFileEntries;$i++) {

			$COAScriptFile[$i] = trim($COAScriptFile[$i]);
			// ignore lines that start with -- or USE or /*
			/// @todo use a regexp to account for initial spaces
			if (mb_substr($COAScriptFile[$i], 0, 2) != '--' and mb_strstr($COAScriptFile[$i], '/*') == false and mb_strlen($COAScriptFile[$i]) > 1) {

				$SQL.= ' ' . $COAScriptFile[$i];

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
			$Result = DB_query($SQLScriptFile, '', '', false, false);
			$DBErrors += DB_error_no($Result);
		}
		DB_ReinstateForeignKeys();
		if ($DBErrors > 0) {
			$Errors++;
			echo '<div class="error">' . __('Database tables could not be populated') . '</div>';
		} else {
			echo '<div class="success">' . __('All database tables have been populated') . '</div>';
		}
		flush();

		/// @todo there is no guarantee that all the db updates have been applied to the single SQL files making up
		///       the installer - that is left to the person preparing the release to verify...
		$SQL = "INSERT INTO config VALUES('DBUpdateNumber', " . HighestFileName($Path_To_Root) . ")";
		$Result = DB_query($SQL, '', '', false, false);
		if (DB_error_no() == 0) {
			echo '<div class="success">' . __('The database update revision has been inserted') . '</div>';
		} else {
			$Errors++;
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
		$Result = DB_query($SQL, '', '', false, false);
		if (DB_error_no() == 0) {
			echo '<div class="success">' . __('The company record has been inserted') . '</div>';
		} else {
			$Errors++;
			echo '<div class="error">' . __('There was an error inserting the DB revision number') . ' - ' . DB_error_msg() . '</div>';
		}
		flush();

		DB_ReinstateForeignKeys();

	} else {
		echo '<div class="info">' . __('Populating the database with demo data.') . '</div>';
		flush();

		DB_IgnoreForeignKeys();
		$Errors = (int)PopulateSQLDataBySQLFile($Path_To_Root. '/install/sql/demo.sql', $DBType);

		/// @todo this could just be pushed into demo.sql - and checked for presence by the scripts in /build
		$SQL = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('FirstLogIn','0')";
		$Result = DB_query($SQL, '', '', false, false);
		/// @todo echo error (warning?) if failure
		if (DB_error_no() == 0) {
			//echo '<div class="success">' . __('...') . '</div>';
		} else {
			$Errors++;
			//echo '<div class="error">' . __('...') . '</div>';
		}
		DB_ReinstateForeignKeys();

		$CompanyDir = $Path_To_Root . '/companies/' . $DataBaseName;
		foreach (glob($Path_To_Root . '/companies/weberpdemo/part_pics/*.jp*') as $JpegFile) {
			copy($Path_To_Root . "/companies/weberpdemo/part_pics/" . basename($JpegFile), $CompanyDir . '/part_pics/' . basename($JpegFile));
		}

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
										'" . $AdminUser . "',
										'" . CryptPass($AdminPassword) . "',
										'" . __('Administrator') . "',
										'',
										'',
										'',
										'',
										'" . $Email . "',
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
		$Result = DB_query($SQL, '', '', false, false);
		if (DB_error_no() == 0) {
			echo '<div class="success">' . __('The admin user has been inserted.') . '</div>';
		} else {
			$Errors++;
			echo '<div class="error">' . __('There was an error inserting the admin user') . ' - ' . DB_error_msg() . '</div>';
		}
		DB_ReinstateForeignKeys();
		flush();

		/// @todo display a warning message if there was any query failure
		echo '<div class="success">' . __('Database now contains the demo data.') . '</div>';
	}

	/// @todo check for errors

	$SQL = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('part_pics_dir', 'companies/" . $DataBaseName . "/part_pics')";
	$Result = DB_query($SQL);

	copy($Path_To_Root . '/companies/weberpdemo/part_pics/webERPsmall.png', $CompanyDir . '/part_pics/webERPsmall.png');

	return ($Errors == 0);
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

/**
 * @param string $File
 * @param string $DBType
 * @return bool
 */
function PopulateSQLDataBySQLFile($File, $DBType) {
	$SQLScriptFile = file($File);
	$ScriptFileEntries = sizeof($SQLScriptFile);

	$Errors = 0;
	$SQL = '';
	$InAFunction = false;
	for ($i = 1; $i <= $ScriptFileEntries; $i++) {

		$SQLScriptFile[$i - 1] = trim($SQLScriptFile[$i - 1]);

		/// @todo ignore lines that start with `--` or USE or /* or CREATE DATABASE

		$SQL.= ' ' . $SQLScriptFile[$i - 1];

		// check if this line kicks off a function definition - pg chokes otherwise
		/// @todo we can disable this filter if on mysql/mariadb
		/// @todo use a regexp
		if (mb_substr($SQLScriptFile[$i - 1], 0, 15) == 'CREATE FUNCTION') {
			$InAFunction = true;
		}
		// check if this line completes a function definition - pg chokes otherwise
		/// @todo use a regexp
		if (mb_substr($SQLScriptFile[$i - 1], 0, 8) == 'LANGUAGE') {
			$InAFunction = false;
		}
		if (mb_strpos($SQLScriptFile[$i - 1], ';') > 0 and !$InAFunction) {
			// Database created above with correct name.
			$Result = DB_query($SQL, '', '', false, false);
			if (DB_error_no() != 0) {
				$Errors++;
			}
			$SQL = '';
		}
		flush();

	} //end of for loop around the lines of the sql script

	return ($Errors == 0);
}

/**
 * @param $Path_To_Root
 * @return bool
 */
function CreateGLTriggers($Path_To_Root, $DBType)
{
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

	return ($DBErrors == 0);
}

function CreateConfigFile($Path_To_Root, $configArray, $timezone) {
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
					$NewValue = addslashes($timezone);
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
		$Result = true;

	} else {
		// Write the updated content to the new config file
		$NewConfigContent = implode($NewLines);
		$Result = file_put_contents($NewConfigFile, $NewConfigContent);

		if ($Result) {
			echo '<div class="success">' . __('The config.php file has been created based on your settings') . '</div>';
		} else {
			echo '<div class="error">' . __('Cannot write to the configuration file') . ' ' . $NewConfigFile . '</div>';
		}
		flush();
		$Result = true;
	}
	return $Result;
}

/**
 * @param string $Path_To_Root
 * @return bool
 */
function CreateCompaniesFile($Path_To_Root, $DatabaseName, $CoyName) {
	$Contents = "<?php\n\n";
	$Contents.= "\$CompanyName['" . $DatabaseName . "'] = '" . $CoyName . "';\n";

	$Result = false;
	$CompaniesFile = $Path_To_Root . '/companies/' . $DatabaseName . '/Companies.php';

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

/// @todo in case this is used outside the installer, we could move to its own file, TimezonesArray.php
function GetTimezones() {
	return array(
		'Africa/Abidjan',
		'Africa/Accra',
		'Africa/Addis_Ababa',
		'Africa/Algiers',
		'Africa/Asmara',
		'Africa/Asmera',
		'Africa/Bamako',
		'Africa/Bangui',
		'Africa/Banjul',
		'Africa/Bissau',
		'Africa/Blantyre',
		'Africa/Brazzaville',
		'Africa/Bujumbura',
		'Africa/Cairo',
		'Africa/Casablanca',
		'Africa/Ceuta',
		'Africa/Conakry',
		'Africa/Dakar',
		'Africa/Dar_es_Salaam',
		'Africa/Djibouti',
		'Africa/Douala',
		'Africa/El_Aaiun',
		'Africa/Freetown',
		'Africa/Gaborone',
		'Africa/Harare',
		'Africa/Johannesburg',
		'Africa/Kampala',
		'Africa/Khartoum',
		'Africa/Kigali',
		'Africa/Kinshasa',
		'Africa/Lagos',
		'Africa/Libreville',
		'Africa/Lome',
		'Africa/Luanda',
		'Africa/Lubumbashi',
		'Africa/Lusaka',
		'Africa/Malabo',
		'Africa/Maputo',
		'Africa/Maseru',
		'Africa/Mbabane',
		'Africa/Mogadishu',
		'Africa/Monrovia',
		'Africa/Nairobi',
		'Africa/Ndjamena',
		'Africa/Niamey',
		'Africa/Nouakchott',
		'Africa/Ouagadougou',
		'Africa/Porto-Novo',
		'Africa/Sao_Tome',
		'Africa/Timbuktu',
		'Africa/Tripoli',
		'Africa/Tunis',
		'Africa/Windhoek',
		'America/Adak',
		'America/Anchorage',
		'America/Anguilla',
		'America/Antigua',
		'America/Araguaina',
		'America/Argentina/Buenos_Aires',
		'America/Argentina/Catamarca',
		'America/Argentina/ComodRivadavia',
		'America/Argentina/Cordoba',
		'America/Argentina/Jujuy',
		'America/Argentina/La_Rioja',
		'America/Argentina/Mendoza',
		'America/Argentina/Rio_Gallegos',
		'America/Argentina/Salta',
		'America/Argentina/San_Juan',
		'America/Argentina/San_Luis',
		'America/Argentina/Tucuman',
		'America/Argentina/Ushuaia',
		'America/Aruba',
		'America/Asuncion',
		'America/Atikokan',
		'America/Atka',
		'America/Bahia',
		'America/Barbados',
		'America/Belem',
		'America/Belize',
		'America/Blanc-Sablon',
		'America/Boa_Vista',
		'America/Bogota',
		'America/Boise',
		'America/Buenos_Aires',
		'America/Cambridge_Bay',
		'America/Campo_Grande',
		'America/Cancun',
		'America/Caracas',
		'America/Catamarca',
		'America/Cayenne',
		'America/Cayman',
		'America/Chicago',
		'America/Chihuahua',
		'America/Coral_Harbour',
		'America/Cordoba',
		'America/Costa_Rica',
		'America/Cuiaba',
		'America/Curacao',
		'America/Danmarkshavn',
		'America/Dawson',
		'America/Dawson_Creek',
		'America/Denver',
		'America/Detroit',
		'America/Dominica',
		'America/Edmonton',
		'America/Eirunepe',
		'America/El_Salvador',
		'America/Ensenada',
		'America/Fort_Wayne',
		'America/Fortaleza',
		'America/Glace_Bay',
		'America/Godthab',
		'America/Goose_Bay',
		'America/Grand_Turk',
		'America/Grenada',
		'America/Guadeloupe',
		'America/Guatemala',
		'America/Guayaquil',
		'America/Guyana',
		'America/Halifax',
		'America/Havana',
		'America/Hermosillo',
		'America/Indiana/Indianapolis',
		'America/Indiana/Knox',
		'America/Indiana/Marengo',
		'America/Indiana/Petersburg',
		'America/Indiana/Tell_City',
		'America/Indiana/Vevay',
		'America/Indiana/Vincennes',
		'America/Indiana/Winamac',
		'America/Indianapolis',
		'America/Inuvik',
		'America/Iqaluit',
		'America/Jamaica',
		'America/Jujuy',
		'America/Juneau',
		'America/Kentucky/Louisville',
		'America/Kentucky/Monticello',
		'America/Knox_IN',
		'America/La_Paz',
		'America/Lima',
		'America/Los_Angeles',
		'America/Louisville',
		'America/Maceio',
		'America/Managua',
		'America/Manaus',
		'America/Marigot',
		'America/Martinique',
		'America/Mazatlan',
		'America/Mendoza',
		'America/Menominee',
		'America/Merida',
		'America/Mexico_City',
		'America/Miquelon',
		'America/Moncton',
		'America/Monterrey',
		'America/Montevideo',
		'America/Montreal',
		'America/Montserrat',
		'America/Nassau',
		'America/New_York',
		'America/Nipigon',
		'America/Nome',
		'America/Noronha',
		'America/North_Dakota/Center',
		'America/North_Dakota/New_Salem',
		'America/Panama',
		'America/Pangnirtung',
		'America/Paramaribo',
		'America/Phoenix',
		'America/Port-au-Prince',
		'America/Port_of_Spain',
		'America/Porto_Acre',
		'America/Porto_Velho',
		'America/Puerto_Rico',
		'America/Rainy_River',
		'America/Rankin_Inlet',
		'America/Recife',
		'America/Regina',
		'America/Resolute',
		'America/Rio_Branco',
		'America/Rosario',
		'America/Santarem',
		'America/Santiago',
		'America/Santo_Domingo',
		'America/Sao_Paulo',
		'America/Scoresbysund',
		'America/Shiprock',
		'America/St_Barthelemy',
		'America/St_Johns',
		'America/St_Kitts',
		'America/St_Lucia',
		'America/St_Thomas',
		'America/St_Vincent',
		'America/Swift_Current',
		'America/Tegucigalpa',
		'America/Thule',
		'America/Thunder_Bay',
		'America/Tijuana',
		'America/Toronto',
		'America/Tortola',
		'America/Vancouver',
		'America/Virgin',
		'America/Whitehorse',
		'America/Winnipeg',
		'America/Yakutat',
		'America/Yellowknife',
		'Asia/Aden',
		'Asia/Almaty',
		'Asia/Amman',
		'Asia/Anadyr',
		'Asia/Aqtau',
		'Asia/Aqtobe',
		'Asia/Ashgabat',
		'Asia/Ashkhabad',
		'Asia/Baghdad',
		'Asia/Bahrain',
		'Asia/Baku',
		'Asia/Bangkok',
		'Asia/Beirut',
		'Asia/Bishkek',
		'Asia/Brunei',
		'Asia/Choibalsan',
		'Asia/Chongqing',
		'Asia/Chungking',
		'Asia/Colombo',
		'Asia/Dacca',
		'Asia/Damascus',
		'Asia/Dhaka',
		'Asia/Dili',
		'Asia/Dubai',
		'Asia/Dushanbe',
		'Asia/Gaza',
		'Asia/Harbin',
		'Asia/Ho_Chi_Minh',
		'Asia/Hong_Kong',
		'Asia/Hovd',
		'Asia/Irkutsk',
		'Asia/Istanbul',
		'Asia/Jakarta',
		'Asia/Jayapura',
		'Asia/Jerusalem',
		'Asia/Kabul',
		'Asia/Kamchatka',
		'Asia/Karachi',
		'Asia/Kashgar',
		'Asia/Kathmandu',
		'Asia/Katmandu',
		'Asia/Kolkata',
		'Asia/Krasnoyarsk',
		'Asia/Kuala_Lumpur',
		'Asia/Kuching',
		'Asia/Kuwait',
		'Asia/Macao',
		'Asia/Macau',
		'Asia/Magadan',
		'Asia/Makassar',
		'Asia/Manila',
		'Asia/Muscat',
		'Asia/Nicosia',
		'Asia/Novosibirsk',
		'Asia/Omsk',
		'Asia/Oral',
		'Asia/Phnom_Penh',
		'Asia/Pontianak',
		'Asia/Pyongyang',
		'Asia/Qatar',
		'Asia/Qyzylorda',
		'Asia/Rangoon',
		'Asia/Riyadh',
		'Asia/Saigon',
		'Asia/Sakhalin',
		'Asia/Samarkand',
		'Asia/Seoul',
		'Asia/Shanghai',
		'Asia/Singapore',
		'Asia/Taipei',
		'Asia/Tashkent',
		'Asia/Tbilisi',
		'Asia/Tehran',
		'Asia/Tel_Aviv',
		'Asia/Thimbu',
		'Asia/Thimphu',
		'Asia/Tokyo',
		'Asia/Ujung_Pandang',
		'Asia/Ulaanbaatar',
		'Asia/Ulan_Bator',
		'Asia/Urumqi',
		'Asia/Vientiane',
		'Asia/Vladivostok',
		'Asia/Yakutsk',
		'Asia/Yekaterinburg',
		'Asia/Yerevan',
		'Atlantic/Azores',
		'Atlantic/Bermuda',
		'Atlantic/Canary',
		'Atlantic/Cape_Verde',
		'Atlantic/Faeroe',
		'Atlantic/Faroe',
		'Atlantic/Jan_Mayen',
		'Atlantic/Madeira',
		'Atlantic/Reykjavik',
		'Atlantic/South_Georgia',
		'Atlantic/St_Helena',
		'Atlantic/Stanley',
		'Australia/ACT',
		'Australia/Adelaide',
		'Australia/Brisbane',
		'Australia/Broken_Hill',
		'Australia/Canberra',
		'Australia/Currie',
		'Australia/Darwin',
		'Australia/Eucla',
		'Australia/Hobart',
		'Australia/LHI',
		'Australia/Lindeman',
		'Australia/Lord_Howe',
		'Australia/Melbourne',
		'Australia/North',
		'Australia/NSW',
		'Australia/Perth',
		'Australia/Queensland',
		'Australia/South',
		'Australia/Sydney',
		'Australia/Tasmania',
		'Australia/Victoria',
		'Australia/West',
		'Australia/Yancowinna',
		'Europe/Amsterdam',
		'Europe/Andorra',
		'Europe/Athens',
		'Europe/Belfast',
		'Europe/Belgrade',
		'Europe/Berlin',
		'Europe/Bratislava',
		'Europe/Brussels',
		'Europe/Bucharest',
		'Europe/Budapest',
		'Europe/Chisinau',
		'Europe/Copenhagen',
		'Europe/Dublin',
		'Europe/Gibraltar',
		'Europe/Guernsey',
		'Europe/Helsinki',
		'Europe/Isle_of_Man',
		'Europe/Istanbul',
		'Europe/Jersey',
		'Europe/Kaliningrad',
		'Europe/Kiev',
		'Europe/Lisbon',
		'Europe/Ljubljana',
		'Europe/London',
		'Europe/Luxembourg',
		'Europe/Madrid',
		'Europe/Malta',
		'Europe/Mariehamn',
		'Europe/Minsk',
		'Europe/Monaco',
		'Europe/Moscow',
		'Europe/Nicosia',
		'Europe/Oslo',
		'Europe/Paris',
		'Europe/Podgorica',
		'Europe/Prague',
		'Europe/Riga',
		'Europe/Rome',
		'Europe/Samara',
		'Europe/San_Marino',
		'Europe/Sarajevo',
		'Europe/Simferopol',
		'Europe/Skopje',
		'Europe/Sofia',
		'Europe/Stockholm',
		'Europe/Tallinn',
		'Europe/Tirane',
		'Europe/Tiraspol',
		'Europe/Uzhgorod',
		'Europe/Vaduz',
		'Europe/Vatican',
		'Europe/Vienna',
		'Europe/Vilnius',
		'Europe/Volgograd',
		'Europe/Warsaw',
		'Europe/Zagreb',
		'Europe/Zaporozhye',
		'Europe/Zurich',
		'Indian/Antananarivo',
		'Indian/Chagos',
		'Indian/Christmas',
		'Indian/Cocos',
		'Indian/Comoro',
		'Indian/Kerguelen',
		'Indian/Mahe',
		'Indian/Maldives',
		'Indian/Mauritius',
		'Indian/Mayotte',
		'Indian/Reunion',
		'Pacific/Apia',
		'Pacific/Auckland',
		'Pacific/Chatham',
		'Pacific/Easter',
		'Pacific/Efate',
		'Pacific/Enderbury',
		'Pacific/Fakaofo',
		'Pacific/Fiji',
		'Pacific/Funafuti',
		'Pacific/Galapagos',
		'Pacific/Gambier',
		'Pacific/Guadalcanal',
		'Pacific/Guam',
		'Pacific/Honolulu',
		'Pacific/Johnston',
		'Pacific/Kiritimati',
		'Pacific/Kosrae',
		'Pacific/Kwajalein',
		'Pacific/Majuro',
		'Pacific/Marquesas',
		'Pacific/Midway',
		'Pacific/Nauru',
		'Pacific/Niue',
		'Pacific/Norfolk',
		'Pacific/Noumea',
		'Pacific/Pago_Pago',
		'Pacific/Palau',
		'Pacific/Pitcairn',
		'Pacific/Ponape',
		'Pacific/Port_Moresby',
		'Pacific/Rarotonga',
		'Pacific/Saipan',
		'Pacific/Samoa',
		'Pacific/Tahiti',
		'Pacific/Tarawa',
		'Pacific/Tongatapu',
		'Pacific/Truk',
		'Pacific/Wake',
		'Pacific/Wallis',
		'Pacific/Yap',
		'Etc/UTC'
	);
}
