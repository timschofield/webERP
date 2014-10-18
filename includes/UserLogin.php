<?php

/*  Performs login checks and $_SESSION initialisation */
/* $Id: UserLogin.php 6547 2014-01-24 08:52:53Z daintree $*/

define('UL_OK',  0);		/* User verified, session initialised */
define('UL_NOTVALID', 1);	/* User/password do not agree */
define('UL_BLOCKED', 2);	/* Account locked, too many failed logins */
define('UL_CONFIGERR', 3);	/* Configuration error in webERP or server */
define('UL_SHOWLOGIN', 4);
define('UL_MAINTENANCE', 5);

/*	UserLogin
 *  Function to validate user name,  perform validity checks and initialise
 *  $_SESSION data.
 *  Returns:
 *	See define() statements above.
 */

function userLogin($Name, $Password, $SysAdminEmail = '', $db) {

	global $debug;

	if (!isset($_SESSION['AccessLevel']) OR $_SESSION['AccessLevel'] == '' OR
		(isset($Name) AND $Name != '')) {
	/* if not logged in */
		$_SESSION['AccessLevel'] = '';
		$_SESSION['CustomerID'] = '';
		$_SESSION['UserBranch'] = '';
		$_SESSION['SalesmanLogin'] = '';
		$_SESSION['Module'] = '';
		$_SESSION['PageSize'] = '';
		$_SESSION['UserStockLocation'] = '';
		$_SESSION['AttemptsCounter']++;

		// Show login screen
		if (!isset($Name) or $Name == '') {
			$_SESSION['DatabaseName'] = '';
		    $_SESSION['CompanyName'] = '';
			return  UL_SHOWLOGIN;
		}
		/* The SQL to get the user info must use the * syntax because the field name could change between versions if the fields are specifed directly then the sql fails and the db upgrade will fail */
		$sql = "SELECT *
				FROM www_users
				WHERE www_users.userid='" . $Name . "'
				AND (www_users.password='" . CryptPass($Password) . "'
				OR  www_users.password='" . $Password . "')";
		$ErrMsg = _('Could not retrieve user details on login because');
		$debug =1;
		$Auth_Result = DB_query($sql, $db,$ErrMsg);
		// Populate session variables with data base results
		if (DB_num_rows($Auth_Result) > 0) {
			$myrow = DB_fetch_array($Auth_Result);
			if ($myrow['blocked']==1){
			//the account is blocked
				return  UL_BLOCKED;
			}
			/*reset the attempts counter on successful login */
			$_SESSION['UserID'] = $myrow['userid'];
			$_SESSION['AttemptsCounter'] = 0;
			$_SESSION['AccessLevel'] = $myrow['fullaccess'];
			$_SESSION['CustomerID'] = $myrow['customerid'];
			$_SESSION['UserBranch'] = $myrow['branchcode'];
			$_SESSION['DefaultPageSize'] = $myrow['pagesize'];
			$_SESSION['UserStockLocation'] = $myrow['defaultlocation'];
			$_SESSION['UserEmail'] = $myrow['email'];
			$_SESSION['ModulesEnabled'] = explode(",", $myrow['modulesallowed']);
			$_SESSION['UsersRealName'] = $myrow['realname'];
			$_SESSION['Theme'] = $myrow['theme'];
			$_SESSION['Language'] = $myrow['language'];
			$_SESSION['SalesmanLogin'] = $myrow['salesman'];
			$_SESSION['CanCreateTender'] = $myrow['cancreatetender'];
			$_SESSION['AllowedDepartment'] = $myrow['department'];

			if (isset($myrow['pdflanguage'])) {
				$_SESSION['PDFLanguage'] = $myrow['pdflanguage'];
			} else {
				$_SESSION['PDFLanguage'] = '0'; //default to latin western languages
			}

			if ($myrow['displayrecordsmax'] > 0) {
				$_SESSION['DisplayRecordsMax'] = $myrow['displayrecordsmax'];
			} else {
				$_SESSION['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax'];  // default comes from config.php
			}

			$sql = "UPDATE www_users SET lastvisitdate='". date('Y-m-d H:i:s') ."'
							WHERE www_users.userid='" . $Name . "'";
			$Auth_Result = DB_query($sql, $db);
			/*get the security tokens that the user has access to */
			$sql = "SELECT tokenid
					FROM securitygroups
					WHERE secroleid =  '" . $_SESSION['AccessLevel'] . "'";
			$Sec_Result = DB_query($sql, $db);
			$_SESSION['AllowedPageSecurityTokens'] = array();
			if (DB_num_rows($Sec_Result)==0){
				return  UL_CONFIGERR;
			} else {
				$i=0;
				$UserIsSysAdmin = FALSE;
				while ($myrow = DB_fetch_row($Sec_Result)){
					if ($myrow[0] == 15){
						$UserIsSysAdmin = TRUE;
					}
					$_SESSION['AllowedPageSecurityTokens'][$i] = $myrow[0];
					$i++;
				}
			}
			// check if only maintenance users can access webERP
			$sql = "SELECT confvalue FROM config WHERE confname = 'DB_Maintenance'";
			$Maintenance_Result = DB_query($sql, $db);
			if (DB_num_rows($Maintenance_Result)==0){
				return  UL_CONFIGERR;
			} else {
				$myMaintenanceRow = DB_fetch_row($Maintenance_Result);
				if (($myMaintenanceRow[0] == -1) AND ($UserIsSysAdmin == FALSE)){
					// the configuration setting has been set to -1 ==> Allow SysAdmin Access Only
					// the user is NOT a SysAdmin
					return  UL_MAINTENANCE;
				}
			}
		} else {     // Incorrect password
			// 5 login attempts, show failed login screen
			if (!isset($_SESSION['AttemptsCounter'])) {
				$_SESSION['AttemptsCounter'] = 0;
			} elseif ($_SESSION['AttemptsCounter'] >= 5 AND isset($Name)) {
				/*User blocked from future accesses until sysadmin releases */
				$sql = "UPDATE www_users
							SET blocked=1
							WHERE www_users.userid='" . $Name . "'";
				$Auth_Result = DB_query($sql, $db);

				if ($SysAdminEmail != ''){
					$EmailSubject = _('User access blocked'). ' ' . $Name ;
					$EmailText =  _('User ID') . ' ' . $Name . ' - ' . $Password . ' - ' . _('has been blocked access at') . ' ' .
								Date('Y-m-d H:i:s') . ' ' . _('from IP') . ' ' . $_SERVER["REMOTE_ADDR"] . ' ' . _('due to too many failed attempts.');
					if($_SESSION['SmtpSetting']==0){
							mail($SysAdminEmail,$EmailSubject,$EmailText);

					}else{
							include('includes/htmlMimeMail.php');
							$mail = new htmlMimeMail();
							$mail->setSubject($EmailSubject);
							$mail->setText($EmailText);
							$result = SendmailBySmtp($mail,array($SysAdminEmail));
					}

				}

				return  UL_BLOCKED;
			}
			return  UL_NOTVALID;
		}
	}		// End of userid/password check
	// Run with debugging messages for the system administrator(s) but not anyone else

	return   UL_OK;		    /* All is well */
}

?>
