INSERT INTO  `systypes` (`typeid` ,`typename` ,`typeno`) VALUES ('600',  'Auto Supplier Number',  '0');
INSERT INTO config (confname, confvalue) VALUES ('AutoSupplierNo', '0');
DELETE FROM config WHERE confname='DefaultTheme';
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('PDFWOPrint.php', '11', 'Produces W/O Paperwork');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('PDFFGLabel.php', '11', 'Produces FG Labels');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('PDFQALabel.php', '2', 'Produces a QA label on receipt of stock');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('CustItem.php', '11', 'Customer Items');
ALTER TABLE `woitems` ADD `comments` LONGBLOB NULL DEFAULT NULL ;
ALTER TABLE  `www_users` CHANGE  `modulesallowed`  `modulesallowed` VARCHAR( 25 ) NOT NULL;
CREATE TABLE `custitem` (
  `debtorno` char(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `cust_part` varchar(20) NOT NULL DEFAULT '',
  `cust_description` varchar(30) NOT NULL DEFAULT '',
  `customersuom` char(50) NOT NULL DEFAULT '',
  `conversionfactor` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`debtorno`,`stockid`),
  KEY `StockID` (`stockid`),
  KEY `Debtorno` (`debtorno`),
  CONSTRAINT ` custitem _ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT ` custitem _ibfk_2` FOREIGN KEY (`debtorno`) REFERENCES `debtorsmaster` (`debtorno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
UPDATE config SET confvalue='4.12' WHERE confname='VersionNumber';
