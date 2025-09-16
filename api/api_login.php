<?php

//  Validates user and sets up $_SESSION environment for API users.
function  LoginAPI($databasename, $user, $password) {
	global $PathPrefix, $SysAdminEmail;

	//include($PathPrefix . 'config.php');

	// Include now for the error code values.
	include($PathPrefix . 'includes/LoginFunctions.php');	/* Login checking and setup */

	$RetCode = array();		// Return result.
	if (!isset($_SESSION['DatabaseName']) || $_SESSION['DatabaseName'] == '' || $_SESSION['DatabaseName'] != $databasename) {
		// Establish the database connection for this session.
		$_SESSION['DatabaseName'] = $databasename;
		/* Drag in the code to connect to the DB, and some other
		 * functions.  If the connection is established, the
		 * variable $db will be set as the DB connection id.
		 * NOTE:  This is needed here, as the api_session.php file
		 * does NOT include this if there is no database name set.
		 */
		include('../includes/ConnectDB.php');
		//  Need to ensure we have a connection.
		if (!isset($db)) {
			$RetCode[0] = NoAuthorisation;
		    $RetCode[1] = UL_CONFIGERR;
			return  $RetCode;
		}
		$_SESSION['db'] = $db;		// Set in above include
	}
	$rc = userLogin($user, $password, $SysAdminEmail);
	switch ($rc) {
	case  UL_OK:
		$RetCode[0] = 0;		// All is well
		DoSetup();	    // Additional setting up
		break;
	case  UL_NOTVALID:
	case  UL_BLOCKED:
	case  UL_CONFIGERR:
	case  UL_SHOWLOGIN:
	//  Following not in use at 18 Nov 09.
	case  UL_MAINTENANCE:
		/*  Just return an error for now */
		$RetCode[0] = NoAuthorisation;
		$RetCode[1] = $rc;
		break;
	}
	return  $RetCode;
}


//  Logout function destroys the session data, and that's about it.
function  LogoutAPI() {

    //  Is this user logged in?
    if (isset ($_SESSION['db']) ) {
		// Cleanup is about all there is to do.
		session_unset();
		session_destroy();
		$RetCode = 0;
    } else {
		$RetCode = NoAuthorisation;
    }

    return $RetCode;
}

/*
 *  Function to return an error message (hopefully in the user's language)
 *  from the supplied error codes.  This is not really related to login/out,
 *  but since it does NOT require being logged in, this seems like a
 *  reasonable place to put it.
 */
function GetAPIErrorMessages( $errcodes )
{
    global  $ErrorDescription;
    $retmsg = array();

    foreach ($errcodes as $errnum) {
	$rm = array ($errnum );
	if (isset ($ErrorDescription[$errnum]) ) {
		if ($errnum == DatabaseUpdateFailed &&
			isset ($_SESSION['db_err_msg']) &&
			mb_strlen ($_SESSION['db_err_msg']) > 0 )
		$rm[] = $ErrorDescription[$errnum] . ":\n" . $_SESSION['db_err_msg'];
	    else
		$rm[] = $ErrorDescription[$errnum];
	} else {
		$rm[] = __('** Error Code Not Defined **');
	}
	// Add this array to returned array.
	$retmsg[] = $rm;
    }

    return  $retmsg;
}

/*
 *  Some initialisation cannot be done until the user is logged in.  This
 *  function should be called when a successful login occurs.
 */
function DoSetup()
{
    global  $PathPrefix;
    if (isset($_SESSION['db']) AND $_SESSION['db'] != '' )
        include($PathPrefix . 'includes/GetConfig.php');

    $db = $_SESSION['db'];	    // Used a bit in the following.
    if(isset($_SESSION['DB_Maintenance'])){
		if ($_SESSION['DB_Maintenance']>0)  {
		    if (DateDiff(Date($_SESSION['DefaultDateFormat']),
				 	ConvertSQLDate($_SESSION['DB_Maintenance_LastRun'])
					,'d') > $_SESSION['DB_Maintenance']){

			    /*Do the DB maintenance routing for the DB_type selected */
				DB_Maintenance();
			    //purge the audit trail if necessary
				if (isset($_SESSION['MonthsAuditTrail'])){
				     $SQL = "DELETE FROM audittrail
						    WHERE  transactiondate <= '" . Date('Y-m-d', mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail'])) . "'";
					$ErrMsg = __('There was a problem deleting expired audit-trail history');
				    $Result = DB_query($SQL, $ErrMsg);
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
			    $CurrenciesResult = DB_query('SELECT currabrev FROM currencies');
				while ($CurrencyRow = DB_fetch_row($CurrenciesResult)){
				    if ($CurrencyRow[0]!=$_SESSION['CompanyRecord']['currencydefault']){
						$UpdateCurrRateResult = DB_query("UPDATE currencies SET
											    rate='" . GetCurrencyRate ($CurrencyRow[0],$CurrencyRates) . "'
											    WHERE currabrev='" . $CurrencyRow[0] . "'");
					}
			    }
				$_SESSION['UpdateCurrencyRatesDaily'] = Date('Y-m-d');
			    $UpdateConfigResult = DB_query("UPDATE config SET confvalue = CURRENT_DATE WHERE confname='UpdateCurrencyRatesDaily'");
			}
	    }
    }
}
