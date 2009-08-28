--Create `mrpdemandtypes` table
CREATE TABLE `mrpdemandtypes` (
  `mrpdemandtype` varchar(6) NOT NULL default '',
  `description` char(30) NOT NULL default '',
  PRIMARY KEY  (`mrpdemandtype`),
  KEY `mrpdemandtype` (`mrpdemandtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--Create `mrpdemands` table
CREATE TABLE `mrpdemands` (
  `demandid` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL default '',
  `mrpdemandtype` varchar(6) NOT NULL default '',
  `quantity` double NOT NULL default '0',
  `duedate` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`demandid`),
  KEY `StockID` (`stockid`),
  CONSTRAINT `mrpdemands_ibfk_1` FOREIGN KEY (`mrpdemandtype`) REFERENCES `mrpdemandtypes` (`mrpdemandtype`),
  CONSTRAINT `mrpdemands_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--Add `pansize` and `shrinkfactor` columns to stockmaster table
ALTER TABLE `stockmaster` ADD `pansize` double NOT NULL default '0',
  						  ADD `shrinkfactor` double NOT NULL default '0';
  
--Create `mrpcalendar` table
CREATE TABLE `mrpcalendar` (
	calendardate date NOT NULL,
	daynumber int(6) NOT NULL,
	manufacturingflag smallint(6) NOT NULL default '1',
	INDEX (daynumber),
	PRIMARY KEY (calendardate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--Add default value to `mrpdemandtypes` table
INSERT INTO mrpdemandtypes (mrpdemandtype,description) VALUES ('FOR','Forecast');

--Add primary key to `geocode_param` table
ALTER TABLE `geocode_param` add PRIMARY KEY (`geocodeid`);
--Alter definition of `geocodeid` field in `geocode_param` table
ALTER TABLE `geocode_param` CHANGE `geocodeid` `geocodeid` TINYINT( 4 ) NOT NULL AUTO_INCREMENT;

--Create a unique index in table `factorcompanies` for `coyname` field
CREATE UNIQUE INDEX factor_name ON factorcompanies (coyname);
--Insert default value into `factorcompanies` table
INSERT INTO `factorcompanies` ( `id` , `coyname` ) VALUES (null, 'None');

--Alter definition of `role` field in `custcontacts` table
ALTER TABLE `custcontacts` CHANGE `role` `role` VARCHAR( 40 ) NOT NULL;
--Alter definition of `phoneno` field in `custcontacts` table
ALTER TABLE `custcontacts` CHANGE `phoneno` `phoneno` VARCHAR( 20 ) NOT NULL;
--Alter definition of `notes` field in `custcontacts` table
ALTER TABLE `custcontacts` CHANGE `notes` `notes` VARCHAR( 255 ) NOT NULL;

--Set `effectivefrom` field to today in `purchdata` where no value currebtly exists
UPDATE `purchdata` SET `effectivefrom`=NOW() WHERE `effectivefrom`='0000-00-00';
--Drop the primary key in the `purchdata` table
ALTER TABLE `purchdata` DROP PRIMARY KEY;
--Create a new primary key in the `purchdata` table
ALTER TABLE `purchdata` ADD PRIMARY KEY (`supplierno`,`stockid`, `effectivefrom`); 

--Add `quotedate` field to `salesorders` table
ALTER TABLE `salesorders` ADD `quotedate` date NOT NULL default '0000-00-00';
--Add `confirmeddate` field to `salesorders` table
ALTER TABLE `salesorders` ADD `confirmeddate` date NOT NULL default '0000-00-00';

--Create `woserialnos` table
CREATE TABLE `woserialnos` (
	`wo` INT NOT NULL ,
	`stockid` VARCHAR( 20 ) NOT NULL ,
	`serialno` VARCHAR( 30 ) NOT NULL ,
	`quantity` DOUBLE NOT NULL DEFAULT '1',
	`qualitytext` TEXT NOT NULL,
	 PRIMARY KEY (`wo`,`stockid`,`serialno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--Create new config value 'AutoCreateWOs'
INSERT INTO config (confname, confvalue) VALUES ('AutoCreateWOs',1);
--Create new config value 'DefaultFactoryLocation'
INSERT INTO config (confname, confvalue) VALUES ('DefaultFactoryLocation','MEL');
--Create new config value 'FactoryManagerEmail'
INSERT INTO config (confname, confvalue) VALUES ('FactoryManagerEmail','manager@company.com');
--Create new config value 'DefineControlledOnWOEntry'
INSERT INTO config (`confname`,`confvalue`) VALUES ('DefineControlledOnWOEntry', '1');

--Add column`nextserialno` to `stockmaster` table
ALTER TABLE `stockmaster` ADD `nextserialno` BIGINT NOT NULL DEFAULT '0';
--Alter definition of column `orderno` in `salesorders` table
ALTER TABLE `salesorders` CHANGE `orderno` `orderno` INT( 11 ) NOT NULL;
--Add column`qualitytext` to `stockserialitems` table
ALTER TABLE `stockserialitems` ADD `qualitytext` TEXT NOT NULL;

--Create table `purchorderauth`
CREATE TABLE `purchorderauth` (
	`userid` varchar(20) NOT NULL DEFAULT '',
	`currabrev` char(3) NOT NULL DEFAULT '',
	`cancreate` smallint(2) NOT NULL DEFAULT 0,
	`authlevel` int(11) NOT NULL DEFAULT 0,
	PRIMARY KEY (`userid`,`currabrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--Add column`version` to `purchorders` table
ALTER TABLE `purchorders` ADD `version` decimal(3,2) NOT NULL default '1.00';
--Add column`revised` to `purchorders` table
ALTER TABLE `purchorders` ADD `revised` date NOT NULL default '0000-00-00';
--Add column`realorderno` to `purchorders` table
ALTER TABLE `purchorders` ADD `realorderno` varchar(16) NOT NULL default '';
--Add column`deliveryby` to `purchorders` table
ALTER TABLE `purchorders` ADD `deliveryby` varchar(100) NOT NULL default '';
--Add column`deliverydate` to `purchorders` table
ALTER TABLE `purchorders` ADD `deliverydate` date NOT NULL default '0000-00-00';
--Add column`status` to `purchorders` table
ALTER TABLE `purchorders` ADD `status` varchar(12) NOT NULL default '';
--Add column`stat_comment` to `purchorders` table
ALTER TABLE `purchorders` ADD `stat_comment` text NOT NULL;

--Add column`itemno` to `purchorderdetails` table
ALTER TABLE `purchorderdetails` ADD `itemno` varchar(50) NOT NULL default '';
--Add column`uom` to `purchorderdetails` table
ALTER TABLE `purchorderdetails` ADD `uom` varchar(50) NOT NULL default '';
--Add column`subtotal_amount` to `purchorderdetails` table
ALTER TABLE `purchorderdetails` ADD `subtotal_amount` varchar(50) NOT NULL default '';
--Add column`package` to `purchorderdetails` table
ALTER TABLE `purchorderdetails` ADD `package` varchar(100) NOT NULL default '';
--Add column`pcunit` to `purchorderdetails` table
ALTER TABLE `purchorderdetails` ADD `pcunit` varchar(50) NOT NULL default '';
--Add column`nw` to `purchorderdetails` table
ALTER TABLE `purchorderdetails` ADD `nw` varchar(50) NOT NULL default '';
--Add column`suppliers_partno` to `purchorderdetails` table
ALTER TABLE `purchorderdetails` ADD `suppliers_partno` varchar(50) NOT NULL default '';
--Add column`gw` to `purchorderdetails` table
ALTER TABLE `purchorderdetails` ADD `gw` varchar(50) NOT NULL default '';
--Add column`cuft` to `purchorderdetails` table
ALTER TABLE `purchorderdetails` ADD `cuft` varchar(50) NOT NULL default '';
--Add column`total_quantity` to `purchorderdetails` table
ALTER TABLE `purchorderdetails` ADD `total_quantity` varchar(50) NOT NULL default '';
--Add column`total_amount` to `purchorderdetails` table
ALTER TABLE `purchorderdetails` ADD `total_amount` varchar(50) NOT NULL default '';

--Add column`phn` to `suppliers` table
ALTER TABLE `suppliers` ADD `phn` varchar(50) NOT NULL default '';
--Add column`port` to `suppliers` table
ALTER TABLE `suppliers` ADD `port` varchar(200) NOT NULL default '';

--Add column`netweight` to `stockmaster` table
ALTER TABLE `stockmaster` ADD `netweight` decimal(20,4) NOT NULL default '0.0000';

--Add column`suppliers_partno` to `purchdata` table
ALTER TABLE `purchdata` ADD `suppliers_partno` varchar(50) NOT NULL default '';

--Set status of all orders to 'Authorised'
UPDATE `purchorders` SET `status`='Authorised';
--Change status of all orders where allowprint is not set to 'Printed'
UPDATE `purchorders` SET `status`='Printed' WHERE `allowprint`=0;
--Change status of all orders which have been delivered to 'Completed'
UPDATE `purchorders` SET `status`='Completed' WHERE (SELECT SUM(`purchorderdetails`.`completed`)-COUNT(`purchorderdetails`.`podetailitem`) FROM `purchorderdetails` where `purchorderdetails`.`orderno`=`purchorders`.`orderno`)=0;
--Set the delivery date of all orders to the latest line delivery date
UPDATE `purchorders` SET `deliverydate`=(SELECT MAX(`purchorderdetails`.`deliverydate`) FROM `purchorderdetails` WHERE `purchorderdetails`.`orderno`=`purchorders`.`orderno`);

--Alter definition of column `note` in `custnotes` table
ALTER TABLE custnotes CHANGE note note TEXT NOT NULL;

--Add column`bankaccountcode` to `bankaccounts` table
ALTER TABLE `bankaccounts` ADD `bankaccountcode` varchar(50) NOT NULL default '' AFTER `currcode`;
--Add column`invoice` to `bankaccounts` table
ALTER TABLE `bankaccounts` ADD `invoice` smallint(2) NOT NULL default 0 AFTER `currcode`;

--Add column`salesman` to `www_users` table
ALTER TABLE `www_users` ADD `salesman` CHAR( 3 ) NOT NULL AFTER `customerid`;

--Alter definition of column `shipvia` in `debtortrans` table
ALTER TABLE debtortrans CHANGE shipvia shipvia int(11) NOT NULL DEFAULT 0;