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
$PathPrefix = __DIR__ . '/../';
include('api_session.php');

include('api_errorcodes.php');
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

include('api_login.php');
include('api_customers.php');
include('api_branches.php');
include('api_currencies.php');
include('api_locations.php');
include('api_shippers.php');
include('api_salestypes.php');
include('api_salesareas.php');
include('api_salesman.php');
include('api_taxgroups.php');
include('api_holdreasons.php');
include('api_paymentterms.php');
include('api_customertypes.php');
include('api_stock.php');
include('api_debtortransactions.php');
include('api_salesorders.php');
include('api_glaccounts.php');
include('api_glsections.php');
include('api_glgroups.php');
include('api_stockcategories.php');
include('api_suppliers.php');
include('api_purchdata.php');
include('api_workorders.php');
include('api_webERPsettings.php');
