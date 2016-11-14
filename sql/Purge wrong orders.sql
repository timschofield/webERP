DELETE FROM oc_order 
WHERE order_id = 4
OR order_id = 5
OR order_id = 6
OR order_id = 7
OR order_id = 10
OR order_id = 11
;

DELETE FROM oc_order_product
WHERE order_id = 4
OR order_id = 5
OR order_id = 6
OR order_id = 7
OR order_id = 10
OR order_id = 11
;
DELETE FROM oc_order_total
WHERE order_id = 4
OR order_id = 5
OR order_id = 6
OR order_id = 7
OR order_id = 10
OR order_id = 11
;
DELETE FROM oc_paypal_order
WHERE order_id = 4
OR order_id = 5
OR order_id = 6
OR order_id = 7
OR order_id = 8
OR order_id = 10
OR order_id = 11
;
DELETE FROM oc_paypal_order_transaction
WHERE paypal_order_id = 1;

DELETE FROM oc_address
WHERE address_id = 2
OR address_id = 4;

DELETE FROM oc_customer
WHERE customer_id = 2;

DELETE FROM oc_customer_ip
WHERE customer_id = 2
OR customer_id = 4;

