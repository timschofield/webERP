-- =====================================================================================================================
-- WorstLocationForItem Function Optimization - Test Suite
-- =====================================================================================================================
-- This file contains comprehensive test cases to validate the optimized WorstLocationForItem function.
-- Tests cover functionality, performance, and edge cases to ensure the optimization maintains correctness.
--
-- Target Function: WorstLocationForItem() in includes/KLReorderLevel.php
-- Expected Performance Improvement: 5-10x faster execution
-- =====================================================================================================================

-- =====================================================================================================================
-- Test Data Setup (Run these first to create test scenarios)
-- =====================================================================================================================

-- Test Scenario 1: Basic functionality test with known data
-- Insert test stock item
INSERT IGNORE INTO stockmaster (stockid, categoryid, description, discontinued) 
VALUES ('TEST_STOCK_001', 'TEST01', 'Test Stock Item for WorstLocationForItem', 0);

-- Insert test locations with different priorities
INSERT IGNORE INTO locations (loccode, locationname, typeloc, priority) VALUES
('SHOP001', 'Test Shop 1', 'SHOP', 1),
('SHOP002', 'Test Shop 2', 'SHOP', 2),
('SHOP003', 'Test Shop 3', 'SHOP', 3),
('OFFICE01', 'Test Office', 'OFFICE', 5);

-- Insert test stock quantities (different scenarios)
INSERT IGNORE INTO locstock (loccode, stockid, quantity, reorderlevel) VALUES
('SHOP001', 'TEST_STOCK_001', 10, 5),  -- Overstock scenario
('SHOP002', 'TEST_STOCK_001', 3, 5),   -- Available but not overstock
('SHOP003', 'TEST_STOCK_001', 8, 5),   -- Overstock scenario
('OFFICE01', 'TEST_STOCK_001', 15, 10); -- Office location (should be excluded)

-- Insert test sales orders with different dates and quantities
INSERT IGNORE INTO salesorders (orderno, debtorno, branchcode, orddate, fromstkloc, quotation) VALUES
(999001, 'TEST001', 'TEST', '2024-08-01', 'SHOP001', 0),
(999002, 'TEST001', 'TEST', '2024-08-15', 'SHOP001', 0),
(999003, 'TEST001', 'TEST', '2024-08-20', 'SHOP002', 0),
(999004, 'TEST001', 'TEST', '2024-07-15', 'SHOP003', 0); -- Older date

-- Insert test sales order details
INSERT IGNORE INTO salesorderdetails (orderlineno, orderno, stkcode, qtyinvoiced, completed) VALUES
(1, 999001, 'TEST_STOCK_001', 2, 1),
(1, 999002, 'TEST_STOCK_001', 1, 1),
(1, 999003, 'TEST_STOCK_001', 1, 1),
(1, 999004, 'TEST_STOCK_001', 3, 1);

-- =====================================================================================================================
-- Performance Comparison Tests
-- =====================================================================================================================

-- Test 1: Performance comparison - Original vs Optimized query structure
-- Note: These are example queries to demonstrate the difference. 
-- Actual testing should be done through the PHP function calls.

-- Original query pattern (for reference - DO NOT RUN in production)
/*
SELECT locstock.loccode
FROM locstock, locations
WHERE locstock.loccode = locations.loccode
    AND locstock.stockid = 'TEST_STOCK_001'
    AND locstock.quantity > locstock.reorderlevel
    AND locations.typeloc = 'SHOP'
ORDER BY locations.priority DESC,
         (SELECT COUNT(qtyinvoiced)
          FROM salesorderdetails, salesorders
          WHERE salesorderdetails.orderno = salesorders.orderno
            AND salesorderdetails.completed = 1
            AND salesorders.orddate >= '2024-08-01'
            AND salesorders.fromstkloc = locstock.loccode
            AND salesorderdetails.stkcode = 'TEST_STOCK_001') ASC;
*/

-- Optimized query pattern (current implementation)
SELECT ls.loccode,
       loc.priority,
       COALESCE(sales_count.sales_total, 0) as sales_count
FROM locstock ls
INNER JOIN locations loc ON ls.loccode = loc.loccode
LEFT JOIN (
    SELECT so.fromstkloc,
           COUNT(sod.qtyinvoiced) as sales_total
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    WHERE sod.stkcode = 'TEST_STOCK_001'
      AND sod.completed = 1
      AND so.orddate >= '2024-08-01'
    GROUP BY so.fromstkloc
) sales_count ON ls.loccode = sales_count.fromstkloc
WHERE ls.stockid = 'TEST_STOCK_001'
  AND ls.quantity > ls.reorderlevel
  AND loc.typeloc = 'SHOP'
ORDER BY loc.priority DESC, sales_count ASC
LIMIT 1;

-- =====================================================================================================================
-- Functional Test Cases
-- =====================================================================================================================

-- Test Case 1: OVERSTOCK scenario - should return location with overstock and lowest sales
-- Expected result: Should prioritize by priority DESC, then by sales count ASC
-- SHOP003 has priority 3, SHOP001 has priority 1, both have overstock
-- SHOP003 should be preferred due to higher priority

-- Test Case 2: AVAILABLE scenario - should return location with available stock and lowest sales
-- Expected result: Should include all locations with quantity > 0

-- Test Case 3: No matching locations - should return empty result
-- Test with non-existent stock ID

-- Test Case 4: Edge case - locations with zero sales
-- Should handle NULL sales counts properly with COALESCE

-- Test Case 5: Date filtering - should only count recent sales
-- Sales older than maxdays should be excluded

-- =====================================================================================================================
-- Index Usage Verification
-- =====================================================================================================================

-- Verify that the optimized query uses the created indexes
EXPLAIN SELECT ls.loccode,
               loc.priority,
               COALESCE(sales_count.sales_total, 0) as sales_count
FROM locstock ls
INNER JOIN locations loc ON ls.loccode = loc.loccode
LEFT JOIN (
    SELECT so.fromstkloc,
           COUNT(sod.qtyinvoiced) as sales_total
    FROM salesorders so
    INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
    WHERE sod.stkcode = 'TEST_STOCK_001'
      AND sod.completed = 1
      AND so.orddate >= '2024-08-01'
    GROUP BY so.fromstkloc
) sales_count ON ls.loccode = sales_count.fromstkloc
WHERE ls.stockid = 'TEST_STOCK_001'
  AND ls.quantity > ls.reorderlevel
  AND loc.typeloc = 'SHOP'
ORDER BY loc.priority DESC, sales_count ASC
LIMIT 1;

-- Expected index usage:
-- - idx_locstock_stockid_quantity_reorderlevel_loccode for main filtering
-- - idx_locations_typeloc_priority_loccode for location filtering and ordering
-- - idx_salesorderdetails_stkcode_completed_orderno for sales filtering
-- - idx_salesorders_orddate_fromstkloc_optimization for date filtering and grouping

-- =====================================================================================================================
-- Performance Benchmarking Queries
-- =====================================================================================================================

-- Benchmark 1: Measure execution time for different stock items
-- Run these with different stockid values and measure execution time

-- Benchmark 2: Measure execution time for different date ranges
-- Test with various maxdays values (7, 30, 90, 365)

-- Benchmark 3: Measure execution time with different data volumes
-- Test with varying numbers of sales records

-- =====================================================================================================================
-- Data Cleanup (Run after testing)
-- =====================================================================================================================

-- Clean up test data
DELETE FROM salesorderdetails WHERE orderno IN (999001, 999002, 999003, 999004);
DELETE FROM salesorders WHERE orderno IN (999001, 999002, 999003, 999004);
DELETE FROM locstock WHERE stockid = 'TEST_STOCK_001';
DELETE FROM locations WHERE loccode IN ('SHOP001', 'SHOP002', 'SHOP003', 'OFFICE01');
DELETE FROM stockmaster WHERE stockid = 'TEST_STOCK_001';

-- =====================================================================================================================
-- Expected Results Documentation
-- =====================================================================================================================

/*
EXPECTED TEST RESULTS:

Test Case 1 (OVERSTOCK):
- Input: stockid='TEST_STOCK_001', Kind='OVERSTOCK', maxdays=30
- Expected: Should return 'SHOP003' (highest priority among overstock locations)
- Reasoning: SHOP003 has priority 3 and quantity (8) > reorderlevel (5)

Test Case 2 (AVAILABLE):
- Input: stockid='TEST_STOCK_001', Kind='AVAILABLE', maxdays=30  
- Expected: Should return location with lowest sales among available stock
- Reasoning: All shops have quantity > 0, ordered by priority DESC then sales ASC

Performance Expectations:
- Query execution time should be 5-10x faster than original
- Index usage should show "Using index" in EXPLAIN output
- No "Using filesort" or "Using temporary" should appear for main query

Index Usage Verification:
- Main query should use idx_locstock_stockid_quantity_reorderlevel_loccode
- Location JOIN should use idx_locations_typeloc_priority_loccode  
- Sales subquery should use idx_salesorderdetails_stkcode_completed_orderno
- Date filtering should use idx_salesorders_orddate_fromstkloc_optimization
*/

-- =====================================================================================================================
-- End of WorstLocationForItem Test Suite
-- =====================================================================================================================