INSERT INTO `config` VALUES ('InvoiceQuantityDefault','1');
ALTER TABLE  `www_users` ADD  `dashboard` TINYINT NOT NULL DEFAULT  '0';
UPDATE config SET confvalue='4.12' WHERE confname='VersionNumber';



