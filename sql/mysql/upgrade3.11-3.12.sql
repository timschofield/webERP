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
INSERT INTO config (`confname`, `confvalue`) VALUES ('FrequentlyOrderedItems',0);
ALTER TABLE `www_users` CHANGE COLUMN `language` `language` varchar(10) NOT NULL DEFAULT 'en_GB';
ALTER TABLE `assetmanager` ADD COLUMN `disposalvalue` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `currencies` ADD COLUMN `decimalplaces` tinyint(3) NOT NULL DEFAULT 2 AFTER `hundredsname`;
ALTER TABLE `fixedassetlocations` ADD COLUMN `parentlocationid` char(6) DEFAULT '';
INSERT INTO `config` (`confname`, `confvalue`) VALUES ('NumberOfMonthMustBeShown', '6');

ALTER TABLE `holdreasons` DROP INDEX `ReasonCode`;
ALTER TABLE `chartmaster` DROP INDEX `AccountCode`;