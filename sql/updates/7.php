<?php

NewScript('GLBudgetHeaders.php', 10);
NewMenuItem('GL', 'Maintenance', __('Create/Amend General Ledger Budgets'), '/GLBudgetHeaders.php', 2);
RemoveMenuItem('GL', 'Maintenance', __('GL Budgets'), '/GLBudgets.php');
NewMenuItem('GL', 'Maintenance', __('Create Budget Amounts'), '/GLBudgets.php', 3);

CreateTable('glbudgetheaders', "CREATE TABLE IF NOT EXISTS `glbudgetheaders` (
  `id` int(11) NOT NULL auto_increment,
  `owner` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(200) NOT NULL DEFAULT '',
  `description` text,
  `startperiod` INT NOT NULL DEFAULT 0,
  `endperiod` INT NOT NULL DEFAULT 0,
  `current` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
)");

CreateTable('glbudgetdetails', "CREATE TABLE IF NOT EXISTS `glbudgetdetails` (
  `id` int(11) NOT NULL auto_increment,
  `headerid` int(11) NOT NULL DEFAULT 0,
  `account` varchar(20) NOT NULL DEFAULT '',
  `period` INT NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0.0,
  PRIMARY KEY  (`id`),
  KEY (`account`),
  KEY (`headerid`, `account`, `period`)
)");

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Move the budget amounts to new tables, separating budgets into 2 tables'));
}
