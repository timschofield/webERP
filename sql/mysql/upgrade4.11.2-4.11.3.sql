ALTER table stockmoves CHANGE reference reference varchar(100) NOT NULL DEFAULT '';
alter table stockcatproperties drop foreign key stockcatproperties_ibfk_2;
alter table stockcatproperties drop foreign key stockcatproperties_ibfk_3;
ALTER TABLE  `emailsettings` CHANGE  `username`  `username` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
CREATE TABLE IF NOT EXISTS `pricematrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantitybreak` int(11) NOT NULL DEFAULT '1',
  `price` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`salestype`,`stockid`,`quantitybreak`),
  KEY `DiscountCategory` (`stockid`),
  KEY `SalesType` (`salestype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO scripts VALUES('PriceMatrix.php',11,'Mantain stock prices according to quantity break and sales types');
DELETE FROM scripts WHERE script='FixedAssetList.php';
DELETE FROM scripts WHERE script='ManualContents.php';
DELETE FROM scripts WHERE script='MenuAccess.php';
DELETE FROM scripts WHERE script='OrderEntryDiscountPricing.php';
DELETE FROM scripts WHERE script='PrintSalesOrder.php';
DELETE FROM scripts WHERE script='ReportBug.php';
DELETE FROM scripts WHERE script='ReportletContainer.php';
DELETE FROM scripts WHERE script='SystemCheck.php';
UPDATE holdreasons set dissallowinvoices=2 WHERE reasoncode=20;
ALTER table stockmoves CHANGE reference reference varchar(100) NOT NULL DEFAULT '';
ALTER TABLE bom ADD COLUMN sequence INT(11) NOT NULL DEFAULT 0 AFTER parent;
INSERT INTO scripts VALUES('TopCustomers.php',1,'Shows the top customers');
UPDATE config SET confvalue='4.11.3' WHERE confname='VersionNumber';
