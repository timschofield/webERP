CREATE TABLE `loctransfercancellations` (
  `reference` int NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `cancelqty` double NOT NULL,
  `canceldate` datetime NOT NULL,
  `canceluserid` varchar(20) NOT NULL,
  KEY `Index1` (`reference`,`stockid`),
  KEY `Index2` (`canceldate`,`reference`,`stockid`),
  KEY `refstockid` (`reference`,`stockid`),
  KEY `cancelrefstockid` (`canceldate`,`reference`,`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
