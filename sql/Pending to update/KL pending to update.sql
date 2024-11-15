/* ALREADY RUN IN DB*/
ALTER TABLE `supplierdiscounts` CONVERT TO CHARACTER SET utf8;
INSERT INTO `scripts` VALUES ('PDFGLJournalCN.php', 1, 'Print GL Journal Chinese version');
CREATE table favourites (userid varchar(20) NOT NULL DEFAULT '',
	caption varchar(50) NOT NULL DEFAULT '',
	href varchar(200) NOT NULL DEFAULT '#',
	PRIMARY KEY (userid,caption)) Engine=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `banktrans` ADD `chequeno` VARCHAR(16) NOT NULL DEFAULT '' AFTER `currcode`;
ALTER TABLE `custbranch` CHANGE `lat` `lat` FLOAT(12,8) NOT NULL DEFAULT '0.00000000';
ALTER TABLE `custbranch` CHANGE `lng` `lng` FLOAT(12,8) NOT NULL DEFAULT '0.00000000';
ALTER TABLE `supptrans` ADD `chequeno` VARCHAR(16) NOT NULL DEFAULT '' AFTER `hold`;
ALTER TABLE `supptrans` ADD `void` TINYINT(1) NOT NULL DEFAULT 0 AFTER `chequeno`;
ALTER table `supptrans` DROP KEY `TypeTransNo`;
ALTER table `supptrans` ADD KEY `TypeTransNo`(`transno`,`type`);
ALTER TABLE `pcexpenses` ADD COLUMN `taxcatid` TINYINT(4) NOT NULL DEFAULT 1 AFTER `tag`;
ALTER TABLE `pctabs` ADD COLUMN `defaulttag` TINYINT(4) NOT NULL DEFAULT 0 AFTER `glaccountpcash`;
ALTER TABLE `pctabs` ADD COLUMN `taxgroupid` TINYINT(4) NOT NULL DEFAULT 1 AFTER `defaulttag`;
ALTER TABLE `pctabs` ADD COLUMN `authorizerexpenses` VARCHAR(20) NOT NULL AFTER `authorizer`;
ALTER TABLE `pcashdetails` ADD COLUMN `tag` INT(11) NOT NULL DEFAULT 0 AFTER `tabcode`;
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PcAuthorizeCash.php', '6', 'Authorisation of assigned cash');
INSERT IGNORE INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_RemovePurchaseBackOrders.php', '1', 'Removes all purchase order back orders');
CREATE TABLE `pcashdetailtaxes` (
	`counterindex` INT(20) NOT NULL AUTO_INCREMENT,
	`pccashdetail` INT(20) NOT NULL DEFAULT 0,
	`calculationorder` TINYINT(4) NOT NULL DEFAULT 0,
	`description` VARCHAR(40) NOT NULL DEFAULT '',
	`taxauthid` TINYINT(4) NOT NULL DEFAULT '0',
	`purchtaxglaccount` VARCHAR(20) NOT NULL DEFAULT '',
	`taxontax` TINYINT(4) NOT NULL DEFAULT 0,
	`taxrate` DOUBLE NOT NULL DEFAULT 0.0,
	`amount` DOUBLE NOT NULL DEFAULT 0.0,
	PRIMARY KEY(counterindex)
) Engine=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE pcashdetails MODIFY receipt text COMMENT 'Column redundant. Replaced by receipt file upload. Nov 2017.';
ALTER TABLE `stockserialitems` ADD `createdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP, ADD INDEX (`createdate`);
UPDATE stockserialitems SET createdate = NULL;
UPDATE stockserialitems as stockserialitems SET createdate =
(SELECT trandate FROM (select trandate, stockserialitems.serialno, stockserialitems.stockid FROM stockserialitems
LEFT JOIN stockserialmoves ON stockserialitems.serialno=stockserialmoves.serialno
LEFT JOIN stockmoves ON stockserialmoves.stockmoveno=stockmoves.stkmoveno
GROUP BY stockserialitems.stockid, stockserialitems.serialno
ORDER BY trandate) AS ssi
WHERE ssi.serialno=stockserialitems.serialno
AND ssi.stockid=stockserialitems.stockid);
ALTER TABLE `salesorders` ADD `internalcomment` BLOB NULL DEFAULT NULL;
INSERT INTO `config` (`confname`, `confvalue`) VALUES ('MaxSerialItemsIssued','50');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('BankAccountBalances.php', '1', 'Shows bank accounts authorised for with balances');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GeneratePickingList.php', '11', 'Generate a picking list');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLAccountGraph.php', '8', 'Shows a graph of GL account transactions');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFAck.php', '15', 'Print an acknowledgement');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFShipLabel.php', '15', 'Print a ship label');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PickingListsControlled.php', '11', 'Picking List Maintenance - Controlled');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PickingLists.php', '11', 'Picking List Maintenance');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectPickingLists.php', '11', 'Select a picking list');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ChangeSalesmanCode.php', '15', 'Utility to change a salesman code');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_Fix1cAllocations.php', '9', '');
CREATE TABLE IF NOT EXISTS pickreq (
	`prid` int not null auto_increment,
	`initiator` varchar(20) not null default '',
	`shippedby` varchar(20) not null default '',
	`initdate` date not null default '0000-00-00',
	`requestdate` date not null default '0000-00-00',
	`shipdate` date not null default '0000-00-00',
	`status` varchar(12) not null default '',
	`comments` text default null,
	`closed` tinyint not null default '0',
	`loccode` varchar(5) not null default '',
	`orderno` int not null default '1',
	`consignment` varchar(15) NOT NULL DEFAULT '',
	`packages` int(11) NOT NULL DEFAULT '1' COMMENT 'number of cartons',
	PRIMARY KEY (`prid`),
	key (`orderno`),
	key (`requestdate`),
	key (`shipdate`),
	key (`status`),
	key (`closed`),
	key (`loccode`),
	CONSTRAINT FOREIGN KEY(`loccode`) REFERENCES `locations`(`loccode`),
	constraint foreign key (`orderno`) REFERENCES salesorders(`orderno`)
) Engine=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pickreqdetails (
	`detailno` int not null auto_increment,
	`prid` int not null default '1',
	`orderlineno` int not null default '0',
	`stockid` varchar(20) not null default '',
	`qtyexpected` double not null default '0',
	`qtypicked` double not null default '0',
	`invoicedqty` double not null default '0',
	`shipqty` double not null default '0',
	PRIMARY KEY (`detailno`),
	key (`prid`),
	key (`stockid`),
	constraint foreign key (`stockid`) REFERENCES stockmaster(`stockid`),
	constraint foreign key (`prid`) REFERENCES pickreq(`prid`)
) Engine=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pickserialdetails (
	`serialmoveid` int not null auto_increment,
	`detailno` int not null default '1',
	`stockid` varchar(20) not null default '',
	`serialno` varchar(30) not null default '',
	`moveqty` double not null default '0',
	PRIMARY KEY (`serialmoveid`),
	key (`detailno`),
	key (`stockid`,`serialno`),
	key (`serialno`),
	CONSTRAINT FOREIGN KEY (`detailno`) REFERENCES pickreqdetails (`detailno`),
	CONSTRAINT FOREIGN KEY (`stockid`,`serialno`) REFERENCES `stockserialitems`(`stockid`,`serialno`)
) Engine=InnoDB DEFAULT CHARSET=utf8;

-- TABLE pickinglists (pickinglistno, orderno, pickinglistdate, dateprinted, deliverynotedate)
INSERT INTO pickreq (prid, initdate, requestdate, shipdate, orderno, closed, loccode)
	SELECT pickinglists.pickinglistno, dateprinted, pickinglistdate, deliverynotedate, pickinglists.orderno, IF(qtyexpected = qtypicked, 1, 0), fromstkloc
		FROM pickinglists
			JOIN pickinglistdetails ON pickinglists.pickinglistno = pickinglistdetails.pickinglistno
			JOIN salesorders ON pickinglists.orderno = salesorders.orderno;

INSERT INTO pickreqdetails (prid, orderlineno, stockid, qtyexpected, qtypicked, invoicedqty, shipqty)
	SELECT pickinglistdetails.pickinglistno, pickinglistdetails.orderlineno, stkcode, qtyexpected, qtypicked, qtypicked, qtypicked
		FROM pickinglistdetails
			JOIN pickinglists ON pickinglistdetails.pickinglistno = pickinglists.pickinglistno
			JOIN salesorderdetails ON salesorderdetails.orderno = pickinglists.orderno;

CREATE TABLE `pcreceipts` (
	`counterindex` INT(20) NOT NULL AUTO_INCREMENT,
	`pccashdetail` INT(20) NOT NULL DEFAULT 0 COMMENT 'Expenses record identity',
	`hashfile` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'MD5 hash of uploaded receipt file',
	`type` varchar(80) NOT NULL DEFAULT '' COMMENT 'Mime type of uploaded receipt file',
	`extension` varchar(4) NOT NULL DEFAULT '' COMMENT 'File extension of uploaded receipt',
	`size` int(20) NOT NULL DEFAULT 0 COMMENT 'File size of uploaded receipt',
	PRIMARY KEY (`counterindex`),
	CONSTRAINT `pcreceipts_ibfk_1` FOREIGN KEY (`pccashdetail`) REFERENCES `pcashdetails` (`counterindex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE pcashdetails ADD COLUMN purpose text NULL AFTER posted;

ALTER TABLE `suppliers` ADD COLUMN `defaultgl` VARCHAR(20) NOT NULL DEFAULT '1' AFTER `url`;
ALTER TABLE `suppliers` ADD COLUMN `defaultshipper` INT(11) NOT NULL DEFAULT '0' AFTER `url`;

-- Inserts new scripts:
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLStatements.php', '8', 'Shows a set of financial statements');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesReport.php', '2', 'Shows a report of sales to customers for the range of selected dates');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_FixGLTransPeriods.php', '15', 'Fixes periods where GL transactions were not created correctly');

-- Updates scripts description:
UPDATE `scripts` SET `description` = 'Add a New Language to the System' WHERE `scripts`.`script` = 'Z_poAddLanguage.php';
UPDATE `scripts` SET `description` = 'Adds or removes security roles by a system administrator' WHERE `scripts`.`script` = 'WWW_Access.php';
UPDATE `scripts` SET `description` = 'Changes the security token of a script' WHERE `scripts`.`script` = 'PageSecurity.php';
UPDATE `scripts` SET `description` = 'Creates a report of the ad-valorem tax -GST/VAT- for the period selected from accounts payable and accounts receivable data' WHERE `scripts`.`script` = 'Tax.php';
UPDATE `scripts` SET `description` = 'Customizes the form layout without requiring the use of scripting or technical development' WHERE `scripts`.`script` = 'FormDesigner.php';
UPDATE `scripts` SET `description` = 'Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used, etc.' WHERE `scripts`.`script` = 'CompanyPreferences.php';
UPDATE `scripts` SET `description` = 'Edit a Language File Header' WHERE `scripts`.`script` = 'Z_poEditLangHeader.php';
UPDATE `scripts` SET `description` = 'Edit a Language File Module' WHERE `scripts`.`script` = 'Z_poEditLangModule.php';
UPDATE `scripts` SET `description` = 'Edit Remaining Strings For This Language' WHERE `scripts`.`script` = 'Z_poEditLangRemaining.php';
UPDATE `scripts` SET `description` = 'Rebuild the System Default Language File' WHERE `scripts`.`script` = 'Z_poRebuildDefault.php';
UPDATE `scripts` SET `description` = 'Sets the configuration for geocoding of customers and suppliers' WHERE `scripts`.`script` = 'GeocodeSetup.php';
UPDATE `scripts` SET `description` = 'Sets the main system configuration parameters' WHERE `scripts`.`script` = 'SystemParameters.php';
UPDATE `scripts` SET `description` = 'Sets the SMTP server' WHERE `scripts`.`script` = 'SMTPServer.php';

INSERT INTO config VALUES ('ShortcutMenu','0');
INSERT INTO config VALUES ('LastDayofWeek','0');

CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `surname` varchar(20) NOT NULL,
  `firstname` varchar(20) NOT NULL,
  `stockid` varchar(20) NOT NULL COMMENT 'FK with stockmaster',
  `manager` int(11) COMMENT 'an employee also in this table',
  `normalhours` double NOT NULL DEFAULT '40',
  `userid` varchar(20) NOT NULL DEFAULT '' COMMENT 'loose FK with www-users will have some employees who are not users',
  `email` varchar(55) NOT NULL DEFAULT '',
  KEY `surname` (`surname`),
  KEY `firstname` (`firstname`),
  KEY `stockid` (`stockid`),
  KEY `manager` (`manager`),
  KEY `userid` (`userid`),
  CONSTRAINT `stk_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Employees.php', '20', 'Employees requiring time-sheets maintenance and entry ');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Timesheets.php', '1', 'Entry of Timesheets');
INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES ('20', 'Timesheet administrator');
INSERT INTO `securitygroups` (`secroleid`, `tokenid`) VALUES ('8', '20');
INSERT INTO `securitygroups` (`secroleid`, `tokenid`) VALUES ('9', '20');

CREATE TABLE `timesheets` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `wo` int(11) NOT NULL COMMENT 'loose FK with workorders',
  `employeeid` INT NOT NULL,
  `weekending` DATE NOT NULL DEFAULT '1900-01-01',
  `workcentre` varchar(5) NOT NULL COMMENT 'loose FK with workcentres',
  `day1` double NOT NULL default 0,
  `day2` double NOT NULL default 0,
  `day3` double NOT NULL default 0,
  `day4` double NOT NULL default 0,
  `day5` double NOT NULL default 0,
  `day6` double NOT NULL default 0,
  `day7` double NOT NULL default 0,
  `status` tinyint(4) NOT NULL default 0 COMMENT '0=entered 1=submitted 2=approved',
  KEY `workcentre` (`workcentre`),
  KEY `employees` (`employeeid`),
  KEY `wo` (`wo`),
  KEY `weekending` (`weekending`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`employeeid`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jnltmplheader` (
  `templateid` INT(11) NOT NULL DEFAULT 0,
  `templatedescription` VARCHAR(50) NOT NULL DEFAULT '',
  `journaltype` INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`templateid`)
);

CREATE TABLE `jnltmpldetails` (
  `linenumber` INT(11) NOT NULL DEFAULT 0,
  `templateid` INT(11) NOT NULL DEFAULT 0,
  `tags` VARCHAR(50) NOT NULL DEFAULT '0',
  `accountcode` VARCHAR(20) NOT NULL DEFAULT '1',
  `amount` DOUBLE NOT NULL DEFAULT 0,
  `narrative` VARCHAR(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`templateid`, `linenumber`)
);
INSERT INTO  `systypes` (`typeid` ,`typename` ,`typeno`) VALUES ('4',  'Journal Template Number',  '0');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('GLJournalTemplates.php', '15', 'Maintain Journal templates');

INSERT INTO `config` (`confname`, `confvalue`) VALUES ('StockUsageShowZeroWithinPeriodRange', '0');
ALTER TABLE `www_users` ADD COLUMN `fontsize` TINYINT NOT NULL DEFAULT '1' AFTER `pdflanguage`;






/* PENDING TO RUN IN DB*/


UPDATE `pctabs` SET authorizerexpenses=authorizer;

-- standardise transaction date to DATE type:
ALTER TABLE `debtortrans` CHANGE `trandate` `trandate` DATE NOT NULL DEFAULT '0000-00-00';
ALTER TABLE `salesanalysis` CHANGE `salesperson` `salesperson` VARCHAR(4) DEFAULT '' NOT NULL;

UPDATE config SET confvalue='4.13.1' WHERE confname='VersionNumber';

-- Convert prices to use non- SQL mode specific end date we will have a year 10000 problem but its a way off!:
UPDATE prices SET enddate='9999-12-31' WHERE enddate='0000-00-00';

UPDATE config SET confvalue='4.14' WHERE confname='VersionNumber';

UPDATE config SET confvalue='4.14.1' WHERE confname='VersionNumber';

UPDATE config SET confvalue='4.15' WHERE confname='VersionNumber';

ALTER TABLE `stockmaster` DROP COLUMN `appendfile`;

-- change date defaults to acceptable default - could also use CURRENT_TIMESTAMP ??
ALTER TABLE `assetmanager` CHANGE `datepurchased` `datepurchased` DATE NOT NULL DEFAULT '1000-01-01';
ALTER TABLE audittrail CHANGE `transactiondate` `transactiondate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE banktrans CHANGE `transdate` `transdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE bom CHANGE `effectiveafter` `effectiveafter`  date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE contracts CHANGE `requireddate` `requireddate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE custallocns CHANGE `datealloc` `datealloc` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE custnotes CHANGE `date` `date` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE debtorsmaster CHANGE `clientsince` `clientsince` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE debtortrans CHANGE `trandate` `trandate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE debtortypenotes CHANGE `date` `date` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE deliverynotes   CHANGE `deliverydate` `deliverydate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE fixedassets CHANGE `datepurchased` `datepurchased` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE fixedassets CHANGE `disposaldate` `disposaldate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE gltrans CHANGE `trandate` `trandate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE grns CHANGE `deliverydate` `deliverydate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE loctransfers CHANGE `shipdate` `shipdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE loctransfers CHANGE `recdate` `recdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE mrpdemands CHANGE `duedate` `duedate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE offers CHANGE `expirydate` `expirydate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE periods CHANGE `lastdate_in_period` `lastdate_in_period` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickinglists CHANGE `pickinglistdate` `pickinglistdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickinglists CHANGE `dateprinted` `dateprinted` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickinglists CHANGE `deliverynotedate` `deliverynotedate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickreq CHANGE `initdate` `initdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickreq CHANGE `requestdate` `requestdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickreq CHANGE `shipdate` `shipdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pricematrix CHANGE `startdate` `startdate`  date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE prices CHANGE `startdate` `startdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE purchorderdetails CHANGE `deliverydate` `deliverydate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE purchorders CHANGE `orddate` `orddate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE purchorders CHANGE `revised` `revised` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE purchorders CHANGE `deliverydate` `deliverydate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE qasamples CHANGE `sampledate` `sampledate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE recurringsalesorders CHANGE `orddate` `orddate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE recurringsalesorders CHANGE `lastrecurrence` `lastrecurrence` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE recurringsalesorders CHANGE `stopdate` `stopdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorderdetails CHANGE `actualdispatchdate` `actualdispatchdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE salesorders CHANGE `orddate` `orddate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders CHANGE `deliverydate` `deliverydate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders CHANGE `confirmeddate` `confirmeddate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders CHANGE `datepackingslipprinted` `datepackingslipprinted` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders CHANGE `quotedate` `quotedate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE sampleresults CHANGE `testdate` `testdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE shipments CHANGE `eta` `eta` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE stockcheckfreeze CHANGE `stockcheckdate` `stockcheckdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockmaster CHANGE `lastcostupdate` `lastcostupdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockmoves CHANGE `trandate` `trandate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockrequest CHANGE `despatchdate` `despatchdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockserialitems CHANGE `expirationdate` `expirationdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE suppallocs CHANGE `datealloc` `datealloc` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE suppliers CHANGE `suppliersince` `suppliersince` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE supptrans CHANGE `trandate` `trandate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE supptrans CHANGE `duedate` `duedate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE tenders CHANGE `requiredbydate` `requiredbydate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE workorders CHANGE `requiredby` `requiredby` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE workorders CHANGE `startdate` `startdate` date NOT NULL DEFAULT '1000-01-01';


-- THIS IS THE LAST SQL QUERY. Updates database version number:
UPDATE config SET confvalue='4.15.1' WHERE confname='VersionNumber';

UPDATE config SET confvalue='4.15.2' WHERE confname='VersionNumber';

