<?php

/* This function returns the default currency code in webERP.
 */

	function GetDefaultCurrency($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = "SELECT currencydefault FROM companies WHERE coycode=1";
		$result = DB_query($sql, $db);
		$answer=DB_fetch_array($result);
		$ReturnValue[0]=0;
		$ReturnValue[1]=$answer;
		return $ReturnValue;
	}

/* This function returns the default sales type in webERP.
 */

	function GetDefaultPriceList($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = "SELECT confvalue FROM config WHERE confname='DefaultPriceList'";
		$result = DB_query($sql, $db);
		$answer=DB_fetch_array($result);
		$ReturnValue[0]=0;
		$ReturnValue[1]=$answer;
		return $ReturnValue;
	}

/* This function returns the default date format in webERP.
 */

	function GetDefaultDateFormat($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = "select confvalue from config where confname='DefaultDateFormat'";
		$result = DB_query($sql, $db);
		$answer=DB_fetch_array($result);
		$ReturnValue[0]=0;
		$ReturnValue[1]=$answer;
		return $ReturnValue;
	}

/* This function returns the default date format in webERP.
 */

	function GetDefaultLocation($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = "select defaultlocation from www_users where userid='".$user."'";
		$result = DB_query($sql, $db);
		$answer=DB_fetch_array($result);
		$ReturnValue[0]=0;
		$ReturnValue[1]=$answer;
		return $ReturnValue;
	}

?>
