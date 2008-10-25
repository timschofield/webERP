<?php

/* Check that the account code doesn't already exist'*/
	function VerifyAccountCode($AccountCode, $i, $Errors, $db) {
		$Searchsql = "SELECT count(accountcode)
				FROM chartmaster
				WHERE accountcode='".$AccountCode."'";
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_array($SearchResult);
		if ($answer[0]>0) {
			$Errors[$i] = GLAccountCodeAlreadyExists;
		}
		return $Errors;
	}

/* Check that the account code already exists'*/
	function VerifyAccountCodeExists($AccountCode, $i, $Errors, $db) {
		$Searchsql = "SELECT count(accountcode)
				FROM chartmaster
				WHERE accountcode='".$AccountCode."'";
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_array($SearchResult);
		if ($answer[0]==0) {
			$Errors[$i] = GLAccountCodeDoesntExists;
		}
		return $Errors;
	}

/* Check that the name is 50 characters or less long */
	function VerifyAccountName($AccountName, $i, $Errors) {
		if (strlen($AccountName)>50) {
			$Errors[$i] = IncorrectAccountNameLength;
		}
		return $Errors;
	}

/* Check that the account group exists*/
	function VerifyAccountGroupExists($AccountGroup, $i, $Errors, $db) {
		$Searchsql = "SELECT count(groupname)
				FROM accountgroups
				WHERE groupname='".$AccountGroup."'";
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_array($SearchResult);
		if ($answer[0]==0) {
			$Errors[$i] = AccountGroupDoesntExist;
		}
		return $Errors;
	}

	function InsertGLAccount($AccountDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($AccountDetails as $key => $value) {
			$AccountDetails[$key] = DB_escape_string($value);
		}
		$Errors=VerifyAccountCode($AccountDetails['accountcode'], sizeof($Errors), $Errors, $db);
		if (isset($AccountDetails['accountname'])){
			$Errors=VerifyAccountName($AccountDetails['accountname'], sizeof($Errors), $Errors);
		}
		$Errors=VerifyAccountGroupExists($AccountDetails['group_'], sizeof($Errors), $Errors, $db);
		$FieldNames='';
		$FieldValues='';
		foreach ($AccountDetails as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		if (sizeof($Errors)==0) {
			$sql = 'INSERT INTO chartmaster ('.substr($FieldNames,0,-2).') '.
		  		'VALUES ('.substr($FieldValues,0,-2).') ';
			$result = DB_Query($sql, $db);
			$sql='INSERT INTO chartdetails (accountcode,
							period)
				SELECT ' . $AccountDetails['accountcode'] . ',
					periodno
				FROM periods';
			$result = DB_query($sql,$db,'','','',false);
			if (DB_error_no($db) != 0) {
				$Errors[0] = DatabaseUpdateFailed;
			} else {
				$Errors[0]=0;
			}
		}
		return $Errors;
	}

/* This function returns a list of the general ledger accounts
 * currently setup on webERP
 */

	function GetGLAccountList($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'select chartmaster.accountcode,chartmaster.accountname,accountgroups.pandl ' .
				'from chartmaster left join accountgroups on ' .
				'chartmaster.group_=accountgroups.groupname ORDER BY accountcode';
		$result = DB_query($sql, $db);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$GLAccountList[$i]['accountcode']=$myrow[0];
			$GLAccountList[$i]['accountname']=$myrow[1];
			$GLAccountList[$i]['pandl']=$myrow[2];
			$i++;
		}
		return $GLAccountList;
	}

/* This function takes as a parameter a general ledger account code
 * and returns an array containing the details of the selected
 * general ledger code.
 */

	function GetGLAccountDetails($accountcode, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT * FROM chartmaster WHERE accountcode="'.$accountcode.'"';
		$result = DB_query($sql, $db);
		return DB_fetch_array($result);
	}

?>
