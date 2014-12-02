<?php

define("CURRENCY_CODE", 'IDR');
define("CUSTOMER_TYPE_RETAIL", '2,7');
define("CUSTOMER_TYPE_CONSIGNMENT", '6');
define("CUSTOMER_TYPE_WHOLESALE", '3,4,5');
define("CUSTOMER_TYPE_ONLINE", '9');
define("RETAIL_PRICE_LIST", 'RT');

/* Defines about prices IDR */
define("PRICE_ROUNDING_STEP01",   10000);
define("PRICE_ROUNDING_LIMIT01", 300000);
define("PRICE_ROUNDING_STEP02",   25000);
define("PRICE_ROUNDING_LIMIT02",1000000);
define("PRICE_ROUNDING_STEP03",   50000);

/* Defines about customer behaviour*/
define("AVERAGE_INVOICE_VALUE_01",  125000);
define("AVERAGE_INVOICE_VALUE_02",  250000);
define("AVERAGE_INVOICE_VALUE_03",  375000);
define("AVERAGE_INVOICE_VALUE_04",  500000);
define("AVERAGE_INVOICE_VALUE_05",  750000);
define("AVERAGE_INVOICE_VALUE_06", 1000000);
define("AVERAGE_INVOICE_VALUE_07", 1500000);
define("AVERAGE_INVOICE_VALUE_08", 2000000);

/* Defines about age of customers */
define("AGE_STEP_01",  17);
define("AGE_STEP_02",  24);
define("AGE_STEP_03",  34);
define("AGE_STEP_04",  44);
define("AGE_STEP_05",  54);
define("AGE_STEP_06",  64);
define("AGE_STEP_07",  74);

/* Defines about categories */
define("LIST_STOCK_CATEGORIES_IN_KL_SHOPS_NOT_FOR_SALE", "('SHDISP', 'SHCONS', 'SHPACK')");

define("LIST_STOCK_CATEGORIES_ACTIVE",             "('TESTSI','TESTSS','TESTFJ','TESTAC','SILVER','STAINL','FASHIO','ACCESO')");
define("LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING", "('NOPOSI','NOPOSS','NOPOFJ','NOPOAC')");
define("LIST_STOCK_CATEGORIES_DISCOUNT",           "('DISCOU', 'OUTLET')");
define("LIST_STOCK_CATEGORIES_OUTLET",             "('OUTLET')");
define("LIST_STOCK_CATEGORIES_PROMOTIONAL_ITEMS",  "('ZAPRO')");
define("LIST_STOCK_CATEGORIES_OLD",                "('ZZZZZZ', 'ZZZZZX')");
define("LIST_STOCK_CATEGORIES_SHOP_DISPLAYS",      "('SHDISP')");
define("LIST_STOCK_CATEGORIES_SHOP_CONSUMABLES",   "('SHCONS')");

/* Defines about LOCATIONS*/
define("LIST_SHOPS_WITH_OUTLET",             "('')");
//define("LIST_SHOPS_WITH_OUTLET",           "('TOKLE')");
define("LIST_ACTIVE_KL_SHOPS_BALI",          "('TOK66','TOKSA','TOKKS','TOKJC','TOKSE','TOKUB','TOKMF','TOKPU','TOKSU','TOKBW','TOKOB','TOKSS')"); // NOT includes the shop online
define("LIST_SHOPS_WITH_DISCOUNT",    		 "('TOKJC','TOKBW','TOKSU','TOKWS')");

define("LIST_LOCATIONS_SPG_STOCK_STATUS",	 "('KANTO','TOK66','TOKSA','TOKKS','TOKJC','TOKSE','TOKUB','TOKMF','TOKOB','TOKPU','TOKSU','TOKSS','TOKBW')"); 

define("LIST_SHOPS_USING_PACKAGING_CONTROL", "('TOK66','TOKSA','TOKKS','TOKJC','TOKSE','TOKUB','TOKMF','TOKOB','TOKPU','TOKSS','TOKSU','TOKBW','PACKA')"); // PACKA MUST be the last one in this list
define("LIST_ITEMS_USING_PACKAGING_CONTROL", "('PKBX01-L','PKBX01-M','PKBX01-S','PKPB01-L','PKPB01-M','PKPB01-S','PKSB02-L','PKSB02-M','PKSB02-S')");

define("LIST_KANTOR_LOCATIONS",              "('KANTO','SAMPR','SASPG','SERSU','SERVI','SERDE','WHOLE')");
define("LIST_CONSIGNMENT_LOCATIONS",         "('WABOM','WHAYA','WHINT')");

define("LIST_LOCATIONS_WITH_RL_ALWAYS_ZERO", "('KANTO','PACKA','SUPBA','WHSHE','SERVI','SERSU','SERDE','SAMPR','SASPG')");

/* Defines about Performance */
define("IMPROVEMENT_AVERAGE_SALES", 5);
define("IMPROVEMENT_SALES_COMPARED_LAST_YEAR", 5);

/* Defines about Retail Sales at KL Shops*/
define("LENGHT_OF_LIST_OF_CODES_RETAIL_SHOP_SALES", 6); 

/* Defines about Packaging*/
define("MINIMUM_REORDER_LEVEL_FOR_PACKAGING_AT_SHOP", 8); 

define("PAYMENT_BY_CASH", 2); 
define("PAYMENT_BY_CREDITCARD", 3); 
define("PERCENTAGE_SALES_CASH_TO_PT", 10); // % of cash transactions going to cash KL acccounts
define("PERCENTAGE_COMPENSATION_HPP_PT", 160); // % of HPP to be assigned to PT sales
define("ACCOUNT_COMPENSATION_HPP_PT", "510010050"); 

define("COMISSION_CC_DANAMON",     1.80); // % of Credit card comission paid to Danamon
define("COMISSION_AMEX_DANAMON",   3.00); // % of Credit card comission paid to American Express by Danamon
define("COMISSION_CC_MANDIRI",     1.80); // % of Credit card comission paid to Mandiri

define("ACCOUNT_CASH_TOK66", "111111101"); // number of account for toko 66
define("ACCOUNT_CASH_TOKSA", "111111102"); // number of account for toko SA
define("ACCOUNT_CASH_TOKKS", "111111103"); // number of account for toko KS
define("ACCOUNT_CASH_TOKLE", "111111104"); // number of account for toko LE
define("ACCOUNT_CASH_TOKJC", "111111105"); // number of account for toko JC
define("ACCOUNT_CASH_TOKBW", "111111106"); // number of account for toko BW
define("ACCOUNT_CASH_TOKMF", "111111107"); // number of account for toko MF
define("ACCOUNT_CASH_TOKUB", "111111108"); // number of account for toko UB
define("ACCOUNT_CASH_TOKSE", "111111109"); // number of account for toko SE
define("ACCOUNT_CASH_TOKPU", "111111110"); // number of account for toko PU
define("ACCOUNT_CASH_TOKSU", "111111111"); // number of account for toko SU
define("ACCOUNT_CASH_TOKOB", "111111112"); // number of account for toko OB
define("ACCOUNT_CASH_TOKSS", "111111113"); // number of account for toko SS

define("ACCOUNT_CASH_TOKPA", "111111130"); // number of account for toko PAMERAN

define("ACCOUNT_BANK_DANAMON_IDR", "111121105PT"); // number of account for Bank Danamon IDR
define("ACCOUNT_BANK_MANDIRI_IDR", "111121100PT"); // number of account for Bank Mandiri IDR
define("ACCOUNT_COMISSION_CREDITCARD", "700211300PT"); // number of account used to charge the bank comission

/* Defines about WebStore */
define('RATE_IDRUSD_FOR_RETAIL_WEBSTORE',12000);
define("ONLINE_PRICE_LIST", 'RT');

/* Defines about weight in KG*/
define('STANDARD_TALI_WEIGHT',     0.050);
define('STANDARD_BEAD_WEIGHT',     0.050);
define('STANDARD_RING_WEIGHT',     0.050);
define('STANDARD_EARRING_WEIGHT',  0.060);
define('STANDARD_BRACELET_WEIGHT', 0.080);
define('STANDARD_PENDANT_WEIGHT',  0.090);
define('STANDARD_NECKLACE_WEIGHT', 0.190);
define('STANDARD_BAG_WEIGHT',      0.750);

/* Defines about volume in m3*/
define('STANDARD_TALI_VOLUME',     0.0003);
define('STANDARD_BEAD_VOLUME',     0.0003);
define('STANDARD_RING_VOLUME',     0.0003);
define('STANDARD_EARRING_VOLUME',  0.0003);
define('STANDARD_BRACELET_VOLUME', 0.0004);
define('STANDARD_PENDANT_VOLUME',  0.0004);
define('STANDARD_NECKLACE_VOLUME', 0.0004);
define('STANDARD_BAG_VOLUME',      0.0060);

/* Defines about website sales categories */
define("CATEGORIES_AVAILABLE_WEBSITE", "('TESTSI','TESTSS','TESTFJ','TESTAC','SILVER','STAINL','FASHIO','ACCESO','NOPOSI','NOPOSS','NOPOFJ','NOPOAC','DISCOU','CONSIG')");
define('FEATURED_IN_WEBSITE_AS_TOP_SALES',20);

define('ITEM_EXCLUDED_FROM_WEBSITE',-9999);

define('SILVER_JEWELLERY',5);
define('SILVER_RINGS',31);
define('SILVER_BRACELETS',32);
define('SILVER_EARRINGS',33);
define('SILVER_PENDANTS',34);
define('SILVER_NECKLACES',48);
define('SILVER_ANKLETS',57);
define('SILVER_TOERINGS',58);
define('SILVER_SLIMRINGS',67);
define('SILVER_EARCUFFS',71);

define('STAINLESS_STEEL_JEWELLERY',6);
define('STAINLESS_STEEL_RINGS',43);
define('STAINLESS_STEEL_BRACELETS',44);
define('STAINLESS_STEEL_EARRINGS',45);
define('STAINLESS_STEEL_PENDANTS',46);
define('STAINLESS_STEEL_NECKLACE',47);
define('STAINLESS_STEEL_EARCUFFS',73);

define('FASHION_JEWELLERY',14);
define('FASHION_JEWELLERY_RINGS',35);
define('FASHION_JEWELLERY_BRACELETS',36);
define('FASHION_JEWELLERY_EARRINGS',37);
define('FASHION_JEWELLERY_PENDANTS',38);
define('FASHION_JEWELLERY_NECKLACES',50);
define('FASHION_JEWELLERY_EARCUFFS',72);

define('LEATHER_JEWELLERY',26);
define('LEATHER_RINGS',39);
define('LEATHER_BRACELETS',40);
define('LEATHER_EARRINGS',41);
define('LEATHER_PENDANTS',42);
define('LEATHER_NECKLACES',49);
define('LEATHER_EARCUFFS',76);

define('BAGS',29);

define('JEWELLERY_ON_SPECIAL',51);
define('RINGS_ON_SPECIAL',52);
define('BRACELETS_ON_SPECIAL',53);
define('EARRINGS_ON_SPECIAL',54);
define('PENDANTS_ON_SPECIAL',55);
define('NECKLACES_ON_SPECIAL',56);
define('ANKLETS_ON_SPECIAL',60);
define('TOERINGS_ON_SPECIAL',59);
define('EARCUFFS_ON_SPECIAL',74);

define('CLASSIC_JEWELLERY',61);
define('CLASSIC_RINGS',62);
define('CLASSIC_BRACELETS',63);
define('CLASSIC_EARRINGS',64);
define('CLASSIC_PENDANTS',65);
define('CLASSIC_NECKLACES',66);
define('CLASSIC_EARCUFFS',75);

define('WORLD_BRAND_JEWELLERY',68);
define('WORLD_BRAND_PLATADEPALO',69);
define('WORLD_BRAND_HIPANEMA',70);


?>