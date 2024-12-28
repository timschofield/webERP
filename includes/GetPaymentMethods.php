<?php

$PaytTypes    = array();
$ReceiptTypes = array();

$SQL = 'SELECT paymentid,
			paymentname,
			paymenttype,
			receipttype,
			percentdiscount
			FROM paymentmethods
			ORDER by paymentname';

$PMResult = DB_query($SQL);
while( $PMrow = DB_fetch_array($PMResult) ) {
	if ($PMrow['paymenttype']==1) {
		$PaytTypes[] = $PMrow['paymentname'];
	}
	if ($PMrow['receipttype']==1) {
		$ReceiptTypes[$PMrow['paymentid']] = $PMrow;
	}
}
DB_free_result($PMResult); // no longer needed
?>
