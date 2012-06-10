INSERT INTO securitytokens VALUES(0, 'Main Index Page');
INSERT INTO securitygroups (SELECT secroleid,0 FROM securityroles);

INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ('reportwriter/admin/ReportCreator.php', '15', 'Report Writer');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ('RecurringSalesOrdersProcess.php', '1', 'Process Recurring Sales Orders');

UPDATE `scripts` SET script='CopyBOM.php' WHERE `script`='Z_CopyBOM.php';

ALTER TABLE `stockcategory` ADD `issueglact` int(11) NOT NULL DEFAULT 0 AFTER `adjglact`;

CREATE TABLE departments (
`departmentid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`description` VARCHAR (100) NOT NULL DEFAULT '',
`authoriser` varchar (20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE stockrequest (
`dispatchid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`loccode` VARCHAR (5) NOT NULL DEFAULT '',
`departmentid` INT NOT NULL DEFAULT 0,
`despatchdate` DATE NOT NULL DEFAULT '0000-00-00',
`authorised` TINYINT NOT NULL DEFAULT 0,
`closed` TINYINT NOT NULL DEFAULT 0,
`narrative` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE stockrequestitems (
`dispatchitemsid` INT NOT NULL DEFAULT 0,
`dispatchid` INT NOT NULL DEFAULT 0,
`stockid` VARCHAR (20) NOT NULL DEFAULT '',
`quantity` DOUBLE NOT NULL DEFAULT 0,
`qtydelivered` DOUBLE NOT NULL DEFAULT 0,
`decimalplaces` INT(11) NOT NULL DEFAULT 0,
`uom` VARCHAR(20) NOT NULL DEFAULT '',
`completed` TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ('Departments.php', '1', 'Create business departments');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ('InternalStockRequest.php', '1', 'Create an internal stock request');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ('InternalStockRequestFulfill.php', '1', 'Fulfill an internal stock request');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ('InternalStockRequestAuthorisation.php', '1', 'Authorise internal stock requests');

UPDATE `stockcategory` SET `issueglact`=`adjglact`;
INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (38, 'Stock Requests', 0);

UPDATE `www_users` SET `modulesallowed` = CONCAT(`modulesallowed`,'0,') WHERE modulesallowed LIKE '_,_,_,_,_,_,_,_,_,_,';
INSERT INTO `config` VALUES ('ShowStockidOnImages','0');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ('SupplierPriceList.php', '4', 'Maintain Supplier Price Lists');

CREATE TABLE IF NOT EXISTS `labels` (
  `labelid` tinyint(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  `papersize` varchar(20) NOT NULL,
  `height` tinyint(11) NOT NULL,
  `width` tinyint(11) NOT NULL,
  `topmargin` tinyint(11) NOT NULL,
  `leftmargin` tinyint(11) NOT NULL,
  `rowheight` tinyint(11) NOT NULL,
  `columnwidth` tinyint(11) NOT NULL,
  PRIMARY KEY (`labelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `labelfields` (
  `labelfieldid` int(11) NOT NULL AUTO_INCREMENT,
  `labelid` tinyint(4) NOT NULL,
  `fieldvalue` varchar(20) CHARACTER SET utf8 NOT NULL,
  `vpos` tinyint(4) NOT NULL,
  `hpos` tinyint(4) NOT NULL,
  `fontsize` tinyint(4) NOT NULL,
  `barcode` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`labelfieldid`),
  KEY `labelid` (`labelid`),
  KEY `vpos` (`vpos`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


ALTER TABLE `locations` ADD UNIQUE `locationname` (`locationname`);

ALTER TABLE `stockmaster` CHANGE `lastcostupdate` `lastcostupdate` DATE NOT NULL DEFAULT '0000-00-00';
ALTER TABLE `labels` CHANGE `papersize` `pagewidth` DOUBLE NOT NULL DEFAULT '0';
ALTER TABLE `labels` ADD `pageheight` DOUBLE NOT NULL DEFAULT '0' AFTER `pagewidth`;
ALTER TABLE `labels` CHANGE `height` `height` DOUBLE NOT NULL DEFAULT '0';
ALTER TABLE `labels` CHANGE `width` `width` DOUBLE NOT NULL DEFAULT '0';
ALTER TABLE `labels` CHANGE `topmargin` `topmargin` DOUBLE NOT NULL DEFAULT '0';
ALTER TABLE `labels` CHANGE `leftmargin` `leftmargin` DOUBLE NOT NULL DEFAULT '0';
ALTER TABLE `labels` CHANGE `rowheight` `rowheight` DOUBLE NOT NULL DEFAULT '0';
ALTER TABLE `labels` CHANGE `columnwidth` `columnwidth` DOUBLE NOT NULL DEFAULT '0';


ALTER TABLE `labelfields` CHANGE `vpos` `vpos` DOUBLE NOT NULL DEFAULT '0';
ALTER TABLE `labelfields` CHANGE `hpos` `hpos` DOUBLE NOT NULL DEFAULT '0';

ALTER TABLE paymentmethods ADD opencashdrawer tinyint NOT NULL default '0';
ALTER TABLE bankaccounts DROP foreign key bankaccounts_ibfk_1;
ALTER TABLE banktrans DROP foreign key banktrans_ibfk_2;
ALTER TABLE bankaccounts MODIFY column accountcode varchar(20) NOT NULL default '0';
ALTER TABLE banktrans MODIFY column bankact varchar(20) NOT NULL default '0';
ALTER TABLE banktrans ADD CONSTRAINT banktrans_ibfk_2 FOREIGN KEY(`bankact`) REFERENCES bankaccounts(`accountcode`);
ALTER TABLE chartdetails DROP FOREIGN KEY chartdetails_ibfk_1;
ALTER TABLE chartdetails DROP PRIMARY KEY;
ALTER TABLE chartdetails MODIFY column accountcode varchar(20) NOT NULL default '0';
ALTER TABLE chartdetails ADD PRIMARY KEY (accountcode,period);
ALTER TABLE gltrans DROP FOREIGN KEY gltrans_ibfk_1;
ALTER TABLE gltrans MODIFY column account varchar(20) NOT NULL default '0';
ALTER TABLE pcexpenses DROP FOREIGN KEY pcexpenses_ibfk_1;
ALTER TABLE pcexpenses MODIFY column glaccount varchar(20) NOT NULL DEFAULT '0';
ALTER TABLE pctabs DROP FOREIGN KEY pctabs_ibfk_5;
ALTER TABLE pctabs MODIFY column glaccountassignment varchar(20) NOT NULL DEFAULT '0';
ALTER TABLE taxauthorities DROP FOREIGN KEY taxauthorities_ibfk_1;
ALTER TABLE taxauthorities DROP FOREIGN KEY taxauthorities_ibfk_2;
ALTER TABLE taxauthorities MODIFY column taxglcode varchar(20) NOT NULL DEFAULT '0';
ALTER TABLE taxauthorities MODIFY column purchtaxglaccount varchar(20) NOT NULL DEFAULT '0';
ALTER TABLE chartmaster MODIFY column accountcode varchar(20) NOT NULL DEFAULT '0';
ALTER TABLE bankaccounts ADD FOREIGN KEY(accountcode) REFERENCES chartmaster(accountcode);
ALTER TABLE chartdetails ADD CONSTRAINT chartdetails_ibfk_1 FOREIGN KEY(accountcode) REFERENCES chartmaster(accountcode);
ALTER TABLE gltrans ADD CONSTRAINT gltrans_ibfk_1 FOREIGN KEY (account) REFERENCES chartmaster(accountcode);
ALTER TABLE pcexpenses ADD CONSTRAINT pcexpenses_ibfk_1 FOREIGN KEY(glaccount) REFERENCES chartmaster(accountcode);
ALTER TABLE pctabs ADD CONSTRAINT pctabs_ibfk_5 FOREIGN KEY(glaccountassignment) REFERENCES chartmaster(accountcode);
ALTER TABLE pctabs MODIFY column glaccountpcash varchar(20) NOT NULL DEFAULT '0';
ALTER TABLE taxauthorities ADD CONSTRAINT taxauthorities_ibfk_1 FOREIGN KEY (taxglcode) REFERENCES chartmaster(accountcode);
ALTER TABLE taxauthorities ADD CONSTRAINT taxauthorities_ibfk_2 FOREIGN KEY (purchtaxglaccount) REFERENCES chartmaster(accountcode);

INSERT INTO  scripts (script, pagesecurity, description) VALUES ('NoSalesItems.php',  '2',  'Shows the No Selling (worst) items');

UPDATE config SET confvalue='4.08' WHERE confname='VersionNumber';

