SELECT stockmaster.categoryid, 
	stockcategory.categorydescription, 
	SUM(locstock.quantity) AS realstock, 
	(SELECT COUNT(DISTINCT(l2.stockid)) 
		FROM locstock AS l2, 
			stockmaster as m2 
		WHERE m2.stockid = l2.stockid 
			AND m2.categoryid = stockcategory.categoryid 
			AND m2.categoryid NOT IN ('SHDISP', 'SHCONS', 'SHPACK') 
			AND l2.quantity != 0) AS realmodels, 
	(SELECT COUNT(DISTINCT(SUBSTRING(l2.stockid,1,6))) 
		FROM locstock AS l2, 
			stockmaster as m2 
		WHERE m2.stockid = l2.stockid 
			AND m2.categoryid = stockcategory.categoryid 
			AND m2.categoryid NOT IN ('SHDISP', 'SHCONS', 'SHPACK') 
			AND l2.quantity != 0) AS realfamilies
FROM locstock, 
	locations, 
	stockmaster, 
	stockcategory 
WHERE locstock.loccode = locations.loccode 
	AND stockmaster.stockid = locstock.stockid 
	AND stockmaster.categoryid = stockcategory.categoryid 
	AND stockcategory.stocktype = 'F' 
	AND stockmaster.categoryid NOT IN ('SHDISP', 'SHCONS', 'SHPACK') 
GROUP BY stockmaster.categoryid 
ORDER BY stockcategory.categorydescription