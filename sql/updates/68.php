<?php

AddColumn('passworddate', 'www_users', 'date', 'NOT NULL', '1000-01-01', 'password');
NewConfigValue('MaxPasswordAge', '365');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Add password age controls'));
}