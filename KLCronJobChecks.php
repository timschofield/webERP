<?php

$ForceConfigReload = true;

include('includes/KLSessionCronJob.php');
include('includes/KLCronJobFunctions.php');

/* Getting time now to calculate starting time */
$time = microtime();
$time = explode(' ', $time);
$begintime = $time[1] + $time[0];

// GRAB THE VARIABLES FROM THE URL
$Group = $_GET['p'];
$ScriptTile  = "Cron Job Daily Task " . $Group; 

$EmailText  = "KL webERP Cron Job: Task Group " . $Group . "\n"; 
$EmailText = $EmailText . 'Cron Job started at '.date('d/M/Y H:i:s'). "\n";

$EmailText  = KLCronJobChecks($Group, $RootPath, $EmailText);

/* Getting end time to calculate the time needed to execute the cron job*/
$time = microtime();
$time = explode(" ", $time);
$endtime = $time[1] + $time[0];
$runningtime = round(($endtime - $begintime),5);

$AuditSQL = "INSERT INTO auditscripts (executiondate,
					secondsrunning,
					userid,
					scripttitle)
			VALUES('" . Date('Y-m-d H:i:s') . "',
				'" . $runningtime . "',
				'" . trim($_SESSION['UserID']) . "',
				'" . DB_escape_string($ScriptTile) . "')";
$Result = DB_query($AuditSQL);

/* Final formatting bits */
$EmailTo = "webmaster@kapal-laut.com";
$EmailSubject  = "KL webERP Cron Job: Task " . $Group; 
$EmailSubject  = trim($EmailSubject); // just to be sure it is clean

if (KLwebERPScriptCalledFromTEST()){
	$webERPType = 'webERP TEST';
	$EmailTo = 'webmaster@kapal-laut.com';
	$EmailSubject = 'TEST ' . $EmailSubject;
}else{
	$webERPType = 'webERP';
}

$EmailText = $EmailText . "\n---\r\n" . 
						'Email sent by ' . $webERPType . ' ' . 
						$_SESSION['VersionNumber'] . 
						'+' . $_SESSION['DBVersion'] .
						'-ADU ' . $KLCodeVersion .
						'-PHP ' . phpversion() .
						' CRON JOB at '.date('d/M/Y H:i:s').' Seconds needed: '. $runningtime;

SendEmailFromWebERP('webmaster@kapal-laut.com',
					$EmailTo,
					$EmailSubject,
					$EmailText,
					'',
					true);

?>