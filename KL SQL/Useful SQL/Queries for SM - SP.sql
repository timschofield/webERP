SELECT firstname,
		lastname,
		country,
		date_of_birth,
		email,
		sex,
		klretailcustomers.date_added AS order_date,
		salesorders.orderno AS order_number,
		(klpaidcash + klpaidcreditcard + klreturnedgoods + klvouchers) AS amount_paid
FROM klretailcustomers, salesorders
WHERE klretailcustomers.orderno = salesorders.orderno
ORDER BY klretailcustomers.orderno DESC


SELECT salesorders.debtorno, 
		salesorders.orddate,
		salesorders.orderno,
		salesorderdetails.stkcode,
		salesorderdetails.qtyinvoiced,
		salesorderdetails.unitprice
FROM salesorders, salesorderdetails
WHERE salesorders.orderno = salesorderdetails.orderno
	AND (salesorders.debtorno = "TOKOPEDIA" 
		OR salesorders.debtorno = "SHOPEE" 
		OR salesorders.debtorno = "WEB-KL-IDR")
	AND salesorderdetails.unitprice != 0
ORDER BY salesorders.orderno DESC

SELECT salesorders.debtorno, 
		salesorders.orddate,
		salesorders.orderno,
		salesorders.deliverto,
		salesorders.deladd1,
		salesorders.deladd2,
		salesorders.deladd3,
		salesorders.deladd4,
		salesorders.deladd5,
		salesorders.deladd6,
		SUM(salesorderdetails.qtyinvoiced*salesorderdetails.unitprice) AS amount_order
FROM salesorders, salesorderdetails
WHERE salesorders.orderno = salesorderdetails.orderno
	AND (salesorders.debtorno = "TOKOPEDIA" 
		OR salesorders.debtorno = "SHOPEE" 
		OR salesorders.debtorno = "WEB-KL-IDR")
	AND salesorderdetails.unitprice != 0
GROUP BY salesorders.orderno 
ORDER BY salesorders.orderno DESC
