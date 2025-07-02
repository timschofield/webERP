CREATE TABLE `menuitems` (
  `secroleid` int NOT NULL DEFAULT '8',
  `modulelink` varchar(10) NOT NULL DEFAULT '',
  `menusection` varchar(15) NOT NULL DEFAULT '',
  `caption` varchar(60) NOT NULL DEFAULT '',
  `url` varchar(60) NOT NULL DEFAULT '',
  `sequence` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`secroleid`,`modulelink`,`menusection`,`caption`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
