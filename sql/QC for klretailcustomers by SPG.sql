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
