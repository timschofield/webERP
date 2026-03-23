CREATE TABLE IF NOT EXISTS `forecastsimulation` (
  `simulationid` int(11) NOT NULL AUTO_INCREMENT,
  `forecastid` int(11) NOT NULL,
  `simulationname` varchar(50) NOT NULL,
  `adjustmentpct` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `adjustmentqty` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `startdate` date NOT NULL,
  `enddate` date NOT NULL,
  `notes` text,
  `createdon` timestamp DEFAULT CURRENT_TIMESTAMP,
  `createdby` varchar(20) NOT NULL,
  PRIMARY KEY (`simulationid`),
  KEY `forecastid` (`forecastid`),
  CONSTRAINT `forecastsimulation_ibfk_1` FOREIGN KEY (`forecastid`) REFERENCES `forecastheader` (`forecastid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
