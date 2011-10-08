ALTER TABLE stockmaster DROP lastcurcostdate;
ALTER TABLE stockmaster ADD lastcostupdate DATE NOT NULL;
INSERT INTO  `config` (`confname` ,`confvalue`)
VALUES ('InventoryManagerEmail',  '');
ALTER TABLE `banktrans` ADD INDEX ( `ref` );
ALTER TABLE  `pcexpenses` ADD  `tag` TINYINT( 4 ) NOT NULL DEFAULT  '0';
UPDATE config SET confvalue='4.05.3' WHERE confname='VersionNumber'; 