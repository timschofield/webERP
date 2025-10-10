CREATE TABLE `qasamples` (
  `sampleid` int NOT NULL AUTO_INCREMENT,
  `prodspeckey` varchar(25) NOT NULL DEFAULT '',
  `lotkey` varchar(25) NOT NULL DEFAULT '',
  `identifier` varchar(10) NOT NULL DEFAULT '',
  `createdby` varchar(15) NOT NULL DEFAULT '',
  `sampledate` date NOT NULL DEFAULT '1000-01-01',
  `comments` varchar(255) NOT NULL DEFAULT '',
  `cert` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`sampleid`),
  KEY `prodspeckey` (`prodspeckey`,`lotkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
