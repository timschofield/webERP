CREATE TABLE IF NOT EXISTS `forecastmetrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forecastid` int(11) NOT NULL,
  `perioddate` date NOT NULL,
  `mad` decimal(20,4) DEFAULT NULL COMMENT 'Mean Absolute Deviation',
  `mse` decimal(20,4) DEFAULT NULL COMMENT 'Mean Squared Error',
  `rmse` decimal(20,4) DEFAULT NULL COMMENT 'Root Mean Squared Error',
  `mape` decimal(10,4) DEFAULT NULL COMMENT 'Mean Absolute Percentage Error',
  `poa` decimal(10,4) DEFAULT NULL COMMENT 'Percent of Accuracy',
  `bias` decimal(20,4) DEFAULT NULL COMMENT 'Forecast bias',
  `trackingsignal` decimal(10,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `forecast_period` (`forecastid`,`perioddate`),
  KEY `forecastid` (`forecastid`),
  CONSTRAINT `forecastmetrics_ibfk_1` FOREIGN KEY (`forecastid`) REFERENCES `forecastheader` (`forecastid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  COLLATE=utf8mb4_general_ci;
