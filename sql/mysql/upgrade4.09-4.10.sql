INSERT INTO config VALUES('ExchangeRateFeed','ECB');
ALTER TABLE `salesorders` ADD `salesperson` VARCHAR( 4 ) NOT NULL , ADD INDEX ( `salesperson` ); 
ALTER TABLE `salesman` CHANGE `salesmancode` `salesmancode` VARCHAR( 4 ) NOT NULL DEFAULT '';
UPDATE config SET confvalue='4.09.1' WHERE confname='VersionNumber';

