CREATE TABLE IF NOT EXISTS `forecastsaleshistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL,
  `locationcode` varchar(5) NOT NULL DEFAULT '',
  `customerid` varchar(10) DEFAULT NULL,
  `customertype` varchar(2) DEFAULT NULL,
  `area` varchar(3) DEFAULT NULL,
  `salesperson` varchar(10) DEFAULT NULL,
  `perioddate` date NOT NULL,
  `quantity` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `amount` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `cost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (`id`),
  KEY `stockid` (`stockid`,`locationcode`,`perioddate`),
  KEY `perioddate` (`perioddate`),
  KEY `composite` (`stockid`,`customertype`,`area`,`perioddate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
