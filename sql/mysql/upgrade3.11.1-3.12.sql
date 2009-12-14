CREATE TABLE IF NOT EXISTS `fixedassetlocations` (
  `locationid` char(6) NOT NULL default '',
  `locationdescription` char(20) NOT NULL default '',
  PRIMARY KEY  (`locationid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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