<?php
/* $Id: api_salesareas.php 6943 2014-10-27 07:06:42Z daintree $*/

/* Check that the area code is set up in the weberp database */
	function VerifyAreaCodeDoesntExist($AreaCode , $i, $Errors, $db) {
		$Searchsql = "SELECT COUNT(areacode)
					 FROM areas
					  WHERE areacode='".$AreaCode."'";
		$SearchResult=DB_query($Searchsql);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] > 0) {
			$Errors[$i] = AreaCodeNotSetup;
		}
		return $Errors;
	}

/* This function returns a list of the sales areas
 * currently setup on webERP
 */

	function GetSalesAreasList($User, $Password) {
		$Errors = array();
		$db = db($User, $Password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT areacode FROM areas';
		$result = DB_query($sql);
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

	function GetSalesAreaDetails($area, $User, $Password) {
		$Errors = array();
		$db = db($User, $Password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT * FROM areas WHERE areacode="'.$area.'"';
		$result = DB_query($sql);
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

	function InsertSalesArea($AreaDetails, $User, $Password) {
		$Errors = array();
		$db = db($User, $Password);
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
		$sql = 'INSERT INTO areas ('.mb_substr($FieldNames,0,-2) . ")
				VALUES ('" .mb_substr($FieldValues,0,-2) . "') ";
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

/* This function takes as a parameter a sales area description
 * and returns an array containing the details of the selected
 * areas.
 */

	function GetSalesAreaDetailsFromName($AreaName, $User, $Password) {
		$Errors = array();
		$db = db($User, $Password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = "SELECT * FROM areas WHERE areadescription='" . $AreaName . "'";
		$result = DB_query($sql);
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