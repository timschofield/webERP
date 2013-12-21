ALTER TABLE  `emailsettings` CHANGE  `username`  `username` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL
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
UPDATE config SET confvalue='4.12' WHERE confname='VersionNumber';

