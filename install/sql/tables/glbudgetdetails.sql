CREATE TABLE `glbudgetdetails` (
  `id` int NOT NULL AUTO_INCREMENT,
  `headerid` int NOT NULL DEFAULT '0',
  `account` varchar(20) NOT NULL DEFAULT '',
  `period` smallint NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `account` (`account`),
  KEY `headerid` (`headerid`,`account`,`period`)
) ENGINE=InnoDB AUTO_INCREMENT=2045 DEFAULT CHARSET=utf8mb3;
