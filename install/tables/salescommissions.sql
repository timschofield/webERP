CREATE TABLE `salescommissions` (
  `commissionno` int NOT NULL DEFAULT '0',
  `type` smallint NOT NULL DEFAULT '10',
  `transno` int NOT NULL DEFAULT '0',
  `stkmoveno` int NOT NULL DEFAULT '0',
  `salespersoncode` varchar(4) NOT NULL DEFAULT '',
  `paid` int NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `currency` char(3) NOT NULL DEFAULT '',
  `exrate` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`type`,`transno`),
  KEY `salespersoncode` (`salespersoncode`),
  KEY `paid` (`paid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
