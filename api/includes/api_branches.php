<?php

if (!isset($PathPrefix)) {
	header('Location: ../../');
	exit();
}

/** Check that the debtor number exist */
function VerifyBranchDebtorExists($DebtorNumber, $i, $Errors) {
	$Searchsql = "SELECT count(debtorno)
			FROM debtorsmaster
			WHERE debtorno='".$DebtorNumber."'";
	$SearchResult=api_DB_query($Searchsql);
	$Answer = DB_fetch_array($SearchResult);
	if ($Answer[0]==0) {
		$Errors[$i] = DebtorDoesntExist;
	}
	return $Errors;
}

/** Verify that the branch number is valid, and doesn't already
   exist. */
function VerifyBranchNo($DebtorNumber, $BranchNumber, $i, $Errors) {
	if ((mb_strlen($BranchNumber)<1) or (mb_strlen($BranchNumber)>10)) {
		$Errors[$i] = IncorrectBranchNumberLength;
	}
	$Searchsql = "SELECT count(debtorno)
				 FROM custbranch
				 WHERE debtorno='".$DebtorNumber."' AND
					   branchcode='".$BranchNumber."'";
	$SearchResult=api_DB_query($Searchsql);
	$Answer = DB_fetch_row($SearchResult);
	if ($Answer[0] != 0) {
		$Errors[$i] = BranchNoAlreadyExists;
	}
	return $Errors;
}

/** Verify that the branch number exists. */
function VerifyBranchNoExists($DebtorNumber, $BranchNumber, $i, $Errors) {
	if ((mb_strlen($BranchNumber)<1) or (mb_strlen($BranchNumber)>10)) {
		$Errors[$i] = IncorrectBranchNumberLength;
	}
	$Searchsql = "SELECT count(debtorno)
				 FROM custbranch
				 WHERE debtorno='".$DebtorNumber."'
				 AND branchcode='".$BranchNumber."'";
	$SearchResult=api_DB_query($Searchsql);
	$Answer = DB_fetch_row($SearchResult);
	if ($Answer[0] == 0) {
		$Errors[$i] = BranchNoDoesntExist;
	}
	return $Errors;
}


/** Check that the name exists and is 40 characters or less long */
function VerifyBranchName($BranchName, $i, $Errors) {
	if ((mb_strlen($BranchName)<1) or (mb_strlen($BranchName)>40)) {
		$Errors[$i] = IncorrectBranchNameLength;
	}
	return $Errors;
}

/** Check that the address lines are correct length */
function VerifyBranchAddressLine($AddressLine, $Length, $i, $Errors) {
	if (mb_strlen($AddressLine)>$Length) {
		$Errors[$i] = InvalidAddressLine;
	}
	return $Errors;
}

/** Check that the latitude is a numeric field */
function VerifyLatitude($Latitude, $i, $Errors) {
	if (!is_numeric($Latitude)) {
		$Errors[$i] = InvalidLatitude;
	}
	return $Errors;
}

/** Check that the longitude is a numeric field */
function VerifyLongitude($Longitude, $i, $Errors) {
	if (!is_numeric($Longitude)) {
		$Errors[$i] = InvalidLongitude;
	}
	return $Errors;
}

/** Check that the delivery days is a numeric field */
function VerifyEstDeliveryDays($EstDeliveryDays, $i, $Errors) {
	if (!is_numeric($EstDeliveryDays)) {
		$Errors[$i] = InvalidEstDeliveryDays;
	}
	return $Errors;
}

/** Check that the area code is set up in the weberp database */
function VerifyAreaCode($AreaCode , $i, $Errors) {
	$Searchsql = "SELECT COUNT(areacode)
				  FROM areas
				  WHERE areacode='".$AreaCode."'";
	$SearchResult=api_DB_query($Searchsql);
	$Answer = DB_fetch_row($SearchResult);
	if ($Answer[0] == 0) {
		$Errors[$i] = AreaCodeNotSetup;
	}
	return $Errors;
}

/** Check that the salesman is set up in the weberp database */
function VerifySalesmanCode($SalesmanCode , $i, $Errors) {
	$Searchsql = "SELECT COUNT(salesmancode)
				  FROM salesman
				  WHERE salesmancode='".$SalesmanCode."'";
	$SearchResult=api_DB_query($Searchsql);
	$Answer = DB_fetch_row($SearchResult);
	if ($Answer[0] == 0) {
		$Errors[$i] = SalesmanCodeNotSetup;
	}
	return $Errors;
}

/** Check that the forward date is a valid date */
function VerifyFwdDate($FwdDate, $i, $Errors) {
	if (!is_numeric($FwdDate)) {
		$Errors[$i] = InvalidFwdDate;
	}
	return $Errors;
}

/** Check that the phone number only has 20 or fewer characters */
function VerifyPhoneNumber($PhoneNumber, $i, $Errors) {
	if (mb_strlen($PhoneNumber)>20) {
		$Errors[$i] = InvalidPhoneNumber;
	}
	return $Errors;
}

/** Check that the fax number only has 20 or fewer characters */
function VerifyFaxNumber($FaxNumber, $i, $Errors) {
	if (mb_strlen($FaxNumber)>20) {
		$Errors[$i] = InvalidFaxNumber;
	}
	return $Errors;
}

/** Check that the contact name only has 30 or fewer characters */
function VerifyContactName($ContactName, $i, $Errors) {
	if (mb_strlen($ContactName)>30) {
		$Errors[$i] = InvalidContactName;
	}
	return $Errors;
}

/** Validate email addresses */
function  checkEmail($email) {
	if (!preg_match("/^( [a-zA-Z0-9] )+( [a-zA-Z0-9\._-] )*@( [a-zA-Z0-9_-] )+( [a-zA-Z0-9\._-] +)+$/" , $email)) {
		return false;
	}
	return true;
}

/** Check that the email address is in a valid format and only has 55 or fewer characters */
function VerifyEmailAddress($EmailAddress, $i, $Errors) {
	if (mb_strlen($EmailAddress)>55 and !checkEmail($EmailAddress)) {
		$Errors[$i] = InvalidEmailAddress;
	}
	return $Errors;
}

/** Check that the default location is set up in the weberp database */
function VerifyDefaultLocation($DefaultLocation , $i, $Errors) {
	$Searchsql = "SELECT COUNT(loccode)
				  FROM locations
				  WHERE loccode='".$DefaultLocation."'";
	$SearchResult=api_DB_query($Searchsql);
	$Answer = DB_fetch_row($SearchResult);
	if ($Answer[0] == 0) {
		$Errors[$i] = LocationCodeNotSetup;
	}
	return $Errors;
}

/** Check that the tax group id is set up in the weberp database */
function VerifyTaxGroupId($TaxGroupId , $i, $Errors) {
	$Searchsql = "SELECT COUNT(taxgroupid)
				  FROM taxgroups
				  WHERE taxgroupid='".$TaxGroupId."'";
	$SearchResult=api_DB_query($Searchsql);
	$Answer = DB_fetch_row($SearchResult);
	if ($Answer[0] == 0) {
		$Errors[$i] = TaxGroupIdNotSetup;
	}
	return $Errors;
}

/** Check that the default shipper is set up in the weberp database */
function VerifyDefaultShipVia($DefaultShipVia , $i, $Errors) {
	$Searchsql = "SELECT COUNT(shipper_id)
				 FROM shippers
				  WHERE shipper_id='".$DefaultShipVia."'";
	$SearchResult=api_DB_query($Searchsql);
	$Answer = DB_fetch_row($SearchResult);
	if ($Answer[0] == 0) {
		$Errors[$i] = ShipperNotSetup;
	}
	return $Errors;
}

/** Verify that the Deliver Blind flag is a 1 or 2 */
function VerifyDeliverBlind($DeliverBlind, $i, $Errors) {
	if ($DeliverBlind!=1 and $DeliverBlind!=2) {
		$Errors[$i] = InvalidDeliverBlind;
	}
	return $Errors;
}

/** Verify that the Disable Trans flag is a 1 or 0 */
function VerifyDisableTrans($DisableTrans, $i, $Errors) {
	if ($DisableTrans!=0 and $DisableTrans!=1) {
		$Errors[$i] = InvalidDisableTrans;
	}
	return $Errors;
}

/** Check that the special instructions only have 256 or fewer characters */
function VerifySpecialInstructions($SpecialInstructions, $i, $Errors) {
	if (mb_strlen($SpecialInstructions)>256) {
		$Errors[$i] = InvalidSpecialInstructions;
	}
	return $Errors;
}

/** Check that the customer branch code only has 30 or fewer characters */
function VerifyCustBranchCode($CustBranchCode, $i, $Errors) {
	if (mb_strlen($CustBranchCode)>30) {
		$Errors[$i] = InvalidCustBranchCode;
	}
	return $Errors;
}

function InsertBranch($BranchDetails, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	foreach ($BranchDetails as $key => $Value) {
		$BranchDetails[$key] = DB_escape_string($Value);
	}
	$Errors=VerifyBranchDebtorExists($BranchDetails['debtorno'], sizeof($Errors), $Errors);
	$Errors=VerifyBranchNo($BranchDetails['debtorno'], $BranchDetails['branchcode'], sizeof($Errors), $Errors);
	$Errors=VerifyBranchName($BranchDetails['brname'], sizeof($Errors), $Errors);
	if (isset($BranchDetails['address1'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['address1'], 40, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['address2'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['address2'], 40, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['address3'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['address3'], 40, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['address4'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['address4'], 50, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['address5'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['address5'], 20, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['address6'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['address6'], 15, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['lat'])){
		$Errors=VerifyLatitude($BranchDetails['lat'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['lng'])){
		$Errors=VerifyLongitude($BranchDetails['lng'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['estdeliverydays'])){
		$Errors=VerifyEstDeliveryDays($BranchDetails['estdeliverydays'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['area'])){
		$Errors=VerifyAreaCode($BranchDetails['area'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['salesman'])){
		$Errors=VerifySalesmanCode($BranchDetails['salesman'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['fwddate'])){
		$Errors=VerifyFwdDate($BranchDetails['fwddate'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['phoneno'])){
		$Errors=VerifyPhoneNumber($BranchDetails['phoneno'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['faxno'])){
		$Errors=VerifyFaxNumber($BranchDetails['faxno'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['contactname'])){
		$Errors=VerifyContactName($BranchDetails['contactname'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['email'])){
		$Errors=VerifyEmailAddress($BranchDetails['email'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['defaultlocation'])){
		$Errors=VerifyDefaultLocation($BranchDetails['defaultlocation'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['taxgroupid'])){
		$Errors=VerifyTaxGroupId($BranchDetails['taxgroupid'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['defaultshipvia'])){
		$Errors=VerifyDefaultShipVia($BranchDetails['defaultshipvia'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['deliverblind'])){
		$Errors=VerifyDeliverBlind($BranchDetails['deliverblind'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['disabletrans'])){
		$Errors=VerifyDisableTrans($BranchDetails['disabletrans'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['brpostaddr1'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['brpostaddr1'], 40, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['brpostaddr2'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['brpostaddr2'], 40, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['brpostaddr3'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['brpostaddr3'], 30, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['brpostaddr4'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['brpostaddr4'], 20, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['brpostaddr5'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['brpostaddr5'], 20, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['brpostaddr6'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['brpostaddr6'], 15, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['specialinstructions'])){
		$Errors=VerifySpecialInstructions($BranchDetails['specialinstructions'], sizeof($Errors), $Errors);
	} else {
		$BranchDetails['specialinstructions']='';
	}
	if (isset($BranchDetails['custbranchcode'])){
		$Errors=VerifyCustBranchCode($BranchDetails['custbranchcode'], sizeof($Errors), $Errors);
	}
	$BranchDetails['lat']=0;
	$BranchDetails['lng']=0;
	$FieldNames='';
	$FieldValues='';
	foreach ($BranchDetails as $key => $Value) {
		$FieldNames.=$key.', ';
		$FieldValues.='"'.$Value.'", ';
	}
	$SQL = 'INSERT INTO custbranch ('.mb_substr($FieldNames,0,-2).') '.
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


function ModifyBranch($BranchDetails, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	foreach ($BranchDetails as $key => $Value) {
		$BranchDetails[$key] = DB_escape_string($Value);
	}
	$Errors=VerifyBranchNoExists($BranchDetails['debtorno'], $BranchDetails['branchcode'], sizeof($Errors), $Errors);
	$Errors=VerifyBranchName($BranchDetails['brname'], sizeof($Errors), $Errors);
	if (isset($BranchDetails['address1'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['address1'], 40, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['address2'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['address2'], 40, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['address3'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['address3'], 40, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['address4'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['address4'], 50, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['address5'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['address5'], 20, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['address6'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['address6'], 15, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['lat'])){
		$Errors=VerifyLatitude($BranchDetails['lat'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['lng'])){
		$Errors=VerifyLongitude($BranchDetails['lng'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['estdeliverydays'])){
		$Errors=VerifyEstDeliveryDays($BranchDetails['estdeliverydays'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['area'])){
		$Errors=VerifyAreaCode($BranchDetails['area'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['salesman'])){
		$Errors=VerifySalesmanCode($BranchDetails['salesman'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['fwddate'])){
		$Errors=VerifyFwdDate($BranchDetails['fwddate'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['phoneno'])){
		$Errors=VerifyPhoneNumber($BranchDetails['phoneno'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['faxno'])){
		$Errors=VerifyFaxNumber($BranchDetails['faxno'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['contactname'])){
		$Errors=VerifyContactName($BranchDetails['contactname'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['email'])){
		$Errors=VerifyEmailAddress($BranchDetails['email'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['defaultlocation'])){
		$Errors=VerifyDefaultLocation($BranchDetails['defaultlocation'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['taxgroupid'])){
		$Errors=VerifyTaxGroupId($BranchDetails['taxgroupid'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['defaultshipvia'])){
		$Errors=VerifyDefaultShipVia($BranchDetails['defaultshipvia'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['deliverblind'])){
		$Errors=VerifyDeliverBlind($BranchDetails['deliverblind'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['disabletrans'])){
		$Errors=VerifyDisableTrans($BranchDetails['disabletrans'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['brpostaddr1'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['brpostaddr1'], 40, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['brpostaddr2'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['brpostaddr2'], 40, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['brpostaddr3'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['brpostaddr3'], 30, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['brpostaddr4'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['brpostaddr4'], 20, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['brpostaddr5'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['brpostaddr5'], 20, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['brpostaddr6'])){
		$Errors=VerifyBranchAddressLine($BranchDetails['brpostaddr6'], 15, sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['specialinstructions'])){
		$Errors=VerifySpecialInstructions($BranchDetails['specialinstructions'], sizeof($Errors), $Errors);
	}
	if (isset($BranchDetails['custbranchcode'])){
		$Errors=VerifyCustBranchCode($BranchDetails['custbranchcode'], sizeof($Errors), $Errors);
	}
	$FieldNames='';
	$FieldValues='';
	foreach ($BranchDetails as $key => $Value) {
		$FieldNames.=$key.', ';
		$FieldValues.='"'.$Value.'", ';
	}
	$SQL='UPDATE custbranch SET ';
	foreach ($BranchDetails as $key => $Value) {
		$SQL .= $key.'="'.$Value.'", ';
	}
	$SQL = mb_substr($SQL,0,-2)." WHERE debtorno='".$BranchDetails['debtorno']."'
							   AND branchcode='".$BranchDetails['branchcode']."'";
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

/** This function returns a list of branch codes from the given debtorno.
 * The returned data is an array with first value 0 and then as many branch
 * codes are there are branches.  Otherwise, the first value is non-zero,
 * and it (and any following) are error codes encountered.
 */
function GetCustomerBranchCodes($DebtorNumber, $user, $password)
{
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL = "SELECT branchcode FROM custbranch
			WHERE debtorno = '" . $DebtorNumber . "'";
	$Result = api_DB_query($SQL);
	if (DB_error_no() != 0)
		$Errors[0] = DatabaseUpdateFailed;
	else {
		$Errors[0] = 0; // Signal data may follow.
		while ($MyRow = DB_fetch_row($Result)) {
			$Errors[] = $MyRow[0];
		}
	}

	return  $Errors;
}

/** This function takes a debtorno and branch code and returns an associative array containing
   the database record for that branch. If the debtor/branch code doesn't exist
   then it returns an $Errors array.
*/
function GetCustomerBranch($DebtorNumber, $BranchCode, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$Errors = VerifyBranchNoExists($DebtorNumber, $BranchCode, sizeof($Errors), $Errors);
	if (sizeof($Errors)!=0) {
		return $Errors;
	}
	$SQL="SELECT * FROM custbranch
				 WHERE debtorno='".$DebtorNumber."'
				 AND branchcode='".$BranchCode."'";
	$Result = api_DB_query($SQL);
	if (DB_error_no() != 0 ) {
		$Errors[0] = DatabaseUpdateFailed;
	} else {
		$Errors[0] = 0;
		if (DB_num_rows($Result) > 0)
			$Errors += DB_fetch_array($Result);
	}
	return  $Errors;
}
