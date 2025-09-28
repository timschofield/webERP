<?php

if (!isset($PathPrefix)) {
	header('Location: ../../');
	exit();
}

/** This function returns a list of the sales type abbreviations
 * currently setup on webERP
 */
function GetSalesTypeList($user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL = "SELECT typeabbrev FROM salestypes";
	$Result = DB_query($SQL);
	$SalesTypeList = array();
	$i=0;
	while ($MyRow=DB_fetch_array($Result)) {
		$SalesTypeList[$i]=$MyRow[0];
		$i++;
	}
	$Errors[0]=0;
	$Errors[1]=$SalesTypeList;
	return $Errors;
}

/** This function takes as a parameter a sales type abbreviation
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
	$Errors = VerifySalesType($salestype, sizeof($Errors), $Errors);
	if (sizeof($Errors)==0) {
		$SQL = "SELECT * FROM salestypes WHERE typeabbrev='".$salestype."'";
		$Result = DB_query($SQL);
		$Errors[0]=0;
		$Errors[1]=DB_fetch_array($Result);
		return $Errors;
	} else {
		return $Errors;
	}
}

/** This function takes as a parameter an array of sales type details
 * to be inserted into webERP.
 */
function InsertSalesType($SalesTypeDetails, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}

	$FieldNames='';
	$FieldValues='';
	foreach ($SalesTypeDetails as $key => $Value) {
		$FieldNames.=$key.', ';
		$FieldValues.='"'.$Value.'", ';
	}
	$SQL = "INSERT INTO salestypes ('" . mb_substr($FieldNames,0,-2) . "')
			VALUES ('" . mb_substr($FieldValues,0,-2) . "') ";
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
