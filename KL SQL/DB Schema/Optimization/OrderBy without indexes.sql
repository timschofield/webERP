pakricard: in #codebase: search for all ORDER BY in SQL statements and list me all the ORDER BY that do NOT have a correct index in the DB Schema, located in  KL SQL/DB Squema folder

GitHub Copilot: 

I'll search through the codebase for all ORDER BY clauses in SQL statements and check them against the database schema in your active file to identify which ones lack proper indexes.

Let me analyze the SQL statements in your codebase and cross-reference them with the indexes defined in your kl_erp.sql schema file.

Based on my analysis of the codebase and your database schema, here are the ORDER BY clauses that do **NOT** have proper supporting indexes:

## Missing Indexes for ORDER BY Clauses

### 1. **Table: `salesorders`**
```sql
-- Found in multiple files
ORDER BY orddate DESC, orderno DESC
```
**Missing Index:** `idx_salesorders_orddate_orderno_desc` (both DESC)
**Current Schema:** Has `uk_salesorders_orddate_debtorno_orderno` but not optimized for DESC ordering

### 2. **Table: `debtortrans`**
```sql
-- Found in customer statements and reports
ORDER BY trandate DESC, id DESC
```
**Missing Index:** `idx_debtortrans_trandate_id_desc` (both DESC)

### 3. **Table: `stockmoves`**
```sql
-- Found in stock movement reports
ORDER BY trandate DESC, stkmoveno DESC
```
**Missing Index:** `idx_stockmoves_trandate_stkmoveno_desc` (both DESC)

### 4. **Table: `purchorders`**
```sql
-- Found in purchase order listings
ORDER BY orddate DESC, status, klstatus
```
**Missing Index:** `idx_purchorders_orddate_status_klstatus_desc`
**Current Schema:** Has `idx_orddate_status_klstatus` but not optimized for DESC on orddate

### 5. **Table: `banktrans`**
```sql
-- Found in bank reconciliation
ORDER BY transdate DESC, banktransid DESC
```
**Missing Index:** `idx_banktrans_transdate_banktransid_desc` (both DESC)

### 6. **Table: `gltrans`**
```sql
-- Found in GL reports
ORDER BY trandate DESC, counterindex DESC
```
**Missing Index:** `idx_gltrans_trandate_counterindex_desc` (both DESC)

### 7. **Table: `locstock`**
```sql
-- Found in reorder level reports
ORDER BY reorderlevel DESC, quantity ASC, stockid
```
**Missing Index:** `idx_locstock_reorderlevel_quantity_stockid_mixed`
**Note:** Current schema has `uk_locstock_reorderlevel_loccode_stockid` but not optimized for this specific ordering

### 8. **Table: `klsalesperformance`**
```sql
-- Found in performance analytics
ORDER BY topsales30 ASC, valuesales30 DESC
ORDER BY topsales60 ASC, valuesales60 DESC  
ORDER BY topsales90 ASC, valuesales90 DESC
```
**Missing Indexes:** Mixed ASC/DESC indexes for these combinations

### 9. **Table: `locations`**
```sql
-- Found in smart dispatch functions
ORDER BY priority ASC, loccode
```
**Current Schema:** Has `idx_locations_typeloc_priorityASC_loccode` but may need location-specific optimization

### 10. **Table: `stockmaster`**
```sql
-- Found in product listings
ORDER BY description ASC, stockid
ORDER BY categoryid, discontinued, description
```
**Missing Index:** `idx_stockmaster_categoryid_discontinued_description`

## Recommended Index Creation Script

````sql
-- Performance optimization indexes for ORDER BY clauses

-- 1. Sales Orders - DESC ordering optimization
CREATE INDEX idx_salesorders_orddate_orderno_desc ON salesorders (orddate DESC, orderno DESC);

-- 2. Debtor Transactions - DESC ordering optimization  
CREATE INDEX idx_debtortrans_trandate_id_desc ON debtortrans (trandate DESC, id DESC);

-- 3. Stock Moves - DESC ordering optimization
CREATE INDEX idx_stockmoves_trandate_stkmoveno_desc ON stockmoves (trandate DESC, stkmoveno DESC);

-- 4. Purchase Orders - DESC orddate with status
CREATE INDEX idx_purchorders_orddate_desc_status_klstatus ON purchorders (orddate DESC, status, klstatus);

-- 5. Bank Transactions - DESC ordering optimization
CREATE INDEX idx_banktrans_transdate_banktransid_desc ON banktrans (transdate DESC, banktransid DESC);

-- 6. GL Transactions - DESC ordering optimization  
CREATE INDEX idx_gltrans_trandate_counterindex_desc ON gltrans (trandate DESC, counterindex DESC);

-- 7. Stock Master - Category and description ordering
CREATE INDEX idx_stockmaster_categoryid_discontinued_description ON stockmaster (categoryid, discontinued, description);

-- 8. Location Stock - Mixed ordering optimization
CREATE INDEX idx_locstock_reorderlevel_desc_quantity_stockid ON locstock (reorderlevel DESC, quantity ASC, stockid);
````

## High Priority Missing Indexes

The most critical missing indexes based on likely query frequency:

1. **`idx_salesorders_orddate_orderno_desc`** - Used in order listings
2. **`idx_debtortrans_trandate_id_desc`** - Used in customer statements  
3. **`idx_stockmoves_trandate_stkmoveno_desc`** - Used in stock reports
4. **`idx_purchorders_orddate_desc_status_klstatus`** - Used in PO management

These indexes should be created first as they likely impact the most frequently used queries in the system.