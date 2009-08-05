<?php

/* Verify that the supplier number is valid, and doesn't already
   exist.*/
	function VerifySupplierNo($SupplierNumber, $i, $Errors, $db) {
		if ((strlen($SupplierNumber)<1) or (strlen($SupplierNumber)>10)) {
			$Errors[$i] = IncorrectDebtorNumberLength;
		}
		$Searchsql = 'SELECT count(supplierid)
				FROM suppliers
				WHERE supplierid="'.$SupplierNumber.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] != 0) {
			$Errors[$i] = SupplierNoAlreadyExists;
		}
		return $Errors;
	}

/* Verify that the supplier number is valid, and already
   exists.*/
	function VerifySupplierNoExists($SupplierNumber, $i, $Errors, $db) {
		if ((strlen($SupplierNumber)<1) or (strlen($SupplierNumber)>10)) {
			$Errors[$i] = IncorrectDebtorNumberLength;
		}
		$Searchsql = 'SELECT count(supplierid)
				FROM suppliers
				WHERE supplierid="'.$SupplierNumber.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = SupplierNoDoesntExists;
		}
		return $Errors;
	}

/* Check that the name exists and is 40 characters or less long */
	function VerifySupplierName($SupplierName, $i, $Errors) {
		if ((strlen($SupplierName)<1) or (strlen($SupplierName)>40)) {
			$Errors[$i] = IncorrectSupplierNameLength;
		}
		return $Errors;
	}

/* Check that the supplier since date is a valid date. The date
 * must be in the same format as the date format specified in the
 * target webERP company */
	function VerifySupplierSinceDate($suppliersincedate, $i, $Errors, $db) {
		$sql='select confvalue from config where confname="'.DefaultDateFormat.'"';
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		$DateFormat=$myrow[0];
		if (strstr('/',$PeriodEnd)) {
			$Date_Array = explode('/',$PeriodEnd);
		} elseif (strstr('.',$PeriodEnd)) {
			$Date_Array = explode('.',$PeriodEnd);
		}
		if ($DateFormat=='d/m/Y') {
			$Day=$DateArray[0];
			$Month=$DateArray[1];
			$Year=$DateArray[2];
		} elseif ($DateFormat=='m/d/Y') {
			$Day=$DateArray[1];
			$Month=$DateArray[0];
			$Year=$DateArray[2];
		} elseif ($DateFormat=='Y/m/d') {
			$Day=$DateArray[2];
			$Month=$DateArray[1];
			$Year=$DateArray[0];
		} elseif ($DateFormat=='d.m.Y') {
			$Day=$DateArray[0];
			$Month=$DateArray[1];
			$Year=$DateArray[2];
		}
		if (!checkdate(intval($Month), intval($Day), intval($Year))) {
			$Errors[$i] = InvalidSupplierSinceDate;
		}
		return $Errors;
	}

	function VerifyBankAccount($BankAccount, $i, $Errors) {
		if (strlen($BankAccount)>30) {
			$Errors[$i] = InvalidBankAccount;
		}
		return $Errors;
	}

	function VerifyBankRef($BankRef, $i, $Errors) {
		if (strlen($BankRef)>12) {
			$Errors[$i] = InvalidBankReference;
		}
		return $Errors;
	}

	function VerifyBankPartics($BankPartics, $i, $Errors) {
		if (strlen($BankPartics)>12) {
			$Errors[$i] = InvalidBankPartics;
		}
		return $Errors;
	}

	function VerifyRemittance($Remittance, $i, $Errors) {
		if ($Remittance!=0 and $Remittance!=1) {
			$Errors[$i] = InvalidRemittanceFlag;
		}
		return $Errors;
	}

/* Check that the factor company is set up in the weberp database */
	function VerifyFactorCompany($factorco , $i, $Errors, $db) {
		$Searchsql = 'SELECT COUNT(id)
					 FROM factorcompanies
					  WHERE id="'.$factorco.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = FactorCompanyNotSetup;
		}
		return $Errors;
	}

/* Insert a new supplier in the webERP database. This function takes an
   associative array called $SupplierDetails, where the keys are the
   names of the fields in the suppliers table, and the values are the
   values to insert.
*/
	function InsertSupplier($SupplierDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($SupplierDetails as $key => $value) {
			$SupplierDetails[$key] = DB_escape_string($value);
		}
		$Errors=VerifySupplierNo($SupplierDetails['supplierid'], sizeof($Errors), $Errors, $db);
		$Errors=VerifySupplierName($SupplierDetails['suppname'], sizeof($Errors), $Errors);
		if (isset($SupplierDetails['address1'])){
			$Errors=VerifyAddressLine($SupplierDetails['address1'], 40, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address2'])){
			$Errors=VerifyAddressLine($SupplierDetails['address2'], 40, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address3'])){
			$Errors=VerifyAddressLine($SupplierDetails['address3'], 40, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address4'])){
			$Errors=VerifyAddressLine($SupplierDetails['address4'], 50, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address5'])){
			$Errors=VerifyAddressLine($SupplierDetails['address5'], 20, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address6'])){
			$Errors=VerifyAddressLine($SupplierDetails['address6'], 15, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['lat'])){
			$Errors=VerifyLatitude($SupplierDetails['lat'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['lng'])){
			$Errors=VerifyLongitude($SupplierDetails['lng'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['currcode'])){
			$Errors=VerifyCurrencyCode($SupplierDetails['currcode'], sizeof($Errors), $Errors, $db);
		}
		if (isset($SupplierDetails['suppliersince'])){
			$Errors=VerifySupplierSince($SupplierDetails['suppliersince'], sizeof($Errors), $Errors, $db);
		}
		if (isset($SupplierDetails['paymentterms'])){
			$Errors=VerifyPaymentTerms($SupplierDetails['paymentterms'], sizeof($Errors), $Errors, $db);
		}
		if (isset($SupplierDetails['lastpaid'])){
			$Errors=VerifyLastPaid($SupplierDetails['lastpaid'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['lastpaiddate'])){
			$Errors=VerifyLastPaidDate($SupplierDetails['lastpaiddate'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['bankact'])){
			$Errors=VerifyBankAccount($SupplierDetails['bankact'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['bankref'])){
			$Errors=VerifyBankRef($SupplierDetails['bankref'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['bankpartics'])){
			$Errors=VerifyBankPartics($SupplierDetails['bankpartics'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['remittance'])){
			$Errors=VerifyRemittance($SupplierDetails['remittance'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['taxgroupid'])){
			$Errors=VerifyTaxGroupId($SupplierDetails['taxgroupid'], sizeof($Errors), $Errors, $db);
		}
		if (isset($SupplierDetails['factorcompanyid'])){
			$Errors=VerifyFactorCompany($SupplierDetails['factorcompanyid'], sizeof($Errors), $Errors, $db);
		}
		if (isset($CustomerDetails['taxref'])){
			$Errors=VerifyTaxRef($CustomerDetails['taxref'], sizeof($Errors), $Errors);
		}
		$FieldNames='';
		$FieldValues='';
		foreach ($SupplierDetails as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		$sql = 'INSERT INTO suppliers ('.substr($FieldNames,0,-2).') '.
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

	function ModifySupplier($SupplierDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($SupplierDetails as $key => $value) {
			$SupplierDetails[$key] = DB_escape_string($value);
		}
		$Errors=VerifySupplierNoExists($SupplierDetails['supplierid'], sizeof($Errors), $Errors, $db);
		$Errors=VerifySupplierName($SupplierDetails['suppname'], sizeof($Errors), $Errors);
		if (isset($SupplierDetails['address1'])){
			$Errors=VerifyAddressLine($SupplierDetails['address1'], 40, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address2'])){
			$Errors=VerifyAddressLine($SupplierDetails['address2'], 40, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address3'])){
			$Errors=VerifyAddressLine($SupplierDetails['address3'], 40, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address4'])){
			$Errors=VerifyAddressLine($SupplierDetails['address4'], 50, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address5'])){
			$Errors=VerifyAddressLine($SupplierDetails['address5'], 20, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address6'])){
			$Errors=VerifyAddressLine($SupplierDetails['address6'], 15, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['lat'])){
			$Errors=VerifyLatitude($SupplierDetails['lat'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['lng'])){
			$Errors=VerifyLongitude($SupplierDetails['lng'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['currcode'])){
			$Errors=VerifyCurrencyCode($SupplierDetails['currcode'], sizeof($Errors), $Errors, $db);
		}
		if (isset($SupplierDetails['suppliersince'])){
			$Errors=VerifySupplierSince($SupplierDetails['suppliersince'], sizeof($Errors), $Errors, $db);
		}
		if (isset($SupplierDetails['paymentterms'])){
			$Errors=VerifyPaymentTerms($SupplierDetails['paymentterms'], sizeof($Errors), $Errors, $db);
		}
		if (isset($SupplierDetails['lastpaid'])){
			$Errors=VerifyLastPaid($SupplierDetails['lastpaid'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['lastpaiddate'])){
			$Errors=VerifyLastPaidDate($SupplierDetails['lastpaiddate'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['bankact'])){
			$Errors=VerifyBankAccount($SupplierDetails['bankact'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['bankref'])){
			$Errors=VerifyBankRef($SupplierDetails['bankref'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['bankpartics'])){
			$Errors=VerifyBankPartics($SupplierDetails['bankpartics'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['remittance'])){
			$Errors=VerifyRemittance($SupplierDetails['remittance'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['taxgroupid'])){
			$Errors=VerifyTaxGroupId($SupplierDetails['taxgroupid'], sizeof($Errors), $Errors, $db);
		}
		if (isset($SupplierDetails['factorcompanyid'])){
			$Errors=VerifyFactorCompany($SupplierDetails['factorcompanyid'], sizeof($Errors), $Errors, $db);
		}
		if (isset($CustomerDetails['taxref'])){
			$Errors=VerifyTaxRef($CustomerDetails['taxref'], sizeof($Errors), $Errors);
		}
		$sql='UPDATE suppliers SET ';
		foreach ($SupplierDetails as $key => $value) {
			$sql .= $key.'="'.$value.'", ';
		}
		$sql = substr($sql,0,-2).' WHERE supplierid="'.$SupplierDetails['supplierid'].'"';
		if (sizeof($Errors)==0) {
			$result = DB_Query($sql, $db);
			echo DB_error_no($db);
			if (DB_error_no($db) != 0) {
				$Errors[0] = DatabaseUpdateFailed;
			} else {
				$Errors[0]=0;
			}
		}
		return $Errors;
	}

/* This function takes a supplier id and returns an associative array containing
   the database record for that supplier. If the supplier id doesn't exist
   then it returns an $Errors array.
*/
	function GetSupplier($SupplierID, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$Errors = VerifySupplierNoExists($SupplierID, sizeof($Errors), $Errors, $db);
		if (sizeof($Errors)!=0) {
			return $Errors;
		}
		$sql='SELECT * FROM suppliers WHERE supplierid="'.$SupplierID.'"';
		$result = DB_Query($sql, $db);
		if (sizeof($Errors)==0) {
			return DB_fetch_array($result);
		} else {
			return $Errors;
		}
	}

/* This function takes a field name, and a string, and then returns an
   array of supplier ids that fulfill this criteria.
*/
	function SearchSuppliers($Field, $Criteria, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql='SELECT supplierid
			FROM suppliers
			WHERE '.$Field.' LIKE "%'.$Criteria.'%" ORDER BY supplierid';
		$result = DB_Query($sql, $db);
		$i=0;
		$SupplierList = array();
		while ($myrow=DB_fetch_array($result)) {
			$SupplierList[$i]=$myrow[0];
			$i++;
		}
		return $SupplierList;
	}

?>