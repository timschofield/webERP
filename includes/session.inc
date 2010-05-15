<?php
/* $Revision: 1.67 $ */

if (!isset($PathPrefix)) {
	$PathPrefix='';
}
if (!file_exists($PathPrefix . 'config.php')){
	$rootpath = dirname($_SERVER['PHP_SELF']);
	if ($rootpath == '/' OR $rootpath == "\\") {
		$rootpath = '';
	}
	header('Location:' . $rootpath . '/install/index.php');
}
include($PathPrefix . 'config.php');

if (isset($SessionSavePath)){
	session_save_path($SessionSavePath);
}

ini_set('session.gc_Maxlifetime',$SessionLifeTime);
ini_set('max_execution_time',$MaximumExecutionTime);

session_start();

include($PathPrefix . 'includes/LanguageSetup.php');
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/DateFunctions.inc');

// Un comment to turn off attempts counter
//$_SESSION['AttemptsCounter'] = 0;

if (!isset($_SESSION['AttemptsCounter'])){
	$_SESSION['AttemptsCounter'] = 0;
}

/* iterate through all elements of the $_POST array and DB_escape_string them
to limit possibility for SQL injection attacks and cross scripting attacks
*/

if (isset($_SESSION['DatabaseName'])){
	foreach ($_POST as $key => $value) {
		if (gettype($value) != "array") {
			$_POST[$key] = DB_escape_string($value);
		} else {
			foreach ($value as $key1 => $value1) {
				$value[$key1] = DB_escape_string($value1);
			}
		}
	}

	/* iterate through all elements of the $_GET array and DB_escape_string them
	to limit possibility for SQL injection attacks and cross scripting attacks
	*/
	foreach ($_GET as $key => $value) {
		if (gettype($value) != "array") {
			$_GET[$key] = DB_escape_string($value);
		}
	}
}

if (!isset($AllowAnyone)){ /* only do security checks if AllowAnyone is not true */

	include $PathPrefix . 'includes/UserLogin.php';	/* Login checking and setup */

	if (isset($_POST['UserNameEntryField']) and isset($_POST['Password'])) {
		$rc = userLogin($_POST['UserNameEntryField'], $_POST['Password'], $db);
	} else if (empty($_SESSION['DatabaseName'])) {
		$rc = UL_SHOWLOGIN;
	} else {
		$rc = UL_OK;
	}

	/*  Need to set the theme to make login screen nice */
	$theme = (isset($_SESSION['Theme'])) ? $_SESSION['Theme'] : 'silverwolf';
	switch ($rc) {
	case  UL_OK;
		break;

	case UL_SHOWLOGIN:
		include($PathPrefix . 'includes/Login.php');
		exit;

	case UL_BLOCKED:
		die(include($PathPrefix . 'includes/FailedLogin.php'));

	case  UL_CONFIGERR:
		$title = _('Account Error Report');
		include($PathPrefix . 'includes/header.inc');
		echo '<br /><br /><br />';
		prnMsg(_('Your user role does not have any access defined for webERP. There is an error in the security setup for this user account'),'error');
		include($PathPrefix . 'includes/footer.inc');
		    exit;

	case  UL_NOTVALID:
		$demo_text = '<font size="3" color="red"><b>' .  _('incorrect password') . '</b></font><br /><b>' . _('The user/password combination') . '<br />' . _('is not a valid user of the system') . '</b>';
		die(include($PathPrefix . 'includes/Login.php'));

	case  UL_MAINTENANCE:
		$demo_text = '<font size="3" color="red"><b>' .  _('system maintenance') . '</b></font><br /><b>' . _('webERP is not available right now') . '<br />' . _('during maintenance of the system') . '</b>';
		die(include($PathPrefix . 'includes/Login.php'));

	}
} /* only do security checks if AllowAnyone is not true */

/*User is logged in so get configuration parameters  - save in session*/
include($PathPrefix . 'includes/GetConfig.php');

if(isset($_SESSION['DB_Maintenance'])){
	if ($_SESSION['DB_Maintenance']!=0)  {
		if (DateDiff(Date($_SESSION['DefaultDateFormat']),
				ConvertSQLDate($_SESSION['DB_Maintenance_LastRun'])
				,'d')	>= 	$_SESSION['DB_Maintenance']){

			/*Do the DB maintenance routing for the DB_type selected */
			DB_Maintenance($db);
			//purge the audit trail if necessary
			if (isset($_SESSION['MonthsAuditTrail'])){
				 $sql = "DELETE FROM audittrail
						WHERE  transactiondate <= '" . Date('Y-m-d', mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail'])) . "'";
				$ErrMsg = _('There was a problem deleting expired audit-trail history');
				$result = DB_query($sql,$db);
			}
			$_SESSION['DB_Maintenance_LastRun'] = Date('Y-m-d');
		}
	}
}

/*Check to see if currency rates need to be updated */
if (isset($_SESSION['UpdateCurrencyRatesDaily'])){
	if ($_SESSION['UpdateCurrencyRatesDaily']!=0)  {
		if (DateDiff(Date($_SESSION['DefaultDateFormat']),
				ConvertSQLDate($_SESSION['UpdateCurrencyRatesDaily'])
				,'d')> 0){

			$CurrencyRates = GetECBCurrencyRates(); // gets rates from ECB see includes/MiscFunctions.php
			/*Loop around the defined currencies and get the rate from ECB */
			if ($CurrencyRates!=false) {
				$CurrenciesResult = DB_query('SELECT currabrev FROM currencies',$db);
				while ($CurrencyRow = DB_fetch_row($CurrenciesResult)){
					if ($CurrencyRow[0]!=$_SESSION['CompanyRecord']['currencydefault']){
						
						$UpdateCurrRateResult = DB_query('UPDATE currencies SET rate=' . GetCurrencyRate ($CurrencyRow[0],$CurrencyRates) . " WHERE currabrev='" . $CurrencyRow[0] . "'",$db);
					}
				}
				$_SESSION['UpdateCurrencyRatesDaily'] = Date('Y-m-d');
				
				
				
				$UpdateConfigResult = DB_query("UPDATE config SET confvalue = '" . Date('Y-m-d') . "' WHERE confname='UpdateCurrencyRatesDaily'",$db);
			}
		}
	}
}


If (isset($_POST['Theme']) && ($_SESSION['UsersRealName'] == $_POST['RealName'])) {
	$_SESSION['Theme'] = $_POST['Theme'];
	$theme = $_POST['Theme'];
} elseif (!isset($_SESSION['Theme'])) {
	$theme = $_SESSION['DefaultTheme'];
	$_SESSION['Theme'] = $_SESSION['DefaultTheme'];

} else {
	$theme = $_SESSION['Theme'];
}

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
			foreach($InCompanyDir as $logofilename)
				if (strncasecmp($logofilename,'logo.jpg',8) === 0 AND
				is_readable($dir . $logofilename) AND
					is_file($dir . $logofilename)) {
				$logo = $logofilename;
				break;
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



// Now check that the user as logged in has access to the page being called. The $PageSecurity
// value must be set in the script before header.inc is included. $SecurityGroups is an array of
// arrays defining access for each group of users. These definitions can be modified by a system admin under setup


if (!is_array($_SESSION['AllowedPageSecurityTokens']) AND !isset($AllowAnyone)) {
	$title = _('Account Error Report');
	include($PathPrefix . 'includes/header.inc');
	echo '<br /><br /><br />';
	prnMsg(_('Security settings have not been defined for your user account. Please advise your system administrator. It could also be that there is a session problem with your PHP web server'),'error');
	include($PathPrefix . 'includes/footer.inc');
	exit;
}

if (!isset($AllowAnyone)){
	if ((!in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PageSecurity))) {
		$title = _('Security Permissions Problem');
		include($PathPrefix . 'includes/header.inc');
		echo '<tr>
			<td class="menu_group_items">
				<table width="100%" class="table_index">
					<tr><td class="menu_group_item">';
		echo '<b><font style="size:+1; text-align:center;">' . _('The security settings on your account do not permit you to access this function') . '</font></b>';

		echo '</td>
		</tr>
		</table>
		</td>';

		include($PathPrefix . 'includes/footer.inc');
		exit;
	}
 }

if (in_array(15, $_SESSION['AllowedPageSecurityTokens'])) {
	$debug = 1;
} else {
	$debug = 0;
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
?>