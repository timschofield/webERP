<?php

include('includes/KLSessionCronJob.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/htmlMimeMail.php');
include('includes/GetPrice.inc');
include('includes/KLSmartStockTransfers.php');

$time = microtime();
$time = explode(' ', $time);
$begintime = $time[1] + $time[0];

$EmailText  = "KL webERP: Smart Stock Dispatch " . "\n"; 
$EmailText = $EmailText . 'Cron Job started at '.date('d/M/Y H:i:s'). "\n";

/* Parameters */
if (KLwebERPScriptCalledFromTEST()){
	$ReportType = "ReportOnly"; // To NOT create proper transfers, just the paperwork to test it
}else{
	$ReportType = "Batch"; // To create proper transfers
}

$DispatchPercent = 0;
$_SESSION['DefaultPageSize'] = 'A4';
$DaysSalesForOrder = 2;

# GRAB THE VARIABLES FROM THE URL
$Group = $_GET['p'];

if ($Group == "1050-SmartDispatchKL"){
	$ScriptTile  = "Cron Job Smart dispatch KL"; 
	$ShopType = "SHOPKL";
	$EmailText = $EmailText . 'Smart dispatch for Kapal-Laut Shops' . "\n";
}elseif ($Group == "1060-SmartDispatchBL"){
	$ScriptTile  = "Cron Job Smart dispatch BL"; 
	$ShopType = "SHOPBL";
	$EmailText = $EmailText . 'Smart dispatch for Blink Shops' . "\n";
}elseif ($Group == "1070-SmartDispatchOU"){
	$ScriptTile  = "Cron Job Smart dispatch OU"; 
	$ShopType = "SHOPOU";
	$EmailText = $EmailText . 'Smart dispatch for Outlet Shops' . "\n";
}else{
	$ScriptTile  = "Cron Job Smart dispatch UNDEFINED"; 
	$EmailText = $EmailText . 'Type Of Shop not defined' . "\n";
}

/* Selection of shops with smart dispatch from / to KANTO, sorted by priority and sales of the last X days */
$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$DaysSalesForOrder));

$DayOfWeek = date('w', strtotime(Date('Y-m-d')));

$SQL = "SELECT locations.loccode,
				locations.smartdispatchmaxmodels,
				locations.smartdispatchminmodels
		FROM locations,locationzones
		WHERE locations.zone = locationzones.code
			AND locations.smartdispatchfrom = 'KANTO' 
			AND locations.typeloc = '" . $ShopType . "' 
			AND locationzones.smarttransferonweekday".$DayOfWeek . " = 1 
		ORDER BY locations.priority ASC,
			(SELECT COUNT(qtyinvoiced)
			FROM salesorderdetails, salesorders
			WHERE salesorderdetails.orderno = salesorders.orderno
				AND salesorderdetails.completed = 1
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.fromstkloc = locations.loccode) DESC";

$Result = DB_query($SQL);
if (DB_num_rows($Result) != 0){
	while ($MyRow = DB_fetch_array($Result)) {
		// From KANTO to Shop, send the items needed to fill the RL
		$EmailText  = KLStockDispatch('KANTO', $MyRow['loccode'], "All", $ReportType, $DispatchPercent, $MyRow['smartdispatchmaxmodels'], $MyRow['smartdispatchminmodels'], $RootPath, $EmailText);
		// From Shop to KANTO, return the overstock
		$EmailText  = KLStockDispatch($MyRow['loccode'], 'KANTO', "OverFrom", $ReportType, $DispatchPercent, $MyRow['smartdispatchmaxmodels'], $MyRow['smartdispatchminmodels'], $RootPath, $EmailText);
	}
}

$EmailAddress = "webmaster@kapal-laut.com";
$EmailSubject  = "KL webERP Cron Job: Smart Dispatch ". $Group;
SendEmailFromCron($EmailAddress, $EmailSubject, $EmailText, '', $begintime, $ScriptTile);

/****************************************************************************************/

?>