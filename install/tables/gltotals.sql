CREATE TABLE `gltotals` (
  `account` varchar(20) NOT NULL DEFAULT '',
  `period` smallint NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`account`,`period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
