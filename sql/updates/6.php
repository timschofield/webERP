<?php

CreateTable('session_data', 'CREATE TABLE `session_data` (
  `userid` VARCHAR(20),
  `field` varchar(100) NOT NULL DEFAULT "",
  `value` varchar(100) NOT NULL DEFAULT "",
  PRIMARY KEY (`userid`,`value`)
)');

AddColumn('timeout', 'www_users', 'TINYINT', 'NOT NULL', 5, 'pagesize');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Database changes to automatically logout remembering state'));
}
