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

	if (!isset($_SESSION['AccessLevel']) OR $_SESSION['AccessLevel'] == '' OR
		(isset($_POST['UserNameEntryField']) AND $_POST['UserNameEntryField'] != '')) {

	/* if not logged in */

		$_SESSION['AccessLevel'] = '';
		$_SESSION['CustomerID'] = '';
		$_SESSION['UserBranch'] = '';
		$_SESSION['SalesmanLogin'] = '';
		$_SESSION['Module'] = '';
		$_SESSION['PageSize'] = '';
		$_SESSION['UserStockLocation'] = '';
		$_SESSION['AttemptsCounter']++;


		$theme = 'silverwolf';

		// Show login screen
		if (!isset($_POST['UserNameEntryField']) or $_POST['UserNameEntryField'] == '') {
			include($PathPrefix . 'includes/Login.php');
			exit;
		}


		$sql = "SELECT www_users.fullaccess,
				www_users.customerid,
				www_users.lastvisitdate,
				www_users.pagesize,
				www_users.defaultlocation,
				www_users.branchcode,
				www_users.modulesallowed,
				www_users.blocked,
				www_users.realname,
				www_users.theme,
				www_users.displayrecordsmax,
				www_users.userid,
				www_users.language,
				www_users.salesman
			FROM www_users
			WHERE www_users.userid='" . DB_escape_string($_POST['UserNameEntryField']) . "'
			AND (www_users.password='" . CryptPass(DB_escape_string($_POST['Password'])) . "'
			OR  www_users.password='" . DB_escape_string($_POST['Password']) . "')";
		$Auth_Result = DB_query($sql, $db);

		// Populate session variables with data base results
		if (DB_num_rows($Auth_Result) > 0) {

			$myrow = DB_fetch_row($Auth_Result);
			if ($myrow[7]==1){
			//the account is blocked
				die(include($PathPrefix . 'includes/FailedLogin.php'));
			}
			/*reset the attempts counter on successful login */
			$_SESSION['AttemptsCounter'] = 0;
			$_SESSION['AccessLevel'] = $myrow[0];
			$_SESSION['CustomerID'] = $myrow[1];
			$_SESSION['UserBranch'] = $myrow[5];
			$_SESSION['DefaultPageSize'] = $myrow[3];
			$_SESSION['UserStockLocation'] = $myrow[4];
			$_SESSION['ModulesEnabled'] = explode(",", $myrow[6]);
			$_SESSION['UsersRealName'] = $myrow[8];
			$_SESSION['Theme'] = $myrow[9];
			$_SESSION['UserID'] = $myrow[11];
			$_SESSION['Language'] = $myrow[12];
			$_SESSION['SalesmanLogin'] = $myrow[13];
			if ($myrow[10] > 0) {
				$_SESSION['DisplayRecordsMax'] = $myrow[10];
			} else {
				$_SESSION['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax'];  // default comes from config.php
			}

			$sql = "UPDATE www_users SET lastvisitdate='". date("Y-m-d H:i:s") ."'
					WHERE www_users.userid='" . DB_escape_string($_POST['UserNameEntryField']) . "'";
			$Auth_Result = DB_query($sql, $db);

			/*get the security tokens that the user has access to */
			$sql = 'SELECT tokenid FROM securitygroups
					WHERE secroleid =  ' . $_SESSION['AccessLevel'];
			$Sec_Result = DB_query($sql, $db);

			$_SESSION['AllowedPageSecurityTokens'] = array();
			if (DB_num_rows($Sec_Result)==0){
				$title = _('Account Error Report');
				include($PathPrefix . 'includes/header.inc');
				echo '<br /><br /><br />';
				prnMsg(_('Your user role does not have any access defined for webERP. There is an error in the security setup for this user account'),'error');
				include($PathPrefix . 'includes/footer.inc');
				exit;
			} else {
				$i=0;
				while ($myrow = DB_fetch_row($Sec_Result)){
					$_SESSION['AllowedPageSecurityTokens'][$i] = $myrow[0];
					$i++;
				}
			}


			echo '<meta http-equiv="refresh" content="0" url="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
			exit;
		} else {     // Incorrect password
			// 5 login attempts, show failed login screen
			if (!isset($_SESSION['AttemptsCounter'])) {
				$_SESSION['AttemptsCounter'] = 0;
			} elseif ($_SESSION['AttemptsCounter'] >= 5 AND isset($_POST['UserNameEntryField'])) {
				/*User blocked from future accesses until sysadmin releases */
				$sql = "UPDATE www_users
						SET blocked=1
					WHERE www_users.userid='" . $_POST['UserNameEntryField'] . "'";
				$Auth_Result = DB_query($sql, $db);
				die(include($PathPrefix . 'includes/FailedLogin.php'));
			}
			$demo_text = '<font size="3" color="red"><b>' .  _('incorrect password') . '</b></font><br /><b>' . _('The user/password combination') . '<br />' . _('is not a valid user of the system') . '</b>';
			die(include($PathPrefix . 'includes/Login.php'));
		}
	}		// End of userid/password check
	// Run with debugging messages for the system administrator(s) but not anyone else

	if (in_array(15, $_SESSION['AllowedPageSecurityTokens'])) {
		$debug = 1;
	} else {
		$debug = 0;
	}

} /* only do security checks if AllowAnyone is not true */

/*User is logged in so get configuration parameters  - save in session*/
include($PathPrefix . 'includes/GetConfig.php');

if(isset($_SESSION['DB_Maintenance'])){
	if ($_SESSION['DB_Maintenance']!=0)  {
		if (DateDiff(Date($_SESSION['DefaultDateFormat']),
				ConvertSQLDate($_SESSION['DB_Maintenance_LastRun'])
				,'d')	> 	$_SESSION['DB_Maintenance']){

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
			$CurrenciesResult = DB_query('SELECT currabrev FROM currencies',$db);
			while ($CurrencyRow = DB_fetch_row($CurrenciesResult)){
				if ($CurrencyRow[0]!=$_SESSION['CompanyRecord']['currencydefault']){
					$UpdateCurrRateResult = DB_query('UPDATE currencies SET
											rate=' . GetCurrencyRate ($CurrencyRow[0],$CurrencyRates) . "
											WHERE currabrev='" . $CurrencyRow[0] . "'",$db);
				}
			}
			$_SESSION['UpdateCurrencyRatesDaily'] = Date('Y-m-d');
			$UpdateConfigResult = DB_query("UPDATE config SET confvalue = '" . Date('Y-m-d') . "' WHERE confname='UpdateCurrencyRatesDaily'",$db);
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