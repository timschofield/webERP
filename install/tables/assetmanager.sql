CREATE TABLE `assetmanager` (
  `id` int NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `location` varchar(15) NOT NULL DEFAULT '',
  `cost` double NOT NULL DEFAULT '0',
  `depn` double NOT NULL DEFAULT '0',
  `datepurchased` date NOT NULL DEFAULT '1000-01-01',
  `disposalvalue` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
