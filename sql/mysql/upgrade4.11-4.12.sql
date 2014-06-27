INSERT INTO  `systypes` (`typeid` ,`typename` ,`typeno`) VALUES ('600',  'Auto Supplier Number',  '0');
INSERT INTO config (confname, confvalue) VALUES ('AutoSupplierNo', '0');
DELETE FROM config WHERE confname='DefaultTheme';
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('PDFWOPrint.php', '11', 'Produces W/O Paperwork');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('PDFFGLabel.php', '11', 'Produces FG Labels');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('PDFQALabel.php', '2', 'Produces a QA label on receipt of stock');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('CustItem.php', '11', 'Customer Items');
ALTER TABLE `woitems` ADD `comments` LONGBLOB NULL DEFAULT NULL ;
ALTER TABLE  `www_users` CHANGE  `modulesallowed`  `modulesallowed` VARCHAR( 25 ) NOT NULL;
INSERT INTO scripts VALUES ('CostUpdate','10','NB Not a script but allows users to maintain item costs from withing StockCostUpdate.php');
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
ALTER table pricematrix ADD column currabrev char(3) NOT NULL DEFAULT '';
ALTER table pricematrix ADD column startdate date NOT NULL DEFAULT '0000-00-00';
ALTER table pricematrix ADD column enddate date NOT NULL DEFAULT '9999-12-31';
ALTER table pricematrix DROP PRIMARY KEY;
ALTER table pricematrix ADD PRIMARY KEY (`salestype`,`stockid`,`currabrev`,`quantitybreak`,`startdate`,`enddate`);
ALTER table pricematrix DROP KEY `DiscountCategory`;
ALTER table pricematrix ADD KEY currabrev(`currabrev`);
ALTER table pricematrix ADD KEY stockid(`stockid`);
ALTER TABLE  `debtortrans` CHANGE  `consignment`  `consignment` VARCHAR( 20 ) NOT NULL DEFAULT  '';
ALTER TABLE `workorders` ADD `closecomments` LONGBLOB NULL DEFAULT NULL ;

CREATE TABLE IF NOT EXISTS `relateditems` (
  `stockid` varchar(20) CHARACTER SET utf8 NOT NULL,
  `related` varchar(20) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`stockid`,`related`),
  UNIQUE KEY `Related` (`related`,`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO  `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ('RelatedItemsUpdate.php',  '2',  'Maintains Related Items');
INSERT INTO scripts VALUES('Z_ImportDebtors.php',15,'Import debtors by csv file');
ALTER table purchorders MODIFY tel varchar(30) NOT NULL DEFAULT '';
UPDATE config SET confvalue='4.12' WHERE confname='VersionNumber';



