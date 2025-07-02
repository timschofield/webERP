CREATE TABLE `contractcharges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contractref` varchar(20) NOT NULL,
  `transtype` smallint NOT NULL DEFAULT '20',
  `transno` int NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `narrative` text NOT NULL,
  `anticipated` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `contractref` (`contractref`,`transtype`,`transno`),
  KEY `contractcharges_ibfk_2` (`transtype`),
  CONSTRAINT `contractcharges_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`),
  CONSTRAINT `contractcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
