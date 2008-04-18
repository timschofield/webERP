<?php

/* This function returns a list of the sales type abbreviations
 * currently setup on webERP 
 */

	function GetSalesTypeList($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT typeabbrev FROM salestypes';
		$result = DB_query($sql, $db);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$SalesTypeList[$i]=$myrow[0];
			$i++;
		}
		return $SalesTypeList;
	}
	
/* This function takes as a parameter a sales type abbreviation
 * and returns an array containing the details of the selected 
 * sales type.
 */
	
	function GetSalesTypeDetails($salestype, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT * FROM salestypes WHERE typeabbrev="'.$salestype.'"';
		$result = DB_query($sql, $db);
		return DB_fetch_array($result);
	}

?>