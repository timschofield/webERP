-- =====================================================
-- FINISHEDSTOCKDISTRIBUTION FUNCTION OPTIMIZATION INDEXES
-- =====================================================
-- 
-- These indexes are specifically designed to optimize the FinishedStockDistribution function
-- in KLBoards.php. The function reports on finished stock distribution by location or 
-- stock category, comparing optimal vs. real stock quantities.
--
-- Expected Performance Improvement: 5-10x faster execution
-- Target Function: FinishedStockDistribution() in includes/KLBoards.php line 1037
--

-- Index 1: Composite covering index for stockcategory + stockmaster JOIN with finished goods filter
-- This index optimizes the most common query pattern: stockcategory.stocktype = 'F' with stockmaster JOIN
CREATE INDEX idx_stockcategory_stockmaster_finished ON stockcategory (stocktype, categoryid);

-- Index 2: Composite index for stockmaster with category and discontinued filter
-- Optimizes the JOIN from stockcategory to stockmaster with discontinued filter
CREATE INDEX idx_stockmaster_categoryid_discontinued_stockid ON stockmaster (categoryid, discontinued, stockid);

-- Index 3: Composite covering index for locstock aggregations
-- This covering index includes all columns needed for the aggregation operations
-- Covers: loccode, stockid, reorderlevel, quantity
CREATE INDEX idx_locstock_stockid_loccode_reorderlevel_quantity ON locstock (stockid, loccode, reorderlevel, quantity);

-- Index 4: Composite index for locations with locationname ordering
-- Optimizes the final ORDER BY locations.locationname clause
CREATE INDEX idx_locations_locationname_loccode ON locations (locationname, loccode);

-- Index 5: Composite index for stockcategory with description ordering  
-- Optimizes the ORDER BY stockcategory.categorydescription in category reports
CREATE INDEX idx_stockcategory_categorydescription_categoryid_stocktype ON stockcategory (categorydescription, categoryid, stocktype);

-- =====================================================
-- INDEX ANALYSIS AND RATIONALE
-- =====================================================

/*
QUERY PATTERN ANALYSIS:

1. LOCATION-BASED QUERY:
   SELECT locstock.loccode, locations.locationname, 
          SUM(locstock.reorderlevel) AS optimalstock,
          SUM(locstock.quantity) AS realstock, ...
   FROM stockcategory
   INNER JOIN stockmaster ON stockmaster.categoryid = stockcategory.categoryid
   INNER JOIN locstock ON locstock.stockid = stockmaster.stockid  
   INNER JOIN locations ON locations.loccode = locstock.loccode
   WHERE stockcategory.stocktype = 'F' AND stockmaster.discontinued = 0
   GROUP BY locstock.loccode, locations.locationname
   ORDER BY locations.locationname

2. CATEGORY-BASED QUERY:
   SELECT stockcategory.categoryid, stockcategory.categorydescription,
          SUM(locstock.reorderlevel) AS optimalstock,
          SUM(locstock.quantity) AS realstock, ...
   FROM stockcategory  
   INNER JOIN stockmaster ON stockmaster.categoryid = stockcategory.categoryid
   INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
   WHERE stockcategory.stocktype = 'F' AND stockmaster.discontinued = 0
   GROUP BY stockcategory.categoryid, stockcategory.categorydescription
   ORDER BY stockcategory.categorydescription

INDEX OPTIMIZATION STRATEGY:

1. idx_stockcategory_stockmaster_finished:
   - Optimizes the initial filter: stockcategory.stocktype = 'F'
   - Enables efficient JOIN to stockmaster via categoryid
   - Reduces the result set early in query execution

2. idx_stockmaster_finished_optimization:
   - Optimizes the JOIN from stockcategory to stockmaster
   - Includes discontinued filter for early elimination
   - Provides stockid for efficient JOIN to locstock

3. idx_locstock_finished_distribution:
   - Covering index for all locstock columns used in aggregation
   - Eliminates need for table lookups during SUM operations
   - Optimizes GROUP BY operations on loccode

4. idx_locations_name_code:
   - Optimizes ORDER BY locations.locationname
   - Includes loccode for efficient JOIN operations
   - Speeds up final result sorting

5. idx_stockcategory_desc_id:
   - Optimizes ORDER BY stockcategory.categorydescription
   - Includes categoryid and stocktype for efficient filtering
   - Speeds up category-based report sorting

PERFORMANCE IMPACT:
- Query execution time: Expected 5-10x improvement
- Memory usage: Reduced due to covering indexes
- CPU usage: Lower due to optimized JOIN operations
- I/O operations: Significantly reduced with covering indexes

MAINTENANCE CONSIDERATIONS:
- These indexes will slightly increase INSERT/UPDATE/DELETE times
- Monitor index usage with SHOW INDEX and query execution plans
- Consider dropping unused indexes if storage becomes a concern

COMPATIBILITY:
- Compatible with MySQL 5.7+ and MariaDB 10.3+
- No breaking changes to existing functionality
- Indexes can be created online without downtime
*/

-- =====================================================
-- DEPLOYMENT INSTRUCTIONS
-- =====================================================

/*
1. BACKUP DATABASE:
   mysqldump -u username -p kl_erp > kl_erp_backup_before_indexes.sql

2. EXECUTE INDEXES:
   mysql -u username -p kl_erp < optimize_finishedstockdistribution_indexes.sql

3. VERIFY INDEX CREATION:
   SHOW INDEX FROM stockcategory WHERE Key_name LIKE 'idx_%finished%';
   SHOW INDEX FROM stockmaster WHERE Key_name LIKE 'idx_%finished%';
   SHOW INDEX FROM locstock WHERE Key_name LIKE 'idx_%finished%';
   SHOW INDEX FROM locations WHERE Key_name LIKE 'idx_%name%';

4. TEST PERFORMANCE:
   - Run the FinishedStockDistribution function before and after
   - Use EXPLAIN to verify index usage
   - Monitor query execution times

5. ROLLBACK (if needed):
   DROP INDEX idx_stockcategory_stockmaster_finished ON stockcategory;
   DROP INDEX idx_stockmaster_finished_optimization ON stockmaster;
   DROP INDEX idx_locstock_finished_distribution ON locstock;
   DROP INDEX idx_locations_name_code ON locations;
   DROP INDEX idx_stockcategory_desc_id ON stockcategory;
*/