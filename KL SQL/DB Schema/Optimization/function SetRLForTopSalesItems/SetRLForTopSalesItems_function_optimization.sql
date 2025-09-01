-- =====================================================================================
-- SetRLForTopSalesItems Function Optimization Analysis
-- Original function location: includes/KLReorderLevel.php line 658
-- Date: 2025-09-01
-- =====================================================================================

-- PERFORMANCE ISSUES IDENTIFIED:
-- 1. Correlated subquery for QtyAvailable calculation (lines 716-722)
-- 2. Correlated subquery for location distribution (lines 726-733)
-- 3. Comma joins instead of explicit JOINs in main query (line 702)
-- 4. Multiple nested loops causing N+1 query problem
-- 5. Inefficient stock availability checking for each top sales item

-- =====================================================================================
-- ORIGINAL PROBLEMATIC QUERIES:
-- =====================================================================================

-- Main query (lines 698-708):
/*
SELECT stockmaster.stockid,
       stockmaster.categoryid,
       stockmaster.description,
       klsalesperformance.topsales60
FROM stockmaster, klsalesperformance
WHERE stockmaster.stockid = klsalesperformance.stockid
  AND stockmaster.discontinued = 0
  AND stockmaster.klchangingprice = 0
  AND stockmaster.categoryid IN (category_list)
ORDER BY topsales60 DESC
LIMIT start_offset, row_count;
*/

-- Correlated subquery for QtyAvailable (lines 716-722):
/*
SELECT SUM(locstock.quantity) AS QtyAvailable
FROM locstock, locations loc2
WHERE locstock.stockid = 'specific_stockid'
  AND locstock.loccode = loc2.loccode
  AND loc2.stockreadytosell = 1
*/

-- Correlated subquery for distribution locations (lines 726-733):
/*
SELECT locstock.loccode, 
       locstock.reorderlevel AS oldrl
FROM locstock,locations
WHERE locstock.stockid = 'specific_stockid'
  AND locstock.loccode = locations.loccode
  AND locations.stockreadytosell = 1
  AND locstock.reorderlevel > 0
*/

-- =====================================================================================
-- OPTIMIZED SOLUTION:
-- =====================================================================================

-- Step 1: Create a single optimized query that eliminates correlated subqueries
-- by using JOINs and aggregations

-- OPTIMIZED MAIN QUERY WITH STOCK AVAILABILITY:
SELECT 
    sm.stockid,
    sm.categoryid,
    sm.description,
    ksp.topsales60,
    COALESCE(stock_summary.total_available, 0) AS qty_available,
    COALESCE(stock_summary.locations_with_rl, 0) AS locations_count
FROM stockmaster sm
INNER JOIN klsalesperformance ksp ON sm.stockid = ksp.stockid
LEFT JOIN (
    -- Pre-aggregate stock data to eliminate correlated subqueries
    SELECT 
        ls.stockid,
        SUM(CASE WHEN loc.stockreadytosell = 1 THEN ls.quantity ELSE 0 END) AS total_available,
        COUNT(CASE WHEN loc.stockreadytosell = 1 AND ls.reorderlevel > 0 THEN 1 END) AS locations_with_rl
    FROM locstock ls
    INNER JOIN locations loc ON ls.loccode = loc.loccode
    GROUP BY ls.stockid
) stock_summary ON sm.stockid = stock_summary.stockid
WHERE sm.discontinued = 0
  AND sm.klchangingprice = 0
  AND sm.categoryid IN (category_list)
  AND stock_summary.total_available > min_stock_available
  AND stock_summary.total_available <= max_stock_available
  AND stock_summary.locations_with_rl > 0
ORDER BY ksp.topsales60 DESC
LIMIT start_offset, row_count;

-- Step 2: For the distribution phase, get all qualifying locations in one query
-- OPTIMIZED DISTRIBUTION QUERY:
SELECT 
    ls.stockid,
    ls.loccode,
    ls.reorderlevel AS oldrl
FROM locstock ls
INNER JOIN locations loc ON ls.loccode = loc.loccode
WHERE ls.stockid IN (list_of_qualifying_stockids)
  AND loc.stockreadytosell = 1
  AND ls.reorderlevel > 0
ORDER BY ls.stockid, ls.loccode;

-- =====================================================================================
-- COMPLETE OPTIMIZED FUNCTION APPROACH:
-- =====================================================================================

-- Instead of processing items one by one, batch process all qualifying items:

-- 1. Get all top sales items with their stock availability in one query
-- 2. Filter items that meet stock criteria
-- 3. Get all distribution locations for qualifying items in one query
-- 4. Process reorder level updates in batches

-- BATCH PROCESSING QUERY:
WITH top_sales_items AS (
    SELECT 
        sm.stockid,
        sm.categoryid,
        sm.description,
        ksp.topsales60,
        ROW_NUMBER() OVER (ORDER BY ksp.topsales60 DESC) as sales_rank
    FROM stockmaster sm
    INNER JOIN klsalesperformance ksp ON sm.stockid = ksp.stockid
    WHERE sm.discontinued = 0
      AND sm.klchangingprice = 0
      AND sm.categoryid IN (category_list)
),
stock_availability AS (
    SELECT 
        ls.stockid,
        SUM(CASE WHEN loc.stockreadytosell = 1 THEN ls.quantity ELSE 0 END) AS total_available
    FROM locstock ls
    INNER JOIN locations loc ON ls.loccode = loc.loccode
    GROUP BY ls.stockid
),
qualifying_items AS (
    SELECT 
        tsi.stockid,
        tsi.categoryid,
        tsi.description,
        tsi.topsales60,
        sa.total_available
    FROM top_sales_items tsi
    INNER JOIN stock_availability sa ON tsi.stockid = sa.stockid
    WHERE tsi.sales_rank BETWEEN start_rank AND end_rank
      AND sa.total_available > min_stock_available
      AND sa.total_available <= max_stock_available
)
SELECT 
    qi.stockid,
    qi.categoryid,
    qi.description,
    qi.total_available,
    ls.loccode,
    ls.reorderlevel AS old_rl
FROM qualifying_items qi
INNER JOIN locstock ls ON qi.stockid = ls.stockid
INNER JOIN locations loc ON ls.loccode = loc.loccode
WHERE loc.stockreadytosell = 1
  AND ls.reorderlevel > 0
ORDER BY qi.topsales60 DESC, qi.stockid, ls.loccode;

-- =====================================================================================
-- RECOMMENDED INDEXES:
-- =====================================================================================

-- These indexes should already exist based on the schema, but verify:

-- 1. klsalesperformance table (already exists):
--    PRIMARY KEY (stockid)
--    UNIQUE KEY uk_klsalesperformance_topsales60_stockid (topsales60, stockid)

-- 2. stockmaster table (already exists):
--    PRIMARY KEY (stockid)
--    UNIQUE KEY uk_stockmaster_discontinued_categoryid_stockid (discontinued, categoryid, stockid)
--    UNIQUE KEY uk_stockmaster_categoryid_stockid (categoryid, stockid)

-- 3. locstock table (already exists):
--    PRIMARY KEY (loccode, stockid)
--    UNIQUE KEY uk_locstock_stockid_loccode (stockid, loccode)
--    UNIQUE KEY uk_locstock_reorderlevel_loccode_stockid (reorderlevel, loccode, stockid)

-- 4. locations table (already exists):
--    PRIMARY KEY (loccode)
--    UNIQUE KEY uk_locations_stockreadytosell_loccode (stockreadytosell, loccode)

-- Additional composite index recommendation:
-- CREATE INDEX idx_stockmaster_optimization_composite 
-- ON stockmaster (discontinued, klchangingprice, categoryid, stockid);

-- This composite index will optimize the WHERE clause filtering in the main query

-- =====================================================================================
-- PERFORMANCE IMPROVEMENTS EXPECTED:
-- =====================================================================================

-- 1. ELIMINATION OF N+1 QUERIES:
--    - Original: 1 main query + N availability queries + M distribution queries
--    - Optimized: 1-2 queries total using JOINs and CTEs
--    - Expected improvement: 70-90% reduction in query execution time

-- 2. REDUCED DATABASE ROUND TRIPS:
--    - Original: Multiple round trips for each item
--    - Optimized: Single batch operation
--    - Expected improvement: 80-95% reduction in network overhead

-- 3. BETTER INDEX UTILIZATION:
--    - Explicit JOINs allow optimizer to choose better execution plans
--    - Composite filtering reduces table scans
--    - Expected improvement: 50-70% reduction in I/O operations

-- 4. MEMORY EFFICIENCY:
--    - Batch processing reduces PHP memory usage
--    - Single result set instead of multiple small ones
--    - Expected improvement: 40-60% reduction in memory usage

-- 5. OVERALL FUNCTION PERFORMANCE:
--    - Expected total improvement: 60-80% faster execution
--    - Scales better with larger datasets
--    - More predictable performance characteristics

-- =====================================================================================
-- IMPLEMENTATION NOTES:
-- =====================================================================================

-- 1. The optimized approach requires restructuring the PHP logic to handle batch results
-- 2. Consider implementing as a stored procedure for even better performance
-- 3. Add appropriate error handling for the batch operations
-- 4. Monitor query execution plans to ensure optimal index usage
-- 5. Consider adding query result caching for frequently accessed data

-- =====================================================================================
-- TESTING RECOMMENDATIONS:
-- =====================================================================================

-- 1. Test with various shop types (SHOPKL, SHOPBL, OUTKL, OUTBL)
-- 2. Verify results match original function output
-- 3. Performance test with different dataset sizes
-- 4. Monitor MySQL slow query log during testing
-- 5. Use EXPLAIN to verify optimal execution plans