<?php
// I Figure This is the fastest and safest way to load the Sections
$PaytTypes    = array();
$ReceiptTypes = array();

$sql = 'SELECT paymentname, 
			paymenttype, 
			receipttype 
			FROM paymentmethods 
			ORDER by paymentname';
$PMResult = DB_query($sql,$db);
while( $PMrow = DB_fetch_array($PMResult) ) {
	if ($PMrow['paymenttype']==1)
		$PaytTypes[] = $PMrow['paymentname'];
	if ($PMrow['receipttype']==1)
		$ReceiptTypes[] = $PMrow['paymentname'];
}
DB_free_result($PMResult); // no longer needed
?>