CREATE TABLE `fixedassettrans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assetid` int NOT NULL,
  `transtype` tinyint NOT NULL,
  `transdate` date NOT NULL,
  `transno` int NOT NULL,
  `periodno` smallint NOT NULL,
  `inputdate` date NOT NULL,
  `fixedassettranstype` varchar(8) NOT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `assetid` (`assetid`,`transtype`,`transno`),
  KEY `inputdate` (`inputdate`),
  KEY `transdate` (`transdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
