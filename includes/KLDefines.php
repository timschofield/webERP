<?php

define("MINIMUM_SURVIVAL_CASH", 3000000000);

define("CURRENCY_CODE", 'IDR');
define("CUSTOMER_TYPE_RETAIL", '2');
define("CUSTOMER_TYPE_CONSIGNMENT", '6');
define("CUSTOMER_TYPE_WHOLESALE", '3,4,5');
define("CUSTOMER_TYPE_WEBSITE", '9');
define("CUSTOMER_TYPE_MARKETPLACE", '10');
define("RETAIL_PRICE_LIST", 'RT');
define("PPN_PERCENT", 11);

/* Defines about Purchase Orders */
define("COMPANY_NAME_FOR_PO", 'PT. ANGIN DINGIN UTARA');
define("NPWP_FOR_PO", '81.304.529.1-906.000');

/* Defines about standard Cost*/
define("STANDARD_COST_FACTOR_INDONESIA", 1.00);
define("STANDARD_COST_FACTOR_FOREIGN"  , 1.00);

/* Defines about Prices factors*/
define("MINIMUM_PRICE_FACTOR_KL",                6.30);
define("MINIMUM_PRICE_FACTOR_TOPSALES_KL",       6.50);
define("MAXIMUM_PRICE_FACTOR_BOTTOMSALES_KL",    7.00);
define("MINIMUM_PRICE_FACTOR_BLINK",             6.80);
define("MINIMUM_PRICE_FACTOR_TOPSALES_BLINK",    7.15);
define("MAXIMUM_PRICE_FACTOR_BOTTOMSALES_BLINK", 7.50);
define("MINIMUM_PRICE_FACTOR_GENERAL",           4.40);

/* Defines about prices IDR */
define("PRICE_ROUNDING_STEP01",               5000);
define("PRICE_ROUNDING_LIMIT01",            100000);
define("PRICE_ROUNDING_STEP02",              25000);
define("PRICE_ROUNDING_LIMIT02",           1000000);
define("PRICE_ROUNDING_COMMERCIAL_MODULE02",100000);
define("PRICE_ROUNDING_COMMERCIAL_STEP02",    5000);
define("PRICE_ROUNDING_STEP03",             100000);

/* Defines abot small selling prices */
define("SMALL_PRICE_CALCULATED_STEP01",  50000);
define("SMALL_PRICE_CORRECTED_STEP01",  165000);

define("SMALL_PRICE_CALCULATED_STEP02", 100000);
define("SMALL_PRICE_CORRECTED_STEP02",  175000);

define("SMALL_PRICE_CALCULATED_STEP03", 150000);
define("SMALL_PRICE_CORRECTED_STEP03",  185000);

define("SMALL_PRICE_CALCULATED_STEP04", 195000);
define("SMALL_PRICE_CORRECTED_STEP04",  195000);

/* Defines about packaging transfers */
define("TRANSFER_ROUNDING_STEP01",               5);
define("TRANSFER_ROUNDING_LIMIT01",             10);
define("TRANSFER_ROUNDING_STEP02",              10);
define("TRANSFER_ROUNDING_LIMIT02",            100);
define("TRANSFER_ROUNDING_STEP03",              50);
define("TRANSFER_ROUNDING_LIMIT03",           1000);
define("TRANSFER_ROUNDING_STEP04",             100);
define("TRANSFER_ROUNDING_PAPER_INSIDE_BOX",   100);

define("MAXIMUM_BOXES_PACKAGING_TRANSFER_TO_SHOP", 999999);

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
define("LIST_STOCK_CATEGORIES_OUTLET",     "('DISC2A','DISC5A','DISC8A')");
define("LIST_STOCK_CATEGORIES_GENERAL",    "('TESTGA','STABGA','NOPOGA')");

/* Defines about LOCATIONS*/
define("CODE_KANTOR",      "'KANTO'");
define("CODE_ONLINE_SHOP", "'TOKWS'");

define("LIST_ZONES_OF_KANTOR",   "('OFFICE')"); 
define("LIST_KANTOR",  "('KANTO')");

define("LIST_BALI_SHOPS_BY_TYPE", "('SHOPKL','SHOPBL','SHOPOU')");
define("LIST_ALL_SHOPS_BY_TYPE",  "('SHOPKL','SHOPBL','SHOPOU','ONLINE')");
define("LIST_ONLINE_SHOPS",       "('TOKWS')");

define("LIST_ITEMS_KAPAL_LAUT_PACKAGING", "('PKBX01-L','PKBX01-M','PKBX01-S','PKPB01-L','PKPB01-M','PKPB01-S','PKSB02-L','PKSB02-M','PKSB02-S','PKKS01-L1','PKKS01-L2','PKKS01-M','PKKS01-S')");
define("LIST_ITEMS_BLINK_PACKAGING",      "('PKBX02-L','PKBX02-M','PKBX02-S','PKPB03-L','PKPB03-M','PKPB03-S','PKSB04-L','PKSB04-M','PKSB04-S','PKKS02-L1','PKKS02-L2','PKKS02-M','PKKS02-S')");
define("LIST_ITEMS_OUTLET_PACKAGING",     "('PKPB02-L','PKPB02-M','PKPB02-S','PKSB03')");
define("MIN_REORDER_LEVEL_PACKAGING_ITEM_PER_SHOP", 3); 

define("LIST_KANTOR_LOCATIONS",      "('KANTO','SAMPR','SASPG','SERSU','SERVI','SERDE')");
define("LIST_SERVICE_LOCATIONS",     "('SERSU','SERSW','SERVI')");
define("LIST_SPECIAL_LOCATIONS",     "('KASPE')");
define("LIST_SAMPLE_LOCATIONS",      "('SAMPR')");
define("LIST_CONSIGNMENT_LOCATIONS", "('CSLAZ','CSZAL')");
define("LIST_PACAKING_LOCATIONS",    "('PACKA','PACKU')");
define("FORECAST_DAYS_FOR_PACKAGING_STOCK", 80);
define("FACTOR_GUDANG_PACKAGING", 1.50);


define("LIST_LOCATIONS_WITH_RL_ALWAYS_ZERO", "('KANTO','PACKA','SUPBA','SERVI','SERSU','SERSW','SERDE','SAMPR','SASPG')");

/* Defines about Performance */
define("MINIMUM_AVERAGE_SALES_TREND", 3);
define("MINIMUM_AVERAGE_SALES_COMPARED_LAST_YEAR_TREND", 5);
define("MINIMUM_BUSINESS_HISTORY_TREND", 3);

/* Defines about Retail Sales at KL Shops*/
define("LENGHT_OF_LIST_OF_CODES_RETAIL_SHOP_SALES", 5); 

/* Defines about Stock Control*/
define("MAX_ITEMS_CHANGING_PRICE_OR_MOVING_DISC", 100); 
define("MAX_ITEMS_CHANGING_PRICE", 50); 
define("MAX_ITEMS_MOVING_DISC20",  20); 
define("MAX_ITEMS_MOVING_DISC50",  20); 
define("MAX_ITEMS_MOVING_DISC80",  20); 

define("STOCK_MOVEMENT_DAYS_FOR_SPG", 60); 
define("TRANSFER_LIST_DAYS_FOR_SPG", 7); 

define("PAYMENT_BY_CASH", 2); 
define("PAYMENT_BY_CREDITCARD", 3); 

define("LIST_SALES_AREAS_PTADU", "('ORA','OWA','OWW','RAC','RAR','RAZ')");

define("ACCOUNT_PPN_ADU", "611012030AD"); // GL account for PPN PT.ADU

define("ACCOUNT_HUTANG_PPH21_PTBB",  "611012000BB"); // GL account for retention of PPH21 PTBB in Petty cash
define("ACCOUNT_HUTANG_PPH21_PTADU", "611012000AD"); // GL account for retention of PPH21 PTADU in Petty cash
define("ACCOUNT_HUTANG_PPH21_PTSMH", "611012000SM"); // GL account for retention of PPH21 PTSMH in Petty cash

define("ACCOUNT_HUTANG_PPH23_PTBB",  "611012005BB"); // GL account for retention of PPH23 PTBB in Petty cash
define("ACCOUNT_HUTANG_PPH23_PTADU", "611012005AD"); // GL account for retention of PPH23 PTADU in Petty cash
define("ACCOUNT_HUTANG_PPH23_PTSMH", "611012005SM"); // GL account for retention of PPH23 PTSMH in Petty cash

/* Defines about financial Analysis*/
define("JUTA", 1000000);
define("GL_INCOME_CC_BB",    "('410000010BB')");
define("GL_INCOME_CASH_BB",  "('410000000BB')");
define("GL_INCOME_CASH",     "('410000000')");
define("GL_INCOME_OTHERS_BB","('410000500BB','410010000BB','410010010BB')");
define("GL_INCOME_OTHERS",   "('410000500','410010000')");

define("GL_COGS_GOODS", "('510010000','510010000AD','510010000BB','510010000SM','510010050')");
define("GL_COGS_OTHERS","('510010100BB','510500010BB','510500010SM')");

/* Defines about Service Fees*/
define("RETAIL_PRICE_FOR_SERVICE_TIER_01",  350000);
define("RETAIL_PRICE_FOR_SERVICE_TIER_02", 1000000);
define("FACTOR_SC_SERVICE_BY_REPLACEMENT", 1.3);

/* Defines about WebStore */

define("ONLINE_PRICE_LIST", 'RT');

/* Defines about weight in KG*/
define('STANDARD_TALI_WEIGHT',       0.050);
define('STANDARD_BEAD_WEIGHT',       0.050);
define('STANDARD_RING_WEIGHT',       0.050);
define('STANDARD_PIERCING_WEIGHT',   0.050);
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
define('STANDARD_JEWEL_BOX_WEIGHT',  0.150); 

/* shipping dimensions in mm (webERP set up in mm) */

define('BOX_XS_LENGTH',       150); // approx for polishing cloth
define('BOX_XS_WIDTH',         75); // approx for polishing cloth
define('BOX_XS_HEIGHT',        10); // approx for polishing cloth
define('BOX_S_LENGTH',         85);
define('BOX_S_WIDTH',          85);
define('BOX_S_HEIGHT',         50);
define('BOX_M_LENGTH',        115);
define('BOX_M_WIDTH',         115);
define('BOX_M_HEIGHT',         50);
define('BOX_L_LENGTH',        235);
define('BOX_L_WIDTH',         165);
define('BOX_L_HEIGHT',         50);
define('BOX_XL_LENGTH',       300); // approx
define('BOX_XL_WIDTH',        300); // approx
define('BOX_XL_HEIGHT',       200); // approx

/* Defines about website sales categories */
define("ONLINESHOP_AVAILABLE_STOCK_CATEGORIES", "('TESTKA','TESTBA','TESTGA','STABKA','STABBA','STABGA','NOPOKA','NOPOBA','NOPOGA','DISC2A','DISC5A')");
define("ONLINESHOP_AVAILABLE_STOCK_KL_BLINK", "('TESTKA','TESTBA','TESTGA','STABKA','STABBA','STABGA','NOPOKA','NOPOBA','NOPOGA')");
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
define('KL_BROOCHES',82);
define('KL_PIERCINGS',126);
define('KL_JEWELLERY_BOXES',130);

define('BLINK_JEWELLERY',14);
define('BLINK_RINGS',35);
define('BLINK_BRACELETS',36);
define('BLINK_EARRINGS',37);
define('BLINK_PENDANTS',38);
define('BLINK_NECKLACES',49);
define('BLINK_EARCUFFS',72);
define('BLINK_BROOCHES',77);
define('BLINK_KEYHOLDERS',84);
define('BLINK_PIERCINGS',125);
define('BAGS',29);

define('GENERAL_ACCESSORIES',88);
define('GE_JEWELLERY_ROLLS',89);
define('GE_FACEMASKS',90);

define('BLINK_OUTLET',128);
define('KL_OUTLET',129);


//********************************************************************************
//********************************************************************************
// WebERP - Opencart Bridge settings
//********************************************************************************
//********************************************************************************

define("WEBERP_ONLINE_RETAIL_CUSTOMER_CODE_PREFIX",    'WEB-KL-');
define("WEBERP_ONLINE_WHOLESALE_CUSTOMER_CODE_PREFIX", 'WEB-WH-');

define("OPENCART_STORE_KAPAL_LAUT", 0);
define("OPENCART_STORE_BLINK", 4);
define("OPENCART_STORE_OUTLET", 5);
define("OPENCART_STORE_WHOLESALE", 8);

define("WEBERP_DISCOUNTS_IN_OPENCART_TABLE", 'product_special');

define("MARKETPLACE_KAPAL_LAUT_SALES_CATEGORIES", '94');
define("MARKETPLACE_BLINK_SALES_CATEGORIES", '95');

define("SALES_CATEGORIES_FOR_GOOGLE_PRODUCT_FEED", '31,32,33,34,36,37,50,38,35,43,44,45,46,47,48,49,51,52,53,54,55,56,40,41,49,42,39,67,71,72,73,74,76,80,89');

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
define("OPENCART_DEFAULT_AREA_RETAIL", 'OWB');
define("OPENCART_DEFAULT_AREA_WHOLESALE", 'OWW');
define("OPENCART_DEFAULT_CURRENCY", 'IDR');
define("OPENCART_DEFAULT_LOCATION", 'TOKWS');

define("SHIPMENT01_OPENCART_TEXT", 'EMS'); 
define("SHIPMENT01_WEBERP_CODE", 5); 
define("SHIPMENT01_POWERTRACK_CODE", 'tqhn'); 

define("SHIPMENT02_OPENCART_TEXT", 'JNE_OKE'); 
define("SHIPMENT02_WEBERP_CODE", 6); 
define("SHIPMENT02_POWERTRACK_CODE", 'nvnr'); 

define("SHIPMENT03_OPENCART_TEXT", 'JNE_YES'); 
define("SHIPMENT03_WEBERP_CODE", 7); 
define("SHIPMENT03_POWERTRACK_CODE", 'nvnr'); 

define("SHIPMENT04_OPENCART_TEXT", 'JNE_REG'); 
define("SHIPMENT04_WEBERP_CODE", 8); 
define("SHIPMENT04_POWERTRACK_CODE", 'nvnr'); 

define("SHIPMENT05_OPENCART_TEXT", 'PICKUP'); 
define("SHIPMENT05_WEBERP_CODE", 10); 
define("SHIPMENT05_POWERTRACK_CODE", ''); 

define("SHIPMENT06_OPENCART_TEXT", 'JNE_INTL'); 
define("SHIPMENT06_WEBERP_CODE", 19); 
define("SHIPMENT06_POWERTRACK_CODE", 'nvnr'); 

define("SHIPMENT07_OPENCART_TEXT", 'DHL'); 
define("SHIPMENT07_WEBERP_CODE", 2); 
define("SHIPMENT07_POWERTRACK_CODE", '9w5t'); 

define("SHIPMENT08_OPENCART_TEXT", 'WHOLESALE'); 
define("SHIPMENT08_WEBERP_CODE", 27); 
define("SHIPMENT08_POWERTRACK_CODE", ''); 

define("SHIPMENT09_OPENCART_TEXT", 'SICEPAT'); 
define("SHIPMENT09_WEBERP_CODE", 11); 
define("SHIPMENT09_POWERTRACK_CODE", 'cxjm'); 

define("SHIPMENT10_OPENCART_TEXT", 'FREE'); //so far, free shipping via JNE OKE
define("SHIPMENT10_WEBERP_CODE", 6); 
define("SHIPMENT10_POWERTRACK_CODE", 'nvnr'); 

define("SHIPMENT11_OPENCART_TEXT", 'SICEPAT'); 
define("SHIPMENT11_WEBERP_CODE", 13); 
define("SHIPMENT11_POWERTRACK_CODE", 'cxjm'); 

define("SHIPMENT12_OPENCART_TEXT", 'SICEPAT'); 
define("SHIPMENT12_WEBERP_CODE", 20); 
define("SHIPMENT12_POWERTRACK_CODE", 'cxjm'); 

define("SHIPMENT13_OPENCART_TEXT", 'FEDEX'); 
define("SHIPMENT13_WEBERP_CODE", 31); 
define("SHIPMENT13_POWERTRACK_CODE", '9egj'); 

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

define("WEBERP_ORDER_STATUS_QUOTATION", 1);
define("WEBERP_ORDER_STATUS_ORDER", 0);

/* HARD CODED PATHS */
define("ABSOLUTE_PATH_OPENCART_IMAGES", '/var/www/vhosts/kapal-laut.com/httpdocs/image/catalog/KL/part_pics/');
define("ABSOLUTE_PATH_WEBERP", '/var/www/vhosts/kapal-laut.com/ptadu.com/weberp/');
define("ABSOLUTE_PATH_WEBERP_TEST", '/var/www/vhosts/kapal-laut.com/ptadu.com/TEST/weberp/');

define("PATH_OPENCART_IMAGES", 'catalog/KL/part_pics/');
define("PATH_OPENCART_BASE", 'https://www.kapal-laut.com');
define("ROUTE_TO_PRODUCT", 'index.php?route=product/product&');
define("PATH_TO_CATALOG_IMAGES", 'https://kapal-laut.com/image/catalog/KL/part_pics/');
define("PATH_TO_CATALOG_PACKAGING_IMAGES", 'https://kapal-laut.com/image/catalog/KL/packaging_sets/');

define("OPENCART_PACKAGING_SET_IMAGE_SORT_ORDER", 9999);
define("OPENCART_PACKAGING_SET_IMAGE_PATH", 'catalog/KL/packaging_sets/');

// META DATA
define("META_STORE_NAME_KL", "Kapal-Laut Jewellery");
define("META_STORE_NAME_BL", "Blink by Kapal-Laut");
define("META_STORE_NAME_OU", "Outlet by Kapal-Laut");

///////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////
// MARKETPLACES
///////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////
define("ACI_MAXIMUM_QOH_TO_SHOW_IN_MARKETPLACES",     10); // if we have more than X then, we will show QOH=X in marketplaces to avoid unneeded updates
define("ACI_MINIMUM_QOH_TO_SHOW_ITEM_IN_MARKETPLACES", 3); // if we have less than X then we consider QOH = 0 for the marketplaces to avoid cancelled orders and bad reviews 

///////////////////////////////////////////////////////////////////////////////////////////////
// TOKOPEDIA
///////////////////////////////////////////////////////////////////////////////////////////////
define("TOKOPEDIA_PREFIX_URL", "https://www.tokopedia.com/");
define("TOKOPEDIA_BLINK_STOREID", "blinkfashionjewellery");
define("TOKOPEDIA_KAPAL_LAUT_STOREID", "kapallautjewellery");

///////////////////////////////////////////////////////////////////////////////////////////////
// SHOPEE
///////////////////////////////////////////////////////////////////////////////////////////////

define("SHOPEE_CATEGORY_RING",           'Cincin Kasual');
define("SHOPEE_CATEGORY_TOE_RING",       'Cincin Kasual');
define("SHOPEE_CATEGORY_BROOCHE",        'Bros');
define("SHOPEE_CATEGORY_PIERCING",       'Anting');
define("SHOPEE_CATEGORY_EARRING",        'Anting Plug');
define("SHOPEE_CATEGORY_EARRING_HOOK",   'Anting Hooks');
define("SHOPEE_CATEGORY_EARRING_STUD",   'Anting Stud');
define("SHOPEE_CATEGORY_EARRING_HOOP",   'Anting Hoops');
define("SHOPEE_CATEGORY_BANGLE",         'Bangle');
define("SHOPEE_CATEGORY_BRACELET_PEARL", 'Gelang Mutiara');
define("SHOPEE_CATEGORY_BRACELET",       'Gelang Etnik & Bead');
define("SHOPEE_CATEGORY_ANKLET",         'Gelang Kaki');
define("SHOPEE_CATEGORY_PENDANT",        'Liontin');
define("SHOPEE_CATEGORY_PENDANT_PEARL",  'Kalung Mutiara');
define("SHOPEE_CATEGORY_CHOKER",         'Choker');
define("SHOPEE_CATEGORY_NECKLACE_PEARL", 'Kalung Mutiara');
define("SHOPEE_CATEGORY_NECKLACE",       'Kalung Etnik');
define("SHOPEE_CATEGORY_BAG",            'Clutch');
define("SHOPEE_CATEGORY_KEYHOLDER",      'Hiasan & Gantungan Kunci');

define("SHOPEE_PREFIX_URL", "https://www.shopee.co.id/produk-i.");
define("SHOPEE_BLINK_STOREID", "303205858");
define("SHOPEE_KAPAL_LAUT_STOREID", "359256976");

///////////////////////////////////////////////////////////////////////////////////////////////
// LAZADA
///////////////////////////////////////////////////////////////////////////////////////////////
define("LAZADA_PREFIX_URL", "https://www.lazada.co.id/products/-i");

///////////////////////////////////////////////////////////////////////////////////////////////
// DANAMON
///////////////////////////////////////////////////////////////////////////////////////////////
define("DANAMON_ACCOUNT_GAJI_PTADU", '3607556887');
define("DANAMON_ACCOUNT_GAJI_PTSMH", '3670857030');
define("DANAMON_ACCOUNT_GAJI_PTBB",  '3568005502');


?>