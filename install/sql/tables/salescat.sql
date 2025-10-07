CREATE TABLE `salescat` (
  `salescatid` tinyint NOT NULL AUTO_INCREMENT,
  `parentcatid` tinyint DEFAULT NULL,
  `salescatname` varchar(50) DEFAULT NULL,
  `active` int NOT NULL DEFAULT '1' COMMENT '1 if active 0 if inactive',
  PRIMARY KEY (`salescatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
