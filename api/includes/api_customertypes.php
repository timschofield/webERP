<?php

if (!isset($PathPrefix)) {
	header('Location: ../../');
	exit();
}

/** This function returns a list of the customer types
 * currently setup on webERP
 */
function GetCustomerTypeList($user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL = 'SELECT typeid FROM debtortype';
	$Result = DB_query($SQL);
	$i=0;
	$TaxgroupList = array();
	while ($MyRow=DB_fetch_array($Result)) {
		$TaxgroupList[$i]=$MyRow[0];
		$i++;
	}
	return $TaxgroupList;
}

/** This function takes as a parameter a customer type id
 * and returns an array containing the details of the selected
 * customer type.
 */
function GetCustomerTypeDetails($typeid, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL = "SELECT * FROM debtortype WHERE typeid='".$typeid."'";
	$Result = DB_query($SQL);
	return DB_fetch_array($Result);
}
