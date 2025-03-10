CREATE TABLE `employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `surname` varchar(20) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `stockid` varchar(20) NOT NULL COMMENT 'FK with stockmaster - ',
  `manager` int DEFAULT NULL,
  `normalhours` double NOT NULL DEFAULT '40',
  `userid` varchar(20) NOT NULL,
  `email` varchar(55) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `surname` (`surname`),
  KEY `firstname` (`firstname`),
  KEY `stockid` (`stockid`),
  KEY `manager` (`manager`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
