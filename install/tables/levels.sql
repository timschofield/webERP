CREATE TABLE `levels` (
  `part` char(20) DEFAULT NULL,
  `level` int DEFAULT NULL,
  `leadtime` smallint NOT NULL DEFAULT '0',
  `pansize` double NOT NULL DEFAULT '0',
  `shrinkfactor` double NOT NULL DEFAULT '0',
  `eoq` double NOT NULL DEFAULT '0',
  KEY `part` (`part`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
