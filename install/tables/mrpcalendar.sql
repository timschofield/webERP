CREATE TABLE `mrpcalendar` (
  `calendardate` date NOT NULL,
  `daynumber` int NOT NULL,
  `manufacturingflag` smallint NOT NULL DEFAULT '1',
  PRIMARY KEY (`calendardate`),
  KEY `daynumber` (`daynumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
