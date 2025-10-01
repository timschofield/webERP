<?php

/**
 * Note: includes api_session.php, to allow database connection, and access to miscfunctions and datefunctions.
 *
 * @todo refactor:
 *       - make the API independent of sessions
 *       - simplify the api bootstrap chain
 */

// FOLLOWING IS ALWAYS REQUIRED

$api_DatabaseName = 'weberpdemo';
if (isset($_SESSION['DatabaseName'])) {
	$api_DatabaseName = $_SESSION['DatabaseName'];
}

$AllowAnyone = true;
$PathPrefix = __DIR__ . '/../../';
include(__DIR__ . '/api_session.php');

include(__DIR__ . '/api_errorcodes.php');
/* Include SQL_CommonFunctions.php, to use GetNextTransNo(). */
include($PathPrefix . 'includes/SQL_CommonFunctions.php');
/* Required for creating invoices/credits */
include($PathPrefix . 'includes/GetSalesTransGLCodes.php');
include($PathPrefix . 'includes/Z_POSDataCreation.php');

/** Get weberp authentication, and return a valid database connection */
function db($user, $password) {

	if (!isset($_SESSION['AccessLevel']) OR
			   $_SESSION['AccessLevel'] == '') {
		//  Login to default database = old clients.
		if ($user != '' AND $password != '') {
			global  $api_DatabaseName;
			$rc = LoginAPI ($api_DatabaseName, $user, $password);
			if ($rc[0] == UL_OK ) {
				return $_SESSION['db'];
			}
		}
		return NoAuthorisation;
	} else {
		return $_SESSION['db'];
	}
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
