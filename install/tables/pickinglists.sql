CREATE TABLE `pickinglists` (
  `pickinglistno` int NOT NULL DEFAULT '0',
  `orderno` int NOT NULL DEFAULT '0',
  `pickinglistdate` date NOT NULL DEFAULT '1000-01-01',
  `dateprinted` date NOT NULL DEFAULT '1000-01-01',
  `deliverynotedate` date NOT NULL DEFAULT '1000-01-01',
  PRIMARY KEY (`pickinglistno`),
  KEY `pickinglists_ibfk_1` (`orderno`),
  CONSTRAINT `pickinglists_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
