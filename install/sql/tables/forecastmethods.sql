CREATE TABLE IF NOT EXISTS `forecastmethods` (
  `methodid` tinyint(2) NOT NULL,
  `methodname` varchar(50) NOT NULL,
  `methoddesc` varchar(255) NOT NULL,
  `requireshistory` int(11) NOT NULL DEFAULT 12,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`methodid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

