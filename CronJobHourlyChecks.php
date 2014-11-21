<?php

include('CronJobStart.php');
include('config.php');
include('includes/session_cronjob.inc');

include('KLDailyChecks.php');

$EmailText  = "KL webERP Cron Job: Hourly Tasks" . "\n"; 

$EmailText = KL_HourlyChecks($RootPath, $db, $EmailText);

$EmailAddress = "webmaster@kapal-laut.com";
$EmailSubject  = "KL webERP Cron Job: Hourly Tasks"; 
SendEmailFromCron($EmailAddress, $EmailSubject, $EmailText, '');

?>