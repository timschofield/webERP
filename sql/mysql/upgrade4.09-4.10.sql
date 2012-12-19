INSERT INTO config VALUES('ExchangeRateFeed','ECB');
ALTER TABLE `salesorders` ADD `salesperson` VARCHAR( 4 ) NOT NULL , ADD INDEX ( `salesperson` );
ALTER TABLE `salesman` CHANGE `salesmancode` `salesmancode` VARCHAR( 4 ) NOT NULL DEFAULT '';
ALTER TABLE `salesorderdetails` DROP `commissionrate`;
ALTER TABLE `salesorderdetails` DROP `commissionearned`;
INSERT INTO scripts VALUES ('CounterReturns.php','5','Allows credits and refunds from the default Counter Sale account for an inventory location');
ALTER TABLE purchorders MODIFY `initiator` VARCHAR(20);
INSERT INTO `weberpdemo`.`scripts` (`script` , `pagesecurity` , `description`)
VALUES ('OrderEntryDiscountPricing', '13', 'Not a script but an authority level marker - required if the user is allowed to enter discounts against a customer order'
);
ALTER TABLE `gltrans` ADD INDEX ( `tag` );
INSERT INTO scripts VALUES ('CustomerPurchases.php','5','Shows the purchases a customer has made.');
UPDATE config SET confvalue='4.10.0' WHERE confname='VersionNumber';


