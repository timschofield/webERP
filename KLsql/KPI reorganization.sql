
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("CASH-PTADU", "Cash PT ADU");
UPDATE klkpi SET class = "CASH-PTADU", concept = "" WHERE class = "Cash" AND concept = "Cash PT ADU";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("CASH-PTBB", "Cash PT BB");
UPDATE klkpi SET class = "CASH-PTBB", concept = "" WHERE class = "Cash" AND concept = "Cash PT BB";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("CASH-PTSMH", "Cash PT SMH");
UPDATE klkpi SET class = "CASH-PTSMH", concept = "" WHERE class = "Cash" AND concept = "Cash PT SMH";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("CASH-FREE", "Cash Free");
UPDATE klkpi SET class = "CASH-FREE", concept = "" WHERE class = "Cash" AND concept = "Free Cash";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("COMP-ANY-ITEM-IDR", "Components for any items (IDR)");
UPDATE klkpi SET class = "COMP-ANY-ITEM-IDR", concept = "" WHERE class = "Components" AND concept = "Components for any items (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("COMP-DISC-ITEM-IDR", "Components for Discount items (IDR)");
UPDATE klkpi SET class = "COMP-DISC-ITEM-IDR", concept = "" WHERE class = "Components" AND concept = "Components for Discount items (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("COMP-NOT-USED-IDR", "Components not used in any BOM (IDR)");
UPDATE klkpi SET class = "COMP-NOT-USED-IDR", concept = "" WHERE class = "Components" AND concept = "Components not used in any BOM (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("COMP-ONLY-DISC-ITEM-IDR", "Components ONLY for Discount items (IDR)");
UPDATE klkpi SET class = "COMP-ONLY-DISC-ITEM-IDR", concept = "" WHERE class = "Components" AND concept = "Components ONLY for Discount items (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("MAINTENANCE-ALL-30", "Maintenance All Tasks during 30 days");
UPDATE klkpi SET class = "MAINTENANCE-ALL-30", concept = "" WHERE class = "Maintenance" AND concept = "All Maintenance Tasks during 30 days";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("MAINTENANCE-CLOSED-30", "Maintenance Closed Tasks during 30 days");
UPDATE klkpi SET class = "MAINTENANCE-CLOSED-30", concept = "" WHERE class = "Maintenance" AND concept = "Closed Maintenance Tasks during 30 days";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("MAINTENANCE-OPEN", "Maintenance Open Tasks");
UPDATE klkpi SET class = "MAINTENANCE-OPEN", concept = "" WHERE class = "Maintenance" AND concept = "Open Maintenance Tasks";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PACK-DAILY-USE-PCS", "Packaging current daily use (PCS)");
UPDATE klkpi SET class = "PACK-DAILY-USE-PCS", concept = "" WHERE class = "Packaging" AND concept = "Packaging current daily use (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PACK-FORE-X-DAYS-PCS", "Packaging forecast next X days (PCS)");
UPDATE klkpi SET class = "PACK-FORE-X-DAYS-PCS", concept = "" WHERE class = "Packaging" AND concept = "Packaging forecast next X days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PACK-OPT-ORDER-PCS", "Packaging Optimum Order (PCS)");
UPDATE klkpi SET class = "PACK-OPT-ORDER-PCS", concept = "" WHERE class = "Packaging" AND concept = "Packaging Optimum Order (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PACK-OPT-QOH-PCS", "Packaging Optimum QOH (PCS)");
UPDATE klkpi SET class = "PACK-OPT-QOH-PCS", concept = "" WHERE class = "Packaging" AND concept = "Packaging Optimum QOH (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PACK-QOH-QOO-TOTAL-DAYS", "Packaging QOH + QOO total (DAYS)");
UPDATE klkpi SET class = "PACK-QOH-QOO-TOTAL-DAYS", concept = "" WHERE class = "Packaging" AND concept = "Packaging QOH + QOO total (DAYS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PACK-QOH-TOTAL-DAYS", "Packaging QOH total (DAYS)");
UPDATE klkpi SET class = "PACK-QOH-TOTAL-DAYS", concept = "" WHERE class = "Packaging" AND concept = "Packaging QOH total (DAYS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PACK-QOH-TOTAL-PCS", "Packaging QOH total (PCS)");
UPDATE klkpi SET class = "PACK-QOH-TOTAL-PCS", concept = "" WHERE class = "Packaging" AND concept = "Packaging QOH total (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PACK-QOO-NOTREC-PCS", "Packaging QOO not received (PCS)");
UPDATE klkpi SET class = "PACK-QOO-NOTREC-PCS", concept = "" WHERE class = "Packaging" AND concept = "Packaging QOO not received (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PACK-SHORTAGE-PERCENT", "Packaging Shortage (%)");
UPDATE klkpi SET class = "PACK-SHORTAGE-PERCENT", concept = "" WHERE class = "Packaging" AND concept = "Packaging Shortage (%)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PACK-SHORTAGE-PCS", "Packaging Shortage (PCS)");
UPDATE klkpi SET class = "PACK-SHORTAGE-PCS", concept = "" WHERE class = "Packaging" AND concept = "Packaging Shortage (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PACK-USED-30-PCS", "Packaging used last 30 days (PCS)");
UPDATE klkpi SET class = "PACK-USED-30-PCS", concept = "" WHERE class = "Packaging" AND concept = "Packaging used last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PRICE-ITEM-CHANGE-PRICE", "Pricing - Items changing price");
UPDATE klkpi SET class = "PRICE-ITEM-CHANGE-PRICE", concept = "" WHERE class = "Prices" AND concept = "Items changing price";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PRICE-ITEM-CHANGE-20D", "Pricing - Items moving to 20% discount");
UPDATE klkpi SET class = "PRICE-ITEM-CHANGE-20D", concept = "" WHERE class = "Prices" AND concept = "Items moving to 20% discount";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PRICE-ITEM-CHANGE-50D", "Pricing - Items moving to 50% discount");
UPDATE klkpi SET class = "PRICE-ITEM-CHANGE-50D", concept = "" WHERE class = "Prices" AND concept = "Items moving to 50% discount";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PRICE-ITEM-CHANGE-80D", "Pricing - Items moving to 80% discount");
UPDATE klkpi SET class = "PRICE-ITEM-CHANGE-80D", concept = "" WHERE class = "Prices" AND concept = "Items moving to 80% discount";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PRICE-ISSUES", "Pricing - Pricing Issues");
UPDATE klkpi SET class = "PRICE-ISSUES", concept = "" WHERE class = "Prices" AND concept = "Pricing Issues";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PO-PAY-PEND-75-IDR", "PO Payments pending items for sale in 75 days (IDR)");
UPDATE klkpi SET class = "PO-PAY-PEND-75-IDR", concept = "" WHERE class = "Purchase Orders" AND concept = "Payments pending items for sale in 75 days (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PO-ITEMS-NEXT-75-IDR", "PO Items for sale arriving next 75 days (IDR)");
UPDATE klkpi SET class = "PO-ITEMS-NEXT-75-IDR", concept = "" WHERE class = "Purchase Orders" AND concept = "PO Items for sale arriving next 75 days (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("PO-ITEMS-NEXT-75-PCS", "PO Items for sale arriving next 75 days (PCS @SC)");
UPDATE klkpi SET class = "PO-ITEMS-NEXT-75-PCS", concept = "" WHERE class = "Purchase Orders" AND concept = "PO Items for sale arriving next 75 days (PCS @SC)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("RET-5-30-PCS", "Returned Items - Barang cacat dalam 3 bulan Last 30 days (PCS)");
UPDATE klkpi SET class = "RET-5-30-PCS", concept = "" WHERE class = "Returned Items" AND concept = "Barang cacat dalam 3 bulan Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("RET-4-30-PCS", "Returned Items - Barang cacat dalam 7 hari Last 30 days (PCS)");
UPDATE klkpi SET class = "RET-4-30-PCS", concept = "" WHERE class = "Returned Items" AND concept = "Barang cacat dalam 7 hari Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("RET-2-30-PCS", "Returned Items - Berubah pikiran Last 30 days (PCS)");
UPDATE klkpi SET class = "RET-2-30-PCS", concept = "" WHERE class = "Returned Items" AND concept = "Berubah pikiran Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("RET-3-30-PCS", "Returned Items - Hadiah tidak cocok Last 30 days (PCS)");
UPDATE klkpi SET class = "RET-3-30-PCS", concept = "" WHERE class = "Returned Items" AND concept = "Hadiah tidak cocok Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("RET-TOTAL-30-PCS", "Returned Items - Total Last 30 days (PCS)");
UPDATE klkpi SET class = "RET-TOTAL-30-PCS", concept = "" WHERE class = "Returned Items" AND concept = "Total Returned Items Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("RET-1-30-PCS", "Returned Items - Ukuran tidak cocok Last 30 days (PCS)");
UPDATE klkpi SET class = "RET-1-30-PCS", concept = "" WHERE class = "Returned Items" AND concept = "Ukuran tidak cocok Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("INV-AV-INV-VALUE-30-IDR-BL", "Invoices - Average Invoice Value Last 30 days (IDR) Blink");
UPDATE klkpi SET class = "INV-AV-INV-VALUE-30-IDR-BL", concept = "" WHERE class = "Sales" AND concept = "Avg Invoice Value Last 30 days (IDR) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("INV-AV-INV-VALUE-30-IDR-KL", "Invoices - Average Invoice Value Last 30 days (IDR) Kapal-Laut");
UPDATE klkpi SET class = "INV-AV-INV-VALUE-30-IDR-KL", concept = "" WHERE class = "Sales" AND concept = "Avg Invoice Value Last 30 days (IDR) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("INV-AV-INV-VALUE-30-IDR-OU", "Invoices - Average Invoice Value Last 30 days (IDR) Outlet");
UPDATE klkpi SET class = "INV-AV-INV-VALUE-30-IDR-OU", concept = "" WHERE class = "Sales" AND concept = "Avg Invoice Value Last 30 days (IDR) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("INV-AV-INV-NUMBER-30-INV-BL", "Invoices - Average Invoices Last 30 days (INVOICES) Blink");
UPDATE klkpi SET class = "INV-AV-INV-NUMBER-30-INV-BL", concept = "" WHERE class = "Sales" AND concept = "Avg Invoices Last 30 days (INVOICES) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("INV-AV-INV-NUMBER-30-INV-KL", "Invoices - Average Invoices Last 30 days (INVOICES) Kapal-Laut");
UPDATE klkpi SET class = "INV-AV-INV-NUMBER-30-INV-KL", concept = "" WHERE class = "Sales" AND concept = "Avg Invoices Last 30 days (INVOICES) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("INV-AV-INV-NUMBER-30-INV-OU", "Invoices - Average Invoices Last 30 days (INVOICES) Outlet");
UPDATE klkpi SET class = "INV-AV-INV-NUMBER-30-INV-OU", concept = "" WHERE class = "Sales" AND concept = "Avg Invoices Last 30 days (INVOICES) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("INV-AV-ITEMS-INV-30-ITEM-BL", "Invoices - Items x Invoice Last 30 days (ITEMS) Blink");
UPDATE klkpi SET class = "INV-AV-ITEMS-INV-30-ITEM-BL", concept = "" WHERE class = "Sales" AND concept = "Items x Invoice Last 30 days (ITEMS) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("INV-AV-ITEMS-INV-30-ITEM-KL", "Invoices - Items x Invoice Last 30 days (ITEMS) Kapal-Laut");
UPDATE klkpi SET class = "INV-AV-ITEMS-INV-30-ITEM-KL", concept = "" WHERE class = "Sales" AND concept = "Items x Invoice Last 30 days (ITEMS) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("INV-AV-ITEMS-INV-30-ITEM-OU", "Invoices - Items x Invoice Last 30 days (ITEMS) Outlet");
UPDATE klkpi SET class = "INV-AV-ITEMS-INV-30-ITEM-OU", concept = "" WHERE class = "Sales" AND concept = "Items x Invoice Last 30 days (ITEMS) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SALES-ONLINE-15D-IDR", "Sales - Online Daily Sales Average Last 15 days (IDR)");
UPDATE klkpi SET class = "SALES-ONLINE-15D-IDR", concept = "" WHERE class = "Sales" AND concept = "Online Daily Sales Average Last 015 days (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SALES-ONLINE-30D-IDR", "Sales - Online Daily Sales Average Last 30 days (IDR)");
UPDATE klkpi SET class = "SALES-ONLINE-30D-IDR", concept = "" WHERE class = "Sales" AND concept = "Online Daily Sales Average Last 030 days (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SALES-RETAIL-15D-IDR", "Sales - Retail Daily Sales Average Last 15 days (IDR)");
UPDATE klkpi SET class = "SALES-RETAIL-15D-IDR", concept = "" WHERE class = "Sales" AND concept = "Retail Daily Sales Average Last 015 days (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SALES-RETAIL-30D-IDR", "Sales - Retail Daily Sales Average Last 30 days (IDR)");
UPDATE klkpi SET class = "SALES-RETAIL-30D-IDR", concept = "" WHERE class = "Sales" AND concept = "Retail Daily Sales Average Last 030 days (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SALES-TREND-RETAIL-30D-PERCENT", "Sales - Trend retail 30 days against last year (%) ");
UPDATE klkpi SET class = "SALES-TREND-RETAIL-30D-PERCENT", concept = "" WHERE class = "Sales" AND concept = "Trend retail 30 days against last year (%) ";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SERVER-CPU-USE-SEC", "Server - CPU Usage (Seconds)");
UPDATE klkpi SET class = "SERVER-CPU-USE-SEC", concept = "" WHERE class = "ServerUsage" AND concept = "CPU Usage (Seconds)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SERVER-CPU-USE-SEC-SCRIPT", "Server - CPU Usage (Seconds/Script)");
UPDATE klkpi SET class = "SERVER-CPU-USE-SEC-SCRIPT", concept = "" WHERE class = "ServerUsage" AND concept = "CPU Usage (Seconds/Script)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SERVER-TX-USE-TX", "Server - DB Usage Tx (Tx)");
UPDATE klkpi SET class = "SERVER-TX-USE-TX", concept = "" WHERE class = "ServerUsage" AND concept = "DB Usage Tx (Tx)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SERVER-TX-DEL-USE-TX", "Server - DB Usage Tx DELETE (Tx)");
UPDATE klkpi SET class = "SERVER-TX-DEL-USE-TX", concept = "" WHERE class = "ServerUsage" AND concept = "DB Usage Tx DELETE (Tx)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SERVER-TX-INS-USE-TX", "Server - DB Usage Tx INSERT (Tx)");
UPDATE klkpi SET class = "SERVER-TX-INS-USE-TX", concept = "" WHERE class = "ServerUsage" AND concept = "DB Usage Tx INSERT (Tx)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SERVER-TX-UPD-USE-TX", "Server - DB Usage Tx UPDATE (Tx)");
UPDATE klkpi SET class = "SERVER-TX-UPD-USE-TX", concept = "" WHERE class = "ServerUsage" AND concept = "DB Usage Tx UPDATE (Tx)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SERVER-USE-SCRIPT", "Server - Scripts Run (Scripts)");
UPDATE klkpi SET class = "SERVER-USE-SCRIPT", concept = "" WHERE class = "ServerUsage" AND concept = "Scripts Run (Scripts)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("TRANSFERS-INTREQ", "Transfers - Internal Requests");
UPDATE klkpi SET class = "TRANSFERS-INTREQ", concept = "" WHERE class = "Shops" AND concept = "Internal Requests";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("TRANSFERS-REBALANCE-MOD", "Transfers - Models rebalanced between shops (MODELS)");
UPDATE klkpi SET class = "TRANSFERS-REBALANCE-MOD", concept = "" WHERE class = "Shops" AND concept = "Models rebalanced between shops (MODELS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SHOPS-OPEN-BL", "Shops Open Blink");
UPDATE klkpi SET class = "SHOPS-OPEN-BL", concept = "" WHERE class = "Shops" AND concept = "Shops Open Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SHOPS-OPEN-KL", "Shops Open Kapal-Laut");
UPDATE klkpi SET class = "SHOPS-OPEN-KL", concept = "" WHERE class = "Shops" AND concept = "Shops Open Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SHOPS-OPEN-OU", "Shops Open Outlet");
UPDATE klkpi SET class = "SHOPS-OPEN-OU", concept = "" WHERE class = "Shops" AND concept = "Shops Open Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-AV-PCS-MODEL-BL", "Stock - Average pieces per model (PCS) Blink");
UPDATE klkpi SET class = "STOCK-AV-PCS-MODEL-BLINK", concept = "" WHERE class = "Stock" AND concept = "Average pieces per model (PCS) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-AV-PCS-MODEL-KL", "Stock - Average pieces per model (PCS) Kapal-Laut");
UPDATE klkpi SET class = "STOCK-AV-PCS-MODEL-KL", concept = "" WHERE class = "Stock" AND concept = "Average pieces per model (PCS) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-AV-PCS-MODEL-OU", "Stock - Average pieces per model (PCS) Outlet");
UPDATE klkpi SET class = "STOCK-AV-PCS-MODEL-OU", concept = "" WHERE class = "Stock" AND concept = "Average pieces per model (PCS) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-AV-STCOST-ITEM-IDR", "Stock - Average Standard Cost for item for sale (IDR)");
UPDATE klkpi SET class = "STOCK-AV-STCOST-ITEM-IDR", concept = "" WHERE class = "Stock" AND concept = "Average Standard Cost for item for sale (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-ITEMS-SALE-IDR", "Stock - Current Stock Items For Sale (IDR)");
UPDATE klkpi SET class = "STOCK-ITEMS-SALE-IDR", concept = "" WHERE class = "Stock" AND concept = "Current Stock Items For Sale (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-ITEMS-SALE-PCS", "Stock - Current Stock Items For Sale (PCS)");
UPDATE klkpi SET class = "STOCK-ITEMS-SALE-PCS", concept = "" WHERE class = "Stock" AND concept = "Current Stock Items For Sale (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-FORECAST-75D-PCS-BL", "Stock - Daily Stock forecast for 75 days (PCS) Blink");
UPDATE klkpi SET class = "STOCK-FORECAST-75D-PCS-BL", concept = "" WHERE class = "Stock" AND concept = "Daily Stock forecast for 75 days (PCS) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-FORECAST-75D-PCS-KL", "Stock - Daily Stock forecast for 75 days (PCS) Kapal-Laut");
UPDATE klkpi SET class = "STOCK-FORECAST-75D-PCS-KL", concept = "" WHERE class = "Stock" AND concept = "Daily Stock forecast for 75 days (PCS) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-FORECAST-75D-PCS-OU", "Stock - Daily Stock forecast for 75 days (PCS) Outlet");
UPDATE klkpi SET class = "STOCK-FORECAST-75D-PCS-OU", concept = "" WHERE class = "Stock" AND concept = "Daily Stock forecast for 75 days (PCS) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-AV-SOLD-75D-PCS-BL", "Stock - Daily Stock sold average 75 days (PCS) Blink");
UPDATE klkpi SET class = "STOCK-AV-SOLD-75D-PCS-BL", concept = "" WHERE class = "Stock" AND concept = "Daily Stock sold average 75 days (PCS) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-AV-SOLD-75D-PCS-KL", "Stock - Daily Stock sold average 75 days (PCS) Kapal-Laut");
UPDATE klkpi SET class = "STOCK-AV-SOLD-75D-PCS-KL", concept = "" WHERE class = "Stock" AND concept = "Daily Stock sold average 75 days (PCS) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-AV-SOLD-75D-PCS-OU", "Stock - Daily Stock sold average 75 days (PCS) Outlet");
UPDATE klkpi SET class = "STOCK-AV-SOLD-75D-PCS-OU", concept = "" WHERE class = "Stock" AND concept = "Daily Stock sold average 75 days (PCS) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-QOH-DAYS-BL", "Stock - Days left of stock QOH (DAYS) Blink");
UPDATE klkpi SET class = "STOCK-QOH-DAYS-BL", concept = "" WHERE class = "Stock" AND concept = "Days left of stock (DAYS) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-QOH-DAYS-KL", "Stock - Days left of stock QOH (DAYS) Kapal-Laut");
UPDATE klkpi SET class = "STOCK-QOH-DAYS-KL", concept = "" WHERE class = "Stock" AND concept = "Days left of stock (DAYS) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-QOH-DAYS-OU", "Stock - Days left of stock QOH (DAYS) Outlet");
UPDATE klkpi SET class = "STOCK-QOH-DAYS-OU", concept = "" WHERE class = "Stock" AND concept = "Days left of stock (DAYS) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-QOH-PO-WO-DAYS-BL", "Stock - Days left of stock QOH+PO+WO(DAYS) Blink");
UPDATE klkpi SET class = "STOCK-QOH-PO-WO-DAYS-BL", concept = "" WHERE class = "Stock" AND concept = "Days left of stock+PO+WO(DAYS) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-QOH-PO-WO-DAYS-KL", "Stock - Days left of stock QOH+PO+WO(DAYS) Kapal-Laut");
UPDATE klkpi SET class = "STOCK-QOH-PO-WO-DAYS-KL", concept = "" WHERE class = "Stock" AND concept = "Days left of stock+PO+WO(DAYS) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-QOH-PO-WO-DAYS-OU", "Stock - Days left of stock QOH+PO+WO(DAYS) Outlet");
UPDATE klkpi SET class = "STOCK-QOH-PO-WO-DAYS-OU", concept = "" WHERE class = "Stock" AND concept = "Days left of stock+PO+WO(DAYS) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-COGS-NEXT-75D-IDR", "Stock - Expected COGS next 75 days (IDR)");
UPDATE klkpi SET class = "STOCK-COGS-NEXT-75D-IDR", concept = "" WHERE class = "Stock" AND concept = "Expected COGS next 75 days (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-COGS-NEXT-75D-PCS", "Stock - Expected COGS next 75 days (PCS)");
UPDATE klkpi SET class = "STOCK-COGS-NEXT-75D-PCS", concept = "" WHERE class = "Stock" AND concept = "Expected COGS next 75 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-DIFF-NEXT-75D-IDR", "Stock - Expected difference stock in 75 days (IDR)");
UPDATE klkpi SET class = "STOCK-DIFF-NEXT-75D-IDR", concept = "" WHERE class = "Stock" AND concept = "Expected difference stock in 75 days (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-DIFF-NEXT-75D-PCS", "Stock - Expected difference stock in 75 days (PCS)");
UPDATE klkpi SET class = "STOCK-DIFF-NEXT-75D-PCS", concept = "" WHERE class = "Stock" AND concept = "Expected difference stock in 75 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-FUTURE-75D-IDR", "Stock - Expected future stock in 75 days (IDR)");
UPDATE klkpi SET class = "STOCK-FUTURE-75D-IDR", concept = "" WHERE class = "Stock" AND concept = "Expected future stock in 75 days (IDR)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-FUTURE-75D-PCS", "Stock - Expected future stock in 75 days (PCS)");
UPDATE klkpi SET class = "STOCK-FUTURE-75D-PCS", concept = "" WHERE class = "Stock" AND concept = "Expected future stock in 75 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-MOVED-OBS-MOD", "Stock - Models moved to obsolete (MODELS)");
UPDATE klkpi SET class = "STOCK-MOVED-OBS-MOD", concept = "" WHERE class = "Stock" AND concept = "Models moved to obsolete (MODELS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-NEG-PCS", "Stock - Negative Stock items (PCS)");
UPDATE klkpi SET class = "STOCK-NEG-PCS", concept = "" WHERE class = "Stock" AND concept = "Negative Stock items (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-FORSALE-PCS-BL", "Stock - Stock available for sale (PCS) Blink");
UPDATE klkpi SET class = "STOCK-FORSALE-PCS-BL", concept = "" WHERE class = "Stock" AND concept = "Stock available for sale (PCS) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-FORSALE-PCS-KL", "Stock - Stock available for sale (PCS) Kapal-Laut");
UPDATE klkpi SET class = "STOCK-FORSALE-PCS-KL", concept = "" WHERE class = "Stock" AND concept = "Stock available for sale (PCS) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-FORSALE-PCS-OU", "Stock - Stock available for sale (PCS) Outlet");
UPDATE klkpi SET class = "STOCK-FORSALE-PCS-OU", concept = "" WHERE class = "Stock" AND concept = "Stock available for sale (PCS) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-DISPLAY-PCS-BL", "Stock - Stock needed for display (PCS) Blink");
UPDATE klkpi SET class = "STOCK-DISPLAY-PCS-BL", concept = "" WHERE class = "Stock" AND concept = "Stock needed for display (PCS) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-DISPLAY-PCS-KL", "Stock - Stock needed for display (PCS) Kapal-Laut");
UPDATE klkpi SET class = "STOCK-DISPLAY-PCS-KL", concept = "" WHERE class = "Stock" AND concept = "Stock needed for display (PCS) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-DISPLAY-PCS-OU", "Stock - Stock needed for display (PCS) Outlet");
UPDATE klkpi SET class = "STOCK-DISPLAY-PCS-OU", concept = "" WHERE class = "Stock" AND concept = "Stock needed for display (PCS) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-NEED-OPTIMAL-PCS-BL", "Stock - Stock needed to reach optimal value (PCS) Blink");
UPDATE klkpi SET class = "STOCK-NEED-OPTIMAL-PCS-BL", concept = "" WHERE class = "Stock" AND concept = "Stock needed for optimal (PCS) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-NEED-OPTIMAL-PCS-KL", "Stock - Stock needed to reach optimal value (PCS) Kapal-Laut");
UPDATE klkpi SET class = "STOCK-NEED-OPTIMAL-PCS-KL", concept = "" WHERE class = "Stock" AND concept = "Stock needed for optimal (PCS) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-NEED-OPTIMAL-PCS-OU", "Stock - Stock needed to reach optimal value (PCS) Outlet");
UPDATE klkpi SET class = "STOCK-NEED-OPTIMAL-PCS-OU", concept = "" WHERE class = "Stock" AND concept = "Stock needed for optimal (PCS) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-DISPLAYS-PCS", "Stock - Stock of Displays (PCS)");
UPDATE klkpi SET class = "STOCK-DISPLAYS-PCS", concept = "" WHERE class = "Stock" AND concept = "Stock of Displays (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-PENDING-PO-PCS-BL", "Stock - Stock to be received by PO (PCS) Blink");
UPDATE klkpi SET class = "STOCK-PENDING-PO-PCS-BL", concept = "" WHERE class = "Stock" AND concept = "Stock to be received PO (PCS) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-PENDING-PO-PCS-KL", "Stock - Stock to be received by PO (PCS) Kapal-Laut");
UPDATE klkpi SET class = "STOCK-PENDING-PO-PCS-KL", concept = "" WHERE class = "Stock" AND concept = "Stock to be received PO (PCS) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-PENDING-PO-PCS-OU", "Stock - Stock to be received by PO (PCS) Outlet");
UPDATE klkpi SET class = "STOCK-PENDING-PO-PCS-OU", concept = "" WHERE class = "Stock" AND concept = "Stock to be received PO (PCS) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-PENDING-WO-PCS-BL", "Stock - Stock to be received by WO (PCS) Blink");
UPDATE klkpi SET class = "STOCK-PENDING-WO-PCS-BL", concept = "" WHERE class = "Stock" AND concept = "Stock to be received WO (PCS) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-PENDING-WO-PCS-KL", "Stock - Stock to be received by WO (PCS) Kapal-Laut");
UPDATE klkpi SET class = "STOCK-PENDING-WO-PCS-KL", concept = "" WHERE class = "Stock" AND concept = "Stock to be received WO (PCS) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-PENDING-WO-PCS-OU", "Stock - Stock to be received by WO (PCS) Outlet");
UPDATE klkpi SET class = "STOCK-PENDING-WO-PCS-OU", concept = "" WHERE class = "Stock" AND concept = "Stock to be received WO (PCS) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-MODELS-BL", "Stock - Total Models (MODELS) Blink");
UPDATE klkpi SET class = "STOCK-MODELS-BL", concept = "" WHERE class = "Stock" AND concept = "Total Models (MODELS) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-MODELS-KL", "Stock - Total Models (MODELS) Kapal-Laut");
UPDATE klkpi SET class = "STOCK-MODELS-KL", concept = "" WHERE class = "Stock" AND concept = "Total Models (MODELS) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-MODELS-OU", "Stock - Total Models (MODELS) Outlet");
UPDATE klkpi SET class = "STOCK-MODELS-OU", concept = "" WHERE class = "Stock" AND concept = "Total Models (MODELS) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-TOTAL-PCS-BL", "Stock - Total Stock (PCS) Blink");
UPDATE klkpi SET class = "STOCK-TOTAL-PCS-BL", concept = "" WHERE class = "Stock" AND concept = "Total Stock (PCS) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-TOTAL-PCS-KL", "Stock - Total Stock (PCS) Kapal-Laut");
UPDATE klkpi SET class = "STOCK-TOTAL-PCS-KL", concept = "" WHERE class = "Stock" AND concept = "Total Stock (PCS) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STOCK-TOTAL-PCS-OU", "Stock - Total Stock (PCS) Outlet");
UPDATE klkpi SET class = "STOCK-TOTAL-PCS-OU", concept = "" WHERE class = "Stock" AND concept = "Total Stock (PCS) Outlet";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SALES-TREND-RETAIL-75D-BL", "Sales - Trend retail 75 days against last year Blink (%)");
UPDATE klkpi SET class = "SALES-TREND-RETAIL-75D-BL", concept = "" WHERE class = "Stock" AND concept = "Trend retail 75 days (%) Blink";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("SALES-TREND-RETAIL-75D-KL", "Sales - Trend retail 75 days against last year Kapal-Laut (%)");
UPDATE klkpi SET class = "SALES-TREND-RETAIL-75D-KL", concept = "" WHERE class = "Stock" AND concept = "Trend retail 75 days (%) Kapal-Laut";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STADJ-5-30D-PCS", "Stock Adjust - Change of size Last 30 days (PCS)");
UPDATE klkpi SET class = "STADJ-5-30D-PCS", concept = "" WHERE class = "Stock Adjustments" AND concept = "Change of size Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STADJ-8-30D-PCS", "Stock Adjust - Gift KL - Giveaway Last 30 days (PCS)");
UPDATE klkpi SET class = "STADJ-8-30D-PCS", concept = "" WHERE class = "Stock Adjustments" AND concept = "Gift KL - Giveaway Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STADJ-6-30D-PCS", "Stock Adjust - Inventory Adjustment Last 30 days (PCS)");
UPDATE klkpi SET class = "STADJ-6-30D-PCS", concept = "" WHERE class = "Stock Adjustments" AND concept = "Inventory Adjustment Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STADJ-2-30D-PCS", "Stock Adjust - Item broken from office Last 30 days (PCS)");
UPDATE klkpi SET class = "STADJ-2-30D-PCS", concept = "" WHERE class = "Stock Adjustments" AND concept = "Item broken from office Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STADJ-1-30D-PCS", "Stock Adjust - Item broken from shop Last 30 days (PCS)");
UPDATE klkpi SET class = "STADJ-1-30D-PCS", concept = "" WHERE class = "Stock Adjustments" AND concept = "Item broken from shop Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STADJ-3-30D-PCS", "Stock Adjust - Returned goods Last 30 days (PCS)");
UPDATE klkpi SET class = "STADJ-3-30D-PCS", concept = "" WHERE class = "Stock Adjustments" AND concept = "Returned goods Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STADJ-4-30D-PCS", "Stock Adjust - Stolen by customer Last 30 days (PCS)");
UPDATE klkpi SET class = "STADJ-4-30D-PCS", concept = "" WHERE class = "Stock Adjustments" AND concept = "Stolen by customer Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STADJ-7-30D-PCS", "Stock Adjust - Supplier rejected - credit noted Last 30 days (PCS)");
UPDATE klkpi SET class = "STADJ-7-30D-PCS", concept = "" WHERE class = "Stock Adjustments" AND concept = "Supplier rejected items - credit noted Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STADJ-TOTAL-30D-PCS", "Stock Adjust - Total Stock Adjustments Last 30 days (PCS)");
UPDATE klkpi SET class = "STADJ-TOTAL-30D-PCS", concept = "" WHERE class = "Stock Adjustments" AND concept = "Total Stock Adjustments Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("STADJ-9-30D-PCS", "Stock Adjust - Others Last 30 days (PCS)");
UPDATE klkpi SET class = "STADJ-9-30D-PCS", concept = "" WHERE class = "Stock Adjustments" AND concept = "_Others Last 30 days (PCS)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("TRANSFERS-ACT-PCS", "Transfers - Active Transfers (pcs)");
UPDATE klkpi SET class = "TRANSFERS-ACT-PCS", concept = "" WHERE class = "Transfers" AND concept = "Active Transfers (pcs)";

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ("TRANSFERS-PENDING-SHOP-PCS", "Transfers - Goods Pending to be transferred @ shops (pcs)");
UPDATE klkpi SET class = "TRANSFERS-PENDING-SHOP-PCS", concept = "" WHERE class = "Transfers" AND concept = "Goods Pending to be transferred @ shops (pcs)";

ALTER TABLE `klkpi` DROP `concept`;
ALTER TABLE `klkpi` CHANGE `class` `kpicode` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;



/*


SELECT class, concept 
FROM `klkpi` 
WHERE concept != ""
GROUP BY `class`, `concept`
ORDER BY `class`, `concept`;



rename class to kpicode
delete column concept from klkpi
*/