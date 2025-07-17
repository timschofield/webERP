CREATE TABLE `modules` (
  `secroleid` int NOT NULL DEFAULT '8',
  `modulelink` varchar(10) NOT NULL DEFAULT '',
  `reportlink` varchar(4) NOT NULL DEFAULT '',
  `modulename` varchar(25) NOT NULL DEFAULT '',
  `sequence` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`secroleid`,`modulelink`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
