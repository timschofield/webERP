<?php
/* $Id: api_session.inc 4866 2012-02-04 06:13:15Z daintree $*/

if (!isset($PathPrefix)) {
	$PathPrefix='';
}

include($PathPrefix . 'config.php');

if (isset($SessionSavePath)){
	session_save_path($SessionSavePath);
}

ini_set('session.gc_Maxlifetime',$SessionLifeTime);
ini_set('max_execution_time',$MaximumExecutionTime);

session_name('webERPapi');
session_start();

include($PathPrefix . 'includes/LanguageSetup.php');
//  Establish a DB connection, if possible.   NOTE that this connection
//  may not have the same 'value' as any previous connection, so
//  save the new one in the session variable.
if (isset($_SESSION['DatabaseName']) AND $_SESSION['DatabaseName'] != '' ) {
	include($PathPrefix . 'includes/ConnectDB.inc');
	$_SESSION['db'] = $db;
}
include($PathPrefix . 'includes/DateFunctions.inc');

// Un comment to turn off attempts counter
//$_SESSION['AttemptsCounter'] = 0;

if (!isset($_SESSION['AttemptsCounter'])){
	$_SESSION['AttemptsCounter'] = 0;
}

if (isset($_SESSION['HTTPS_Only']) AND $_SESSION['HTTPS_Only']==1){
	if ($_SERVER['HTTPS']!='on'){
		prnMsg(_('webERP is configured to allow only secure socket connections. Pages must be called with https://') . ' .....','error');
		exit;
	}
}

// Now check that the user as logged in has access to the page being called. The $PageSecurity
// value must be set in the script before header.inc is included. $SecurityGroups is an array of
// arrays defining access for each group of users. These definitions can be modified by a system admin under setup


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

// API wrapper for DB issues - no HTML output, AND remember any error message
function api_DB_query( $sql, $db, $EMsg= '', $DMsg= '', $Transaction='', $TrapErrors=false )
{
    //  Basically we have disabled the error reporting from the standard
    //  query function,  and will remember any error message in the session
    //  data.

    $result = DB_query($sql, $db, $EMsg, $DMsg, $Transaction, $TrapErrors);
    if (DB_error_no($db) != 0) {
		$_SESSION['db_err_msg'] = "SQL: " . $sql . "\nDB error message: " . DB_error_msg($db) . "\n";
    } else {
		$_SESSION['db_err_msg'] = '';
	}

    return  $result;
}
?>