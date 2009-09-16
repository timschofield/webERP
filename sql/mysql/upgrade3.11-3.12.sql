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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `currencies` ADD `decimalplaces` tinyint(3) NOT NULL default 2 AFTER `hundredsname`;