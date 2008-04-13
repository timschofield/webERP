<?php

/* Include session.inc, to allow database connection, and access to 
   miscfunctions, and datefunctions.*/
    $DatabaseName='weberp';
	$AllowAnyone = true;
	$PathPrefix=dirname(__FILE__).'/../';
	include($PathPrefix.'includes/session.inc');
	$_SESSION['db']=$db;

	include 'api_customers.php';

?>