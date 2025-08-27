CREATE TABLE `relateditems` (
  `stockid` varchar(20) NOT NULL,
  `related` varchar(20) NOT NULL,
  PRIMARY KEY (`stockid`,`related`),
  UNIQUE KEY `Related` (`related`,`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
