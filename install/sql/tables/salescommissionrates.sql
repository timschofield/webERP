CREATE TABLE `salescommissionrates` (
  `salespersoncode` varchar(4) NOT NULL DEFAULT '',
  `categoryid` char(6) NOT NULL DEFAULT '',
  `area` char(3) NOT NULL DEFAULT '',
  `startfrom` double NOT NULL DEFAULT '0',
  `daysactive` int NOT NULL DEFAULT '0',
  `rate` double NOT NULL DEFAULT '0',
  `currency` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`salespersoncode`,`categoryid`,`startfrom`),
  KEY `salespersoncode` (`salespersoncode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
