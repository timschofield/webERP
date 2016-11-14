Set shop into Maintenance mode

in webERP database:
ALTER TABLE  `currencies` 
	ADD  `date_created` DATETIME NOT NULL ,
	ADD  `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL;

CREATE TRIGGER currencies_creation_timestamp BEFORE INSERT ON currencies 
FOR EACH ROW
SET NEW.date_created = NOW();	

UPDATE stockmaster SET date_updated = NOW();
UPDATE currencies SET date_created = NOW();
UPDATE prices SET date_updated = NOW();

UPDATE config SET confvalue = "" WHERE confname = "ShopDebtorNo";
UPDATE config SET confvalue = "" WHERE confname = "ShopBranchCode";

Copy new sets of files to HOST

In Admin OpenCart:
System / Settings / Shop / Local / Currency = IDR
Set auto currency update to FALSE
Clean Cache browser + nitro + Cloudflare

TRUNCATE TABLE  `oc_product_special`;
UPDATE oc_order SET total = total * 10828, currency_value = 0.00008227 WHERE currency_code = "USD";
UPDATE oc_order SET total = total * 10828, currency_value = 0.00006579 WHERE currency_code = "EUR";
UPDATE oc_order SET total = total * 10828, currency_value = 0.00009735 WHERE currency_code = "AUD";
UPDATE oc_order SET total = total * 10828, currency_value = 1          WHERE currency_code = "IDR";

UPDATE `oc_order_product`  SET price = price * 10828, total = total * 10828;

UPDATE oc_product SET price = price * 12000 WHERE price < 1000;

Sync webERP to OC daily

Change prices of shipping costs

Extensions / Payments / bank Transfer set TOTAL to 10.000.000 OR DISABLE!

Unset Maintenance Mode in OC shop.
