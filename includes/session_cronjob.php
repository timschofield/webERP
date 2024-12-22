<?php
/* $Id: session.php 6338 2013-09-28 05:10:46Z daintree $*/

/*****************************************************************************************
KL RICARD MODIFICATIONS:
- Script based on session.php but simplified to used with cron jobs
- Change of AllowAnyone by AllowCronJobToBeRun to minimize risk of intrusions
- Added $_SESSION['UserID'] = "CronJobKL";
*****************************************************************************************/

if (!isset($PathPrefix)) {
	$PathPrefix='';
}

include($PathPrefix . 'config.php');

if (isset($dbuser)) {
	$DBUser=$dbuser;
	$DBPassword=$dbpassword;
	$DBType=$dbType;
}

if (isset($SessionSavePath)){
	session_save_path($SessionSavePath);
}

if (!isset($SysAdminEmail)) {
	$SysAdminEmail='';
}

ini_set('session.gc_maxlifetime',$SessionLifeTime);

if( !ini_get('safe_mode') ){
	set_time_limit($MaximumExecutionTime);
	ini_set('max_execution_time',$MaximumExecutionTime);
}
session_write_close(); //in case a previous session is not closed
session_start();

include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/DateFunctions.inc');

$_SESSION['AttemptsCounter'] = 0;

/* iterate through all elements of the $_POST array and DB_escape_string them
to limit possibility for SQL injection attacks and cross scripting attacks
*/

if (isset($_SESSION['DatabaseName'])){
	foreach ($_POST as $PostVariableName => $PostVariableValue) {
		if (gettype($PostVariableValue) != 'array') {
			if(get_magic_quotes_gpc()) {
				$_POST['name'] = stripslashes($_POST['name']);
			}
			$_POST[$PostVariableName] = DB_escape_string($PostVariableValue);
		} else {
			foreach ($PostVariableValue as $PostArrayKey => $PostArrayValue) {
				if(get_magic_quotes_gpc()) {
					$PostVariableValue[$PostArrayKey] = stripslashes($value[$PostArrayKey]);
				}
				$PostVariableValue[$PostArrayKey] = DB_escape_string($PostArrayValue);
			}
		}
	}

	/* iterate through all elements of the $_GET array and DB_escape_string them
	to limit possibility for SQL injection attacks and cross scripting attacks
	*/
	foreach ($_GET as $GetKey => $GetValue) {
		if (gettype($GetValue) != 'array') {
			$_GET[$GetKey] = DB_escape_string($GetValue);
		}
	}
} else { //set SESSION['FormID'] before the a user has even logged in
	$_SESSION['FormID'] = sha1(uniqid(mt_rand(), true));
}

include($PathPrefix . 'includes/LanguageSetup.php');

if (!isset($AllowCronJobToBeRun)){ /* only do security checks if AllowCronJobToBeRun is not true */
		exit;
} /* only do security checks if AllowCronJobToBeRun is not true */

/*User is logged in so get configuration parameters  - save in session*/
include($PathPrefix . 'includes/GetConfig.php');

/* RICARD KL: Do not perform any Version check to run Upgrade DB when we are doing cron jobs */

/* RICARD KL Set up the login theme for production, test, development, development test webERP */
$Theme = 'professional'; // Production environment: we are on production code with the real production DB
$_SESSION['Theme'] = $Theme;
/* RICARD KL END MODIFICATION Set up the login theme for production, test, development, development test webERP */

/* Set the logo if not yet set.
 * will be done only once per session and each time
 * we are not in session (i.e. before login)
 */
if (empty($_SESSION['LogoFile'])) {
	/* find a logo in companies/$CompanyDir
	 * (nice side effect of function:
	 * variables are local, so we will never
	 * cause name clashes)
	 */

	function findLogoFile($CompanyDir, $PathPrefix) {
		$dir = $PathPrefix.'companies/' . $CompanyDir . '/';
		$DirHandle = dir($dir);
		while ($DirEntry = $DirHandle->read() ){
			if ($DirEntry != '.' AND $DirEntry !='..'){
				$InCompanyDir[] = $DirEntry; //make an array of all files under company directory
			}
		} //loop through list of files in the company directory
		if ($InCompanyDir !== FALSE) {
			foreach($InCompanyDir as $logofilename) {
				if (strncasecmp($logofilename,'logo.png',8) === 0 AND
					is_readable($dir . $logofilename) AND
					is_file($dir . $logofilename)) {
					$logo = $logofilename;
					break;
				}
			}
			if (!isset($logo)) {
				foreach($InCompanyDir as $logofilename) {
					if (strncasecmp($logofilename,'logo.jpg',8) === 0 AND
						is_readable($dir . $logofilename) AND
						is_file($dir . $logofilename)) {
						$logo = $logofilename;
						break;
					}
				}
			}
			if (empty($logo)) {
				return null;
			} else {
				return 'companies/' .$CompanyDir .'/'. $logo;
			}
		} //end listing of files under company directory is not empty
	}

	/* Find a logo in companies/<company of this session> */
	if (!empty($_SESSION['DatabaseName'])) {
		$_SESSION['LogoFile'] = findLogoFile($_SESSION['DatabaseName'], $PathPrefix);
	}
}

if ($_SESSION['HTTPS_Only']==1){
	if ($_SERVER['HTTPS']!='on'){
		prnMsg(_('webERP is configured to allow only secure socket connections. Pages must be called with https://') . ' .....','error');
		exit;
	}
}



// Now check that the user as logged in has access to the page being called. $SecurityGroups is an array of
// arrays defining access for each group of users. These definitions can be modified by a system admin under setup


if (!is_array($_SESSION['AllowedPageSecurityTokens']) AND !isset($AllowCronJobToBeRun)) {
	$Title = _('Account Error Report');
	include($PathPrefix . 'includes/header.php');
	echo '<br /><br /><br />';
	prnMsg(_('Security settings have not been defined for your user account. Please advise your system administrator. It could also be that there is a session problem with your PHP web server'),'error');
	include($PathPrefix . 'includes/footer.php');
	exit;
}

/*The page security variable is now retrieved from the database in GetConfig.php and stored in the $SESSION['PageSecurityArray'] array
 * the key for the array is the script name - the script name is retrieved from the basename ($_SERVER['SCRIPT_NAME'])
 */
if (!isset($PageSecurity)){
//only hardcoded in the UpgradeDatabase script - so old versions that don't have the scripts.pagesecurity field do not choke
	$PageSecurity = $_SESSION['PageSecurityArray'][basename($_SERVER['SCRIPT_NAME'])];
}


if (!isset($AllowCronJobToBeRun)){
	if ((!in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PageSecurity))) {
		$Title = _('Security Permissions Problem');
		include($PathPrefix . 'includes/header.php');
		echo '<tr>
			<td class="menu_group_items">
				<table width="100%" class="table_index">
					<tr><td class="menu_group_item">';
		echo '<b><font style="size:+1; text-align:center;">' . _('The security settings on your account do not permit you to access this function') . '</font></b>';

		echo '</td>
			</tr>
			</table>
			</td>
			</tr>';

		include($PathPrefix . 'includes/footer.php');
		exit;
	}
}


if (in_array($_SESSION['PageSecurityArray']['WWW_Users.php'], $_SESSION['AllowedPageSecurityTokens'])) { /*System administrator login */
	$debug = 1; //allow debug messages
} else {
	$debug = 0; //don't allow debug messages
}
function CryptPass( $Password ) {
		global $CryptFunction;
		if ( $CryptFunction == 'sha1' ) {
			return sha1($Password);
		} elseif ( $CryptFunction == 'md5' ) {
			return md5($Password);
	} else {
			return $Password;
		}
 }


if (sizeof($_POST) > 0 AND !isset($AllowCronJobToBeRun)) {
	/*Security check to ensure that the form submitted is originally sourced from webERP with the FormID = $_SESSION['FormID'] - which is set before the first login*/
	if (!isset($_POST['FormID']) OR ($_POST['FormID'] != $_SESSION['FormID'])) {
		$Title = _('Error in form verification');
		include('includes/header.php');
		prnMsg(_('This form was not submitted with a correct ID') , 'error');
		include('includes/footer.php');
		exit;
	}
}

$_SESSION['UserID'] = "CronJobKL";

?>
