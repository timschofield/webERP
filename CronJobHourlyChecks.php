<?php

include('CronJobStart.php');
include('config.php');
include('includes/session_cronjob.inc');

include('KLDailyChecks.php');

$EmailText  = "KL webERP: Hourly CRON JOB" . "\n"; 

$EmailText = KL_HourlyChecks($RootPath, $db, $EmailText);

$EmailAddress = "webmaster@kapal-laut.com";
$EmailSubject  = "KL webERP: Hourly CRON JOB"; 
SendEmailFromCron($EmailAddress, $EmailSubject, $EmailText, '');

?>