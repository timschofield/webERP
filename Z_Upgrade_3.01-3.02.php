<?php

$PageSecurity = 15;
include('includes/session.inc');
$title = _('Upgrade webERP 3.01 - 3.02');
include('includes/header.inc');

prnMsg(_('Upgrade script to number salesorderdetails records as required by version 3.02 .... please wait'),'info');

$TestAlreadyDoneResult = DB_query('SELECT * FROM salesorderdetails WHERE orderlineno>=1',$db);
if (DB_num_rows($TestAlreadyDoneResult)>0){
	prnMsg(_('The upgrade script appears to have been run already successfully - there is no need to re-run it'),'info');
	include('includes/footer.inc');
	exit;
}


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

prnMsg(_('The sales orderdetails lines have been numbered appropriately for version 3.02'),'success');
include('includes/footer.inc');
?>
