<?php

if (!isset($PathPrefix)) {
	header('Location: ../');
	exit();
}

/** Check that the area code is set up in the weberp database */
function VerifyAreaCodeDoesntExist($AreaCode , $i, $Errors) {
	$Searchsql = "SELECT COUNT(areacode)
				 FROM areas
				  WHERE areacode='".$AreaCode."'";
	$SearchResult = DB_query($Searchsql);
	$Answer = DB_fetch_row($SearchResult);
	if ($Answer[0] > 0) {
		$Errors[$i] = AreaCodeNotSetup;
	}
	return $Errors;
}

/** This function returns a list of the sales areas
 * currently setup on webERP
 */
function GetSalesAreasList($User, $Password) {
	$Errors = array();
	$db = db($User, $Password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL = 'SELECT areacode FROM areas';
	$Result = DB_query($SQL);
	$i=0;
	$SalesAreaList = array();
	while ($MyRow=DB_fetch_array($Result)) {
		$SalesAreaList[$i]=$MyRow[0];
		$i++;
	}
	return $SalesAreaList;
}

/** This function takes as a parameter a sales area code
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
	$SQL = 'SELECT * FROM areas WHERE areacode="'.$area.'"';
	$Result = DB_query($SQL);
	if (DB_num_rows($Result)==0) {
		$Errors[0]=NoSuchArea;
		return $Errors;
	} else {
		$Errors[0]=0;
		$Errors[1]=DB_fetch_array($Result);
		return $Errors;
	}
}

/** This function takes as a parameter an array of sales area details
 * to be inserted into webERP.
 */
function InsertSalesArea($AreaDetails, $User, $Password) {
	$Errors = array();
	$db = db($User, $Password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$Errors= VerifyAreaCodeDoesntExist($AreaDetails['areacode'], 0, $Errors);
	if (sizeof($Errors>0)) {
//			return $Errors;
	}
	$FieldNames='';
	$FieldValues='';
	foreach ($AreaDetails as $key => $Value) {
		$FieldNames.=$key.', ';
		$FieldValues.='"'.$Value.'", ';
	}
	$SQL = 'INSERT INTO areas ('.mb_substr($FieldNames,0,-2) . ")
			VALUES ('" .mb_substr($FieldValues,0,-2) . "') ";
	if (sizeof($Errors)==0) {
		$Result = DB_query($SQL);
		if (DB_error_no() != 0) {
			$Errors[0] = DatabaseUpdateFailed;
		} else {
			$Errors[0]=0;
		}
	}
	return $Errors;
}

/** This function takes as a parameter a sales area description
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
	$SQL = "SELECT * FROM areas WHERE areadescription='" . $AreaName . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result)==0) {
		$Errors[0]=NoSuchArea;
		return $Errors;
	} else {
		$Errors[0]=0;
		$Errors[1]=DB_fetch_array($Result);
		return $Errors;
	}
}
