UPDATE locstock
SET reorderlevel = 5
WHERE loccode = "TOKAR"
	AND reorderlevel > 5
	AND stockid IN (SELECT stockid
					FROM stockmaster
					WHERE categoryid LIKE 'DISC%')
					
					
					
SELECT *
FROM locstock
WHERE loccode LIKE "TOK%"
	AND stockid IN (
					SELECT stockid
					FROM stockmaster
					WHERE categoryid = "DISCOU"
					)
ORDER BY reorderlevel DESC