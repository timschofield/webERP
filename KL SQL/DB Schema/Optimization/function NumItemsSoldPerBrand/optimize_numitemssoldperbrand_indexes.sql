-- =====================================================================================
-- OPTIMIZATION INDEXES FOR NumItemsSoldPerBrand FUNCTION
-- Created by: Roo (AI Assistant)
-- Date: 2025-08-26
-- Purpose: Optimize performance of NumItemsSoldPerBrand function in KLGeneralFunctions.php
-- =====================================================================================

-- Analysis of the optimized query:
-- SELECT SUM(sod.qtyinvoiced) AS solditems
-- FROM stockmaster sm
-- INNER JOIN salesorderdetails sod ON sm.stockid = sod.stkcode
-- WHERE sm.categoryid IN (category_list)
--   AND sod.itemdue >= 'FromDate'
--   AND sod.itemdue <= 'ToDate'

-- =====================================================================================
-- INDEX 1: Composite index on salesorderdetails for optimal date + stock filtering
-- =====================================================================================

-- This index will dramatically improve performance by allowing the database to:
-- 1. Quickly filter by date range using itemdue
-- 2. Include stkcode in the index for efficient JOINs
-- 3. Include qtyinvoiced as a covering index to avoid table lookups

CREATE INDEX `idx_salesorderdetails_itemdue_stkcode_qtyinvoiced` 
ON `salesorderdetails` (`itemdue`, `stkcode`, `qtyinvoiced`);

-- =====================================================================================
-- INDEX 2: Enhanced index on stockmaster for category filtering (if not optimal)
-- =====================================================================================

-- The existing index uk_stockmaster_categoryid_stockid (categoryid, stockid) 
-- should be sufficient, but if performance is still not optimal, we could add:
-- (This is commented out as the existing index should work well)

-- CREATE INDEX `idx_stockmaster_categoryid_stockid_optimized` 
-- ON `stockmaster` (`categoryid`, `stockid`);

-- =====================================================================================
-- PERFORMANCE ANALYSIS QUERIES
-- =====================================================================================

-- Use these queries to analyze performance before and after index creation:

-- 1. Check if the new index is being used:
-- EXPLAIN SELECT SUM(sod.qtyinvoiced) AS solditems
-- FROM stockmaster sm
-- INNER JOIN salesorderdetails sod ON sm.stockid = sod.stkcode  
-- WHERE sm.categoryid IN ('KLSIL', 'KLGOL')
--   AND sod.itemdue >= '2025-01-01'
--   AND sod.itemdue <= '2025-08-26';

-- 2. Check index usage statistics:
-- SHOW INDEX FROM salesorderdetails WHERE Key_name = 'idx_salesorderdetails_itemdue_stkcode_qtyinvoiced';

-- 3. Monitor query performance:
-- SET profiling = 1;
-- [Run the NumItemsSoldPerBrand query]
-- SHOW PROFILES;
-- SHOW PROFILE FOR QUERY [query_id];

-- =====================================================================================
-- MAINTENANCE NOTES
-- =====================================================================================

-- 1. This index will slightly slow down INSERT/UPDATE operations on salesorderdetails
--    but will dramatically improve SELECT performance for date-range queries
--
-- 2. The index size will be approximately:
--    - itemdue: 3 bytes (DATE)
--    - stkcode: 20 bytes (VARCHAR(20)) 
--    - qtyinvoiced: 8 bytes (DOUBLE)
--    Total: ~31 bytes per row + overhead
--
-- 3. Monitor index usage with:
--    SELECT * FROM information_schema.INDEX_STATISTICS 
--    WHERE table_name = 'salesorderdetails' 
--    AND index_name = 'idx_salesorderdetails_itemdue_stkcode_qtyinvoiced';

-- =====================================================================================
-- ROLLBACK SCRIPT (if needed)
-- =====================================================================================

-- To remove the index if performance doesn't improve or causes issues:
-- DROP INDEX `idx_salesorderdetails_itemdue_stkcode_qtyinvoiced` ON `salesorderdetails`;

-- =====================================================================================
-- EXPECTED PERFORMANCE IMPROVEMENTS
-- =====================================================================================

-- Before optimization:
-- - Full table scan on salesorderdetails filtered by date
-- - JOIN with stockmaster 
-- - Filter by category
-- - Estimated time: 500ms - 2000ms for large datasets

-- After optimization:
-- - Index seek on salesorderdetails by date range
-- - Efficient JOIN using covering index
-- - Category filtering on smaller result set
-- - Estimated time: 50ms - 200ms for large datasets
-- - Expected improvement: 5-10x faster query execution
