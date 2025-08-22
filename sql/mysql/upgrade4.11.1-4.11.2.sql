INSERT INTO scripts VALUES ('Z_UpdateSalesAnalysisWithLatestCustomerData.php','15','Update sales analysis with latest customer and branch salesperson sales area and salestype irrespective of what these settings were when the sale was made');
INSERT INTO scripts VALUES ('PurchaseByPrefSupplier.php','2','Purchase ordering by preferred supplier');
ALTER TABLE `suppliers` ADD COLUMN `url` varchar(50) NOT NULL DEFAULT '';
INSERT INTO config VALUES ('ShopFreightMethod','webERPCalculation');
INSERT INTO config VALUES ('ShopPaypalCommissionAccount', '7220');
INSERT INTO  `scripts` (`script` , `pagesecurity` , `description`) VALUES ('StockClone.php',  '11',  'Script to copy a stock item and associated properties, image, price, purchase and cost data');
ALTER table locstock change bin bin varchar(10) NOT NULL DEFAULT '';
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`)
	VALUES ('BankAccountUsers.php', '15', 'Maintains table bankaccountusers (Authorized users to work with a bank account in webERP)');

CREATE TABLE IF NOT EXISTS `bankaccountusers` (
  `accountcode` varchar(20) NOT NULL COMMENT 'Bank account code',
  `userid` varchar(20) NOT NULL COMMENT 'User code'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
UPDATE config SET confvalue='4.11.2' WHERE confname='VersionNumber';
