INSERT INTO `config` VALUES ('InvoiceQuantityDefault','1');
ALTER TABLE  `www_users` ADD  `dashboard` TINYINT NOT NULL DEFAULT  '0';

INSERT INTO  `scripts` VALUES ('UserLocations.php',  '15',  'Location User Maintenance');

UPDATE config SET confvalue='4.12' WHERE confname='VersionNumber';