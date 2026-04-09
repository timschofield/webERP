CREATE TABLE IF NOT EXISTS `forecastdetails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forecastid` int(11) NOT NULL,
  `perioddate` date NOT NULL,
  `periodnum` int(11) NOT NULL,
  `forecastqty` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `actualqty` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `revisedqty` decimal(20,4) DEFAULT NULL,
  `variance` decimal(20,4) DEFAULT NULL,
  `mad` decimal(20,4) DEFAULT NULL COMMENT 'Mean Absolute Deviation',
  `poa` decimal(10,4) DEFAULT NULL COMMENT 'Percent of Accuracy',
  `confidence` decimal(10,4) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `forecast_period` (`forecastid`,`perioddate`),
  KEY `forecastid` (`forecastid`),
  KEY `perioddate` (`perioddate`),
  CONSTRAINT `forecastdetails_ibfk_1` FOREIGN KEY (`forecastid`) REFERENCES `forecastheader` (`forecastid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  COLLATE=utf8mb4_general_ci;
