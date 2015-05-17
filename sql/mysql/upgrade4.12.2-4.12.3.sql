INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustomerAccount.php', '1', 'Shows customer account/statement on screen rather than PDF');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockCategorySalesInquiry.php', '2', 'Sales inquiry by stock category showing top items');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PcAnalysis.php', '15', 'Creates an Excel with details of PC expnese for 24 months');

-- Add field to store location's GL account code:
ALTER TABLE `locations` ADD `glaccountcode` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'GL account of the location';
-- Add field to allow or deny the invoicing of items in this location:
ALTER TABLE `locations` ADD `allowinvoicing` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Allow invoicing of items at this location';
---- QUESTION: Existing locations are (always) set to 1? ***********************

-- Update version number:
UPDATE config SET confvalue='4.12.3' WHERE confname='VersionNumber';
