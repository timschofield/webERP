<?php

	function VerifyCategoryID($CategoryID, $i, $Errors) {
		if (strlen($CategoryID)>6 or $CategoryID=='') {
			$Errors[$i] = InvalidCategoryID;
		}
		return $Errors;
	}

/* Verify the category doesnt exist */
	function VerifyStockCategoryAlreadyExists($StockCategory, $i, $Errors, $db) {
		$Searchsql = "SELECT count(categoryid)
				FROM stockcategory
				WHERE categoryid='".$StockCategory."'";
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_array($SearchResult);
		if ($answer[0]>0) {
			$Errors[$i] = StockCategoryAlreadyExists;
		}
		return $Errors;
	}

	function VerifyCategoryDescription($CategoryDescription, $i, $Errors) {
		if (strlen($CategoryDescription)>20 or $CategoryDescription=='') {
			$Errors[$i] = InvalidCategoryDescription;
		}
		return $Errors;
	}

	function VerifyStockType($StockType, $i, $Errors) {
		if (strlen($StockType)>1 or $StockType=='') {
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
		foreach ($CategoryDetails as $key => $value) {
			$CategoryDetails[$key] = DB_escape_string($value);
		}
		$Errors=VerifyStockCategoryAlreadyExists($CategoryDetails['categoryid'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyCategoryID($CategoryDetails['categoryid'], sizeof($Errors), $Errors);
		$Errors=VerifyCategoryDescription($CategoryDetails['categorydescription'], sizeof($Errors), $Errors);
		$Errors=VerifyStockType($CategoryDetails['stocktype'], sizeof($Errors), $Errors);
		$Errors=VerifyAccountCodeExists($CategoryDetails['stockact'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyAccountCodeExists($CategoryDetails['adjglact'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyAccountCodeExists($CategoryDetails['purchpricevaract'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyAccountCodeExists($CategoryDetails['materialuseagevarac'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyAccountCodeExists($CategoryDetails['wipact'], sizeof($Errors), $Errors, $db);
		$FieldNames='';
		$FieldValues='';
		foreach ($CategoryDetails as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		$sql = 'INSERT INTO stockcategory ('.substr($FieldNames,0,-2).') '.
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

	function ModifyStockCategory($CategoryDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($CategoryDetails as $key => $value) {
			$CategoryDetails[$key] = DB_escape_string($value);
		}
		$Errors=VerifyStockCategoryExists($CategoryDetails['categoryid'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyCategoryID($CategoryDetails['categoryid'], sizeof($Errors), $Errors);
		$Errors=VerifyCategoryDescription($CategoryDetails['categorydescription'], sizeof($Errors), $Errors);
		$Errors=VerifyStockType($CategoryDetails['stocktype'], sizeof($Errors), $Errors);
		$Errors=VerifyAccountCodeExists($CategoryDetails['stockact'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyAccountCodeExists($CategoryDetails['adjglact'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyAccountCodeExists($CategoryDetails['purchpricevaract'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyAccountCodeExists($CategoryDetails['materialuseagevarac'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyAccountCodeExists($CategoryDetails['wipact'], sizeof($Errors), $Errors, $db);
		$FieldNames='';
		$FieldValues='';
		foreach ($CategoryDetails as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		$sql='UPDATE stockcategory SET ';
		foreach ($CategoryDetails as $key => $value) {
			$sql .= $key.'="'.$value.'", ';
		}
		$sql = substr($sql,0,-2).' WHERE categoryid="'.$CategoryDetails['categoryid'].'"';
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

/* This function takes a categoryid and returns an associative array containing
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
		$Errors=VerifyStockCategoryExists($Categoryid, sizeof($Errors), $Errors, $db);
		if (sizeof($Errors)!=0) {
			return $Errors;
		}
		$sql='SELECT * FROM stockcategory WHERE categoryid="'.$Categoryid.'"';
		$result = DB_Query($sql, $db);
		if (sizeof($Errors)==0) {
			return DB_fetch_array($result);
		} else {
			return $Errors;
		}
	}

/* This function takes a field name, and a string, and then returns an
   array of categories that fulfill this criteria.
*/
	function SearchStockCategories($Field, $Criteria, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql='SELECT categoryid, categorydescription
			FROM stockcategory
			WHERE '.$Field.' LIKE "%'.$Criteria.'%"';
		$result = DB_Query($sql, $db);
		$i=0;
		$CategoryList = array();
		while ($myrow=DB_fetch_array($result)) {
			$CategoryList[1][$i]['categoryid']=$myrow[0];
			$CategoryList[1][$i]['categorydescription']=$myrow[1];
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
		$sql='select stockitemproperties.stockid, description from stockitemproperties
		 inner join stockcatproperties 
		 on stockitemproperties.stkcatpropid=stockcatproperties.stkcatpropid
		 inner join stockmaster 
		 on stockitemproperties.stockid=stockmaster.stockid 
		 where stockitemproperties.value like "'.$Label.'" and 
		 stockcatproperties.categoryid="'.$Category.'"';
		$result = DB_Query($sql, $db);
		$i=0;
		$ItemList = array();
		$ItemList[0]=0;
		while ($myrow=DB_fetch_array($result)) {
			$ItemList[1][$i]['stockid']=$myrow[0];
			$ItemList[1][$i]['description']=$myrow[1];
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
		$sql='SELECT value FROM stockitemproperties WHERE
			stockid="'.$StockID.'" AND stkcatpropid='.$Property;
		$result = DB_Query($sql, $db);
		$myrow=DB_fetch_array($result);
		$Errors[0]=0;
		$Errors[1]=$myrow[0];
		return $Errors;
	}				
	
?>