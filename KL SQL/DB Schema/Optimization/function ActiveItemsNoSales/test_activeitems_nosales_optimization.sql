-- =====================================================================================
-- TEST SCRIPT FOR ActiveItemsNoSales OPTIMIZATION
-- =====================================================================================
-- This script provides testing queries to validate the optimization of the 
-- ActiveItemsNoSales function and compare performance before/after changes.
-- =====================================================================================

-- Step 1: Enable query profiling for performance measurement
SET profiling = 1;
SET profiling_history_size = 10;

-- Step 2: Test the original query structure (for comparison)
-- Note: This is the original query logic reconstructed for testing
SELECT 'TESTING ORIGINAL QUERY STRUCTURE' AS test_phase;

SELECT 	COUNT(*) as original_result_count
FROM 	stockmaster, stockcategory, klsalesperformance
WHERE 	stockmaster.stockid = klsalesperformance.stockid
		AND stockmaster.categoryid = stockcategory.categoryid
		AND stockmaster.discontinued = 0 
		AND stockmaster.klchangingprice = 0
		AND stockmaster.klmovingdiscount20 = 0
		AND stockmaster.klmovingdiscount50 = 0
		AND stockmaster.klmovingdiscount80 = 0
		AND stockmaster.lastcategoryupdate <= DATE_SUB(NOW(), INTERVAL 30 DAY)
		AND stockcategory.stocktype = 'F'
		AND NOT EXISTS (SELECT * 
						FROM 	salesorderdetails, salesorders
						WHERE 	stockmaster.stockid = salesorderdetails.stkcode
								AND (salesorderdetails.orderno = salesorders.orderno)
								AND salesorderdetails.actualdispatchdate > DATE_SUB(NOW(), INTERVAL 30 DAY))
		AND (IFNULL((SELECT SUM(woitems.qtyreqd -woitems.qtyrecd) 
				FROM woitems, workorders
				WHERE woitems.stockid = stockmaster.stockid
					AND woitems.wo = workorders.wo
					AND workorders.closed = 0) ,0) = 0 )
		AND NOT EXISTS (SELECT * 
						FROM 	stockmoves
						WHERE 	stockmoves.stockid = stockmaster.stockid
								AND stockmoves.trandate >= DATE_SUB(NOW(), INTERVAL 30 DAY))
		AND EXISTS (SELECT * 
					FROM 	stockmoves
					WHERE 	stockmoves.stockid = stockmaster.stockid
							AND stockmoves.trandate < DATE_SUB(NOW(), INTERVAL 30 DAY)
							AND stockmoves.qty > 0) 
		AND NOT EXISTS (SELECT * 
						FROM 	purchorderdetails
						WHERE 	purchorderdetails.itemcode = stockmaster.stockid
								AND purchorderdetails.completed = 0);

-- Step 3: Test the optimized query structure
SELECT 'TESTING OPTIMIZED QUERY STRUCTURE' AS test_phase;

SELECT COUNT(*) as optimized_result_count
FROM stockmaster sm
INNER JOIN stockcategory sc ON sm.categoryid = sc.categoryid
INNER JOIN klsalesperformance ksp ON sm.stockid = ksp.stockid

-- Pre-aggregate locstock quantities to avoid correlated subquery
LEFT JOIN (
	SELECT stockid, SUM(quantity) AS quantity
	FROM locstock
	GROUP BY stockid
) ls_sum ON sm.stockid = ls_sum.stockid

-- LEFT JOIN to check for recent sales (replaces NOT EXISTS)
LEFT JOIN (
	SELECT DISTINCT sod.stkcode
	FROM salesorderdetails sod
	INNER JOIN salesorders so ON sod.orderno = so.orderno
	WHERE sod.actualdispatchdate > DATE_SUB(NOW(), INTERVAL 30 DAY)
) recent_sales ON sm.stockid = recent_sales.stkcode

-- LEFT JOIN to check for active work orders (replaces complex subquery)
LEFT JOIN (
	SELECT wi.stockid, SUM(wi.qtyreqd - wi.qtyrecd) AS pending_qty
	FROM woitems wi
	INNER JOIN workorders wo ON wi.wo = wo.wo
	WHERE wo.closed = 0
	GROUP BY wi.stockid
	HAVING pending_qty > 0
) active_wo ON sm.stockid = active_wo.stockid

-- LEFT JOIN to check for recent stock movements (replaces NOT EXISTS)
LEFT JOIN (
	SELECT DISTINCT stockid
	FROM stockmoves
	WHERE trandate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
) recent_moves ON sm.stockid = recent_moves.stockid

-- LEFT JOIN to check for historical stock movements (replaces EXISTS)
LEFT JOIN (
	SELECT DISTINCT stockid
	FROM stockmoves
	WHERE trandate < DATE_SUB(NOW(), INTERVAL 30 DAY)
	  AND qty > 0
) historical_moves ON sm.stockid = historical_moves.stockid

-- LEFT JOIN to check for active purchase orders (replaces NOT EXISTS)
LEFT JOIN (
	SELECT DISTINCT itemcode
	FROM purchorderdetails
	WHERE completed = 0
) active_po ON sm.stockid = active_po.itemcode

WHERE sm.discontinued = 0 
		AND sm.klchangingprice = 0
		AND sm.klmovingdiscount20 = 0
		AND sm.klmovingdiscount50 = 0
		AND sm.klmovingdiscount80 = 0
		AND sm.lastcategoryupdate <= DATE_SUB(NOW(), INTERVAL 30 DAY)
		AND sc.stocktype = 'F'
		AND recent_sales.stkcode IS NULL
		AND active_wo.stockid IS NULL
		AND recent_moves.stockid IS NULL
		AND historical_moves.stockid IS NOT NULL
		AND active_po.itemcode IS NULL;

-- Step 4: Show query execution profiles
SELECT 'PERFORMANCE COMPARISON' AS test_phase;
SHOW PROFILES;

-- Step 5: Analyze query execution plans
SELECT 'ANALYZING EXECUTION PLANS' AS test_phase;

-- Explain the optimized query
EXPLAIN FORMAT=JSON
SELECT sm.stockid,
		sm.description,
		sm.categoryid,
		sm.lastcategoryupdate,
		sm.units, 
		COALESCE(ls_sum.quantity, 0) AS quantity,
		ksp.topsales30,
		ksp.topsales60,
		ksp.topsales90
FROM stockmaster sm
INNER JOIN stockcategory sc ON sm.categoryid = sc.categoryid
INNER JOIN klsalesperformance ksp ON sm.stockid = ksp.stockid

LEFT JOIN (
	SELECT stockid, SUM(quantity) AS quantity
	FROM locstock
	GROUP BY stockid
) ls_sum ON sm.stockid = ls_sum.stockid

LEFT JOIN (
	SELECT DISTINCT sod.stkcode
	FROM salesorderdetails sod
	INNER JOIN salesorders so ON sod.orderno = so.orderno
	WHERE sod.actualdispatchdate > DATE_SUB(NOW(), INTERVAL 30 DAY)
) recent_sales ON sm.stockid = recent_sales.stkcode

LEFT JOIN (
	SELECT wi.stockid, SUM(wi.qtyreqd - wi.qtyrecd) AS pending_qty
	FROM woitems wi
	INNER JOIN workorders wo ON wi.wo = wo.wo
	WHERE wo.closed = 0
	GROUP BY wi.stockid
	HAVING pending_qty > 0
) active_wo ON sm.stockid = active_wo.stockid

LEFT JOIN (
	SELECT DISTINCT stockid
	FROM stockmoves
	WHERE trandate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
) recent_moves ON sm.stockid = recent_moves.stockid

LEFT JOIN (
	SELECT DISTINCT stockid
	FROM stockmoves
	WHERE trandate < DATE_SUB(NOW(), INTERVAL 30 DAY)
	  AND qty > 0
) historical_moves ON sm.stockid = historical_moves.stockid

LEFT JOIN (
	SELECT DISTINCT itemcode
	FROM purchorderdetails
	WHERE completed = 0
) active_po ON sm.stockid = active_po.itemcode

WHERE sm.discontinued = 0 
		AND sm.klchangingprice = 0
		AND sm.klmovingdiscount20 = 0
		AND sm.klmovingdiscount50 = 0
		AND sm.klmovingdiscount80 = 0
		AND sm.lastcategoryupdate <= DATE_SUB(NOW(), INTERVAL 30 DAY)
		AND sc.stocktype = 'F'
		AND recent_sales.stkcode IS NULL
		AND active_wo.stockid IS NULL
		AND recent_moves.stockid IS NULL
		AND historical_moves.stockid IS NOT NULL
		AND active_po.itemcode IS NULL
ORDER BY sm.stockid
LIMIT 10;

-- Step 6: Check index usage
SELECT 'CHECKING INDEX USAGE' AS test_phase;

-- Show indexes on key tables
SHOW INDEX FROM stockmaster WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM salesorderdetails WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM stockmoves WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM purchorderdetails WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM workorders WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM woitems WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM locstock WHERE Key_name LIKE 'idx_%';

-- Step 7: Sample data validation
SELECT 'SAMPLE DATA VALIDATION' AS test_phase;

-- Show sample results from optimized query
SELECT sm.stockid,
		sm.description,
		sm.categoryid,
		COALESCE(ls_sum.quantity, 0) AS quantity,
		ksp.topsales30
FROM stockmaster sm
INNER JOIN stockcategory sc ON sm.categoryid = sc.categoryid
INNER JOIN klsalesperformance ksp ON sm.stockid = ksp.stockid

LEFT JOIN (
	SELECT stockid, SUM(quantity) AS quantity
	FROM locstock
	GROUP BY stockid
) ls_sum ON sm.stockid = ls_sum.stockid

LEFT JOIN (
	SELECT DISTINCT sod.stkcode
	FROM salesorderdetails sod
	INNER JOIN salesorders so ON sod.orderno = so.orderno
	WHERE sod.actualdispatchdate > DATE_SUB(NOW(), INTERVAL 30 DAY)
) recent_sales ON sm.stockid = recent_sales.stkcode

LEFT JOIN (
	SELECT wi.stockid, SUM(wi.qtyreqd - wi.qtyrecd) AS pending_qty
	FROM woitems wi
	INNER JOIN workorders wo ON wi.wo = wo.wo
	WHERE wo.closed = 0
	GROUP BY wi.stockid
	HAVING pending_qty > 0
) active_wo ON sm.stockid = active_wo.stockid

LEFT JOIN (
	SELECT DISTINCT stockid
	FROM stockmoves
	WHERE trandate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
) recent_moves ON sm.stockid = recent_moves.stockid

LEFT JOIN (
	SELECT DISTINCT stockid
	FROM stockmoves
	WHERE trandate < DATE_SUB(NOW(), INTERVAL 30 DAY)
	  AND qty > 0
) historical_moves ON sm.stockid = historical_moves.stockid

LEFT JOIN (
	SELECT DISTINCT itemcode
	FROM purchorderdetails
	WHERE completed = 0
) active_po ON sm.stockid = active_po.itemcode

WHERE sm.discontinued = 0 
		AND sm.klchangingprice = 0
		AND sm.klmovingdiscount20 = 0
		AND sm.klmovingdiscount50 = 0
		AND sm.klmovingdiscount80 = 0
		AND sm.lastcategoryupdate <= DATE_SUB(NOW(), INTERVAL 30 DAY)
		AND sc.stocktype = 'F'
		AND recent_sales.stkcode IS NULL
		AND active_wo.stockid IS NULL
		AND recent_moves.stockid IS NULL
		AND historical_moves.stockid IS NOT NULL
		AND active_po.itemcode IS NULL
ORDER BY sm.stockid
LIMIT 5;

-- Step 8: Performance summary
SELECT 'TEST COMPLETED - CHECK PROFILES ABOVE FOR PERFORMANCE COMPARISON' AS summary;

-- Disable profiling
SET profiling = 0;

-- =====================================================================================
-- INSTRUCTIONS FOR RUNNING THIS TEST:
-- =====================================================================================
-- 1. Ensure the optimization indexes have been created first
-- 2. Run this script in your MySQL client
-- 3. Compare the execution times in the SHOW PROFILES output
-- 4. Verify that both queries return the same result count
-- 5. Check that the optimized query uses the new indexes
-- =====================================================================================