CREATE TABLE `klstockmarketplaces` ( 
`stockid` VARCHAR(20) NOT NULL , 
`tokopediaproductid` VARCHAR(20) NULL , 
`tokopediaenabled` TINYINT(1) NOT NULL , 
`tokopediaurl` TEXT NULL , 
`shopeeproductid` VARCHAR(20) NULL , 
`shopeeenabled` TINYINT(1) NOT NULL , 
`shopeeurl` TEXT NULL ,
`date_created` datetime NOT NULL,
`date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) 
ENGINE = InnoDB;

DELIMITER $$
CREATE TRIGGER `klstockmarketplaces_creation_timestamp` BEFORE INSERT ON `klstockmarketplaces` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

ALTER TABLE `klstockmarketplaces`
  ADD PRIMARY KEY (`stockid`);

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) 
VALUES ('KLShopeeURL.php', '50400', 'Reads an excel from Shopee and maintains product URL in Shopee');

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) 
VALUES ('KLTokopediaURL.php', '50400', 'Reads an excel from Tokopedia and maintains product URL in Tokopedia');



