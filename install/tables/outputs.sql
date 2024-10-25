CREATE TABLE `outputs` (
  `runtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `testnumber` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `message` varchar(200) NOT NULL DEFAULT '',
  `testoutput` text NOT NULL,
  PRIMARY KEY (`runtime`,`testnumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3