<?php

	function GetCurrencyList($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT currabrev FROM currencies';
		$result = DB_query($sql, $db);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$CurrencyList[$i]=$myrow[0];
			$i++;
		}
		return $CurrencyList;
	}
	
	function GetCurrencyDetails($currency, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT * FROM currencies WHERE currabrev="'.$currency.'"';
		$result = DB_query($sql, $db);
		return DB_fetch_array($result);
	}

?>