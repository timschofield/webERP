<?php

/**
 * Note: includes api_session.php, to allow database connection, and access to miscfunctions and datefunctions.
 *
 * @todo refactor:
 *       - make the API independent of sessions. Ideally, api_session.php would be included by the script which includes
 *         this one. We leave its inclusion here for BC - but make it possible to avoid its inclusion
 *       - simplify the api bootstrap chain
 *       - get rid of $_SESSION['db'], use only the global var $db
 */

$AllowAnyone = true;
$PathPrefix = __DIR__ . '/../../';
if (!isset($WebErpSessionType)) {
	include(__DIR__ . '/api_session.php');
} else {
	if ($WebErpSessionType == 'web') {
		$_SESSION['db'] = $db;
	}
}

$api_DatabaseName = $_SESSION['DatabaseName'] ?? $DefaultDatabase;

include(__DIR__ . '/api_errorcodes.php');
/* Include SQL_CommonFunctions.php, to use GetNextTransNo(). */
include($PathPrefix . 'includes/SQL_CommonFunctions.php');
/* Required for creating invoices/credits */
include($PathPrefix . 'includes/GetSalesTransGLCodes.php');
include($PathPrefix . 'includes/Z_POSDataCreation.php');

/**
 * Get weberp authentication, and return a valid database connection, or 1.
 * Note: atm none of the code calling this function does use the returned db connection - it only checks for failure...
 * @return int|resource
 */
function db($user, $password) {

	if (!isset($_SESSION['AccessLevel']) OR $_SESSION['AccessLevel'] == '' OR !isset($_SESSION['db'])) {
		// Login to default database = old clients.
		if ($user != '' AND $password != '') {
			global $api_DatabaseName;
			$rc = LoginAPI($api_DatabaseName, $user, $password);
			if ($rc[0] == UL_OK ) {
				return $_SESSION['db'];
			}
		}
		/// @todo why not return null/false ?
		return NoAuthorisation;
	} else {
		return $_SESSION['db'];
	}
}

// API wrapper for DB issues - no HTML output, AND remember any error message
function api_DB_query( $SQL, $EMsg= '', $DMsg= '', $Transaction='', $TrapErrors=false )
{
    //  Basically we have disabled the error reporting from the standard
    //  query function,  and will remember any error message in the session
    //  data.

    $Result = DB_query($SQL, $EMsg, $DMsg, $Transaction, $TrapErrors);
    if (DB_error_no() != 0) {
        $_SESSION['db_err_msg'] = "SQL: " . $SQL . "\nDB error message: " . DB_error_msg() . "\n";
    } else {
        $_SESSION['db_err_msg'] = '';
    }

    return  $Result;
}

include(__DIR__ . '/api_login.php');
include(__DIR__ . '/api_customers.php');
include(__DIR__ . '/api_branches.php');
include(__DIR__ . '/api_currencies.php');
include(__DIR__ . '/api_locations.php');
include(__DIR__ . '/api_shippers.php');
include(__DIR__ . '/api_salestypes.php');
include(__DIR__ . '/api_salesareas.php');
include(__DIR__ . '/api_salesman.php');
include(__DIR__ . '/api_taxgroups.php');
include(__DIR__ . '/api_holdreasons.php');
include(__DIR__ . '/api_paymentterms.php');
include(__DIR__ . '/api_customertypes.php');
include(__DIR__ . '/api_stock.php');
include(__DIR__ . '/api_debtortransactions.php');
include(__DIR__ . '/api_salesorders.php');
include(__DIR__ . '/api_glaccounts.php');
include(__DIR__ . '/api_glsections.php');
include(__DIR__ . '/api_glgroups.php');
include(__DIR__ . '/api_stockcategories.php');
include(__DIR__ . '/api_suppliers.php');
include(__DIR__ . '/api_purchdata.php');
include(__DIR__ . '/api_workorders.php');
include(__DIR__ . '/api_webERPsettings.php');
