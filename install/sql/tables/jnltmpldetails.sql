CREATE TABLE `jnltmpldetails` (
  `linenumber` int NOT NULL DEFAULT '0',
  `templateid` int NOT NULL DEFAULT '0',
  `tags` varchar(50) NOT NULL DEFAULT '0',
  `accountcode` varchar(20) NOT NULL DEFAULT '1',
  `amount` double NOT NULL DEFAULT '0',
  `narrative` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`templateid`,`linenumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
