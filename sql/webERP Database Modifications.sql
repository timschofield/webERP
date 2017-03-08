UPDATE  `config` SET `confvalue` = 'data/KL/part_pics/' WHERE confname = 'WeberpImagesFromOpenCart';

ALTER TABLE  `kloldprices` ADD  `date_created` DATETIME NOT NULL ,
	ADD  `date_updated` DATETIME NOT NULL;

ALTER TABLE `stockmaster`  
	ADD `length` DECIMAL(15,8) NULL DEFAULT '0' AFTER `netweight`,  
	ADD `width` DECIMAL(15,8) NULL DEFAULT '0' AFTER `length`,  
	ADD `height` DECIMAL(15,8) NULL DEFAULT '0' AFTER `width`,  
	ADD `unitsdimension` VARCHAR(15) NULL DEFAULT 'mm' AFTER `height`;
	
CREATE TABLE IF NOT EXISTS `unitsofdimension` (
  `unitid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `unitname` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`unitid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO  `unitsofdimension` (`unitid` ,`unitname`)
VALUES (NULL ,  'mm'), (NULL ,  'cm');

INSERT INTO  .`scripts` (`script` ,`pagesecurity` ,`description`)
VALUES ('OpenCartToWeberp.php',  '15',  'Opencart Connector. Sync from OC to webERP'), 
       ('WeberpToOpenCart.php',  '15',  'Opencart Connector. Sync from webERP to OC');

/* WeberpDiscountsInOpencartTable can have ONLY 2 values product_discount or product_special depending on the table we want to use to store the webERP discount in OpenCart */

INSERT INTO  `config` (`confname` ,`confvalue`)
VALUES 	('OpenCartToWeberp_LastRun',  '0000-00-00 00:00:00'), 
		('WeberpToOpenCartHourly_LastRun',  '0000-00-00 00:00:00'), 
		('WeberpToOpenCartDaily_LastRun',  '0000-00-00 00:00:00');
		
/* SALES CATEGORIES SET UP */		
		
ALTER TABLE  `salescat` 
	ADD  `date_created` DATETIME NOT NULL ,
	ADD  `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL;

CREATE TRIGGER salescat_creation_timestamp BEFORE INSERT ON salescat 
FOR EACH ROW
SET NEW.date_created = NOW();	

/* PRODUCTS SET UP */		
		
ALTER TABLE  `stockmaster` 
	ADD  `date_created` DATETIME NOT NULL ,
	ADD  `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL;

CREATE TRIGGER stockmaster_creation_timestamp BEFORE INSERT ON stockmaster 
FOR EACH ROW
SET NEW.date_created = NOW();	

ALTER TABLE  `salescatprod` 
	ADD  `date_created` DATETIME NOT NULL ,
	ADD  `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL;

CREATE TRIGGER salescatprod_creation_timestamp BEFORE INSERT ON salescatprod 
FOR EACH ROW
SET NEW.date_created = NOW();	

ALTER TABLE  `prices` 
	ADD  `date_created` DATETIME NOT NULL ,
	ADD  `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL;

CREATE TRIGGER prices_creation_timestamp BEFORE INSERT ON prices 
FOR EACH ROW
SET NEW.date_created = NOW();

ALTER TABLE  `locstock` 
	ADD  `date_created` DATETIME NOT NULL ,
	ADD  `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL;

CREATE TRIGGER lockstock_creation_timestamp BEFORE INSERT ON locstock 
FOR EACH ROW
SET NEW.date_created = NOW();

ALTER TABLE  `relateditems` 
	ADD  `date_created` DATETIME NOT NULL ,
	ADD  `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL;

CREATE TRIGGER stockmaster_creation_timestamp BEFORE INSERT ON stockmaster 
FOR EACH ROW
SET NEW.date_created = NOW();	

ALTER TABLE  `currencies` 
	ADD  `date_created` DATETIME NOT NULL ,
	ADD  `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL;

CREATE TRIGGER currencies_creation_timestamp BEFORE INSERT ON currencies 
FOR EACH ROW
SET NEW.date_created = NOW();	

UPDATE currencies SET date_created = NOW();

ALTER TABLE  `stockdescriptiontranslations` 
	ADD  `date_created` DATETIME NOT NULL ,
	ADD  `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL;

CREATE TRIGGER stockdescriptiontranslations_creation_timestamp BEFORE INSERT ON stockdescriptiontranslations 
FOR EACH ROW
SET NEW.date_created = NOW();	

UPDATE stockdescriptiontranslations SET date_created = NOW();


/* FOR TESTING PURPOSES ONLY WHILE DEVELOPING ... DELETE ON FINAL RELEASE*/
UPDATE config
	SET confvalue = '0000-00-00 00:00:00'
	WHERE confname = 'OpenCartToWeberp_LastRun' 
		OR confname = 'WeberpToOpenCartDaily_LastRun'
		OR confname = 'WeberpToOpenCartHourly_LastRun';