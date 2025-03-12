CREATE TABLE `labelfields` (
  `labelfieldid` int NOT NULL AUTO_INCREMENT,
  `labelid` tinyint NOT NULL,
  `fieldvalue` varchar(20) NOT NULL,
  `vpos` double NOT NULL DEFAULT '0',
  `hpos` double NOT NULL DEFAULT '0',
  `fontsize` tinyint NOT NULL,
  `barcode` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`labelfieldid`),
  KEY `labelid` (`labelid`),
  KEY `vpos` (`vpos`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
