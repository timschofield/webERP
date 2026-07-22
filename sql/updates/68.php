<?php

AddColumn('passworddate', 'www_users', 'date', 'NOT NULL', '1000-01-01', 'password');
$SQL = "UPDATE www_users SET passworddate=CURRENT_DATE WHERE passworddate='1000-01-01'";
$ErrMsg = __('Initialising password date for existing users failed');
$Result = DB_query($SQL, $ErrMsg);
NewConfigValue('MaxPasswordAge', '365');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Add password age controls'));
}