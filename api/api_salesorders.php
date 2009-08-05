<?php

/*State the Current Revision
//revision 1.14
*/

/* Check that the custmerref field is 50 characters or less long */
	function VerifyCustomerRef($customerref, $i, $Errors) {
		if (strlen($customerref)>50) {
			$Errors[$i] = InvalidCustomerRef;
		}
		return $Errors;
	}

/* Check that the buyername field is 50 characters or less long */
	function VerifyBuyerName($buyername, $i, $Errors) {
		if (strlen($buyername)>50) {
			$Errors[$i] = InvalidBuyerName;
		}
		return $Errors;
	}

/* Check that the comments field is 256 characters or less long */
	function VerifyComments($comments, $i, $Errors) {
		if (strlen($comments)>256) {
			$Errors[$i] = InvalidComments;
		}
		return $Errors;
	}

/* Check that the order date is a valid date. The date
 * must be in the same format as the date format specified in the
 * target webERP company */
	function VerifyOrderDate($orddate, $i, $Errors, $db) {
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
			$Errors[$i] = InvalidOrderDate;
		}
		return $Errors;
	}

/* Check that the order type is set up in the weberp database */
	function VerifyOrderType($ordertype, $i, $Errors, $db) {
		$Searchsql = 'SELECT COUNT(typeabbrev)
					 FROM salestypes
					  WHERE typeabbrev="'.$ordertype.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = SalesTypeNotSetup;
		}
		return $Errors;
	}

/* Check that the delivery name field is 40 characters or less long */
	function VerifyDeliverTo($delverto, $i, $Errors) {
		if (strlen($delverto)>40) {
			$Errors[$i] = InvalidDeliverTo;
		}
		return $Errors;
	}

/* Verify that the last freight cost is numeric */
	function VerifyFreightCost($freightcost, $i, $Errors) {
		if (!is_numeric($freightcost)) {
			$Errors[$i] = InvalidFreightCost;
		}
		return $Errors;
	}

/* Check that the from stock location is set up in the weberp database */
	function VerifyFromStockLocation($fromstkloc, $i, $Errors, $db) {
		$Searchsql = 'SELECT COUNT(loccode)
					 FROM locations
					  WHERE loccode="'.$fromstkloc.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = LocationCodeNotSetup;
		}
		return $Errors;
	}

/* Check that the delivery date is a valid date. The date
 * must be in the same format as the date format specified in the
 * target webERP company */
	function VerifyDeliveryDate($deliverydate, $i, $Errors, $db) {
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
			$Errors[$i] = InvalidDeliveryDate;
		}
		return $Errors;
	}

/* Verify that the quotation flag is a 1 or 0 */
	function VerifyQuotation($quotation, $i, $Errors) {
		if ($quotation!=0 and $quotation!=1) {
			$Errors[$i] = InvalidQuotationFlag;
		}
		return $Errors;
	}

/* Fetch the next line number */
	function GetOrderLineNumber($orderno, $i, $Errors, $db) {
		$linesql = 'SELECT MAX(orderlineno)
					FROM salesorderdetails
					 WHERE orderno='.$orderno;
		$lineresult = DB_query($linesql, $db);
		if ($myrow=DB_fetch_row($lineresult)) {
			return $myrow[0] + 1;
		} else {
			return 1;
		}
	}

/* Check that the order header already exists */
	function VerifyOrderHeaderExists($orderno, $i, $Errors, $db) {
		$Searchsql = 'SELECT COUNT(orderno)
					 FROM salesorders
					  WHERE orderno="'.$orderno.'"';
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_row($SearchResult);
		if ($answer[0] == 0) {
			$Errors[$i] = OrderHeaderNotSetup;
		}
		return $Errors;
	}

/* Verify that the unit price is numeric */
	function VerifyUnitPrice($unitprice, $i, $Errors) {
		if (!is_numeric($unitprice)) {
			$Errors[$i] = InvalidUnitPrice;
		}
		return $Errors;
	}

/* Verify that the quantity is numeric */
	function VerifyQuantity($quantity, $i, $Errors) {
		if (!is_numeric($quantity)) {
			$Errors[$i] = InvalidQuantity;
		}
		return $Errors;
	}

/* Verify that the discount percent is numeric */
	function VerifyDiscountPercent($discountpercent, $i, $Errors) {
		if (!is_numeric($discountpercent) or $discountpercent>100) {
			$Errors[$i] = InvalidDiscountPercent;
		}
		return $Errors;
	}

/* Check that the narrative field is 256 characters or less long */
	function VerifyNarrative($narrative, $i, $Errors) {
		if (strlen($narrative)>256) {
			$Errors[$i] = InvalidNarrative;
		}
		return $Errors;
	}

/* Check that the poline field is 10 characters or less long */
	function VerifyPOLine($poline, $i, $Errors) {
		if (strlen($poline)>10) {
			$Errors[$i] = InvalidPOLine;
		}
		return $Errors;
	}

/* Check that the item due date is a valid date. The date
 * must be in the same format as the date format specified in the
 * target webERP company */
	function VerifyItemDueDate($itemdue, $i, $Errors, $db) {
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
			$Errors[$i] = InvalidItemDueDate;
		}
		return $Errors;
	}

/* Create a customer sales order header in webERP. If successful
 * returns $Errors[0]=0 and $Errors[1] will contain the order number.
 */
	function InsertSalesOrderHeader($OrderHeader, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($OrderHeader as $key => $value) {
			$OrderHeader[$key] = DB_escape_string($value);
		}
		$Errors=VerifyDebtorExists($OrderHeader['debtorno'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyBranchNoExists($OrderHeader['debtorno'],$OrderHeader['branchcode'], sizeof($Errors), $Errors, $db);
		if (isset($OrderHeader['customerref'])){
			$Errors=VerifyCustomerRef($OrderHeader['customerref'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['buyername'])){
			$Errors=VerifyBuyerName($OrderHeader['buyername'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['comments'])){
			$Errors=VerifyComments($OrderHeader['comments'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['orddate'])){
			$Errors=VerifyOrderDate($OrderHeader['orddate'], sizeof($Errors), $Errors, $db);
		}
		if (isset($OrderHeader['ordertype'])){
			$Errors=VerifyOrderType($OrderHeader['ordertype'], sizeof($Errors), $Errors, $db);
		}
		if (isset($OrderHeader['shipvia'])){
			$Errors=VerifyShipVia($OrderHeader['shipvia'], sizeof($Errors), $Errors, $db);
		}
		if (isset($OrderHeader['deladd1'])){
			$Errors=VerifyAddressLine($OrderHeader['deladd1'], 40, sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['deladd2'])){
			$Errors=VerifyAddressLine($OrderHeader['deladd2'], 40, sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['deladd3'])){
			$Errors=VerifyAddressLine($OrderHeader['deladd3'], 40, sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['deladd4'])){
			$Errors=VerifyAddressLine($OrderHeader['deladd4'], 40, sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['deladd5'])){
			$Errors=VerifyAddressLine($OrderHeader['deladd5'], 20, sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['deladd6'])){
			$Errors=VerifyAddressLine($OrderHeader['deladd6'], 15, sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['contactphone'])){
			$Errors=VerifyPhoneNumber($OrderHeader['contactphone'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['contactemail'])){
			$Errors=VerifyEmailAddress($OrderHeader['contactemail'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['deliverto'])){
			$Errors=VerifyDeliverTo($OrderHeader['deliverto'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['deliverblind'])){
			$Errors=VerifyDeliverBlind($OrderHeader['deliverblind'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['freightcost'])){
			$Errors=VerifyFreightCost($OrderHeader['freightcost'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['fromstkloc'])){
			$Errors=VerifyFromStockLocation($OrderHeader['fromstkloc'], sizeof($Errors), $Errors, $db);
		}
		if (isset($OrderHeader['deliverydate'])){
			$Errors=VerifyDeliveryDate($OrderHeader['deliverydate'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['quotation'])){
			$Errors=VerifyQuotation($OrderHeader['quotation'], sizeof($Errors), $Errors);
		}
		$FieldNames='';
		$FieldValues='';
		$OrderHeader['orderno'] = GetNextTransNo(30);
		foreach ($OrderHeader as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		$sql = 'INSERT INTO salesorders ('.substr($FieldNames,0,-2).') '.
		  'VALUES ('.substr($FieldValues,0,-2).') ';
		if (sizeof($Errors)==0) {
			$result = DB_Query($sql, $db);
			if (DB_error_no($db) != 0) {
				$Errors[0] = DatabaseUpdateFailed;
			} else {
				$Errors[0]=0;
				$Errors[1]=$OrderHeader['orderno'];
			}
		}
		return $Errors;
	}

/* Modify a customer sales order header in webERP.
 */
	function ModifySalesOrderHeader($OrderHeader, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($OrderHeader as $key => $value) {
			$OrderHeader[$key] = DB_escape_string($value);
		}
		$Errors=VerifyOrderHeaderExists($OrderHeader['orderno'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyDebtorExists($OrderHeader['debtorno'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyBranchNoExists($OrderHeader['debtorno'],$OrderHeader['branchcode'], sizeof($Errors), $Errors, $db);
		if (isset($OrderHeader['customerref'])){
			$Errors=VerifyCustomerRef($OrderHeader['customerref'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['buyername'])){
			$Errors=VerifyBuyerName($OrderHeader['buyername'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['comments'])){
			$Errors=VerifyComments($OrderHeader['comments'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['orddate'])){
			$Errors=VerifyOrderDate($OrderHeader['orddate'], sizeof($Errors), $Errors, $db);
		}
		if (isset($OrderHeader['ordertype'])){
			$Errors=VerifyOrderType($OrderHeader['ordertype'], sizeof($Errors), $Errors, $db);
		}
		if (isset($OrderHeader['shipvia'])){
			$Errors=VerifyShipVia($OrderHeader['shipvia'], sizeof($Errors), $Errors, $db);
		}
		if (isset($OrderHeader['deladd1'])){
			$Errors=VerifyAddressLine($OrderHeader['deladd1'], 40, sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['deladd2'])){
			$Errors=VerifyAddressLine($OrderHeader['deladd2'], 40, sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['deladd3'])){
			$Errors=VerifyAddressLine($OrderHeader['deladd3'], 40, sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['deladd4'])){
			$Errors=VerifyAddressLine($OrderHeader['deladd4'], 40, sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['deladd5'])){
			$Errors=VerifyAddressLine($OrderHeader['deladd5'], 20, sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['deladd6'])){
			$Errors=VerifyAddressLine($OrderHeader['deladd6'], 15, sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['contactphone'])){
			$Errors=VerifyPhoneNumber($OrderHeader['contactphone'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['contactemail'])){
			$Errors=VerifyEmailAddress($OrderHeader['contactemail'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['deliverto'])){
			$Errors=VerifyDeliverTo($OrderHeader['deliverto'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['deliverblind'])){
			$Errors=VerifyDeliverBlind($OrderHeader['deliverblind'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['freightcost'])){
			$Errors=VerifyFreightCost($OrderHeader['freightcost'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['fromstkloc'])){
			$Errors=VerifyFromStockLocation($OrderHeader['fromstkloc'], sizeof($Errors), $Errors, $db);
		}
		if (isset($OrderHeader['deliverydate'])){
			$Errors=VerifyDeliveryDate($OrderHeader['deliverydate'], sizeof($Errors), $Errors);
		}
		if (isset($OrderHeader['quotation'])){
			$Errors=VerifyQuotation($OrderHeader['quotation'], sizeof($Errors), $Errors);
		}
		$sql='UPDATE salesorders SET ';
		foreach ($OrderHeader as $key => $value) {
			$sql .= $key.'="'.$value.'", ';
		}
		$sql = substr($sql,0,-2).' WHERE orderno="'.$OrderHeader['orderno'].'"';
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

/* Create a customer sales order line in webERP. The order header must
 * already exist in webERP.
 */
	function InsertSalesOrderLine($OrderLine, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($OrderLine as $key => $value) {
			$OrderLine[$key] = DB_escape_string($value);
		}
		$OrderLine['orderlineno'] = GetOrderLineNumber($OrderLine['orderno'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyOrderHeaderExists($OrderLine['orderno'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyStockCodeExists($OrderLine['stkcode'], sizeof($Errors), $Errors, $db);
		if (isset($OrderLine['unitprice'])){
			$Errors=VerifyUnitPrice($OrderLine['unitprice'], sizeof($Errors), $Errors);
		}
		if (isset($OrderLine['quantity'])){
			$Errors=VerifyQuantity($OrderLine['quantity'], sizeof($Errors), $Errors);
		}
		if (isset($OrderLine['discountpercent'])){
			//$OrderLine['discountpercent'] = $OrderLine['discountpercent'] * 100;
			$Errors=VerifyDiscountPercent($OrderLine['discountpercent'], sizeof($Errors), $Errors);
			$OrderLine['discountpercent'] = $OrderLine['discountpercent']/100;
		}
		if (isset($OrderLine['narrative'])){
			$Errors=VerifyNarrative($OrderLine['narrative'], sizeof($Errors), $Errors);
		}
		if (isset($OrderLine['itemdue'])){
			$Errors=VerifyItemDueDate($OrderLine['itemdue'], sizeof($Errors), $Errors);
		}
		if (isset($OrderLine['poline'])){
			$Errors=VerifyPOLine($OrderLine['poline'], sizeof($Errors), $Errors);
		}
		$FieldNames='';
		$FieldValues='';
		foreach ($OrderLine as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		$sql = 'INSERT INTO salesorderdetails ('.substr($FieldNames,0,-2).') '.
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

/* Modify a customer sales order line in webERP. The order header must
 * already exist in webERP.
 */
	function ModifySalesOrderLine($OrderLine, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($OrderLine as $key => $value) {
			$OrderLine[$key] = DB_escape_string($value);
		}
		$Errors=VerifyOrderHeaderExists($OrderLine['orderno'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyStockCodeExists($OrderLine['stkcode'], sizeof($Errors), $Errors, $db);
		if (isset($OrderLine['unitprice'])){
			$Errors=VerifyUnitPrice($OrderLine['unitprice'], sizeof($Errors), $Errors);
		}
		if (isset($OrderLine['quantity'])){
			$Errors=VerifyQuantity($OrderLine['quantity'], sizeof($Errors), $Errors);
		}
		if (isset($OrderLine['discountpercent'])){
			//$OrderLine['discountpercent'] = $OrderLine['discountpercent'] * 100;
			$Errors=VerifyDiscountPercent($OrderLine['discountpercent'], sizeof($Errors), $Errors);
			$OrderLine['discountpercent'] = $OrderLine['discountpercent']/100;
		}
		if (isset($OrderLine['narrative'])){
			$Errors=VerifyNarrative($OrderLine['narrative'], sizeof($Errors), $Errors);
		}
		if (isset($OrderLine['itemdue'])){
			$Errors=VerifyItemDueDate($OrderLine['itemdue'], sizeof($Errors), $Errors);
		}
		if (isset($OrderLine['poline'])){
			$Errors=VerifyPOLine($OrderLine['poline'], sizeof($Errors), $Errors);
		}
		$sql='UPDATE salesorderdetails SET ';
		foreach ($OrderLine as $key => $value) {
			$sql .= $key.'="'.$value.'", ';
		}
		//$sql = substr($sql,0,-2).' WHERE orderno="'.$OrderLine['orderno'].'" and
			//	" orderlineno='.$OrderLine['orderlineno'];
		$sql = substr($sql,0,-2).' WHERE orderno="'.$OrderLine['orderno'].'" and stkcode="'.$OrderLine['stkcode'].'"';
				//echo $sql;
				//exit;
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
	
/* This function takes a Order Header ID  and returns an associative array containing
   the database record for that Order. If the Order Header ID doesn't exist
   then it returns an $Errors array.
*/
	function GetSalesOrderHeader($OrderNo, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$Errors=VerifyOrderHeaderExists($OrderNo, sizeof($Errors), $Errors, $db);
		if (sizeof($Errors)!=0) {
			return $Errors;
		}
		$sql='SELECT * FROM salesorders WHERE orderno="'.$OrderNo.'"';
		$result = DB_Query($sql, $db);
		if (sizeof($Errors)==0) {
			return DB_fetch_array($result);
		} else {
			return $Errors;
		}
	}
	
/* This function takes a Order Header ID  and returns an associative array containing
   the database record for that Order. If the Order Header ID doesn't exist
   then it returns an $Errors array.
*/
	function GetSalesOrderLine($OrderNo, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$Errors=VerifyOrderHeaderExists($OrderNo, sizeof($Errors), $Errors, $db);
		if (sizeof($Errors)!=0) {
			return $Errors;
		}
		$sql='SELECT * FROM salesorderdetails WHERE orderno="'.$OrderNo.'"';
		$result = DB_Query($sql, $db);
		if (sizeof($Errors)==0) {
			return DB_fetch_array($result);
		} else {
			return $Errors;
		}
	}
?>