<?php

$PageSecurity = 15;
include('includes/session.inc');

$lineno = 1;
$orderno = 0;

$SalesOrdersResult = DB_query('SELECT orderno, stkcode FROM salesorderdetails ORDER BY orderno', $db);

while ($SalesOrderDetails = DB_fetch_array($SalesOrdersResult)) {
	
	if($OrderNo != $SalesOrderDetails['orderno']) {
		$LineNo = 0;
	} else {
		$LineNo++;
	}
	
	$OrderNo = $SalesOrderDetails['orderno'];
	DB_query('UPDATE salesorderdetails 
		SET orderlineno=' . $LineNo . ' 
		WHERE orderno=' . $OrderNo . " 
		AND stkcode='" . $SalesOrderDetails['stkcode'] ."'", $db);

}

DB_query( 'ALTER TABLE salesorderdetails ADD CONSTRAINT salesorderdetails_pk primary key(orderno, orderlineno)',$db);

prnMsg(_('The sales orderdetails lines have been numbered appropriately for version 3.02','success'));

?>
