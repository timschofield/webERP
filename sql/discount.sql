UPDATE locstock
SET reorderlevel = 0
WHERE loccode = "TOKSA"
	AND stockid IN (
					SELECT stockid
					FROM stockmaster
					WHERE categoryid = "DISCOU"
					)
					
					
					
SELECT *
FROM locstock
WHERE loccode LIKE "TOK%"
	AND stockid IN (
					SELECT stockid
					FROM stockmaster
					WHERE categoryid = "DISCOU"
					)
ORDER BY reorderlevel DESC