CREATE TABLE `relateditems` (
  `stockid` varchar(64) NOT NULL,
  `related` varchar(64) NOT NULL,
  PRIMARY KEY (`stockid`,`related`),
  UNIQUE KEY `Related` (`related`,`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
