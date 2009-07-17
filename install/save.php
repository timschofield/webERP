<?php
error_reporting(E_ALL);
ini_set('display_errors', "On");
ini_set('max_execution_time', "180");

// Start a session
if(!defined('SESSION_STARTED')){
	session_name('ba_session_id');
	session_start();
	define('SESSION_STARTED', true);
}

function dircopy( $source, $target ) {
    $permissions=fileperms($source);
    if ( is_dir( $source ) ) {
        @mkdir( $target );
        $d = dir( $source );
        while ( FALSE !== ( $entry = $d->read() ) ) {
            if ( $entry == '.' || $entry == '..' )  {
                continue;
            }
            $Entry = $source . '/' . $entry;
            if ( is_dir( $Entry ) ) {
                dircopy( $Entry, $target . '/' . $entry );
                continue;
            }
            copy( $Entry, $target . '/' . $entry );
            chmod($target . '/' . $entry, $permissions);
        }
        $d->close();
    } else {
        copy( $source, $target );
    }
    chmod($target, $permissions);
}

function set_error($message) {
	
	if(isset($message) AND $message != '') {
		// Copy values entered into session so user doesn't have to re-enter everything
		if(isset($_POST['company_name'])) {
			$_SESSION['ba_url'] = $_POST['ba_url'];
			if(!isset($_POST['operating_system'])) {
				$_SESSION['operating_system'] = 'linux';
			} else {
				$_SESSION['operating_system'] = $_POST['operating_system'];
			} 
            if(!isset($_POST['world_writeable'])) {
				$_SESSION['world_writeable'] = false;
			} else {
				$_SESSION['world_writeable'] = true;
			}
			$_SESSION['database_host'] = $_POST['database_host'];
			$_SESSION['database_username'] = $_POST['database_username'];
			$_SESSION['database_password'] = $_POST['database_password'];
			$_SESSION['database_name'] = $_POST['company_name'];
			$_SESSION['timezone'] = $_POST['timezone'];
			if(!isset($_POST['install_tables'])) {
				$_SESSION['install_tables'] = false;
			} else {
				$_SESSION['install_tables'] = true;
			}
			$_SESSION['company_name'] = $_POST['company_name'];
			$_SESSION['admin_email'] = $_POST['admin_email'];
			$_SESSION['admin_password'] = $_POST['admin_password'];

		}
		// Set the message
		$_SESSION['message'] = $message;
		// Specify that session support is enabled
		$_SESSION['session_support'] = '<font class="good">Enabled</font>';
		// Redirect to first page again and exit
		header('Location: index.php?sessions_checked=true');
		exit();
	}
}

// Function to workout what the default permissions are for files created by the webserver
function default_file_mode($temp_dir) {
	$v = explode(".",PHP_VERSION);
	$v = $v[0].$v[1];
	
	if($v > 41 && is_writable($temp_dir)) {
		$filename = $temp_dir.'/test_permissions.txt';
		$handle = fopen($filename, 'w');
		fwrite($handle, 'This file is to get the default file permissions');
		fclose($handle);
		$default_file_mode = '0'.substr(sprintf('%o', fileperms($filename)), -3);
		unlink($filename);
	} else {
		$default_file_mode = '0777';
	}
	return $default_file_mode;
}

// Function to workout what the default permissions are for directories created by the webserver
function default_dir_mode($temp_dir) {
	$v = explode(".",PHP_VERSION);
	$v = $v[0].$v[1];
	if ($v > 41 && is_writable($temp_dir)) {
		$dirname = $temp_dir.'/test_permissions/';
		mkdir($dirname);
		$default_dir_mode = '0'.substr(sprintf('%o', fileperms($dirname)), -3);
		rmdir($dirname);
	} else {
		$default_dir_mode = '0777';
	}
	return $default_dir_mode;
}

function add_slashes($input) {
	if (get_magic_quotes_gpc() || (!is_string($input))) {
		return $input;
	}
	$output = addslashes($input);
	return $output;
}

function check_db_error($err_msg, $sql) {
	return true;
}

if (isset($_POST['path_to_root']))
	$path_to_root = $_POST['path_to_root'];
else
	$path_to_root = '..';

// Begin check to see if form was even submitted
// Set error if no post vars found

if (!isset($_POST['company_name'])) {
	set_error('Please fill-in the form below');
}
// End check to see if form was even submitted

// Begin path and timezone details code

// Check if user has entered the installation url
if (!isset($_POST['ba_url']) || $_POST['ba_url'] == '') {
	set_error('Please enter an absolute URL');
} else {
	$ba_url = $_POST['ba_url'];
}

// Remove any slashes at the end of the URL
if(substr($ba_url, strlen($ba_url) - 1, 1) == "/") {
	$ba_url = substr($ba_url, 0, strlen($ba_url) - 1);
}
if(substr($ba_url, strlen($ba_url) - 1, 1) == "\\") {
	$ba_url = substr($ba_url, 0, strlen($ba_url) - 1);
}
if(substr($ba_url, strlen($ba_url) - 1, 1) == "/") {
	$ba_url = substr($ba_url, 0, strlen($ba_url) - 1);
}
if(substr($ba_url, strlen($ba_url) - 1, 1) == "\\") {
	$ba_url = substr($ba_url, 0, strlen($ba_url) - 1);
}
// End path

// Begin operating system specific code
// Get operating system
if (!isset($_POST['operating_system']) || $_POST['operating_system'] != 'linux' && $_POST['operating_system'] != 'windows') {
	set_error('Please select a valid operating system');
} else {
	$operating_system = $_POST['operating_system'];
}
// Work-out file permissions
if($operating_system == 'windows') {
	$file_mode = '0777';
	$dir_mode = '0777';
} elseif (isset($_POST['world_writeable']) && $_POST['world_writeable'] == 'true') {
	$file_mode = '0777';
	$dir_mode = '0777';
} else {
	$file_mode = default_file_mode('../includes');
	$dir_mode = default_dir_mode('../includes');
}
// End operating system specific code

// Begin database details code
// Check if user has entered a database host
if (!isset($_POST['database_host']) || $_POST['database_host'] == '') {
	set_error('Please enter a database host name');
} 
// Check if user has entered a database username
if (!isset($_POST['database_username']) || $_POST['database_username'] == '') {
	set_error('Please enter a database username');
} 
// Check if user has entered a database password
if (!isset($_POST['database_password'])) {
	set_error('Please enter a database password');
} 
// Check if user has entered a database name
if (!isset($_POST['company_name']) || $_POST['company_name'] == '') {
	set_error('Please enter a company name');
} else {
	$_POST['company_name'] = add_slashes($_POST['company_name']);
}
 
if (!isset($_POST['timezone']) || $_POST['timezone'] == ''){
	set_error('Please enter timezone');
}

// Check if the user has entered a correct path
if (!file_exists($path_to_root.'/sql/mysql/weberp-demo.sql')){
	set_error('It appears the Absolute path that you entered is incorrect');
}

// Get admin email and validate it
if (!isset($_POST['admin_email']) || $_POST['admin_email'] == ''){
	set_error('Please enter an email for the Administrator account');
} else {
	if (eregi("^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$", $_POST['admin_email'])==false) {
		set_error('Please enter a valid email address for the Administrator account');
	}
}
// Get the two admin passwords entered, and check that they match
if (!isset($_POST['admin_password']) || $_POST['admin_password'] == '') {
	set_error('Please enter a password for the Administrator account');
} 
if (!isset($_POST['admin_repassword']) || $_POST['admin_repassword'] == '') {
	set_error('Please make sure you re-enter the password for the Administrator account');
}
if ($admin_password != $admin_repassword){
	set_error('Sorry, the two Administrator account passwords you entered do not match');
}
// End admin user details code

if ($table_prefix != "" && $id > 0){
	$table_prefix = $tb_pref_counter . "_";
}

$config_filename = $path_to_root . '/config.php';

dircopy($path_to_root.'/companies/weberpdemo', $path_to_root.'/companies/'.$_POST['company_name'], 0);

$err = write_config_db($table_prefix != "");

if ($err == -1) {
	set_error("Cannot open the configuration file ($config_filename)");
} elseif ($err == -2) {
	set_error("Cannot write to the configuration file ($config_filename)");
} elseif ($err == -3) {
	set_error("The configuration file $config_filename is not writable. Change its permissions so it is, then re-run step 4.");
}
// Try connecting to database

$db = mysql_connect($_POST['database_host'], $_POST['database_username'], $_POST['database_password']);
if (!$db){
	set_error('Database host name, username and/or password incorrect. MySQL Error:<br />'.mysql_error());
}

if($install_tables == true){
	// Try to create the database
	mysql_query('CREATE DATABASE IF NOT EXISTS `'.$database_name.'`', $db);
	mysql_select_db($database_name, $db);
	/* Need to read in the sql script and process the queries to initate a new DB */
	if ($_POST['db_file']==true){
		$SQLScriptFile = file('../sql/mysql/weberp-demo.sql');
	} else {
		$SQLScriptFile = file('../sql/mysql/weberp-new.sql');
	}
	$ScriptFileEntries = sizeof($SQLScriptFile);
	$ErrMsg = _('The script to create the new company database failed because');
	$SQL ='';
	$InAFunction = false;
	
	for ($i=0; $i<=$ScriptFileEntries; $i++) {
		
		$SQLScriptFile[$i] = trim($SQLScriptFile[$i]);
		//ignore lines that start with -- or USE or /*			
		if (substr($SQLScriptFile[$i], 0, 2) != '--' 
			AND substr($SQLScriptFile[$i], 0, 3) != 'USE' 
			AND strstr($SQLScriptFile[$i],'/*')==FALSE 
			AND strlen($SQLScriptFile[$i])>1){
				
			$SQL .= ' ' . $SQLScriptFile[$i];

			//check if this line kicks off a function definition - pg chokes otherwise
			if (substr($SQLScriptFile[$i],0,15) == 'CREATE FUNCTION'){
				$InAFunction = true;
			}
			//check if this line completes a function definition - pg chokes otherwise
			if (substr($SQLScriptFile[$i],0,8) == 'LANGUAGE'){
				$InAFunction = false;
			}
			if (strpos($SQLScriptFile[$i],';')>0 AND ! $InAFunction){
				$SQL = substr($SQL,0,strlen($SQL)-1);
				$result = mysql_query($SQL, $db);
				$SQL='';
			}
			
		} //end if its a valid sql line not a comment
	} //end of for loop around the lines of the sql script
} else {
	mysql_select_db($_POST['company_name'], $db);
}
$sql = "UPDATE www_users 
			SET password = '" . sha1($_POST['admin_password']) . "', 
				email = '".mysql_real_escape_string($_POST['admin_email']) ."' 
			WHERE user_id = 'admin'";
mysql_query($sql, $db);
$sql = "UPDATE companies 
			SET coyname = '". mysql_real_escape_string($_POST['company_name']) ." 
			WHERE coycode = 1"; 
mysql_query($sql, "could not update company name. Do it manually later in Setup");

session_unset();
session_destroy();

header('Location: ' . $path_to_root . '/index.php');
ini_set('max_execution_time', '60');
echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $path_to_root . '/index.php?' . SID . "'>";
//end of script
exit();

function write_config_db() {
	global $path_to_root;
	
	$msg = "<?php\n\n";
	$msg .= "/* \$Revision: 1.5 $ */\n";
	$msg .= "// User configurable variables\n";
	$msg .= "//---------------------------------------------------\n\n";
	$msg .= "//DefaultLanguage to use for the login screen and the setup of new users - the users language selection will override\n";
	$msg .= "\$DefaultLanguage ='en_GB';\n\n";
	$msg .= "// Whether to display the demo login and password or not on the login screen\n";
	$msg .= "\$allow_demo_mode = True;\n\n";
	$msg .= "// webERP version\n\n";
	$msg .= "\$Version = '3.11';\n\n";
	$msg .= "//  Connection information for the database\n";
	$msg .= "// \$host is the computer ip address or name where the database is located\n";
	$msg .= "// assuming that the web server is also the sql server\n";		
	$msg .= "\$host = '" . $_POST['database_host'] . "';\n\n";
	
	$msg .= "// assuming that the web server is also the sql server\n";		
	$msg .= "\$dbType = 'mysqli';\n";
	
	$msg .= "// assuming that the web server is also the sql server\n";		
	$msg .= "\$dbuser = '" . $_POST['database_user'] . "';\n";
	$msg .= "// assuming that the web server is also the sql server\n";		
	$msg .= "\$dbpassword = '" . $_POST['database_password'] . "';\n";
	
	$msg .= "// The timezone of the business - this allows the possibility of having;\n";
	
	$msg .= "putenv('" . $_POST['timezone'] . "');\n";
	$msg .= "\$AllowCompanySelectionBox = true;\n";
	$msg .= "\$DefaultCompany = '" . $_POST['company_name']. "';\n";
	$msg .= "\$SessionLifeTime = 3600;\n";
	$msg .= "\$MaximumExecutionTime =120;\n";
	$msg .= "\$CryptFunction = 'sha1';\n";
	$msg .= "\$DefaultClock = 12;\n";
	
	$msg .= "\$rootpath = dirname(\$_SERVER['PHP_SELF']);\n";		
	$msg .= "if (isset(\$DirectoryLevelsDeep)){\n";		
	$msg .= "for (\$i=0;\$i<\$DirectoryLevelsDeep;\$i++){\n";		
	$msg .= "\$rootpath = substr(\$rootpath,0, strrpos(\$rootpath,'/'));\n";		
	$msg .= "} }\n";		
	
	$msg .= "if (\$rootpath == '/' OR \$rootpath == '\\\') {;\n";		
	$msg .= "\$rootpath = '';\n";		
	$msg .= "}\n";		
	$msg .= "error_reporting (E_ALL & ~E_NOTICE);\n";		
	
	$msg .= "?>";
	
	if (!$zp = fopen($path_to_root . '/config.php', 'w')){
		return -1;
	} else {
		if (!fwrite($zp, $msg)){
			fclose($zp);
			return -2;
		}
		// close file
		fclose($zp);
	}
	return 0;
}
?>