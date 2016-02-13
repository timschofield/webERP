<?php

define("CURRENCY_CODE", 'IDR');
define("CUSTOMER_TYPE_RETAIL", '2,7');
define("CUSTOMER_TYPE_CONSIGNMENT", '6');
define("CUSTOMER_TYPE_WHOLESALE", '3,4,5');
define("CUSTOMER_TYPE_ONLINE", '9');
define("RETAIL_PRICE_LIST", 'RT');

/* Defines about prices IDR */
/* Changed all steps to 5.000 IDR per laia's request on 07/12/2015*/
define("PRICE_ROUNDING_STEP01",    5000);
define("PRICE_ROUNDING_LIMIT01", 300000);
define("PRICE_ROUNDING_STEP02",    5000);
define("PRICE_ROUNDING_LIMIT02",1000000);
define("PRICE_ROUNDING_STEP03",    5000);

/* Defines abot small selling prices */
define("SMALL_PRICE_CALCULATED_STEP01",  50000);
define("SMALL_PRICE_CORRECTED_STEP01",  100000);

define("SMALL_PRICE_CALCULATED_STEP02",  75000);
define("SMALL_PRICE_CORRECTED_STEP02",  110000);

define("SMALL_PRICE_CALCULATED_STEP03", 100000);
define("SMALL_PRICE_CORRECTED_STEP03",  120000);

define("SMALL_PRICE_CALCULATED_STEP04", 130000);
define("SMALL_PRICE_CORRECTED_STEP04",  130000);


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

/* Defines about Pricetag control */
define("RE_CHECK_PRICETAGS_CHANGED_DURING_LAST_X_DAYS",  35);

/* Defines about standard Cost*/
define("STANDARD_COST_FACTOR_INDONESIA"  , 1.00);
define("STANDARD_COST_FACTOR_THAILAND"   , 1.25);
define("STANDARD_COST_FACTOR_CHINA"      , 1.25);
define("STANDARD_COST_FACTOR_HONG_KONG"  , 1.25);
define("STANDARD_COST_FACTOR_CATALONIA"  , 1.25);
define("STANDARD_COST_FACTOR_PHILIPPINES", 1.25);

/* Defines about categories */
define("LIST_STOCK_CATEGORIES_IN_KL_SHOPS_NOT_FOR_SALE", "('SHDISP', 'SHCONS', 'SHPACK')");

define("LIST_STOCK_CATEGORIES_SETUP",              "('SETKL','SETBL','SETGE')");
define("LIST_STOCK_CATEGORIES_TEST",               "('TESTKL','TESTBL','TESTGE')");
define("LIST_STOCK_CATEGORIES_STABLE",             "('STABKL','STABBL','STABGE')");
define("LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING", "('NOPOKL','NOPOBL','NOPOGE')");
define("LIST_STOCK_CATEGORIES_DISCOUNT",           "('DISC20','DISC50','DISC80')");
define("LIST_STOCK_CATEGORIES_OUTLET",             "('DISC50','DISC80')");
define("LIST_STOCK_CATEGORIES_CONSIGNMENT",        "('CONSIG')");
define("LIST_STOCK_CATEGORIES_OLD",                "('ZZZZZZ', 'ZZZZZX')");
define("LIST_STOCK_CATEGORIES_PROMOTIONAL_ITEMS",  "('ZAPRO')");
define("LIST_STOCK_CATEGORIES_SHOP_CONSUMABLES",   "('SHCONS')");
define("LIST_STOCK_CATEGORIES_SHOP_DISPLAYS",      "('SHDISP')");
define("LIST_STOCK_CATEGORIES_SHOP_PACKAGING",     "('SHPACK')");

define("LIST_STOCK_CATEGORIES_KAPAL_LAUT", "('TESTKL','STABKL','NOPOKL')");
define("LIST_STOCK_CATEGORIES_BLINK",      "('TESTBL','STABBL','NOPOBL')");
define("LIST_STOCK_CATEGORIES_GENERAL",    "('TESTGE','STABGE','NOPOGE')");


/* Defines about LOCATIONS*/
define("CODE_KANTOR",     "'KANTO'");
define("CODE_ONLINE_SHOP","'TOKWS'");

define("LIST_ALL_SHOPS",           "('TOK66','TOKSA','TOKKS','TOKJC','TOKSE','TOKUB','TOKMF','TOKPU','TOKSU','TOKBW','TOKOB','TOKSS','TOKPA','TOKKA','TOKSU','TOKJC','TOKMU','TOKPS')"); // NOT includes the shop online
define("LIST_SHOPS_KAPAL_LAUT",    "('TOK66','TOKSA','TOKKS','TOKSE','TOKMF','TOKPU','TOKBW','TOKOB','TOKSS','TOKPA','TOKKA')"); // NOT includes the shop online
define("LIST_SHOPS_OUTLET",        "('TOKSU','TOKJC','TOKUB')");
define("LIST_SHOPS_BLINK",         "('TOKMU','TOKPS')");

define("LIST_ONLINE_SHOPS", "(" . CODE_ONLINE_SHOP . ")");

define("LIST_GUDANG_FOR_PACKAGING","('PACKA')");

define("LIST_LOCATIONS_SPG_STOCK_STATUS",	 "('KANTO','TOK66','TOKSA','TOKKS','TOKJC','TOKSE','TOKUB','TOKMF','TOKOB','TOKPU','TOKSU','TOKSS','TOKBW','TOKPA','TOKKA','TOKMU','TOKPS')"); 

define("LIST_ITEMS_KAPAL_LAUT_PACKAGING", "('PKBX01-L','PKBX01-M','PKBX01-S','PKPB01-L','PKPB01-M','PKPB01-S','PKSB02-L','PKSB02-M','PKSB02-S')");
define("LIST_ITEMS_OUTLET_PACKAGING",     "('PKPB02-L','PKPB02-M','PKPB02-S','PKSB03')");
define("LIST_ITEMS_BLINK_PACKAGING",      "('PKPB03-L','PKPB03-M','PKPB03-S','PKSB04-XL','PKSB04-L','PKSB04-M','PKSB04-S')");

define("LIST_KANTOR_LOCATIONS",      "('KANTO','SAMPR','SASPG','SERSU','SERVI','SERDE','WHOLE')");
define("LIST_SERVICE_LOCATIONS",     "('SERSU','SERVI')");
define("LIST_CONSIGNMENT_LOCATIONS", "('')");

define("LIST_LOCATIONS_WITH_RL_ALWAYS_ZERO", "('KANTO','PACKA','SUPBA','SERVI','SERSU','SERDE','SAMPR','SASPG')");

/* Defines about Performance */
define("IMPROVEMENT_AVERAGE_SALES", 5);
define("IMPROVEMENT_SALES_COMPARED_LAST_YEAR", 5);

/* Defines about Retail Sales at KL Shops*/
define("LENGHT_OF_LIST_OF_CODES_RETAIL_SHOP_SALES", 6); 

/* Defines about Stock Control*/
define("STOCK_MOVEMENT_DAYS_FOR_SPG", 60); 
define("TRANSFER_LIST_DAYS_FOR_SPG", 7); 

define("PAYMENT_BY_CASH", 2); 
define("PAYMENT_BY_CREDITCARD", 3); 
define("PERCENTAGE_SALES_CASH_TO_PT", 10.5); // % of cash transactions going to PT cash acccounts
define("PERCENTAGE_COMPENSATION_HPP_PT", 100.0); // % of HPP to be assigned to PT sales. 100 means NO compensation.
define("ACCOUNT_COMPENSATION_HPP_PT", "510010050"); 
define("ACCOUNT_HUTANG_PPH23", "611012005PT"); // number of account for retention PPH23

define("COMISSION_CC_DANAMON",     1.80); // % of Credit card comission paid to Danamon
define("COMISSION_AMEX_DANAMON",   3.00); // % of Credit card comission paid to American Express by Danamon
define("COMISSION_CC_MANDIRI",     1.80); // % of Credit card comission paid to Mandiri
define("COMISSION_CC_BCA",         1.80); // % of Credit card comission paid to BCA
define("COMISSION_AMEX_BCA",       3.00); // % of Credit card comission paid to American Express by BCA

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
define("ACCOUNT_CASH_TOKPA", "111111114"); // number of account for toko PA
define("ACCOUNT_CASH_TOKKA", "111111115"); // number of account for toko KA
define("ACCOUNT_CASH_TOKMU", "111111116"); // number of account for toko MU
define("ACCOUNT_CASH_TOKPS", "111111117"); // number of account for toko PS
define("ACCOUNT_CASH_TOKAR", "111111118"); // number of account for toko AR

define("ACCOUNT_BANK_DANAMON_IDR", "111121105PT"); // number of account for Bank Danamon IDR
define("ACCOUNT_BANK_MANDIRI_IDR", "111121101PT"); // number of account for Bank Mandiri IDR
define("ACCOUNT_BANK_BCA_IDR", "111121110PT"); // number of account for Bank BCA IDR
define("ACCOUNT_COMISSION_CREDITCARD", "700211300PT"); // number of account used to charge the bank comission

/* Defines about WebStore */

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
define('STANDARD_FOULARD_WEIGHT',  0.250);
define('STANDARD_BROOCHE_WEIGHT',  0.080);

/* Defines about volume in m3*/
define('STANDARD_TALI_VOLUME',     0.0003);
define('STANDARD_BEAD_VOLUME',     0.0003);
define('STANDARD_RING_VOLUME',     0.0003);
define('STANDARD_EARRING_VOLUME',  0.0003);
define('STANDARD_BRACELET_VOLUME', 0.0004);
define('STANDARD_PENDANT_VOLUME',  0.0004);
define('STANDARD_NECKLACE_VOLUME', 0.0004);
define('STANDARD_BAG_VOLUME',      0.0060);
define('STANDARD_FOULARD_VOLUME',  0.0010);
define('STANDARD_BROOCHE_VOLUME',  0.0004);

/* Defines about website sales categories */
define("CATEGORIES_AVAILABLE_WEBSITE", "('TESTKL','TESTBL','TESTGE','STABKL','STABBL','STABGE','NOPOKL','NOPOBL','NOPOGE','DISC20','DISC50','CONSIG')");
define('FEATURED_IN_WEBSITE_AS_TOP_SALES',20);

define('ITEM_EXCLUDED_FROM_WEBSITE',-9999);

define('KL_JEWELLERY',5);
define('KL_RINGS',31);
define('KL_BRACELETS',32);
define('KL_EARRINGS',33);
define('KL_PENDANTS',34);
define('KL_NECKLACES',48);
define('KL_ANKLETS',57);
define('KL_TOERINGS',58);
define('KL_SLIMRINGS',67);
define('KL_EARCUFFS',71);
define('KL_BROOCHES',82);

define('BLINK_JEWELLERY',14);
define('BLINK_RINGS',35);
define('BLINK_BRACELETS',36);
define('BLINK_EARRINGS',37);
define('BLINK_PENDANTS',38);
define('BLINK_NECKLACES',50);
define('BLINK_EARCUFFS',72);
define('BLINK_BROOCHES',77);

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
define('BROOCHES_ON_SPECIAL',81);

define('CLASSIC_JEWELLERY',61);
define('CLASSIC_RINGS',62);
define('CLASSIC_BRACELETS',63);
define('CLASSIC_EARRINGS',64);
define('CLASSIC_PENDANTS',65);
define('CLASSIC_NECKLACES',66);
define('CLASSIC_EARCUFFS',75);
define('CLASSIC_BROOCHES',80);

define('WORLD_BRAND_JEWELLERY',68);
define('WORLD_BRAND_PLATADEPALO',69);
define('WORLD_BRAND_HIPANEMA',70);
define('WORLD_BRAND_DESIGUAL',83);

?>