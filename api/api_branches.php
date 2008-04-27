<?php

/* Check that the debtor number exists*/
	function VerifyDebtorExists($DebtorNumber, $i, $Errors, $db) {
		$Searchsql = "SELECT count(debtorno) 
				FROM debtorsmaster
				WHERE debtorno='".$DebtorNumber."'";
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_array($SearchResult);
		if ($answer[0]==0) {
			$Errors[$i] = DebtorDoesntExist;
		}
		return $Errors;
	}

/* Verify that the branch number is valid, and doesn't already
   exist.*/
	function VerifyBranchNo($DebtorNumber, $BranchNumber, $i, $Errors, $db) {
		if ((strlen($BranchNumber)<1) or (strlen($BranchNumber)>10)) {
			$Errors[$i] = IncorrectBranchNumberLength;
		}
		$Searchsql = 'SELECT count(debtorno) 
				FROM custbranch
				WHERE debtorno="'.$DebtorNumber.'" AND
				branchcode="'.$BranchNumber.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] != 0) {
			$Errors[$i] = BranchNoAlreadyExists;
		}
		return $Errors;
	}

/* Check that the name exists and is 40 characters or less long */		
	function VerifyBranchName($BranchName, $i, $Errors) {
		if ((strlen($BranchName)<1) or (strlen($BranchName)>40)) {
			$Errors[$i] = IncorrectBranchNameLength;
		}
		return $Errors;
	}

/* Check that the address lines are correct length*/		
	function VerifyAddressLine($AddressLine, $length, $i, $Errors) {
		if (strlen($AddressLine)>$length) {
			$Errors[$i] = InvalidAddressLine;
		}
		return $Errors;
	}

/* Check that the address lines are correct length*/		
	function VerifyEstDeliveryDays($EstDeliveryDays, $i, $Errors) {
		if (!is_numeric($EstDeliveryDays)) {
			$Errors[$i] = InvalidEstDeliveryDays;			
		}
		return $Errors;
	}

/* Check that the area code is set up in the weberp database */	
	function VerifyAreaCode($AreaCode , $i, $Errors, $db) {
		$Searchsql = 'SELECT COUNT(areacode)
					 FROM areas
					  WHERE areacode="'.$AreaCode.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = AreaCodeNotSetup;
		}
		return $Errors;		
	}

/* Check that the salesman is set up in the weberp database */	
	function VerifySalesmanCode($SalesmanCode , $i, $Errors, $db) {
		$Searchsql = 'SELECT COUNT(salesmancode)
					 FROM salesman
					  WHERE salesmancode="'.$SalesmanCode.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = SalesmanCodeNotSetup;
		}
		return $Errors;		
	}
	
/* Check that the forward date is a valid date */
	function VerifyFwdDate($FwdDate, $i, $Errors) {
		if (!Is_Date($FwdDate)) {
			$Errors[$i] = InvalidFwdDate;
		}
		return $Errors;
	}

/* Check that the phone number only has 20 or fewer characters */		
	function VerifyPhoneNumber($PhoneNumber, $i, $Errors) {
		if (strlen($PhoneNumber)>20) {
			$Errors[$i] = InvalidPhoneNumber;
		}
		return $Errors;
	}

/* Check that the fax number only has 20 or fewer characters */		
	function VerifyFaxNumber($FaxNumber, $i, $Errors) {
		if (strlen($FaxNumber)>20) {
			$Errors[$i] = InvalidFaxNumber;
		}
		return $Errors;
	}

/* Check that the contact name only has 30 or fewer characters */		
	function VerifyContactName($ContactName, $i, $Errors) {
		if (strlen($ContactName)>30) {
			$Errors[$i] = InvalidContactName;
		}
		return $Errors;
	}

/* Validate email addresses */	
	function  checkEmail($email) {
		if (!preg_match("/^( [a-zA-Z0-9] )+( [a-zA-Z0-9\._-] )*@( [a-zA-Z0-9_-] )+( [a-zA-Z0-9\._-] +)+$/" , $email)) {
  			return false;
 		}
 		return true;
	}
	
/* Check that the email address is in a valid format and only has 55 or fewer characters */		
	function VerifyEmailAddress($EmailAddress, $i, $Errors) {
		if (strlen($EmailAddress)>55 and !checkEmail($EmailAddress)) {
			$Errors[$i] = InvalidEmailAddress;
		}
		return $Errors;
	}

/* Check that the default location is set up in the weberp database */	
	function VerifyDefaultLocation($DefaultLocation , $i, $Errors, $db) {
		$Searchsql = 'SELECT COUNT(loccode)
					 FROM locations
					  WHERE loccode="'.$DefaultLocation.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = LocationCodeNotSetup;
		}
		return $Errors;		
	}

/* Check that the tax group id is set up in the weberp database */	
	function VerifyTaxGroupId($TaxGroupId , $i, $Errors, $db) {
		$Searchsql = 'SELECT COUNT(taxgroupid)
					 FROM taxgroups
					  WHERE taxgroupid="'.$TaxGroupId.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = TaxGroupIdNotSetup;
		}
		return $Errors;		
	}

/* Check that the default shipper is set up in the weberp database */	
	function VerifyDefaultShipVia($DefaultShipVia , $i, $Errors, $db) {
		$Searchsql = 'SELECT COUNT(shipper_id)
					 FROM shippers
					  WHERE shipper_id="'.$DefaultShipVia.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = ShipperNotSetup;
		}
		return $Errors;		
	}
	
/* Verify that the Deliver Blind flag is a 1 or 0 */
	function VerifyDeliverBlind($DeliverBlind, $i, $Errors) {
		if ($DeliverBlind!=0 and $DeliverBlind!=1) {
			$Errors[$i] = InvalidDeliverBlind;			
		}
		return $Errors;
	}
	
/* Verify that the Disable Trans flag is a 1 or 0 */
	function VerifyDisableTrans($DisableTrans, $i, $Errors) {
		if ($DisableTrans!=0 and $DisableTrans!=1) {
			$Errors[$i] = InvalidDisableTrans;			
		}
		return $Errors;
	}

/* Check that the special instructions only have 256 or fewer characters */		
	function VerifySpecialInstructions($SpecialInstructions, $i, $Errors) {
		if (strlen($SpecialInstructions)>256) {
			$Errors[$i] = InvalidSpecialInstructions;
		}
		return $Errors;
	}

/* Check that the customer branch code only has 30 or fewer characters */		
	function VerifyCustBranchCode($CustBranchCode, $i, $Errors) {
		if (strlen($CustBranchCode)>30) {
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
		foreach ($BranchDetails as $key => $value) {
			$BranchDetails[$key] = DB_escape_string($value);
		}
		$Errors=VerifyDebtorExists($BranchDetails['debtorno'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyBranchNo($BranchDetails['debtorno'], $BranchDetails['branchcode'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyBranchName($BranchDetails['brname'], sizeof($Errors), $Errors, $db);
		if (isset($BranchDetails['address1'])){
			$Errors=VerifyAddressLine($BranchDetails['address1'], 40, sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['address2'])){
			$Errors=VerifyAddressLine($BranchDetails['address2'], 40, sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['address3'])){
			$Errors=VerifyAddressLine($BranchDetails['address3'], 40, sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['address4'])){
			$Errors=VerifyAddressLine($BranchDetails['address4'], 50, sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['address5'])){
			$Errors=VerifyAddressLine($BranchDetails['address5'], 20, sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['address6'])){
			$Errors=VerifyAddressLine($BranchDetails['address6'], 15, sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['estdeliverydays'])){
			$Errors=VerifyEstDeliveryDays($BranchDetails['estdeliverydays'], sizeof($Errors), $Errors);
		}
		if (isset($BranchDetails['area'])){
			$Errors=VerifyAreaCode($BranchDetails['area'], sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['salesman'])){
			$Errors=VerifySalesmanCode($BranchDetails['salesman'], sizeof($Errors), $Errors, $db);
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
			$Errors=VerifyDefaultLocation($BranchDetails['defaultlocation'], sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['taxgroupid'])){
			$Errors=VerifyTaxGroupId($BranchDetails['taxgroupid'], sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['defaultshipvia'])){
			$Errors=VerifyDefaultShipVia($BranchDetails['defaultshipvia'], sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['deliverblind'])){
			$Errors=VerifyDeliverBlind($BranchDetails['deliverblind'], sizeof($Errors), $Errors);
		}
		if (isset($BranchDetails['disabletrans'])){
			$Errors=VerifyDisableTrans($BranchDetails['disabletrans'], sizeof($Errors), $Errors);
		}
		if (isset($BranchDetails['brpostaddr1'])){
			$Errors=VerifyAddressLine($BranchDetails['brpostaddr1'], 40, sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['brpostaddr2'])){
			$Errors=VerifyAddressLine($BranchDetails['brpostaddr2'], 40, sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['brpostaddr3'])){
			$Errors=VerifyAddressLine($BranchDetails['brpostaddr3'], 30, sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['brpostaddr4'])){
			$Errors=VerifyAddressLine($BranchDetails['brpostaddr4'], 20, sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['brpostaddr5'])){
			$Errors=VerifyAddressLine($BranchDetails['brpostaddr5'], 20, sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['brpostaddr6'])){
			$Errors=VerifyAddressLine($BranchDetails['brpostaddr6'], 15, sizeof($Errors), $Errors, $db);
		}
		if (isset($BranchDetails['specialinstructions'])){
			$Errors=VerifySpecialInstructions($BranchDetails['specialinstructions'], sizeof($Errors), $Errors);
		}
		if (isset($BranchDetails['custbranchcode'])){
			$Errors=VerifyCustBranchCode($BranchDetails['custbranchcode'], sizeof($Errors), $Errors);
		}
		$FieldNames='';
		$FieldValues='';
		foreach ($BranchDetails as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		$sql = 'INSERT INTO custbranch ('.substr($FieldNames,0,-2).') '.
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

?>