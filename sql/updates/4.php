<?php

CreateTable('regularpayments', "CREATE TABLE IF NOT EXISTS `regularpayments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `frequency` char(1) NOT NULL default 'M',
  `days` tinyint(3) NOT NULL DEFAULT 0,
  `glcode` varchar(20) NOT NULL DEFAULT '1',
  `bankaccountcode` varchar(20) NOT NULL DEFAULT '0',
  `tag` varchar(255) NOT NULL DEFAULT '',
  `amount` double NOT NULL default 0,
  `currabrev` char(3) NOT NULL DEFAULT '',
  `narrative` varchar(255) default '',
  `firstpayment` date NOT NULL default '1000-01-01',
  `finalpayment` date NOT NULL default '1000-01-01',
  `nextpayment` date NOT NULL default '1000-01-01',
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
)");

NewScript('RegularPaymentsSetup.php', 5);
NewScript('RegularPaymentsProcess.php', 5);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Database changes to process regular payments'));
}
