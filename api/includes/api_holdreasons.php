<?php

if (!isset($PathPrefix)) {
	header('Location: ../../');
	exit();
}

/** This function returns a list of the hold reason codes
 * currently setup on webERP
 */
function GetHoldReasonList($user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL = 'SELECT reasoncode FROM holdreasons';
	$Result = DB_query($SQL);
	$i=0;
	$HoldReasonList = array();
	while ($MyRow=DB_fetch_array($Result)) {
		$HoldReasonList[$i]=$MyRow[0];
		$i++;
	}
	return $HoldReasonList;
}

/** This function takes as a parameter a hold reason code
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
	$SQL = "SELECT * FROM holdreasons WHERE reasoncode='".$holdreason."'";
	$Result = DB_query($SQL);
	return DB_fetch_array($Result);
}
