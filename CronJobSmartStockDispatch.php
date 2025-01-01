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

# GRAB THE VARIABLES FROM THE URL
$Group = $_GET['p'];

$EmailText = KLPrepareGroupSmartStockTransfers($Group, $RootPath, $EmailText);

$EmailAddress = "webmaster@kapal-laut.com";
$EmailSubject  = "KL webERP Cron Job: Smart Dispatch ". $Group;
SendEmailFromCron($EmailAddress, $EmailSubject, $EmailText, '', $begintime, $ScriptTile);

/****************************************************************************************/

?>