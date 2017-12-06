ALTER TABLE `banktrans` ADD `chequeno` VARCHAR(16) NOT NULL DEFAULT '' AFTER `currcode`;
ALTER TABLE `supptrans` ADD `chequeno` VARCHAR(16) NOT NULL DEFAULT '' AFTER `hold`;
ALTER TABLE `supptrans` ADD `void` TINYINT(1) NOT NULL DEFAULT 0 AFTER `chequeno`;
ALTER table `supptrans` DROP KEY `TypeTransNo`;
ALTER table `supptrans` ADD KEY `TypeTransNo`(`transno`,`type`);

ALTER TABLE `pcexpenses` ADD COLUMN `taxcatid` TINYINT(4) NOT NULL DEFAULT 1 AFTER `tag`;
ALTER TABLE `pctabs` ADD COLUMN `defaulttag` TINYINT(4) NOT NULL DEFAULT 0 AFTER `glaccountpcash`;
ALTER TABLE `pctabs` ADD COLUMN `taxgroupid` TINYINT(4) NOT NULL DEFAULT 1 AFTER `defaulttag`;
ALTER TABLE `pctabs` ADD COLUMN `authorizerexpenses` VARCHAR(20) NOT NULL AFTER `authorizer`;
UPDATE `pctabs` SET authorizerexpenses=authorizer;
ALTER TABLE `pcashdetails` ADD COLUMN `tag` INT(11) NOT NULL DEFAULT 0 AFTER `tabcode`;
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PcAuthorizeCash.php', '6', 'Authorisation of assigned cash');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_RemovePurchaseBackOrders.php', '1', 'Removes all purchase order back orders');
CREATE TABLE `pcashdetailtaxes` (
	`counterindex` INT(20) NOT NULL AUTO_INCREMENT,
	`pccashdetail` INT(20) NOT NULL DEFAULT 0,
	`calculationorder` TINYINT(4) NOT NULL DEFAULT 0,
	`description` VARCHAR(40) NOT NULL DEFAULT '',
	`taxauthid` TINYINT(4) NOT NULL DEFAULT '0',
	`purchtaxglaccount` VARCHAR(20) NOT NULL DEFAULT '',
	`taxontax` TINYINT(4) NOT NULL DEFAULT 0,
	`taxrate` DOUBLE NOT NULL DEFAULT 0.0,
	`amount` DOUBLE NOT NULL DEFAULT 0.0,
	PRIMARY KEY(counterindex)
);

ALTER TABLE `custbranch` CHANGE `lat` `lat` FLOAT(12,8) NOT NULL DEFAULT '0.00000000';
ALTER TABLE `custbranch` CHANGE `lng` `lng` FLOAT(12,8) NOT NULL DEFAULT '0.00000000';

ALTER TABLE pcashdetails MODIFY receipt text COMMENT 'Column redundant. Replaced by receipt file upload. Nov 2017.';
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ('BankAccountBalances.php',  '1',  'Shows bank accounts authorised for with balances');


UPDATE config SET confvalue='4.15' WHERE confname='VersionNumber';

