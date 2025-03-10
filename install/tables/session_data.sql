CREATE TABLE `session_data` (
  `userid` varchar(20) NOT NULL,
  `field` varchar(100) NOT NULL DEFAULT '',
  `value` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`userid`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
