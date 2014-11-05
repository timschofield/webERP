INSERT INTO `config` VALUES ('InvoiceQuantityDefault','1');

ALTER TABLE `stockcategory` ADD INDEX `CategoryDescription` (`categorydescription`);

UPDATE config SET confvalue='4.12' WHERE confname='VersionNumber';



