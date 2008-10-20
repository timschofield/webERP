<?php

/* Check that the account group doesn't already exist'*/
	function VerifyAccountGroup($AccountGroup, $i, $Errors, $db) {
		$Searchsql = "SELECT count(groupname)
				FROM accountgroups
				WHERE groupname='".$AccountGroup."'";
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_array($SearchResult);
		if ($answer[0]>0) {
			$Errors[$i] = GLAccountGroupAlreadyExists;
		}
		return $Errors;
	}

/* Check that the account sectiont already exists'*/
	function VerifyAccountSectionExists($AccountSection, $i, $Errors, $db) {
		$Searchsql = "SELECT count(sectionid)
				FROM accountsection
				WHERE sectionid='".$AccountSection."'";
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_array($SearchResult);
		if ($answer[0]==0) {
			$Errors[$i] = GLAccountSectionDoesntExist;
		}
		return $Errors;
	}

/* Verify that the pandl flag is a 1 or 0 */
	function VerifyPandL($pandl, $i, $Errors) {
		if ($pandl!=0 and $pandl!=1) {
			$Errors[$i] = InvalidPandL;
		}
		return $Errors;
	}

/* Verify that the sequenceintb figure is numeric */
	function VerifySequenceInTB($sequenceintb, $i, $Errors) {
		if (!is_numeric($sequenceintb)) {
			$Errors[$i] = InvalidSequenceInTB;
		}
		return $Errors;
	}

/* Check that the parent group exists*/
	function VerifyParentGroupExists($AccountGroup, $i, $Errors, $db) {
		$Searchsql = "SELECT count(groupname)
				FROM accountgroups
				WHERE groupname='".$AccountGroup."'";
		$SearchResult=DB_query($Searchsql, $db);
		$answer = DB_fetch_array($SearchResult);
		if ($answer[0]==0 and $AccountGroup!='') {
			$Errors[$i] = AccountGroupDoesntExist;
		}
		return $Errors;
	}

	function InsertGLAccountGroup($AccountGroupDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($AccountGroupDetails as $key => $value) {
			$AccountGroupDetails[$key] = DB_escape_string($value);
		}
		$Errors=VerifyAccountGroup($AccountGroupDetails['groupname'], sizeof($Errors), $Errors, $db);
		$Errors=VerifyAccountSectionExists($AccountGroupDetails['sectioninaccounts'], sizeof($Errors), $Errors, $db);
		if (isset($AccountGroupDetails['pandl'])){
			$Errors=VerifyPandL($AccountGroupDetails['pandl'], sizeof($Errors), $Errors);
		}
		$Errors=VerifyParentGroupExists($AccountGroupDetails['parentgroupname'], sizeof($Errors), $Errors, $db);
		$FieldNames='';
		$FieldValues='';
		foreach ($AccountGroupDetails as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		if (sizeof($Errors)==0) {
			$sql = 'INSERT INTO accountgroups ('.substr($FieldNames,0,-2).') '.
		  		'VALUES ('.substr($FieldValues,0,-2).') ';
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