-- =====================================================================================
-- TEST SCRIPT FOR NumItemsSoldPerBrand OPTIMIZATION
-- Created by: Roo (AI Assistant)
-- Date: 2025-08-26
-- Purpose: Test and validate the performance improvements for NumItemsSoldPerBrand function
-- =====================================================================================

-- =====================================================================================
-- STEP 1: APPLY THE NEW INDEX
-- =====================================================================================

-- First, create the optimized index
CREATE INDEX `idx_salesorderdetails_itemdue_stkcode_qtyinvoiced` 
ON `salesorderdetails` (`itemdue`, `stkcode`, `qtyinvoiced`);

-- =====================================================================================
-- STEP 2: PERFORMANCE COMPARISON TESTS
-- =====================================================================================

-- Enable query profiling to measure performance
SET profiling = 1;

-- Test 1: Original query structure (for comparison)
SELECT 'ORIGINAL QUERY STRUCTURE' as test_type;
SELECT SUM(salesorderdetails.qtyinvoiced) AS solditems
FROM salesorderdetails
INNER JOIN stockmaster
    ON salesorderdetails.stkcode = stockmaster.stockid
WHERE salesorderdetails.itemdue >= '2025-01-01'
    AND salesorderdetails.itemdue <= '2025-08-26'
    AND stockmaster.categoryid IN ('TESTKA','STABKA','NOPOKA');

-- Test 2: Optimized query structure
SELECT 'OPTIMIZED QUERY STRUCTURE' as test_type;
SELECT SUM(sod.qtyinvoiced) AS solditems
FROM stockmaster sm
INNER JOIN salesorderdetails sod 
    ON sm.stockid = sod.stkcode
WHERE sm.categoryid IN ('TESTKA','STABKA','NOPOKA')
    AND sod.itemdue >= '2025-01-01'
    AND sod.itemdue <= '2025-08-26';

-- Show performance comparison
SHOW PROFILES;

-- =====================================================================================
-- STEP 3: EXPLAIN PLAN ANALYSIS
-- =====================================================================================

-- Analyze the execution plan for the original query
EXPLAIN FORMAT=JSON
SELECT SUM(salesorderdetails.qtyinvoiced) AS solditems
FROM salesorderdetails
INNER JOIN stockmaster
    ON salesorderdetails.stkcode = stockmaster.stockid
WHERE salesorderdetails.itemdue >= '2025-01-01'
    AND salesorderdetails.itemdue <= '2025-08-26'
    AND stockmaster.categoryid IN ('TESTKA','STABKA','NOPOKA');

-- Analyze the execution plan for the optimized query
EXPLAIN FORMAT=JSON
SELECT SUM(sod.qtyinvoiced) AS solditems
FROM stockmaster sm
INNER JOIN salesorderdetails sod 
    ON sm.stockid = sod.stkcode
WHERE sm.categoryid IN ('TESTKA','STABKA','NOPOKA')
    AND sod.itemdue >= '2025-01-01'
    AND sod.itemdue <= '2025-08-26';

-- =====================================================================================
-- STEP 4: INDEX USAGE VERIFICATION
-- =====================================================================================

-- Check if our new index exists and is being used
SHOW INDEX FROM salesorderdetails WHERE Key_name = 'idx_salesorderdetails_itemdue_stkcode_qtyinvoiced';

-- Check existing relevant indexes
SHOW INDEX FROM salesorderdetails WHERE Key_name IN ('idx_itemdue_stkcode', 'idx_salesorderdetails_itemdue_stkcode_qtyinvoiced');
SHOW INDEX FROM stockmaster WHERE Key_name LIKE '%categoryid%';

-- =====================================================================================
-- STEP 5: FUNCTIONAL TESTING WITH DIFFERENT BRANDS
-- =====================================================================================

-- Test all brand categories to ensure functionality is preserved

-- Test SHOPKL (Kapal Laut)
SELECT 'SHOPKL TEST' as brand_test, SUM(sod.qtyinvoiced) AS solditems
FROM stockmaster sm
INNER JOIN salesorderdetails sod ON sm.stockid = sod.stkcode
WHERE sm.categoryid IN ('TESTKA','STABKA','NOPOKA')
    AND sod.itemdue >= '2025-01-01'
    AND sod.itemdue <= '2025-08-26';

-- Test SHOPBL (Blink)  
SELECT 'SHOPBL TEST' as brand_test, SUM(sod.qtyinvoiced) AS solditems
FROM stockmaster sm
INNER JOIN salesorderdetails sod ON sm.stockid = sod.stkcode
WHERE sm.categoryid IN ('TESTBA','STABBA','NOPOBA')
    AND sod.itemdue >= '2025-01-01'
    AND sod.itemdue <= '2025-08-26';

-- Test with different date ranges
SELECT 'LAST 30 DAYS TEST' as date_test, SUM(sod.qtyinvoiced) AS solditems
FROM stockmaster sm
INNER JOIN salesorderdetails sod ON sm.stockid = sod.stkcode
WHERE sm.categoryid IN ('TESTBA','STABBA','NOPOBA')
    AND sod.itemdue >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    AND sod.itemdue <= CURDATE();

-- Test with larger date range (1 year)
SELECT 'LAST YEAR TEST' as date_test, SUM(sod.qtyinvoiced) AS solditems
FROM stockmaster sm
INNER JOIN salesorderdetails sod ON sm.stockid = sod.stkcode
WHERE sm.categoryid IN ('TESTBA','STABBA','NOPOBA')
    AND sod.itemdue >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    AND sod.itemdue <= CURDATE();

-- =====================================================================================
-- STEP 6: STRESS TEST WITH CONCURRENT QUERIES
-- =====================================================================================

-- This section should be run manually with multiple connections
-- to test performance under load

/*
-- Run these queries simultaneously from different connections:

-- Connection 1:
SELECT 'CONCURRENT_TEST_1' as test, SUM(sod.qtyinvoiced) AS solditems
FROM stockmaster sm
INNER JOIN salesorderdetails sod ON sm.stockid = sod.stkcode
WHERE sm.categoryid IN ('TESTKA','STABKA','NOPOKA')
    AND sod.itemdue >= '2024-01-01' AND sod.itemdue <= '2024-12-31';

-- Connection 2:
SELECT 'CONCURRENT_TEST_2' as test, SUM(sod.qtyinvoiced) AS solditems  
FROM stockmaster sm
INNER JOIN salesorderdetails sod ON sm.stockid = sod.stkcode
WHERE sm.categoryid IN ('BLSIL', 'BLGOL')
    AND sod.itemdue >= '2024-01-01' AND sod.itemdue <= '2024-12-31';

-- Connection 3:
SELECT 'CONCURRENT_TEST_3' as test, SUM(sod.qtyinvoiced) AS solditems
FROM stockmaster sm  
INNER JOIN salesorderdetails sod ON sm.stockid = sod.stkcode
WHERE sm.categoryid IN ('OUSIL', 'OUGOL')
    AND sod.itemdue >= '2024-01-01' AND sod.itemdue <= '2024-12-31';
*/

-- =====================================================================================
-- STEP 7: VALIDATION QUERIES
-- =====================================================================================

-- Ensure results are consistent between old and new query structures
-- (Run these with the same parameters and compare results)

-- Count total records to ensure no data loss
SELECT 'RECORD COUNT CHECK' as validation,
       COUNT(*) as total_records,
       COUNT(DISTINCT sod.stkcode) as unique_products,
       MIN(sod.itemdue) as earliest_date,
       MAX(sod.itemdue) as latest_date
FROM stockmaster sm
INNER JOIN salesorderdetails sod ON sm.stockid = sod.stkcode
WHERE sm.categoryid IN ('TESTKA','STABKA','NOPOKA')
    AND sod.itemdue >= '2025-01-01'
    AND sod.itemdue <= '2025-08-26';

-- Check for NULL values that might affect SUM
SELECT 'NULL VALUE CHECK' as validation,
       COUNT(*) as total_records,
       COUNT(sod.qtyinvoiced) as non_null_qty,
       SUM(CASE WHEN sod.qtyinvoiced IS NULL THEN 1 ELSE 0 END) as null_qty_count
FROM stockmaster sm
INNER JOIN salesorderdetails sod ON sm.stockid = sod.stkcode
WHERE sm.categoryid IN ('TESTKA','STABKA','NOPOKA')
    AND sod.itemdue >= '2025-01-01'
    AND sod.itemdue <= '2025-08-26';

-- =====================================================================================
-- STEP 8: CLEANUP (OPTIONAL)
-- =====================================================================================

-- Disable profiling
SET profiling = 0;

-- If you need to remove the index for any reason:
-- DROP INDEX `idx_salesorderdetails_itemdue_stkcode_qtyinvoiced` ON `salesorderdetails`;

-- =====================================================================================
-- EXPECTED RESULTS
-- =====================================================================================

/*
Expected improvements after optimization:

1. QUERY EXECUTION TIME:
   - Before: 500ms - 2000ms (depending on data size)
   - After: 50ms - 200ms (5-10x improvement)

2. INDEX USAGE:
   - New index should appear in EXPLAIN plans
   - "Using index" should appear for covering index scenarios

3. RESOURCE USAGE:
   - Lower CPU usage due to fewer table scans
   - Reduced I/O operations
   - Better concurrent query performance

4. SCALABILITY:
   - Performance improvement increases with data size
   - Better performance under concurrent load
*/

SELECT 'OPTIMIZATION TEST COMPLETED' as status, NOW() as completion_time;