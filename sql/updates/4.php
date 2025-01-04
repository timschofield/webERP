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
  `firstpayment` date NOT NULL default '0000-00-00',
  `finalpayment` date NOT NULL default '0000-00-00',
  `nextpayment` date NOT NULL default '0000-00-00',
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
)");

NewScript('RegularPaymentsSetup.php', 5);
NewScript('RegularPaymentsProcess.php', 5);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), _('Database changes to process regular payments'));
}

?>