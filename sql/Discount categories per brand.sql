/* UPDATE `locstock` SET quantity =  0 WHERE stockid = "ALNE33";
UPDATE `locstock` SET quantity = 15 WHERE stockid = "ALNE33" AND loccode = "KANTO";

UPDATE `locstock` SET quantity =  0 WHERE stockid = "YSPU19";
UPDATE `locstock` SET quantity = 15 WHERE stockid = "YSPU19" AND loccode = "KANTO";

UPDATE `locstock` SET quantity =  0 WHERE stockid = "ANAR02";
UPDATE `locstock` SET quantity = 15 WHERE stockid = "ANAR02" AND loccode = "KANTO";

UPDATE `locstock` SET quantity =  0 WHERE stockid = "HMPU07";
UPDATE `locstock` SET quantity = 15 WHERE stockid = "HMPU07" AND loccode = "KANTO";

SELECT * 
FROM stockmaster, salescatprod
WHERE stockmaster.stockid = salescatprod.stockid
AND (stockmaster.categoryid = 'DISC2A'
	OR stockmaster.categoryid = 'DISC5A'
	OR stockmaster.categoryid = 'DISC8A')
AND salescatprod.manufacturers_id = 2
AND stockmaster.discontinued = 0
ORDER BY stockmaster.stockid
*/

UPDATE stockcategory SET categorydescription = "40-Disc 20 KL" WHERE categoryid = "DISC2A";
UPDATE stockcategory SET categorydescription = "50-Disc 50 KL" WHERE categoryid = "DISC5A";
UPDATE stockcategory SET categorydescription = "60-Disc 80 KL" WHERE categoryid = "DISC8A";


UPDATE stockmaster, salescatprod
SET stockmaster. categoryid = 'DISC2B'
WHERE stockmaster.stockid = salescatprod.stockid
	AND stockmaster.categoryid = 'DISC2A'
	AND salescatprod.manufacturers_id = 2;
	
UPDATE stockmaster, salescatprod
SET stockmaster. categoryid = 'DISC5B'
WHERE stockmaster.stockid = salescatprod.stockid
	AND stockmaster.categoryid = 'DISC5A'
	AND salescatprod.manufacturers_id = 2;
	
UPDATE stockmaster, salescatprod
SET stockmaster. categoryid = 'DISC8B'
WHERE stockmaster.stockid = salescatprod.stockid
	AND stockmaster.categoryid = 'DISC8A'
	AND salescatprod.manufacturers_id = 2;
	
	