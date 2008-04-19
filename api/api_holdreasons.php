<?php

/* This function returns a list of the hold reason codes
 * currently setup on webERP 
 */

	function GetHoldReasonList($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT reasoncode FROM holdreasons';
		$result = DB_query($sql, $db);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$HoldReasonList[$i]=$myrow[0];
			$i++;
		}
		return $HoldReasonList;
	}
	
/* This function takes as a parameter a hold reason code
 * and returns an array containing the details of the selected 
 * hold reason.
 */
	
	function GetHoldReasonDetails($holdreason, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT * FROM holdreasons WHERE reasoncode="'.$holdreason.'"';
		$result = DB_query($sql, $db);
		return DB_fetch_array($result);
	}

?>