CREATE TABLE `qatests` (
  `testid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `method` varchar(20) DEFAULT NULL,
  `groupby` varchar(20) DEFAULT NULL,
  `units` varchar(20) NOT NULL,
  `type` varchar(15) NOT NULL,
  `defaultvalue` varchar(150) NOT NULL DEFAULT '''''',
  `numericvalue` tinyint NOT NULL DEFAULT '0',
  `showoncert` int NOT NULL DEFAULT '1',
  `showonspec` int NOT NULL DEFAULT '1',
  `showontestplan` tinyint NOT NULL DEFAULT '1',
  `active` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`testid`),
  KEY `name` (`name`),
  KEY `groupname` (`groupby`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
