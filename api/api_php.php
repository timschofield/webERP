<?php

	include 'api_errorcodes.php';

/* Include session.inc, to allow database connection, and access to
   miscfunctions, and datefunctions.*/
    $DatabaseName='weberpdemo';
	$AllowAnyone = true;
	$PathPrefix=dirname(__FILE__).'/../';
	include($PathPrefix.'includes/session.inc');
	$_SESSION['db']=$db;

/* Get weberp authentication, and return a valid database
   connection */
	function db($user, $password) {
		$_SESSION['UserID'] = $user;
		$sql = "SELECT userid,
						accesslevel
				FROM www_users
				WHERE userid='" . DB_escape_string($user) . "'
				AND (password='" . CryptPass(DB_escape_string($password)) . "'
				OR  password='" . DB_escape_string($password) . "')";
		$Auth_Result = DB_query($sql, $_SESSION['db']);
		$myrow=DB_fetch_row($Auth_Result);
		if (DB_num_rows($Auth_Result) > 0) {
			$sql = 'SELECT tokenid FROM securitygroups
					WHERE secroleid =  ' . $_SESSION['AccessLevel'];
			$Sec_Result = DB_query($sql, $db);

			$_SESSION['AllowedPageSecurityTokens'] = array();
			if (DB_num_rows($Sec_Result)==0){
				return NoAuthorisation;
			} else {
				$i=0;
				while ($myrow = DB_fetch_row($Sec_Result)){
					$_SESSION['AllowedPageSecurityTokens'][$i] = $myrow[0];
					$i++;
				}
			}
			return $_SESSION['db'];
		} else {
			return NoAuthorisation;
		}
	}

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