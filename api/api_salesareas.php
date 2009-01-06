<?php

/* Check that the area code is set up in the weberp database */
	function VerifyAreaCodeDoesntExist($AreaCode , $i, $Errors, $db) {
		$Searchsql = 'SELECT COUNT(areacode)
					 FROM areas
					  WHERE areacode="'.$AreaCode.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] > 0) {
			$Errors[$i] = AreaCodeNotSetup;
		}
		return $Errors;
	}

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
		if (DB_num_rows($result)==0) {
			$Errors[0]=NoSuchArea;
			return $Errors;
		} else {
			$Errors[0]=0;
			$Errors[1]=DB_fetch_array($result);
			return $Errors;
		}
	}

/* This function takes as a parameter an array of sales area details
 * to be inserted into webERP.
 */

	function InsertSalesArea($AreaDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$Errors= VerifyAreaCodeDoesntExist($AreaDetails['areacode'], 0, $Errors, $db);
		if (sizeof($Errors>0)) {
//			return $Errors;
		}
		$FieldNames='';
		$FieldValues='';
		foreach ($AreaDetails as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		$sql = 'INSERT INTO areas ('.substr($FieldNames,0,-2).') '.
		  'VALUES ('.substr($FieldValues,0,-2).') ';
		if (sizeof($Errors)==0) {
			$result = DB_Query($sql, $db);
			if (DB_error_no($db) != 0) {
				$Errors[0] = DatabaseUpdateFailed;
			} else {
				$Errors[0]=0;
			}
		}
		return $Errors;
	}

/* This function takes as a parameter a sales area description
 * and returns an array containing the details of the selected
 * areas.
 */

	function GetSalesAreaDetailsFromName($areaname, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT * FROM areas WHERE areadescription="'.$areaname.'"';
		$result = DB_query($sql, $db);
		if (DB_num_rows($result)==0) {
			$Errors[0]=NoSuchArea;
			return $Errors;
		} else {
			$Errors[0]=0;
			$Errors[1]=DB_fetch_array($result);
			return $Errors;
		}
	}
?>