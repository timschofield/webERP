CREATE TABLE `pcreceipts` (
  `counterindex` int NOT NULL AUTO_INCREMENT,
  `pccashdetail` int NOT NULL DEFAULT '0' COMMENT 'Expenses record identity',
  `hashfile` varchar(32) NOT NULL DEFAULT '' COMMENT 'MD5 hash of uploaded receipt file',
  `type` varchar(80) NOT NULL DEFAULT '' COMMENT 'Mime type of uploaded receipt file',
  `extension` varchar(4) NOT NULL DEFAULT '' COMMENT 'File extension of uploaded receipt',
  `size` int NOT NULL DEFAULT '0' COMMENT 'File size of uploaded receipt',
  PRIMARY KEY (`counterindex`),
  KEY `pcreceipts_ibfk_1` (`pccashdetail`),
  CONSTRAINT `pcreceipts_ibfk_1` FOREIGN KEY (`pccashdetail`) REFERENCES `pcashdetails` (`counterindex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
