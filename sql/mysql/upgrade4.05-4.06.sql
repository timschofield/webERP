ALTER TABLE stockmaster DROP lastcurcostdate;
ALTER TABLE stockmaster ADD lastcostupdate DATE NOT NULL;
INSERT INTO  `config` (`confname` ,`confvalue`)
VALUES ('InventoryManagerEmail',  '');
ALTER TABLE `banktrans` ADD INDEX ( `ref` );
ALTER TABLE  `pcexpenses` ADD  `tag` TINYINT( 4 ) NOT NULL DEFAULT  '0';
ALTER TABLE `debtortrans` DROP FOREIGN KEY `debtortrans_ibfk_1`;
UPDATE config SET confvalue='4.06.6' WHERE confname='VersionNumber';

CREATE TABLE `tenders` (
  `tenderid` int(11) NOT NULL DEFAULT '0',
  `location` varchar(5) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(40) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `closed` int(2) NOT NULL DEFAULT '0',
  `requiredbydate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`tenderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tenderitems` (
  `tenderid` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` varchar(40) NOT NULL DEFAULT '',
  `units` varchar(20) NOT NULL DEFAULT 'each',
  PRIMARY KEY (`tenderid`,`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tendersuppliers` (
  `tenderid` int(11) NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `email` varchar(40) NOT NULL DEFAULT '',
  `responded` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tenderid`,`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ('SupplierTenderCreate.php', '4', 'Create or Edit tenders');
ALTER TABLE `www_users` ADD `cancreatetender` tinyint(1) NOT NULL DEFAULT 0 AFTER `fullaccess`;
INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (37, 'Tenders', 0);

UPDATE config SET confvalue='4.06.7' WHERE confname='VersionNumber';