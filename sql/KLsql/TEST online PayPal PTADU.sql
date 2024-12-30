UPDATE test_online_shop.`oc_order` SET order_status_id = "2" WHERE `date_added` >= "2023-03-31";
UPDATE test_erp.`config` SET confvalue = "2023-03-31 14:29:50" WHERE confname = "OpenCartToWeberp_LastRun";
TRUNCATE test_erp.salesorders;
TRUNCATE test_erp.salesorderdetails;
TRUNCATE test_erp.chartdetails;
TRUNCATE test_erp.gltrans;

