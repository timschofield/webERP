<?php

/* This function returns a list of the stock salesman codes
 * currently setup on webERP
 */

	function GetSalesmanList($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT salesmancode FROM salesman';
		$result = DB_query($sql, $db);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$SalesmanList[$i]=$myrow[0];
			$i++;
		}
		return $SalesmanList;
	}

/* This function takes as a parameter a salesman code
 * and returns an array containing the details of the selected
 * salesman.
 */

	function GetSalesmanDetails($salesman, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT * FROM salesman WHERE salesmancode="'.$salesman.'"';
		$result = DB_query($sql, $db);
		return DB_fetch_array($result);
	}
?>