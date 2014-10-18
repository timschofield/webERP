<?php
/* $Id: api_paymentterms.php 5768 2012-12-20 08:38:22Z daintree $*/

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
/* This function returns a list of the payment methods
 * currently setup on webERP
 */
	function GetPaymentMethodsList($User, $Password) {
		$Errors = array();
		if (!isset($db)) {
			$db = db($User, $Password);
			if (gettype($db)=='integer') {
				$Errors[0]=NoAuthorisation;
				return $Errors;
			}
		}
		$sql = "SELECT paymentid FROM paymentmethods";
		$result = DB_query($sql, $db);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$PaymentMethodsList[$i]=$myrow[0];
			$i++;
		}
		return $PaymentMethodsList;
	}

/* This function takes as a parameter a payment method code
 * and returns an array containing the details of the selected
 * payment method.
 */

	function GetPaymentMethodDetails($PaymentMethod, $User, $Password) {
		$Errors = array();
		if (!isset($db)) {
			$db = db($User, $Password);
			if (gettype($db)=='integer') {
				$Errors[0]=NoAuthorisation;
				return $Errors;
			}
		}
		$sql = "SELECT * FROM paymentmethods WHERE paymentid='".$PaymentMethod."'";
		$result = DB_query($sql, $db);
		return DB_fetch_array($result);
	}

?>