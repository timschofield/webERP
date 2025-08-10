SELECT purchorders.orderno,
	purchorders.supplierno,
	purchorders.orddate,
	purchorderdetails.itemcode,
	(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS pending
FROM purchorders 
INNER JOIN purchorderdetails
	ON purchorders.orderno = purchorderdetails.orderno
INNER JOIN stockmaster
	ON stockmaster.stockid = purchorderdetails.itemcode
WHERE purchorderdetails.completed=0
	AND purchorders.status IN ('Authorised', 'Printed', 'Pending')
	AND stockmaster.categoryid IN ('SETBLA','TESTBA','STABBA','NOPOBA')
	
	
SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS pending
FROM purchorders 
INNER JOIN purchorderdetails
	ON purchorders.orderno = purchorderdetails.orderno
INNER JOIN stockmaster
	ON stockmaster.stockid = purchorderdetails.itemcode
WHERE purchorderdetails.completed=0
	AND purchorders.status IN ('Authorised', 'Printed', 'Pending')
	AND stockmaster.categoryid IN ('SETBLA','TESTBA','STABBA','NOPOBA')
	
SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS pending
FROM purchorders 
INNER JOIN purchorderdetails
	ON purchorders.orderno = purchorderdetails.orderno
INNER JOIN stockmaster
	ON stockmaster.stockid = purchorderdetails.itemcode
WHERE purchorderdetails.completed=0
	AND purchorders.status IN ('Authorised', 'Printed', 'Pending')
	AND stockmaster.categoryid IN ('SETKLA','TESTKA','STABKA','NOPOKA')