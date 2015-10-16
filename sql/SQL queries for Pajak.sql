/* MOVE Account compensation to HPP */
UPDATE gltrans SET account "510010000PT" WHERE account = "510010050" AND trandate >= "2015-01-01";

/* List of GL transactions in a period of time */

SELECT accountgroups.groupname AS 'Group',
	gltrans.account AS 'Account code', 
	chartmasterPT.accountname AS 'Account name', 
	gltrans.trandate AS 'Date', 
	ROUND(gltrans.amount,0) AS 'Amount', 
	gltrans.narrative AS 'Description'
FROM gltrans, 
	chartmasterPT, 
	accountgroups
WHERE gltrans.account = chartmasterPT.accountcode
	AND chartmasterPT.group_ = accountgroups.groupname
	AND (accountgroups.pandl = 1)
	AND trandate >= "2014-01-01"
	AND trandate <= "2014-08-31"
ORDER BY accountgroups.groupname ASC,
	gltrans.account ASC, 
	gltrans.trandate ASC;



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