SET FOREIGN_KEY_CHECKS=0;

UPDATE  `config` SET  `confvalue` =  'companies/kurakura_kl_test_erp/part_pics' WHERE  `confname` =  'part_pics_dir';
UPDATE  `config` SET  `confvalue` =  'companies/kurakura_kl_test_erp/reportwriter' WHERE  `confname` =  'reports_dir';
UPDATE  `config` SET  `confvalue` =  'companies/kurakura_kl_test_erp/logs' WHERE  `confname` =  'LogPath';

UPDATE  `config` SET  `confvalue` =  'TEST SHOP' WHERE  `confname` =  'ShopName';
UPDATE  `config` SET  `confvalue` =  'TEST SHOP' WHERE  `confname` =  'ShopTitle';

UPDATE  `config` SET  `confvalue` =  '' WHERE  `confname` =  'InventoryManagerEmail';
UPDATE  `config` SET  `confvalue` =  '' WHERE  `confname` =  'FactoryManagerEmail';
UPDATE  `config` SET  `confvalue` =  '' WHERE  `confname` =  'PurchasingManagerEmail';

UPDATE  `config` SET  `confvalue` =  'test' WHERE  `confname` =  'ShopMode';
UPDATE  `config` SET  `confvalue` =  '1372497542' WHERE  `confname` =  'ShopPayPalPassword';
UPDATE  `config` SET  `confvalue` =  'AKh80SD3d.pLz9oyaerqiR90yzDdARP3knOWMSTyjcbBNEns94xTl6WW' WHERE  `confname` =  'ShopPayPalSignature';
UPDATE  `config` SET  `confvalue` =  'testmerchant_api1.kapal-laut.com' WHERE  `confname` =  'ShopPayPalUser';

UPDATE www_users SET theme = "gel";
UPDATE www_users SET blocked = 0 WHERE userid LIKE "999%";

TRUNCATE audittrail;

SET FOREIGN_KEY_CHECKS=1;
