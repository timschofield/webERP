<?php

/*  Performs login checks and $_SESSION initialisation */
/* $Id$*/

define('UL_OK',  0);		/* User verified, session initialised */
define('UL_NOTVALID', 1);	/* User/password do not agree */
define('UL_BLOCKED', 2);	/* Account locked, too many failed logins */
define('UL_CONFIGERR', 3);	/* Configuration error in webERP or server */
define('UL_SHOWLOGIN', 4);	/* Can this happen? */
//  Following not in use at 18 Nov 09.
define('UL_MAINTENANCE', 5);	/* Maintenance mode - no user logins */

/*	UserLogin
 *  Function to validate user name,  perform validity checks and initialise
 *  $_SESSION data.
 *  Returns:
 *	See define() statements above.
 */

function userLogin($Name, $Password, $db) {
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
			return  UL_SHOWLOGIN;
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
			WHERE www_users.userid='" . $Name . "'
			AND (www_users.password='" . CryptPass($Password) . "'
			OR  www_users.password='" . $Password . "')";
		$Auth_Result = DB_query($sql, $db);
		// Populate session variables with data base results
		if (DB_num_rows($Auth_Result) > 0) {
			$myrow = DB_fetch_row($Auth_Result);
			if ($myrow[7]==1){
			//the account is blocked
				return  UL_BLOCKED;
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
//			$_SESSION['UserID'] = $myrow[11];
			$_SESSION['Language'] = $myrow[12];
			$_SESSION['SalesmanLogin'] = $myrow[13];
			if ($myrow[10] > 0) {
				$_SESSION['DisplayRecordsMax'] = $myrow[10];
			} else {
				$_SESSION['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax'];  // default comes from config.php
			}
			$sql = "UPDATE www_users SET lastvisitdate='". date("Y-m-d H:i:s") ."'
					WHERE www_users.userid='" . $Name . "'";
			$Auth_Result = DB_query($sql, $db);
			/*get the security tokens that the user has access to */
			$sql = 'SELECT tokenid FROM securitygroups
					WHERE secroleid =  ' . $_SESSION['AccessLevel'];
			$Sec_Result = DB_query($sql, $db);
			$_SESSION['AllowedPageSecurityTokens'] = array();
			if (DB_num_rows($Sec_Result)==0){
				return  UL_CONFIGERR;
			} else {
				$i=0;
				while ($myrow = DB_fetch_row($Sec_Result)){
					$_SESSION['AllowedPageSecurityTokens'][$i] = $myrow[0];
					$i++;
				}
			}
			//  Temporary shift - disable log messages.
			$_SESSION['UserID'] = $myrow[11];
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
				return  UL_BLOCKED;
			}
			return  UL_NOTVALID;
		}
	}		// End of userid/password check
	// Run with debugging messages for the system administrator(s) but not anyone else
	if (in_array(15, $_SESSION['AllowedPageSecurityTokens'])) {
		$debug = 1;
	} else {
		$debug = 0;
	}
	return   UL_OK;		    /* All is well */
}

?>