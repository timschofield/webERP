CREATE TABLE `tenders` (
  `tenderid` int NOT NULL DEFAULT '0',
  `location` varchar(5) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(40) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `closed` int NOT NULL DEFAULT '0',
  `requiredbydate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`tenderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
