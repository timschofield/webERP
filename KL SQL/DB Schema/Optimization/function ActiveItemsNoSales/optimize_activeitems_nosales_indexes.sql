-- =====================================================================================
-- SQL OPTIMIZATION SCRIPT FOR ActiveItemsNoSales FUNCTION
-- =====================================================================================
-- This script creates indexes to optimize the ActiveItemsNoSales function performance
-- Function location: includes/KLControlBoardFunctions.php:88
-- 
-- PERFORMANCE IMPROVEMENTS IMPLEMENTED:
-- 1. Replaced correlated subqueries with LEFT JOINs
-- 2. Pre-aggregated locstock quantities
-- 3. Added covering indexes for optimal query performance
-- 4. Eliminated repeated date calculations
-- =====================================================================================

-- Index 1: Optimize salesorderdetails + salesorders JOIN for recent sales check
-- This covers the LEFT JOIN for recent sales detection
CREATE INDEX IF NOT EXISTS idx_salesorders_orddate_orderno 
ON salesorders (orddate, orderno);

CREATE INDEX IF NOT EXISTS idx_salesorderdetails_orderno_stkcode_actualdispatch 
ON salesorderdetails (orderno, stkcode, actualdispatchdate);

-- Index 2: Optimize woitems + workorders JOIN for active work orders
-- This covers the LEFT JOIN for active work orders check
CREATE INDEX IF NOT EXISTS idx_workorders_closed_wo 
ON workorders (closed, wo);

CREATE INDEX IF NOT EXISTS idx_woitems_wo_stockid_qtyreqd_qtyrecd 
ON woitems (wo, stockid, qtyreqd, qtyrecd);

-- Index 3: Optimize stockmoves for date-based queries
-- This covers both recent and historical stock movements checks
CREATE INDEX IF NOT EXISTS idx_stockmoves_trandate_stockid_qty 
ON stockmoves (trandate, stockid, qty);

CREATE INDEX IF NOT EXISTS idx_stockmoves_stockid_trandate_qty 
ON stockmoves (stockid, trandate, qty);

-- Index 4: Optimize purchorderdetails for active purchase orders
-- This covers the LEFT JOIN for active purchase orders check
CREATE INDEX IF NOT EXISTS idx_purchorderdetails_completed_itemcode 
ON purchorderdetails (completed, itemcode);

-- Index 5: Optimize stockmaster for the main WHERE conditions
-- This is a covering index for the main table filtering
CREATE INDEX IF NOT EXISTS idx_stockmaster_category_filters 
ON stockmaster (categoryid, discontinued, klchangingprice, klmovingdiscount20, 
                klmovingdiscount50, klmovingdiscount80, lastcategoryupdate, stockid);

-- Index 6: Optimize locstock for quantity aggregation
-- This covers the pre-aggregation of locstock quantities
CREATE INDEX IF NOT EXISTS idx_locstock_stockid_quantity 
ON locstock (stockid, quantity);

-- Index 7: Optimize stockcategory for stocktype filtering
-- This covers the stocktype = 'F' condition
CREATE INDEX IF NOT EXISTS idx_stockcategory_categoryid_stocktype 
ON stockcategory (categoryid, stocktype);

-- =====================================================================================
-- PERFORMANCE ANALYSIS QUERIES
-- =====================================================================================
-- Use these queries to analyze the performance improvements:

-- 1. Check index usage for the optimized query
-- EXPLAIN SELECT ... (run the optimized query with EXPLAIN)

-- 2. Compare execution times before and after optimization
-- SET profiling = 1;
-- SELECT ... (run original query)
-- SELECT ... (run optimized query)
-- SHOW PROFILES;

-- 3. Check index statistics
-- SHOW INDEX FROM salesorders;
-- SHOW INDEX FROM salesorderdetails;
-- SHOW INDEX FROM stockmoves;
-- SHOW INDEX FROM purchorderdetails;
-- SHOW INDEX FROM stockmaster;
-- SHOW INDEX FROM locstock;
-- SHOW INDEX FROM woitems;
-- SHOW INDEX FROM workorders;

-- =====================================================================================
-- MAINTENANCE NOTES
-- =====================================================================================
-- 1. Monitor index usage with: 
--    SELECT * FROM information_schema.INDEX_STATISTICS WHERE table_schema = 'kl_erp';
--
-- 2. These indexes will increase INSERT/UPDATE/DELETE overhead slightly but will
--    dramatically improve SELECT performance for the ActiveItemsNoSales function
--
-- 3. Consider running ANALYZE TABLE after creating indexes:
--    ANALYZE TABLE salesorders, salesorderdetails, stockmoves, purchorderdetails, 
--                  stockmaster, locstock, woitems, workorders, stockcategory;
--
-- 4. Monitor disk space usage as these indexes will require additional storage
-- =====================================================================================

-- Optional: Drop old unused indexes if they exist and are not needed
-- (Uncomment only after verifying they are not used by other queries)
-- DROP INDEX IF EXISTS old_index_name ON table_name;

-- Analyze tables to update statistics after index creation
ANALYZE TABLE salesorders, salesorderdetails, stockmoves, purchorderdetails, 
             stockmaster, locstock, woitems, workorders, stockcategory;

-- Show completion message
SELECT 'ActiveItemsNoSales optimization indexes created successfully!' AS Status;