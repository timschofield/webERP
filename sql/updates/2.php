<?php

CreateTable('dashboard_scripts',
"CREATE TABLE IF NOT EXISTS `dashboard_scripts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `scripts` varchar(78) NOT NULL,
  `pagesecurity` int NOT NULL DEFAULT '1',
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
)");

InsertRecord('dashboard_scripts', array('scripts', 'pagesecurity', 'description' ), array('total_dashboard.php', 1, __('Shows total for sales, purchase and outstanding orders')), array('scripts', 'pagesecurity', 'description' ), array('total_dashboard.php', 1, __('Shows total for sales, purchase and outstanding orders')));
InsertRecord('dashboard_scripts', array('scripts', 'pagesecurity', 'description' ), array('customer_orders.php', 2, __('Shows latest customer orders have been placed.')), array('scripts', 'pagesecurity', 'description' ), array('customer_orders.php', 1, __('Shows latest customer orders have been placed.')));
InsertRecord('dashboard_scripts', array('scripts', 'pagesecurity', 'description' ), array('unpaid_invoice.php', 2, __('Shows Outstanding invoices')), array('scripts', 'pagesecurity', 'description' ), array('unpaid_invoice.php', 1, __('Shows Outstanding invoices')));
InsertRecord('dashboard_scripts', array('scripts', 'pagesecurity', 'description' ), array('latest_po.php', 3, __('Shows latest Purchase orders')), array('scripts', 'pagesecurity', 'description' ), array('latest_po.php', 1, __('Shows latest Purchase orders')));
InsertRecord('dashboard_scripts', array('scripts', 'pagesecurity', 'description' ), array('latest_po_auth.php', 3, __('Shows Purchase orders to authorise')), array('scripts', 'pagesecurity', 'description' ), array('latest_po_auth.php', 1, __('Shows Purchase orders to authorise')));
InsertRecord('dashboard_scripts', array('scripts', 'pagesecurity', 'description' ), array('latest_stock_status.php', 3, __('Shows latest stock status')), array('scripts', 'pagesecurity', 'description' ), array('latest_stock_status.php', 1, __('Shows latest stock status')));
InsertRecord('dashboard_scripts', array('scripts', 'pagesecurity', 'description' ), array('work_orders.php', 3, __('Shows latest work orders')), array('scripts', 'pagesecurity', 'description' ), array('work_orders.php', 1, __('Shows latest work orders')));
InsertRecord('dashboard_scripts', array('scripts', 'pagesecurity', 'description' ), array('mrp_dashboard.php', 3, __('Shows latest MRP')), array('scripts', 'pagesecurity', 'description' ), array('mrp_dashboard.php', 1, __('Shows latest MRP')));
InsertRecord('dashboard_scripts', array('scripts', 'pagesecurity', 'description' ), array('bank_trans.php', 2, __('Shows latest bank transactions')), array('scripts', 'pagesecurity', 'description' ), array('bank_trans.php', 1, __('Shows latest bank transactions')));
InsertRecord('dashboard_scripts', array('scripts', 'pagesecurity', 'description' ), array('latest_grns.php', 2, __('Shows latest goods received into the company')), array('scripts', 'pagesecurity', 'description' ), array('bank_trans.php', 1, __('Shows latest bank transactions')));

NewScript('total_dashboard.php', 1);
NewScript('customer_orders.php', 2);
NewScript('unpaid_invoice.php', 2);
NewScript('latest_stock_status.php', 3);
NewScript('work_orders.php', 3);
NewScript('mrp_dashboard.php', 3);
NewScript('bank_trans.php', 3);
NewScript('latest_po.php', 3);
NewScript('latest_po_auth.php', 2);
NewScript('latest_grns.php', 2);

CreateTable('dashboard_users',
"CREATE TABLE IF NOT EXISTS `dashboard_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` varchar(20) NOT NULL,
  `scripts` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
)");

InsertRecord('dashboard_users', array('userid', 'scripts' ), array('admin', '1,2,5,7'), array('userid', 'scripts' ), array('admin', '1,2,5,7'));

NewScript('DashboardConfig.php', 15);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('New database updates for the new dashboard system'));
}
