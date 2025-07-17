CREATE TABLE `mrprequirements` (
  `part` char(20) DEFAULT NULL,
  `daterequired` date DEFAULT NULL,
  `quantity` double DEFAULT NULL,
  `mrpdemandtype` varchar(6) DEFAULT NULL,
  `orderno` int DEFAULT NULL,
  `directdemand` smallint DEFAULT NULL,
  `whererequired` char(20) DEFAULT NULL,
  KEY `part` (`part`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
