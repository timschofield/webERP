ALTER TABLE stockmaster DROP lastcurcostdate;
ALTER TABLE stockmaster ADD lastcostupdate DATE NOT NULL;
INSERT INTO  `config` (`confname` ,`confvalue`)
VALUES ('InventoryManagerEmail',  '');
ALTER TABLE `banktrans` ADD INDEX ( `ref` );
UPDATE config SET confvalue='4.05.2' WHERE confname='VersionNumber'; 