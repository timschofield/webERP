<?php
/* $Id: api_customertypes.php 4521 2011-03-29 09:04:20Z daintree $*/

/* This function returns a list of the customer types
 * currently setup on webERP
 */

	function GetCustomerTypeList($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT typeid FROM debtortype';
		$result = DB_query($sql, $db);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$TaxgroupList[$i]=$myrow[0];
			$i++;
		}
		return $TaxgroupList;
	}

/* This function takes as a parameter a customer type id
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
		$sql = "SELECT * FROM debtortype WHERE typeid='".$typeid."'";
		$result = DB_query($sql, $db);
		return DB_fetch_array($result);
	}
?>