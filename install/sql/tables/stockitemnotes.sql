CREATE TABLE `stockitemnotes` (
  `noteid` int NOT NULL AUTO_INCREMENT,
  `stockid` varchar(64) NOT NULL DEFAULT '0',
  `note` text NOT NULL,
  `date` date NOT NULL DEFAULT '1000-01-01',
  PRIMARY KEY (`noteid`),
  KEY `stockitemnotes_ibfk_1` (`stockid`),
  CONSTRAINT `stockitemnotes_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  COLLATE=utf8mb4_general_ci;
