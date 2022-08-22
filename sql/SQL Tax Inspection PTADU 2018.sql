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
FROM stockmaster 
INNER JOIN bom ON stockmaster.stockid=bom.component
WHERE bom.parent IN (SELECT woitems.stockid
						FROM woitems, workorders
						WHERE woitems.wo = workorders.wo
							AND workorders.requiredby >= "2018-01-01"
							AND workorders.requiredby <= "2018-12-31"
						)
ORDER BY bom.parent,
		bom.component
		
		
SELECT stockmaster.stockid,
	stockmaster.description,
	purchdata.suppliers_partno,
	purchdata.suppliersuom
FROM woitems, workorders, stockmaster, purchdata
WHERE woitems.wo = workorders.wo
	AND woitems.stockid = stockmaster.stockid
	AND purchdata.stockid = stockmaster.stockid
	AND workorders.requiredby >= "2018-01-01"
	AND workorders.requiredby <= "2018-12-31"
GROUP BY stockmaster.stockid, purchdata.suppliers_partno
ORDER BY stockmaster.stockid,purchdata.suppliers_partno


SELECT bom.component,
	stockmaster.description,
	purchdata.suppliers_partno,
	purchdata.suppliersuom
FROM stockmaster 
INNER JOIN bom ON stockmaster.stockid=bom.component
INNER JOIN purchdata ON bom.component=purchdata.stockid
WHERE bom.parent IN (SELECT woitems.stockid
						FROM woitems, workorders
						WHERE woitems.wo = workorders.wo
							AND workorders.requiredby >= "2018-01-01"
							AND workorders.requiredby <= "2018-12-31"
						)
GROUP BY bom.component
ORDER BY bom.component
