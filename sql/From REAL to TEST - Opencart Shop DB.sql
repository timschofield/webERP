/* PASSAR PAYPAL DE REAL A TEST */

UPDATE oc_setting
SET `value` = 'AKh80SD3d.pLz9oyaerqiR90yzDdARP3knOWMSTyjcbBNEns94xTl6WW'
WHERE `group` = 'pp_express'
	AND `key` = 'pp_express_signature';
	
UPDATE oc_setting
SET value = '1372497542'
WHERE `group` = 'pp_express'
	AND `key` = 'pp_express_password';
	
UPDATE oc_setting
SET `value` = 'testmerchant_api1.kapal-laut.com'
WHERE `group` = 'pp_express'
	AND `key` = 'pp_express_username';	

UPDATE oc_setting
SET `value` = '1'
WHERE `group` = 'pp_express'
	AND `key` = 'pp_express_test';	
	
	UPDATE oc_setting
SET `value` = 'Kapal-Laut TEST Shop'
WHERE `group` = 'config'
	AND `key` = 'config_title';		

UPDATE oc_setting
SET `value` = 'TEST TEST TEST TEST TEST TEST'
WHERE `group` = 'config'
	AND `key` = 'config_name';		

UPDATE oc_setting
SET `value` = 'data/KL/WHITE-background.jpg'
WHERE `group` = 'templatemela'
	AND `key` = 'templatemela_custom_pattern';		
/*
UPDATE oc_setting SET `value` = '1'
WHERE `group` = 'templatemela'
	AND `key` = 'templatemela_showcontrolpanel';	
	*/
UPDATE oc_setting
SET `value` = 'http://www.bumibiru.com/TEST/shop/captainkapal/'
WHERE `group` = 'config'
	AND `key` = 'entry_admin_foldername';	