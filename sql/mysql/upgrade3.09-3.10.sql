CREATE TABLE `custcontacts` (
  `contid` int(11) NOT NULL auto_increment,
  `debtorno` varchar(10) NOT NULL,
  `contactname` varchar(40) NOT NULL,
  `role` varchar(40) NOT NULL,
  `phoneno` varchar(20) NOT NULL,
  `notes` varchar(255) NOT NULL,
  PRIMARY KEY  (`contid`)
) ENGINE=InnoDB;

ALTER TABLE `suppliers` ADD COLUMN `taxref` varchar(20) NOT NULL default '' AFTER `factorcompanyid`;

CREATE TABLE `tags` (
`tagref` tinyint(4) NOT NULL auto_increment,
`tagdescription` varchar(50) NOT NULL,
PRIMARY KEY (`tagref`)
) ENGINE=InnoDB;

ALTER TABLE `gltrans` ADD COLUMN `tag` tinyint(4) NOT NULL default '0' AFTER `jobref`;

ALTER TABLE `custbranch` DROP COLUMN `vtiger_accountid`;
ALTER TABLE `salesorders` DROP COLUMN `vtiger_accountid`;
ALTER TABLE `stockmaster` DROP COLUMN `vtiger_productid`;
DELETE FROM `config` WHERE `confname`='vtiger_integration';

ALTER TABLE `custbranch` ADD `lat` FLOAT( 10, 6 ) NOT NULL AFTER `braddress6` ,
ADD `lng` FLOAT( 10, 6 ) NOT NULL AFTER `lat`;
ALTER TABLE `suppliers` ADD `lat` FLOAT( 10, 6 ) NOT NULL AFTER `address6` ,
ADD `lng` FLOAT( 10, 6 ) NOT NULL AFTER `lat`;

CREATE TABLE `geocode_param` (
 `geocodeid` varchar(4) NOT NULL default '',
 `geocode_key` varchar(200) NOT NULL default '',
 `center_long` varchar(20) NOT NULL default '',
 `center_lat` varchar(20) NOT NULL default '',
 `map_height` varchar(10) NOT NULL default '',
 `map_width` varchar(10) NOT NULL default '',
 `map_host` varchar(50) NOT NULL default ''
) ENGINE=InnoDB;

INSERT INTO `config` ( `confname` , `confvalue` ) VALUES ('geocode_integration', '0');

INSERT INTO `config` ( `confname` , `confvalue` ) VALUES ('DefaultCustomerType', '1');

CREATE TABLE `debtortype` (
`typeid` tinyint(4) NOT NULL auto_increment,
`typename` varchar(100) NOT NULL,
PRIMARY KEY (`typeid`)
) ENGINE=InnoDB;

INSERT INTO `debtortype` ( `typeid` , `typename` ) VALUES (1, 'Default');

ALTER TABLE `debtorsmaster` ADD `typeid` tinyint(4) NOT NULL default '1';
ALTER TABLE `debtorsmaster` ADD CONSTRAINT `debtorsmaster_ibfk_5` FOREIGN KEY (`typeid`) REFERENCES `debtortype` (`typeid`);
ALTER TABLE `purchdata` ADD `effectivefrom` DATE NOT NULL;

CREATE TABLE `debtortypenotes` (
`noteid` tinyint(4) NOT NULL auto_increment,
`typeid` tinyint(4) NOT NULL default '0',
`href` varchar(100) NOT NULL,
`note` varchar(200) NOT NULL,
`date` date NOT NULL default '0000-00-00',
`priority` varchar(20) NOT NULL,
PRIMARY KEY (`noteid`)
) ENGINE=InnoDB;

CREATE TABLE `custnotes` (
`noteid` tinyint(4) NOT NULL auto_increment,
`debtorno` varchar(10) NOT NULL default '0',
`href` varchar(100) NOT NULL,
`note` varchar(200) NOT NULL,
`date` date NOT NULL default '0000-00-00',
`priority` varchar(20) NOT NULL,
PRIMARY KEY (`noteid`)
) ENGINE=InnoDB;

INSERT INTO `config` ( `confname` , `confvalue` ) VALUES ('Extended_CustomerInfo', '0');
INSERT INTO `config` ( `confname` , `confvalue` ) VALUES ('Extended_SupplierInfo', '0');

ALTER TABLE salesglpostings MODIFY COLUMN area VARCHAR(3) NOT NULL;
ALTER TABLE salesanalysis MODIFY COLUMN area VARCHAR(3) NOT NULL;
ALTER TABLE `debtortrans` CHANGE `trandate` `trandate` DATE NOT NULL DEFAULT '0000-00-00';