<?php

if (!isset($PathPrefix)) {
	header('Location: ../');
	exit();
}

function VerifyCategoryID($CategoryID, $i, $Errors) {
	if (mb_strlen($CategoryID)>6 or $CategoryID=='') {
		$Errors[$i] = InvalidCategoryID;
	}
	return $Errors;
}

/** Verify the category doesnt exist */
function VerifyStockCategoryAlreadyExists($StockCategory, $i, $Errors) {
	$Searchsql = "SELECT count(categoryid)
				  FROM stockcategory
				  WHERE categoryid='".$StockCategory."'";
	$SearchResult = DB_query($Searchsql);
	$Answer = DB_fetch_array($SearchResult);
	if ($Answer[0]>0) {
		$Errors[$i] = StockCategoryAlreadyExists;
	}
	return $Errors;
}

function VerifyCategoryDescription($CategoryDescription, $i, $Errors) {
	if (mb_strlen($CategoryDescription)>20 or $CategoryDescription=='') {
		$Errors[$i] = InvalidCategoryDescription;
	}
	return $Errors;
}

function VerifyStockType($StockType, $i, $Errors) {
	if (mb_strlen($StockType)>1 or $StockType=='') {
		$Errors[$i] = InvalidStockType;
	}
	if ($StockType!='F' and $StockType!='M' and $StockType!='D' and $StockType!='L') {
		$Errors[$i] = InvalidStockType;
	}
	return $Errors;
}

function InsertStockCategory($CategoryDetails, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	foreach ($CategoryDetails as $key => $Value) {
		$CategoryDetails[$key] = DB_escape_string($Value);
	}
	$Errors=VerifyStockCategoryAlreadyExists($CategoryDetails['categoryid'], sizeof($Errors), $Errors);
	$Errors=VerifyCategoryID($CategoryDetails['categoryid'], sizeof($Errors), $Errors);
	$Errors=VerifyCategoryDescription($CategoryDetails['categorydescription'], sizeof($Errors), $Errors);
	$Errors=VerifyStockType($CategoryDetails['stocktype'], sizeof($Errors), $Errors);
	$Errors=VerifyAccountCodeExists($CategoryDetails['stockact'], sizeof($Errors), $Errors);
	$Errors=VerifyAccountCodeExists($CategoryDetails['adjglact'], sizeof($Errors), $Errors);
	$Errors=VerifyAccountCodeExists($CategoryDetails['purchpricevaract'], sizeof($Errors), $Errors);
	$Errors=VerifyAccountCodeExists($CategoryDetails['materialuseagevarac'], sizeof($Errors), $Errors);
	$Errors=VerifyAccountCodeExists($CategoryDetails['wipact'], sizeof($Errors), $Errors);
	$FieldNames='';
	$FieldValues='';
	foreach ($CategoryDetails as $key => $Value) {
		$FieldNames.=$key.', ';
		$FieldValues.='"'.$Value.'", ';
	}
	$SQL = "INSERT INTO stockcategory ('" . mb_substr($FieldNames,0,-2) . "')
			VALUES ('" . mb_substr($FieldValues,0,-2) . "') ";
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

function ModifyStockCategory($CategoryDetails, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	foreach ($CategoryDetails as $key => $Value) {
		$CategoryDetails[$key] = DB_escape_string($Value);
	}
	$Errors=VerifyStockCategoryExists($CategoryDetails['categoryid'], sizeof($Errors), $Errors);
	$Errors=VerifyCategoryID($CategoryDetails['categoryid'], sizeof($Errors), $Errors);
	$Errors=VerifyCategoryDescription($CategoryDetails['categorydescription'], sizeof($Errors), $Errors);
	$Errors=VerifyStockType($CategoryDetails['stocktype'], sizeof($Errors), $Errors);
	$Errors=VerifyAccountCodeExists($CategoryDetails['stockact'], sizeof($Errors), $Errors);
	$Errors=VerifyAccountCodeExists($CategoryDetails['adjglact'], sizeof($Errors), $Errors);
	$Errors=VerifyAccountCodeExists($CategoryDetails['purchpricevaract'], sizeof($Errors), $Errors);
	$Errors=VerifyAccountCodeExists($CategoryDetails['materialuseagevarac'], sizeof($Errors), $Errors);
	$Errors=VerifyAccountCodeExists($CategoryDetails['wipact'], sizeof($Errors), $Errors);
	$FieldNames='';
	$FieldValues='';
	foreach ($CategoryDetails as $key => $Value) {
		$FieldNames.=$key.', ';
		$FieldValues.='"'.$Value.'", ';
	}
	$SQL="UPDATE stockcategory SET ";
	foreach ($CategoryDetails as $key => $Value) {
		$SQL .= $key . "='" .$Value. "', ";
	}
	$SQL = mb_substr($SQL,0,-2)." WHERE categoryid='" . $CategoryDetails['categoryid'] . "'";
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

/** This function takes a categoryid and returns an associative array containing
   the database record for that category. If the category doesn't exist
   then it returns an $Errors array.
*/
function GetStockCategory($Categoryid, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$Errors=VerifyStockCategoryExists($Categoryid, sizeof($Errors), $Errors);
	if (sizeof($Errors)!=0) {
		return $Errors;
	}
	$SQL="SELECT * FROM stockcategory WHERE categoryid='".$Categoryid."'";
	$Result = DB_query($SQL);
	if (sizeof($Errors)==0) {
		return DB_fetch_array($Result);
	} else {
		return $Errors;
	}
}

/** This function takes a field name, and a string, and then returns an
   array of categories that fulfill this criteria.
*/
function SearchStockCategories($Field, $Criteria, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL="SELECT categoryid,
				categorydescription
		FROM stockcategory
		WHERE " . $Field ." " . LIKE  . " '%".$Criteria."%'";
	$Result = DB_query($SQL);
	$i=0;
	$CategoryList = array();
	while ($MyRow=DB_fetch_array($Result)) {
		$CategoryList[1][$i]['categoryid']=$MyRow[0];
		$CategoryList[1][$i]['categorydescription']=$MyRow[1];
		$i++;
	}
	return $CategoryList;
}

function StockCatPropertyList($Label, $Category, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL="SELECT stockitemproperties.stockid,
				description
		FROM stockitemproperties
			  INNER JOIN stockcatproperties
			  ON stockitemproperties.stkcatpropid=stockcatproperties.stkcatpropid
			  INNER JOIN stockmaster
			  ON stockitemproperties.stockid=stockmaster.stockid
			  WHERE stockitemproperties.value like '".$Label."'
			AND stockcatproperties.categoryid='".$Category."'";
	$Result = DB_query($SQL);
	$i=0;
	$ItemList = array();
	$ItemList[0]=0;
	while ($MyRow=DB_fetch_array($Result)) {
		$ItemList[1][$i]['stockid']=$MyRow[0];
		$ItemList[1][$i]['description']=$MyRow[1];
		$i++;
	}
	return $ItemList;
}

function GetStockCatProperty($Property, $StockID, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL="SELECT value FROM stockitemproperties
				   WHERE stockid='".$StockID."'
				   AND stkcatpropid='".$Property . "'";
	$Result = DB_query($SQL);
	$MyRow=DB_fetch_array($Result);
	$Errors[0]=0;
	$Errors[1]=$MyRow[0];
	return $Errors;
}

/** This function returns a list of the stock categories setup on webERP  */
function GetStockCategoryList($user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL = "SELECT categoryid FROM stockcategory";
	$Result = DB_query($SQL);
	$i=0;
	$StockCategoryList = array();
	while ($MyRow=DB_fetch_array($Result)) {
		$StockCategoryList[$i]=$MyRow[0];
		$i++;
	}
	return $StockCategoryList;
}
