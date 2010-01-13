<?php
/* $Id$*/
//  Validates user and sets up $_SESSION environment for API users.
function  LoginAPI($databasename, $user, $password) {
	global  $PathPrefix;		// For included files
	include('../config.php');
	// Include now for the error code values.
	include  '../includes/UserLogin.php';	/* Login checking and setup */
	$RetCode = array();		// Return result.
	if (!isset($_SESSION['DatabaseName']) OR $_SESSION['DatabaseName'] == '' ) {
		// Establish the database connection for this session.
		$_SESSION['DatabaseName'] = $databasename;
		/* Drag in the code to connect to the DB, and some other
		 * functions.  If the connection is established, the
		 * variable $db will be set as the DB connection id.
		 * NOTE:  This is needed here, as the api_session.inc file
		 * does NOT include this if there is no database name set.
		 */
		include('../includes/ConnectDB.inc');
		//  Need to ensure we have a connection.
		if (!isset($db)) {
		    $RetCode[0] = NoAuthorisation;
		    $RetCode[1] = UL_CONFIGERR;
		    return  $RetCode;
		}
		$_SESSION['db'] = $db;		// Set in above include
	}
	$rc = userLogin($user, $password, $_SESSION['db']);
	switch ($rc) {
	case  UL_OK:
		$RetCode[0] = 0;		// All is well
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
	    $rm[] = $ErrorDescription[$errnum];
	} else {
	    $rm[] = _('** Error Code Not Defined **');
	}
	// Add this array to returned array.
	$retmsg[] = $rm;
    }

    return  $retmsg;
}
?>