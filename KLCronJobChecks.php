<?php

include('includes/KLSessionCronJob.php');
include('includes/KLCronJobFunctions.php');

$time = microtime();
$time = explode(' ', $time);
$begintime = $time[1] + $time[0];

# GRAB THE VARIABLES FROM THE URL
$Group = $_GET['p'];
$ScriptTile  = "Cron Job Daily Task " . $Group; 

$EmailText  = "KL webERP Cron Job: Task Group " . $Group . "\n"; 
$EmailText = $EmailText . 'Cron Job started at '.date('d/M/Y H:i:s'). "\n";

$EmailText  = KLCronJobChecks($Group, $RootPath, $EmailText);

$EmailAddress = "webmaster@kapal-laut.com";
$EmailSubject  = "KL webERP Cron Job: Task " . $Group; 
SendEmailFromCron($EmailAddress, $EmailSubject, $EmailText, '', $begintime, $ScriptTile);

?>