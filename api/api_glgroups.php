<?php

if (!isset($PathPrefix)) {
	header('Location: ../');
	exit();
}

/** Check that the account group doesn't already exist' */
function VerifyAccountGroup($AccountGroup, $i, $Errors) {
	$Searchsql = "SELECT count(groupname)
			FROM accountgroups
			WHERE groupname='".$AccountGroup."'";
	$SearchResult = DB_query($Searchsql);
	$Answer = DB_fetch_array($SearchResult);
	if ($Answer[0]>0) {
		$Errors[$i] = GLAccountGroupAlreadyExists;
	}
	return $Errors;
}

/** Check that the account sectiont already exists' */
function VerifyAccountSectionExists($AccountSection, $i, $Errors) {
	$Searchsql = "SELECT count(sectionid)
			FROM accountsection
			WHERE sectionid='".$AccountSection."'";
	$SearchResult = DB_query($Searchsql);
	$Answer = DB_fetch_array($SearchResult);
	if ($Answer[0]==0) {
		$Errors[$i] = GLAccountSectionDoesntExist;
	}
	return $Errors;
}

/** Verify that the pandl flag is a 1 or 0 */
function VerifyPandL($pandl, $i, $Errors) {
	if ($pandl!=0 and $pandl!=1) {
		$Errors[$i] = InvalidPandL;
	}
	return $Errors;
}

/** Verify that the sequenceintb figure is numeric */
function VerifySequenceInTB($sequenceintb, $i, $Errors) {
	if (!is_numeric($sequenceintb)) {
		$Errors[$i] = InvalidSequenceInTB;
	}
	return $Errors;
}

/** Check that the parent group exists */
function VerifyParentGroupExists($AccountGroup, $i, $Errors) {
	$Searchsql = "SELECT count(groupname)
			FROM accountgroups
			WHERE groupname='".$AccountGroup."'";
	$SearchResult = DB_query($Searchsql);
	$Answer = DB_fetch_array($SearchResult);
	if ($Answer[0]==0 and $AccountGroup!='') {
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
	foreach ($AccountGroupDetails as $key => $Value) {
		$AccountGroupDetails[$key] = DB_escape_string($Value);
	}
	$Errors=VerifyAccountGroup($AccountGroupDetails['groupname'], sizeof($Errors), $Errors);
	$Errors=VerifyAccountSectionExists($AccountGroupDetails['sectioninaccounts'], sizeof($Errors), $Errors);
	if (isset($AccountGroupDetails['pandl'])){
		$Errors=VerifyPandL($AccountGroupDetails['pandl'], sizeof($Errors), $Errors);
	}
	$Errors=VerifyParentGroupExists($AccountGroupDetails['parentgroupname'], sizeof($Errors), $Errors);
	$FieldNames='';
	$FieldValues='';
	foreach ($AccountGroupDetails as $key => $Value) {
		$FieldNames.=$key.', ';
		$FieldValues.='"'.$Value.'", ';
	}
	if (sizeof($Errors)==0) {
		$SQL = "INSERT INTO accountgroups ('" .mb_substr($FieldNames,0,-2) . "')
				VALUES ('" . mb_substr($FieldValues,0,-2) . "' ) ";
		$Result = DB_query($SQL);
		if (DB_error_no() != 0) {
			$Errors[0] = DatabaseUpdateFailed;
		} else {
			$Errors[0]=0;
		}
	}
	return $Errors;
}
