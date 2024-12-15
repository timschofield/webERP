CREATE TABLE IF NOT EXISTS `loctransfercancellations` (
			`reference` INT(11) NOT NULL ,
			`stockid` VARCHAR(20) NOT NULL ,
			`cancelqty` DOUBLE NOT NULL ,
			`canceldate` DATETIME NOT NULL ,
			`canceluserid` VARCHAR(20) NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `loctransfercancellations` ADD INDEX `Index1` (`reference`, `stockid`) COMMENT '';
ALTER TABLE `loctransfercancellations` ADD INDEX `Index2` (`canceldate`, `reference`, `stockid`) COMMENT '';

-- Add new scripts:
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AnalysisHorizontalIncome.php', '8', 'Shows the horizontal analysis of the statement of comprehensive income');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AnalysisHorizontalPosition.php', '8', 'Shows the horizontal analysis of the statement of financial position');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CollectiveWorkOrderCost.php', 2, 'Multiple work orders cost review');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('EmailCustStatements.php','3','Email customer statement to customer');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLAccountUsers.php', '15', 'Maintenance of users allowed to a GL Account');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SupplierGRNAndInvoiceInquiry.php',5,'Supplier\'s delivery note and grn relationship inquiry');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SuppWhereAlloc.php', 3, 'Suppliers where allocated');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('UserBankAccounts.php', '15', 'Maintains table bankaccountusers (Authorized users to work with a bank account in webERP)');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('UserGLAccounts.php', '15', 'Maintenance of GL Accounts allowed for a user');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_GLAccountUsersCopyAuthority.php', '15', 'Utility to copy authority of GL accounts from one user to another');

CREATE TABLE IF NOT EXISTS `suppinvstogrn` (
	  `suppinv` int(11) NOT NULL,
	  `grnno` int(11) NOT NULL,
  PRIMARY KEY (`suppinv`,`grnno`),
  KEY `suppinvstogrn_ibfk_1` (`grnno`),
  CONSTRAINT `suppinvstogrn_ibfk_1` FOREIGN KEY (`grnno`) REFERENCES `grns` (`grnno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER table grns ADD supplierref varchar(30) NOT NULL DEFAULT '';

CREATE TABLE IF NOT EXISTS `glaccountusers` (
  `accountcode` varchar(20) NOT NULL COMMENT 'GL account code from chartmaster',
  `userid` varchar(20) NOT NULL,
  `canview` tinyint(4) NOT NULL DEFAULT '0',
  `canupd` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `glaccountusers` ADD UNIQUE `useraccount` (`userid`, `accountcode`);
ALTER TABLE `glaccountusers` ADD UNIQUE `accountuser` (`accountcode`, `userid`);

/* Populate the table by default */
INSERT INTO glaccountusers (userid, accountcode, canview, canupd)
		SELECT www_users.userid, chartmaster.accountcode,1,1
		FROM www_users CROSS JOIN chartmaster
		LEFT JOIN glaccountusers
		ON www_users.userid = glaccountusers.userid
		AND chartmaster.accountcode = glaccountusers.accountcode
        WHERE glaccountusers.userid IS NULL;

ALTER table stockrequest DROP FOREIGN KEY `stockrequest_ibfk_3`;
ALTER table stockrequest DROP FOREIGN KEY `stockrequest_ibfk_4`;
ALTER table bom ADD remark varchar(500) NOT NULL DEFAULT '';
ALTER table pctabs DROP FOREIGN KEY `pctabs_ibfk_4`;
ALTER table pctabs CHANGE authorizer authorizer varchar(100);
ALTER table pctabs CHANGE assigner assigner varchar(100);
INSERT INTO securitytokens VALUES(18,'Cost authority');
ALTER table bom ADD digitals int(11) NOT NULL DEFAULT 0;
ALTER table custcontacts ADD statement tinyint(4) NOT NULL DEFAULT 0;

-- Update version number:
UPDATE config SET confvalue='4.13' WHERE confname='VersionNumber';
