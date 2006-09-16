<?php

$PageSecurity = 15;
include('includes/session.inc');
$title = _('Upgrade webERP 3.04 - 3.05');
include('includes/header.inc');

prnMsg(_('Upgrade script to put cost information against GRN records from purchorderdetails records .... please wait'),'info');

$TestAlreadyDoneResult = DB_query('SELECT * FROM grns WHERE stdcostunit<>0',$db);
if (DB_num_rows($TestAlreadyDoneResult)>0){
	prnMsg(_('The upgrade script appears to have been run already successfully - there is no need to re-run it'),'info');
	include('includes/footer.inc');
	exit;
}


$UpdateGRNCosts = DB_query('UPDATE grns INNER JOIN purchorderdetails ON grns.podetailitem=purchorderdetails.podetailitem SET grns.stdcostunit = purchorderdetails.stdcostunit', $db);


prnMsg(_('The GRN records have been updated with cost information from purchorderdetails successfully'),'success');
include('includes/footer.inc');
?>
