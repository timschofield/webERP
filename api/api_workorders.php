<?php

	function VerifyRequiredByDate($RequiredByDate, $i, $Errors, $db) {
		$sql='select confvalue from config where confname="'.DefaultDateFormat.'"';
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		$DateFormat=$myrow[0];
		$DateArray=explode('/',$RequiredByDate);
		if ($DateFormat=='d/m/Y') {
			$Day=$DateArray[0];
			$Month=$DateArray[1];
			$Year=$DateArray[2];
		}
		if ($DateFormat=='m/d/Y') {
			$Day=$DateArray[1];
			$Month=$DateArray[0];
			$Year=$DateArray[2];
		}
		if ($DateFormat=='Y/m/d') {
			$Day=$DateArray[2];
			$Month=$DateArray[1];
			$Year=$DateArray[0];
		}
		if (!checkdate(intval($Month), intval($Day), intval($Year))) {
			$Errors[$i] = InvalidRequiredByDate;
		}
		return $Errors;
	}

	function VerifyStartDate($StartDate, $i, $Errors, $db) {
		$sql='select confvalue from config where confname="'.DefaultDateFormat.'"';
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		$DateFormat=$myrow[0];
		$DateArray=explode('/',$StartDate);
		if ($DateFormat=='d/m/Y') {
			$Day=$DateArray[0];
			$Month=$DateArray[1];
			$Year=$DateArray[2];
		}
		if ($DateFormat=='m/d/Y') {
			$Day=$DateArray[1];
			$Month=$DateArray[0];
			$Year=$DateArray[2];
		}
		if ($DateFormat=='Y/m/d') {
			$Day=$DateArray[2];
			$Month=$DateArray[1];
			$Year=$DateArray[0];
		}
		if (!checkdate(intval($Month), intval($Day), intval($Year))) {
			$Errors[$i] = InvalidStartDate;
		}
		return $Errors;
	}

	function VerifyCostIssued($CostIssued, $i, $Errors) {
		if (!is_numeric($CostIssued)) {
			$Errors[$i] = InvalidCostIssued;
		}
		return $Errors;
	}

	function VerifyQtyReqd($QtyReqd, $i, $Errors) {
		if (!is_numeric($QtyReqd)) {
			$Errors[$i] = InvalidQuantityRequired;
		}
		return $Errors;
	}

	function VerifyQtyRecd($QtyRecd, $i, $Errors) {
		if (!is_numeric($QtyRecd)) {
			$Errors[$i] = InvalidQuantityReceived;
		}
		return $Errors;
	}

	function VerifyStdCost($StdCost, $i, $Errors) {
		if (!is_numeric($StdCost)) {
			$Errors[$i] = InvalidStandardCost;
		}
		return $Errors;
	}

	function VerifyLotSerialNumber($nextlotsnref, $i, $Errors) {
		if (strlen($nextlotsnref)>20) {
			$Errors[$i] = IncorrectSerialNumber;
		}
		return $Errors;
	}

	function InsertWorkOrder($WorkOrderDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($WorkOrderDetails as $key => $value) {
			$WorkOrderDetails[$key] = DB_escape_string($value);
		}
		$WorkOrder['wo']=GetNextTransactionNo(40, $db);
		$WorkOrderItem['wo']=$WorkOrder['wo'];
		if (isset($WorkOrderDetails['loccode'])){
			$Errors=VerifyFromStockLocation($WorkOrderDetails['loccode'], sizeof($Errors), $Errors, $db);
			$WorkOrder['loccode']=$WorkOrderDetails['loccode'];
		}
		if (isset($WorkOrderDetails['requiredby'])){
			$Errors=VerifyRequiredByDate($WorkOrderDetails['requiredby'], sizeof($Errors), $Errors, $db);
			$WorkOrder['requiredby']=$WorkOrderDetails['requiredby'];
		}
		if (isset($WorkOrderDetails['startdate'])){
			$Errors=VerifyStartDate($WorkOrderDetails['startdate'], sizeof($Errors), $Errors, $db);
			$WorkOrder['startdate']=$WorkOrderDetails['startdate'];
		}
		if (isset($WorkOrderDetails['costissued'])){
			$Errors=VerifyCostIssued($WorkOrderDetails['costissued'], sizeof($Errors), $Errors, $db);
			$WorkOrder['costissued']=$WorkOrderDetails['costissued'];
		}
		if (isset($WorkOrderDetails['completed'])){
			$Errors=VerifyCompleted($WorkOrderDetails['completed'], sizeof($Errors), $Errors);
			$WorkOrder['completed']=$WorkOrderDetails['completed'];
		}
		if (isset($WorkOrderDetails['stockid'])){
			$Errors=VerifyStockCodeExists($WorkOrderDetails['stockid'], sizeof($Errors), $Errors, $db);
			$WorkOrderItem['stockid']=$WorkOrderDetails['stockid'];
		}
		if (isset($WorkOrderDetails['qtyreqd'])){
			$Errors=VerifyQtyReqd($WorkOrderDetails['qtyreqd'], sizeof($Errors), $Errors);
			$WorkOrderItem['qtyreqd']=$WorkOrderDetails['qtyreqd'];
		}
		if (isset($WorkOrderDetails['qtyrecd'])){
			$Errors=VerifyQtyRecd($WorkOrderDetails['qtyrecd'], sizeof($Errors), $Errors);
			$WorkOrderItem['qtyrecd']=$WorkOrderDetails['qtyrecd'];
		}
		if (isset($WorkOrderDetails['stdcost'])){
			$Errors=VerifyStdCost($WorkOrderDetails['stdcost'], sizeof($Errors), $Errors);
			$WorkOrderItem['stdcost']=$WorkOrderDetails['stdcost'];
		}
		if (isset($WorkOrderDetails['nextlotsnref'])){
			$Errors=VerifyLotSerialNumber($WorkOrderDetails['nextlotsnref'], sizeof($Errors), $Errors);
			$WorkOrderItem['nextlotsnref']=$WorkOrderDetails['nextlotsnref'];
		}

		$WOFieldNames='';
		$WOFieldValues='';
		foreach ($WorkOrder as $key => $value) {
			$WOFieldNames.=$key.', ';
			$WOFieldValues.='"'.$value.'", ';
		}
		$ItemFieldNames='';
		$ItemFieldValues='';
		foreach ($WorkOrderItem as $key => $value) {
			$ItemFieldNames.=$key.', ';
			$ItemFieldValues.='"'.$value.'", ';
		}
		if (sizeof($Errors)==0) {
			$wosql = 'INSERT INTO workorders ('.substr($WOFieldNames,0,-2).') '.
		  		'VALUES ('.substr($WOFieldValues,0,-2).') ';
			$itemsql = 'INSERT INTO woitems ('.substr($ItemFieldNames,0,-2).') '.
		  		'VALUES ('.substr($ItemFieldValues,0,-2).') ';
			$systypessql = 'UPDATE systypes set typeno='.GetNextTransactionNo(40, $db).' where typeid=40';
			DB_query('START TRANSACTION', $db);
			$woresult = DB_Query($wosql, $db);
			$itemresult = DB_Query($itemsql, $db);
			$systyperesult = DB_Query($systypessql, $db);
			DB_query('COMMIT', $db);
			if (DB_error_no($db) != 0) {
				$Errors[0] = DatabaseUpdateFailed;
			} else {
				$Errors[0]=0;
			}
		}
		return $Errors;
	}

?>
