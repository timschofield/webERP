CREATE TABLE IF NOT EXISTS `forecastsummary` (
  `summaryid` int(11) NOT NULL AUTO_INCREMENT,
  `summarycode` varchar(20) NOT NULL,
  `summarydesc` varchar(100) NOT NULL,
  `categorycode` varchar(20) DEFAULT NULL,
  `customertype` varchar(2) DEFAULT NULL,
  `area` varchar(3) DEFAULT NULL,
  `salesperson` varchar(10) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `createdby` varchar(20) NOT NULL,
  `createdon` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`summaryid`),
  UNIQUE KEY `summarycode` (`summarycode`),
  KEY `categorycode` (`categorycode`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
