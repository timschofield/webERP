CREATE TABLE `salescatprod` (
  `salescatid` tinyint NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `manufacturers_id` int NOT NULL,
  `featured` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`salescatid`,`stockid`),
  KEY `salescatid` (`salescatid`),
  KEY `stockid` (`stockid`),
  KEY `manufacturer_id` (`manufacturers_id`),
  CONSTRAINT `salescatprod_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `salescatprod_ibfk_2` FOREIGN KEY (`salescatid`) REFERENCES `salescat` (`salescatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
