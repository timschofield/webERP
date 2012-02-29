INSERT INTO securitytokens VALUES(0, 'Main Index Page');
INSERT INTO securitygroups (SELECT secroleid,0 FROM securityroles);

INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ('reportwriter/admin/ReportCreator.php', '15', 'Report Writer');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ('RecurringSalesOrdersProcess.php', '1', 'Process Recurring Sales Orders');

DELETE FROM `scripts` WHERE `script`='Z_CopyBOM.php';

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

UPDATE `www_users` SET `modulesallowed` = CONCAT(`modulesallowed`,'0,');
INSERT INTO `config` VALUES ('ShowStockidOnImages','0');
UPDATE config SET confvalue='4.08' WHERE confname='VersionNumber';
