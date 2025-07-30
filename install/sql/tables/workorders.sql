CREATE TABLE `workorders` (
  `wo` int NOT NULL,
  `loccode` char(5) NOT NULL DEFAULT '',
  `requiredby` date NOT NULL DEFAULT '1000-01-01',
  `startdate` date NOT NULL DEFAULT '1000-01-01',
  `costissued` double NOT NULL DEFAULT '0',
  `closed` tinyint NOT NULL DEFAULT '0',
  `closecomments` longblob DEFAULT NULL,
  `reference` varchar(40) NOT NULL DEFAULT '',
  `remark` text DEFAULT NULL,
  PRIMARY KEY (`wo`),
  KEY `LocCode` (`loccode`),
  KEY `StartDate` (`startdate`),
  KEY `RequiredBy` (`requiredby`),
  CONSTRAINT `worksorders_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
