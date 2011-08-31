ALTER TABLE stockmaster DROP lastcurcostdate;
ALTER TABLE stockmaster ADD lastcostupdate DATE NOT NULL;
INSERT INTO  `config` (`confname` ,`confvalue`)
VALUES ('InventoryManagerEmail',  'info@yourdomain.com');
UPDATE config SET confvalue='4.05.1' WHERE confname='VersionNumber'; 