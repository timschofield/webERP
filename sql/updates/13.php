<?php

CreateTable('sessions', 'CREATE TABLE `sessions` (
  `sessionid` char(32),  
  `last_poll` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
)');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), _('Creates database table sessions'));
}

?>