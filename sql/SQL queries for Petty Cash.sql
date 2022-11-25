SELECT pcashdetails.codeexpense, 
	SUM(pcashdetails.amount) AS total
FROM pcashdetails,
	pctabs
WHERE  pcashdetails.tabcode = pctabs.tabcode
	AND pctabs.typetabcode = "COURIER"
	AND pcashdetails.date >= "2022-01-01"
GROUP BY pcashdetails.codeexpense
ORDER BY total

SELECT pcashdetails.tabcode,
	pcashdetails.date,
	pcashdetails.amount,
	pcashdetails.notes,
	pcashdetails.receipt
FROM pcashdetails,
	pctabs
WHERE  pcashdetails.tabcode = pctabs.tabcode
	AND pctabs.typetabcode = "COURIER"
	AND pcashdetails.codeexpense = "MAINT-KANTOR-ADU"
	AND pcashdetails.date >= "2022-01-01"
ORDER BY pcashdetails.amount

