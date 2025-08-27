<?php

$PageSecurity = 0;

include('includes/session.php');

$SQL = "SELECT NOW()- logintime AS time_interval FROM sessions WHERE sessionid='" . $_GET['Id'] . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);
if ($MyRow['time_interval'] > 60) {
	header('Location: ' . htmlspecialchars_decode($RootPath) . '/Logout.php');
	exit();
}

$SQL = "UPDATE sessions SET logintime = NOW() WHERE sessionid='" . $_GET['Id'] . "'";
$Result = DB_query($SQL);
