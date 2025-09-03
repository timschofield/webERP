CREATE TABLE `sessions` (
  `sessionid` char(32),
  `logintime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userid` varchar(20),
  `script` varchar(100) NOT NULL DEFAULT '',
  `scripttime` TIMESTAMP NULL,
  PRIMARY KEY (`sessionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
