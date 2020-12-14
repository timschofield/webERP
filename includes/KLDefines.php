<?php

define("MINIMUM_SURVIVAL_CASH", 3000000000);

define("CURRENCY_CODE", 'IDR');
define("CUSTOMER_TYPE_RETAIL", '2');
define("CUSTOMER_TYPE_CONSIGNMENT", '6');
define("CUSTOMER_TYPE_WHOLESALE", '3,4,5');
define("CUSTOMER_TYPE_WEBSITE", '9');
define("CUSTOMER_TYPE_MARKETPLACE", '10');
define("RETAIL_PRICE_LIST", 'RT');
define("PPN_PERCENT", 10);

/* Defines about prices IDR */
define("PRICE_ROUNDING_STEP01",   10000);
define("PRICE_ROUNDING_LIMIT01", 300000);
define("PRICE_ROUNDING_STEP02",   25000);
define("PRICE_ROUNDING_LIMIT02", 600000);
define("PRICE_ROUNDING_STEP03",   50000);

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

define("LIST_STOCK_CATEGORIES_SETUP",              "('SETKLA','SETBLA','SETGEA')");
define("LIST_STOCK_CATEGORIES_TEST",               "('TESTKA','TESTBA','TESTGA')");
define("LIST_STOCK_CATEGORIES_STABLE",             "('STABKA','STABBA','STABGA')");
define("LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING", "('NOPOKA','NOPOBA','NOPOGA')");
define("LIST_STOCK_CATEGORIES_CONSIGNMENT",        "('CONSIG')");
define("LIST_STOCK_CATEGORIES_OLD",                "('ZZZZZZ', 'ZZZZZX')");
define("LIST_STOCK_CATEGORIES_PROMOTIONAL_ITEMS",  "('ZAPRO')");
define("LIST_STOCK_CATEGORIES_SHOP_CONSUMABLES",   "('SHCONS', 'SHOTHE')");
define("LIST_STOCK_CATEGORIES_SHOP_DISPLAYS",      "('SHDISP')");
define("LIST_STOCK_CATEGORIES_SHOP_PACKAGING",     "('SHPACK','SHPACA')");
define("LIST_STOCK_CATEGORIES_COMPONENTS",         "('COMPOA')");

define("LIST_STOCK_CATEGORIES_KAPAL_LAUT", "('TESTKA','STABKA','NOPOKA')");
define("LIST_STOCK_CATEGORIES_BLINK",      "('TESTBA','STABBA','NOPOBA')");
define("LIST_STOCK_CATEGORIES_OUTLET",     "('DISC20','DISC50','DISC80','DISC2A','DISC5A','DISC8A')");
define("LIST_STOCK_CATEGORIES_GENERAL",    "('TESTGA','STABGA','NOPOGA')");


/* Defines about LOCATIONS*/
define("CODE_KANTOR",      "'KANTO'");
define("CODE_ONLINE_SHOP", "'TOKWS'");
define("ZONES_OF_KANTOR",   "('OFFICE')"); 

define("BALI_SHOPS_LIST_BY_TYPE",  "('SHOPKL','SHOPBL','SHOPOU')");
define("ALL_SHOPS_LIST_BY_TYPE",   "('SHOPKL','SHOPBL','SHOPOU','ONLINE')");

define("LIST_ONLINE_SHOPS", "('TOKWS')");

define("LIST_GUDANG_FOR_PACKAGING","('PACKA')");

define("LIST_ITEMS_KAPAL_LAUT_PACKAGING", "('PKBX01-L','PKBX01-M','PKBX01-S','PKPB01-L','PKPB01-M','PKPB01-S','PKSB02-L','PKSB02-M','PKSB02-S')");
define("LIST_ITEMS_OUTLET_PACKAGING",     "('PKPB02-L','PKPB02-M','PKPB02-S','PKSB03')");
define("LIST_ITEMS_BLINK_PACKAGING",      "('PKPB03-XL','PKPB03-L','PKPB03-M','PKPB03-S','PKSB04-L','PKSB04-M','PKSB04-S')");
define("MIN_REORDER_LEVEL_PACKAGING_ITEM_PER_SHOP", 4); 

define("LIST_KANTOR_LOCATIONS",      "('KANTO','SAMPR','SASPG','SERSU','SERVI','SERDE')");
define("LIST_SERVICE_LOCATIONS",     "('SERSU','SERSW','SERVI')");
define("LIST_SAMPLE_LOCATIONS",      "('SAMPR')");
define("LIST_CONSIGNMENT_LOCATIONS", "('CSLAZ','CSZAL')");

define("LIST_LOCATIONS_WITH_RL_ALWAYS_ZERO", "('KANTO','PACKA','SUPBA','SERVI','SERSU','SERSW','SERDE','SAMPR','SASPG')");

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

define("ACCOUNT_PPN_ADU", "611012030AD"); // GL account for PPN PT.ADU
define("ACCOUNT_PPN_BB", "611012030PT"); // GL account for PPN PT.BB

define("ACCOUNT_HUTANG_PPH23", "611012005PT"); // GL account for retention of PPH23 in Petty cash

/* Defines about financial Analysis*/
define("JUTA", 1000000);
define("GL_INCOME_CC_PT",   "('410000010PT')");
define("GL_INCOME_CASH_PT",   "('410000000PT')");
define("GL_INCOME_CASH",   "('410000000')");
define("GL_INCOME_OTHERS_PT",   "('410000500PT','410010000PT','410010010PT')");
define("GL_INCOME_OTHERS",   "('410000500','410010000')");

define("GL_COGS_GOODS",   "('510010000','510010000AD','510010000PT','510010050')");
define("GL_COGS_OTHERS",   "('510010100PT','510500010PT')");

/* Defines about WebStore */

define("ONLINE_PRICE_LIST", 'RT');
// Modification COVID.  To increase the importance of shop online we keep at least MINIMUM_STOCK_ONLINESHOP_EVERY_ITEM in location "400-Online shop"
// ready to be shipped if there is any online order. 
// This way, we avoid the issue of items with QOH kantor = =, stock at the shops and online orders request them, 
// and we need to send someone to the shops to get it.
// once Bali shops return to normal, reset to 0 or a very low value, depending of importance of online shop.
// Side effect = Online shop will have a full catalog available.
define("MINIMUM_STOCK_ONLINESHOP_EVERY_ITEM", 1); 

/* Defines about weight in KG*/
define('STANDARD_TALI_WEIGHT',       0.050);
define('STANDARD_BEAD_WEIGHT',       0.050);
define('STANDARD_RING_WEIGHT',       0.050);
define('STANDARD_EARRING_WEIGHT',    0.060);
define('STANDARD_BRACELET_WEIGHT',   0.080);
define('STANDARD_PENDANT_WEIGHT',    0.090);
define('STANDARD_NECKLACE_WEIGHT',   0.190);
define('STANDARD_BAG_WEIGHT',        0.450);
define('STANDARD_FOULARD_WEIGHT',    0.250);
define('STANDARD_BROOCHE_WEIGHT',    0.080);
define('STANDARD_KEYHOLDER_WEIGHT',  0.080);
define('STANDARD_FACEMASK_WEIGHT',   0.080); 
define('STANDARD_JEWEL_ROLL_WEIGHT', 0.080); 

/* shipping dimensions in mm (webERP set up in mm) */

define('BOX_S_LENGTH',         75);
define('BOX_S_WIDTH',          75);
define('BOX_S_HEIGHT',         45);
define('BOX_M_LENGTH',        110);
define('BOX_M_WIDTH',         110);
define('BOX_M_HEIGHT',         45);
define('BOX_L_LENGTH',        230);
define('BOX_L_WIDTH',         160);
define('BOX_L_HEIGHT',         45);
define('BOX_XL_LENGTH',       300); // approx
define('BOX_XL_WIDTH',        300); // approx
define('BOX_XL_HEIGHT',       200); // approx

/* Defines about website sales categories */
define("ONLINESHOP_AVAILABLE_STOCK_CATEGORIES", "('TESTKA','TESTBA','TESTGA','STABKA','STABBA','STABGA','NOPOKA','NOPOBA','NOPOGA','DISC20','DISC2A','DISC50','DISC5A')");
define('FEATURED_IN_WEBSITE_AS_TOP_SALES',20);

define('ITEM_EXCLUDED_FROM_WEBSITE',-9999);

/* Sales Categories based on type of item*/
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
define('BLINK_KEYHOLDERS',84);
define('BAGS',29);
define('GENERAL_ACCESSORIES',88);
define('GE_JEWELLERY_ROLLS',89);
define('GE_FACEMASKS',90);
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
define('KEYHOLDERS_ON_SPECIAL',85);
define('JEWELLERY_ROLLS_ON_SPECIAL',91);
define('FACEMASKS_ON_SPECIAL',92);
define('CLASSIC_JEWELLERY',61);
define('CLASSIC_RINGS',62);
define('CLASSIC_BRACELETS',63);
define('CLASSIC_EARRINGS',64);
define('CLASSIC_PENDANTS',65);
define('CLASSIC_NECKLACES',66);
define('CLASSIC_EARCUFFS',75);
define('CLASSIC_BROOCHES',80);


//********************************************************************************
//********************************************************************************
// WebERP - Opencart Bridge settings
//********************************************************************************
//********************************************************************************

define("WEBERP_ONLINE_CUSTOMER_CODE_PREFIX",    'WEB-KL-');

define("OPENCART_STORE_KAPAL_LAUT", 0);
define("OPENCART_STORE_BLINK", 4);
define("OPENCART_STORE_OUTLET", 5);
define("OPENCART_STORE_WHOLESALE", 8);

define("WEBERP_DISCOUNTS_IN_OPENCART_TABLE", 'product_special');

define("ONLINESHOP_OUTLET_SALES_CATEGORIES", '51,52,53,54,55,56,59,60,74,81,85,91,92');

define("SALES_CATEGORIES_FOR_GOOGLE_PRODUCT_FEED", '31,32,33,34,36,37,50,38,35,43,44,45,46,47,48,51,52,53,54,55,56,40,41,49,42,39,61,62,63,64,65,66,67,71,72,73,74,75,76,88,89');

define("GOOGLE_BRAND_KL", 'Kapal-Laut. Your Essential Jewellery');
define("GOOGLE_BRAND_BLINK", 'Blink by Kapal-Laut');
define("GOOGLE_BRAND_OUTLET", 'Outlet by Kapal-Laut');
define("GOOGLE_GENDER", 'Female');
define("GOOGLE_AGEGROUP", 'Adult');
define("GOOGLE_CONDITION", 'New');
define("GOOGLE_OOS_STATUS", 'Available for Order');
define("GOOGLE_IDENTIFIER", 'TRUE');

/*	From webERP to OpenCart */
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
define("OPENCART_DEFAULT_AREA_CASH", 'OWS');
define("OPENCART_DEFAULT_AREA_PTBB", 'OWB');
define("OPENCART_DEFAULT_AREA_WHOLESALE", 'WHC');
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

define("OPENCART_DEFAULT_SHIPVIA", 6); 

define("OPENCART_ONLINE_ORDER_DISCOUNT10", 'DISCOUNT-10%');
define("OPENCART_ONLINE_ORDER_DISCOUNT20", 'DISCOUNT-20%');
define("OPENCART_ONLINE_ORDER_DISCOUNT30", 'DISCOUNT-30%');
define("OPENCART_ONLINE_ORDER_DISCOUNT40", 'DISCOUNT-40%');
define("OPENCART_ONLINE_ORDER_DISCOUNT50", 'DISCOUNT-50%');
define("OPENCART_ONLINE_ORDER_DISCOUNT60", 'DISCOUNT-60%');

define("OPENCART_PROMOTION_DISCOUNT_CODE", 'PROMOTION-DISCOUNT');
define("OPENCART_VIP_ONLINE_CODE", 'VIP-ONLINE-10%');
define("OPENCART_VIP_SILVER_CODE", 'VIP-SILVER-15%');
define("OPENCART_VIP_GOLD_CODE", 'VIP-GOLD-30%');
define("OPENCART_VIP_ELITE_CODE", 'VIP-ELITE-50%');
define("OPENCART_VIP_ELITE_CODE", 'VIP-PLATINUM-100%');
define("OPENCART_CUSTOMER_REFUND_CODE", 'CUSTOMER-REFUND');
define("OPENCART_WHOLESALE_DISCOUNT", 'WHOLESALE-DISCOUNT');

define("OPENCART_DEFAULT_PAYMENT_SYSTEM", 'PayPal');
define("OPENCART_DOKU_PAYMENT_SYSTEM", 'Doku');


define("OPENCART_ORDER_STATUS_PENDING", 1);
define("OPENCART_ORDER_STATUS_PROCESSING", 2);
define("OPENCART_ORDER_STATUS_SHIPPED", 3);
define("OPENCART_ORDER_STATUS_COMPLETE", 5);
define("OPENCART_ORDER_STATUS_CANCELLED", 7);
define("OPENCART_ORDER_STATUS_EXPIRED", 14);


/* HARD CODED PATHS */
define("ABSOLUTE_PATH_OPENCART_IMAGES", '/var/www/vhosts/kapal-laut.com/httpdocs/image/data/KL/part_pics/');
define("ABSOLUTE_PATH_WEBERP", '/var/www/vhosts/kapal-laut.com/ptadu.com/weberp/');
define("ABSOLUTE_PATH_WEBERP_TEST", '/var/www/vhosts/kapal-laut.com/ptadu.com/TEST/weberp/');

//define("PATH_OPENCART_IMAGES", 'data/KL/part_pics/');
define("PATH_OPENCART_IMAGES", 'catalog/KL/part_pics/');
define("PATH_OPENCART_BASE", 'http://www.kapal-laut.com');
define("ROUTE_TO_PRODUCT", 'index.php?route=product/product&');

// META DATA
define("META_STORE_NAME_KL", "Kapal-Laut Jewellery");
define("META_STORE_NAME_BL", "Blink by Kapal-Laut");
define("META_STORE_NAME_OU", "Outlet by Kapal-Laut");


///////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////
// TOKOPEDIA
///////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////

define("TOKOPEDIA_BANK_GL_ACCOUNT", '111121121PT');
define("TOKOPEDIA_COMMISSION_GL_ACCOUNT", '700211321PT');
define("TOKOPEDIA_COMMISSION_PERCENT", 1.00);
define("TOKOPEDIA_COMMISSION_FREE_SHIPPING_PER_ITEM_PERCENT", 2.50);
define("TOKOPEDIA_COMMISSION_FREE_SHIPPING_PER_ITEM_MAXIMUM", 10000);


///////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////
// SHOPEE
///////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////
define("SHOPEE_BANK_GL_ACCOUNT", '111121122PT');
define("SHOPEE_COMMISSION_GL_ACCOUNT", '700211322PT');
define("SHOPEE_COMMISSION_PERCENT", 1.50);



?>