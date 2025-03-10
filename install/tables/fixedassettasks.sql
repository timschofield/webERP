CREATE TABLE `fixedassettasks` (
  `taskid` int NOT NULL AUTO_INCREMENT,
  `assetid` int NOT NULL,
  `taskdescription` text NOT NULL,
  `frequencydays` int NOT NULL DEFAULT '365',
  `lastcompleted` date NOT NULL,
  `userresponsible` varchar(20) NOT NULL,
  `manager` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`taskid`),
  KEY `assetid` (`assetid`),
  KEY `userresponsible` (`userresponsible`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
