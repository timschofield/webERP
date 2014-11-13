<?php
/* $Id: api_salesman.php 6943 2014-10-27 07:06:42Z daintree $*/

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
		$result = DB_query($sql);
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
		$sql = "SELECT * FROM salesman WHERE salesmancode='".$salesman."'";
		$result = DB_query($sql);
		if (DB_num_rows($result)==0) {
			$Errors[0]=NoSuchSalesMan;
			return $Errors;
		} else {
			$Errors[0]=0;
			$Errors[1]=DB_fetch_array($result);
			return $Errors;
		}
	}

/* This function takes as a parameter an array of salesman details
 * to be inserted into webERP.
 */

	function InsertSalesman($SalesmanDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}

		$FieldNames='';
		$FieldValues='';
		foreach ($SalesmanDetails as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		$sql = 'INSERT INTO salesman ('.mb_substr($FieldNames,0,-2).') '.
		  'VALUES ('.mb_substr($FieldValues,0,-2).') ';
		if (sizeof($Errors)==0) {
			$result = DB_Query($sql, $db);
			if (DB_error_no() != 0) {
				$Errors[0] = DatabaseUpdateFailed;
			} else {
				$Errors[0]=0;
			}
		}
		return $Errors;
	}

/* This function takes as a parameter a sales man name
 * and returns an array containing the details of the selected
 * salesman.
 */

	function GetSalesmanDetailsFromName($SalesmanName, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = "SELECT * FROM salesman WHERE salesmanname='".$SalesmanName."'";
		$result = DB_query($sql);
		if (DB_num_rows($result)==0) {
			$Errors[0]=NoSuchSalesMan;
			return $Errors;
		} else {
			$Errors[0]=0;
			$Errors[1]=DB_fetch_array($result);
			return $Errors;
		}
	}

?>