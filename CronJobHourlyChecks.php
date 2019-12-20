<?php

include('CronJobStart.php');
include('config.php');
include('includes/session_cronjob.php');

include('KLDailyChecks.php');

$EmailText  = "KL webERP Cron Job: Hourly Tasks" . "\n"; 
$EmailText = $EmailText . 'Cron Job started at '.date('d/M/Y H:i:s'). "\n";

$EmailText = KL_HourlyChecks($RootPath, $db, $EmailText);

$EmailAddress = "webmaster@kapal-laut.com";
$EmailSubject  = "KL webERP Cron Job: Hourly Tasks"; 
SendEmailFromCron($EmailAddress, $EmailSubject, $EmailText, '');

?>