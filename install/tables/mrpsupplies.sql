CREATE TABLE `mrpsupplies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `part` char(20) DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `supplyquantity` double DEFAULT NULL,
  `ordertype` varchar(6) DEFAULT NULL,
  `orderno` int DEFAULT NULL,
  `mrpdate` date DEFAULT NULL,
  `updateflag` smallint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `part` (`part`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3;
