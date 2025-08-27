<?php

function CreateCompanyLogo($CompanyName, $Path_To_Root, $CompanyDir) {
	if (extension_loaded('gd')) {
		// generate an image, based on company name

		// same size as logo_server.jpg
		/// @todo grab the size from the file via gd calls
		$width = 200;
		$height = 51;
		$Font = 3;

		$im = imagecreate($width, $height);
		$BackgroundColour = imagecolorallocate($im, 119, 119, 119); // #777777, same as default color theme
		$TextColour = imagecolorallocate($im, 255, 255, 255);

		$fw = imagefontwidth($Font);
		$fh = imagefontheight($Font);
		$TextWidth = $fw * mb_strlen($CompanyName);
		$px = (imagesx($im) - $TextWidth) / 2;
		$py = (imagesy($im) - ($fh)) / 2;
		imagefill($im, 0, 0, $BackgroundColour);
		imagestring($im, $Font, $px, $py, $CompanyName, $TextColour);

		/// @todo add white bevel, rounded border with transparent background

		if (!imagepng($im, $CompanyDir . '/logo.png')) {
			copy($Path_To_Root . '/images/logo_server.jpg', $CompanyDir . '/logo.jpg');
		}
		imagedestroy($im);

	} else {
		copy($Path_To_Root . '/images/logo_server.jpg', $CompanyDir . '/logo.jpg');
	}
}

/**
 * @todo we miss the PORT setting!
 * @return string[] error messages
 */
function CreateDataBase($HostName, $UserName, $Password, $DataBaseName) {
	$Errors = [];

	$DB = @mysqli_connect($HostName, $UserName, $Password, null, $MySQLPort);

	if (!$DB) {
		$Errors[] = __('Failed to connect the database management system');
		return $Errors;
	} else {
		// avoid exceptions being thrown on query errors
		mysqli_report(MYSQLI_REPORT_ERROR);

		mysqli_set_charset($DB, 'utf8');

		$Result = @mysqli_query($DB, 'SET SQL_MODE=""');
		$Result = @mysqli_query($DB, 'SET SESSION SQL_MODE=""');
	}

	$DBExistsSql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . mysqli_real_escape_string($DB, $DataBaseName) . "'";
	$PrivilegesSql = "SELECT * FROM INFORMATION_SCHEMA.USER_PRIVILEGES WHERE GRANTEE=" . '"' . "'" . mysqli_real_escape_string($DB, $UserName) . "'@'" . mysqli_real_escape_string($DB, $HostName) . "'" . '"' . " AND PRIVILEGE_TYPE='CREATE'";

	$DBExistsResult = @mysqli_query($DB, $DBExistsSql);
	$PrivilegesResult = @mysqli_query($DB, $PrivilegesSql);
	$Rows = @mysqli_num_rows($DBExistsResult);
	$Privileges = @mysqli_num_rows($PrivilegesResult);

	if ($Rows == 0) { /* Then the database does not exist */
		if ($Privileges == 0) {
			$Errors[] = __('The database does not exist, and this database user does not have privileges to create it');
		} else { /* Then we can create the database */
			/// @todo add utf8-mb4 as default charset
			$SQL = "CREATE DATABASE " . $DataBaseName;
			if (!@mysqli_query($DB, $SQL)) {
				$Errors[] = __('Failed creating the database');
			}
		}
	} else { /* Need to make sure any data is removed from existing DB */
		/// @todo this is incomplete - and dangerous!
		$SQL = "SELECT 'TRUNCATE TABLE ' + table_name + ';' FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $DataBaseName . "'";
		$Result = @mysqli_query($DB, $SQL);
	}

	return $Errors;
}

function CreateCompanyFolder($DatabaseName, $Path_To_Root) {
	if (!file_exists($Path_To_Root . '/companies/' . $DatabaseName)) {
		$CompanyDir = $Path_To_Root . '/companies/' . $DatabaseName;
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
		echo '<div class="success">' . __('The companies directory has been successfully created') . '</div>';
		ob_flush();

		/* Upload logo file */
		$TargetDir = $Path_To_Root . '/companies/' . $DatabaseName . '/';
		$TargetFile = $TargetDir . basename($_FILES["LogoFile"]["name"]);
		$UploadOK = 1;
		$ImageFileType = strtolower(pathinfo($TargetFile, PATHINFO_EXTENSION));

		if ($_FILES["LogoFile"]["tmp_name"] != '') {
			// Check if image file is an actual image or fake image
			if(isset($_POST["install"])) {
				$check = getimagesize($_FILES["LogoFile"]["tmp_name"]);
				if($check !== false) {
					$UploadOK = 1;
				} else {
					echo '<div class="error">' . __('Logo file is not an image.') . '</div>';
					$UploadOK = 0;
				}
			}

			// Check if file already exists
			if (file_exists($TargetFile)) {
				echo '<div class="error">' . __('Sorry, logo file already exists.') . '</div>';
				$UploadOK = 0;
			}

			// Check file size
			if ($_FILES["LogoFile"]["size"] > 500000) {
				echo '<div class="error">' . __('Sorry, your logo file is too large.') . '</div>';
				$UploadOK = 0;
			}

			// Allow certain file formats
			if ($ImageFileType != "jpg" && $ImageFileType != "png" && $ImageFileType != "jpeg" && $ImageFileType != "gif" ) {
				echo '<div class="error">' . __('Sorry, only JPG, JPEG, PNG & GIF logo files are allowed.') . '</div>';
				$UploadOK = 0;
			}

			// Check if $UploadOK is set to 0 by an error
			if ($UploadOK == 0) {
				echo '<div class="error">' . __('Sorry, your logo file was not uploaded.') . '</div>';
				// if everything is ok, try to upload file
			} else {
				if (move_uploaded_file($_FILES["LogoFile"]["tmp_name"], $TargetFile)) {
					echo '<div class="success">' . __('Your logo has been successfully uploaded') . '</div>';
				} else {
					echo '<div class="warn">' . __('Your logo could not be uploaded. You must copy this to your companies directory later.') . '</div>';
				}
			}
			ob_flush();
		} else {
			CreateCompanyLogo($DatabaseName, $Path_To_Root, $CompanyDir);
		}
	}
}

function CreateTables($Path_To_Root) {
	$DBErrors = 0;
	foreach (glob($Path_To_Root . "/install/sql/tables/*.sql") as $FileName) {
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
	ob_flush();
}

function UploadData($Demo, $AdminPassword, $AdminUser, $Email, $Language, $CoA, $CompanyName, $Path_To_Root, $DataBaseName) {
	if (isset($Demo) and $Demo != 'Yes') {
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
			echo '<div class="success">' . __('The admin user has been inserted.') . '</div>';
		} else {
			echo '<div class="error">' . __('There was an error inserting the admin user') . ' - ' . DB_error_msg() . '</div>';
		}
		ob_flush();

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
		ob_flush();

		$SQL = "INSERT INTO glaccountusers SELECT accountcode, 'admin', 1, 1 FROM chartmaster";
		$Result = DB_query($SQL);
		if (DB_error_no() == 0) {
			echo '<div class="success">' . __('The admin user has been given permissions on all GL accounts.') . '</div>';
		} else {
			echo '<div class="error">' . __('There was an error with creating permission for the admin user') . ' - ' . DB_error_msg() . '</div>';
		}
		ob_flush();

		$SQL = "INSERT INTO tags VALUES(0, 'None')";
		$Result = DB_query($SQL);
		if (DB_error_no() == 0) {
			echo '<div class="success">' . __('The default GL tag has been inserted.') . '</div>';
		} else {
			echo '<div class="error">' . __('There was an error inserting the default GL tag') . ' - ' . DB_error_msg() . '</div>';
		}
		ob_flush();

		$DBErrors = 0;
		foreach (glob($Path_To_Root . "/install/sql/data/*.sql") as $FileName) {
			$SQLScriptFile = file_get_contents($FileName);
			DB_IgnoreForeignKeys();
			$Result = DB_query($SQLScriptFile);
			$DBErrors += DB_error_no($Result);
		}
		if ($DBErrors > 0) {
			echo '<div class="error">' . __('Database tables could not be created') . '</div>';
		} else {
			echo '<div class="success">' . __('All database tables have been created') . '</div>';
		}
		ob_flush();

		$SQL = "INSERT INTO config VALUES('DBUpdateNumber', " . HighestFileName('../') . ")";
		$Result = DB_query($SQL);
		if (DB_error_no() == 0) {
			echo '<div class="success">' . __('The database update revision has been inserted.') . '</div>';
		} else {
			echo '<div class="error">' . __('There was an error inserting the DB revision number') . ' - ' . DB_error_msg() . '</div>';
		}
		ob_flush();

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
			echo '<div class="success">' . __('The company record has been inserted.') . '</div>';
		} else {
			echo '<div class="error">' . __('There was an error inserting the DB revision number') . ' - ' . DB_error_msg() . '</div>';
		}
		ob_flush();

	} else {
		echo '<div class="success">' . __('Populating the database with demo data.') . '</div>';

		PopulateSQLDataBySQL(__DIR__ . '/../sql/demo.sql');

		$SQL = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('FirstLogIn','0')";
		$Result = DB_query($SQL);

		// gg: there is no /companies/default folder atm...
		$CompanyDir = $Path_To_Root . 'companies/' . $DataBaseName;
		foreach (glob($Path_To_Root . "companies/default/part_pics/*.jp*") as $JpegFile) {
			copy("../companies/default/part_pics/" . basename($JpegFile), $CompanyDir . '/part_pics/' . basename($JpegFile));
		}

//		copy("companies/weberpdemo/logo.png", $CompanyDir . '/logo.png');
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
		ob_flush();
		echo '<div class="success">' . __('Database now contains the demo data.') . '</div>';
	}
}

function HighestFileName($PathPrefix) {
	$files = glob($PathPrefix . 'sql/updates/*.php');
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
			$SQL = '';
		}
		flush();

	} //end of for loop around the lines of the sql script

}

function CreateGLTriggers($Path_To_Root)
{
	$DBErrors = 0;
	foreach (glob($Path_To_Root . "/install/sql/triggers/*.sql") as $FileName) {
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

	return $DBErrors;
}
