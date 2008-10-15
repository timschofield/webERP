<?php

/* This function returns a list of the stock location id's
 * currently setup on webERP
 */

	function GetLocationList($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT loccode FROM locations';
		$result = DB_query($sql, $db);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$LocationList[$i]=$myrow[0];
			$i++;
		}
		return $LocationList;
	}

/* This function takes as a parameter a stock location id
 * and returns an array containing the details of the selected
 * location.
 */

	function GetLocationDetails($location, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT * FROM locations WHERE loccode="'.$location.'"';
		$result = DB_query($sql, $db);
		return DB_fetch_array($result);
	}
?>
