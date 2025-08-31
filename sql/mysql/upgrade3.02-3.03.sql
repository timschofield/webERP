/*USE weberp; */
/*May need to uncomment the line above or edit to the name of the db you wish to upgrade*/

ALTER TABLE `debtorsmaster` ADD `address5` VARCHAR( 20 ) NOT NULL AFTER `address4` , ADD `address6` VARCHAR( 15 ) NOT NULL AFTER address5;
ALTER TABLE `custbranch` ADD `braddress5` VARCHAR( 20 ) NOT NULL default '' AFTER `braddress4` , ADD `braddress6` VARCHAR( 15 ) NOT NULL default '' AFTER `braddress5` ;
ALTER TABLE `custbranch` ADD `brpostaddr5` VARCHAR( 20 ) NOT NULL default '' AFTER `brpostaddr4` , ADD `brpostaddr6` VARCHAR( 15 ) NOT NULL default '' AFTER `brpostaddr5` ;

ALTER TABLE `locations` ADD `deladd4` VARCHAR( 40 ) NOT NULL default '' AFTER `deladd3` ,
ADD `deladd5` VARCHAR( 20 ) NOT NULL default '' AFTER `deladd4` ,
ADD `deladd6` VARCHAR( 15 ) NOT NULL default '' AFTER `deladd5` ;

ALTER TABLE `purchorders` ADD `deladd5` VARCHAR( 20 ) NOT NULL default '' AFTER `deladd4` ,
ADD `deladd6` VARCHAR( 15 ) NOT NULL default '' AFTER `deladd5` ;
ALTER TABLE `purchorders` ADD `contact` VARCHAR( 30 ) NOT NULL default '' AFTER `deladd6` ;

ALTER TABLE `recurringsalesorders` ADD `deladd5` VARCHAR( 20 ) NOT NULL default '' AFTER `deladd4` ,
ADD `deladd6` VARCHAR( 15 ) NOT NULL default '' AFTER `deladd5` ;

ALTER TABLE `recurringsalesorders` CHANGE `deladd2` `deladd2` VARCHAR( 40 )  NOT NULL ,
CHANGE `deladd3` `deladd3` VARCHAR( 40 ) NOT NULL ,
CHANGE `deladd4` `deladd4` VARCHAR( 40 ) DEFAULT NULL ;

ALTER TABLE `salesorders` ADD `deladd5` VARCHAR( 20 ) NOT NULL default '' AFTER `deladd4` ,
ADD `deladd6` VARCHAR( 15 ) NOT NULL default '' AFTER `deladd5` ;

ALTER TABLE `salesorders` CHANGE `deladd2` `deladd2` VARCHAR( 40 ) NOT NULL ,
CHANGE `deladd3` `deladd3` VARCHAR( 40 ) NOT NULL ,
CHANGE `deladd4` `deladd4` VARCHAR( 40 ) DEFAULT NULL ;

ALTER TABLE `suppliers` ADD `address5` VARCHAR( 20 ) NOT NULL default '' AFTER `address4` ,
ADD `address6` VARCHAR( 15 ) NOT NULL default '' AFTER `address5` ;

ALTER TABLE `companies` CHANGE `regoffice3` `regoffice4` VARCHAR( 40 ) NOT NULL ;
ALTER TABLE `companies` CHANGE `regoffice2` `regoffice3` VARCHAR( 40 ) NOT NULL ;
ALTER TABLE `companies` CHANGE `regoffice1` `regoffice2` VARCHAR( 40 ) NOT NULL ;
ALTER TABLE `companies` CHANGE `postaladdress` `regoffice1` VARCHAR( 40 ) NOT NULL ;
ALTER TABLE `companies` ADD `regoffice5` VARCHAR( 20 ) NOT NULL default '' AFTER `regoffice4` ,
ADD `regoffice6` VARCHAR( 15 ) NOT NULL default '' AFTER `regoffice5` ;
