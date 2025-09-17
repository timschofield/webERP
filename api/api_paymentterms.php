<?php

if (!isset($PathPrefix)) {
	header('Location: ../');
	exit();
}

/** This function returns a list of the payment terms abbreviations
 * currently setup on webERP
 */
function GetPaymentTermsList($user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL = 'SELECT termsindicator FROM paymentterms';
	$Result = DB_query($SQL);
	$i=0;
	$PaymentTermsList = array();
	while ($MyRow=DB_fetch_array($Result)) {
		$PaymentTermsList[$i]=$MyRow[0];
		$i++;
	}
	return $PaymentTermsList;
}

/** This function takes as a parameter a payment terms code
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
	$SQL = "SELECT * FROM paymentterms WHERE termsindicator='".$paymentterms."'";
	$Result = DB_query($SQL);
	return DB_fetch_array($Result);
}

/** This function returns a list of the payment methods
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
	$SQL = "SELECT paymentid FROM paymentmethods";
	$Result = DB_query($SQL);
	$i=0;
	$PaymentMethodsList = array();
	while ($MyRow=DB_fetch_array($Result)) {
		$PaymentMethodsList[$i]=$MyRow[0];
		$i++;
	}
	return $PaymentMethodsList;
}

/** This function takes as a parameter a payment method code
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
	$SQL = "SELECT * FROM paymentmethods WHERE paymentid='".$PaymentMethod."'";
	$Result = DB_query($SQL);
	return DB_fetch_array($Result);
}
