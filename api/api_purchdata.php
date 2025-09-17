<?php

if (!isset($PathPrefix)) {
	header('Location: ../');
	exit();
}

function VerifyPurchDataLineExists($SupplierID, $StockID, $i, $Errors) {
	if (VerifyStockCodeExists($StockID, $i, $Errors)!=0 and
		VerifySupplierNoExists($SupplierID, $i, $Errors)!=0) {
			$Errors[$i] = StockSupplierLineDoesntExist;
	}
	return $Errors;
}

function VerifySuppliersUOM($suppliersuom, $i, $Errors) {
	if (mb_strlen($suppliersuom)>50) {
		$Errors[$i] = InvalidSuppliersUOM;
	}
	return $Errors;
}

/** Verify that the conversion factor figure is numeric */
function VerifyConversionFactor($ConversionFactor, $i, $Errors) {
	if (!is_numeric($ConversionFactor)) {
		$Errors[$i] = InvalidConversionFactor;
	}
	return $Errors;
}

function VerifySupplierDescription($supplierdescription, $i, $Errors) {
	if (mb_strlen($supplierdescription)>50) {
		$Errors[$i] = InvalidSupplierDescription;
	}
	return $Errors;
}

/** Verify that the lead time is numeric */
function VerifyLeadTime($LeadTime, $i, $Errors) {
	if (!is_numeric($LeadTime)) {
		$Errors[$i] = InvalidLeadTime;
	}
	return $Errors;
}

/** Verify that the Preferred flag is a 1 or 0 */
function VerifyPreferredFlag($Preferred, $i, $Errors) {
	if ($Preferred!=0 and $Preferred!=1) {
		$Errors[$i] = InvalidPreferredFlag;
	}
	return $Errors;
}

function InsertPurchData($PurchDataDetails, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	foreach ($PurchDataDetails as $key => $Value) {
		$PurchDataDetails[$key] = DB_escape_string($Value);
	}
	$Errors=VerifyStockCodeExists($PurchDataDetails['stockid'], sizeof($Errors), $Errors);
	$Errors=VerifySupplierNoExists($PurchDataDetails['supplierno'], sizeof($Errors), $Errors);
	if (isset($StockItemDetails['price'])){
		$Errors=VerifyUnitPrice($PurchDataDetails['price'], sizeof($Errors), $Errors);
	}
	if (isset($StockItemDetails['suppliersuom'])){
		$Errors=VerifySuppliersUOM($PurchDataDetails['suppliersuom'], sizeof($Errors), $Errors);
	}
	if (isset($StockItemDetails['conversionfactor'])){
		$Errors=VerifyConversionFactor($PurchDataDetails['conversionfactor'], sizeof($Errors), $Errors);
	}
	if (isset($StockItemDetails['supplierdescription'])){
		$Errors=VerifySupplierDescription($PurchDataDetails['supplierdescription'], sizeof($Errors), $Errors);
	}
	if (isset($StockItemDetails['leadtime'])){
		$Errors=VerifyLeadTime($PurchDataDetails['leadtime'], sizeof($Errors), $Errors);
	}
	if (isset($StockItemDetails['preferred'])){
		$Errors=VerifyPreferredFlag($PurchDataDetails['preferred'], sizeof($Errors), $Errors);
	}
	$FieldNames='';
	$FieldValues='';
	foreach ($PurchDataDetails as $key => $Value) {
		$FieldNames.=$key.', ';
		$FieldValues.='"'.$Value.'", ';
	}
	if (sizeof($Errors)==0) {
		$SQL = "INSERT INTO purchdata (".mb_substr($FieldNames,0,-2).")
				VALUES ('" . mb_substr($FieldValues,0,-2). "') ";
		DB_Txn_Begin();
		$Result = DB_query($SQL);
		DB_Txn_Commit();
		if (DB_error_no() != 0) {
			$Errors[0] = DatabaseUpdateFailed;
		} else {
			$Errors[0]=0;
		}
	}
	return $Errors;
}

function ModifyPurchData($PurchDataDetails, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	foreach ($PurchDataDetails as $key => $Value) {
		$PurchDataDetails[$key] = DB_escape_string($Value);
	}
	$Errors=VerifyPurchDataLineExists($PurchDataDetails['supplierno'], $PurchDataDetails['stockid'], sizeof($Errors), $Errors);
	$Errors=VerifyStockCodeExists($PurchDataDetails['stockid'], sizeof($Errors), $Errors);
	$Errors=VerifySupplierNoExists($PurchDataDetails['supplierno'], sizeof($Errors), $Errors);
	if (isset($StockItemDetails['price'])){
		$Errors=VerifyUnitPrice($PurchDataDetails['price'], sizeof($Errors), $Errors);
	}
	if (isset($StockItemDetails['suppliersuom'])){
		$Errors=VerifySuppliersUOM($PurchDataDetails['suppliersuom'], sizeof($Errors), $Errors);
	}
	if (isset($StockItemDetails['conversionfactor'])){
		$Errors=VerifyConversionFactor($PurchDataDetails['conversionfactor'], sizeof($Errors), $Errors);
	}
	if (isset($StockItemDetails['supplierdescription'])){
		$Errors=VerifySupplierDescription($PurchDataDetails['supplierdescription'], sizeof($Errors), $Errors);
	}
	if (isset($StockItemDetails['leadtime'])){
		$Errors=VerifyLeadTime($PurchDataDetails['leadtime'], sizeof($Errors), $Errors);
	}
	if (isset($StockItemDetails['preferred'])){
		$Errors=VerifyPreferredFlag($PurchDataDetails['preferred'], sizeof($Errors), $Errors);
	}
	$SQL="UPDATE purchdata SET ";
	foreach ($PurchDataDetails as $key => $Value) {
		$SQL .= $key."='" . $Value."', ";
	}
	$SQL = mb_substr($SQL,0,-2) . " WHERE stockid='" . $PurchDataDetails['stockid'] ."'
							AND supplierno='" . $PurchDataDetails['supplierno'] ."'";
	if (sizeof($Errors)==0) {
		$Result = DB_query($SQL);
		echo DB_error_no();
		if (DB_error_no() != 0) {
			$Errors[0] = DatabaseUpdateFailed;
		} else {
			$Errors[0]=0;
		}
	}
	return $Errors;
}
