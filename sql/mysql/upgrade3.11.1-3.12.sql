CREATE TABLE IF NOT EXISTS `fixedassetlocations` (
  `locationid` char(6) NOT NULL default '',
  `locationdescription` char(20) NOT NULL default '',
  PRIMARY KEY  (`locationid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE `assetmanager`;

CREATE TABLE `assetmanager` (
  `id` int(11) NOT NULL auto_increment,
  `stockid` varchar(20) NOT NULL default '',
  `serialno` varchar(30) NOT NULL default '',
  `location` varchar(15) NOT NULL default '',
  `cost` double NOT NULL default '0',
  `depn` double NOT NULL default '0',
  `datepurchased` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE `www_users` SET `modulesallowed`=(SELECT insert(`modulesallowed`, 15,0,"1,"));
INSERT INTO `config` (`confname`, `confvalue`) VALUES ('FrequentlyOrderedItems',0);
ALTER TABLE `www_users` CHANGE COLUMN `language` `language` varchar(10) NOT NULL DEFAULT 'en_GB';
ALTER TABLE `assetmanager` ADD COLUMN `disposalvalue` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `currencies` ADD COLUMN `decimalplaces` tinyint(3) NOT NULL DEFAULT 2 AFTER `hundredsname`;
ALTER TABLE `fixedassetlocations` ADD COLUMN `parentlocationid` char(6) DEFAULT '';
INSERT INTO `config` (`confname`, `confvalue`) VALUES ('NumberOfMonthMustBeShown', '6');

ALTER TABLE `holdreasons` DROP INDEX `ReasonCode`;
ALTER TABLE `chartmaster` DROP INDEX `AccountCode`;

ALTER TABLE `purchorders` ADD COLUMN `paymentterms` char(2) NOT NULL DEFAULT '';
ALTER TABLE `purchorders` ADD COLUMN `suppdeladdress1` varchar(40) NOT NULL DEFAULT '' AFTER deladd6;
ALTER TABLE `purchorders` ADD COLUMN `suppdeladdress2` varchar(40) NOT NULL DEFAULT '' AFTER suppdeladdress1;
ALTER TABLE `purchorders` ADD COLUMN `suppdeladdress3` varchar(40) NOT NULL DEFAULT '' AFTER suppdeladdress2;
ALTER TABLE `purchorders` ADD COLUMN `suppdeladdress4` varchar(40) NOT NULL DEFAULT '' AFTER suppdeladdress3;
ALTER TABLE `purchorders` ADD COLUMN `suppdeladdress5` varchar(20) NOT NULL DEFAULT '' AFTER suppdeladdress4;
ALTER TABLE `purchorders` ADD COLUMN `suppdeladdress6` varchar(15) NOT NULL DEFAULT '' AFTER suppdeladdress5;
ALTER TABLE `purchorders` ADD COLUMN `suppliercontact` varchar(30) NOT NULL DEFAULT '' AFTER suppdeladdress6;
ALTER TABLE `purchorders` ADD COLUMN `supptel` varchar(30) NOT NULL DEFAULT '' AFTER suppliercontact;
ALTER TABLE `purchorders` ADD COLUMN `tel` varchar(15) NOT NULL DEFAULT '' AFTER deladd6;
ALTER TABLE `purchorders` ADD COLUMN `port` varchar(40) NOT NULL DEFAULT '' ;

ALTER TABLE `suppliers` DROP FOREIGN KEY `suppliers_ibfk_4`;
UPDATE `suppliers` SET `factorcompanyid`=0 WHERE `factorcompanyid`=1;
DELETE FROM `factorcompanies` WHERE `coyname`='None';

INSERT INTO  `config` (`confname`, `confvalue`) VALUES ('LogPath', '');
INSERT INTO  `config` (`confname`, `confvalue`) VALUES ('LogSeverity', '0');

ALTER TABLE `www_users` ADD COLUMN `pdflanguage` tinyint(1) NOT NULL DEFAULT '0';

ALTER TABLE `purchorderauth` ADD COLUMN `offhold` tinyint(1) NOT NULL DEFAULT 0;

UPDATE `www_users` SET `modulesallowed` = '1,1,1,1,1,1,1,1,1,1'

UPDATE securitytokens SET tokenname = 'Petty Cash' WHERE tokenid = 6;

CREATE TABLE IF NOT EXISTS `pcashdetails` (
  `counterindex` int(20) NOT NULL AUTO_INCREMENT,
  `tabcode` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  `amount` double NOT NULL,
  `authorized` date NOT NULL COMMENT 'date cash assigment was revised and authorized by authorizer from tabs table',
  `posted` tinyint(4) NOT NULL COMMENT 'has (or has not) been posted into gltrans',
  `notes` text NOT NULL,
  `receipt` text COMMENT 'filename or path to scanned receipt or code of receipt to find physical receipt if tax guys or auditors show up',
  PRIMARY KEY (`counterindex`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `pcexpenses` (
  `codeexpense` varchar(20) NOT NULL COMMENT 'code for the group',
  `description` varchar(50) NOT NULL COMMENT 'text description, e.g. meals, train tickets, fuel, etc',
  `glaccount` int(11) NOT NULL COMMENT 'GL related account',
  PRIMARY KEY (`codeexpense`),
  KEY `pcexpenses_ibfk_1` (`glaccount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `pctabexpenses` (
  `typetabcode` varchar(20) NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  KEY `pctabexpenses_ibfk_1` (`typetabcode`),
  KEY `pctabexpenses_ibfk_2` (`codeexpense`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pctabs` (
  `tabcode` varchar(20) NOT NULL,
  `usercode` varchar(20) NOT NULL COMMENT 'code of user employee from www_users',
  `typetabcode` varchar(20) NOT NULL,
  `currency` char(3) NOT NULL,
  `tablimit` double NOT NULL,
  `authorizer` varchar(20) NOT NULL COMMENT 'code of user from www_users',
  `glaccountassignment` int(11) NOT NULL COMMENT 'gl account where the money comes from',
  `glaccountpcash` int(11) NOT NULL,
  PRIMARY KEY (`tabcode`),
  KEY `pctabs_ibfk_1` (`usercode`),
  KEY `pctabs_ibfk_2` (`typetabcode`),
  KEY `pctabs_ibfk_3` (`currency`),
  KEY `pctabs_ibfk_4` (`authorizer`),
  KEY `pctabs_ibfk_5` (`glaccountassignment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pctypetabs` (
  `typetabcode` varchar(20) NOT NULL COMMENT 'code for the type of petty cash tab',
  `typetabdescription` varchar(50) NOT NULL COMMENT 'text description, e.g. tab for CEO',
  PRIMARY KEY (`typetabcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `pcexpenses`
  ADD CONSTRAINT `pcexpenses_ibfk_1` FOREIGN KEY (`glaccount`) REFERENCES `chartmaster` (`accountcode`);

ALTER TABLE `pctabexpenses`
  ADD CONSTRAINT `pctabexpenses_ibfk_1` FOREIGN KEY (`typetabcode`) REFERENCES `pctypetabs` (`typetabcode`),
  ADD CONSTRAINT `pctabexpenses_ibfk_2` FOREIGN KEY (`codeexpense`) REFERENCES `pcexpenses` (`codeexpense`);

ALTER TABLE `pctabs`
  ADD CONSTRAINT `pctabs_ibfk_1` FOREIGN KEY (`usercode`) REFERENCES `www_users` (`userid`),
  ADD CONSTRAINT `pctabs_ibfk_2` FOREIGN KEY (`typetabcode`) REFERENCES `pctypetabs` (`typetabcode`),
  ADD CONSTRAINT `pctabs_ibfk_3` FOREIGN KEY (`currency`) REFERENCES `currencies` (`currabrev`),
  ADD CONSTRAINT `pctabs_ibfk_4` FOREIGN KEY (`authorizer`) REFERENCES `www_users` (`userid`),
  ADD CONSTRAINT `pctabs_ibfk_5` FOREIGN KEY (`glaccountassignment`) REFERENCES `chartmaster` (`accountcode`);

ALTER TABLE `supptrans`
  ADD COLUMN `inputdate` datetime NOT NULL AFTER `duedate` ;