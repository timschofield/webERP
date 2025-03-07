<?php

$PageSecurity=0;

include('includes/session.php');

$SQL = "SELECT NOW()-last_poll AS time_interval FROM sessions WHERE sessionid='" . $_GET['Id'] . "'";
$Result = DB_query($SQL);

$MyRow=DB_fetch_array($Result);
if ($MyRow['time_interval'] > 60) {
	header('Location: Logout.php');
}

$SQL = "UPDATE sessions SET last_poll=NOW() WHERE sessionid='" . $_GET['Id'] . "'";
$Result = DB_query($SQL);

?>