<?php

if (!isset($PathPrefix)) {
	header('Location: ../../');
	exit();
}

/** This function returns a list of the stock salesman codes
 * currently setup on webERP
 */
function GetSalesmanList($user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL = 'SELECT salesmancode FROM salesman';
	$Result = DB_query($SQL);
	$i=0;
	$SalesmanList = array();
	while ($MyRow=DB_fetch_array($Result)) {
		$SalesmanList[$i]=$MyRow[0];
		$i++;
	}
	return $SalesmanList;
}

/** This function takes as a parameter a salesman code
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
	$SQL = "SELECT * FROM salesman WHERE salesmancode='".$salesman."'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result)==0) {
		$Errors[0]=NoSuchSalesMan;
		return $Errors;
	} else {
		$Errors[0]=0;
		$Errors[1]=DB_fetch_array($Result);
		return $Errors;
	}
}

/** This function takes as a parameter an array of salesman details
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
	foreach ($SalesmanDetails as $key => $Value) {
		$FieldNames.=$key.', ';
		$FieldValues.='"'.$Value.'", ';
	}
	$SQL = 'INSERT INTO salesman ('.mb_substr($FieldNames,0,-2).') '.
		'VALUES ('.mb_substr($FieldValues,0,-2).') ';
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

/** This function takes as a parameter a sales man name
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
	$SQL = "SELECT * FROM salesman WHERE salesmanname='".$SalesmanName."'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result)==0) {
		$Errors[0]=NoSuchSalesMan;
		return $Errors;
	} else {
		$Errors[0]=0;
		$Errors[1]=DB_fetch_array($Result);
		return $Errors;
	}
}
