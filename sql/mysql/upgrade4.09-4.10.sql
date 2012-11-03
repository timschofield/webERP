INSERT INTO config VALUES('ExchangeRateFeed','ECB');
ALTER TABLE `salesorders` ADD `salesperson` VARCHAR( 4 ) NOT NULL , ADD INDEX ( `salesperson` ); 
ALTER TABLE `salesman` CHANGE `salesmancode` `salesmancode` VARCHAR( 4 ) NOT NULL DEFAULT '';
ALTER TABLE `salesorderdetails` DROP `commissionrate`;
ALTER TABLE `salesorderdetails` DROP `commissionearned`;
INSERT INTO scripts VALUES ('CounterReturns.php','5','Allows credits and refunds from the default Counter Sale account for an inventory location');
UPDATE config SET confvalue='4.09.1' WHERE confname='VersionNumber';

