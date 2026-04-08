CREATE TABLE IF NOT EXISTS `forecastsummarydetails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `summaryid` int(11) NOT NULL,
  `perioddate` date NOT NULL,
  `periodnum` int(11) NOT NULL,
  `forecastqty` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `forecastvalue` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `actualqty` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `actualvalue` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `revisedqty` decimal(20,4) DEFAULT NULL,
  `revisedvalue` decimal(20,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `summary_period` (`summaryid`,`perioddate`),
  KEY `summaryid` (`summaryid`),
  KEY `perioddate` (`perioddate`),
  CONSTRAINT `forecastsummarydetails_ibfk_1` FOREIGN KEY (`summaryid`) REFERENCES `forecastsummary` (`summaryid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  COLLATE=utf8mb4_general_ci;
