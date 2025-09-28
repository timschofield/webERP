<?php

if (!isset($PathPrefix)) {
	header('Location: ../../');
	exit();
}

/*List all revisions
//revision 1.2
*/

/** Verify that the Location code is valid, and doesn't already
   exist. */
function VerifyLocationCode($LocationCode, $i, $Errors) {
	if ((mb_strlen($LocationCode)<1) or (mb_strlen($LocationCode)>5)) {
		$Errors[$i] = IncorrectLocationCodeLength;
	}
	$Searchsql = "SELECT count(loccode)
					FROM locations
					WHERE loccode='".$LocationCode."'";
	$SearchResult = DB_query($Searchsql);
	$Answer = DB_fetch_row($SearchResult);
	if ($Answer[0] != 0) {
		$Errors[$i] = LocationCodeAlreadyExists;
	}
	return $Errors;
}

/** Check that the Location Code exists */
function VerifyLocationExists($LocationCode, $i, $Errors) {
	$Searchsql = "SELECT count(loccode)
					FROM locations
					WHERE loccode='".$LocationCode."'";
	$SearchResult = DB_query($Searchsql);
	$Answer = DB_fetch_array($SearchResult);
	if ($Answer[0]==0) {
		$Errors[$i] = LocationCodeDoesntExist;
	}
	return $Errors;
}

/** Check that the Location name is valid and is 50 characters or less long */
function VerifyLocationName($LocationName, $i, $Errors) {
	if ((mb_strlen($LocationName)<1) or (mb_strlen($LocationName)>50)) {
		$Errors[$i] = IncorrectLocationNameLength;
	}
	return $Errors;
}

/** Check that the tax province id is set up in the weberp database */
function VerifyTaxProvinceId($TaxProvinceId , $i, $Errors) {
	$Searchsql = "SELECT COUNT(taxprovinceid)
					FROM taxprovinces
					WHERE taxprovinceid='".$TaxProvinceId."'";
	$SearchResult = DB_query($Searchsql);
	$Answer = DB_fetch_row($SearchResult);
	if ($Answer[0] == 0) {
		$Errors[$i] = TaxProvinceIdNotSetup;
	}
	return $Errors;
}


/** This function returns a list of the stock location id's
 * currently setup on webERP
 */
function GetLocationList($user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL = "SELECT loccode FROM locations";
	$Result = DB_query($SQL);
	$i=0;
	$LocationList = array();
	while ($MyRow=DB_fetch_array($Result)) {
		$LocationList[$i]=$MyRow[0];
		$i++;
	}
	return array(0, $LocationList);
}

/** This function takes as a parameter a stock location id
 * and returns an array containing the details of the selected
 * location.
 */
function GetLocationDetails($Location, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL = "SELECT * FROM locations WHERE loccode='".$Location."'";
	$Result = DB_query($SQL);
	return array(0, DB_fetch_array($Result));
}

/** Inserts a Location in webERP.
 */
function InsertLocation($Location, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	foreach ($Location as $key => $Value) {
		$Location[$key] = DB_escape_string($Value);
	}
	$Errors=VerifyLocationCode($Location['loccode'], sizeof($Errors), $Errors);
	$Errors=VerifyLocationName($Location['locationname'], sizeof($Errors), $Errors);
	$Errors=VerifyTaxProvinceId($Location['taxprovinceid'], sizeof($Errors), $Errors);
	if (isset($Location['deladd1'])){
		$Errors=VerifyAddressLine($Location['deladd1'], 40, sizeof($Errors), $Errors);
	}
	if (isset($Location['deladd2'])){
		$Errors=VerifyAddressLine($Location['deladd2'], 40, sizeof($Errors), $Errors);
	}
	if (isset($Location['deladd3'])){
		$Errors=VerifyAddressLine($Location['deladd3'], 40, sizeof($Errors), $Errors);
	}
	if (isset($Location['deladd4'])){
		$Errors=VerifyAddressLine($Location['deladd4'], 40, sizeof($Errors), $Errors);
	}
	if (isset($Location['deladd5'])){
		$Errors=VerifyAddressLine($Location['deladd5'], 20, sizeof($Errors), $Errors);
	}
	if (isset($Location['deladd6'])){
		$Errors=VerifyAddressLine($Location['deladd6'], 15, sizeof($Errors), $Errors);
	}
	if (isset($Location['tel'])){
		$Errors=VerifyPhoneNumber($Location['tel'], sizeof($Errors), $Errors);
	}
	if (isset($Location['fax'])){
		$Errors=VerifyFaxNumber($Location['fax'], sizeof($Errors), $Errors);
	}
	if (isset($Location['email'])){
		$Errors=VerifyEmailAddress($Location['email'], sizeof($Errors), $Errors);
	}
	if (isset($Location['contact'])){
		$Errors=VerifyContactName($Location['contact'], sizeof($Errors), $Errors);
	}
	$FieldNames='';
	$FieldValues='';
	foreach ($Location as $key => $Value) {
		$FieldNames.=$key.', ';
		$FieldValues.='"'.$Value.'", ';
	}
	if (sizeof($Errors)==0) {
		$SQL = "INSERT INTO locations (" . mb_substr($FieldNames,0,-2) . ")
					VALUES ('" . mb_substr($FieldValues,0,-2) . "') ";

		$Result = DB_query($SQL);
		if (DB_error_no() != 0) {
			$Errors[0] = DatabaseUpdateFailed;
		} else {
			$Errors[0]=0;
		}
	}
	return $Errors;
}

/** Modify a Location Details in webERP.
 */
function ModifyLocation($Location, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	foreach ($Location as $key => $Value) {
		$Location[$key] = DB_escape_string($Value);
	}
	$Errors=VerifyLocationExists($Location['loccode'], sizeof($Errors), $Errors);
	$Errors=VerifyLocationName($Location['locationname'], sizeof($Errors), $Errors);
	$Errors=VerifyTaxProvinceId($Location['taxprovinceid'], sizeof($Errors), $Errors);
	if (isset($Location['deladd1'])){
		$Errors=VerifyAddressLine($Location['deladd1'], 40, sizeof($Errors), $Errors);
	}
	if (isset($Location['deladd2'])){
		$Errors=VerifyAddressLine($Location['deladd2'], 40, sizeof($Errors), $Errors);
	}
	if (isset($Location['deladd3'])){
		$Errors=VerifyAddressLine($Location['deladd3'], 40, sizeof($Errors), $Errors);
	}
	if (isset($Location['deladd4'])){
		$Errors=VerifyAddressLine($Location['deladd4'], 40, sizeof($Errors), $Errors);
	}
	if (isset($Location['deladd5'])){
		$Errors=VerifyAddressLine($Location['deladd5'], 20, sizeof($Errors), $Errors);
	}
	if (isset($Location['deladd6'])){
		$Errors=VerifyAddressLine($Location['deladd6'], 15, sizeof($Errors), $Errors);
	}
	if (isset($Location['tel'])){
		$Errors=VerifyPhoneNumber($Location['tel'], sizeof($Errors), $Errors);
	}
	if (isset($Location['fax'])){
		$Errors=VerifyFaxNumber($Location['fax'], sizeof($Errors), $Errors);
	}
	if (isset($Location['email'])){
		$Errors=VerifyEmailAddress($Location['email'], sizeof($Errors), $Errors);
	}
	if (isset($Location['contact'])){
		$Errors=VerifyContactName($Location['contact'], sizeof($Errors), $Errors);
	}
	$SQL="UPDATE locations SET ";
	foreach ($Location as $key => $Value) {
		$SQL .= $key."='" . $Value."', ";
	}
	$SQL = mb_substr($SQL,0,-2)." WHERE loccode='".$Location['loccode']."'";
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
