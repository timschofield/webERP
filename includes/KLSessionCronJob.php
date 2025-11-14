<?php

/*****************************************************************************************
KL RICARD MODIFICATIONS:
- Script based on session.php but simplified to used with cron jobs
- Set up the login theme for development, production and test webERP
- Control match of DB and Code
- Commented out the standard call to Dashboard
- Change of AllowAnyone by AllowCronJobToBeRun to minimize risk of intrusions
- Added $_SESSION['UserID'] = "CronJobKL";
- Load the KLRoles Variables
*****************************************************************************************/

$AllowCronJobToBeRun = true;

if (!isset($PathPrefix)) {
	$PathPrefix = '';
}
require $PathPrefix.'vendor/autoload.php';

// KL RICARD: Include the specific KL session functions
include($PathPrefix . 'includes/KLsession.php');
// KL RICARD END: Include the specific KL session functions

// KL RICARD Select the database depending on the code version
$DefaultDatabase = KLDatabaseSelection();

// KL RICARD END Select the database depending on the code version

if (!file_exists($PathPrefix . 'config.php')) {
	// gg: there is no need for htmlspecialchars here, as we never output $RootPath into html
	$RootPath = dirname($_SERVER['PHP_SELF']);
	if ($RootPath == '/' or $RootPath == "\\") {
		$RootPath = '';
	}
	// KL RICARD: If the config.php file does not exist, DO NOT redirect to install/index.php as it is a cron job. Something is off, just exit
	//	header('Location:' . htmlspecialchars_decode($RootPath) . '/install/index.php');
	// KL RICARD END: If the config.php file does not exist, DO NOT redirect to install/index.php as it is a cron job. Something is off, just exit
	exit();
}

include($PathPrefix . 'config.php');

// KL RICARD: Include the specific KL config file
include($PathPrefix . 'KLConfig.php');
// KL RICARD END: Include the specific KL config file

if (isset($dbuser)) { //this gets past an upgrade issue where old versions used lower case variable names
	$DBUser = $dbuser;
	$DBPassword = $dbpassword;
	$DBType = $dbType;
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
	$SysAdminEmail = 'webmaster@kapal-laut.com';
}

ini_set('session.gc_maxlifetime', $SessionLifeTime);

session_write_close(); //in case a previous session is not closed
ini_set('session.cookie_httponly', 1);

// Set a specific session_name to avoid potential default session_name conflicts
// with other apps using the same host.
// For an example situation to support this need, see:
// http://www.weberp.org/forum/showthread.php?tid=8133
session_name('PHPSESSIDwebERPCronJob');
session_start();

include($PathPrefix . 'includes/ConnectDB.php');
include($PathPrefix . 'includes/DateFunctions.php');

if (!isset($_SESSION['AttemptsCounter']) or $AllowDemoMode == true) {
	$_SESSION['AttemptsCounter'] = 0;
}

/* KL RICARD Log the script we run so we can optimize CPU time*/	
$_SESSION['ScriptStartTime'] = microtime();

/* iterate through all elements of the $_POST array and DB_escape_string them
to limit possibility for SQL injection attacks and cross scripting attacks
*/

if (isset($_SESSION['DatabaseName'])) {
	
	foreach ($_POST as $PostVariableName => $PostVariableValue) {
		if (gettype($PostVariableValue) != 'array') {
			$_POST[$PostVariableName] = quote_smart($_POST[$PostVariableName]);
			$_POST[$PostVariableName] = DB_escape_string(htmlspecialchars($PostVariableValue, ENT_QUOTES, 'UTF-8'));
		} else {
			foreach ($PostVariableValue as $PostArrayKey => $PostArrayValue) {
				$PostVariableValue[$PostArrayKey] = quote_smart($PostVariableValue[$PostArrayKey]);
				$_POST[$PostVariableName][$PostArrayKey] = DB_escape_string(htmlspecialchars($PostArrayValue, ENT_QUOTES, 'UTF-8'));
			}
		}
	}

	/* iterate through all elements of the $_GET array and DB_escape_string them
	to limit possibility for SQL injection attacks and cross scripting attacks
	*/
	foreach ($_GET as $GetKey => $GetValue) {
		if (gettype($GetValue) != 'array') {
			$_GET[$GetKey] = DB_escape_string(htmlspecialchars($GetValue, ENT_QUOTES, 'UTF-8'));
		} else {
			foreach ($GetValue as $GetArrayKey => $GetArrayValue) {
				$_POST[$GetVariableName][$GetArrayKey] = DB_escape_string(htmlspecialchars($GetArrayValue, ENT_QUOTES, 'UTF-8'));

			}
		}
	}

} else { //set SESSION['FormID'] before the a user has even logged in
	$_SESSION['FormID'] = sha1(uniqid(mt_rand(), true));
}

include($PathPrefix . 'includes/LanguageSetup.php');
$FirstLogin = False;


if (basename($_SERVER['SCRIPT_NAME']) == 'Logout.php') {
	if (isset($_SESSION['Favourites'])) {
		//retrieve the sql data;
		$SQL = "SELECT href, caption FROM favourites WHERE userid='" . $_SESSION['UserID'] . "'";
		$ErrMsg = __('Failed to retrieve favorites');
		$Result = DB_query($SQL, $ErrMsg);
		if (DB_num_rows($Result) > 0) {
			$SQL = array();
			while ($MyRow = DB_fetch_array($Result)) {
				if (!isset($_SESSION['Favourites'][$MyRow['href']])) { //The script is removed;
					$SQL[] = "DELETE FROM favourites WHERE href='" . $MyRow['href'] . "' AND userid='" . $_SESSION['UserID'] . "'";
				} else {
					unset($_SESSION['Favourites'][$MyRow['href']]);
				}
			}
		}
	}

	header('Location: ' . htmlspecialchars_decode($RootPath) . '/index.php'); //go back to the main index/login

} elseif (isset($AllowCronJobToBeRun)) { /* only do security checks if AllowCronJobToBeRun is not true */
	if (!isset($_SESSION['AllowedPageSecurityTokens'])) {
		$_SESSION['AllowedPageSecurityTokens'] = array();
	}
	if (!isset($_SESSION['DatabaseName'])) {
		$_SESSION['DatabaseName'] = $DefaultDatabase;
	}
	$_SESSION['UserID'] = "CronJobKL";
	include_once($PathPrefix . 'includes/ConnectDB_' . $DBType . '.php');
	include($PathPrefix . 'includes/GetConfig.php');
} else {
	include $PathPrefix . 'includes/LoginFunctions.php'; /* Login checking and setup */

	if (isset($_POST['UserNameEntryField']) and isset($_POST['Password'])) {
		$rc = userLogin($_POST['UserNameEntryField'], $_POST['Password'], $SysAdminEmail);
		$FirstLogin = true;
	} elseif (empty($_SESSION['DatabaseName'])) {
		$rc = UL_SHOWLOGIN;
	} else {
		$rc = UL_OK;
	}
	
	// KL RICARD: Include the specific KL config file to assign a KL Role to each user
	include($PathPrefix . 'includes/KLRoles.php');
	// KL RICARD END: Include the specific KL config file

	/* RICARD KL Set up the login theme for production, test, development, development test webERP */
	$Theme = KLThemeSelection();
	/* RICARD KL END MODIFICATION Set up the login theme for production, test, development, development test webERP */
	
	switch ($rc) {
		case UL_OK; //user logged in successfully
			include($PathPrefix . 'includes/LanguageSetup.php'); //set up the language
			break;
	
		case UL_SHOWLOGIN:
			include($PathPrefix . 'includes/Login.php');
			exit();
	
		case UL_BLOCKED:
			die(include($PathPrefix . 'includes/FailedLogin.php'));
	
		case UL_CONFIGERR:
			$Title = __('Account Error Report');
			include($PathPrefix . 'includes/header.php');
			echo '<br /><br /><br />';
			prnMsg(__('Your user role does not have any access defined for webERP. There is an error in the security setup for this user account'), 'error');
			include($PathPrefix . 'includes/footer.php');
			exit();
	
		case UL_NOTVALID:
			$DemoText = '<font size="3" color="red"><b>' . __('incorrect password') . '</b></font><br /><b>' . __('The user/password combination') . '<br />' . __('is not a valid user of the system') . '</b>';
			die(include($PathPrefix . 'includes/Login.php'));
	
		case UL_MAINTENANCE:
			$DemoText = '<font size="3" color="red"><b>' . __('system maintenance') . '</b></font><br /><b>' . __('webERP is not available right now') . '<br />' . __('during maintenance of the system') . '</b>';
			die(include($PathPrefix . 'includes/Login.php'));
	
	}

	// KL RICARD Check if the user is allowed to access the page
	if (KLwebERPScriptCalledFromTEST()){
		/* If script is from TEST weberp or from localhost */
		if ($_SESSION['DatabaseName'] != "test_erp"){
			/* If DB is not test_erp we have a problem and should stop*/
			$Title = __('Wrong webERP Type');
			include($PathPrefix . 'includes/header.php');
			prnMsg(__('Accessing webERP TEST but connecting to Production Database. Logout and login again.'),'error');
			include($PathPrefix . 'includes/footer.php');
			exit();
		}
	} else {
		/* The script is not from TEST*/
		if ($_SESSION['DatabaseName'] != "kl_erp"){
			/* If DB is not kl_erp we have a problem and should stop*/
			include($PathPrefix . 'includes/header.php');
			prnMsg(__('Accessing webERP Production but connecting to TEST Database. Logout and login again.'),'error');
			include($PathPrefix . 'includes/footer.php');
			exit();
		}
	}
	// KL RICARD END Check if the user is allowed to access the page
}

/*If the Code $Version - held in ConnectDB.php is > than the Database VersionNumber held in config table then do upgrades */
/*If the highest of the DB update files is greater than the DBUpdateNumber held in config table then do upgrades */
/* RICARD KL No need to check for updates in cronjobs, just set the DBVersion to the highest file name */
$_SESSION['DBVersion'] = HighestFileName($PathPrefix);
//if (isset($_SESSION['DBVersion'])
//	and isset($_SESSION['DBUpdateNumber'])
//	and ($_SESSION['DBVersion'] > $_SESSION['DBUpdateNumber'])
//	and (basename($_SERVER['SCRIPT_NAME']) != 'Logout.php')
//	and (basename($_SERVER['SCRIPT_NAME']) != 'Z_UpgradeDatabase.php')) {
//	header('Location: ' . htmlspecialchars_decode($RootPath) . '/Z_UpgradeDatabase.php');
//}
//	exit();
/* KL RICARD END: No need to check for updates in cronjobs, just set the DBVersion to the highest file name} */

/* RICARD KL Set up the theme for production, test, development, development test webERP */
$_SESSION['Theme'] = KLThemeSelection();
/* RICARD KL END MODIFICATION Set up the theme for production, test, development, development test webERP */
if ($_SESSION['HTTPS_Only'] == 1) {
	if ($_SERVER['HTTPS'] != 'on') {
		prnMsg(__('webERP is configured to allow only secure socket connections. Pages must be called with https://') . ' .....', 'error');
		exit();
	}
}

/*The page security variable is now retrieved from the database in GetConfig.php and stored in the $SESSION['PageSecurityArray'] array
 * the key for the array is the script name - the script name is retrieved from the basename ($_SERVER['SCRIPT_NAME'])
*/

if (!isset($AllowCronJobToBeRun)){
	if ((!in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) or !isset($PageSecurity))) {
		$Title = __('Security Permissions Problem');
		include($PathPrefix . 'includes/header.php');
		echo '<tr>
				<td class="menu_group_items">
					<table width="100%" class="table_index">
						<tr>
							<td class="menu_group_item">
								<b><font style="size:+1; text-align:center;">' . __('The security settings on your account do not permit you to access this function') . '</font></b>
							</td>
						</tr>
					</table>
				</td>
			</tr>';

		include($PathPrefix . 'includes/footer.php');
		exit();
	}
}

/* KL RICARD for CronJobs, always $SupplierLogin = 0; $CustomerLogin = 0; $Debug = 0; */ 
$SupplierLogin = 0; //false
$CustomerLogin = 0;
$Debug = 0; //don't allow debug messages
/* KL RICARD for CronJobs, always $SupplierLogin = 0; $CustomerLogin = 0; $Debug = 0; */

if (sizeof($_POST) > 0 and !isset($AllowCronJobToBeRun)) {
	/*Security check to ensure that the form submitted is originally sourced from webERP with the FormID = $_SESSION['FormID'] - which is set before the first login*/
	if (!isset($_POST['FormID']) or ($_POST['FormID'] != $_SESSION['FormID'])) {
		$Title = __('Session verification error');
		include('includes/header.php');
		prnMsg(__('This page was not submitted with a correct FormID'), 'error');
		include('includes/footer.php');
		exit();
	}
}

$_SESSION['UserID'] = "CronJobKL";

function CryptPass($Password) {
	$Hash = password_hash($Password, PASSWORD_DEFAULT);
	return $Hash;
}

function VerifyPass($Password, $Hash) {
	if (PHP_VERSION_ID < 50500) {
		return (crypt($Password, $Hash) == $Hash);
	} else {
		return password_verify($Password, $Hash);
	}
}

function HighestFileName($PathPrefix) {
	$files = glob($PathPrefix.'sql/updates/*.php');
	natsort($files);
	$LastFile = array_pop($files);
	return $LastFile ? basename($LastFile, ".php") : '';
}

function quote_smart($Value) {
	// Quote if not integer
	if (!is_numeric($Value)) {
		$Value = "'" . DB_escape_string($Value) . "'";
	} 
	return $Value;
}
