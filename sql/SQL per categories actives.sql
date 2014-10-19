SELECT salescatid,
	parentcatid,
	salescatname,
	active,
	(SELECT COUNT(locstock.quantity)
		FROM salescatprod,locstock
		WHERE salescat.salescatid = salescatprod.salescatid
			AND salescatprod.stockid = locstock.stockid
			AND locstock.loccode IN ('KANTO','TOK66','TOKSA','TOKKS','TOKBW','TOKJC','TOKUB','TOKMF','TOKSE','TOKSU','TOKPU')
	) as qoh
FROM salescat
WHERE active = 1
	AND parentcatid != 0
ORDER BY salescatid

SELECT stockid, COUNT(locstock.quantity)
	FROM salescatprod,locstock
	WHERE salescat.salescatid = salescatprod.salescatid
		AND salescatprod.stockid = locstock.stockid
		AND locstock.loccode IN ('KANTO','TOK66','TOKSA','TOKKS','TOKBW','TOKJC','TOKUB','TOKMF','TOKSE','TOKSU','TOKPU')

SELECT salescatid, stockid
FROM salescatprod
WHERE salescatid = "49"

SELECT COUNT(locstock.quantity)
		FROM locstock
		WHERE locstock.loccode IN ('KANTO','TOK66','TOKSA','TOKKS','TOKBW','TOKJC','TOKUB','TOKMF','TOKSE','TOKSU','TOKPU')
			AND locstock.stockid = "ALCL05-GR" 
			
SELECT stockmaster.stockid,
			   stockmaster.description,
			   stockmaster.grossweight,
			   stockmaster.volume,
			   stockmaster.longdescription,	
			   stockmaster.categoryid	
		FROM stockmaster, stockcategory
		WHERE stockmaster.categoryid = stockcategory.categoryid
			AND stockcategory.stocktype = 'F'
			AND stockmaster.categoryid IN ('BKACCE','BKFAJW','CHACCE','BKSILV','CHFAJW','CHSILV','KLPRJW','ZXTEST','ZYDISC')
			AND stockmaster.discontinued = 0
			AND (NOT EXISTS (SELECT * 
							FROM salescatprod
							WHERE stockmaster.stockid = salescatprod.stockid))
		ORDER BY stockmaster.stockid