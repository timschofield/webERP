<?php

/* This script should run only if DB is not maria DB 5.5, 
   as the CURRENT_TIMESTAMP default column is not supported in that version */

$isMariaDB55 = false;
$VersionResult = DB_query("SELECT VERSION()");
if ($VersionResult) {
	$VersionRow = DB_fetch_row($VersionResult);
	$ServerVersion = $VersionRow[0] ?? '';
	if (stripos($ServerVersion, 'mariadb') !== false && stripos($ServerVersion, '5.5') !== false) {
		$isMariaDB55 = true;
	}
}

if (!$isMariaDB55) {
	/*
	ChangeColumnType('createdat', 'custcontacts', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createdat', 'custnotes', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	*/

	ChangeColumnType('createddate', 'departments', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createdon', 'forecastheader', 'DATETIME', 'NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createdon', 'forecastsimulation', 'DATETIME', 'NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createdon', 'forecastsummary', 'DATETIME', 'NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hrapplicantreqs', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hrapplicants', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hrcolleaguefeedback', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hrcompreviewcycles', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hremployeecompensation', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hremployees', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hrfeedbackcriteriascores', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hrpaygrades', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hrperfappraisals', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hrperformancegoals', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hrperformancereviews', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hrpositions', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hrratingscales', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hrrequisitions', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hrsafetyincidents', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('createddate', 'hrskills', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('logintime', 'sessions', 'DATETIME', 'NOT NULL', 'CURRENT_TIMESTAMP');
	ChangeColumnType('scripttime', 'sessions', 'DATETIME', 'NULL', '');
	ChangeColumnType('createdate', 'stockserialitems', 'DATETIME', 'NULL', 'CURRENT_TIMESTAMP');
}

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Change Column Type from timestamp to datetime on servers others than mariaDB 5.5'));
}
