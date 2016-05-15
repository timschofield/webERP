CREATE TABLE IF NOT EXISTS `loctransfercancellations` ( 
			`reference` INT(11) NOT NULL , 
			`stockid` VARCHAR(20) NOT NULL , 
			`cancelqty` DOUBLE NOT NULL , 
			`canceldate` DATETIME NOT NULL , 
			`canceluserid` VARCHAR(20) NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `loctransfercancellations` ADD INDEX `Index1` (`reference`, `stockid`) COMMENT '';
ALTER TABLE `loctransfercancellations` ADD INDEX `Index2` (`canceldate`, `reference`, `stockid`) COMMENT '';

-- Add new scripts:
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES
	('AnalysisHorizontalIncome.php', '8', 'Shows the horizontal analysis of the statement of comprehensive income'),
	('AnalysisHorizontalPosition.php', '8', 'Shows the horizontal analysis of the statement of financial position'),
	('EmailCustStatements.php','3','Email customer statement to customer'),
	('GLAccountUsers.php', '15', 'Maintenance of users allowed to a GL Account'),
	('SupplierGRNAndInvoiceInquiry.php',5,'Supplier\'s delivery note and grn relationship inquiry'),
	('UserBankAccounts.php', '15', 'Maintains table bankaccountusers (Authorized users to work with a bank account in webERP)'),
	('UserGLAccounts.php', '15', 'Maintenance of GL Accounts allowed for a user');

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
INSERT INTO scripts VALUES('CollectiveWorkOrderCost.php',2,'Multiple work orders cost review');
ALTER table bom ADD remark varchar(500) NOT NULL DEFAULT '';
INSERT INTO scripts VALUES ('SuppWhereAlloc.php',3,'Suppliers Where allocated');
ALTER table pctabs DROP FOREIGN KEY `pctabs_ibfk_4`;
ALTER table pctabs CHANGE authorizer authorizer varchar(100);
ALTER table pctabs CHANGE assigner assigner varchar(100);
INSERT INTO securitytokens VALUES(18,'Cost authority');
ALTER table BOM ADD digitals int(11) NOT NULL DEFAULT 0;
INSERT INTO scripts VALUES('StockIntransitStatus.php',1,'Inventory transaction status');
INSERT INTO scripts VALUES ('StockTransferControlledDispatched.php',11,'Inventory controlled input');
ALTER table loctransfers ADD closed tinyint(1) not null default '0';
CREATE table trfserialno (trfno int(11) NOT NULL AUTO_INCREMENT,
			trfref int(11) NOT NUll DEFAULT '0',
			stkcode varchar(20) NOT NULL DEFAULT '0',
			serialno varchar(20) NOT NULL DEFAULT '0',
			trfqty double NOT NULL DEFAULT '0',
			loccode varchar(5) NOT NULL DEFAULT '',
			recqty double NOT NULL DEFAULT '0',
			PRIMARY KEY (`trfno`),
			KEY (`trfref`),
			KEY (`stkcode`,`serialno`),
			KEY (`serialno`),
			KEY (`stkcode`),
			KEY (`stkcode`,`serialno`,`loccode`),
			CONSTRAINT FOREIGN KEY (`trfref`) REFERENCES `loctransfers`(`reference`),
			CONSTRAINT FOREIGN KEY (`stkcode`,`serialno`,`loccode`) REFERENCES `stockserialitems`(`stockid`,`serialno`,`loccode`)) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
			

-- Update version number:
UPDATE config SET confvalue='4.13' WHERE confname='VersionNumber';
