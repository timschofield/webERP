-- =====================================================
-- Test Suite for PeriodDifferenceSales Optimization
-- =====================================================
-- Created: 2025-08-27
-- Purpose: Comprehensive testing of optimized PeriodDifferenceSales function
-- Expected Results: 5-10x performance improvement with identical output
-- =====================================================

-- =====================================================
-- Test Setup and Configuration
-- =====================================================

-- Enable query timing and profiling
SET profiling = 1;
SET profiling_history_size = 100;

-- Test parameters
SET @test_start_date = '2024-01-01';
SET @test_end_date = '2024-12-31';
SET @compare_start_date = '2023-01-01';
SET @compare_end_date = '2023-12-31';

-- =====================================================
-- Performance Baseline Tests (Before Optimization)
-- =====================================================

-- Note: These would be run against the original function for comparison
-- Baseline timing will be recorded separately

-- =====================================================
-- Test 1: Shop Query Performance Test
-- =====================================================

SELECT 'TEST 1: Shop Query Performance' as test_name;

-- Reset query cache for accurate timing
RESET QUERY CACHE;

-- Execute optimized Shop query
SELECT SQL_NO_CACHE
    l.locationname as shop_name,
    COALESCE(current_sales.total_current, 0) as current_period_sales,
    COALESCE(compare_sales.total_compare, 0) as compare_period_sales,
    COALESCE(current_sales.total_current, 0) - COALESCE(compare_sales.total_compare, 0) as difference,
    CASE 
        WHEN COALESCE(compare_sales.total_compare, 0) = 0 THEN 0
        ELSE ROUND(((COALESCE(current_sales.total_current, 0) - COALESCE(compare_sales.total_compare, 0)) / compare_sales.total_compare) * 100, 2)
    END as percentage_change
FROM locations l
LEFT JOIN (
    SELECT 
        so.fromstkloc,
        SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100)) as total_current
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    INNER JOIN debtorsmaster dm ON so.debtorno = dm.debtorno
    WHERE so.orddate >= @test_start_date 
        AND so.orddate <= @test_end_date
        AND so.quotation = 0
        AND sod.qtyinvoiced > 0
    GROUP BY so.fromstkloc
) current_sales ON l.loccode = current_sales.fromstkloc
LEFT JOIN (
    SELECT 
        so.fromstkloc,
        SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100)) as total_compare
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    INNER JOIN debtorsmaster dm ON so.debtorno = dm.debtorno
    WHERE so.orddate >= @compare_start_date 
        AND so.orddate <= @compare_end_date
        AND so.quotation = 0
        AND sod.qtyinvoiced > 0
    GROUP BY so.fromstkloc
) compare_sales ON l.loccode = compare_sales.fromstkloc
WHERE l.typeloc = 'SHOP'
ORDER BY current_period_sales DESC
LIMIT 10;

-- Record execution time
SHOW PROFILES;

-- =====================================================
-- Test 2: Online Query Performance Test
-- =====================================================

SELECT 'TEST 2: Online Query Performance' as test_name;

-- Reset query cache for accurate timing
RESET QUERY CACHE;

-- Execute optimized Online query
SELECT SQL_NO_CACHE
    dt.typename as customer_type,
    COALESCE(current_sales.total_current, 0) as current_period_sales,
    COALESCE(compare_sales.total_compare, 0) as compare_period_sales,
    COALESCE(current_sales.total_current, 0) - COALESCE(compare_sales.total_compare, 0) as difference,
    CASE 
        WHEN COALESCE(compare_sales.total_compare, 0) = 0 THEN 0
        ELSE ROUND(((COALESCE(current_sales.total_current, 0) - COALESCE(compare_sales.total_compare, 0)) / compare_sales.total_compare) * 100, 2)
    END as percentage_change
FROM debtortype dt
LEFT JOIN (
    SELECT 
        dm.typeid,
        SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100)) as total_current
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    INNER JOIN debtorsmaster dm ON so.debtorno = dm.debtorno
    WHERE so.orddate >= @test_start_date 
        AND so.orddate <= @test_end_date
        AND so.quotation = 0
        AND sod.qtyinvoiced > 0
        AND dm.typeid IN (2, 3, 4, 5)
    GROUP BY dm.typeid
) current_sales ON dt.typeid = current_sales.typeid
LEFT JOIN (
    SELECT 
        dm.typeid,
        SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100)) as total_compare
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    INNER JOIN debtorsmaster dm ON so.debtorno = dm.debtorno
    WHERE so.orddate >= @compare_start_date 
        AND so.orddate <= @compare_end_date
        AND so.quotation = 0
        AND sod.qtyinvoiced > 0
        AND dm.typeid IN (2, 3, 4, 5)
    GROUP BY dm.typeid
) compare_sales ON dt.typeid = compare_sales.typeid
WHERE dt.typeid IN (2, 3, 4, 5)
ORDER BY current_period_sales DESC;

-- Record execution time
SHOW PROFILES;

-- =====================================================
-- Test 3: Salesman Query Performance Test
-- =====================================================

SELECT 'TEST 3: Salesman Query Performance' as test_name;

-- Reset query cache for accurate timing
RESET QUERY CACHE;

-- Execute optimized Salesman query
SELECT SQL_NO_CACHE
    s.salesmanname,
    COALESCE(current_sales.total_current, 0) as current_period_sales,
    COALESCE(compare_sales.total_compare, 0) as compare_period_sales,
    COALESCE(current_sales.total_current, 0) - COALESCE(compare_sales.total_compare, 0) as difference,
    CASE 
        WHEN COALESCE(compare_sales.total_compare, 0) = 0 THEN 0
        ELSE ROUND(((COALESCE(current_sales.total_current, 0) - COALESCE(compare_sales.total_compare, 0)) / compare_sales.total_compare) * 100, 2)
    END as percentage_change
FROM salesman s
LEFT JOIN (
    SELECT 
        so.salesperson,
        SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100)) as total_current
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    WHERE so.orddate >= @test_start_date 
        AND so.orddate <= @test_end_date
        AND so.quotation = 0
        AND sod.qtyinvoiced > 0
        AND so.salesperson != ''
    GROUP BY so.salesperson
) current_sales ON s.salesmancode = current_sales.salesperson
LEFT JOIN (
    SELECT 
        so.salesperson,
        SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100)) as total_compare
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    WHERE so.orddate >= @compare_start_date 
        AND so.orddate <= @compare_end_date
        AND so.quotation = 0
        AND sod.qtyinvoiced > 0
        AND so.salesperson != ''
    GROUP BY so.salesperson
) compare_sales ON s.salesmancode = compare_sales.salesperson
WHERE s.current = 1
ORDER BY current_period_sales DESC
LIMIT 10;

-- Record execution time
SHOW PROFILES;

-- =====================================================
-- Test 4: Index Usage Verification
-- =====================================================

SELECT 'TEST 4: Index Usage Verification' as test_name;

-- Check if our new indexes are being used
EXPLAIN FORMAT=JSON
SELECT 
    so.fromstkloc,
    SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100)) as total_sales
FROM salesorders so
INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
INNER JOIN debtorsmaster dm ON so.debtorno = dm.debtorno
WHERE so.orddate >= @test_start_date 
    AND so.orddate <= @test_end_date
    AND so.quotation = 0
    AND sod.qtyinvoiced > 0
GROUP BY so.fromstkloc;

-- =====================================================
-- Test 5: Data Accuracy Verification
-- =====================================================

SELECT 'TEST 5: Data Accuracy Verification' as test_name;

-- Test with known data to verify calculations are correct
-- This would compare results with manual calculations or previous known good results

-- Sample verification query for a specific shop
SELECT 
    'Accuracy Test - Sample Shop' as test_type,
    l.locationname,
    COUNT(DISTINCT so.orderno) as order_count,
    SUM(sod.qtyinvoiced) as total_quantity,
    SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100)) as total_sales
FROM locations l
INNER JOIN salesorders so ON l.loccode = so.fromstkloc
INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
WHERE l.typeloc = 'SHOP'
    AND so.orddate >= @test_start_date 
    AND so.orddate <= @test_end_date
    AND so.quotation = 0
    AND sod.qtyinvoiced > 0
    AND l.loccode = (SELECT loccode FROM locations WHERE typeloc = 'SHOP' LIMIT 1)
GROUP BY l.loccode, l.locationname;

-- =====================================================
-- Test 6: Stress Test with Large Date Ranges
-- =====================================================

SELECT 'TEST 6: Stress Test with Large Date Ranges' as test_name;

-- Test with larger date ranges to verify performance scales well
SET @stress_start_date = '2020-01-01';
SET @stress_end_date = '2024-12-31';

-- Reset query cache for accurate timing
RESET QUERY CACHE;

SELECT SQL_NO_CACHE
    COUNT(*) as total_records,
    SUM(COALESCE(current_sales.total_current, 0)) as total_current_sales
FROM locations l
LEFT JOIN (
    SELECT 
        so.fromstkloc,
        SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100)) as total_current
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    WHERE so.orddate >= @stress_start_date 
        AND so.orddate <= @stress_end_date
        AND so.quotation = 0
        AND sod.qtyinvoiced > 0
    GROUP BY so.fromstkloc
) current_sales ON l.loccode = current_sales.fromstkloc
WHERE l.typeloc = 'SHOP';

-- Record execution time
SHOW PROFILES;

-- =====================================================
-- Test 7: Concurrent Load Test Simulation
-- =====================================================

SELECT 'TEST 7: Concurrent Load Test Simulation' as test_name;

-- Simulate multiple concurrent queries (run this multiple times in parallel)
-- This tests index contention and locking behavior

SELECT SQL_NO_CACHE
    'Concurrent Test',
    COUNT(*) as shop_count,
    AVG(COALESCE(current_sales.total_current, 0)) as avg_sales
FROM locations l
LEFT JOIN (
    SELECT 
        so.fromstkloc,
        SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100)) as total_current
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    WHERE so.orddate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        AND so.orddate <= NOW()
        AND so.quotation = 0
        AND sod.qtyinvoiced > 0
    GROUP BY so.fromstkloc
) current_sales ON l.loccode = current_sales.fromstkloc
WHERE l.typeloc = 'SHOP';

-- =====================================================
-- Test Results Summary
-- =====================================================

SELECT 'TEST SUMMARY: Performance Analysis' as summary;

-- Show all query profiles for analysis
SHOW PROFILES;

-- Index usage statistics
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    CARDINALITY,
    SUB_PART,
    PACKED,
    NULLABLE,
    INDEX_TYPE
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('salesorders', 'salesorderdetails', 'debtorsmaster', 'locations', 'salesman', 'debtortype')
    AND INDEX_NAME LIKE 'idx_%'
ORDER BY TABLE_NAME, INDEX_NAME;

-- =====================================================
-- Expected Performance Improvements
-- =====================================================

/*
Expected Results Summary:

1. Query Execution Time:
   - Shop Query: 5-10x faster (from ~2-5 seconds to ~0.2-0.5 seconds)
   - Online Query: 5-8x faster (from ~1-3 seconds to ~0.1-0.4 seconds)
   - Salesman Query: 6-10x faster (from ~3-6 seconds to ~0.3-0.6 seconds)

2. Index Usage:
   - All queries should show "Using index" in EXPLAIN output
   - No full table scans should occur
   - JOIN operations should use index lookups

3. Resource Usage:
   - Reduced CPU usage during query execution
   - Lower memory consumption for temporary tables
   - Decreased I/O operations

4. Scalability:
   - Performance should remain consistent with larger date ranges
   - Concurrent queries should not significantly impact performance
   - Index maintenance overhead should be minimal

5. Data Accuracy:
   - All results should match original function output exactly
   - Calculations should be mathematically identical
   - No data loss or corruption should occur
*/

-- =====================================================
-- Test Cleanup
-- =====================================================

-- Reset profiling
SET profiling = 0;

SELECT 'PeriodDifferenceSales Optimization Tests Completed' as status;