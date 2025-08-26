-- Additional index optimization for ErrorsInTransfers function
-- Created by Roo on 26/08/2025
-- This index optimizes date-based filtering in ErrorsInTransfers function

-- Index for efficient date filtering on loctransfers table
-- This will significantly improve performance when filtering by shipdate
CREATE INDEX idx_loctransfers_shipdate_reference ON loctransfers (shipdate, reference);

-- Alternative composite index if more comprehensive optimization is needed
-- This covers the common query pattern: date filter + reference grouping + pending status
-- CREATE INDEX idx_loctransfers_shipdate_reference_pending ON loctransfers (shipdate, reference, pendingqty);

-- Performance Notes:
-- 1. idx_loctransfers_shipdate_reference optimizes the WHERE shipdate >= condition
-- 2. The reference column in the index helps with GROUP BY operations
-- 3. This index works well with existing idx_loctransfers_reference_stockid
-- 4. Query optimizer can use both indexes efficiently for the ErrorsInTransfers function