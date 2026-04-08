CREATE TABLE IF NOT EXISTS `forecastprices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forecastid` int(11) NOT NULL,
  `effectivedate` date NOT NULL,
  `unitprice` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `costprice` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `currencycode` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `forecastid` (`forecastid`,`effectivedate`),
  CONSTRAINT `forecastprices_ibfk_1` FOREIGN KEY (`forecastid`) REFERENCES `forecastheader` (`forecastid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  COLLATE=utf8mb4_general_ci;
