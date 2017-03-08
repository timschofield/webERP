SELECT salesorders.orddate,
	salesorders.fromstkloc,
	salesorders.salesperson,
	klretailcustomers.orderno, 
	klretailcustomers.firstname, 
	klretailcustomers.lastname, 
	klretailcustomers.country, 
	klretailcustomers.date_of_birth, 
	klretailcustomers.email, 
	klretailcustomers.sex
FROM klretailcustomers, salesorders
WHERE salesorders.orderno = klretailcustomers.orderno
ORDER BY salesorders.salesperson,
klretailcustomers.orderno

SELECT salesorders.salesperson,
	(SELECT COUNT(*)
		FROM salesorders
		WHERE salesorders.orddate > '2014-10-01'
			AND salesorders.salesman = salesman.salesmancode) AS totalorders,
	count(klretailcustomers.orderno) AS harvested
	sum(case klretailcustomers.firstname WHEN '' then 0 else 1 END) AS firstnames, 
	sum(case klretailcustomers.lastname WHEN '' then 0 else 1 END) AS lastnames, 
	sum(case klretailcustomers.country WHEN '0' then 0 else 1 END) AS countries, 
	sum(case klretailcustomers.date_of_birth WHEN '0000-00-00' then 0 else 1 END) AS date_of_births, 
	sum(case klretailcustomers.email WHEN '' then 0 else 1 END) AS emails, 
	sum(case klretailcustomers.sex WHEN '' then 0 else 1 END) AS sexs
FROM klretailcustomers, salesorders, salesman
WHERE salesorders.orderno = klretailcustomers.orderno
	AND salesman.salesmancode = salesorders.salesperson
	AND salesorders.orddate > '2014-10-01'
GROUP BY salesorders.salesperson
ORDER BY salesorders.salesperson
