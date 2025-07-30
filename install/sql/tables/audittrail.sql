CREATE TABLE `audittrail` (
  `transactiondate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `userid` varchar(20) NOT NULL DEFAULT '',
  `querystring` text DEFAULT NULL,
  KEY `UserID` (`userid`),
  KEY `transactiondate` (`transactiondate`),
  KEY `transactiondate_2` (`transactiondate`),
  KEY `transactiondate_3` (`transactiondate`),
  CONSTRAINT `audittrail_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `www_users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
