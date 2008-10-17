<?php

/* This function returns a list of the sales areas
 * currently setup on webERP
 */

	function GetSalesAreasList($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT areacode FROM areas';
		$result = DB_query($sql, $db);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$SalesAreaList[$i]=$myrow[0];
			$i++;
		}
		return $SalesAreaList;
	}

/* This function takes as a parameter a sales area code
 * and returns an array containing the details of the selected
 * areas.
 */

	function GetSalesAreaDetails($area, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT * FROM areas WHERE areacode="'.$area.'"';
		$result = DB_query($sql, $db);
		return DB_fetch_array($result);
	}
?>
