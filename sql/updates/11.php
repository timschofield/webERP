<?php

CreateTable('login_data', 'CREATE TABLE `login_data` (
  `sessionid` char(255),
  `userid` varchar(20),
  `login` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `script` varchar(100) NOT NULL DEFAULT "",
  PRIMARY KEY (`sessionid`)
)');

NewMenuItem('system', 'Maintenance', 'Logged in users', '/LoggedInUsers.php', 8);
NewScript('LoggedInUsers.php', 8);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('New table to record who is currently logged in'));
}
