<?php
/* $Id: MaintenaceReminders.php 4551 2011-04-16 06:20:56Z daintree $*/
//this script can be set to run from cron
$AllowAnyone = true;
include('includes/session.php');
include('includes/htmlMimeMail.php');

$sql="SELECT 	description,
				taskdescription,
				ADDDATE(lastcompleted,frequencydays) AS duedate,
				userresponsible,
				email
		FROM fixedassettasks
		INNER JOIN fixedassets
		ON fixedassettasks.assetid=fixedassets.assetid
		INNER JOIN www_users
		ON fixedassettasks.userresponsible=www_users.userid
		WHERE ADDDATE(lastcompleted,frequencydays-10)> CURDATE()
		ORDER BY userresponsible";

$result = DB_query($sql);
$LastUserResponsible = '';

while ($myrow = DB_fetch_array($result)){
	if (!isset(${'Mail' . $myrow['userresponsible']}) AND IsEmailAddress($myrow['email'])) {
		if ($LastUserResponsible!=''){
			${'Mail' . $myrow['userresponsible']}->setText($MailText);
			$SendResult = ${'Mail' . $myrow['userresponsible']}->send(array($LastUserEmail));
			$MailText = _('You have the following maintenance task(s) falling due or over-due:') . "\n";
		}
		$LastUserResponsible = $myrow['userresponsible'];
		$LastUserEmail = $myrow['email'];
		${'Mail' . $myrow['userresponsible']} = new htmlMimeMail();
		${'Mail' . $myrow['userresponsible']}->setSubject('Maintenance Tasks Reminder');
		${'Mail' . $myrow['userresponsible']}->setFrom('Do_not_reply <>');
	}
	$MailText .= 'Asset' . ': ' . $myrow['description'] . "\nTask: " . $myrow['taskdescription'] . "\nDue: " . ConvertSQLDate($myrow['duedate']);
	if (Date1GreaterThanDate2(ConvertSQLDate($myrow['duedate']),Date($_SESSION['DefaultDateFormat']))) {
		$MailText .= _('NB: THIS JOB IS OVERDUE');
	}
	$MailText . "\n\n";
}
if (DB_num_rows($result)>0){
	${'Mail' . $LastUserResponsible}->setText($MailText);
	$SendResult = ${'Mail' . $LastUserResponsible}->send(array(${'Mail' . $LastUserResponsible}));
}

/* Now do manager emails for overdue jobs */
$sql="SELECT 	description,
				taskdescription,
				ADDDATE(lastcompleted,frequencydays) AS duedate,
				realname,
				manager
		FROM fixedassettasks
		INNER JOIN fixedassets
		ON fixedassettasks.assetid=fixedassets.assetid
		INNER JOIN www_users
		ON fixedassettasks.userresponsible=www_users.userid
		WHERE ADDDATE(lastcompleted,frequencydays)> CURDATE()
		ORDER BY manager";

$result = DB_query($sql);
$LastManager = '';
while ($myrow = DB_fetch_array($result)){
	if (!isset(${'Mail' . $myrow['userresponsible']})) {
		if ($LastUserResponsible!=''){
			${'Mail' . $myrow['userresponsible']}->setText($MailText);
			$SendResult = ${'Mail' . $myrow['manager']}->send(array($LastManagerEmail));
			$MailText = "Your staff have failed to complete the following tasks by the due date:\n";
		}
		$LastManager = $myrow['manager'];
		$LastManagerEmail = $myrow['email'];
		${'Mail' . $myrow['manager']} = new htmlMimeMail();
		${'Mail' . $myrow['manager']}->setSubject('Overdue Maintenance Tasks Reminder');
		${'Mail' . $myrow['manager']}->setFrom('Do_not_reply <>');
	}
	$MailText .= _('Asset') . ': ' . $myrow['description'] . "\n" . _('Task:') . ' ' . $myrow['taskdescription'] . "\n" . _('Due:') . ' ' . ConvertSQLDate($myrow['duedate']);
	$MailText . "\n\n";
}
if (DB_num_rows($result)>0){
	${'Mail' . $LastManager}->setText($MailText);
	$SendResult = ${'Mail' . $LastManager}->send(array($LastManagerEmail));
}

?>