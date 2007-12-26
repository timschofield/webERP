CREATE TABLE `audittrail` (
	`transactiondate` datetime NOT NULL default '0000-00-00',
	`userid` varchar(20) NOT NULL default '',
	`querystring` text,
	KEY `UserID` (`userid`),
  CONSTRAINT `audittrail_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `www_users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `salesorders` CHANGE `contactemail` `contactemail` VARCHAR( 40 ) DEFAULT NULL;
INSERT INTO `config` ( `confname` , `confvalue` ) VALUES ('MonthsAuditTrail', '1');

CREATE TABLE `factorcompanies` (
  `id` int(11) NOT NULL auto_increment,
  `coyname` varchar(50) NOT NULL default '',
  `address1` varchar(40) NOT NULL default '',
  `address2` varchar(40) NOT NULL default '',
  `address3` varchar(40) NOT NULL default '',
  `address4` varchar(40) NOT NULL default '',
  `address5` varchar(20) NOT NULL default '',
  `address6` varchar(15) NOT NULL default '',
  `contact` varchar(25) NOT NULL default '',
  `telephone` varchar(25) NOT NULL default '',
  `fax` varchar(25) NOT NULL default '',
  `email` varchar(55) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `factorcompanies` ( `id` , `coyname` ) VALUES (null, 'None');

ALTER TABLE `suppliers` ADD COLUMN  `factorcompanyid` int(11) NOT NULL default 1 AFTER `taxgroupid`;
ALTER TABLE `suppliers` ADD CONSTRAINT `suppliers_ibfk_4` FOREIGN KEY (`factorcompanyid`) REFERENCES `factorcompanies` (`id`);

