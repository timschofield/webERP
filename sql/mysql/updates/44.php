<?php

/* New report to hold the details of each users reportlets
 * that appear in the dashboard app
 */



CreateTable('reportlets', "CREATE TABLE `reportlets` (
	`userid` varchar(20) NOT NULL DEFAULT '',
	`id` varchar(50) NOT NULL DEFAULT '',
	`title` varchar(50) NOT NULL DEFAULT '',
	`refresh` int(11) NOT NULL DEFAULT 600,
	PRIMARY KEY  (`userid`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

InsertRecord('reportlets', array('userid', 'id', 'title'), array('admin', 'TopSalesVal', 'Top selling items by value'), array('userid', 'id', 'title', 'refresh'), array('admin', 'TopSalesVal', 'Top selling items by value', 60), $db);
InsertRecord('reportlets', array('userid', 'id', 'title'), array('admin', 'TopSalesQty', 'Top selling items by quantity'), array('userid', 'id', 'title', 'refresh'), array('admin', 'TopSalesQty', 'Top selling items by quantity', 60), $db);
InsertRecord('reportlets', array('userid', 'id', 'title'), array('admin', 'SalesGraph', 'Graph of last 12 months sales activity'), array('userid', 'id', 'title', 'refresh'), array('admin', 'SalesGraph', 'Graph of last 12 months sales activity', 60), $db);

UpdateDBNo(44, $db);

?>