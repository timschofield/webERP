<?php

// this script can be set to run from cron
$AllowAnyone = true;

require(__DIR__ . '/includes/session.php');
$Title = __('Send maintenance reminders');

$SQL = "SELECT description,
			taskdescription,
			ADDDATE(lastcompleted,frequencydays) AS duedate,
			userresponsible,
			email
		FROM fixedassettasks
		INNER JOIN fixedassets
			ON fixedassettasks.assetid = fixedassets.assetid
		INNER JOIN www_users
			ON fixedassettasks.userresponsible = www_users.userid
		WHERE ADDDATE(lastcompleted,frequencydays-10) > CURDATE()
		ORDER BY userresponsible";

$Result = DB_query($SQL);
$LastUserResponsible = '';
$MailText = __('You have the following maintenance task(s) falling due or over-due:') . "\n";

while ($MyRow = DB_fetch_array($Result)) {
	if ($LastUserResponsible != '' && $LastUserResponsible != $MyRow['userresponsible'] && IsEmailAddress($LastUserEmail)) {
		// Send email to the previous user before moving to the next one
		$SendResult = SendEmailFromWebERP($SysAdminEmail, $LastUserEmail, 'Maintenance Tasks Reminder', $MailText);
		// Reset mail text for new recipient
		$MailText = __('You have the following maintenance task(s) falling due or over-due:') . "\n";
	}

	if ($LastUserResponsible != $MyRow['userresponsible']) {
		$LastUserResponsible = $MyRow['userresponsible'];
		$LastUserEmail = $MyRow['email'];
	}

	$MailText .= 'Asset' . ': ' . $MyRow['description'] . "\nTask: " . $MyRow['taskdescription'] . "\nDue: "
		. ConvertSQLDate($MyRow['duedate']);
	if (Date1GreaterThanDate2(ConvertSQLDate($MyRow['duedate']), Date($_SESSION['DefaultDateFormat']))) {
		$MailText .= __('NB: THIS JOB IS OVERDUE');
	}
	$MailText .= "\n\n";
}

// Send email to the last user if there were results
if (DB_num_rows($Result) > 0 && IsEmailAddress($LastUserEmail)) {
	$SendResult = SendEmailFromWebERP($SysAdminEmail, $LastUserEmail, 'Maintenance Tasks Reminder', $MailText);
}

/* Now do manager emails for overdue jobs */
$SQL = "SELECT description,
			taskdescription,
			ADDDATE(lastcompleted,frequencydays) AS duedate,
			realname,
			manager,
			email
		FROM fixedassettasks
		INNER JOIN fixedassets
			ON fixedassettasks.assetid = fixedassets.assetid
		INNER JOIN www_users
			ON fixedassettasks.userresponsible = www_users.userid
		WHERE ADDDATE(lastcompleted,frequencydays) > CURDATE()
		ORDER BY manager";

$Result = DB_query($SQL);
$LastManager = '';
$ManagerMailText = "Your staff have failed to complete the following tasks by the due date:\n";

while ($MyRow = DB_fetch_array($Result)) {
	if ($LastManager != '' && $LastManager != $MyRow['manager'] && IsEmailAddress($LastManagerEmail)) {
		// Send email to the previous manager before moving to the next one
		$SendResult = SendEmailFromWebERP($SysAdminEmail, $LastManagerEmail, 'Overdue Maintenance Tasks Reminder', $ManagerMailText);
		// Reset mail text for new recipient
		$ManagerMailText = "Your staff have failed to complete the following tasks by the due date:\n";
	}

	if ($LastManager != $MyRow['manager']) {
		$LastManager = $MyRow['manager'];
		$LastManagerEmail = $MyRow['email'];
	}

	$ManagerMailText .= __('Asset') . ': ' . $MyRow['description'] . "\n" . __('Task:') . ' ' . $MyRow['taskdescription'] . "\n"
		. __('Due:') . ' ' . ConvertSQLDate($MyRow['duedate']);
	$ManagerMailText .= "\n\n";
}

if (DB_num_rows($Result) > 0) {
	include('includes/header.php');
	if (IsEmailAddress($LastManagerEmail)) {
		$SendResult = SendEmailFromWebERP($SysAdminEmail, $LastManagerEmail, 'Overdue Maintenance Tasks Reminder', $ManagerMailText);
		prnMsg(__('Reminder sent to') . ' ' . $LastManagerEmail, 'success');
	}
	include('includes/footer.php');
} else {
	include('includes/header.php');
	prnMsg(__('There are no reminders to be sent'), 'info');
	include('includes/footer.php');
}
