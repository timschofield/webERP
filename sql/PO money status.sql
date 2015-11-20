INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('POFinancialPlanning.php', '4', '');

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

SELECT suppliers.supplierid,
	suppliers.suppname,
	suppliers.currcode,
	(SELECT SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc)
		FROM supptrans
		WHERE suppliers.supplierid = supptrans.supplierno) AS balance
FROM suppliers 
INNER JOIN purchorders 
	ON  purchorders.supplierno = suppliers.supplierid 
INNER JOIN purchorderdetails
	ON purchorders.orderno = purchorderdetails.orderno
INNER JOIN currencies
	ON suppliers.currcode=currencies.currabrev
WHERE purchorderdetails.completed=0
	AND purchorders.status IN ('Authorised', 'Printed', 'Pending')		
GROUP BY 
	suppliers.supplierid
ORDER BY suppliers.supplierid ASC


SELECT purchorders.orderno,
	purchorders.orddate,
	purchorders.deliverydate,
	purchorders.status	
	SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
FROM purchorders INNER JOIN purchorderdetails
	ON purchorders.orderno = purchorderdetails.orderno
WHERE purchorderdetails.completed=0
	AND purchorders.status IN ('Authorised', 'Printed', 'Pending')		
	AND purchorders.supplierno = 'CREATION'
GROUP BY purchorders.orderno
ORDER BY purchorders.orderno ASC