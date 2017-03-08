SELECT  pcashdetails.codeexpense,
		pctabs.currency,
		SUM(CASE WHEN (date >= '2014-11-01' AND date <= '2014-11-31') THEN -amount ELSE 0 END) AS prd0,
		SUM(CASE WHEN (date >= '2014-10-01' AND date <= '2014-10-31') THEN -amount ELSE 0 END) AS prd1,
		SUM(CASE WHEN (date >= '2014-09-01' AND date <= '2014-09-31') THEN -amount ELSE 0 END) AS prd2,
		SUM(CASE WHEN (date >= '2014-08-01' AND date <= '2014-08-31') THEN -amount ELSE 0 END) AS prd3,
		SUM(CASE WHEN (date >= '2014-07-01' AND date <= '2014-07-31') THEN -amount ELSE 0 END) AS prd4,
		SUM(CASE WHEN (date >= '2014-06-01' AND date <= '2014-06-31') THEN -amount ELSE 0 END) AS prd5
FROM pcashdetails, pcexpenses, pctabs
WHERE pcashdetails.codeexpense = pcexpenses.codeexpense
	AND pcashdetails.tabcode = pctabs.tabcode
GROUP BY pcashdetails.codeexpense, pctabs.currency
