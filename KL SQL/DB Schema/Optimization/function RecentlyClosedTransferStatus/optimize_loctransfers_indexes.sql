-- Optimization for RecentlyClosedTransferStatus function
-- Created by Roo on 26/08/2025
-- This index optimizes date-based filtering and reference grouping

-- Add composite index for recdate filtering and reference grouping
ALTER TABLE `loctransfers` 
ADD INDEX `idx_loctransfers_recdate_reference` (`recdate`, `reference`);

-- This index will significantly improve performance for:
-- 1. WHERE recdate >= 'date' filtering
-- 2. GROUP BY reference operations
-- 3. ORDER BY recdate ASC, reference ASC sorting