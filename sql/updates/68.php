<?php

AddColumn('forcepasswordchange', 'www_users', 'tinyint(1)', 'NOT NULL', '0', 'password');
$SQL = "UPDATE www_users SET forcepasswordchange = 0";
$ErrMsg = __('Initialising forced password change flag for existing users failed');
$Result = DB_query($SQL, $ErrMsg);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Add password change flag controls'));
}