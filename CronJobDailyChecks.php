<?php

include('CronJobStart.php');
include('config.php');
include('includes/session.inc');

include('KLDailyChecks.php');

# GRAB THE VARIABLES FROM THE URL
$Group = $_GET['p'];

$EmailText  = "KL webERP: Daily CRON JOB " . $Group . "\n"; 

$EmailText  = KL_DailyChecks($Group, $RootPath, $db, $EmailText);

$EmailAddress = "webmaster@kapal-laut.com";
$EmailSubject  = "KL webERP: Daily CRON JOB " . $Group ; 
SendEmailFromCron($EmailAddress, $EmailSubject, $EmailText, '');

?>