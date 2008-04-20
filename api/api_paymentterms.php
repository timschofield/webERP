<?php

/* This function returns a list of the payment terms abbreviations
 * currently setup on webERP 
 */

	function GetPaymentTermsList($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT termsindicator FROM paymentterms';
		$result = DB_query($sql, $db);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$PaymentTermsList[$i]=$myrow[0];
			$i++;
		}
		return $PaymentTermsList;
	}
	
/* This function takes as a parameter a payment terms code
 * and returns an array containing the details of the selected 
 * payment terms.
 */
	
	function GetPaymentTermsDetails($paymentterms, $user, $password) {
		$Errors = array();
		if (!isset($db)) {
			$db = db($user, $password);
			if (gettype($db)=='integer') {
				$Errors[0]=NoAuthorisation;
				return $Errors;
			}
		}
		$sql = "SELECT * FROM paymentterms WHERE termsindicator='".$paymentterms."'";
		$result = DB_query($sql, $db);
		return DB_fetch_array($result);
	}

?>