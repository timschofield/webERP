CREATE TABLE `pricematrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantitybreak` int NOT NULL DEFAULT '1',
  `price` double NOT NULL DEFAULT '0',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `startdate` date NOT NULL DEFAULT '1000-01-01',
  `enddate` date NOT NULL DEFAULT '9999-12-31',
  PRIMARY KEY (`salestype`,`stockid`,`currabrev`,`quantitybreak`,`startdate`,`enddate`),
  KEY `SalesType` (`salestype`),
  KEY `currabrev` (`currabrev`),
  KEY `stockid` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
