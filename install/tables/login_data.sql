CREATE TABLE `login_data` (
  `sessionid` char(26) NOT NULL,
  `userid` varchar(20) DEFAULT NULL,
  `login` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `script` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`sessionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
