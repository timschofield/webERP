ALTER TABLE stockmaster DROP lastcurcostdate;
ALTER TABLE stockmaster ADD lastcostupdate DATE NOT NULL;
INSERT INTO  `config` (`confname` ,`confvalue`)
VALUES ('InventoryManagerEmail',  '');
ALTER TABLE `banktrans` ADD INDEX ( `ref` );
ALTER TABLE  `pcexpenses` ADD  `tag` TINYINT( 4 ) NOT NULL DEFAULT  '0';
ALTER TABLE `debtortrans` DROP FOREIGN KEY `debtortrans_ibfk_1`;
UPDATE config SET confvalue='4.06.6' WHERE confname='VersionNumber';