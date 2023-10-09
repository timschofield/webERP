/* MOVE Account compensation to HPP */
UPDATE gltrans SET account "510010000PT" WHERE account = "510010050" AND trandate >= "2015-01-01";

/* List of GL transactions in a period of time */

SELECT accountgroups.groupname AS 'Group',
	gltrans.account AS 'Account code', 
	chartmasterBB.accountname AS 'Account name', 
	gltrans.trandate AS 'Date', 
	ROUND(gltrans.amount,0) AS 'Amount', 
	gltrans.narrative AS 'Description'
FROM gltrans, 
	chartmasterBB, 
	accountgroups
WHERE gltrans.account = chartmasterBB.accountcode
	AND chartmasterBB.group_ = accountgroups.groupname
	AND (accountgroups.pandl = 1)
	AND trandate >= "2014-01-01"
	AND trandate <= "2014-08-31"
ORDER BY accountgroups.groupname ASC,
	gltrans.account ASC, 
	gltrans.trandate ASC;

	
/* FOR FEL
*/

SELECT accountgroups.groupname AS 'Group',
	gltrans.account AS 'Account code', 
	chartmaster.accountname AS 'Account name', 
	gltrans.trandate AS 'Date', 
	ROUND(gltrans.amount,2) AS 'Amount', 
	gltrans.narrative AS 'Description'
FROM gltrans, 
	chartmaster, 
	accountgroups
WHERE gltrans.account = chartmaster.accountcode
	AND chartmaster.group_ = accountgroups.groupname
	AND (accountgroups.pandl = 1)
	AND trandate >= "2015-01-01"
	AND trandate <= "2015-12-31"
ORDER BY gltrans.trandate ASC,
	accountgroups.groupname ASC,
	gltrans.account ASC;

	
SELECT accountgroups.groupname AS 'Group',
	gltrans.account AS 'Account code', 
	chartmaster.accountname AS 'Account name', 
	gltrans.trandate AS 'Date', 
	ROUND(gltrans.amount,2) AS 'Amount', 
	gltrans.narrative AS 'Description'
FROM gltrans, 
	chartmaster, 
	accountgroups
WHERE gltrans.account = chartmaster.accountcode
	AND chartmaster.group_ = accountgroups.groupname
	AND gltrans.account = "1100"
	AND trandate >= "2017-01-01"
	AND trandate <= "2017-12-31"
ORDER BY gltrans.trandate ASC,
	accountgroups.groupname ASC,
	gltrans.account ASC;	
	
/* List of No Sales */

SELECT orderno as webERPcode,
	debtorno as Customer,
	orddate as SalesDate,
	customerref as YellowNumber,
	area,
	klpaidcash as Cash,
	klpaidcreditcard as CreditCard,
	klreturnedgoods as ReturnedGoods
FROM `salesorders` 
WHERE area IN ("REZ")
	AND `orddate` >= "2013-01-01"
	AND orddate <= "2013-12-31"
ORDER BY Customer, SalesDate, YellowNumber;

/* Items sold during a period 
Kartu stock OUT */

SELECT stkcode AS codeItem,
	SUM(qtyinvoiced) AS pcsSold
FROM salesorders, salesorderdetails
WHERE salesorders.orderno = salesorderdetails.orderno
	AND salesorders.area IN ("REC", "RER", "OWB", "WCS", "WHC")
	AND salesorders.orddate >= "2015-01-01"
	AND salesorders.orddate <= "2015-12-31"
GROUP BY stkcode
ORDER BY stkcode;


/* Amount paid in cash (bank notes) for expenses PT
*/

SELECT SUM(pcashdetails.amount) 
FROM pcashdetails, pctabs, pcexpenses
WHERE pcashdetails.date >= "2017-01-01"
	AND pcashdetails.date <= "2017-12-31"
	AND pcashdetails.tabcode = pctabs.tabcode
	AND pcashdetails.codeexpense = pcexpenses.codeexpense
	AND pctabs.currency = "IDR"
	AND pcashdetails.codeexpense != "ASSIGNCASH"
	AND pctabs.tabcode NOT LIKE "SALARIES%"
	AND pctabs.tabcode NOT LIKE "%DANAMON"
	AND pctabs.tabcode NOT LIKE "CC-BCA%"
	AND pcexpenses.glaccount LIKE "%PT"
	
/* Amount moved from Danamon to cash kantor
*/

SELECT SUM(gltrans.amount)
FROM gltrans
WHERE gltrans.trandate >= "2017-01-01"
	AND gltrans.trandate <= "2017-12-31"
	AND gltrans.account = "111121105PT"
	AND (gltrans.narrative LIKE "%CASH TO CASH%"
		OR gltrans.narrative LIKE "%BANK TO CASH%"
		OR gltrans.narrative LIKE "%UANG KECIL%")

SELECT SUM(gltrans.amount)
FROM gltrans
WHERE gltrans.trandate >= "2017-01-01"
	AND gltrans.trandate <= "2017-12-31"
	AND gltrans.account = '111121105PT'
	AND (gltrans.narrative LIKE '%CASH TO CASH%'
		OR gltrans.narrative LIKE '%BANK TO CASH%'
		OR gltrans.narrative LIKE '%UANG KECIL%')	
