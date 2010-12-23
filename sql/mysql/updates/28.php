<?php

/* A new table to contain offers from suppliers
 */

CreateTable('offers', "CREATE TABLE IF NOT EXISTS `offers` (
  offerid int(11) NOT NULL AUTO_INCREMENT,
  tenderid int(11) NOT NULL DEFAULT 0,
  supplierid varchar(10) NOT NULL DEFAULT '',
  stockid varchar(20) NOT NULL DEFAULT '',
  quantity double NOT NULL DEFAULT 0.0,
  uom varchar(15) NOT NULL DEFAULT '',
  price double NOT NULL DEFAULT 0.0,
  expirydate date NOT NULL DEFAULT '0000-00-00',
  currcode char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`offerid`),
  CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `offers_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

UpdateDBNo(28, $db);

?>