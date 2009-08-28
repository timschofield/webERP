<?php

/* Verify that the debtor number is valid, and doesn't already
   exist.*/
	function VerifyDebtorNo($DebtorNumber, $i, $Errors, $db) {
		if ((strlen($DebtorNumber)<1) or (strlen($DebtorNumber)>10)) {
			$Errors[$i] = IncorrectDebtorNumberLength;
		}
		$Searchsql = 'SELECT count(debtorno)
				FROM debtorsmaster
				WHERE debtorno="'.$DebtorNumber.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] != 0) {
			$Errors[$i] = DebtorNoAlreadyExists;
		}
		return $Errors;
	}

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

/* Check that the name exists and is 40 characters or less long */
	function VerifyDebtorName($DebtorName, $i, $Errors) {
		if ((strlen($DebtorName)<1) or (strlen($DebtorName)>40)) {
			$Errors[$i] = IncorrectDebtorNameLength;
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

/* Check that the currency code is set up in the weberp database */
	function VerifyCurrencyCode($CurrCode, $i, $Errors, $db) {
		$Searchsql = 'SELECT COUNT(currabrev)
					  FROM currencies
					  WHERE currabrev="'.$CurrCode.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = CurrencyCodeNotSetup;
		}
		return $Errors;
	}

/* Check that the sales type is set up in the weberp database */
	function VerifySalesType($SalesType, $i, $Errors, $db) {
		$Searchsql = "SELECT COUNT(typeabbrev)
					 FROM salestypes
					  WHERE typeabbrev='".$SalesType."'";
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = SalesTypeNotSetup;
		}
		return $Errors;
	}

/* Check that the clientsince date is a valid date */
	function VerifyClientSince($ClientSince, $i, $Errors) {
		if (!Is_Date($ClientSince)) {
//			$Errors[$i] = InvalidClientSinceDate;
		}
		return $Errors;
	}

/* Check that the hold reason is set up in the weberp database */
	function VerifyHoldReason($HoldReason , $i, $Errors, $db) {
		$Searchsql = 'SELECT COUNT(reasoncode)
					 FROM holdreasons
					  WHERE reasoncode="'.$HoldReason.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = HoldReasonNotSetup;
		}
		return $Errors;
	}

/* Check that the payment terms are set up in the weberp database */
	function VerifyPaymentTerms($PaymentTerms , $i, $Errors, $db) {
		$Searchsql = 'SELECT COUNT(termsindicator)
					 FROM paymentterms
					  WHERE termsindicator="'.$PaymentTerms.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = PaymentTermsNotSetup;
		}
		return $Errors;
	}

/* Verify that the discount figure is numeric */
	function VerifyDiscount($Discount, $i, $Errors) {
		if (!is_numeric($Discount)) {
			$Errors[$i] = InvalidDiscount;
		}
		return $Errors;
	}

/* Verify that the payment discount figure is numeric */
	function VerifyPymtDiscount($Discount, $i, $Errors) {
		if (!is_numeric($Discount)) {
			$Errors[$i] = InvalidPaymentDiscount;
		}
		return $Errors;
	}

/* Verify that the last paid amount is numeric */
	function VerifyLastPaid($LastPaid, $i, $Errors) {
		if (!is_numeric($LastPaid)) {
			$Errors[$i] = InvalidLastPaid;
		}
		return $Errors;
	}

/* Check that the last paid date is a valid date */
	function VerifyLastPaidDate($ClientSince, $i, $Errors) {
		if (!Is_Date($ClientSince)) {
			$Errors[$i] = InvalidLastPaidDate;
		}
		return $Errors;
	}

/* Verify that the last credit limit is numeric and positive */
	function VerifyCreditLimit($CreditLimit, $i, $Errors) {
		if (!is_numeric($CreditLimit) or $CreditLimit<0) {
			$Errors[$i] = InvalidCreditLimit;
		}
		return $Errors;
	}

/* Verify that the InvAddrBranch is a 1 or 0 */
	function VerifyInvAddrBranch($InvAddrBranch, $i, $Errors) {
		if ($InvAddrBranch!=0 and $InvAddrBranch!=1) {
			$Errors[$i] = InvalidInvAddrBranch;
		}
		return $Errors;
	}

/* Check that the discount code only has 1 or 2 characters */
	function VerifyDiscountCode($DiscountCode, $i, $Errors) {
		if (strlen($DiscountCode)>2) {
			$Errors[$i] = InvalidDiscountCode;
		}
		return $Errors;
	}

/* Verify that the EDIInvoices is a 1 or 0 */
	function VerifyEDIInvoices($EDIInvoices, $i, $Errors) {
		if ($EDIInvoices!=0 and $EDIInvoices!=1) {
			$Errors[$i] = InvalidEDIInvoices;
		}
		return $Errors;
	}

/* Verify that the EDIOrders is a 1 or 0 */
	function VerifyEDIOrders($EDIOrders, $i, $Errors) {
		if ($EDIOrders!=0 and $EDIOrders!=1) {
			$Errors[$i] = InvalidEDIOrders;
		}
		return $Errors;
	}

	function VerifyEDIReference($EDIReference, $i, $Errors) {
		if (strlen($EDIReference)>20) {
			$Errors[$i] = IvalidEDIReference;
		}
		return $Errors;
	}

	function VerifyEDITransport($EDITransport, $i, $Errors) {
		if ($EDITransport!='email' and $EDITransport!='ftp') {
			$Errors[$i] = InvalidEDITransport;
		}
		return $Errors;
	}

	function VerifyEDIAddress($EDIAddress, $i, $Errors) {
		if (strlen($EDIAddress)>50) {
			$Errors[$i] = IvalidEDIAddress;
		}
		return $Errors;
	}

	function VerifyEDIServerUser($EDIServerUser, $i, $Errors) {
		if (strlen($EDIServerUser)>20) {
			$Errors[$i] = IvalidEDIServerUser;
		}
		return $Errors;
	}

	function VerifyEDIServerPassword($EDIServerPassword, $i, $Errors) {
		if (strlen($EDIServerPassword)>20) {
			$Errors[$i] = IvalidEDIServerPassword;
		}
		return $Errors;
	}

	function VerifyTaxRef($TaxRef, $i, $Errors) {
		if (strlen($TaxRef)>20) {
			$Errors[$i] = IvalidTaxRef;
		}
		return $Errors;
	}

	function VerifyCustomerPOLine($CustomerPOLine, $i, $Errors) {
		if ($CustomerPOLine!=0 and $CustomerPOLine!=1) {
			$Errors[$i] = InvalidCustomerPOLine;
		}
		return $Errors;
	}

/* Check that the customer type is set up in the weberp database */
	function VerifyCustomerType($debtortype , $i, $Errors, $db) {
		$Searchsql = 'SELECT COUNT(typeid)
					 FROM debtortype
					  WHERE typeid="'.$debtortype.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = CustomerTypeNotSetup;
		}
		return $Errors;
	}

/* Insert a new customer in the webERP database. This function takes an
   associative array called $CustomerDetails, where the keys are the
   names of the fields in the debtorsmaster table, and the values are the
   values to insert. The only mandatory fields are the debtorno, name,
   currency code, sales type, payment terms, and reason code
   fields. If the other fields aren't set, then the database defaults
   are used. The function returns an array called $Errors. The database
   is only updated if the $Errors is empty, else the function returns an
   array of one to many error codes.
*/
	function InsertCustomer($CustomerDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($CustomerDetails as $key => $value) {
			$CustomerDetails[$key] = DB_escape_string($value);
		}
		$autonumbersql='SELECT confvalue FROM config
						 WHERE confname="AutoDebtorNo"';
		$autonumberresult=DB_query($autonumbersql, $db);
		$autonumber=DB_fetch_row($autonumberresult);
		if ($autonumber[0]==0) {
			$Errors=VerifyDebtorNo($CustomerDetails['debtorno'], sizeof($Errors), $Errors, $db);
		} else {
			$CustomerDetails['debtorno']='';
		}
		$Errors=VerifyDebtorName($CustomerDetails['name'], sizeof($Errors), $Errors);
		if (isset($CustomerDetails['address1'])){
			$Errors=VerifyAddressLine($CustomerDetails['address1'], 40, sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['address2'])){
			$Errors=VerifyAddressLine($CustomerDetails['address2'], 40, sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['address3'])){
			$Errors=VerifyAddressLine($CustomerDetails['address3'], 40, sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['address4'])){
			$Errors=VerifyAddressLine($CustomerDetails['address4'], 50, sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['address5'])){
			$Errors=VerifyAddressLine($CustomerDetails['address5'], 20, sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['address6'])){
			$Errors=VerifyAddressLine($CustomerDetails['address6'], 15, sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['currcode'])){
			$Errors=VerifyCurrencyCode($CustomerDetails['currcode'], sizeof($Errors), $Errors, $db);
		}
		if (isset($CustomerDetails['salestype'])){
			$Errors=VerifySalesType($CustomerDetails['salestype'], sizeof($Errors), $Errors, $db);
		}
		if (isset($CustomerDetails['clientsince'])){
			$Errors=VerifyClientSince($CustomerDetails['clientsince'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['holdreason'])){
			$Errors=VerifyHoldReason($CustomerDetails['holdreason'], sizeof($Errors), $Errors, $db);
		}
		if (isset($CustomerDetails['paymentterms'])){
			$Errors=VerifyPaymentTerms($CustomerDetails['paymentterms'], sizeof($Errors), $Errors, $db);
		}
		if (isset($CustomerDetails['discount'])){
			$Errors=VerifyDiscount($CustomerDetails['discount'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['pymtdiscount'])){
			$Errors=VerifyPymtDiscount($CustomerDetails['pymtdiscount'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['lastpaid'])){
			$Errors=VerifyLastPaid($CustomerDetails['lastpaid'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['lastpaiddate'])){
			$Errors=VerifyLastPaidDate($CustomerDetails['lastpaiddate'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['creditlimit'])){
			$Errors=VerifyCreditLimit($CustomerDetails['creditlimit'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['invaddrbranch'])){
			$Errors=VerifyInvAddrBranch($CustomerDetails['invaddrbranch'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['discountcode'])){
			$Errors=VerifyDiscountCode($CustomerDetails['discountcode'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['ediinvoices'])){
			$Errors=VerifyEDIInvoices($CustomerDetails['ediinvoices'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['ediorders'])){
			$Errors=VerifyEDIOrders($CustomerDetails['ediorders'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['edireference'])){
			$Errors=VerifyEDIReference($CustomerDetails['edireference'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['editransport'])){
			$Errors=VerifyEDITransport($CustomerDetails['editransport'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['ediserveruser'])){
			$Errors=VerifyEDIServerUser($CustomerDetails['ediserveruser'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['ediserverpwd'])){
			$Errors=VerifyEDIServerPassword($CustomerDetails['ediserverpwd'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['taxref'])){
			$Errors=VerifyTaxRef($CustomerDetails['taxref'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['customerpoline'])){
			$Errors=VerifyCustomerPOLine($CustomerDetails['customerpoline'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['typeid'])){
			$Errors=VerifyCustomerType($CustomerDetails['typeid'], sizeof($Errors), $Errors, $db);
		}
		$FieldNames='';
		$FieldValues='';
		foreach ($CustomerDetails as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		$sql = 'INSERT INTO debtorsmaster ('.substr($FieldNames,0,-2).') '.
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

/* Modifies a customer record in the webERP database. This function takes an
   associative array called $CustomerDetails, where the keys are the
   names of the fields in the debtorsmaster table, and the values are the
   values to update. The debtorno is mandatory and only fields that need
   updating should be included. The function returns an array called $Errors.
   The database is only updated if the $Errors is empty, else the function
   returns an array of one to many error codes.
*/
	function ModifyCustomer($CustomerDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($CustomerDetails as $key => $value) {
			$CustomerDetails[$key] = DB_escape_string($value);
		}
		if (!isset($CustomerDetails['debtorno'])) {
			$Errors[sizeof($Errors)] = NoDebtorNumber;
			return $Errors;
		}
		$Errors=VerifyDebtorExists($CustomerDetails['debtorno'], sizeof($Errors), $Errors, $db);
		if (in_array(DebtorDoesntExist, $Errors)) {
			return $Errors;
		}
		if (isset($CustomerDetails['name'])){
			$Errors=VerifyDebtorName($CustomerDetails['name'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['address1'])){
			$Errors=VerifyAddressLine($CustomerDetails['address1'], 40, sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['address2'])){
			$Errors=VerifyAddressLine($CustomerDetails['address2'], 40, sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['address3'])){
			$Errors=VerifyAddressLine($CustomerDetails['address3'], 40, sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['address4'])){
			$Errors=VerifyAddressLine($CustomerDetails['address4'], 50, sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['address5'])){
			$Errors=VerifyAddressLine($CustomerDetails['address5'], 20, sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['address6'])){
			$Errors=VerifyAddressLine($CustomerDetails['address6'], 15, sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['currcode'])){
			$Errors=VerifyCurrencyCode($CustomerDetails['currcode'], sizeof($Errors), $Errors, $db);
		}
		if (isset($CustomerDetails['salestype'])){
			$Errors=VerifySalesType($CustomerDetails['salestype'], sizeof($Errors), $Errors, $db);
		}
		if (isset($CustomerDetails['clientsince'])){
			$Errors=VerifyClientSince($CustomerDetails['clientsince'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['holdreason'])){
			$Errors=VerifyHoldReason($CustomerDetails['holdreason'], sizeof($Errors), $Errors, $db);
		}
		if (isset($CustomerDetails['paymentterms'])){
			$Errors=VerifyPaymentTerms($CustomerDetails['paymentterms'], sizeof($Errors), $Errors, $db);
		}
		if (isset($CustomerDetails['discount'])){
			$Errors=VerifyDiscount($CustomerDetails['discount'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['pymtdiscount'])){
			$Errors=VerifyPymtDiscount($CustomerDetails['pymtdiscount'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['lastpaid'])){
			$Errors=VerifyLastPaid($CustomerDetails['lastpaid'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['lastpaiddate'])){
			$Errors=VerifyLastPaidDate($CustomerDetails['lastpaiddate'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['creditlimit'])){
			$Errors=VerifyCreditLimit($CustomerDetails['creditlimit'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['invaddrbranch'])){
			$Errors=VerifyInvAddrBranch($CustomerDetails['invaddrbranch'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['discountcode'])){
			$Errors=VerifyDiscountCode($CustomerDetails['discountcode'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['ediinvoices'])){
			$Errors=VerifyEDIInvoices($CustomerDetails['ediinvoices'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['ediorders'])){
			$Errors=VerifyEDIOrders($CustomerDetails['ediorders'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['edireference'])){
			$Errors=VerifyEDIReference($CustomerDetails['edireference'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['editransport'])){
			$Errors=VerifyEDITransport($CustomerDetails['editransport'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['ediserveruser'])){
			$Errors=VerifyEDIServerUser($CustomerDetails['ediserveruser'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['ediserverpwd'])){
			$Errors=VerifyEDIServerPassword($CustomerDetails['ediserverpwd'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['taxref'])){
			$Errors=VerifyTaxRef($CustomerDetails['taxref'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['customerpoline'])){
			$Errors=VerifyCustomerPOLine($CustomerDetails['customerpoline'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['typeid'])){
			$Errors=VerifyCustomerType($CustomerDetails['typeid'], sizeof($Errors), $Errors, $db);
		}
		$sql='UPDATE debtorsmaster SET ';
		foreach ($CustomerDetails as $key => $value) {
			$sql .= $key.'="'.$value.'", ';
		}
		$sql = substr($sql,0,-2).' WHERE debtorno="'.$CustomerDetails['debtorno'].'"';
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

/* This function takes a debtorno and returns an associative array containing
   the database record for that debtor. If the debtor number doesn't exist
   then it returns an $Errors array.
*/
	function GetCustomer($DebtorNumber, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$Errors = VerifyDebtorExists($DebtorNumber, sizeof($Errors), $Errors, $db);
		if (sizeof($Errors)!=0) {
			return $Errors;
		}
		$sql='SELECT * FROM debtorsmaster WHERE debtorno="'.$DebtorNumber.'"';
		$result = DB_Query($sql, $db);
		if (sizeof($Errors)==0) {
			return DB_fetch_array($result);
		} else {
			return $Errors;
		}
	}

/* This function takes a field name, and a string, and then returns an
   array of debtornos that fulfill this criteria.
*/
	function SearchCustomers($Field, $Criteria, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql='SELECT debtorno
			FROM debtorsmaster
			WHERE '.$Field.' LIKE "%'.$Criteria.'%"';
		$result = DB_Query($sql, $db);
		$i=0;
		$DebtorList = array();
		while ($myrow=DB_fetch_array($result)) {
			$DebtorList[$i]=$myrow[0];
			$i++;
		}
		return $DebtorList;
	}

?>