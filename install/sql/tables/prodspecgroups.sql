CREATE TABLE `prodspecgroups` (
  `groupid` smallint NOT NULL AUTO_INCREMENT,
  `groupname` char(50) DEFAULT NULL,
  `groupbyNo` int NOT NULL DEFAULT '1',
  `headertitle` varchar(100) DEFAULT NULL,
  `trailertext` varchar(240) DEFAULT NULL,
  `labels` varchar(240) NOT NULL,
  `numcols` tinyint(1) NOT NULL,
  PRIMARY KEY (`groupid`),
  UNIQUE KEY `groupname` (`groupname`),
  KEY `groupbyNo` (`groupbyNo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3