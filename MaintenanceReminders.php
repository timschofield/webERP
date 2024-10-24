<?php
//this script can be set to run from cron
$AllowAnyone = true;
include('includes/session.php');
include('includes/htmlMimeMail.php');

$SQL="SELECT 	description,
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

$Result = DB_query($SQL);
$LastUserResponsible = '';

while ($MyRow = DB_fetch_array($Result)){
	if (!isset(${'Mail' . $MyRow['userresponsible']}) AND IsEmailAddress($MyRow['email'])) {
		if ($LastUserResponsible!=''){
			${'Mail' . $MyRow['userresponsible']}->setText($MailText);
			$SendResult = ${'Mail' . $MyRow['userresponsible']}->send(array($LastUserEmail));
			$MailText = _('You have the following maintenance task(s) falling due or over-due:') . "\n";
		}
		$LastUserResponsible = $MyRow['userresponsible'];
		$LastUserEmail = $MyRow['email'];
		${'Mail' . $MyRow['userresponsible']} = new htmlMimeMail();
		${'Mail' . $MyRow['userresponsible']}->setSubject('Maintenance Tasks Reminder');
		${'Mail' . $MyRow['userresponsible']}->setFrom('Do_not_reply <>');
	}
	$MailText .= 'Asset' . ': ' . $MyRow['description'] . "\nTask: " . $MyRow['taskdescription'] . "\nDue: " . ConvertSQLDate($MyRow['duedate']);
	if (Date1GreaterThanDate2(ConvertSQLDate($MyRow['duedate']),Date($_SESSION['DefaultDateFormat']))) {
		$MailText .= _('NB: THIS JOB IS OVERDUE');
	}
	$MailText .= "\n\n";
}
if (DB_num_rows($Result)>0){
	${'Mail' . $LastUserResponsible}->setText($MailText);
	$SendResult = ${'Mail' . $LastUserResponsible}->send(array(${'Mail' . $LastUserResponsible}));
}

/* Now do manager emails for overdue jobs */
$SQL="SELECT 	description,
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

$Result = DB_query($SQL);
$LastManager = '';
while ($MyRow = DB_fetch_array($Result)){
	if (!isset(${'Mail' . $MyRow['userresponsible']})) {
		if ($LastUserResponsible!=''){
			${'Mail' . $MyRow['userresponsible']}->setText($MailText);
			$SendResult = ${'Mail' . $MyRow['manager']}->send(array($LastManagerEmail));
			$MailText = "Your staff have failed to complete the following tasks by the due date:\n";
		}
		$LastManager = $MyRow['manager'];
		$LastManagerEmail = $MyRow['email'];
		${'Mail' . $MyRow['manager']} = new htmlMimeMail();
		${'Mail' . $MyRow['manager']}->setSubject('Overdue Maintenance Tasks Reminder');
		${'Mail' . $MyRow['manager']}->setFrom('Do_not_reply <>');
	}
	$MailText .= _('Asset') . ': ' . $MyRow['description'] . "\n" . _('Task:') . ' ' . $MyRow['taskdescription'] . "\n" . _('Due:') . ' ' . ConvertSQLDate($MyRow['duedate']);
	$MailText .= "\n\n";
}
if (DB_num_rows($Result)>0){
	${'Mail' . $LastManager}->setText($MailText);
	$SendResult = ${'Mail' . $LastManager}->send(array($LastManagerEmail));
}

?>