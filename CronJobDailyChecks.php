<?php

include('CronJobStart.php');
include('config.php');
include('includes/session_cronjob.inc');

include('KLDailyChecks.php');

# GRAB THE VARIABLES FROM THE URL
$Group = $_GET['p'];

$EmailText  = "KL webERP Cron Job: Daily Tasks Group " . $Group . "\n"; 

$EmailText  = KL_DailyChecks($Group, $RootPath, $db, $EmailText);

$EmailAddress = "webmaster@kapal-laut.com";
$EmailSubject  = "KL webERP Cron Job: Daily Tasks " . $Group ; 
SendEmailFromCron($EmailAddress, $EmailSubject, $EmailText, '');

?>