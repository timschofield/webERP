CREATE TABLE `cogsglpostings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `area` char(3) NOT NULL DEFAULT '',
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `glcode` varchar(20) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  KEY `Area` (`area`),
  KEY `StkCat` (`stkcat`),
  KEY `GLCode` (`glcode`),
  KEY `SalesType` (`salestype`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;
