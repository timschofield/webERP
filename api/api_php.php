<?php

	include 'api_errorcodes.php';
	
/* Include session.inc, to allow database connection, and access to 
   miscfunctions, and datefunctions.*/
    $DatabaseName='weberp';
	$AllowAnyone = true;
	$PathPrefix=dirname(__FILE__).'/../';
	include($PathPrefix.'includes/session.inc');
	$_SESSION['db']=$db;

/* Get weberp authentication, and return a valid database
   connection */
	function db($user, $password) {
		$_SESSION['UserID'] = $user;
		$sql = "SELECT userid
			FROM www_users
			WHERE userid='" . DB_escape_string($user) . "'
			AND (password='" . CryptPass(DB_escape_string($password)) . "'
			OR  password='" . DB_escape_string($password) . "')";
		$Auth_Result = DB_query($sql, $_SESSION['db']);
		$myrow=DB_fetch_row($Auth_Result);
		if (DB_num_rows($Auth_Result) > 0) {
			return $_SESSION['db'];
		} else {
			return NoAuthorisation;
		}
	}

	include 'api_customers.php';
	include 'api_branches.php';
	include 'api_currencies.php';
	include 'api_salestypes.php';
	include 'api_holdreasons.php';
	include 'api_paymentterms.php';

?>