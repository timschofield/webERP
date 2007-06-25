<?php
/* $Revision: 1.52 $ */

if (!isset($PathPrefix)) {
	$PathPrefix='';
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


if (!isset($AllowAnyone)){ /* only do security checks if AllowAnyone is not true */

	if (!isset($_SESSION['AccessLevel']) OR $_SESSION['AccessLevel'] == '' OR
		(isset($_POST['UserNameEntryField']) AND $_POST['UserNameEntryField'] != '')) {

	/* if not logged in */

		$_SESSION['AccessLevel'] = '';
		$_SESSION['CustomerID'] = '';
		$_SESSION['UserBranch'] = '';
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
				www_users.language
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
			
			if ($myrow[10] > 0) {
				$_SESSION['DisplayRecordsMax'] = $myrow[10];
			} else {
				$_SESSION['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax'];  // default comes from config.php
			}

			$sql = "UPDATE www_users SET lastvisitdate='". date("Y-m-d H:i:s") ."'
					WHERE www_users.userid='" . DB_escape_string($_POST['UserNameEntryField']) . "'
					AND www_users.password='" . CryptPass(DB_escape_string($_POST['Password'])) ."'";
			$Auth_Result = DB_query($sql, $db);

			/*get the security tokens that the user has access to */
			$sql = 'SELECT tokenid FROM securitygroups
					WHERE secroleid =  ' . $_SESSION['AccessLevel'];
			$Sec_Result = DB_query($sql, $db);

			$_SESSION['AllowedPageSecurityTokens'] = array();
			if (DB_num_rows($Sec_Result)==0){
				$title = _('Account Error Report');
				include($PathPrefix . 'includes/header.inc');
				echo '<BR><BR><BR>';
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

			echo "<META HTTP-EQUIV='refresh' CONTENT='0; URL=" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";
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
			$demo_text = '<FONT SIZE="3" COLOR="red"><b>' .  _('incorrect password') . '</B></FONT><BR><B>' . _('The user/password combination') . '<BR>' . _('is not a valid user of the system') . '</B>';
			die(include($PathPrefix . 'includes/Login.php'));
		}
	}		// End of userid/password check
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
			$_SESSION['DB_Maintenance_LastRun'] = Date('Y-m-d');
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

if ($_SESSION['HTTPS_Only']==1){
	if ($_SERVER['HTTPS']!='on'){
		prnMsg(_('webERP is configured to allow only secure socket connections. Pages must be called with https://') . ' .....','error');
		exit;
	}
}


// Run with debugging messages for the system administrator(s) but not anyone else
if (in_array(15, $_SESSION['AllowedPageSecurityTokens'])) {
	$debug = 1;
} else {
	$debug = 0;
}

// Now check that the user as logged in has access to the page being called. The $PageSecurity
// value must be set in the script before header.inc is included. $SecurityGroups is an array of
// arrays defining access for each group of users. These definitions can be modified by a system admin under setup


if (!is_array($_SESSION['AllowedPageSecurityTokens']) AND !isset($AllowAnyone)) {
	$title = _('Account Error Report');
	include($PathPrefix . 'includes/header.inc');
	echo '<BR><BR><BR>';
	prnMsg(_('Security settings have not been defined for your user account. Please advise your system administrator. It could also be that there is a session problem with your PHP web server'),'error');
	include($PathPrefix . 'includes/footer.inc');
	exit;
}

if (!isset($AllowAnyone)){
	if ((!in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PageSecurity))) {
		$title = _('Security Permissions Problem');
		include($PathPrefix . 'includes/header.inc');
		echo '<TR>
			<TD CLASS="menu_group_items">
				<TABLE WIDTH="100%" CLASS="table_index">
					<TR><TD CLASS="menu_group_item">';
		echo '<B><FONT SIZE="+1"><CENTER>' . _('The security settings on your account do not permit you to access this function') . '</FONT></B></CENTER>';

		echo '</TD>
		</TR>
		</TABLE>
		</TD>';

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