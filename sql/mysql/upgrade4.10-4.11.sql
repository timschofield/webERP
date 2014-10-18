INSERT INTO config VALUES('SmtpSetting',0);
ALTER TABLE  `companies` CHANGE  `debtorsact`  `debtorsact` VARCHAR( 20 ) NOT NULL DEFAULT  '70000',
CHANGE  `pytdiscountact`  `pytdiscountact` VARCHAR( 20 ) NOT NULL DEFAULT  '55000',
CHANGE  `creditorsact`  `creditorsact` VARCHAR( 20 ) NOT NULL DEFAULT  '80000',
CHANGE  `payrollact`  `payrollact` VARCHAR( 20 ) NOT NULL DEFAULT  '84000',
CHANGE  `grnact`  `grnact` VARCHAR( 20 ) NOT NULL DEFAULT  '72000',
CHANGE  `exchangediffact`  `exchangediffact` VARCHAR( 20 ) NOT NULL DEFAULT  '65000',
CHANGE  `purchasesexchangediffact`  `purchasesexchangediffact` VARCHAR( 20 ) NOT NULL DEFAULT  '0',
CHANGE  `retainedearnings`  `retainedearnings` VARCHAR( 20 ) NOT NULL DEFAULT  '90000',
CHANGE  `freightact`  `freightact` VARCHAR( 20 ) NOT NULL DEFAULT  '0';

ALTER TABLE  `lastcostrollup` CHANGE  `stockact`  `stockact` VARCHAR( 20 ) NOT NULL DEFAULT  '0',
CHANGE  `adjglact`  `adjglact` VARCHAR( 20 ) NOT NULL DEFAULT  '0';

ALTER TABLE  `salesglpostings` CHANGE  `discountglcode`  `discountglcode` VARCHAR( 20 ) NOT NULL DEFAULT  '0',
CHANGE  `salesglcode`  `salesglcode` VARCHAR( 20 ) NOT NULL DEFAULT  '0';

ALTER TABLE  `cogsglpostings` CHANGE  `glcode`  `glcode` VARCHAR( 20 ) NOT NULL DEFAULT  '0';

ALTER TABLE  `fixedassetcategories` CHANGE  `costact`  `costact` VARCHAR( 20 ) NOT NULL DEFAULT  '0',
CHANGE  `depnact`  `depnact` VARCHAR( 20 ) NOT NULL DEFAULT  '0',
CHANGE  `disposalact`  `disposalact` VARCHAR( 20 ) NOT NULL DEFAULT  '80000',
CHANGE  `accumdepnact`  `accumdepnact` VARCHAR( 20 ) NOT NULL DEFAULT  '0';

ALTER TABLE  `purchorderdetails` CHANGE  `glcode`  `glcode` VARCHAR( 20 ) NOT NULL DEFAULT  '0';

ALTER TABLE  `stockcategory` CHANGE  `stockact`  `stockact` VARCHAR( 20 ) NOT NULL DEFAULT  '0',
CHANGE  `adjglact`  `adjglact` VARCHAR( 20 ) NOT NULL DEFAULT  '0',
CHANGE  `issueglact`  `issueglact` VARCHAR( 20 ) NOT NULL DEFAULT  '0',
CHANGE  `purchpricevaract`  `purchpricevaract` VARCHAR( 20 ) NOT NULL DEFAULT  '80000',
CHANGE  `materialuseagevarac`  `materialuseagevarac` VARCHAR( 20 ) NOT NULL DEFAULT  '80000',
CHANGE  `wipact`  `wipact` VARCHAR( 20 ) NOT NULL DEFAULT  '0';

ALTER TABLE  `workcentres` CHANGE  `overheadrecoveryact`  `overheadrecoveryact` VARCHAR( 20 ) NOT NULL DEFAULT  '0';

INSERT INTO  `scripts` (`script` , `pagesecurity` , `description`)
VALUES ('Z_ChangeGLAccountCode.php',  '15',  'Script to change a GL account code accross all tables necessary');

ALTER TABLE  `currencies` ADD  `webcart` TINYINT( 1 ) NOT NULL DEFAULT  '1' COMMENT  'If 1 shown in weberp cart. if 0 no show';

ALTER TABLE  `salescat` CHANGE  `salescatname`  `salescatname` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
CREATE TABLE `mailgroups` ( id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	                      groupname varchar(100) NOT NULL,
			      unique (groupname)) ENGINE = InnoDB DEFAULT CHARSET = utf8;
CREATE TABLE `mailgroupdetails` (groupname varchar(100) NOT NULL,
	                   userid varchar(20) NOT NULL,
			   CONSTRAINT FOREIGN KEY (`groupname`) REFERENCES `mailgroups` (`groupname`),
			   CONSTRAINT FOREIGN KEY (`userid`) REFERENCES `www_users`(`userid`),
			   INDEX(`groupname`)) Engine=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO scripts VALUES('MailingGroupMaintenance.php', 15, 'Mainting mailing lists for items to mail');

INSERT INTO mailgroups VALUES(1,'ChkListingRecipients');
INSERT INTO mailgroups VALUES(2,'SalesAnalysisReportRecipients');
INSERT INTO scripts VALUES('MailSalesReport_csv.php',15,'Mailing the sales report');
INSERT INTO mailgroups VALUES(3,'OffersReceivedResultRecipients');
INSERT INTO mailgroups VALUES(4,'InventoryValuationRecipients');
ALTER TABLE stockrequestitems DROP PRIMARY KEY;
ALTER TABLE stockrequestitems ADD PRIMARY KEY (`dispatchitemsid`,`dispatchid`);
INSERT INTO scripts VALUES('Z_ImportGLTransactions.php', 15, 'Import General Ledger Transactions');
CREATE TABLE IF NOT EXISTS `fixedassettasks` (
  `taskid` int(11) NOT NULL AUTO_INCREMENT,
  `assetid` int(11) NOT NULL,
  `taskdescription` text NOT NULL,
  `frequencydays` int(11) NOT NULL DEFAULT '365',
  `lastcompleted` date NOT NULL,
  `userresponsible` varchar(20) NOT NULL,
  `manager` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`taskid`),
  KEY `assetid` (`assetid`),
  KEY `userresponsible` (`userresponsible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MaintenanceTasks.php', '1', 'Allows set up and edit of scheduled maintenance tasks');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MaintenanceUserSchedule.php', '1', 'List users or managers scheduled maintenance tasks and allow to be flagged as completed');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MaintenanceReminders.php', '1', 'Sends email reminders for scheduled asset maintenance tasks');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ImportBankTransAnalysis.php', '11', 'Allows analysis of bank transactions being imported');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ImportBankTrans.php', '11', 'Imports bank transactions');
INSERT INTO scripts VALUES ('Manufacturers.php','15','Maintain brands of sales products');
INSERT INTO scripts VALUES ('SalesCategoryDescriptions.php','15','Maintain translations for sales categories');
INSERT INTO scripts VALUES ('ShopParameters.php','15','Maintain web-store configuration and set up');

INSERT INTO config VALUES ('ShopName','');
INSERT INTO config VALUES ('ShopPrivacyStatement','');
INSERT INTO config VALUES ('ShopFreightPolicy','');
INSERT INTO config VALUES ('ShopTermsConditions','');
INSERT INTO config VALUES ('ShopDebtorNo','');
INSERT INTO config VALUES ('ShopBranchCode','');
INSERT INTO config VALUES ('ShopAboutUs','');

INSERT INTO config VALUES ('ShopPayPalUser','');
INSERT INTO config VALUES ('ShopPayPalPassword','');
INSERT INTO config VALUES ('ShopPayPalSignature','');

INSERT INTO config VALUES ('ShopPayPalProUser','');
INSERT INTO config VALUES ('ShopPayPalProPassword','');
INSERT INTO config VALUES ('ShopPayPalProSignature','');

INSERT INTO config VALUES ('ShopCreditCardGateway', 'PayFlowPro');

INSERT INTO config VALUES ('ShopPayFlowUser','');
INSERT INTO config VALUES ('ShopPayFlowPassword','');
INSERT INTO config VALUES ('ShopPayFlowVendor','');

INSERT INTO config VALUES ('ShopAllowPayPal', '1');
INSERT INTO config VALUES ('ShopAllowCreditCards', '1');
INSERT INTO config VALUES ('ShopAllowBankTransfer', '1');
INSERT INTO config VALUES ('ShopAllowSurcharges', '1');

INSERT INTO config VALUES ('ShopPayPalSurcharge', '0.034');
INSERT INTO config VALUES ('ShopBankTransferSurcharge', '0.0');
INSERT INTO config VALUES ('ShopCreditCardSurcharge', '0.029');

INSERT INTO config VALUES ('ShopPayPalBankAccount', '1030');
INSERT INTO config VALUES ('ShopCreditCardBankAccount', '1030');

INSERT INTO config VALUES ('ShopSwipeHQMerchantID', '');
INSERT INTO config VALUES ('ShopSwipeHQAPIKey', '');

INSERT INTO config VALUES ('ShopSurchargeStockID', '');

INSERT INTO config VALUES ('ItemDescriptionLanguages','');
INSERT INTO config VALUES('SmtpSetting',0);

CREATE TABLE IF NOT EXISTS `stockdescriptiontranslations` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `descriptiontranslation` varchar(50) NOT NULL,
  PRIMARY KEY (`stockid`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `debtorsmaster` ADD `language_id` VARCHAR( 10 ) NOT NULL DEFAULT 'en_GB.utf8';

ALTER TABLE `debtortrans` ADD `salesperson` VARCHAR( 4 ) NOT NULL DEFAULT '' , ADD INDEX ( `salesperson` );
UPDATE debtortrans INNER JOIN salesorders ON debtortrans.order_=salesorders.orderno SET debtortrans.salesperson=salesorders.salesperson;
UPDATE debtortrans INNER JOIN custbranch ON debtortrans.debtorno=custbranch.debtorno AND debtortrans.branchcode=custbranch.branchcode SET debtortrans.salesperson=custbranch.salesman WHERE debtortrans.type=11;

CREATE TABLE IF NOT EXISTS `manufacturers` (
  `manufacturers_id` int(11) NOT NULL AUTO_INCREMENT,
  `manufacturers_name` varchar(32) NOT NULL,
  `manufacturers_url` varchar(50) NOT NULL DEFAULT '',
  `manufacturers_image` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`manufacturers_id`),
  KEY (`manufacturers_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `salescattranslations` (
  `salescatid` tinyint(4) NOT NULL DEFAULT '0',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `salescattranslation` varchar(40) NOT NULL,
  PRIMARY KEY (`salescatid`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE salescatprod ADD COLUMN manufacturers_id int(11) NOT NULL;
ALTER TABLE salescatprod ADD COLUMN featured int(11) DEFAULT '0' NOT NULL;
ALTER TABLE `salescatprod` ADD INDEX ( `manufacturers_id` );


INSERT INTO config VALUES ('ShopMode','test');
INSERT INTO config VALUES ('ShopContactUs','');

ALTER TABLE  `purchorderauth` CHANGE  `authlevel`  `authlevel` DOUBLE NOT NULL DEFAULT  '0';

INSERT INTO config VALUES ('ShopShowOnlyAvailableItems','0');
INSERT INTO config VALUES ('ShopShowQOHColumn','1');
INSERT INTO config VALUES ('ShopStockLocations','');


ALTER TABLE  `freightcosts` ADD  `destinationcountry` VARCHAR( 40 ) NOT NULL AFTER  `locationfrom`;

INSERT INTO config VALUES ('ShopTitle','Shop Home');

ALTER TABLE  `stockmaster` CHANGE  `kgs`  `grossweight` DECIMAL( 20, 4 ) NOT NULL DEFAULT  '0.0000';

ALTER TABLE  `custbranch` CHANGE  `brpostaddr3`  `brpostaddr3` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '',
						  CHANGE  `brpostaddr4`  `brpostaddr4` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '',
 						  CHANGE  `brpostaddr6`  `brpostaddr6` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';

ALTER TABLE `stockcategory` ADD `defaulttaxcatid` TINYINT NOT NULL DEFAULT '1';
ALTER TABLE  `salescat` ADD  `active` INT NOT NULL DEFAULT  '1' COMMENT  '1 if active 0 if inactive';

INSERT INTO config VALUES ('ShopManagerEmail','');

UPDATE config SET confvalue='4.11.0' WHERE confname='VersionNumber';

