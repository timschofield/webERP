CREATE TRIGGER `prices_update_timestamp` BEFORE UPDATE ON `prices`
FOR EACH ROW
SET NEW.date_updated = NOW();

CREATE TRIGGER `relateditems_update_timestamp` BEFORE UPDATE ON `relateditems`
FOR EACH ROW
SET NEW.date_updated = NOW();

CREATE TRIGGER `salariescalculated_update_timestamp` BEFORE UPDATE ON `salariescalculated`
FOR EACH ROW
SET NEW.date_updated = NOW();

CREATE TRIGGER `salescat_update_timestamp` BEFORE UPDATE ON `salescat`
FOR EACH ROW
SET NEW.date_updated = NOW();

CREATE TRIGGER `salescatprod_update_timestamp` BEFORE UPDATE ON `salescatprod`
FOR EACH ROW
SET NEW.date_updated = NOW();

CREATE TRIGGER `stockdescriptiontranslations_update_timestamp` BEFORE UPDATE ON `stockdescriptiontranslations`
FOR EACH ROW
SET NEW.date_updated = NOW();

CREATE TRIGGER `stockmaster_update_timestamp` BEFORE UPDATE ON `stockmaster`
FOR EACH ROW
SET NEW.date_updated = NOW();

CREATE TRIGGER `currencies_update_timestamp` BEFORE UPDATE ON `currencies`
FOR EACH ROW
SET NEW.date_updated = NOW();

CREATE TRIGGER `klretailcustomers_update_timestamp` BEFORE UPDATE ON `klretailcustomers`
FOR EACH ROW
SET NEW.date_updated = NOW();

CREATE TRIGGER `klstockmarketplaces_update_timestamp` BEFORE UPDATE ON `klstockmarketplaces`
FOR EACH ROW
SET NEW.date_updated = NOW();

ALTER TABLE `accountsection` CHANGE `sectionname` `sectionname` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `config` CHANGE `confvalue` `confvalue` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `fixedassets` CHANGE `longdescription` `longdescription` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `fixedassets` CHANGE `depnrate` `depnrate` DOUBLE NOT NULL DEFAULT '0';
ALTER TABLE `fixedassets` CHANGE `barcode` `barcode` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `fixedassettasks` CHANGE `taskdescription` `taskdescription` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `fixedassettasks` CHANGE `lastcompleted` `lastcompleted` DATE NOT NULL DEFAULT '1000-01-01';
ALTER TABLE `fixedassettasks` CHANGE `userresponsible` `userresponsible` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `freightcosts` CHANGE `destinationcountry` `destinationcountry` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `klchangeprice` CHANGE `startprocessdate` `startprocessdate` DATE NOT NULL DEFAULT '1000-01-01';
ALTER TABLE `klchangeprice` CHANGE `newretailprice` `newretailprice` DECIMAL(20,4) NOT NULL DEFAULT '0';
ALTER TABLE `klchangeprice` CHANGE `endprocessdate` `endprocessdate` DATE NOT NULL DEFAULT '1000-01-01';
ALTER TABLE `salesman` CHANGE `current` `current` TINYINT(4) NOT NULL DEFAULT '1' COMMENT 'Salesman current (1) or not (0)';
ALTER TABLE `woitems` CHANGE `stdcost` `stdcost` DOUBLE NOT NULL DEFAULT '0';


ALTER TABLE `klretailcustomers` CHANGE `date_updated` `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `salariescalculated` CHANGE `date_updated` `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
