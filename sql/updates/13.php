<?php

CreateTable('sessions', 'CREATE TABLE `sessions` (
  `sessionid` char(32),
  `last_poll` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Creates database table sessions'));
}
