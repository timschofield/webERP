SELECT DISTINCT(purchorders.supplierno), 
	ROUND(SUM(purchorderdetails.`qtyinvoiced` * purchorderdetails.`unitprice` / purchorders.rate)) 
FROM `purchorderdetails`, 
	purchorders 
WHERE purchorderdetails.`orderno` = purchorders.`orderno` 
	AND purchorders.orddate >= "2022-05-01" 
	AND purchorders.orddate <= "2023-04-30" 
GROUP BY purchorders.supplierno 
ORDER BY `ROUND(SUM(purchorderdetails.``qtyinvoiced`` * purchorderdetails.``unitprice`` / purchorders.rate))` DESC