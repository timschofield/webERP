<?php
/* $Id: api_customertypes.php 6941 2014-10-26 23:18:08Z daintree $*/

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
		$result = DB_query($sql);
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
		$result = DB_query($sql);
		return DB_fetch_array($result);
	}
?>