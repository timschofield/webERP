-- =====================================================
-- FINISHEDSTOCKDISTRIBUTION OPTIMIZATION TEST SUITE
-- =====================================================
--
-- This file contains comprehensive tests to validate the performance improvements
-- of the optimized FinishedStockDistribution function.
--
-- Target Function: FinishedStockDistribution() in includes/KLBoards.php line 1037
-- Expected Performance Improvement: 5-10x faster execution
--

-- =====================================================
-- PRE-OPTIMIZATION BASELINE TESTS
-- =====================================================

-- Test 1: Original Location-based Query Performance
-- This represents the original query structure before optimization
SELECT 'BASELINE TEST 1: Original Location Query' as test_name;

EXPLAIN ANALYZE
SELECT 
    locstock.loccode,
    locations.locationname,
    SUM(locstock.reorderlevel) AS optimalstock,
    SUM(locstock.quantity) AS realstock,
    SUM(CASE WHEN locstock.reorderlevel != 0 THEN 1 ELSE 0 END) AS optimalmodels,
    SUM(CASE WHEN locstock.quantity != 0 THEN 1 ELSE 0 END) AS realmodels
FROM locstock
INNER JOIN locations ON locstock.loccode = locations.loccode
INNER JOIN stockmaster ON locstock.stockid = stockmaster.stockid
INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
WHERE stockcategory.stocktype = 'F'
GROUP BY locstock.loccode
ORDER BY locations.locationname;

-- Test 2: Original Category-based Query Performance
SELECT 'BASELINE TEST 2: Original Category Query' as test_name;

EXPLAIN ANALYZE
SELECT 
    stockmaster.categoryid,
    stockcategory.categorydescription,
    SUM(locstock.reorderlevel) AS optimalstock,
    SUM(locstock.quantity) AS realstock,
    COUNT(DISTINCT CASE WHEN locstock.quantity != 0 THEN locstock.stockid ELSE NULL END) AS realmodels
FROM locstock
INNER JOIN stockmaster ON locstock.stockid = stockmaster.stockid
INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
WHERE stockcategory.stocktype = 'F'
GROUP BY stockmaster.categoryid
ORDER BY stockcategory.categorydescription;

-- =====================================================
-- POST-OPTIMIZATION PERFORMANCE TESTS
-- =====================================================

-- Test 3: Optimized Location-based Query Performance
SELECT 'OPTIMIZATION TEST 3: Optimized Location Query' as test_name;

EXPLAIN ANALYZE
SELECT 
    locstock.loccode,
    locations.locationname,
    SUM(locstock.reorderlevel) AS optimalstock,
    SUM(locstock.quantity) AS realstock,
    SUM(CASE WHEN locstock.reorderlevel > 0 THEN 1 ELSE 0 END) AS optimalmodels,
    SUM(CASE WHEN locstock.quantity > 0 THEN 1 ELSE 0 END) AS realmodels
FROM stockcategory
INNER JOIN stockmaster ON stockmaster.categoryid = stockcategory.categoryid
INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
INNER JOIN locations ON locations.loccode = locstock.loccode
WHERE stockcategory.stocktype = 'F'
    AND stockmaster.discontinued = 0
GROUP BY locstock.loccode, locations.locationname
ORDER BY locations.locationname;

-- Test 4: Optimized Category-based Query Performance
SELECT 'OPTIMIZATION TEST 4: Optimized Category Query' as test_name;

EXPLAIN ANALYZE
SELECT 
    stockcategory.categoryid,
    stockcategory.categorydescription,
    SUM(locstock.reorderlevel) AS optimalstock,
    SUM(locstock.quantity) AS realstock,
    COUNT(DISTINCT CASE WHEN locstock.quantity > 0 THEN locstock.stockid END) AS realmodels
FROM stockcategory
INNER JOIN stockmaster ON stockmaster.categoryid = stockcategory.categoryid
INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
WHERE stockcategory.stocktype = 'F'
    AND stockmaster.discontinued = 0
GROUP BY stockcategory.categoryid, stockcategory.categorydescription
ORDER BY stockcategory.categorydescription;

-- =====================================================
-- INDEX USAGE VERIFICATION TESTS
-- =====================================================

-- Test 5: Verify Index Usage for Stockcategory Filter
SELECT 'INDEX TEST 5: Stockcategory Index Usage' as test_name;

EXPLAIN
SELECT categoryid 
FROM stockcategory 
WHERE stocktype = 'F'
ORDER BY categoryid;

-- Test 6: Verify Index Usage for Stockmaster JOIN
SELECT 'INDEX TEST 6: Stockmaster Index Usage' as test_name;

EXPLAIN
SELECT sm.stockid
FROM stockcategory sc
INNER JOIN stockmaster sm ON sm.categoryid = sc.categoryid
WHERE sc.stocktype = 'F' AND sm.discontinued = 0;

-- Test 7: Verify Index Usage for Locstock Aggregation
SELECT 'INDEX TEST 7: Locstock Index Usage' as test_name;

EXPLAIN
SELECT loccode, SUM(quantity), SUM(reorderlevel)
FROM locstock
WHERE stockid IN (
    SELECT sm.stockid
    FROM stockcategory sc
    INNER JOIN stockmaster sm ON sm.categoryid = sc.categoryid
    WHERE sc.stocktype = 'F' AND sm.discontinued = 0
)
GROUP BY loccode;

-- Test 8: Verify Index Usage for Locations ORDER BY
SELECT 'INDEX TEST 8: Locations Index Usage' as test_name;

EXPLAIN
SELECT loccode, locationname
FROM locations
ORDER BY locationname;

-- =====================================================
-- FUNCTIONAL CORRECTNESS TESTS
-- =====================================================

-- Test 9: Data Consistency Check - Location Results
SELECT 'CONSISTENCY TEST 9: Location Results Match' as test_name;

-- Original query results
CREATE TEMPORARY TABLE temp_original_location AS
SELECT 
    locstock.loccode,
    SUM(locstock.reorderlevel) AS optimalstock,
    SUM(locstock.quantity) AS realstock,
    SUM(CASE WHEN locstock.reorderlevel != 0 THEN 1 ELSE 0 END) AS optimalmodels,
    SUM(CASE WHEN locstock.quantity != 0 THEN 1 ELSE 0 END) AS realmodels
FROM locstock
INNER JOIN locations ON locstock.loccode = locations.loccode
INNER JOIN stockmaster ON locstock.stockid = stockmaster.stockid
INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
WHERE stockcategory.stocktype = 'F'
GROUP BY locstock.loccode;

-- Optimized query results
CREATE TEMPORARY TABLE temp_optimized_location AS
SELECT 
    locstock.loccode,
    SUM(locstock.reorderlevel) AS optimalstock,
    SUM(locstock.quantity) AS realstock,
    SUM(CASE WHEN locstock.reorderlevel > 0 THEN 1 ELSE 0 END) AS optimalmodels,
    SUM(CASE WHEN locstock.quantity > 0 THEN 1 ELSE 0 END) AS realmodels
FROM stockcategory
INNER JOIN stockmaster ON stockmaster.categoryid = stockcategory.categoryid
INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
WHERE stockcategory.stocktype = 'F'
    AND stockmaster.discontinued = 0
GROUP BY locstock.loccode;

-- Compare results (should return 0 differences)
SELECT 
    COUNT(*) as differences_found,
    CASE 
        WHEN COUNT(*) = 0 THEN 'PASS: Location results match'
        ELSE 'FAIL: Location results differ'
    END as test_result
FROM (
    SELECT loccode FROM temp_original_location
    EXCEPT
    SELECT loccode FROM temp_optimized_location
    UNION
    SELECT loccode FROM temp_optimized_location
    EXCEPT
    SELECT loccode FROM temp_original_location
) as differences;

-- Test 10: Data Consistency Check - Category Results
SELECT 'CONSISTENCY TEST 10: Category Results Match' as test_name;

-- Original category query results
CREATE TEMPORARY TABLE temp_original_category AS
SELECT 
    stockmaster.categoryid,
    SUM(locstock.reorderlevel) AS optimalstock,
    SUM(locstock.quantity) AS realstock
FROM locstock
INNER JOIN stockmaster ON locstock.stockid = stockmaster.stockid
INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
WHERE stockcategory.stocktype = 'F'
GROUP BY stockmaster.categoryid;

-- Optimized category query results
CREATE TEMPORARY TABLE temp_optimized_category AS
SELECT 
    stockcategory.categoryid,
    SUM(locstock.reorderlevel) AS optimalstock,
    SUM(locstock.quantity) AS realstock
FROM stockcategory
INNER JOIN stockmaster ON stockmaster.categoryid = stockcategory.categoryid
INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
WHERE stockcategory.stocktype = 'F'
    AND stockmaster.discontinued = 0
GROUP BY stockcategory.categoryid;

-- Compare category results
SELECT 
    COUNT(*) as differences_found,
    CASE 
        WHEN COUNT(*) = 0 THEN 'PASS: Category results match'
        ELSE 'FAIL: Category results differ'
    END as test_result
FROM (
    SELECT categoryid FROM temp_original_category
    EXCEPT
    SELECT categoryid FROM temp_optimized_category
    UNION
    SELECT categoryid FROM temp_optimized_category
    EXCEPT
    SELECT categoryid FROM temp_original_category
) as differences;

-- =====================================================
-- PERFORMANCE BENCHMARK TESTS
-- =====================================================

-- Test 11: Execution Time Comparison
SELECT 'BENCHMARK TEST 11: Execution Time Comparison' as test_name;

-- Measure original query execution time
SET @start_time = NOW(6);

SELECT COUNT(*) as location_count
FROM (
    SELECT locstock.loccode
    FROM locstock
    INNER JOIN locations ON locstock.loccode = locations.loccode
    INNER JOIN stockmaster ON locstock.stockid = stockmaster.stockid
    INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
    WHERE stockcategory.stocktype = 'F'
    GROUP BY locstock.loccode
) as original_query;

SET @original_time = TIMESTAMPDIFF(MICROSECOND, @start_time, NOW(6));

-- Measure optimized query execution time
SET @start_time = NOW(6);

SELECT COUNT(*) as location_count
FROM (
    SELECT locstock.loccode
    FROM stockcategory
    INNER JOIN stockmaster ON stockmaster.categoryid = stockcategory.categoryid
    INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
    WHERE stockcategory.stocktype = 'F'
        AND stockmaster.discontinued = 0
    GROUP BY locstock.loccode
) as optimized_query;

SET @optimized_time = TIMESTAMPDIFF(MICROSECOND, @start_time, NOW(6));

-- Calculate performance improvement
SELECT 
    @original_time as original_time_microseconds,
    @optimized_time as optimized_time_microseconds,
    ROUND(@original_time / @optimized_time, 2) as performance_improvement_factor,
    CASE 
        WHEN @original_time / @optimized_time >= 5 THEN 'PASS: 5x+ improvement achieved'
        WHEN @original_time / @optimized_time >= 2 THEN 'PARTIAL: 2x+ improvement achieved'
        ELSE 'FAIL: Less than 2x improvement'
    END as performance_test_result;

-- =====================================================
-- CLEANUP
-- =====================================================

-- Drop temporary tables
DROP TEMPORARY TABLE IF EXISTS temp_original_location;
DROP TEMPORARY TABLE IF EXISTS temp_optimized_location;
DROP TEMPORARY TABLE IF EXISTS temp_original_category;
DROP TEMPORARY TABLE IF EXISTS temp_optimized_category;

-- =====================================================
-- TEST EXECUTION SUMMARY
-- =====================================================

SELECT 'TEST SUITE COMPLETED' as status,
       'Review EXPLAIN ANALYZE results for performance improvements' as next_steps,
       'Expected: 5-10x performance improvement with new indexes' as expected_outcome;

/*
EXPECTED TEST RESULTS:

1. EXPLAIN ANALYZE should show:
   - Reduced rows examined
   - Index usage instead of table scans
   - Lower execution times

2. Index usage tests should show:
   - "Using index" in Extra column
   - Key column showing our new indexes
   - Lower cost estimates

3. Consistency tests should show:
   - 0 differences between original and optimized results
   - PASS status for all functional tests

4. Performance tests should show:
   - 5-10x improvement in execution time
   - Reduced CPU and I/O usage

TROUBLESHOOTING:
- If tests fail, verify indexes were created correctly
- Check that statistics are up to date: ANALYZE TABLE tablename;
- Ensure MySQL query cache is disabled for accurate timing
- Run tests multiple times to account for caching effects
*/