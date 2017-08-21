<?php

define("CURRENCY_CODE", 'IDR');
define("CUSTOMER_TYPE_RETAIL", '2');
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
define("STANDARD_COST_FACTOR_INDONESIA", 1.00);
define("STANDARD_COST_FACTOR_FOREIGN"  , 1.25);

/* Defines about categories */
define("LIST_STOCK_CATEGORIES_IN_KL_SHOPS_NOT_FOR_SALE", "('SHDISP', 'SHCONS', 'SHPACK', 'SHOTHE')");

define("LIST_STOCK_CATEGORIES_SETUP",              "('SETKL', 'SETBL', 'SETGE' )");
define("LIST_STOCK_CATEGORIES_TEST",               "('TESTKL','TESTBL','TESTGE')");
define("LIST_STOCK_CATEGORIES_STABLE",             "('STABKL','STABBL','STABGE')");
define("LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING", "('NOPOKL','NOPOBL','NOPOGE')");
define("LIST_STOCK_CATEGORIES_OUTLET",             "('DISC20','DISC50','DISC80')");
define("LIST_STOCK_CATEGORIES_CONSIGNMENT",        "('CONSIG')");
define("LIST_STOCK_CATEGORIES_OLD",                "('ZZZZZZ', 'ZZZZZX')");
define("LIST_STOCK_CATEGORIES_PROMOTIONAL_ITEMS",  "('ZAPRO')");
define("LIST_STOCK_CATEGORIES_SHOP_CONSUMABLES",   "('SHCONS', 'SHOTHE')");
define("LIST_STOCK_CATEGORIES_SHOP_DISPLAYS",      "('SHDISP')");
define("LIST_STOCK_CATEGORIES_SHOP_PACKAGING",     "('SHPACK')");
define("LIST_STOCK_CATEGORIES_COMPONENTS",         "('COMPON')");

define("LIST_STOCK_CATEGORIES_KAPAL_LAUT", "('TESTKL','STABKL','NOPOKL')");
define("LIST_STOCK_CATEGORIES_BLINK",      "('TESTBL','STABBL','NOPOBL')");
define("LIST_STOCK_CATEGORIES_GENERAL",    "('TESTGE','STABGE','NOPOGE')");


/* Defines about LOCATIONS*/
define("CODE_KANTOR",      "'KANTO'");
define("CODE_ONLINE_SHOP", "'TOKWS'");
define("ZONES_OF_KANTOR",   "('OFFICE')"); 

define("LIST_ALL_SHOPS",           "('TOK66','TOKSA','TOKKS','TOKSE','TOKUB','TOKPU','TOKSU','TOKOB','TOKSS','TOKPA','TOKKA','TOKMU','TOKPS','TOKAR','TOKSB','TOKPB','TOKBU','TOKM2','TOKU2','TOKU3','TOKO2','TOKBB','TOKTB')"); // NOT includes the shop online
define("LIST_SHOPS_KAPAL_LAUT",    "('TOK66','TOKSA','TOKKS','TOKSE','TOKPU','TOKOB','TOKSS','TOKPA','TOKKA','TOKM2','TOKSU','TOKU2')"); // NOT includes the shop online
define("LIST_SHOPS_OUTLET",        "('TOKUB','TOKAR')"); 
define("LIST_SHOPS_BLINK",         "('TOKMU','TOKPS','TOKSB','TOKPB','TOKBU','TOKU3','TOKO2','TOKBB','TOKTB')");

define("LIST_ONLINE_SHOPS", "('TOKWS')");

define("LIST_GUDANG_FOR_PACKAGING","('PACKA')");

define("LIST_LOCATIONS_SPG_STOCK_STATUS", "('KANTO','TOK66','TOKSA','TOKKS','TOKSE','TOKUB','TOKOB','TOKPU','TOKSU','TOKSS','TOKPA','TOKKA','TOKMU','TOKPS','TOKAR','TOKSB','TOKPB','TOKBU','TOKM2','TOKU2','TOKU3','TOKO2','TOKBB','TOKTB')"); 

define("LIST_ITEMS_KAPAL_LAUT_PACKAGING", "('PKBX01-L','PKBX01-M','PKBX01-S','PKPB01-L','PKPB01-M','PKPB01-S','PKSB02-L','PKSB02-M','PKSB02-S')");
define("LIST_ITEMS_OUTLET_PACKAGING",     "('PKPB02-L','PKPB02-M','PKPB02-S','PKSB03')");
define("LIST_ITEMS_BLINK_PACKAGING",      "('PKPB03-L','PKPB03-M','PKPB03-S','PKSB04-L','PKSB04-M','PKSB04-S')");

define("LIST_KANTOR_LOCATIONS",      "('KANTO','SAMPR','SASPG','SERSU','SERVI','SERDE','WHOLE')");
define("LIST_SERVICE_LOCATIONS",     "('SERSU','SERVI')");
define("LIST_SAMPLE_LOCATIONS",      "('SAMPR')");
define("LIST_CONSIGNMENT_LOCATIONS", "('')");

define("LIST_LOCATIONS_WITH_RL_ALWAYS_ZERO", "('KANTO','PACKA','SUPBA','SERVI','SERSU','SERDE','SAMPR','SASPG')");

/* Defines about Performance */
define("IMPROVEMENT_AVERAGE_SALES", 5);
define("IMPROVEMENT_SALES_COMPARED_LAST_YEAR", 5);

/* Defines about Retail Sales at KL Shops*/
define("LENGHT_OF_LIST_OF_CODES_RETAIL_SHOP_SALES", 5); 

/* Defines about Stock Control*/
define("STOCK_MOVEMENT_DAYS_FOR_SPG", 60); 
define("TRANSFER_LIST_DAYS_FOR_SPG", 7); 

define("PAYMENT_BY_CASH", 2); 
define("PAYMENT_BY_CREDITCARD", 3); 
define("PERCENTAGE_PPN", 10); // %PPN
define("PERCENTAGE_SALES_CASH_TO_PT", 10.0); // % of cash transactions going to PT cash acccounts
define("PERCENTAGE_COMPENSATION_HPP_PT", 150.0); // % of HPP to be assigned to PT sales. 100 means NO compensation.
define("ACCOUNT_COMPENSATION_HPP_PT", "510010050"); 
define("ACCOUNT_HUTANG_PPH23", "611012005PT"); // GL account for retention of PPH23 in Petty cash

define("COMISSION_CC_DANAMON",     1.80); // % of Credit card comission paid to Danamon
define("COMISSION_AMEX_DANAMON",   3.00); // % of Credit card comission paid to American Express by Danamon
define("COMISSION_CC_MANDIRI",     1.80); // % of Credit card comission paid to Mandiri
define("COMISSION_CC_BCA",         1.80); // % of Credit card comission paid to BCA
define("COMISSION_AMEX_BCA",       3.00); // % of Credit card comission paid to American Express by BCA

define("ACCOUNT_BANK_DANAMON_IDR", "111121105PT"); // number of account for Bank Danamon IDR
define("ACCOUNT_BANK_MANDIRI_IDR", "111121101PT"); // number of account for Bank Mandiri IDR
define("ACCOUNT_BANK_BCA_IDR", "111121110PT"); // number of account for Bank BCA IDR
define("ACCOUNT_COMISSION_CREDITCARD", "700211300PT"); // number of account used to charge the bank comission

define("PERCENTAGE_CONSIGNMENT_PTADU_TP_PTBB", 60.0); // %of retail price charged by PT.ADU for sales to PT.BB


/* Defines about financial Analysis*/
define("JUTA", 1000000);
define("GL_INCOME_CC_PT",   "('410000010PT')");
define("GL_INCOME_CASH_PT",   "('410000000PT')");
define("GL_INCOME_CASH",   "('410000000')");
define("GL_INCOME_OTHERS_PT",   "('410000500PT','410010000PT','410010010PT')");
define("GL_INCOME_OTHERS",   "('410000500','410010000')");

define("GL_COGS_GOODS",   "('510010000','510010000PT','510010050')");
define("GL_COGS_OTHERS",   "('510010100PT','510500010PT')");

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
define("CATEGORIES_AVAILABLE_WEBSITE", "('TESTKL','TESTBL','TESTGE','STABKL','STABBL','STABGE','NOPOKL','NOPOBL','NOPOGE','DISC20','DISC50')");
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

//********************************************************************************
// WebERP - Opencart Bridge settings
// previously were in the file includes/WeberpOpenCartDefines.php
// moved here for easy maintenance
//********************************************************************************

define("WEBERP_ONLINE_CUSTOMER_CODE_PREFIX",    'WEB-KL-');

define("OPENCART_STORE_KAPAL_LAUT", 0);
define("OPENCART_STORE_BLINK", 4);
define("OPENCART_STORE_OUTLET", 5);
define("OPENCART_STORE_WHOLESALE", 8);

define("LOCATIONS_WITH_STOCK_FOR_ONLINE_SHOP", 'KANTO,TOK66,TOKSA,TOKSS,TOKOB,TOKKS,TOKUB,TOKSE,TOKSU,TOKPU,TOKPA,TOKKA,TOKMU,TOKPS,TOKAR,TOKSB,TOKPB,TOKBU,TOKM2,TOKU2,TOKU3,TOKO2,TOKBB,TOKTB');
define("WEBERP_DISCOUNTS_IN_OPENCART_TABLE", 'product_special');

define("OPENCART_OUTLET_CATEGORIES", '51,52,53,54,55,56,59,60,74');
define("WEBERP_OUTLET_CATEGORIES", '51,52,53,54,55,56,59,60,74');

define("WEBERP_CATEGORIES_FOR_GOOGLE_PRODUCT_FEED", '31,32,33,34,36,37,50,38,35,43,44,45,46,47,48,51,52,53,54,55,56,40,41,49,42,39,61,62,63,64,65,66,67,71,72,73,74,75,76');

define("GOOGLE_BRAND", 'Kapal-Laut. Your Essential Jewellery');
define("GOOGLE_GENDER", 'Female');
define("GOOGLE_AGEGROUP", 'Adult');
define("GOOGLE_CONDITION", 'New');
define("GOOGLE_OOS_STATUS", 'Available for Order');
define("GOOGLE_IDENTIFIER", 'TRUE');

/*	From webERP to OpenCart */
define("PATH_OPENCART_IMAGES", 'data/KL/part_pics/');
define("ABSOLUTE_PATH_OPENCART_IMAGES", '/home4/kurakura/public_html/kapal-laut.com/image/data/KL/part_pics/');

define("PATH_OPENCART_BASE", 'http://www.kapal-laut.com');
define("ROUTE_TO_PRODUCT", 'index.php?route=product/product&');
define("REDIRECT_RESPONSE_CODE", 301);

/* From OpenCart to webERP CUSTOMERS */

define("OPENCART_DEFAULT_CUSTOMER_HOLD_REASON", '1');
define("OPENCART_DEFAULT_CUSTOMER_PAYMENT_TERMS", 'CW');
define("OPENCART_DEFAULT_CUSTOMER_SALES_TYPE", 'RT');
define("OPENCART_DEFAULT_CUSTOMER_TYPE", '9');
define("OPENCART_DEFAULT_CUSTOMER_CREDIT_LIMIT", 0);
define("OPENCART_DEFAULT_CUSTOMER_LANGUAGE", 'en_GB.utf8');
define("OPENCART_DEFAULT_CUSTOMER_TAXREF", '');
define("OPENCART_DEFAULT_CUSTOMER_TAXGROUPID", 1);

define("OPENCART_DEFAULT_SALESMAN", '999');
define("OPENCART_DEFAULT_AREA", 'OWS');
define("OPENCART_DEFAULT_AREA_INDONESIA", 'OWB');
define("OPENCART_DEFAULT_CURRENCY", 'IDR');
define("OPENCART_DEFAULT_LOCATION", 'TOKWS');

define("SHIPMENT01_OPENCART_TEXT", 'EMS'); 
define("SHIPMENT01_WEBERP_CODE", 5); 
define("SHIPMENT02_OPENCART_TEXT", 'JNE-OKE'); 
define("SHIPMENT02_WEBERP_CODE", 6); 
define("SHIPMENT03_OPENCART_TEXT", 'JNE-YES'); 
define("SHIPMENT03_WEBERP_CODE", 7); 
define("SHIPMENT04_OPENCART_TEXT", 'JNE-REG'); 
define("SHIPMENT04_WEBERP_CODE", 8); 
define("SHIPMENT05_OPENCART_TEXT", 'Store'); 
define("SHIPMENT05_WEBERP_CODE", 10); 

define("OPENCART_DEFAULT_SHIPVIA", 5); 

define("OPENCART_ONLINE_ORDER_DISCOUNT10", 'DISCOUNT-10%');
define("OPENCART_ONLINE_ORDER_DISCOUNT20", 'DISCOUNT-20%');
define("OPENCART_ONLINE_ORDER_DISCOUNT30", 'DISCOUNT-30%');
define("OPENCART_ONLINE_ORDER_DISCOUNT40", 'DISCOUNT-40%');
define("OPENCART_ONLINE_ORDER_DISCOUNT50", 'DISCOUNT-50%');

define("OPENCART_ONLINE_COUPON_CODE", 'VIP-ONLINE-10%');
define("OPENCART_VIP_SILVER_CODE", 'VIP-SILVER-15%');
define("OPENCART_VIP_GOLD_CODE", 'VIP-GOLD-30%');
define("OPENCART_VIP_ELITE_CODE", 'VIP-ELITE-50%');
define("OPENCART_CUSTOMER_REFUND_CODE", 'CUSTOMER-REFUND');
define("OPENCART_BIRTHDAY_DISCOUNT_CODE", 'BIRTHDAY-20%');
define("OPENCART_VALENTINE_DAY_DISCOUNT_CODE", 'VALENTINE-20%');
define("OPENCART_KARTINI_DAY_DISCOUNT_CODE", 'KARTINI-20%');
define("OPENCART_NATIONAL_DAY_DISCOUNT_CODE", 'NATIONALDAY-20%');
define("OPENCART_GIFT_100K_CODE", 'GIFT-100K');
define("OPENCART_GIFT_125K_CODE", 'GIFT-125K');
define("OPENCART_GIFT_250K_CODE", 'GIFT-250K');
define("OPENCART_GIFT_300K_CODE", 'GIFT-300K');

define("OPENCART_DEFAULT_PAYMENT_SYSTEM", 'PayPal');

define("OPENCART_FOREIGN_CURRENCY_SURCHARGE_PERCENT", 1.05); // factor to increase the exchange rate for foreign currency (> 1 = more expensive in foreign) 

/*************************************************************************/
/*                PAYPAL SETTINGS FOR sales@kapal-laut.com               */
/*************************************************************************/
define("WEBERP_GL_PAYPAL_ACCOUNT_AUD",    '111259050');
define("WEBERP_GL_PAYPAL_COMMISSION_AUD", '700211700');

define("WEBERP_GL_PAYPAL_ACCOUNT_EUR",    '111259020');
define("WEBERP_GL_PAYPAL_COMMISSION_EUR", '700211700');

define("WEBERP_GL_PAYPAL_ACCOUNT_USD",    '111259010');
define("WEBERP_GL_PAYPAL_COMMISSION_USD", '700211700');

/*************************************************************************/
/*                PAYPAL SETTINGS FOR paypal@kapal-laut.com              */
/*************************************************************************/
/*

CAL CANVIAR LES API CREDENTIALS DE OPENCART 

define("WEBERP_GL_PAYPAL_ACCOUNT_AUD",    '111259050PT');
define("WEBERP_GL_PAYPAL_COMMISSION_AUD", '700211700PT');

define("WEBERP_GL_PAYPAL_ACCOUNT_EUR",    '111259020PT');
define("WEBERP_GL_PAYPAL_COMMISSION_EUR", '700211700PT');

define("WEBERP_GL_PAYPAL_ACCOUNT_USD",    '111259010PT');
define("WEBERP_GL_PAYPAL_COMMISSION_USD", '700211700PT');
*/

?>