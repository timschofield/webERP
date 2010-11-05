SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE accountgroups CONVERT TO CHARACTER SET utf8;
ALTER TABLE accountsection CONVERT TO CHARACTER SET utf8;
ALTER TABLE areas  CONVERT TO CHARACTER SET utf8;
ALTER TABLE audittrail CONVERT TO CHARACTER SET utf8;
ALTER TABLE bankaccounts CONVERT TO CHARACTER SET utf8;
ALTER TABLE banktrans CONVERT TO CHARACTER SET utf8;
ALTER TABLE bom CONVERT TO CHARACTER SET utf8;
ALTER TABLE buckets CONVERT TO CHARACTER SET utf8;
ALTER TABLE chartdetails CONVERT TO CHARACTER SET utf8;
ALTER TABLE chartmaster CONVERT TO CHARACTER SET utf8;
ALTER TABLE cogsglpostings CONVERT TO CHARACTER SET utf8;
ALTER TABLE companies CONVERT TO CHARACTER SET utf8;
ALTER TABLE config CONVERT TO CHARACTER SET utf8;
ALTER TABLE currencies CONVERT TO CHARACTER SET utf8;
ALTER TABLE custallocns CONVERT TO CHARACTER SET utf8;
ALTER TABLE custbranch CONVERT TO CHARACTER SET utf8;
ALTER TABLE custcontacts CONVERT TO CHARACTER SET utf8;
ALTER TABLE custnotes  CONVERT TO CHARACTER SET utf8;
ALTER TABLE debtorsmaster CONVERT TO CHARACTER SET utf8;
ALTER TABLE debtortrans CONVERT TO CHARACTER SET utf8;
ALTER TABLE debtortranstaxes  CONVERT TO CHARACTER SET utf8;
ALTER TABLE debtortype CONVERT TO CHARACTER SET utf8;
ALTER TABLE debtortypenotes CONVERT TO CHARACTER SET utf8;
ALTER TABLE deliverynotes CONVERT TO CHARACTER SET utf8;
ALTER TABLE discountmatrix CONVERT TO CHARACTER SET utf8;
ALTER TABLE edi_orders_seg_groups  CONVERT TO CHARACTER SET utf8;
ALTER TABLE edi_orders_segs CONVERT TO CHARACTER SET utf8;
ALTER TABLE ediitemmapping CONVERT TO CHARACTER SET utf8;
ALTER TABLE edimessageformat CONVERT TO CHARACTER SET utf8;
ALTER TABLE factorcompanies CONVERT TO CHARACTER SET utf8;
ALTER TABLE freightcosts  CONVERT TO CHARACTER SET utf8;
ALTER TABLE geocode_param CONVERT TO CHARACTER SET utf8;
ALTER TABLE gltrans CONVERT TO CHARACTER SET utf8;
ALTER TABLE grns  CONVERT TO CHARACTER SET utf8;
ALTER TABLE holdreasons CONVERT TO CHARACTER SET utf8;
ALTER TABLE lastcostrollup CONVERT TO CHARACTER SET utf8;
ALTER TABLE locations CONVERT TO CHARACTER SET utf8;
ALTER TABLE locstock  CONVERT TO CHARACTER SET utf8;
ALTER TABLE loctransfers  CONVERT TO CHARACTER SET utf8;
ALTER TABLE mrpcalendar  CONVERT TO CHARACTER SET utf8;
ALTER TABLE mrpdemands CONVERT TO CHARACTER SET utf8;
ALTER TABLE mrpdemandtypes CONVERT TO CHARACTER SET utf8;
ALTER TABLE orderdeliverydifferenceslog CONVERT TO CHARACTER SET utf8;
ALTER TABLE paymentmethods CONVERT TO CHARACTER SET utf8;
ALTER TABLE paymentterms CONVERT TO CHARACTER SET utf8;
ALTER TABLE periods CONVERT TO CHARACTER SET utf8;
ALTER TABLE prices CONVERT TO CHARACTER SET utf8;
ALTER TABLE purchdata CONVERT TO CHARACTER SET utf8;
ALTER TABLE purchorderauth CONVERT TO CHARACTER SET utf8;
ALTER TABLE purchorderdetails CONVERT TO CHARACTER SET utf8;
ALTER TABLE purchorders   CONVERT TO CHARACTER SET utf8;
ALTER TABLE recurringsalesorders CONVERT TO CHARACTER SET utf8;
ALTER TABLE recurrsalesorderdetails CONVERT TO CHARACTER SET utf8;
ALTER TABLE reportcolumns CONVERT TO CHARACTER SET utf8;
ALTER TABLE reportfields  CONVERT TO CHARACTER SET utf8;
ALTER TABLE reportheaders CONVERT TO CHARACTER SET utf8;
ALTER TABLE reportlinks   CONVERT TO CHARACTER SET utf8;
ALTER TABLE reports  CONVERT TO CHARACTER SET utf8;
ALTER TABLE salesanalysis CONVERT TO CHARACTER SET utf8;
ALTER TABLE salescat CONVERT TO CHARACTER SET utf8;
ALTER TABLE salescatprod  CONVERT TO CHARACTER SET utf8;
ALTER TABLE salesglpostings  CONVERT TO CHARACTER SET utf8;
ALTER TABLE salesman      CONVERT TO CHARACTER SET utf8;
ALTER TABLE salesorderdetails  CONVERT TO CHARACTER SET utf8;
ALTER TABLE salesorders CONVERT TO CHARACTER SET utf8;
ALTER TABLE salestypes  CONVERT TO CHARACTER SET utf8;
ALTER TABLE scripts  CONVERT TO CHARACTER SET utf8;
ALTER TABLE securitygroups CONVERT TO CHARACTER SET utf8;
ALTER TABLE securityroles CONVERT TO CHARACTER SET utf8;
ALTER TABLE securitytokens CONVERT TO CHARACTER SET utf8;
ALTER TABLE shipmentcharges CONVERT TO CHARACTER SET utf8;
ALTER TABLE shipments  CONVERT TO CHARACTER SET utf8;
ALTER TABLE shippers CONVERT TO CHARACTER SET utf8;
ALTER TABLE stockcategory CONVERT TO CHARACTER SET utf8;
ALTER TABLE stockcatproperties CONVERT TO CHARACTER SET utf8;
ALTER TABLE stockcheckfreeze CONVERT TO CHARACTER SET utf8;
ALTER TABLE stockcounts   CONVERT TO CHARACTER SET utf8;
ALTER TABLE stockitemproperties CONVERT TO CHARACTER SET utf8;
ALTER TABLE stockmaster CONVERT TO CHARACTER SET utf8;
ALTER TABLE stockmoves CONVERT TO CHARACTER SET utf8;
ALTER TABLE stockmovestaxes CONVERT TO CHARACTER SET utf8;
ALTER TABLE stockserialitems CONVERT TO CHARACTER SET utf8;
ALTER TABLE stockserialmoves CONVERT TO CHARACTER SET utf8;
ALTER TABLE suppallocs CONVERT TO CHARACTER SET utf8;
ALTER TABLE suppliercontacts CONVERT TO CHARACTER SET utf8;
ALTER TABLE suppliers CONVERT TO CHARACTER SET utf8;
ALTER TABLE supptrans CONVERT TO CHARACTER SET utf8;
ALTER TABLE supptranstaxes CONVERT TO CHARACTER SET utf8;
ALTER TABLE systypes  CONVERT TO CHARACTER SET utf8;
ALTER TABLE tags CONVERT TO CHARACTER SET utf8;
ALTER TABLE taxauthorities CONVERT TO CHARACTER SET utf8;
ALTER TABLE taxauthrates CONVERT TO CHARACTER SET utf8;
ALTER TABLE taxcategories CONVERT TO CHARACTER SET utf8;
ALTER TABLE taxgroups CONVERT TO CHARACTER SET utf8;
ALTER TABLE taxgrouptaxes CONVERT TO CHARACTER SET utf8;
ALTER TABLE taxprovinces  CONVERT TO CHARACTER SET utf8;
ALTER TABLE unitsofmeasure CONVERT TO CHARACTER SET utf8;
ALTER TABLE woitems CONVERT TO CHARACTER SET utf8;
ALTER TABLE worequirements CONVERT TO CHARACTER SET utf8;
ALTER TABLE workcentres CONVERT TO CHARACTER SET utf8;
ALTER TABLE workorders CONVERT TO CHARACTER SET utf8;
ALTER TABLE woserialnos CONVERT TO CHARACTER SET utf8;
ALTER TABLE www_users CONVERT TO CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS `fixedassetlocations` (
  `locationid` char(6) NOT NULL default '',
  `locationdescription` char(20) NOT NULL default '',
  `parentlocationid` char(6) DEFAULT '',
  PRIMARY KEY  (`locationid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `assetmanager`;

CREATE TABLE `assetmanager` (
  `id` int(11) NOT NULL auto_increment,
  `stockid` varchar(20) NOT NULL default '',
  `serialno` varchar(30) NOT NULL default '',
  `location` varchar(15) NOT NULL default '',
  `cost` double NOT NULL default '0',
  `depn` double NOT NULL default '0',
  `datepurchased` date NOT NULL default '0000-00-00',
  `disposalvalue` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE `www_users` SET `modulesallowed`=(SELECT insert(`modulesallowed`, 15,0,"1,"));
INSERT INTO `config` (`confname`, `confvalue`) VALUES ('FrequentlyOrderedItems',0);
ALTER TABLE `www_users` CHANGE COLUMN `language` `language` varchar(10) NOT NULL DEFAULT 'en_GB.utf8';

ALTER TABLE `currencies` ADD COLUMN `decimalplaces` tinyint(3) NOT NULL DEFAULT 2 AFTER `hundredsname`;

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

ALTER TABLE `suppliers` DROP KEY `suppliers_ibfk_4`;
UPDATE `suppliers` SET `factorcompanyid`=0 WHERE `factorcompanyid`=1;
DELETE FROM `factorcompanies` WHERE `coyname`='None';

INSERT INTO  `config` (`confname`, `confvalue`) VALUES ('LogPath', '');
INSERT INTO  `config` (`confname`, `confvalue`) VALUES ('LogSeverity', '0');

ALTER TABLE `www_users` ADD COLUMN `pdflanguage` tinyint(1) NOT NULL DEFAULT '0';

ALTER TABLE `purchorderauth` ADD COLUMN `offhold` tinyint(1) NOT NULL DEFAULT 0;

UPDATE `www_users` SET `modulesallowed` = '1,1,1,1,1,1,1,1,1,1';

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
  KEY (`glaccount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `pctabexpenses` (
  `typetabcode` varchar(20) NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  KEY (`typetabcode`),
  KEY (`codeexpense`)
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
  KEY (`usercode`),
  KEY (`typetabcode`),
  KEY (`currency`),
  KEY (`authorizer`),
  KEY (`glaccountassignment`)
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

ALTER TABLE `debtortrans`
  ADD COLUMN `inputdate` datetime NOT NULL AFTER `trandate` ;

ALTER TABLE `reportfields` CHANGE COLUMN `fieldname` `fieldname` VARCHAR(60) NOT NULL DEFAULT '';

INSERT INTO `config` (`confname`, `confvalue`) VALUES ('RequirePickingNote',0);

CREATE TABLE IF NOT EXISTS `pickinglists` (
  `pickinglistno` int(11) NOT NULL DEFAULT 0,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `pickinglistdate` date NOT NULL default '0000-00-00',
  `dateprinted` date NOT NULL default '0000-00-00',
  `deliverynotedate` date NOT NULL default '0000-00-00',
  CONSTRAINT `pickinglists_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`),
  PRIMARY KEY (`pickinglistno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pickinglistdetails` (
  `pickinglistno` int(11) NOT NULL DEFAULT 0,
  `pickinglistlineno` int(11) NOT NULL DEFAULT 0,
  `orderlineno` int(11) NOT NULL DEFAULT 0,
  `qtyexpected` double NOT NULL default 0.00,
  `qtypicked` double NOT NULL default 0.00,
  CONSTRAINT `pickinglistdetails_ibfk_1` FOREIGN KEY (`pickinglistno`) REFERENCES `pickinglists` (`pickinglistno`),
  PRIMARY KEY (`pickinglistno`, `pickinglistlineno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `systypes` VALUES(19, 'Picking List', 0);
ALTER TABLE `prices` ADD `startdate` DATE NOT NULL DEFAULT '0000-00-00' , ADD `enddate` DATE NOT NULL DEFAULT '9999-12-31';
ALTER TABLE prices DROP PRIMARY KEY ,
ADD PRIMARY KEY ( `stockid` , `typeabbrev` , `currabrev` , `debtorno` , `branchcode`, `startdate` , `enddate` ) ;
ALTER TABLE purchdata ADD column minorderqty int(11) NOT NULL default 1;
UPDATE prices SET startdate='1999-01-01', enddate='';

ALTER TABLE stockcheckfreeze ADD COLUMN stockcheckdate date NOT NULL;

ALTER TABLE suppliers add (email varchar(55),fax varchar(25), telephone varchar(25));

ALTER TABLE `www_users` add `supplierid` varchar(10) NOT NULL DEFAULT '' AFTER `customerid`;
INSERT INTO `securityroles` VALUES (9,'Supplier Log On Only');
UPDATE `securitytokens` SET `tokenname`='Supplier centre - Supplier access only' WHERE tokenid=9;
INSERT INTO `securitygroups` VALUES(9,9);

ALTER TABLE locations add cashsalecustomer VARCHAR(21) NOT NULL DEFAULT '';

DROP TABLE contracts;
DROP TABLE contractreqts;
DROP TABLE contractbom;

CREATE TABLE IF NOT EXISTS `contractbom` (
   contractref varchar(20) NOT NULL DEFAULT '0',
   `stockid` varchar(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`contractref`,`stockid`,`workcentreadded`),
  KEY `Stockid` (`stockid`),
  KEY `ContractRef` (`contractref`),
  KEY `WorkCentreAdded` (`workcentreadded`),
  CONSTRAINT `contractbom_ibfk_1` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `contractbom_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `contractreqts` (
  `contractreqid` int(11) NOT NULL AUTO_INCREMENT,
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `requirement` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  `costperunit` double NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`contractreqid`),
  KEY `ContractRef` (`contractref`),
  CONSTRAINT `contractreqts_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `contracts` (
  `contractref` varchar(20) NOT NULL DEFAULT '',
  `contractdescription` text NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
   `loccode` varchar(5) NOT NULL DEFAULT '',
  `status` tinyint NOT NULL DEFAULT 0,
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `customerref` VARCHAR( 20 ) NOT NULL DEFAULT '',
  `margin` double NOT NULL DEFAULT '1',
  `wo` int(11) NOT NULL DEFAULT '0',
  `requireddate` date NOT NULL DEFAULT '0000-00-00',
  `drawing` varchar(50) NOT NULL DEFAULT '',
  `exrate` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`contractref`),
  KEY `OrderNo` (`orderno`),
  KEY `CategoryID` (`categoryid`),
  KEY `Status` (`status`),
  KEY `WO` (`wo`),
  KEY `loccode` (`loccode`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`debtorno`, `branchcode`) REFERENCES `custbranch` (`debtorno`, `branchcode`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `salestypes` CHANGE COLUMN `sales_type` `sales_type` VARCHAR(40) NOT NULL DEFAULT '';
INSERT INTO `config` VALUES ('ShowValueOnGRN', 1);

CREATE TABLE IF NOT EXISTS `offers` (
  offerid int(11) NOT NULL AUTO_INCREMENT,
  tenderid int(11) NOT NULL DEFAULT 0,
  supplierid varchar(10) NOT NULL DEFAULT '',
  stockid varchar(20) NOT NULL DEFAULT '',
  quantity double NOT NULL DEFAULT 0.0,
  uom varchar(15) NOT NULL DEFAULT '',
  price double NOT NULL DEFAULT 0.0,
  expirydate date NOT NULL DEFAULT '0000-00-00',
  currcode char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`offerid`),
  CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `offers_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `config` VALUES('PurchasingManagerEmail', '');

CREATE TABLE IF NOT EXISTS `emailsettings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(30) NOT NULL,
  `port` char(5) NOT NULL,
  `heloaddress` varchar(20) NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(30) DEFAULT NULL,
  `timeout` int(11) DEFAULT '5',
  `companyname` varchar(50) DEFAULT NULL,
  `auth` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO emailsettings VALUES(Null, 'localhost', 25, 'helo', '', '', 5, '', 0);

ALTER TABLE `salesorderdetails` ADD COLUMN `commissionrate` double NOT NULL DEFAULT 0.0;
ALTER TABLE `salesorderdetails` ADD COLUMN `commissionearned` double NOT NULL DEFAULT 0.0;

CREATE TABLE `suppliertype` (
  `typeid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `typename` varchar(100) NOT NULL,
  PRIMARY KEY (`typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `config` VALUES ('DefaultSupplierType', 1);
INSERT INTO `suppliertype` VALUES(1, 'Default');
ALTER TABLE `suppliers` ADD COLUMN `supptype` tinyint(4) NOT NULL DEFAULT 1 AFTER `address6`;

ALTER TABLE `loctransfers` CHANGE COLUMN `shipqty` `shipqty` double NOT NULL DEFAULT 0.0;

INSERT INTO `config` VALUES ('VersionNumber', '4.00-RC1');
UPDATE `securitytokens` SET `tokenname`='Prices Security' WHERE tokenid=12;

ALTER TABLE `www_users` CHANGE `supplierid` `supplierid` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `orderdeliverydifferenceslog` DROP PRIMARY KEY;

ALTER TABLE `loctransfers` CHANGE COLUMN `recqty` `recqty` double NOT NULL DEFAULT 0.0;

CREATE TABLE IF NOT EXISTS `contractcharges` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `contractref` varchar(20) NOT NULL,
  `transtype` SMALLINT NOT NULL DEFAULT 20,
  `transno` INT NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0,
  `narrative` TEXT NOT NULL DEFAULT '',
  `anticipated` TINYINT NOT NULL DEFAULT 0,
  INDEX ( `contractref` , `transtype` , `transno` ),
  CONSTRAINT `contractcharges_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`),
  CONSTRAINT `contractcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES ('32', 'Contract Close', '1');

ALTER TABLE `reports` ADD `col9width` INT( 3 ) NOT NULL DEFAULT '25' AFTER `col8width` ;

ALTER TABLE `reports` ADD `col10width` INT( 3 ) NOT NULL DEFAULT '25' AFTER `col9width` ;

ALTER TABLE `reports` ADD `col11width` INT( 3 ) NOT NULL DEFAULT '25' AFTER `col10width` ;

ALTER TABLE `reports` ADD `col12width` INT( 3 ) NOT NULL DEFAULT '25' AFTER `col11width` ;

ALTER TABLE `reports` ADD `col13width` INT( 3 ) NOT NULL DEFAULT '25' AFTER `col12width` ;

ALTER TABLE `reports` ADD `col14width` INT( 3 ) NOT NULL DEFAULT '25' AFTER `col13width` ;

ALTER TABLE `reports` ADD `col15width` INT( 3 ) NOT NULL DEFAULT '25' AFTER `col14width` ;

ALTER TABLE `reports` ADD `col16width` INT( 3 ) NOT NULL DEFAULT '25' AFTER `col15width` ;

ALTER TABLE `reports` ADD `col17width` INT( 3 ) NOT NULL DEFAULT '25' AFTER `col16width` ;

ALTER TABLE `reports` ADD `col18width` INT( 3 ) NOT NULL DEFAULT '25' AFTER `col17width` ;

ALTER TABLE `reports` ADD `col19width` INT( 3 ) NOT NULL DEFAULT '25' AFTER `col18width` ;

ALTER TABLE `reports` ADD `col20width` INT( 3 ) NOT NULL DEFAULT '25' AFTER `col19width` ;

ALTER TABLE `reportfields` CHANGE `fieldname` `fieldname` VARCHAR( 80) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '';
