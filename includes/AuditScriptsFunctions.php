<?php
/**********************************************************************
*
* Functions related to Audit Scripts
*
***********************************************************************/

function RecordRunningTime($Title, $UserName) {

	if (isset($Title)) {
		$TitleScriptRunning = $Title;
	} else {
		$TitleScriptRunning = "Undefined title";
	}

	$Time = explode(' ', $_SESSION['ScriptStartTime']);
	$BeginTime = $Time[1] + $Time[0];

	$Time = microtime();
	$Time = explode(" ", $Time);
	$EndTime = $Time[1] + $Time[0];
	
	// Ensure positive running time
	$TimeDifference = $EndTime - $BeginTime;
	$RunningTime = ($TimeDifference > 0) ? round($EndTime - $BeginTime, 5) : 0;

	// Ensure $UserName is not null before passing it to trim()
	$UserName = isset($UserName) ? trim($UserName) : '';

	// Check if auditscripts table exists before inserting
	$TableExistsSQL = "SHOW TABLES LIKE 'auditscripts'";
	$TableExistsResult = DB_query($TableExistsSQL);

	if (DB_num_rows($TableExistsResult) > 0) {
		$AuditSQL = "INSERT INTO auditscripts (executiondate,
							secondsrunning,
							userid,
							scripttitle)
					VALUES('" . date('Y-m-d H:i:s') . "',
						'" . $RunningTime . "',
						'" . $UserName . "',
						'" . DB_escape_string($TitleScriptRunning) . "')";
		DB_query($AuditSQL);
	}

}

