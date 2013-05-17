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


