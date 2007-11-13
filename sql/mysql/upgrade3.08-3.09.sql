CREATE TABLE `audittrail` (
	`transactiondate` datetime NOT NULL default '0000-00-00',
	`userid` varchar(20) NOT NULL default '',
	`querystring` text,
	KEY `UserID` (`userid`),
  CONSTRAINT `audittrail_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `www_users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `salesorders` CHANGE `contactemail` `contactemail` VARCHAR( 40 ) DEFAULT NULL;
INSERT INTO `config` ( `confname` , `confvalue` ) VALUES ('MonthsAuditTrail', '1');
