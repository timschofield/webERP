CREATE TABLE `login_data` (
  `sessionid` char(32),
  `userid` varchar(20),
  `login` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `script` varchar(100) NOT NULL DEFAULT "",
  PRIMARY KEY (`sessionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3