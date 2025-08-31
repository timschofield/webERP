-- =====================================================================================================================
-- WorstLocationForItem Function Optimization - Database Indexes
-- =====================================================================================================================
-- This file contains the database indexes required to optimize the WorstLocationForItem function performance.
-- These indexes are designed to support the optimized query structure and eliminate performance bottlenecks.
--
-- Expected Performance Improvement: 5-10x faster execution
-- Target Function: WorstLocationForItem() in includes/KLReorderLevel.php
-- 
-- DEPLOYMENT INSTRUCTIONS:
-- 1. Execute these statements in the target database during a maintenance window
-- 2. Monitor index creation progress for large tables
-- 3. Update table statistics after index creation
-- 4. Test the optimized function thoroughly before production deployment
-- =====================================================================================================================

-- Index 1: Composite index for salesorders table to optimize the subquery JOIN and filtering
-- This index supports: so.orddate >= date AND sod.stkcode = stockid filtering and JOIN operations
-- Covers the most selective columns first: orddate (date range), then fromstkloc for grouping
DROP INDEX IF EXISTS idx_salesorders_orddate_fromstkloc_orderno ON salesorders;
CREATE INDEX idx_salesorders_orddate_fromstkloc_orderno 
ON salesorders (orddate, fromstkloc, orderno);

-- Index 2: Composite index for salesorderdetails to optimize stock filtering and completed status
-- This index supports: sod.stkcode = stockid AND sod.completed = 1 filtering
-- Covers: stkcode (most selective), completed status, orderno for JOIN
DROP INDEX IF EXISTS idx_salesorderdetails_stkcode_completed_orderno ON salesorderdetails;
CREATE INDEX idx_salesorderdetails_stkcode_completed_orderno 
ON salesorderdetails (stkcode, completed, orderno);

-- Index 3: Enhanced composite index for locstock table to optimize main query filtering
-- This index supports: ls.stockid = stockid AND quantity conditions
-- Covers: stockid (most selective), quantity, reorderlevel, loccode
-- DROP INDEX IF EXISTS idx_locstock_stockid_quantity_reorderlevel_loccode ON locstock;
-- CREATE INDEX idx_locstock_stockid_quantity_reorderlevel_loccode 
-- ON locstock (stockid, quantity, reorderlevel, loccode);
-- Index 3 NOT CREATED as it is highly dynamic and will cause overload.

-- Index 4: Composite index for locations table to optimize typeloc filtering and priority ordering
-- This index supports: loc.typeloc IN (...) AND ORDER BY loc.priority DESC
-- Covers: typeloc (filtering), priority (ordering), loccode (JOIN)
DROP INDEX IF EXISTS idx_locations_typeloc_priorityDESC_loccode ON locations;
CREATE INDEX idx_locations_typeloc_priorityDESC_loccode 
ON locations (typeloc, priority DESC, loccode);


-- =====================================================================================================================
-- Index Statistics and Maintenance
-- =====================================================================================================================

-- Update table statistics after index creation (MySQL/MariaDB)
ANALYZE TABLE salesorders;
ANALYZE TABLE salesorderdetails;
ANALYZE TABLE locstock;
ANALYZE TABLE locations;

-- =====================================================================================================================
-- Performance Verification Queries
-- =====================================================================================================================

-- Test query 1: Verify index usage for main query pattern
-- EXPLAIN SELECT ls.loccode, loc.priority, COALESCE(sales_count.sales_total, 0) as sales_count
-- FROM locstock ls
-- INNER JOIN locations loc ON ls.loccode = loc.loccode
-- LEFT JOIN (
--     SELECT so.fromstkloc, COUNT(sod.qtyinvoiced) as sales_total
--     FROM salesorders so
--     INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
--     WHERE sod.stkcode = 'TEST_STOCK_ID'
--       AND sod.completed = 1
--       AND so.orddate >= '2024-01-01'
--     GROUP BY so.fromstkloc
-- ) sales_count ON ls.loccode = sales_count.fromstkloc
-- WHERE ls.stockid = 'TEST_STOCK_ID'
--   AND ls.quantity > 0
--   AND loc.typeloc = 'SHOP'
-- ORDER BY loc.priority DESC, sales_count ASC
-- LIMIT 1;

-- =====================================================================================================================
-- Rollback Script (if needed)
-- =====================================================================================================================

-- Uncomment the following lines if you need to remove the indexes:
-- DROP INDEX IF EXISTS idx_salesorders_orddate_fromstkloc_optimization ON salesorders;
-- DROP INDEX IF EXISTS idx_salesorderdetails_stkcode_completed_orderno ON salesorderdetails;
-- DROP INDEX IF EXISTS idx_locstock_stockid_quantity_reorderlevel_loccode ON locstock;
-- DROP INDEX IF EXISTS idx_locations_typeloc_priority_loccode ON locations;
-- DROP INDEX IF EXISTS idx_salesorders_salesorderdetails_covering ON salesorders;
-- DROP INDEX IF EXISTS idx_salesorderdetails_covering_optimization ON salesorderdetails;

-- =====================================================================================================================
-- End of WorstLocationForItem Optimization Indexes
-- =====================================================================================================================