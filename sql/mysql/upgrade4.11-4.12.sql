INSERT INTO `config` VALUES ('InvoiceQuantityDefault','1');
ALTER TABLE  `www_users` ADD  `dashboard` TINYINT NOT NULL DEFAULT  '0';
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('Z_UpdateItemCosts.php', '15', 'Use CSV of item codes and costs to update webERP item costs');
INSERT INTO scripts (`script` ,`pagesecurity` ,`description` ) VALUES ('CustomerBalancesMovement.php', '3', 'Allow customers to be listed in local currency with balances and activity over a date range');
INSERT INTO  `scripts` VALUES ('UserLocations.php',  '15',  'Location User Maintenance');
ALTER TABLE  `stockmoves` ADD  `userid` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER  `trandate`;

UPDATE config SET confvalue='4.12' WHERE confname='VersionNumber';
