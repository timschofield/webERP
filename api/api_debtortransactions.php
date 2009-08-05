<?php

/* Check that the transaction number is unique
 * for this type of transaction*/
	function VerifyTransNo($TransNo, $Type, $i, $Errors, $db) {
		$Searchsql = "SELECT count(transno)
				FROM debtortrans
				WHERE type=".$Type." and transno=".$TransNo;
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_array($SearchResult);
		if ($answer[0]>0) {
			$Errors[$i] = TransactionNumberAlreadyExists;
		}
		return $Errors;
	}

function ConvertToSQLDate($DateEntry) {

//for MySQL dates are in the format YYYY-mm-dd


	if (strpos($DateEntry,'/')) {
		$Date_Array = explode('/',$DateEntry);
	} elseif (strpos ($DateEntry,'-')) {
		$Date_Array = explode('-',$DateEntry);
	} elseif (strpos ($DateEntry,'.')) {
		$Date_Array = explode('.',$DateEntry);
	}

	if (strlen($Date_Array[2])>4) {  /*chop off the time stuff */
		$Date_Array[2]= substr($Date_Array[2],0,2);
	}


	if ($_SESSION['DefaultDateFormat']=='d/m/Y'){
		return $Date_Array[2].'-0'.$Date_Array[1].'-'.$Date_Array[0];
	} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y'){
		return $Date_Array[1].'/'.$Date_Array[2].'/'.$Date_Array[0];
	} elseif ($_SESSION['DefaultDateFormat']=='Y/m/d'){
		return $Date_Array[0].'/'.$Date_Array[1].'/'.$Date_Array[2];
	} elseif ($_SESSION['DefaultDateFormat']=='d.m.Y'){
		return $Date_Array[2].'/'.$Date_Array[1].'/'.$Date_Array[0];
	}

} // end function ConvertSQLDate

/* Check that the transaction date is a valid date. The date
 * must be in the same format as the date format specified in the
 * target webERP company */
	function VerifyTransactionDate($TranDate, $i, $Errors, $db) {
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
			$Errors[$i] = InvalidCurCostDate;
		}
		return $Errors;
	}

/* Find the period number from the transaction date */
	function GetPeriodFromTransactionDate($TranDate, $i, $Errors, $db) {
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
		$DateArray=explode('-',$TranDate);
		$Day=$DateArray[2];
		$Month=$DateArray[1];
		$Year=$DateArray[0];
		$Date=$Year.'-'.$Month.'-'.$Day;
		$sql='select max(periodno) from periods where lastdate_in_period<="'.$Date.'"';
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		return $myrow[0];
	}

/* Verify that the Settled flag is a 1 or 0 */
	function VerifySettled($Settled, $i, $Errors) {
		if ($Settled!=0 and $Settled!=1) {
			$Errors[$i] = InvalidSettled;
		}
		return $Errors;
	}

/* Check that the transaction reference is 20 characters
 *  or less long */
	function VerifyReference($reference, $i, $Errors) {
		if (strlen($reference)>20) {
			$Errors[$i] = IncorrectReference;
		}
		return $Errors;
	}

/* Check that the tpe field is 2 characters or less long */
	function VerifyTpe($tpe, $i, $Errors) {
		if (strlen($tpe)>2) {
			$Errors[$i] = IncorrectTpe;
		}
		return $Errors;
	}

/* Verify that the order number is numeric */
	function VerifyOrderNumber($order, $i, $Errors) {
		if (!is_numeric($order)) {
			$Errors[$i] = InvalidOrderNumbers;
		}
		return $Errors;
	}

/* Verify that the exchange rate is numeric */
	function VerifyExchangeRate($rate, $i, $Errors) {
		if (!is_numeric($rate)) {
			$Errors[$i] = InvalidExchangeRate;
		}
		return $Errors;
	}

/* Verify that the ovamount is numeric */
	function VerifyOVAmount($ovamount, $i, $Errors) {
		if (!is_numeric($ovamount)) {
			$Errors[$i] = InvalidOVAmount;
		}
		return $Errors;
	}

/* Verify that the ovgst is numeric */
	function VerifyOVGst($ovgst, $i, $Errors) {
		if (!is_numeric($ovgst)) {
			$Errors[$i] = InvalidOVGst;
		}
		return $Errors;
	}

/* Verify that the ovfreight is numeric */
	function VerifyOVFreight($ovfreight, $i, $Errors) {
		if (!is_numeric($ovfreight)) {
			$Errors[$i] = InvalidOVFreight;
		}
		return $Errors;
	}

/* Verify that the ovdiscount is numeric */
	function VerifyOVDiscount($ovdiscount, $i, $Errors) {
		if (!is_numeric($ovdiscount)) {
			$Errors[$i] = InvalidOVDiscount;
		}
		return $Errors;
	}

/* Verify that the diffonexch is numeric */
	function VerifyDiffOnExchange($diffonexch, $i, $Errors) {
		if (!is_numeric($diffonexch)) {
			$Errors[$i] = InvalidDiffOnExchange;
		}
		return $Errors;
	}

/* Verify that the allocated figure is numeric */
	function VerifyAllocated($alloc, $i, $Errors) {
		if (!is_numeric($alloc)) {
			$Errors[$i] = InvalidAllocation;
		}
		return $Errors;
	}

/* Check that the invoice text is 256 characters or less long */
	function VerifyInvoiceText($invtext, $i, $Errors) {
		if (strlen($invtext)>256) {
			$Errors[$i] = IncorrectInvoiceText;
		}
		return $Errors;
	}

/* Check that the ship via field is 10 characters or less long */
	function VerifyShipVia($shipvia, $i, $Errors) {
		if (strlen($shipvia)>10) {
			$Errors[$i] = InvalidShipVia;
		}
		return $Errors;
	}

/* Verify that the edisent flag is a 1 or 0 */
	function VerifyEdiSent($edisent, $i, $Errors) {
		if ($edisent!=0 and $edisent!=1) {
			$Errors[$i] = InvalidEdiSent;
		}
		return $Errors;
	}

/* Check that the consignment field is 15 characters or less long */
	function VerifyConsignment($consignment, $i, $Errors) {
		if (strlen($consignment)>15) {
			$Errors[$i] = InvalidConsignment;
		}
		return $Errors;
	}

/* Retrieves the default sales GL code for a given part code and sales area */
	function GetSalesGLCode($salesarea, $partnumber, $db) {
		$sql='select salesglcode from salesglpostings
			where stkcat="any"';
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		return $myrow[0];
	}

/* Retrieves the default debtors code for webERP */
	function GetDebtorsGLCode($db) {
		$sql='select debtorsact from companies';
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		return $myrow[0];
	}

/* Retrieves the next transaction number for the given type */
	function GetNextTransactionNo($type, $db) {
		$sql='select typeno from systypes where typeid='.$type;
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		$NextTransaction=$myrow[0]+1;
		return $NextTransaction;
	}

/* Create a customer invoice in webERP. This function will bypass the
 * normal procedure in webERP for creating a sales order first, and then
 * delivering it.
 */
	function InsertSalesInvoice($InvoiceDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($InvoiceDetails as $key => $value) {
			$InvoiceDetails[$key] = DB_escape_string($value);
		}
		$PartCode=$InvoiceDetails['partcode'];
		$Errors=VerifyStockCodeExists($PartCode, sizeof($Errors), $Errors, $db );
		unset($InvoiceDetails['partcode']);
		$SalesArea=$InvoiceDetails['salesarea'];
		unset($InvoiceDetails['salesarea']);
		$InvoiceDetails['transno']=GetNextTransactionNo(10, $db);
		$InvoiceDetails['type'] = 10;
		$Errors=VerifyDebtorExists($InvoiceDetails['debtorno'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyBranchNoExists($InvoiceDetails['debtorno'],$InvoiceDetails['branchcode'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyTransNO($InvoiceDetails['transno'], 10, sizeof($Errors), $Errors, $db);
		$Errors=VerifyTransactionDate($InvoiceDetails['trandate'], sizeof($Errors), $Errors, $db);
		if (isset($InvoiceDetails['settled'])){
			$Errors=VerifySettled($InvoiceDetails['settled'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['reference'])){
			$Errors=VerifyReference($InvoiceDetails['reference'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['tpe'])){
			$Errors=VerifyTpe($InvoiceDetails['tpe'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['order_'])){
			$Errors=VerifyOrderNumber($InvoiceDetails['order_'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['rate'])){
			$Errors=VerifyExchangeRate($InvoiceDetails['rate'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['ovamount'])){
			$Errors=VerifyOVAmount($InvoiceDetails['ovamount'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['ovgst'])){
			$Errors=VerifyOVGst($InvoiceDetails['ovgst'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['ovfreight'])){
			$Errors=VerifyOVFreight($InvoiceDetails['ovfreight'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['ovdiscount'])){
			$Errors=VerifyOVDiscount($InvoiceDetails['ovdiscount'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['diffonexch'])){
			$Errors=VerifyDiffOnExchange($InvoiceDetails['diffonexch'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['alloc'])){
			$Errors=VerifyAllocated($InvoiceDetails['alloc'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['invtext'])){
			$Errors=VerifyInvoiceText($InvoiceDetails['invtext'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['shipvia'])){
			$Errors=VerifyShipVia($InvoiceDetails['shipvia'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['edisent'])){
			$Errors=VerifyEdiSent($InvoiceDetails['edisent'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['consignment'])){
			$Errors=VerifyConsignment($InvoiceDetails['consignment'], sizeof($Errors), $Errors);
		}
		$FieldNames='';
		$FieldValues='';
		$InvoiceDetails['trandate']=ConvertToSQLDate($InvoiceDetails['trandate']);
		$InvoiceDetails['prd']=GetPeriodFromTransactionDate($InvoiceDetails['trandate'], sizeof($Errors), $Errors, $db);
		foreach ($InvoiceDetails as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		if (sizeof($Errors)==0) {
			$result = DB_Txn_Begin($db);
			$sql = 'INSERT INTO debtortrans ('.substr($FieldNames,0,-2).') '.
		  		'VALUES ('.substr($FieldValues,0,-2).') ';
			$result = DB_Query($sql, $db);
			$sql = 'UPDATE systypes set typeno='.GetNextTransactionNo(10, $db).' where typeid=10';
			$result = DB_Query($sql, $db);
			$SalesGLCode=GetSalesGLCode($SalesArea, $PartCode, $db);
			$DebtorsGLCode=GetDebtorsGLCode($db);
			$sql='insert into gltrans Values(null, 10,'.GetNextTransactionNo(10, $db).
				',0,"'.$InvoiceDetails['trandate'].'",'.$InvoiceDetails['prd'].', '.$DebtorsGLCode.
				',"'.'Invoice for -'.$InvoiceDetails['debtorno'].' Total - '.$InvoiceDetails['ovamount'].
				'", '.$InvoiceDetails['ovamount'].', 0,"'.$InvoiceDetails['jobref'].'",1)';
			$result = DB_Query($sql, $db);
			$sql='insert into gltrans Values(null, 10,'.GetNextTransactionNo(10, $db).
				',0,"'.$InvoiceDetails['trandate'].'",'.$InvoiceDetails['prd'].', '.$SalesGLCode.
				',"'.'Invoice for -'.$InvoiceDetails['debtorno'].' Total - '.$InvoiceDetails['ovamount'].
				'", '.-intval($InvoiceDetails['ovamount']).', 0,"'.$InvoiceDetails['jobref'].'",1)';
			$result = DB_Query($sql, $db);
			$result= DB_Txn_Commit($db);
			if (DB_error_no($db) != 0) {
				$Errors[0] = DatabaseUpdateFailed;
			} else {
				$Errors[0]=0;
			}
		} else {
			return $Errors;
		}
	}

/* Create a customer credit note in webERP. This function will bypass the
 * normal procedure in webERP for creating a sales order first, and then
 * delivering it. All values should be sent as negatives.
 */
	function InsertSalesCredit($CreditDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($CreditDetails as $key => $value) {
			$CreditDetails[$key] = DB_escape_string($value);
		}
		$PartCode=$CreditDetails['partcode'];
		$Errors=VerifyStockCodeExists($PartCode, sizeof($Errors), $Errors, $db );
		unset($CreditDetails['partcode']);
		$SalesArea=$CreditDetails['salesarea'];
		unset($CreditDetails['salesarea']);
		$CreditDetails['transno']=GetNextTransactionNo(11, $db);
		$CreditDetails['type'] = 10;
		$Errors=VerifyDebtorExists($CreditDetails['debtorno'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyBranchNoExists($CreditDetails['debtorno'],$CreditDetails['branchcode'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyTransNO($CreditDetails['transno'], 10, sizeof($Errors), $Errors, $db);
		$Errors=VerifyTransactionDate($CreditDetails['trandate'], sizeof($Errors), $Errors, $db);
		if (isset($CreditDetails['settled'])){
			$Errors=VerifySettled($CreditDetails['settled'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['reference'])){
			$Errors=VerifyReference($CreditDetails['reference'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['tpe'])){
			$Errors=VerifyTpe($CreditDetails['tpe'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['order_'])){
			$Errors=VerifyOrderNumber($CreditDetails['order_'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['rate'])){
			$Errors=VerifyExchangeRate($CreditDetails['rate'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['ovamount'])){
			$Errors=VerifyOVAmount($CreditDetails['ovamount'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['ovgst'])){
			$Errors=VerifyOVGst($CreditDetails['ovgst'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['ovfreight'])){
			$Errors=VerifyOVFreight($CreditDetails['ovfreight'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['ovdiscount'])){
			$Errors=VerifyOVDiscount($CreditDetails['ovdiscount'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['diffonexch'])){
			$Errors=VerifyDiffOnExchange($CreditDetails['diffonexch'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['alloc'])){
			$Errors=VerifyAllocated($CreditDetails['alloc'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['invtext'])){
			$Errors=VerifyInvoiceText($CreditDetails['invtext'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['shipvia'])){
			$Errors=VerifyShipVia($CreditDetails['shipvia'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['edisent'])){
			$Errors=VerifyEdiSent($CreditDetails['edisent'], sizeof($Errors), $Errors);
		}
		if (isset($CreditDetails['consignment'])){
			$Errors=VerifyConsignment($CreditDetails['consignment'], sizeof($Errors), $Errors);
		}
		$FieldNames='';
		$FieldValues='';
		$CreditDetails['trandate']=ConvertToSQLDate($CreditDetails['trandate']);
		$CreditDetails['prd']=GetPeriodFromTransactionDate($CreditDetails['trandate'], sizeof($Errors), $Errors, $db);
		foreach ($CreditDetails as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		if (sizeof($Errors)==0) {
			$result = DB_Txn_Begin($db);
			$sql = 'INSERT INTO debtortrans ('.substr($FieldNames,0,-2).') '.
		  		'VALUES ('.substr($FieldValues,0,-2).') ';
			$result = DB_Query($sql, $db);
			$sql = 'UPDATE systypes set typeno='.GetNextTransactionNo(11, $db).' where typeid=10';
			$result = DB_Query($sql, $db);
			$SalesGLCode=GetSalesGLCode($SalesArea, $PartCode, $db);
			$DebtorsGLCode=GetDebtorsGLCode($db);
			$sql='insert into gltrans Values("null", 10,'.GetNextTransactionNo(11, $db).
				',0,"'.$CreditDetails['trandate'].'",'.$CreditDetails['prd'].', '.$DebtorsGLCode.
				',"'.'Invoice for -'.$CreditDetails['debtorno'].' Total - '.$CreditDetails['ovamount'].
				'", '.$CreditDetails['ovamount'].', 0,"'.$CreditDetails['jobref'].'")';
			$result = DB_Query($sql, $db);
			$sql='insert into gltrans Values("null", 10,'.GetNextTransactionNo(11, $db).
				',0,"'.$CreditDetails['trandate'].'",'.$CreditDetails['prd'].', '.$SalesGLCode.
				',"'.'Invoice for -'.$CreditDetails['debtorno'].' Total - '.$CreditDetails['ovamount'].
				'", '.-intval($CreditDetails['ovamount']).', 0,"'.$CreditDetails['jobref'].'")';
			$result = DB_Query($sql, $db);
			$result= DB_Txn_Commit($db);
			if (DB_error_no($db) != 0) {
				$Errors[0] = DatabaseUpdateFailed;
			} else {
				$Errors[0]=0;
			}
		} else {
			return $Errors;
		}
	}

?>