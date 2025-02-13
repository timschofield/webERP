CREATE TABLE `login_data` (
  `sessionid` char(32) DEFAULT NULL,
  `userid` varchar(20) DEFAULT NULL,
  `login` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `script` varchar(100) NOT NULL DEFAULT '',
  KEY `sessionid` (`sessionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3