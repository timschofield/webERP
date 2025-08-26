-- Additional index optimizations for POStatusControl function
-- Created by Roo on 26/08/2025
-- These indexes optimize Purchase Order status control queries

-- Index for supptrans table to optimize supplier balance queries
-- This will significantly improve performance when getting supplier balances

-- KL NOT EXECUTED AS WE ARE PLANNING TO USE A CALCULATED FIELD FOR upptrans.ovamount + supptrans.ovgst - supptrans.alloc
--CREATE INDEX idx_supptrans_supplierno_amounts ON supptrans (supplierno, ovamount, ovgst, alloc);


-- Alternative simpler index if the above is too complex
-- CREATE INDEX idx_supptrans_supplierno ON supptrans (supplierno);

-- Enhanced composite index for purchorders to optimize status-based queries
-- This covers the common query pattern: klstatus + payment terms + dates
CREATE INDEX idx_purchorders_klstatus_dates ON purchorders (klstatus, orddate, deliverydate, paymentdate, shipmentdate, customsdate, arrivaldate);

-- Alternative focused index for date-based filtering
-- CREATE INDEX idx_purchorders_klstatus_orddate ON purchorders (klstatus, orddate);

-- Composite index for purchorderdetails to optimize the main query JOIN
-- This covers the JOIN condition and filtering in POStatusControl
CREATE INDEX idx_purchorderdetails_orderno_completed_itemcode ON purchorderdetails (orderno, completed, itemcode);

-- Performance Notes:
-- 1. idx_supptrans_supplierno_amounts optimizes supplier balance calculations
-- 2. idx_purchorders_klstatus_dates covers multiple date-based status queries
-- 3. idx_purchorderdetails_orderno_completed_itemcode optimizes the main JOIN and filtering
-- 4. These indexes work with existing indexes to provide comprehensive coverage
-- 5. Query optimizer can choose the most appropriate index based on query conditions

-- Index usage analysis:
-- - Supplier balance queries will use idx_supptrans_supplierno_amounts
-- - Main PO query will use idx_purchorders_klstatus_dates and idx_purchorderdetails_orderno_completed_itemcode
-- - COGS calculations will use existing idx_gltrans_trandate_account index
-- - Existing indexes (idx_purchorders_supplierno, idx_orddate_status_klstatus) provide additional coverage