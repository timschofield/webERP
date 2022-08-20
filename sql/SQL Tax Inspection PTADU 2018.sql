SELECT bom.parent,
	(SELECT SUM(qtyreqd)
	FROM woitems, workorders
	WHERE woitems.wo = workorders.wo
		AND bom.parent = woitems.stockid
		AND workorders.requiredby >= "2018-01-01"
		AND workorders.requiredby <= "2018-12-31"
		) AS qty_parent_2018,
	bom.component,
	stockmaster.description as compdescription,
	bom.quantity AS qty_in_bom,
	bom.quantity * (SELECT SUM(qtyreqd)
					FROM woitems, workorders
					WHERE woitems.wo = workorders.wo
						AND bom.parent = woitems.stockid
						AND workorders.requiredby >= "2018-01-01"
						AND workorders.requiredby <= "2018-12-31"
						)
	AS qty_component_2018
FROM stockmaster INNER JOIN bom
ON stockmaster.stockid=bom.component
WHERE bom.parent IN (SELECT woitems.stockid
						FROM woitems, workorders
						WHERE woitems.wo = workorders.wo
							AND workorders.requiredby >= "2018-01-01"
							AND workorders.requiredby <= "2018-12-31"
						)
ORDER BY bom.parent,
		bom.component
		
		
(SELECT * 
FROM woitems, workorders
WHERE woitems.wo = workorders.wo
	AND workorders.requiredby >= "2018-01-01"
	AND workorders.requiredby <= "2018-12-31"
	)