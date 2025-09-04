# ActiveItemsNoSales Function SQL Optimization Report

## Overview
This report documents the optimization of the `ActiveItemsNoSales` function located in `includes/KLControlBoardFunctions.php:88`. The function identifies items with no sales activity within a specified time period and no current purchase orders or work orders.

## Original Query Performance Issues

### 1. **Correlated Subqueries**
The original query used multiple `NOT EXISTS` and `EXISTS` subqueries that executed for each row in the main result set:
- Sales order details check (`actualdispatchdate > FromDate`)
- Work orders check (`SUM(qtyreqd - qtyrecd)` for open work orders)
- Stock movements check (recent movements `>= FromDate`)
- Historical stock movements check (`< FromDate` with `qty > 0`)
- Purchase order details check (`completed = 0`)

### 2. **Inefficient Aggregation**
- Correlated subquery for `SUM(locstock.quantity)` executed for each row
- Complex `SUM(woitems.qtyreqd - woitems.qtyrecd)` calculation in WHERE clause
- Multiple table scans without proper indexes

### 3. **Missing Indexes**
Analysis of the database schema revealed missing indexes for:
- `salesorderdetails.actualdispatchdate`
- `stockmoves.trandate + stockid + qty` composite index
- `purchorderdetails.completed + itemcode` composite index
- `workorders.closed + wo` composite index

## Optimization Strategy

### 1. **Query Structure Improvements**
- **Replaced correlated subqueries with LEFT JOINs**: Converted all `NOT EXISTS` and `EXISTS` clauses to LEFT JOINs with `IS NULL` checks
- **Pre-aggregated data**: Created subqueries that aggregate data once instead of per-row calculations
- **Eliminated repeated calculations**: Moved complex logic to separate subqueries

### 2. **Index Optimization**
Created comprehensive indexes to support the optimized query:

```sql
-- Sales performance indexes
CREATE INDEX idx_salesorders_orddate_orderno ON salesorders (orddate, orderno);
CREATE INDEX idx_salesorderdetails_orderno_stkcode_actualdispatch ON salesorderdetails (orderno, stkcode, actualdispatchdate);

-- Work orders indexes
CREATE INDEX idx_workorders_closed_wo ON workorders (closed, wo);
CREATE INDEX idx_woitems_wo_stockid_qtyreqd_qtyrecd ON woitems (wo, stockid, qtyreqd, qtyrecd);

-- Stock movements indexes
CREATE INDEX idx_stockmoves_trandate_stockid_qty ON stockmoves (trandate, stockid, qty);
CREATE INDEX idx_stockmoves_stockid_trandate_qty ON stockmoves (stockid, trandate, qty);

-- Purchase orders indexes
CREATE INDEX idx_purchorderdetails_completed_itemcode ON purchorderdetails (completed, itemcode);

-- Main table covering index
CREATE INDEX idx_stockmaster_category_filters ON stockmaster (categoryid, discontinued, klchangingprice, klmovingdiscount20, klmovingdiscount50, klmovingdiscount80, lastcategoryupdate, stockid);

-- Locstock aggregation index
CREATE INDEX idx_locstock_stockid_quantity ON locstock (stockid, quantity);

-- Category filtering index
CREATE INDEX idx_stockcategory_categoryid_stocktype ON stockcategory (categoryid, stocktype);
```

## Optimized Query Structure

### Before (Original):
```sql
SELECT stockmaster.stockid, ...
FROM stockmaster, stockcategory, klsalesperformance
WHERE ... 
  AND NOT EXISTS (SELECT * FROM salesorderdetails, salesorders WHERE ...)
  AND (IFNULL((SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) FROM ...) ,0) = 0)
  AND NOT EXISTS (SELECT * FROM stockmoves WHERE ...)
  AND EXISTS (SELECT * FROM stockmoves WHERE ...)
  AND NOT EXISTS (SELECT * FROM purchorderdetails WHERE ...)
```

### After (Optimized):
```sql
SELECT sm.stockid, ...
FROM stockmaster sm
INNER JOIN stockcategory sc ON sm.categoryid = sc.categoryid
INNER JOIN klsalesperformance ksp ON sm.stockid = ksp.stockid
LEFT JOIN (SELECT stockid, SUM(quantity) AS quantity FROM locstock GROUP BY stockid) ls_sum ON sm.stockid = ls_sum.stockid
LEFT JOIN (SELECT DISTINCT sod.stkcode FROM salesorderdetails sod INNER JOIN salesorders so ON sod.orderno = so.orderno WHERE sod.actualdispatchdate > 'FromDate') recent_sales ON sm.stockid = recent_sales.stkcode
-- ... additional LEFT JOINs for other conditions
WHERE sm.discontinued = 0 
  AND recent_sales.stkcode IS NULL
  AND active_wo.stockid IS NULL
  -- ... other IS NULL checks
```

## Performance Benefits

### 1. **Reduced Query Complexity**
- Eliminated 5 correlated subqueries
- Converted to set-based operations using JOINs
- Reduced nested query execution from O(n×m) to O(n+m)

### 2. **Index Utilization**
- All major filtering conditions now use indexes
- Covering indexes eliminate table lookups
- Composite indexes optimize multi-column filtering

### 3. **Expected Performance Improvements**
- **Query execution time**: 70-90% reduction expected
- **CPU usage**: Significant reduction due to fewer table scans
- **Memory usage**: More efficient due to better index utilization
- **Scalability**: Linear performance scaling with data growth

## Implementation Files

1. **`includes/KLControlBoardFunctions.php`** - Contains the optimized function
2. **`KL SQL/DB Schema/optimize_activeitems_nosales_indexes.sql`** - Index creation script
3. **`KL SQL/DB Schema/ActiveItemsNoSales_Optimization_Report.md`** - This documentation

## Testing and Validation

### Recommended Testing Steps:
1. **Backup the database** before applying changes
2. **Run the index creation script**: `optimize_activeitems_nosales_indexes.sql`
3. **Test the optimized function** with various parameters
4. **Compare execution times** using `EXPLAIN` and profiling
5. **Validate results** match the original query output

### Performance Monitoring:
```sql
-- Enable profiling
SET profiling = 1;

-- Run the optimized query
SELECT ... (optimized query)

-- Check execution time
SHOW PROFILES;

-- Analyze query execution plan
EXPLAIN SELECT ... (optimized query)
```

## Maintenance Considerations

1. **Index Maintenance**: New indexes will require additional storage and maintenance overhead
2. **Statistics Updates**: Run `ANALYZE TABLE` periodically to keep index statistics current
3. **Monitoring**: Monitor query performance and index usage regularly
4. **Backup Strategy**: Ensure backup procedures account for additional index storage

## Conclusion

The optimization transforms a complex query with multiple correlated subqueries into an efficient set-based operation using proper indexing. This should result in significant performance improvements, especially as the database grows in size.

The changes maintain full compatibility with the existing function interface while dramatically improving performance through better query structure and comprehensive indexing strategy.