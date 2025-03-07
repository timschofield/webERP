SELECT locstock.stockid,
	locstock.quantity
FROM locstock
WHERE locstock.quantity > 0
	AND locstock.loccode = "KANTO"
	AND locstock.date_updated  < "2025-02-01"


SELECT locstock.stockid,
	locstock.quantity
FROM locstock
WHERE locstock.quantity > 0
	AND locstock.loccode = "KANTO"
	AND EXISTS (SELECT *
				FROM stockmoves
				WHERE locstock.stockid = stockmoves.stockid
					AND locstock.loccode = stockmoves.loccode
					AND stockmoves.trandate >= "2025-02-01"
					AND stockmoves.qty > 0
				)
	AND NOT EXISTS (SELECT *
					FROM stockmoves
					WHERE locstock.stockid = stockmoves.stockid
						AND locstock.loccode = stockmoves.loccode
					AND stockmoves.trandate >= "2025-02-01"
						AND stockmoves.qty < 0
					)

SELECT locstock.stockid,
	locstock.quantity
FROM locstock
INNER JOIN stockmoves
	ON locstock.stockid = stockmoves.stockid
		AND locstock.loccode = stockmoves.loccode
WHERE locstock.quantity > 0
	AND locstock.loccode = "KANTO"
	AND stockmoves.trandate >= "2025-02-01"
	AND EXISTS (SELECT *
				FROM stockmoves
				WHERE locstock.stockid = stockmoves.stockid
					AND locstock.loccode = stockmoves.loccode
					AND stockmoves.trandate >= "2025-02-01"
					AND stockmoves.qty > 0
				)
	AND NOT EXISTS (SELECT *
					FROM stockmoves
					WHERE locstock.stockid = stockmoves.stockid
						AND locstock.loccode = stockmoves.loccode
					AND stockmoves.trandate >= "2025-02-01"
						AND stockmoves.qty < 0
					)

