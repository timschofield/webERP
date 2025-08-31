-- =====================================================
-- INDEX OPTIMIZATION FOR index.php
-- =====================================================
-- 
-- This file contains the missing indexes needed to optimize
-- SQL SELECT statements found in index.php
--
-- Analysis Date: 2025-08-31
-- Target Database: kl_erp
-- 
-- =====================================================

-- -----------------------------------------------------
-- QUERY ANALYSIS
-- -----------------------------------------------------
-- 
-- Query found in index.php line 30:
-- SELECT value FROM session_data WHERE userid='" . $_SESSION['UserID'] . "' AND field='module'
--
-- Current Index: PRIMARY KEY (userid, value)
-- Issue: The query filters by userid AND field, but current index doesn't include field
-- Impact: Requires table scan of all rows matching userid to find the specific field
--
-- -----------------------------------------------------

-- -----------------------------------------------------
-- RECOMMENDED INDEX OPTIMIZATION
-- -----------------------------------------------------

-- Add composite index for optimal query performance
-- This index will allow the query to efficiently filter by both userid and field
ALTER TABLE `session_data` 
ADD INDEX `idx_session_data_userid_field` (`userid`, `field`);

-- -----------------------------------------------------
-- PERFORMANCE IMPACT
-- -----------------------------------------------------
-- 
-- Before: O(n) where n = number of session_data records per userid
-- After:  O(log n) direct index lookup
-- 
-- Expected improvement: 
-- - Faster session module retrieval on login
-- - Reduced I/O operations
-- - Better scalability as session_data grows
--
-- -----------------------------------------------------

-- -----------------------------------------------------
-- VERIFICATION QUERIES
-- -----------------------------------------------------
-- 
-- To verify the index is being used, run:
-- EXPLAIN SELECT value FROM session_data WHERE userid='test_user' AND field='module';
-- 
-- Expected result should show:
-- - key: idx_session_data_userid_field
-- - type: ref (not ALL or index)
--
-- -----------------------------------------------------