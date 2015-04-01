INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustomerAccount.php', '1', 'Shows customer account/statement on screen rather than PDF');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockCategorySalesInquiry.php', '2', 'Sales inquiry by stock category showing top items');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PcAnalysis.php', '15', 'Creates an Excel with details of PC expnese for 24 months');


-- Update version number:
UPDATE config SET confvalue='4.13' WHERE confname='VersionNumber';
