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

ALTER TABLE `suppliers` DROP FOREIGN KEY `suppliers_ibfk_4`;
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

ALTER TABLE `pcexpenses`   ADD CONSTRAINT `pcexpenses_ibfk_1` FOREIGN KEY (`glaccount`) REFERENCES `chartmaster` (`accountcode`);

ALTER TABLE `pctabexpenses`
  ADD CONSTRAINT `pctabexpenses_ibfk_1` FOREIGN KEY (`typetabcode`) REFERENCES `pctypetabs` (`typetabcode`),
  ADD CONSTRAINT `pctabexpenses_ibfk_2` FOREIGN KEY (`codeexpense`) REFERENCES `pcexpenses` (`codeexpense`);

ALTER TABLE `pctabs`
  ADD CONSTRAINT `pctabs_ibfk_1` FOREIGN KEY (`usercode`) REFERENCES `www_users` (`userid`),
  ADD CONSTRAINT `pctabs_ibfk_2` FOREIGN KEY (`typetabcode`) REFERENCES `pctypetabs` (`typetabcode`),
  ADD CONSTRAINT `pctabs_ibfk_3` FOREIGN KEY (`currency`) REFERENCES `currencies` (`currabrev`),
  ADD CONSTRAINT `pctabs_ibfk_4` FOREIGN KEY (`authorizer`) REFERENCES `www_users` (`userid`),
  ADD CONSTRAINT `pctabs_ibfk_5` FOREIGN KEY (`glaccountassignment`) REFERENCES `chartmaster` (`accountcode`);

ALTER TABLE `supptrans`  ADD COLUMN `inputdate` datetime NOT NULL AFTER `duedate` ;

ALTER TABLE `debtortrans`  ADD COLUMN `inputdate` datetime NOT NULL AFTER `trandate` ;

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
ALTER TABLE `prices` ADD `startdate` DATE NOT NULL DEFAULT '0000-00-00' , ADD `enddate` DATE NOT NULL DEFAULT '0000-00-00';
ALTER TABLE prices DROP PRIMARY KEY ,
ADD PRIMARY KEY ( `stockid` , `typeabbrev` , `currabrev` , `debtorno` , `branchcode`, `startdate` , `enddate` ) ;
ALTER TABLE purchdata ADD column minorderqty int(11) NOT NULL default 1;
UPDATE prices SET startdate='1999-01-01', enddate='0000-00-00';

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

ALTER TABLE `reportfields` CHANGE `fieldname` `fieldname` VARCHAR( 80) NOT NULL DEFAULT '';

ALTER TABLE `stockcatproperties` ADD `maximumvalue` DOUBLE NOT NULL DEFAULT 999999999 AFTER `defaultvalue` ,
	ADD `minimumvalue` DOUBLE NOT NULL DEFAULT -999999999,
	ADD `numericvalue` TINYINT NOT NULL DEFAULT 0 ;

CREATE TABLE IF NOT EXISTS `fixedassetcategories` (
  `categoryid` char(6) NOT NULL DEFAULT '',
  `categorydescription` char(20) NOT NULL DEFAULT '',
  `costact` int(11) NOT NULL DEFAULT '0',
  `depnact` int(11) NOT NULL DEFAULT '0',
  `disposalact` int(11) NOT NULL DEFAULT '80000',
  `accumdepnact` int(11) NOT NULL DEFAULT '0',
  defaultdepnrate double NOT NULL DEFAULT '.2',
  defaultdepntype int NOT NULL DEFAULT '1',
  PRIMARY KEY (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `fixedassets` (
  `assetid` int(11) NOT NULL AUTO_INCREMENT,
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `barcode` varchar(20) NOT NULL,
  `assetlocation` varchar(6) NOT NULL DEFAULT '',
  `cost` double NOT NULL DEFAULT '0',
  `accumdepn` double NOT NULL DEFAULT '0',
  `datepurchased` date NOT NULL DEFAULT '0000-00-00',
  `disposalproceeds` double NOT NULL DEFAULT '0',
  `assetcategoryid` varchar(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT '',
  `longdescription` text NOT NULL,
  `depntype` int(11) NOT NULL DEFAULT '1',
  `depnrate` double NOT NULL,
  `disposaldate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`assetid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES ('41', 'Asset Addition', '1');
INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES ('42', 'Asset Category Change', '1');
INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES ('43', 'Delete w/down asset', '1');
INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES ('44', 'Depreciation', '1');

CREATE TABLE fixedassettrans(
	id INT( 11 ) NOT NULL AUTO_INCREMENT ,
	assetid INT( 11 ) NOT NULL ,
	transtype TINYINT( 4 ) NOT NULL ,
	transdate DATE NOT NULL,
	transno INT NOT NULL ,
	periodno SMALLINT( 6 ) NOT NULL ,
	inputdate DATE NOT NULL ,
	fixedassettranstype  varchar(8) NOT NULL ,
	amount DOUBLE NOT NULL ,
	PRIMARY KEY ( id ) ,
	INDEX ( assetid, transtype, transno ) ,
	INDEX ( inputdate ),
	INDEX (transdate)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

ALTER TABLE stockcheckfreeze CHANGE stockcheckdate stockcheckdate date NOT NULL DEFAULT '0000-00-00';

ALTER TABLE purchorderdetails ADD COLUMN assetid int NOT NULL DEFAULT 0;

INSERT INTO `systypes` (`typeid` ,`typename` ,`typeno`) VALUES ('49', 'Import Fixed Assets', '1');

DROP TABLE scripts;

CREATE TABLE IF NOT EXISTS `scripts` (
  `script` varchar(78) NOT NULL DEFAULT '',
  `pagesecurity` tinyint(11) NOT NULL DEFAULT '1',
  `description` text NOT NULL,
  PRIMARY KEY (`script`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES
('AccountGroups.php', 10, 'Defines the groupings of general ledger accounts'),
('AccountSections.php', 10, 'Defines the sections in the general ledger reports'),
('AddCustomerContacts.php', 3, 'Adds customer contacts'),
('AddCustomerNotes.php', 3, 'Adds notes about customers'),
('AddCustomerTypeNotes.php', 3, ''),
('AgedDebtors.php', 2, 'Lists customer account balances in detail or summary in selected currency'),
('AgedSuppliers.php', 2, 'Lists supplier account balances in detail or summary in selected currency'),
('Areas.php', 3, 'Defines the sales areas - all customers must belong to a sales area for the purposes of sales analysis'),
('AuditTrail.php', 15, 'Shows the activity with SQL statements and who performed the changes'),
('BankAccounts.php', 10, 'Defines the general ledger code for bank accounts and specifies that bank transactions be created for these accounts for the purposes of reconciliation'),
('BankMatching.php', 7, 'Allows payments and receipts to be matched off against bank statements'),
('BankReconciliation.php', 7, 'Displays the bank reconciliation for a selected bank account'),
('BOMExtendedQty.php', 2, 'Shows the component requirements to make an item'),
('BOMIndented.php', 2, 'Shows the bill of material indented for each level'),
('BOMIndentedReverse.php', 2, ''),
('BOMInquiry.php', 2, 'Displays the bill of material with cost information'),
('BOMListing.php', 2, 'Lists the bills of material for a selected range of items'),
('BOMs.php', 9, 'Administers the bills of material for a selected item'),
('COGSGLPostings.php', 10, 'Defines the general ledger account to be used for cost of sales entries'),
('CompanyPreferences.php', 10, 'Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used etc.'),
('ConfirmDispatchControlled_Invoice.php', 11, 'Specifies the batch references/serial numbers of items dispatched that are being invoiced'),
('ConfirmDispatch_Invoice.php', 2, 'Creates sales invoices from entered sales orders based on the quantities dispatched that can be modified'),
('ContractBOM.php', 6, 'Creates the item requirements from stock for a contract as part of the contract cost build up'),
('ContractCosting.php', 6, 'Shows a contract cost - the components and other non-stock costs issued to the contract'),
('ContractOtherReqts.php', 4, 'Creates the other requirements for a contract cost build up'),
('Contracts.php', 6, 'Creates or modifies a customer contract costing'),
('CounterSales.php', 1, 'Allows sales to be entered against a cash sale customer account defined in the users location record'),
('CreditItemsControlled.php', 3, 'Specifies the batch references/serial numbers of items being credited back into stock'),
('CreditStatus.php', 3, 'Defines the credit status records. Each customer account is given a credit status from this table. Some credit status records can prohibit invoicing and new orders being entered.'),
('Credit_Invoice.php', 3, 'Creates a credit note based on the details of an existing invoice'),
('Currencies.php', 9, 'Defines the currencies available. Each customer and supplier must be defined as transacting in one of the currencies defined here.'),
('CustEDISetup.php', 11, 'Allows the set up the customer specified EDI parameters for server, email or ftp.'),
('CustLoginSetup.php', 15, ''),
('CustomerAllocations.php', 3, 'Allows customer receipts and credit notes to be allocated to sales invoices'),
('CustomerBranches.php', 3, 'Defines the details of customer branches such as delivery address and contact details - also sales area, representative etc'),
('CustomerInquiry.php', 1, 'Shows the customers account transactions with balances outstanding, links available to drill down to invoice/credit note or email invoices/credit notes'),
('CustomerReceipt.php', 3, 'Entry of both customer receipts against accounts receivable and also general ledger or nominal receipts'),
('Customers.php', 3, 'Defines the setup of a customer account, including payment terms, billing address, credit status, currency etc'),
('CustomerTransInquiry.php', 2, 'Lists in html the sequence of customer transactions, invoices, credit notes or receipts by a user entered date range'),
('CustomerTypes.php', 15, ''),
('CustWhereAlloc.php', 2, 'Shows to which invoices a receipt was allocated to'),
('DailyBankTransactions.php', 8, ''),
('DailySalesInquiry.php', 2, 'Shows the daily sales with GP in a calendar format'),
('DebtorsAtPeriodEnd.php', 2, 'Shows the debtors control account as at a previous period end - based on system calendar monthly periods'),
('DeliveryDetails.php', 1, 'Used during order entry to allow the entry of delivery addresses other than the defaulted branch delivery address and information about carrier/shipping method etc'),
('DiscountCategories.php', 11, 'Defines the items belonging to a discount category. Discount Categories are used to allow discounts based on quantities across a range of producs'),
('DiscountMatrix.php', 11, 'Defines the rates of discount applicable to discount categories and the customer groupings to which the rates are to apply'),
('EDIMessageFormat.php', 10, 'Specifies the EDI message format used by a customer - administrator use only.'),
('EDIProcessOrders.php', 11, 'Processes incoming EDI orders into sales orders'),
('EDISendInvoices.php', 15, 'Processes invoiced EDI customer invoices into EDI messages and sends using the customers preferred method either ftp or email attachments.'),
('EmailConfirmation.php', 2, ''),
('EmailCustTrans.php', 2, 'Emails selected invoice or credit to the customer'),
('ExchangeRateTrend.php', 2, 'Shows the trend in exchange rates as retrieved from ECB'),
('Factors.php', 5, 'Defines supplier factor companies'),
('FixedAssetCategories.php', 11, 'Defines the various categories of fixed assets'),
('FixedAssetDepreciation.php', 10, 'Calculates and creates GL transactions to post depreciation for a period'),
('FixedAssetItems.php', 11, 'Allows fixed assets to be defined'),
('FixedAssetList.php', 11, ''),
('FixedAssetLocations.php', 11, 'Allows the locations of fixed assets to be defined'),
('FixedAssetRegister.php', 11, 'Produces a csv, html or pdf report of the fixed assets over a period showing period depreciation, additions and disposals'),
('FixedAssetTransfer.php', 11, 'Allows the fixed asset locations to be changed in bulk'),
('FormDesigner.php', 14, ''),
('FreightCosts.php', 11, 'Defines the setup of the freight cost using different shipping methods to different destinations. The system can use this information to calculate applicable freight if the items are defined with the correct kgs and cubic volume'),
('FTP_RadioBeacon.php', 2, 'FTPs sales orders for dispatch to a radio beacon software enabled warehouse dispatching facility'),
('geocode.php', 3, ''),
('GeocodeSetup.php', 3, ''),
('geocode_genxml_customers.php', 3, ''),
('geocode_genxml_suppliers.php', 3, ''),
('geo_displaymap_customers.php', 3, ''),
('geo_displaymap_suppliers.php', 3, ''),
('GetStockImage.php', 1, ''),
('GLAccountCSV.php', 8, 'Produces a CSV of the GL transactions for a particular range of periods and GL account'),
('GLAccountInquiry.php', 8, 'Shows the general ledger transactions for a specified account over a specified range of periods'),
('GLAccountReport.php', 8, 'Produces a report of the GL transactions for a particular account'),
('GLAccounts.php', 10, 'Defines the general ledger accounts'),
('GLBalanceSheet.php', 8, 'Shows the balance sheet for the company as at a specified date'),
('GLBudgets.php', 10, 'Defines GL Budgets'),
('GLCodesInquiry.php', 8, 'Shows the list of general ledger codes defined with account names and groupings'),
('GLJournal.php', 10, 'Entry of general ledger journals, periods are calculated based on the date entered here'),
('GLProfit_Loss.php', 8, 'Shows the profit and loss of the company for the range of periods entered'),
('GLTagProfit_Loss.php', 8, ''),
('GLTags.php', 10, 'Allows GL tags to be defined'),
('GLTransInquiry.php', 8, 'Shows the general ledger journal created for the sub ledger transaction specified'),
('GLTrialBalance.php', 8, 'Shows the trial balance for the month and the for the period selected together with the budgeted trial balances'),
('GLTrialBalance_csv.php', 8, 'Produces a CSV of the Trial Balance for a particular period'),
('GoodsReceived.php', 11, 'Entry of items received against purchase orders'),
('GoodsReceivedControlled.php', 11, 'Entry of the serial numbers or batch references for controlled items received against purchase orders'),
('index.php', 1, 'The main menu from where all functions available to the user are accessed by clicking on the links'),
('InventoryPlanning.php', 2, 'Creates a pdf report showing the last 4 months use of items including as a component of assemblies together with stock quantity on hand, current demand for the item and current quantity on sales order.'),
('InventoryPlanningPrefSupplier.php', 2, 'Produces a report showing the inventory to be ordered by supplier'),
('InventoryQuantities.php', 2, ''),
('InventoryValuation.php', 2, 'Creates a pdf report showing the value of stock at standard cost for a range of product categories selected'),
('Labels.php', 15, 'Produces item pricing labels in a pdf from a range of selected criteria'),
('Locations.php', 11, 'Defines the inventory stocking locations or warehouses'),
('Logout.php', 1, 'Shows when the user logs out of webERP'),
('MailInventoryValuation.php', 1, 'Meant to be run as a scheduled process to email the stock valuation off to a specified person. Creates the same stock valuation report as InventoryValuation.php'),
('ManualContents.php', 1, ''),
('MenuAccess.php', 15, ''),
('MRP.php', 9, ''),
('MRPCalendar.php', 9, ''),
('MRPCreateDemands.php', 9, ''),
('MRPDemands.php', 9, ''),
('MRPDemandTypes.php', 9, ''),
('MRPPlannedPurchaseOrders.php', 2, ''),
('MRPPlannedWorkOrders.php', 2, ''),
('MRPReport.php', 2, ''),
('MRPReschedules.php', 2, ''),
('MRPShortages.php', 2, ''),
('OffersReceived.php', 4, ''),
('OrderDetails.php', 2, 'Shows the detail of a sales order'),
('OutstandingGRNs.php', 2, 'Creates a pdf showing all GRNs for which there has been no purchase invoice matched off against.'),
('PageSecurity.php', 15, ''),
('PaymentAllocations.php', 5, ''),
('PaymentMethods.php', 15, ''),
('Payments.php', 5, 'Entry of bank account payments either against an AP account or a general ledger payment - if the AP-GL link in company preferences is set'),
('PaymentTerms.php', 10, 'Defines the payment terms records, these can be expressed as either a number of days credit or a day in the following month. All customers and suppliers must have a corresponding payment term recorded against their account'),
('PcAssignCashToTab.php', 6, ''),
('PcAuthorizeExpenses.php', 6, ''),
('PcClaimExpensesFromTab.php', 6, ''),
('PcExpenses.php', 15, ''),
('PcExpensesTypeTab.php', 15, ''),
('PcReportTab.php', 6, ''),
('PcTabs.php', 15, ''),
('PcTypeTabs.php', 15, ''),
('PDFBankingSummary.php', 3, 'Creates a pdf showing the amounts entered as receipts on a specified date together with references for the purposes of banking'),
('PDFChequeListing.php', 3, 'Creates a pdf showing all payments that have been made from a specified bank account over a specified period. This can be emailed to an email account defined in config.php - ie a financial controller'),
('PDFCustomerList.php', 2, 'Creates a report of the customer and branch information held. This report has options to print only customer branches in a specified sales area and sales person. Additional option allows to list only those customers with activity either under or over a specified amount, since a specified date.'),
('PDFCustTransListing.php', 3, ''),
('PDFDeliveryDifferences.php', 3, 'Creates a pdf report listing the delivery differences from what the customer requested as recorded in the order entry. The report calculates a percentage of order fill based on the number of orders filled in full on time'),
('PDFDIFOT.php', 3, 'Produces a pdf showing the delivery in full on time performance'),
('PDFGrn.php', 2, 'Produces a GRN report on the receipt of stock'),
('PDFLowGP.php', 2, 'Creates a pdf report showing the low gross profit sales made in the selected date range. The percentage of gp deemed acceptable can also be entered'),
('PDFOrdersInvoiced.php', 3, 'Produces a pdf of orders invoiced based on selected criteria'),
('PDFOrderStatus.php', 3, 'Reports on sales order status by date range, by stock location and stock category - producing a pdf showing each line items and any quantites delivered'),
('PDFPickingList.php', 2, ''),
('PDFPriceList.php', 2, 'Creates a pdf of the price list applicable to a given sales type and customer. Also allows the listing of prices specific to a customer'),
('PDFPrintLabel.php', 10, ''),
('PDFQuotation.php', 2, ''),
('PDFReceipt.php', 2, ''),
('PDFRemittanceAdvice.php', 2, ''),
('PDFStockCheckComparison.php', 2, 'Creates a pdf comparing the quantites entered as counted at a given range of locations against the quantity stored as on hand as at the time a stock check was initiated.'),
('PDFStockLocTransfer.php', 1, 'Creates a stock location transfer docket for the selected location transfer reference number'),
('PDFStockNegatives.php', 1, 'Produces a pdf of the negative stocks by location'),
('PDFStockTransfer.php', 2, 'Produces a report for stock transfers'),
('PDFStockTransListing.php', 3, ''),
('PDFSuppTransListing.php', 3, ''),
('PDFTopItems.php', 2, 'Produces a pdf report of the top items sold'),
('PeriodsInquiry.php', 2, 'Shows a list of all the system defined periods'),
('POReport.php', 2, ''),
('PO_AuthorisationLevels.php', 15, ''),
('PO_AuthoriseMyOrders.php', 4, ''),
('PO_Header.php', 4, 'Entry of a purchase order header record - date, references buyer etc'),
('PO_Items.php', 4, 'Entry of a purchase order items - allows entry of items with lookup of currency cost from Purchasing Data previously entered also allows entry of nominal items against a general ledger code if the AP is integrated to the GL'),
('PO_OrderDetails.php', 2, 'Purchase order inquiry shows the quantity received and invoiced of purchase order items as well as the header information'),
('PO_PDFPurchOrder.php', 2, 'Creates a pdf of the selected purchase order for printing or email to one of the supplier contacts entered'),
('PO_SelectOSPurchOrder.php', 2, 'Shows the outstanding purchase orders for selecting with links to receive or modify the purchase order header and items'),
('PO_SelectPurchOrder.php', 2, 'Allows selection of any purchase order with links to the inquiry'),
('Prices.php', 9, 'Entry of prices for a selected item also allows selection of sales type and currency for the price'),
('PricesBasedOnMarkUp.php', 11, ''),
('PricesByCost.php', 11, 'Allows prices to be updated based on cost'),
('Prices_Customer.php', 11, 'Entry of prices for a selected item and selected customer/branch. The currency and sales type is defaulted from the customer''s record'),
('PrintCheque.php', 5, ''),
('PrintCustOrder.php', 2, 'Creates a pdf of the dispatch note - by default this is expected to be on two part pre-printed stationery to allow pickers to note discrepancies for the confirmer to update the dispatch at the time of invoicing'),
('PrintCustOrder_generic.php', 2, 'Creates two copies of a laser printed dispatch note - both copies need to be written on by the pickers with any discrepancies to advise customer of any shortfall and on the office copy to ensure the correct quantites are invoiced'),
('PrintCustStatements.php', 2, 'Creates a pdf for the customer statements in the selected range'),
('PrintCustTrans.php', 1, 'Creates either a html invoice or credit note or a pdf. A range of invoices or credit notes can be selected also.'),
('PrintCustTransPortrait.php', 1, ''),
('PrintSalesOrder_generic.php', 2, ''),
('PurchData.php', 4, 'Entry of supplier purchasing data, the suppliers part reference and the suppliers currency cost of the item'),
('RecurringSalesOrders.php', 1, ''),
('ReorderLevel.php', 2, 'Allows reorder levels of inventory to be updated'),
('ReorderLevelLocation.php', 2, ''),
('ReportBug.php', 15, ''),
('ReportletContainer.php', 1, ''),
('ReverseGRN.php', 11, 'Reverses the entry of goods received - creating stock movements back out and necessary general ledger journals to effect the reversal'),
('SalesAnalReptCols.php', 2, 'Entry of the definition of a sales analysis report''s columns.'),
('SalesAnalRepts.php', 2, 'Entry of the definition of a sales analysis report headers'),
('SalesAnalysis_UserDefined.php', 2, 'Creates a pdf of a selected user defined sales analysis report'),
('SalesCategories.php', 11, ''),
('SalesGLPostings.php', 10, 'Defines the general ledger accounts used to post sales to based on product categories and sales areas'),
('SalesGraph.php', 6, ''),
('SalesInquiry.php', 2, ''),
('SalesPeople.php', 3, 'Defines the sales people of the business'),
('SalesTypes.php', 15, 'Defines the sales types - prices are held against sales types they can be considered price lists. Sales analysis records are held by sales type too.'),
('SelectAsset.php', 2, 'Allows a fixed asset to be selected for modification or viewing'),
('SelectCompletedOrder.php', 1, 'Allows the selection of completed sales orders for inquiries - choices to select by item code or customer'),
('SelectContract.php', 6, 'Allows a contract costing to be selected for modification or viewing'),
('SelectCreditItems.php', 3, 'Entry of credit notes from scratch, selecting the items in either quick entry mode or searching for them manually'),
('SelectCustomer.php', 2, 'Selection of customer - from where all customer related maintenance, transactions and inquiries start'),
('SelectGLAccount.php', 8, 'Selection of general ledger account from where all general ledger account maintenance, or inquiries are initiated'),
('SelectOrderItems.php', 1, 'Entry of sales order items with both quick entry and part search functions'),
('SelectProduct.php', 2, 'Selection of items. All item maintenance, transactions and inquiries start with this script'),
('SelectRecurringSalesOrder.php', 2, ''),
('SelectSalesOrder.php', 2, 'Selects a sales order irrespective of completed or not for inquiries'),
('SelectSupplier.php', 2, 'Selects a supplier. A supplier is required to be selected before any AP transactions and before any maintenance or inquiry of the supplier'),
('SelectWorkOrder.php', 2, ''),
('ShipmentCosting.php', 11, 'Shows the costing of a shipment with all the items invoice values and any shipment costs apportioned. Updating the shipment has an option to update standard costs of all items on the shipment and create any general ledger variance journals'),
('Shipments.php', 11, 'Entry of shipments from outstanding purchase orders for a selected supplier - changes in the delivery date will cascade into the different purchase orders on the shipment'),
('Shippers.php', 15, 'Defines the shipping methods available. Each customer branch has a default shipping method associated with it which must match a record from this table'),
('ShiptsList.php', 2, 'Shows a list of all the open shipments for a selected supplier. Linked from POItems.php'),
('Shipt_Select.php', 11, 'Selection of a shipment for displaying and modification or updating'),
('SMTPServer.php', 15, ''),
('SpecialOrder.php', 4, 'Allows for a sales order to be created and an indent order to be created on a supplier for a one off item that may never be purchased again. A dummy part is created based on the description and cost details given.'),
('StockAdjustments.php', 11, 'Entry of quantity corrections to stocks in a selected location.'),
('StockAdjustmentsControlled.php', 11, 'Entry of batch references or serial numbers on controlled stock items being adjusted'),
('StockCategories.php', 11, 'Defines the stock categories. All items must refer to one of these categories. The category record also allows the specification of the general ledger codes where stock items are to be posted - the balance sheet account and the profit and loss effect of any adjustments and the profit and loss effect of any price variances'),
('StockCheck.php', 2, 'Allows creation of a stock check file - copying the current quantites in stock for later comparison to the entered counts. Also produces a pdf for the count sheets.'),
('StockCostUpdate.php', 9, 'Allows update of the standard cost of items producing general ledger journals if the company preferences stock GL interface is active'),
('StockCounts.php', 2, 'Allows entry of stock counts'),
('StockDispatch.php', 2, ''),
('StockLocMovements.php', 2, 'Inquiry shows the Movements of all stock items for a specified location'),
('StockLocStatus.php', 2, 'Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for all items in the selected stock category'),
('StockLocTransfer.php', 11, 'Entry of a bulk stock location transfer for many parts from one location to another.'),
('StockLocTransferReceive.php', 11, 'Effects the transfer and creates the stock movements for a bulk stock location transfer initiated from StockLocTransfer.php'),
('StockMovements.php', 2, 'Shows a list of all the stock movements for a selected item and stock location including the price at which they were sold in local currency and the price at which they were purchased for in local currency'),
('StockQties_csv.php', 5, 'Makes a comma separated values (CSV)file of the stock item codes and quantities'),
('StockQuantityByDate.php', 2, 'Shows the stock on hand for each item at a selected location and stock category as at a specified date'),
('StockReorderLevel.php', 4, 'Entry and review of the re-order level of items by stocking location'),
('Stocks.php', 11, 'Defines an item - maintenance and addition of new parts'),
('StockSerialItemResearch.php', 3, ''),
('StockSerialItems.php', 2, 'Shows a list of the serial numbers or the batch references and quantities of controlled items. This inquiry is linked from the stock status inquiry'),
('StockStatus.php', 2, 'Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for a selected part. Has a link to show the serial numbers in stock at the location selected if the item is controlled'),
('StockTransferControlled.php', 11, 'Entry of serial numbers/batch references for controlled items being received on a stock transfer. The script is used by both bulk transfers and point to point transfers'),
('StockTransfers.php', 11, 'Entry of point to point stock location transfers of a single part'),
('StockUsage.php', 2, 'Inquiry showing the quantity of stock used by period calculated from the sum of the stock movements over that period - by item and stock location. Also available over all locations'),
('StockUsageGraph.php', 2, ''),
('SuppContractChgs.php', 5, ''),
('SuppCreditGRNs.php', 5, 'Entry of a supplier credit notes (debit notes) against existing GRN which have already been matched in full or in part'),
('SuppFixedAssetChgs.php', 5, ''),
('SuppInvGRNs.php', 5, 'Entry of supplier invoices against goods received'),
('SupplierAllocations.php', 5, 'Entry of allocations of supplier payments and credit notes to invoices'),
('SupplierBalsAtPeriodEnd.php', 2, ''),
('SupplierContacts.php', 5, 'Entry of supplier contacts and contact details including email addresses'),
('SupplierCredit.php', 5, 'Entry of supplier credit notes (debit notes)'),
('SupplierInquiry.php', 2, 'Inquiry showing invoices, credit notes and payments made to suppliers together with the amounts outstanding'),
('SupplierInvoice.php', 5, 'Entry of supplier invoices'),
('Suppliers.php', 5, 'Entry of new suppliers and maintenance of existing suppliers'),
('SupplierTenders.php', 9, ''),
('SupplierTransInquiry.php', 2, ''),
('SupplierTypes.php', 4, ''),
('SuppLoginSetup.php', 15, ''),
('SuppPaymentRun.php', 5, 'Automatic creation of payment records based on calculated amounts due from AP invoices entered'),
('SuppPriceList.php', 2, ''),
('SuppShiptChgs.php', 5, 'Entry of supplier invoices against shipments as charges against a shipment'),
('SuppTransGLAnalysis.php', 5, 'Entry of supplier invoices against general ledger codes'),
('SystemCheck.php', 10, ''),
('SystemParameters.php', 15, ''),
('Tax.php', 2, 'Creates a report of the ad-valoerm tax - GST/VAT - for the period selected from accounts payable and accounts receivable data'),
('TaxAuthorities.php', 15, 'Entry of tax authorities - the state intitutions that charge tax'),
('TaxAuthorityRates.php', 11, 'Entry of the rates of tax applicable to the tax authority depending on the item tax level'),
('TaxCategories.php', 15, 'Allows for categories of items to be defined that might have different tax rates applied to them'),
('TaxGroups.php', 15, 'Allows for taxes to be grouped together where multiple taxes might apply on sale or purchase of items'),
('TaxProvinces.php', 15, 'Allows for inventory locations to be defined so that tax applicable from sales in different provinces can be dealt with'),
('TopItems.php', 2, 'Shows the top selling items'),
('UnitsOfMeasure.php', 15, 'Allows for units of measure to be defined'),
('UpgradeDatabase.php', 15, 'Allows for the database to be automatically upgraded based on currently recorded DBUpgradeNumber config option'),
('UserSettings.php', 1, 'Allows the user to change system wide defaults for the theme - appearance, the number of records to show in searches and the language to display messages in'),
('WhereUsedInquiry.php', 2, 'Inquiry showing where an item is used ie all the parents where the item is a component of'),
('WorkCentres.php', 9, 'Defines the various centres of work within a manufacturing company. Also the overhead and labour rates applicable to the work centre and its standard capacity'),
('WorkOrderCosting.php', 11, ''),
('WorkOrderEntry.php', 10, 'Entry of new work orders'),
('WorkOrderIssue.php', 11, 'Issue of materials to a work order'),
('WorkOrderReceive.php', 11, 'Allows for receiving of works orders'),
('WorkOrderStatus.php', 11, 'Shows the status of works orders'),
('WOSerialNos.php', 10, ''),
('WWW_Access.php', 15, ''),
('WWW_Users.php', 15, 'Entry of users and security settings of users'),
('Z_BottomUpCosts.php', 15, ''),
('Z_ChangeBranchCode.php', 15, 'Utility to change the branch code of a customer that cascades the change through all the necessary tables'),
('Z_ChangeCustomerCode.php', 15, 'Utility to change a customer code that cascades the change through all the necessary tables'),
('Z_ChangeStockCategory.php', 15, ''),
('Z_ChangeStockCode.php', 15, 'Utility to change an item code that cascades the change through all the necessary tables'),
('Z_CheckAllocationsFrom.php', 15, ''),
('Z_CheckAllocs.php', 2, ''),
('Z_CheckDebtorsControl.php', 15, 'Inquiry that shows the total local currency (functional currency) balance of all customer accounts to reconcile with the general ledger debtors account'),
('Z_CheckGLTransBalance.php', 15, 'Checks all GL transactions balance and reports problem ones'),
('Z_CopyBOM.php', 9, 'Allows a bill of material to be copied between items'),
('Z_CreateChartDetails.php', 9, 'Utility page to create chart detail records for all general ledger accounts and periods created - needs expert assistance in use'),
('Z_CreateCompany.php', 15, 'Utility to insert company number 1 if not already there - actually only company 1 is used - the system is not multi-company'),
('Z_CreateCompanyTemplateFile.php', 15, ''),
('Z_CurrencyDebtorsBalances.php', 15, 'Inquiry that shows the total foreign currency together with the total local currency (functional currency) balances of all customer accounts to reconcile with the general ledger debtors account'),
('Z_CurrencySuppliersBalances.php', 15, 'Inquiry that shows the total foreign currency amounts and also the local currency (functional currency) balances of all supplier accounts to reconcile with the general ledger creditors account'),
('Z_DataExport.php', 15, ''),
('Z_DeleteCreditNote.php', 15, 'Utility to reverse a customer credit note - a desperate measure that should not be used except in extreme circumstances'),
('Z_DeleteInvoice.php', 15, 'Utility to reverse a customer invoice - a desperate measure that should not be used except in extreme circumstances'),
('Z_DeleteSalesTransActions.php', 15, 'Utility to delete all sales transactions, sales analysis the lot! Extreme care required!!!'),
('Z_DescribeTable.php', 11, ''),
('Z_ImportChartOfAccounts.php', 11, ''),
('Z_ImportFixedAssets.php', 15, 'Allow fixed assets to be imported from a csv'),
('Z_ImportGLAccountGroups.php', 11, ''),
('Z_ImportGLAccountSections.php', 11, ''),
('Z_ImportPartCodes.php', 11, 'Allows inventory items to be imported from a csv'),
('Z_ImportStocks.php', 15, ''),
('Z_index.php', 15, 'Utility menu page'),
('Z_MakeNewCompany.php', 15, ''),
('Z_MakeStockLocns.php', 15, 'Utility to make LocStock records for all items and locations if not already set up.'),
('Z_poAddLanguage.php', 15, 'Allows a new language po file to be created'),
('Z_poAdmin.php', 15, 'Allows for a gettext language po file to be administered'),
('Z_poEditLangHeader.php', 15, ''),
('Z_poEditLangModule.php', 15, ''),
('Z_poEditLangRemaining.php', 15, ''),
('Z_poRebuildDefault.php', 15, ''),
('Z_PriceChanges.php', 15, 'Utility to make bulk pricing alterations to selected sales type price lists or selected customer prices only'),
('Z_ReApplyCostToSA.php', 15, 'Utility to allow the sales analysis table to be updated with the latest cost information - the sales analysis takes the cost at the time the sale was made to reconcile with the enteries made in the gl.'),
('Z_RePostGLFromPeriod.php', 15, 'Utility to repost all general ledger transaction commencing from a specified period. This can take some time in busy environments. Normally GL transactions are posted automatically each time a trial balance or profit and loss account is run'),
('Z_ReverseSuppPaymentRun.php', 15, 'Utility to reverse an entire Supplier payment run'),
('Z_SalesIntegrityCheck.php', 15, ''),
('Z_UpdateChartDetailsBFwd.php', 15, 'Utility to recalculate the ChartDetails table B/Fwd balances - extreme care!!'),
('Z_Upgrade3.10.php', 15, ''),
('Z_Upgrade_3.01-3.02.php', 15, ''),
('Z_Upgrade_3.04-3.05.php', 15, ''),
('Z_Upgrade_3.05-3.06.php', 15, ''),
('Z_Upgrade_3.07-3.08.php', 15, ''),
('Z_Upgrade_3.08-3.09.php', 15, ''),
('Z_Upgrade_3.09-3.10.php', 15, ''),
('Z_Upgrade_3.10-3.11.php', 15, ''),
('Z_Upgrade_3.11-4.00.php', 15, ''),
('Z_UploadForm.php', 15, 'Utility to upload a file to a remote server'),
('Z_UploadResult.php', 15, 'Utility to upload a file to a remote server');

INSERT INTO config (confname, confvalue) VALUES ('VersionNumber', '3.12.0');
UPDATE config SET confvalue='3.12.1' WHERE confname='VersionNumber';

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES
('FormMaker.php', 1, 'Allows running user defined Forms'),
('ReportMaker.php', 1, 'Produces reports from the report writer templates created'),
('ReportCreator.php', 13, 'Report Writer and Form Creator script that creates templates for user defined reports and forms');
UPDATE config SET confvalue='3.12.2' WHERE confname='VersionNumber';
ALTER TABLE `purchorderdetails` CHANGE `nw` `netweight` VARCHAR( 50 )  DEFAULT '';
ALTER TABLE `purchorderdetails` CHANGE `gw` `kgs` VARCHAR( 50 )  DEFAULT '';
ALTER TABLE `purchorderdetails` ADD `conversionfactor` DOUBLE NOT NULL DEFAULT '1';
UPDATE config SET confvalue='3.12.3' WHERE confname='VersionNumber';
ALTER TABLE `purchorderdetails` CHANGE `uom` `suppliersunit` VARCHAR( 50 );
UPDATE config SET confvalue='3.12.31' WHERE confname='VersionNumber';
INSERT INTO config (`confname`, `confvalue`) VALUES ('AutoAuthorisePO', '1');
UPDATE config SET confvalue='4.03' WHERE confname='VersionNumber';
ALTER TABLE `salesorders` ADD `poplaced` TINYINT NOT NULL DEFAULT '0',
ADD INDEX ( `poplaced` );
UPDATE config SET confvalue='4.03.1' WHERE confname='VersionNumber';

CREATE TABLE IF NOT EXISTS `fixedassetlocations` (
  `locationid` char(6) NOT NULL DEFAULT '',
  `locationdescription` char(20) NOT NULL DEFAULT '',
  `parentlocationid` char(6) DEFAULT '',
  PRIMARY KEY (`locationid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `fixedassetlocations` (`locationid`, `locationdescription`, `parentlocationid`) VALUES
('HEADOF', 'Head Office', '');
UPDATE config SET confvalue='4.03.2' WHERE confname='VersionNumber';
ALTER TABLE locations ADD cashsalebranch varchar(10) DEFAULT '';
ALTER TABLE `locations` CHANGE `cashsalecustomer` `cashsalecustomer` VARCHAR( 10 ) DEFAULT '';
UPDATE config SET confvalue='4.03.3' WHERE confname='VersionNumber';
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ChangeSupplierCode.php', '15', 'Script to change a supplier code accross all tables necessary');
UPDATE config SET confvalue='4.03.5' WHERE confname='VersionNumber';
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ( 'ReprintGRN.php', '11', 'Allows selection of a goods received batch for reprinting the goods received note given a purchase order number');
UPDATE config SET confvalue='4.03.6' WHERE confname='VersionNumber';
ALTER TABLE `paymentmethods` ADD `usepreprintedstationery` TINYINT NOT NULL DEFAULT '0';
DELETE FROM scripts WHERE script='PDFStockTransListing.php';
INSERT INTO scripts (`script` ,`pagesecurity` ,`description`) VALUES('PDFPeriodStockTransListing.php','3','Allows stock transactions of a specific transaction type to be listed over a single day or period range');
UPDATE config SET confvalue='4.03.7' WHERE confname='VersionNumber';
ALTER TABLE `purchorderdetails`
  DROP `itemno`,
  DROP `subtotal_amount`,
  DROP `package`,
  DROP `pcunit`,
  DROP `kgs`,
  DROP `cuft`,
  DROP `total_quantity`,
  DROP `netweight`,
  DROP `total_amount`;
  UPDATE purchdata INNER JOIN unitsofmeasure  ON purchdata.suppliersuom=unitsofmeasure.unitid SET suppliersuom = unitsofmeasure.unitname;
UPDATE config SET confvalue='4.03.8' WHERE confname='VersionNumber';
SET FOREIGN_KEY_CHECKS=1;