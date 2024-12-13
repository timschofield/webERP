<?php

CreateTable('login_data', 'CREATE TABLE `login_data` (
  `sessionid` char(26),
  `userid` varchar(20),
  `login` datetime NOT NULL DEFAULT NOW(),
  `script` varchar(100) NOT NULL DEFAULT "",
  PRIMARY KEY (`sessionid`)
)');

NewMenuItem('system', 'Maintenance', 'Logged in users', '/LoggedInUsers.php', 8);
NewScript('LoggedInUsers.php', 8);

UpdateDBNo(basename(__FILE__, '.php'), _('New table to record who is currently logged in'));

?>