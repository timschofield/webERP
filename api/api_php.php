<?php

/* Include session.inc, to allow database connection, and access to
   miscfunctions, and datefunctions.*/
	$AllowAnyone = true;
	$PathPrefix=dirname(__FILE__).'/../';
	include('api_session.inc');

	include 'api_errorcodes.php';
	/* Include SQL_CommonFunctions.inc, to use GetNextTransNo().*/
	include($PathPrefix.'includes/SQL_CommonFunctions.inc');

/* Get weberp authentication, and return a valid database
   connection */
	function db($user, $password) {
		if (!isset($_SESSION['AccessLevel']) OR
		           $_SESSION['AccessLevel'] == '') {
			return NoAuthorisation;
		} else {
			return $_SESSION['db'];
		}
	}

	include 'api_login.php';
	include 'api_customers.php';
	include 'api_branches.php';
	include 'api_currencies.php';
	include 'api_locations.php';
	include 'api_shippers.php';
	include 'api_salestypes.php';
	include 'api_salesareas.php';
	include 'api_salesman.php';
	include 'api_taxgroups.php';
	include 'api_holdreasons.php';
	include 'api_paymentterms.php';
	include 'api_customertypes.php';
	include 'api_stock.php';
	include 'api_debtortransactions.php';
	include 'api_salesorders.php';
	include 'api_glaccounts.php';
	include 'api_glsections.php';
	include 'api_glgroups.php';
	include 'api_stockcategories.php';
	include 'api_suppliers.php';
	include 'api_purchdata.php';
	include 'api_workorders.php';
	include 'api_webERPsettings.php';

?>