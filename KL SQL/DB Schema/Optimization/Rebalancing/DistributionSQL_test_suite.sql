-- =====================================================================================
-- DistributionSQL Query Optimization - Test Suite
-- =====================================================================================
-- File: DistributionSQL_test_suite.sql
-- Purpose: Comprehensive test cases for the optimized DistributionSQL query
-- Location: RebalancingBetweenShops function, line 340 in KLReorderLevel.php
-- Date: 2025-01-31
-- =====================================================================================

-- =====================================================================================
-- TEST 1: Performance Comparison - Before vs After Optimization
-- =====================================================================================

-- Original Query (BEFORE optimization) - for performance comparison
-- Note: This is the old correlated subquery version
SET @test_stockid = 'TEST001';
SET @test_start_date = '2024-12-01';

SELECT 'BEFORE OPTIMIZATION - Original Query' as test_name;
EXPLAIN FORMAT=JSON

SELECT locstock.loccode, 
       locstock.reorderlevel AS oldrl
FROM locstock, locations
WHERE locstock.loccode = locations.loccode
  AND locstock.stockid = @test_stockid
  AND locations.typeloc IN ('SHOPKL', 'SHOPBL')
  AND locstock.reorderlevel > 0 
ORDER BY locations.priority ASC,
         (SELECT COUNT(qtyinvoiced)
          FROM salesorderdetails, salesorders
          WHERE salesorderdetails.orderno = salesorders.orderno
            AND salesorderdetails.completed = 1
            AND salesorders.orddate >= @test_start_date
            AND salesorders.fromstkloc = locstock.loccode) DESC;

-- Optimized Query (AFTER optimization)
SELECT 'AFTER OPTIMIZATION - Optimized Query' as test_name;
EXPLAIN FORMAT=JSON
SELECT ls.loccode, 
       ls.reorderlevel AS oldrl,
       COALESCE(sales_data.sales_count, 0) as sales_count
FROM locstock ls
INNER JOIN locations loc ON ls.loccode = loc.loccode
LEFT JOIN (
    SELECT so.fromstkloc,
           COUNT(sod.qtyinvoiced) as sales_count
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    WHERE sod.stkcode = @test_stockid
      AND sod.completed = 1
      AND so.orddate >= @test_start_date
    GROUP BY so.fromstkloc
) sales_data ON ls.loccode = sales_data.fromstkloc
WHERE ls.stockid = @test_stockid
  AND loc.typeloc IN ('SHOPKL', 'SHOPBL')
  AND ls.reorderlevel > 0 
ORDER BY loc.priority ASC,
         sales_data.sales_count DESC;

-- =====================================================================================
-- TEST 2: Functional Validation - Results Consistency
-- =====================================================================================

-- Test that both queries return identical results (excluding the new sales_count column)
SELECT 'FUNCTIONAL TEST - Results Consistency Check' as test_name;

-- Create temporary tables to compare results
CREATE TEMPORARY TABLE temp_original_results AS
SELECT locstock.loccode, 
       locstock.reorderlevel AS oldrl,
       (SELECT COUNT(qtyinvoiced)
        FROM salesorderdetails, salesorders
        WHERE salesorderdetails.orderno = salesorders.orderno
          AND salesorderdetails.completed = 1
          AND salesorders.orddate >= @test_start_date
          AND salesorders.fromstkloc = locstock.loccode) as sales_count
FROM locstock, locations
WHERE locstock.loccode = locations.loccode
  AND locstock.stockid = @test_stockid
  AND locations.typeloc IN ('SHOPKL', 'SHOPBL')
  AND locstock.reorderlevel > 0 
ORDER BY locations.priority ASC, sales_count DESC;

CREATE TEMPORARY TABLE temp_optimized_results AS
SELECT ls.loccode, 
       ls.reorderlevel AS oldrl,
       COALESCE(sales_data.sales_count, 0) as sales_count
FROM locstock ls
INNER JOIN locations loc ON ls.loccode = loc.loccode
LEFT JOIN (
    SELECT so.fromstkloc,
           COUNT(sod.qtyinvoiced) as sales_count
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    WHERE sod.stkcode = @test_stockid
      AND sod.completed = 1
      AND so.orddate >= @test_start_date
    GROUP BY so.fromstkloc
) sales_data ON ls.loccode = sales_data.fromstkloc
WHERE ls.stockid = @test_stockid
  AND loc.typeloc IN ('SHOPKL', 'SHOPBL')
  AND ls.reorderlevel > 0 
ORDER BY loc.priority ASC, sales_data.sales_count DESC;

-- Compare results
SELECT 
    CASE 
        WHEN orig.row_count = opt.row_count THEN 'PASS'
        ELSE 'FAIL'
    END as result_count_test,
    orig.row_count as original_rows,
    opt.row_count as optimized_rows
FROM 
    (SELECT COUNT(*) as row_count FROM temp_original_results) orig,
    (SELECT COUNT(*) as row_count FROM temp_optimized_results) opt;

-- Check for any differences in results
SELECT 
    CASE 
        WHEN COUNT(*) = 0 THEN 'PASS - Results identical'
        ELSE CONCAT('FAIL - ', COUNT(*), ' differences found')
    END as consistency_test
FROM (
    SELECT loccode, oldrl, sales_count FROM temp_original_results
    EXCEPT
    SELECT loccode, oldrl, sales_count FROM temp_optimized_results
    UNION ALL
    SELECT loccode, oldrl, sales_count FROM temp_optimized_results
    EXCEPT
    SELECT loccode, oldrl, sales_count FROM temp_original_results
) differences;

-- =====================================================================================
-- TEST 3: Index Usage Verification
-- =====================================================================================

SELECT 'INDEX USAGE TEST - Verify Optimal Index Usage' as test_name;

-- Test index usage for the main query components
EXPLAIN FORMAT=JSON
SELECT ls.loccode, ls.reorderlevel AS oldrl
FROM locstock ls
INNER JOIN locations loc ON ls.loccode = loc.loccode
WHERE ls.stockid = @test_stockid
  AND loc.typeloc IN ('SHOPKL', 'SHOPBL')
  AND ls.reorderlevel > 0;

-- Test index usage for the sales aggregation subquery
EXPLAIN FORMAT=JSON
SELECT so.fromstkloc, COUNT(sod.qtyinvoiced) as sales_count
FROM salesorders so
INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
WHERE sod.stkcode = @test_stockid
  AND sod.completed = 1
  AND so.orddate >= @test_start_date
GROUP BY so.fromstkloc;

-- =====================================================================================
-- TEST 4: Edge Cases and Data Validation
-- =====================================================================================

SELECT 'EDGE CASES TEST - Various Scenarios' as test_name;

-- Test 4.1: Stock item with no sales history
SET @test_stockid_no_sales = 'NOSALES001';
SELECT 'Test 4.1: Item with no sales history' as test_case;
SELECT ls.loccode, ls.reorderlevel AS oldrl, COALESCE(sales_data.sales_count, 0) as sales_count
FROM locstock ls
INNER JOIN locations loc ON ls.loccode = loc.loccode
LEFT JOIN (
    SELECT so.fromstkloc, COUNT(sod.qtyinvoiced) as sales_count
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    WHERE sod.stkcode = @test_stockid_no_sales
      AND sod.completed = 1
      AND so.orddate >= @test_start_date
    GROUP BY so.fromstkloc
) sales_data ON ls.loccode = sales_data.fromstkloc
WHERE ls.stockid = @test_stockid_no_sales
  AND loc.typeloc IN ('SHOPKL', 'SHOPBL')
  AND ls.reorderlevel > 0
ORDER BY loc.priority ASC, sales_data.sales_count DESC;

-- Test 4.2: Stock item with zero reorder levels
SET @test_stockid_zero_rl = 'ZERORL001';
SELECT 'Test 4.2: Item with zero reorder levels (should return no rows)' as test_case;
SELECT COUNT(*) as row_count_should_be_zero
FROM locstock ls
INNER JOIN locations loc ON ls.loccode = loc.loccode
WHERE ls.stockid = @test_stockid_zero_rl
  AND loc.typeloc IN ('SHOPKL', 'SHOPBL')
  AND ls.reorderlevel > 0;

-- Test 4.3: Different date ranges
SELECT 'Test 4.3: Different date ranges' as test_case;
SET @test_date_30_days = DATE_SUB(CURDATE(), INTERVAL 30 DAY);
SET @test_date_60_days = DATE_SUB(CURDATE(), INTERVAL 60 DAY);

SELECT 'Last 30 days' as period, COUNT(*) as locations_with_sales
FROM (
    SELECT so.fromstkloc
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    WHERE sod.stkcode = @test_stockid
      AND sod.completed = 1
      AND so.orddate >= @test_date_30_days
    GROUP BY so.fromstkloc
) sales_30;

SELECT 'Last 60 days' as period, COUNT(*) as locations_with_sales
FROM (
    SELECT so.fromstkloc
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    WHERE sod.stkcode = @test_stockid
      AND sod.completed = 1
      AND so.orddate >= @test_date_60_days
    GROUP BY so.fromstkloc
) sales_60;

-- =====================================================================================
-- TEST 5: Performance Benchmarking
-- =====================================================================================

SELECT 'PERFORMANCE BENCHMARK - Execution Time Comparison' as test_name;

-- Benchmark the optimized query with different stock items
SET @benchmark_start = NOW(6);

SELECT ls.loccode, ls.reorderlevel AS oldrl, COALESCE(sales_data.sales_count, 0) as sales_count
FROM locstock ls
INNER JOIN locations loc ON ls.loccode = loc.loccode
LEFT JOIN (
    SELECT so.fromstkloc, COUNT(sod.qtyinvoiced) as sales_count
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    WHERE sod.stkcode = @test_stockid
      AND sod.completed = 1
      AND so.orddate >= @test_start_date
    GROUP BY so.fromstkloc
) sales_data ON ls.loccode = sales_data.fromstkloc
WHERE ls.stockid = @test_stockid
  AND loc.typeloc IN ('SHOPKL', 'SHOPBL')
  AND ls.reorderlevel > 0
ORDER BY loc.priority ASC, sales_data.sales_count DESC;

SET @benchmark_end = NOW(6);
SELECT TIMESTAMPDIFF(MICROSECOND, @benchmark_start, @benchmark_end) as execution_time_microseconds;

-- =====================================================================================
-- TEST 6: Data Integrity Validation
-- =====================================================================================

SELECT 'DATA INTEGRITY TEST - Validate Query Logic' as test_name;

-- Test 6.1: Verify that sales_count matches manual calculation
SELECT 'Test 6.1: Sales count accuracy verification' as test_case;
SELECT 
    ls.loccode,
    COALESCE(sales_data.sales_count, 0) as calculated_sales_count,
    (SELECT COUNT(sod.qtyinvoiced)
     FROM salesorders so2
     INNER JOIN salesorderdetails sod ON so2.orderno = sod.orderno
     WHERE sod.stkcode = @test_stockid
       AND sod.completed = 1
       AND so2.orddate >= @test_start_date
       AND so2.fromstkloc = ls.loccode) as manual_sales_count,
    CASE 
        WHEN COALESCE(sales_data.sales_count, 0) = 
             (SELECT COUNT(sod.qtyinvoiced)
              FROM salesorders so2
              INNER JOIN salesorderdetails sod ON so2.orderno = sod.orderno
              WHERE sod.stkcode = @test_stockid
                AND sod.completed = 1
                AND so2.orddate >= @test_start_date
                AND so2.fromstkloc = ls.loccode)
        THEN 'PASS'
        ELSE 'FAIL'
    END as accuracy_test
FROM locstock ls
INNER JOIN locations loc ON ls.loccode = loc.loccode
LEFT JOIN (
    SELECT so.fromstkloc, COUNT(sod.qtyinvoiced) as sales_count
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    WHERE sod.stkcode = @test_stockid
      AND sod.completed = 1
      AND so.orddate >= @test_start_date
    GROUP BY so.fromstkloc
) sales_data ON ls.loccode = sales_data.fromstkloc
WHERE ls.stockid = @test_stockid
  AND loc.typeloc IN ('SHOPKL', 'SHOPBL')
  AND ls.reorderlevel > 0
ORDER BY loc.priority ASC;

-- =====================================================================================
-- CLEANUP
-- =====================================================================================

DROP TEMPORARY TABLE IF EXISTS temp_original_results;
DROP TEMPORARY TABLE IF EXISTS temp_optimized_results;

SELECT 'TEST SUITE COMPLETED - Review results above for optimization validation' as final_message;

-- =====================================================================================
-- Expected Results Summary:
-- =====================================================================================
-- 1. Performance Test: Optimized query should show better execution plan
-- 2. Functional Test: Both queries should return identical results
-- 3. Index Usage: Should utilize the new composite indexes effectively
-- 4. Edge Cases: Should handle various scenarios correctly
-- 5. Benchmark: Should show improved execution time
-- 6. Data Integrity: Sales counts should match manual calculations
-- =====================================================================================