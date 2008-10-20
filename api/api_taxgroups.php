<?php

/* This function returns a list of the tax group id's
 * currently setup on webERP
 */

	function GetTaxgroupList($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT taxgroupid FROM taxgroups';
		$result = DB_query($sql, $db);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$TaxgroupList[$i]=$myrow[0];
			$i++;
		}
		return $TaxgroupList;
	}

/* This function takes as a parameter a tax group id
 * and returns an array containing the details of the selected
 * tax group.
 */

	function GetTaxgroupDetails($taxgroup, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT * FROM taxgroups WHERE taxgroupid="'.$taxgroup.'"';
		$result = DB_query($sql, $db);
		return DB_fetch_array($result);
	}
?>