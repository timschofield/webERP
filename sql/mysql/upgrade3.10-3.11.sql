CREATE TABLE IF NOT EXISTS `mrpdemandtypes` (
  `mrpdemandtype` varchar(6) NOT NULL default '',
  `description` char(30) NOT NULL default '',
  PRIMARY KEY  (`mrpdemandtype`),
  KEY `mrpdemandtype` (`mrpdemandtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mrpdemands` (
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

ALTER TABLE `stockmaster` ADD `pansize` double NOT NULL default '0',
  						  ADD `shrinkfactor` double NOT NULL default '0';
  
CREATE TABLE IF NOT EXISTS mrpcalendar (
	calendardate date NOT NULL,
	daynumber int(6) NOT NULL,
	manufacturingflag smallint(6) NOT NULL default "1",
	INDEX (daynumber),
	PRIMARY KEY (calendardate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO mrpdemandtypes (mrpdemandtype,description) VALUES ('FOR','Forecast');

ALTER TABLE `geocode_param` add PRIMARY KEY (`geocodeid`);
ALTER TABLE `geocode_param` CHANGE `geocodeid` `geocodeid` TINYINT( 4 ) NOT NULL AUTO_INCREMENT;

INSERT INTO `factorcompanies` ( `id` , `coyname` ) VALUES (null, "None");

ALTER TABLE `custcontacts` CHANGE `role` `role` VARCHAR( 40 ) NOT NULL;
ALTER TABLE `custcontacts` CHANGE `phoneno` `phoneno` VARCHAR( 20 ) NOT NULL;
ALTER TABLE `custcontacts` CHANGE `notes` `notes` VARCHAR( 255 ) NOT NULL;

ALTER TABLE `purchdata` DROP PRIMARY KEY;
ALTER TABLE `purchdata` ADD PRIMARY KEY (`supplierno`,`stockid`, `effectivefrom`); 

ALTER TABLE `salesorders` ADD `quotedate` date NOT NULL default '0000-00-00';
ALTER TABLE `salesorders` ADD `confirmeddate` date NOT NULL default '0000-00-00';

CREATE TABLE IF NOT EXISTS `woserialnos` (
	`wo` INT NOT NULL ,
	`stockid` VARCHAR( 20 ) NOT NULL ,
	`serialno` VARCHAR( 30 ) NOT NULL ,
	`quantity` DOUBLE NOT NULL DEFAULT '1',
	`qualitytext` TEXT NOT NULL,
	 PRIMARY KEY (`wo`,`stockid`,`serialno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO config (confname, confvalue) VALUES ('AutoCreateWOs',1);
INSERT INTO config (confname, confvalue) VALUES ('DefaultFactoryLocation','MEL');
INSERT INTO config (confname, confvalue) VALUES ('FactoryManagerEmail','manager@company.com');
INSERT INTO config (`confname`,`confvalue`) VALUES ('DefineControlledOnWOEntry', '1');

ALTER TABLE `stockmaster` ADD `nextserialno` BIGINT NOT NULL DEFAULT '0';
ALTER TABLE `salesorders` CHANGE `orderno` `orderno` INT( 11 ) NOT NULL;
ALTER TABLE `stockserialitems` ADD `qualitytext` TEXT NOT NULL;

CREATE TABLE IF NOT EXISTS `purchorderauth` (
	`userid` varchar(20) NOT NULL DEFAULT '',
	`currabrev` char(3) NOT NULL DEFAULT '',
	`cancreate` smallint(2) NOT NULL DEFAULT 0,
	`authlevel` int(11) NOT NULL DEFAULT 0,
	PRIMARY KEY (`userid`,`currabrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `purchorders` ADD `version` decimal(3,2) NOT NULL default '1.00';
ALTER TABLE `purchorders` ADD `revised` date NOT NULL default '0000-00-00';
ALTER TABLE `purchorders` ADD `realorderno` varchar(16) NOT NULL default '';
ALTER TABLE `purchorders` ADD `deliveryby` varchar(100) NOT NULL default '';
ALTER TABLE `purchorders` ADD `deliverydate` date NOT NULL default '0000-00-00';
ALTER TABLE `purchorders` ADD `status` varchar(12) NOT NULL default '';
ALTER TABLE `purchorders` ADD `stat_comment` text NOT NULL;

ALTER TABLE `purchorderdetails` ADD `itemno` varchar(50) NOT NULL default '';
ALTER TABLE `purchorderdetails` ADD `uom` varchar(50) NOT NULL default '';
ALTER TABLE `purchorderdetails` ADD `subtotal_amount` varchar(50) NOT NULL default '';
ALTER TABLE `purchorderdetails` ADD `package` varchar(100) NOT NULL default '';
ALTER TABLE `purchorderdetails` ADD `pcunit` varchar(50) NOT NULL default '';
ALTER TABLE `purchorderdetails` ADD `nw` varchar(50) NOT NULL default '';
ALTER TABLE `purchorderdetails` ADD `suppliers_partno` varchar(50) NOT NULL default '';
ALTER TABLE `purchorderdetails` ADD `gw` varchar(50) NOT NULL default '';
ALTER TABLE `purchorderdetails` ADD `cuft` varchar(50) NOT NULL default '';
ALTER TABLE `purchorderdetails` ADD `total_quantity` varchar(50) NOT NULL default '';
ALTER TABLE `purchorderdetails` ADD `total_amount` varchar(50) NOT NULL default '';

ALTER TABLE `suppliers` ADD `phn` varchar(50) NOT NULL default '';
ALTER TABLE `suppliers` ADD `port` varchar(200) NOT NULL default '';

ALTER TABLE `stockmaster` ADD `netweight` decimal(20,4) NOT NULL default '0.0000';

ALTER TABLE `purchdata` ADD `suppliers_partno` varchar(50) NOT NULL default '';

UPDATE `purchorders` SET `status`='Authorised';
UPDATE `purchorders` SET `status`='Printed' WHERE `allowprint`=0;
UPDATE `purchorders` SET `status`='Completed' WHERE (SELECT SUM(`purchorderdetails`.`completed`)-COUNT(`purchorderdetails`.`podetailitem`) FROM `purchorderdetails` where `purchorderdetails`.`orderno`=`purchorders`.`orderno`)=0;
UPDATE `purchorders` SET `deliverydate`=(SELECT MAX(`purchorderdetails`.`deliverydate`) FROM `purchorderdetails` WHERE `purchorderdetails`.`orderno`=`purchorders`.`orderno`);

ALTER TABLE custnotes CHANGE note note TEXT NOT NULL;

ALTER TABLE `bankaccounts` ADD `bankaccountcode` varchar(50) NOT NULL default '' AFTER `currcode`;
ALTER TABLE `bankaccounts` ADD `invoice` smallint(2) NOT NULL default 0 AFTER `currcode`;

ALTER TABLE `www_users` ADD `salesman` CHAR( 3 ) NOT NULL AFTER `customerid`;