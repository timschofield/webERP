
/*USE weberp; */
/*May need to uncomment the line above or edit to the name of the db you wish to upgrade*/

ALTER TABLE `debtorsmaster` ADD `address5` VARCHAR( 20 ) NOT NULL AFTER `address4` , ADD `address6` VARCHAR( 15 ) NOT NULL AFTER address5;
ALTER TABLE `custbranch` ADD `braddress5` VARCHAR( 20 ) NOT NULL default '' AFTER `braddress4` , ADD `braddress6` VARCHAR( 15 ) NOT NULL default '' AFTER `braddress5` ;
ALTER TABLE `custbranch` ADD `brpostaddr5` VARCHAR( 20 ) NOT NULL default '' AFTER `brpostaddr4` , ADD `brpostaddr6` VARCHAR( 15 ) NOT NULL default '' AFTER `brpostaddr5` ;
