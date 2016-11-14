ALTER TABLE  `oc_customer` 
	ADD  `date_updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL;

TRUNCATE oc_tax_class;
INSERT INTO `oc_tax_class` (`tax_class_id`, `title`, `description`, `date_added`, `date_modified`) 
	VALUES (1, 'No Tax (Export)', 'No Tax (Export)', '2013-11-15 00:26:48', '0000-00-00 00:00:00');

TRUNCATE oc_tax_rate;
TRUNCATE oc_tax_rate_to_customer_group;
TRUNCATE oc_tax_rule;

TRUNCATE oc_manufacturer;
INSERT INTO  `oc_manufacturer` (`manufacturer_id` ,`name` ,`image` ,`sort_order`)
	VALUES (NULL ,  'Kapal-Laut', NULL ,  '1');

TRUNCATE oc_manufacturer_to_store;
INSERT INTO  `oc_manufacturer_to_store` (`manufacturer_id` ,`store_id`)
	VALUES ('1',  '0');

TRUNCATE oc_attribute;
TRUNCATE oc_attribute_description;
TRUNCATE oc_attribute_group;
TRUNCATE oc_attribute_group_description;

TRUNCATE oc_coupon;
TRUNCATE oc_coupon_category;
TRUNCATE oc_coupon_history;
TRUNCATE oc_coupon_product;

TRUNCATE oc_option;
TRUNCATE oc_option_description;
TRUNCATE oc_option_value;
TRUNCATE oc_option_value_description;

TRUNCATE oc_product;
TRUNCATE oc_product_description;
TRUNCATE oc_product_to_store;
TRUNCATE oc_product_to_category;
TRUNCATE oc_product_discount;
TRUNCATE oc_product_special;
TRUNCATE oc_product_attribute;
TRUNCATE oc_product_option;
TRUNCATE oc_product_option_value;
TRUNCATE oc_product_filter;
TRUNCATE oc_product_image;
TRUNCATE oc_product_profile;
TRUNCATE oc_product_recurring;
TRUNCATE oc_product_related;
TRUNCATE oc_product_reward;
TRUNCATE oc_product_to_download;
TRUNCATE oc_product_to_layout;
DELETE FROM oc_url_alias 
	WHERE query LIKE "product_id%";

TRUNCATE oc_category;
TRUNCATE oc_category_description;
TRUNCATE oc_category_path;
TRUNCATE oc_category_to_store;
DELETE FROM oc_url_alias 
	WHERE query LIKE "category_id%";

TRUNCATE oc_order;
TRUNCATE oc_order_history;
TRUNCATE oc_order_product;
TRUNCATE oc_order_total;

TRUNCATE oc_paypal_order;
TRUNCATE oc_paypal_order_transaction;

TRUNCATE oc_address;
TRUNCATE oc_customer;
TRUNCATE oc_customer_ip;
TRUNCATE oc_customer;
TRUNCATE oc_customer;
TRUNCATE oc_customer;


