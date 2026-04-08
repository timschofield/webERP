CREATE TABLE `custitem` (
  `debtorno` char(10) NOT NULL DEFAULT '',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `cust_part` varchar(64) NOT NULL DEFAULT '',
  `cust_description` varchar(255) NOT NULL DEFAULT '',
  `customersuom` char(50) NOT NULL DEFAULT '',
  `conversionfactor` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`debtorno`,`stockid`),
  KEY `StockID` (`stockid`),
  KEY `Debtorno` (`debtorno`),
  CONSTRAINT `custitem_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `custitem_ibfk_2` FOREIGN KEY (`debtorno`) REFERENCES `debtorsmaster` (`debtorno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  COLLATE=utf8mb4_general_ci;
