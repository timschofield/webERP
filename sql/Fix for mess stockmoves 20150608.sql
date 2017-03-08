// moves affected from 

SELECT * 
FROM stockmoves
WHERE trandate = "0000-00-00";

UPDATE stockmoves
	SET loccode = CONCAT("TOK",SUBSTRING(userid,5,2))
	WHERE trandate = "0000-00-00"
	AND loccode != "KANTO";

UPDATE stockmoves
	SET reference = CONCAT("To ",SUBSTRING(userid,5,2))
	WHERE trandate = "0000-00-00"
	AND loccode = "KANTO";
	
UPDATE stockmoves
	SET newqoh = (SELECT locstock.quantity
				FROM locstock
				WHERE locstock.stockid = stockmoves.stockid
					AND locstock.loccode = stockmoves.loccode) + qty
	WHERE trandate = "0000-00-00"
	AND loccode != "KANTO";

UPDATE stockmoves
	SET reference = CONCAT("FIXED TRANSFER ISSUE: ",reference)
	WHERE trandate = "0000-00-00";
	
UPDATE stockmoves
	SET stkmoveno = stkmoveno + 10000
	WHERE trandate = "0000-00-00";

	
	FIXED TRANSFER ISSUE: FIXED ISSUE: To OB
SELECT * 
FROM stockmoves
WHERE SUBSTRING(reference,1,6) = "FIXED" 

UPDATE stockmoves 
SET reference = CONCAT('FIXED TRANSFER ISSUE: ',SUBSTRING(reference,35))
WHERE SUBSTRING(reference,1,6) = "FIXED";
	
	
UPDATE stockmoves
	SET trandate = "2015-06-08"
	WHERE trandate = "0000-00-00";
	
ALTER TABLE stockmoves AUTO_INCREMENT=1300000;