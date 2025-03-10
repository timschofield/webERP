CREATE TABLE `gltags` (
  `counterindex` int NOT NULL DEFAULT '0',
  `tagref` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`counterindex`,`tagref`),
  KEY `tagref` (`tagref`),
  CONSTRAINT `gltags_ibfk_1` FOREIGN KEY (`counterindex`) REFERENCES `gltrans` (`counterindex`),
  CONSTRAINT `gltags_ibfk_2` FOREIGN KEY (`tagref`) REFERENCES `tags` (`tagref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
