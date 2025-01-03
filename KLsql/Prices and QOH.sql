SELECT stockmaster.stockid,
		stockmaster.description,
		stockmaster.categoryid,
		prices.price
FROM stockmaster, prices
WHERE stockmaster.stockid = prices.stockid
	AND stockmaster.discontinued = 0
	AND prices.typeabbrev = 'RT'
	AND prices.currabrev = 'IDR'
	AND prices.startdate <= '2021-01-14' 
	AND (prices.enddate >= '2021-01-14' OR prices.enddate = '9999-12-31')
ORDER BY stockmaster.stockid

SELECT DISTINCT(prices.price), SUM(quantity)
FROM prices, stockmaster, locstock
WHERE stockmaster.stockid = prices.stockid
	AND stockmaster.stockid = locstock.stockid
	AND stockmaster.discontinued = 0
	AND stockmaster.categoryid IN ('TESTKA','STABKA','NOPOKA','TESTBA','STABBA','NOPOBA','TESTGA','STABGA','NOPOGA')
	AND prices.typeabbrev = 'RT'
	AND prices.currabrev = 'IDR'
	AND prices.startdate <= '2021-01-14' 
	AND (prices.enddate >= '2021-01-14' OR prices.enddate = '9999-12-31')
GROUP BY prices.price
ORDER BY prices.price