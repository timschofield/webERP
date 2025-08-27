<?php

/* Performs login checks and $_SESSION initialisation */

define('UL_OK',  0);		/* User verified, session initialised */
define('UL_NOTVALID', 1);	/* User/password do not agree */
define('UL_BLOCKED', 2);	/* Account locked, too many failed logins */
define('UL_CONFIGERR', 3);	/* Configuration error in webERP or server */
define('UL_SHOWLOGIN', 4);
define('UL_MAINTENANCE', 5);

/*
 *  Function to validate username and password, perform validity checks and initialise $_SESSION data.
 *  @returns int See define() statements above.
 */
function userLogin($Name, $Password, $SysAdminEmail = '') {

	global $PathPrefix;

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

		if (!isset($Name) or $Name == '') {
			// Show login screen
			$_SESSION['DatabaseName'] = '';
			return  UL_SHOWLOGIN;
		}

		/* The SQL to get the user info must use the * syntax because the field name could change between versions if the fields are specifed directly then the sql fails and the db upgrade will fail */
		$SQL = "SELECT *
				FROM www_users
				WHERE www_users.userid='" . $Name . "'";

		$ErrMsg = __('Could not retrieve user details on login because');
        $PasswordVerified = false;
		$Auth_Result = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($Auth_Result) > 0) {
			$MyRow = DB_fetch_array($Auth_Result);
			if (VerifyPass($Password, $MyRow['password'])) {
				$PasswordVerified = true;
			} elseif (isset($GLOBALS['CryptFunction'])) {
				/*if the password stored in the DB was compiled the old way,
				 * the previous comparison will fail,
				 * try again with the old hashing algorithm,
				 * then re-hash the password using the new algorithm.
				 * The next version should not have $CryptFunction any more for new installs.
				 */
				switch ($GLOBALS['CryptFunction']) {
					case 'sha1':
						if ($MyRow['password'] == sha1($Password)) {
							$PasswordVerified = true;
						}
						break;
					case 'md5':
						if ($MyRow['password'] == md5($Password)) {
							$PasswordVerified = true;
						}
						break;
					default:
						if ($MyRow['password'] == $Password) {
							$PasswordVerified = true;
						}
				}
				if ($PasswordVerified) {
					$SQL = "UPDATE www_users SET password = '" . CryptPass($Password) . "'"
							. " WHERE userid = '" . $Name . "';";
					DB_query($SQL);
				}
			}
		}

		// Populate session variables with database results
		if ($PasswordVerified) {

			if ($MyRow['blocked']==1) {
				//the account is blocked
				unset($_POST['UserNameEntryField']);
				unset($_POST['Password']);
				unset($_SESSION['DatabaseName']);

				return  UL_BLOCKED;
			}

			/*reset the attempts counter on successful login */
			$_SESSION['UserID'] = $MyRow['userid'];
			$_SESSION['AttemptsCounter'] = 0;
			$_SESSION['AccessLevel'] = $MyRow['fullaccess'];
			$_SESSION['CustomerID'] = $MyRow['customerid'];
			$_SESSION['UserBranch'] = $MyRow['branchcode'];
			$_SESSION['PageSize'] = $MyRow['pagesize'];
			$_SESSION['UserStockLocation'] = $MyRow['defaultlocation'];
			$_SESSION['UserEmail'] = $MyRow['email'];
			$_SESSION['Timeout'] = $MyRow['timeout'];
			$_SESSION['ModulesEnabled'] = explode(",", $MyRow['modulesallowed']);
			$_SESSION['UsersRealName'] = $MyRow['realname'];
			$_SESSION['Theme'] = $MyRow['theme'];
			$_SESSION['Language'] = $MyRow['language'];
			$_SESSION['SalesmanLogin'] = $MyRow['salesman'];
			$_SESSION['CanCreateTender'] = $MyRow['cancreatetender'];
			$_SESSION['AllowedDepartment'] = $MyRow['department'];
			$_SESSION['ShowDashboard'] = $MyRow['showdashboard'];
			$_SESSION['ShowPageHelp'] = $MyRow['showpagehelp'];
			$_SESSION['ShowFieldHelp'] = $MyRow['showfieldhelp'];
			$_SESSION['ScreenFontSize'] = $MyRow['fontsize'];

			switch ($_SESSION['ScreenFontSize']) {
				case 0:
					$_SESSION['FontSize'] = '0.667rem';
				break;
				case 1:
					$_SESSION['FontSize'] = '0.833rem';
				break;
				case 2:
					$_SESSION['FontSize'] = '1rem';
				break;
				default:
					$_SESSION['FontSize'] = '0.833rem';
			}

			if (isset($MyRow['pdflanguage'])) {
				$_SESSION['PDFLanguage'] = $MyRow['pdflanguage'];
			} else {
				$_SESSION['PDFLanguage'] = '0'; //default to latin western languages
			}

			if ($MyRow['displayrecordsmax'] > 0) {
				$_SESSION['DisplayRecordsMax'] = $MyRow['displayrecordsmax'];
			} else {
				$_SESSION['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax'];  // default comes from config.php
			}

			$SQL = "UPDATE www_users SET lastvisitdate='". date('Y-m-d H:i:s') ."'
							WHERE www_users.userid='" . $Name . "'";
			$Auth_Result = DB_query($SQL);
			/*get the security tokens that the user has access to */
			$SQL = "SELECT tokenid
					FROM securitygroups
					WHERE secroleid =  '" . $_SESSION['AccessLevel'] . "'";
			$Sec_Result = DB_query($SQL);
			$_SESSION['AllowedPageSecurityTokens'] = array();
			if (DB_num_rows($Sec_Result)==0) {
				return  UL_CONFIGERR;
			} else {
				$i = 0;
				$UserIsSysAdmin = false;
				while ($MyRow = DB_fetch_row($Sec_Result)) {
					if ($MyRow[0] == 15) {
						$UserIsSysAdmin = true;
					}
					$_SESSION['AllowedPageSecurityTokens'][$i] = $MyRow[0];
					$i++;
				}
			}

			/* User is logged in so get configuration parameters - save in session */
			include($PathPrefix . 'includes/GetConfig.php');

			if (isset($_SESSION['DB_Maintenance'])) {
				if ($_SESSION['DB_Maintenance']>0)  { //run the DB maintenance script
					if (DateDiff(Date($_SESSION['DefaultDateFormat']),
							ConvertSQLDate($_SESSION['DB_Maintenance_LastRun'])
							,'d')	>= 	$_SESSION['DB_Maintenance']) {

						/*Do the DB maintenance routing for the DB_type selected */
						DB_Maintenance();
						$_SESSION['DB_Maintenance_LastRun'] = Date('Y-m-d');

						/* Audit trail purge only runs if DB_Maintenance is enabled */
						if (isset($_SESSION['MonthsAuditTrail'])) {
							 $SQL = "DELETE FROM audittrail
									WHERE  transactiondate <= '" . Date('Y-m-d', mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail'])) . "'";
							$ErrMsg = __('There was a problem deleting expired audit-trail history');
							$Result = DB_query($SQL);
						}
					}
				}
			}

			/*Check to see if currency rates need to be updated */
			if (isset($_SESSION['UpdateCurrencyRatesDaily'])) {
				if ($_SESSION['UpdateCurrencyRatesDaily']!=0)  {
					/* Only run the update to currency rates if today is after the last update i.e. only runs once a day */
					if (DateDiff(Date($_SESSION['DefaultDateFormat']),
						ConvertSQLDate($_SESSION['UpdateCurrencyRatesDaily']),'d')> 0) {

						if ($_SESSION['ExchangeRateFeed']=='ECB') {
							$CurrencyRates = GetECBCurrencyRates(); // gets rates from ECB see includes/MiscFunctions.php
							/*Loop around the defined currencies and get the rate from ECB */
							if ($CurrencyRates!=false) {
								$CurrenciesResult = DB_query("SELECT currabrev FROM currencies");
								while ($CurrencyRow = DB_fetch_row($CurrenciesResult)){
									if ($CurrencyRow[0]!=$_SESSION['CompanyRecord']['currencydefault']){

										$Rate = GetCurrencyRate($CurrencyRow[0],$CurrencyRates);
										if ($Rate == '') {
											$Rate = 1;
										}
										$UpdateCurrRateResult = DB_query("UPDATE currencies SET rate='" . $Rate . "'
																			WHERE currabrev='" . $CurrencyRow[0] . "'");
									}
								}
							}
						} else {
							$CurrenciesResult = DB_query("SELECT currabrev FROM currencies");
							while ($CurrencyRow = DB_fetch_row($CurrenciesResult)){
								if ($CurrencyRow[0]!=$_SESSION['CompanyRecord']['currencydefault']){
									$UpdateCurrRateResult = DB_query("UPDATE currencies SET rate='" . google_currency_rate($CurrencyRow[0]) . "'
																		WHERE currabrev='" . $CurrencyRow[0] . "'");
								}
							}
						}
						$_SESSION['UpdateCurrencyRatesDaily'] = Date('Y-m-d');
						$UpdateConfigResult = DB_query("UPDATE config SET confvalue = CURRENT_DATE WHERE confname='UpdateCurrencyRatesDaily'");
					}
				}
			}

			/* Set the logo if not yet set.
			 * will be done only once per session and each time
			 * we are not in session (i.e. before login)
			 */
			if (empty($_SESSION['LogoFile'])) {
				/* find a logo in companies/CompanyDir */
				if (file_exists($PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/logo.png')) {
					$_SESSION['LogoFile'] = 'companies/' .  $_SESSION['DatabaseName'] . '/logo.png';
				} elseif (file_exists($PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/logo.jpeg')) {
					$_SESSION['LogoFile'] = 'companies/' .  $_SESSION['DatabaseName'] . '/logo.jpeg';
				} elseif (file_exists($PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/logo.jpg')) {
					$_SESSION['LogoFile'] = 'companies/' .  $_SESSION['DatabaseName'] . '/logo.jpg';
				} elseif (file_exists($PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/logo.gif')) {
					$_SESSION['LogoFile'] = 'companies/' .  $_SESSION['DatabaseName'] . '/logo.gif';
				}
			}

			if (!isset($_SESSION['DB_Maintenance'])) {
				return  UL_CONFIGERR;
			} else {
				if ($_SESSION['DB_Maintenance']==-1 AND !in_array(15, $_SESSION['AllowedPageSecurityTokens'])) {
					// the configuration setting has been set to -1 ==> Allow SysAdmin Access Only
					// the user is NOT a SysAdmin
					return  UL_MAINTENANCE;
				}
			}
		} else {
			// Incorrect password

			// after 5 login attempts, show failed login screen
			if (!isset($_SESSION['AttemptsCounter'])) {
				$_SESSION['AttemptsCounter'] = 0;
			} elseif ($_SESSION['AttemptsCounter'] >= 5 AND isset($Name)) {
				/*User blocked from future accesses until sysadmin releases */
				$SQL = "UPDATE www_users
							SET blocked=1
							WHERE www_users.userid='" . $Name . "'";
				$Auth_Result = DB_query($SQL);

				if ($SysAdminEmail != ''){
					$EmailSubject = __('User access blocked'). ' ' . $Name ;
					$EmailText =  __('User ID') . ' ' . $Name . ' - ' . $Password . ' - ' . __('has been blocked access at') . ' ' .
								Date('Y-m-d H:i:s') . ' ' . __('from IP') . ' ' . $_SERVER["REMOTE_ADDR"] . ' ' . __('due to too many failed attempts.');
					SendEmailFromWebERP($SysAdminEmail,
										$SysAdminEmail,
										$EmailSubject,
										$EmailText);
				}

				unset($_POST['UserNameEntryField']);
				unset($_POST['Password']);
				unset($_SESSION['DatabaseName']);

				return  UL_BLOCKED;
			}

			return  UL_NOTVALID;
		} // End of incorrect password
	} // End of userid/password check

	// Run with debugging messages for the system administrator(s) but not anyone else

	return   UL_OK;		    /* All is well */
}
