CREATE TABLE `tests` (
  `testnumber` int NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `lastrun` datetime NOT NULL DEFAULT '1001-01-01 00:00:00',
  PRIMARY KEY (`testnumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
