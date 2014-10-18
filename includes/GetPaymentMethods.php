<?php
/* $Id: GetPaymentMethods.php 5768 2012-12-20 08:38:22Z daintree $*/

$PaytTypes    = array();
$ReceiptTypes = array();

$sql = 'SELECT paymentname,
			paymenttype,
			receipttype
			FROM paymentmethods
			ORDER by paymentname';

$PMResult = DB_query($sql,$db);
while( $PMrow = DB_fetch_array($PMResult) ) {
	if ($PMrow['paymenttype']==1) {
		$PaytTypes[] = $PMrow['paymentname'];
	}
	if ($PMrow['receipttype']==1) {
		$ReceiptTypes[] = $PMrow['paymentname'];
	}
}
DB_free_result($PMResult); // no longer needed
?>