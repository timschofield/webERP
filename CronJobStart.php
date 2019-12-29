<?php
/* Prepares an script to be used as a cron job
*/

/************************************************************************************
KL RICARD MODIFICATIONS:
 Change of AllowAnyone by AllowCronJobToBeRun to minimize risk of intrusions
*************************************************************************************/

$AllowCronJobToBeRun = true;
$DatabaseName = 'kurakura_kl_erp';

/*************************************************************************************************************************/ 	
	
function SendEmailFromCron($EmailAddress, $EmailSubject, $EmailText, $EmailHeaders, $begintime){
	/* Getting time now to calculate time needed for cron job*/
	$time = microtime();
	$time = explode(" ", $time);
	$endtime = $time[1] + $time[0];
	$totalsecs = ($endtime - $begintime);

	/* Final formatting bits */
	$EmailSubject  = trim($EmailSubject); // just for sure
	$EmailText = $EmailText . "\n---\r\n"; // \r is needed for signature separating
	$EmailText = $EmailText . 'Email sent by webERP KL CRON JOB at '.date('d/M/Y H:i:s').' Seconds needed: '. round($totalsecs, 3);
	if ($EmailHeaders = ''){
		$EmailHeaders = 'From: webmaster@kapal-laut.com' . "\r\n" .
						'Reply-To: webmaster@kapal-laut.com' . "\r\n" .
						'X-Mailer: PHP/' . phpversion();
	}

	mail($EmailAddress,$EmailSubject,$EmailText,$EmailHeaders);
}	

?>