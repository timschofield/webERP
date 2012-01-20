CREATE TABLE `audittrail` (
	`transactiondate` datetime NOT NULL default '0000-00-00',
	`userid` varchar(20) NOT NULL default '',
	`querystring` text,
	KEY `UserID` (`userid`),
  CONSTRAINT `audittrail_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `www_users` (`userid`)
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

INSERT INTO `factorcompanies` ( `id` , `coyname` ) VALUES (null, 'None');

ALTER TABLE `suppliers` ADD COLUMN  `factorcompanyid` int(11) NOT NULL default 1 AFTER `taxgroupid`;
ALTER TABLE `suppliers` ADD CONSTRAINT `suppliers_ibfk_4` FOREIGN KEY (`factorcompanyid`) REFERENCES `factorcompanies` (`id`);

ALTER TABLE `stockmaster` ADD COLUMN  `perishable` tinyint(1) NOT NULL default 0 AFTER `serialised`;
ALTER TABLE `stockmaster` ADD COLUMN `appendfile` varchar(40) NOT NULL default 'none' AFTER `serialised`;
ALTER TABLE `stockserialitems` ADD COLUMN  `expirationdate` datetime NOT NULL default '0000-00-00' AFTER `serialno`;
ALTER TABLE `bankaccounts` ADD COLUMN `currcode` CHAR( 3 ) NOT NULL AFTER `accountcode` ;
ALTER TABLE `bankaccounts` ADD INDEX ( `currcode` ) ;
ALTER TABLE `banktrans` CHANGE `exrate` `exrate` DOUBLE NOT NULL DEFAULT '1' COMMENT 'From bank account currency to payment currency';
ALTER TABLE `banktrans` ADD `functionalexrate` DOUBLE NOT NULL DEFAULT '1' COMMENT 'Account currency to functional currency';
ALTER TABLE `worequirements` DROP FOREIGN KEY `worequirements_ibfk_3`;
ALTER TABLE `worequirements` Add CONSTRAINT `worequirements_ibfk_3` FOREIGN KEY (`wo`, `parentstockid`) REFERENCES `woitems` (`wo`, `stockid`);

INSERT INTO `config` VALUES ('ProhibitNegativeStock','1');
INSERT INTO `systypes` (`typeid` ,`typename` ,`typeno`) VALUES ('36', 'Exchange Difference', '1');
INSERT INTO `systypes` (`typeid` ,`typename` ,`typeno`) VALUES ('40', 'Work Order', '1');
INSERT INTO `config` (`confname`, `confvalue`) VALUES ('UpdateCurrencyRatesDaily', '0');

UPDATE systypes SET typeno=(SELECT max(orderno) FROM salesorders) WHERE typeid=30;
UPDATE systypes SET typeno=(SELECT max(orderno) FROM purchorders) WHERE typeid=18;
UPDATE systypes SET typeno=(SELECT max(wo) FROM workorders) WHERE typeid=40;

CREATE TABLE `assetmanager` (
  `id` int(11) NOT NULL auto_increment,
  `serialno` varchar(30) NOT NULL default '',
  `assetglcode` int(11) NOT NULL default '0',
  `depnglcode` int(11) NOT NULL default '0',
  `description` varchar(30) NOT NULL default '',
  `lifetime` int(11) NOT NULL default 0,
  `location` varchar(15) NOT NULL default '',
  `cost` double NOT NULL default 0.0,
  `depn` double NOT NULL default 0.0,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;