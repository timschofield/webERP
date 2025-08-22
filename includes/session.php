<?php

/* webERP Session handling and general bootstrapping.
 *
 * This file is included at the start of every script in webERP.
 * It sets up the session and includes the necessary files for:
 * - database connection
 * - language setup
 * - password checking
 * - security authorisation level check
 * NB: config.php is included in session.php
 */

if (!isset($PathPrefix)) {
	$PathPrefix = __DIR__ . '/../';
}

require($PathPrefix.'vendor/autoload.php');

$DefaultDatabase = 'weberp';

if (!file_exists($PathPrefix . 'config.php')) {
	// gg: there is no need for htmlspecialchars here, as we never output $RootPath into html
	$RootPath = dirname($_SERVER['PHP_SELF']);
	if ($RootPath == '/' or $RootPath == "\\") {
		$RootPath = '';
	}
	header('Location:' . $RootPath . '/install/index.php');
	exit();
}

include($PathPrefix . 'config.php');

if (isset($dbuser)) { //this gets past an upgrade issue where old versions used lower case variable names
	/// @todo we should attempt to update the config.php file...
	$DBUser = $dbuser;
	$DBPassword = $dbpassword;
	$DBType = $dbType;
	unset($dbuser, $dbpassword, $dbType);
}

// another upgrade issue - mysql php extension is not available anymore, unless users are on obsolete php versions
if ($DBType === 'mysql' && !extension_loaded('mysql')) {
	/// @todo we should attempt to update the config.php file...
	$DBType = 'mysqli';
}

if (isset($SessionSavePath)) {
	session_save_path($SessionSavePath);
}

if (!isset($SysAdminEmail)) {
	$SysAdminEmail = '';
}

if (isset($_SESSION['Timeout'])) {
	ini_set('session.gc_maxlifetime', (60 * $_SESSION['Timeout'] + 1));
}

session_write_close(); //in case a previous session is not closed
ini_set('session.cookie_httponly', 1);

if (!isset($SessionName)) {
	$SessionName = 'PHPSESSIDwebERPteam';
}
session_name($SessionName);
session_start();

include($PathPrefix . 'includes/ConnectDB.php');
include($PathPrefix . 'includes/DateFunctions.php');

if (!isset($_SESSION['AttemptsCounter']) or $AllowDemoMode == true) {
	$_SESSION['AttemptsCounter'] = 0;
}

if (isset($_SESSION['DatabaseName'])) {

	/* iterate through all elements of the $_GET and $_POST arrays and DB_escape_string plus htmlspecialchars them
	to avoid both SQL injection attacks and cross scripting attacks
	*/

	foreach ($_POST as $PostVariableName => $PostVariableValue) {
		if (gettype($PostVariableValue) != 'array') {
			//$_POST[$PostVariableName] = quote_smart($PostVariableValue);
			$_POST[$PostVariableName] = DB_escape_string(htmlspecialchars($PostVariableValue, ENT_QUOTES, 'UTF-8'));
		} else {
			foreach ($PostVariableValue as $PostArrayKey => $PostArrayValue) {
				//$PostVariableValue[$PostArrayKey] = quote_smart($PostVariableValue[$PostArrayKey]);
				$_POST[$PostVariableName][$PostArrayKey] = DB_escape_string(htmlspecialchars($PostArrayValue, ENT_QUOTES, 'UTF-8'));
			}
		}
	}

	foreach ($_GET as $GetKey => $GetValue) {
		if (gettype($GetValue) != 'array') {
			$_GET[$GetKey] = DB_escape_string(htmlspecialchars($GetValue, ENT_QUOTES, 'UTF-8'));
		} else {
			foreach ($GetValue as $GetArrayKey => $GetArrayValue) {
				$_GET[$GetKey][$GetArrayKey] = DB_escape_string(htmlspecialchars($GetArrayValue, ENT_QUOTES, 'UTF-8'));
			}
		}
	}

} else { //set SESSION['FormID'] before a user has even logged in
	$_SESSION['FormID'] = sha1(uniqid(mt_rand(), true));
}

include($PathPrefix . 'includes/LanguageSetup.php');

$FirstLogin = False;

if (basename($_SERVER['SCRIPT_NAME']) == 'Logout.php') {
	if (isset($_SESSION['Favourites'])) {
		// Remove from the db the user favorites which are not in the session
		/// @todo this could be done in a single query using WHERE NOT IN ...
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

} elseif (isset($AllowAnyone)) { /* only do security checks if AllowAnyone is not true */
	if (!isset($_SESSION['DatabaseName'])) {
		$_SESSION['AllowedPageSecurityTokens'] = array();
		$_SESSION['DatabaseName'] = $DefaultDatabase;
	}
	include_once($PathPrefix . 'includes/ConnectDB_' . $DBType . '.php');
	include($PathPrefix . 'includes/GetConfig.php');

} else {
	include($PathPrefix . 'includes/UserLogin.php'); /* Login checking and setup. Includes GetConfig.php on successful logins */

	if (isset($_POST['UserNameEntryField']) and isset($_POST['Password'])) {
		$rc = userLogin($_POST['UserNameEntryField'], $_POST['Password'], $SysAdminEmail);
		$FirstLogin = true;
	} elseif (empty($_SESSION['DatabaseName'])) {
		$rc = UL_SHOWLOGIN;
	} else {
		$rc = UL_OK;
	}

	/*  Need to set the theme to make login screen nice */
	$Theme = (isset($_SESSION['Theme'])) ? $_SESSION['Theme'] : $DefaultTheme;

	switch ($rc) {
		case UL_OK; //user logged in successfully
			setcookie('Login', $_SESSION['DatabaseName']);
			include($PathPrefix . 'includes/LanguageSetup.php'); //set up the language
			if ($_SESSION['DBUpdateNumber'] >= 11) {
				$CheckSQL = "SELECT sessionid
							FROM sessions
							WHERE sessionid = '" . session_id() . "'";
				$CheckResult = DB_query($CheckSQL);
				if (DB_num_rows($CheckResult) == 0) {
					// new session
					// delete any previous session for this user
					if ($_SESSION['DBUpdateNumber'] >= 22) {
						$SQL = "DELETE FROM sessions
								WHERE userid = '" . $_SESSION['UserID'] . "'";
						$Result = DB_query($SQL);
						// insert the current session
						// session_id() is the PHP session id, which is unique for each session
						$SQL = "INSERT INTO sessions
										(sessionid,
										logintime,
										userid,
										script,
										scripttime)
								VALUES ('" . session_id() . "',
										NOW(),
										'" . $_SESSION['UserID'] . "',
										'" . basename($_SERVER['SCRIPT_NAME']) . "',
										NOW())";
						$Result = DB_query($SQL);
					}
				} else {
					// it is not a new session, update the script name
					$SQL = "UPDATE sessions
							SET script = '" . basename($_SERVER['SCRIPT_NAME']) . "',
								scripttime = NOW()
							WHERE sessionid='" . session_id() . "'";
					$Result = DB_query($SQL);
				}
				unset($CheckSQL, $CheckResult, $Result, $SQL);
			}
			break;

		case UL_SHOWLOGIN:
			include($PathPrefix . 'includes/Login.php');
			exit();

		case UL_BLOCKED:
			include($PathPrefix . 'includes/FailedLogin.php');
			exit();

		case UL_CONFIGERR:
			$Title = __('Account Error Report');
			include($PathPrefix . 'includes/header.php');
			echo '<br /><br /><br />';
			prnMsg(__('Your user role does not have any access defined for webERP. There is an error in the security setup for this user account'), 'error');
			include($PathPrefix . 'includes/footer.php');
			exit();

		case UL_NOTVALID:
			$DemoText = '<font size="3" color="red"><b>' . __('incorrect password') . '</b></font><br /><b>' . __('The user/password combination') . '<br />' . __('is not a valid user of the system') . '</b>';
			include($PathPrefix . 'includes/Login.php');
			exit();

		case UL_MAINTENANCE:
			$DemoText = '<font size="3" color="red"><b>' . __('system maintenance') . '</b></font><br /><b>' . __('webERP is not available right now') . '<br />' . __('during maintenance of the system') . '</b>';
			include($PathPrefix . 'includes/Login.php');
			exit();
	}

	unset($rc);
}

/* If the Code $Version - held in ConnectDB.php is > than the Database VersionNumber held in config table then do upgrades */
/* If the highest of the DB update files is greater than the DBUpdateNumber held in config table then do upgrades */
$_SESSION['DBVersion'] = HighestFileName($PathPrefix);
if (isset($_SESSION['DBVersion'])
	and isset($_SESSION['DBUpdateNumber'])
	and ($_SESSION['DBVersion'] > $_SESSION['DBUpdateNumber'])
	and (basename($_SERVER['SCRIPT_NAME']) != 'Logout.php')
	and (basename($_SERVER['SCRIPT_NAME']) != 'Z_UpgradeDatabase.php')) {
	header('Location: ' . htmlspecialchars_decode($RootPath) . '/Z_UpgradeDatabase.php');
	exit();
}

if (isset($_POST['Theme']) and ($_SESSION['UsersRealName'] == $_POST['RealName'])) {
	$_SESSION['Theme'] = $_POST['Theme'];
	$Theme = $_POST['Theme'];
} elseif (isset($_SESSION['Theme'])) {
	$Theme = $_SESSION['Theme'];
} else {
	$Theme = $DefaultTheme;
	$_SESSION['Theme'] = $DefaultTheme;
}

if ($_SESSION['HTTPS_Only'] == 1) {
	if ($_SERVER['HTTPS'] != 'on') {
		prnMsg(__('webERP is configured to allow only secure socket connections. Pages must be called with https://') . ' .....', 'error');
		exit();
	}
}

// Now check that the user as logged in has access to the page being called. $SecurityGroups is an array of
// arrays defining access for each group of users. These definitions can be modified by a system admin under setup

if (!is_array($_SESSION['AllowedPageSecurityTokens']) and !isset($AllowAnyone)) {
	$Title = __('Account Error Report');
	include($PathPrefix . 'includes/header.php');
	echo '<br /><br /><br />';
	prnMsg(__('Security settings have not been defined for your user account. Please advise your system administrator. It could also be that there is a session problem with your PHP web server'), 'error');
	include($PathPrefix . 'includes/footer.php');
	exit();
}

/*
 * The page security variable is now retrieved from the database in GetConfig.php and stored in the $SESSION['PageSecurityArray'] array
 * the key for the array is the script name - the script name is retrieved from the basename ($_SERVER['SCRIPT_NAME'])
 */
if (!isset($PageSecurity)) {
	//only hardcoded in the UpgradeDatabase script - so old versions that don't have the scripts.pagesecurity field do not choke
	$PageSecurity = $_SESSION['PageSecurityArray'][basename($_SERVER['SCRIPT_NAME']) ];
}

if (!isset($AllowAnyone)) {
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

// $PageSecurity = 9 hard coded for supplier access. Supplier access must have just 9 and 0 tokens
if (in_array(9, $_SESSION['AllowedPageSecurityTokens']) and count($_SESSION['AllowedPageSecurityTokens']) == 2) {
	$SupplierLogin = 1;
} else {
	$SupplierLogin = 0; // false

}
if (in_array(1, $_SESSION['AllowedPageSecurityTokens']) and count($_SESSION['AllowedPageSecurityTokens']) == 2) {
	$CustomerLogin = 1;
} else {
	$CustomerLogin = 0;
}

if ($FirstLogin and !$SupplierLogin and !$CustomerLogin and $_SESSION['ShowDashboard'] == 1) {
	header('Location: ' . htmlspecialchars_decode($RootPath) . '/Dashboard.php');
}

if (!isset($_POST['CompanyNameField']) and sizeof($_POST) > 0 and !isset($AllowAnyone)) {
	/*Security check to ensure that the form submitted is originally sourced from webERP with the FormID = $_SESSION['FormID'] - which is set before the first login*/
	if (!isset($_POST['FormID']) or ($_POST['FormID'] != $_SESSION['FormID'])) {
		$Title = __('Error in form verification');
		include('includes/header.php');
		prnMsg(__('This form was not submitted with a correct ID'), 'error');
		include('includes/footer.php');
		exit();
	}
}

function CryptPass($Password) {
	$Hash = password_hash($Password, PASSWORD_DEFAULT);
	return $Hash;
}

function VerifyPass($Password, $Hash) {
	return password_verify($Password, $Hash);
}

function HighestFileName($PathPrefix) {
	$files = glob($PathPrefix.'sql/updates/*.php');
	natsort($files);
	$LastFile = array_pop($files);
	return $LastFile ? basename($LastFile, ".php") : '';
}

/*function quote_smart($Value) {
	// Quote if not integer
	if (!is_numeric($Value)) {
		$Value = "'" . DB_escape_string($Value) . "'";
	}
	return $Value;
}*/
