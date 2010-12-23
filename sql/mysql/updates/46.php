<?php

CreateTable('menu', "CREATE TABLE `menu` (
	`id` int(11) NOT NULL DEFAULT 0,
	`caption` varchar(70) NOT NULL DEFAULT '',
	`parent` int(11) NOT NULL DEFAULT -1,
	`href` varchar(200) NOT NULL DEFAULT '#',
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(1, 'Sales', -1, '#'), array('id', 'caption', 'parent', 'href'), array(1, 'Sales', -1, '#'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(2, 'Transactions', 1, '#'), array('id', 'caption', 'parent', 'href'), array(2, 'Transactions', 1, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(3, 'Enter An Order or Quotation', 2, 'SelectOrderItems.php?&amp;NewOrder=Yes'), array('id', 'caption', 'parent', 'href'), array(3, 'Enter An Order or Quotation', 2, 'SelectOrderItems.php?&amp;NewOrder=Yes'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(4, 'Enter Counter Sales', 2, 'CounterSales.php?'), array('id', 'caption', 'parent', 'href'), array(4, 'Enter Counter Sales', 2, 'CounterSales.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(5, 'Print Picking Lists', 2, 'PDFPickingList.php?'), array('id', 'caption', 'parent', 'href'), array(5, 'Print Picking Lists', 2, 'PDFPickingList.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(6, 'Outstanding Sales Orders/Quotations', 2, 'SelectSalesOrder.php?'), array('id', 'caption', 'parent', 'href'), array(6, 'Outstanding Sales Orders/Quotations', 2, 'SelectSalesOrder.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(7, 'Special Order', 2, 'SpecialOrder.php?&amp;NewSpecial=Yes'), array('id', 'caption', 'parent', 'href'), array(7, 'Special Order', 2, 'SpecialOrder.php?&amp;NewSpecial=Yes'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(8, 'Recurring Order Template', 2, 'SelectRecurringSalesOrder.php?'), array('id', 'caption', 'parent', 'href'), array(8, 'Recurring Order Template', 2, 'SelectRecurringSalesOrder.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(9, 'Process Recurring Orders', 2, 'RecurringSalesOrdersProcess.php?'), array('id', 'caption', 'parent', 'href'), array(9, 'Process Recurring Orders', 2, 'RecurringSalesOrdersProcess.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(10, 'Inquiries and Reports ', 1, '#'), array('id', 'caption', 'parent', 'href'), array(10, 'Inquiries and Reports ', 1, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(11, 'Order Inquiry', 10, 'SelectCompletedOrder.php?'), array('id', 'caption', 'parent', 'href'), array(11, 'Order Inquiry', 10, 'SelectCompletedOrder.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(12, 'Print Price Lists', 10, 'PDFPriceList.php?'), array('id', 'caption', 'parent', 'href'), array(12, 'Print Price Lists', 10, 'PDFPriceList.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(13, 'Order Status Report', 10, 'PDFOrderStatus.php?'), array('id', 'caption', 'parent', 'href'), array(13, 'Order Status Report', 10, 'PDFOrderStatus.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(14, 'Orders Invoiced Reports', 10, 'PDFOrdersInvoiced.php?'), array('id', 'caption', 'parent', 'href'), array(14, 'Orders Invoiced Reports', 10, 'PDFOrdersInvoiced.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(15, 'Daily Sales Inquiry', 10, 'DailySalesInquiry.php?'), array('id', 'caption', 'parent', 'href'), array(15, 'Daily Sales Inquiry', 10, 'DailySalesInquiry.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(16, 'Order Delivery Differences Report', 10, 'PDFDeliveryDifferences.php?'), array('id', 'caption', 'parent', 'href'), array(16, 'Order Delivery Differences Report', 10, 'PDFDeliveryDifferences.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(17, 'Delivery In Full On Time (DIFOT) Report', 10, 'PDFDIFOT.php?'), array('id', 'caption', 'parent', 'href'), array(17, 'Delivery In Full On Time (DIFOT) Report', 10, 'PDFDIFOT.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(18, 'Sales Order Detail Or Summary Inquiries', 10, 'SalesInquiry.php?'), array('id', 'caption', 'parent', 'href'), array(18, 'Sales Order Detail Or Summary Inquiries', 10, 'SalesInquiry.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(19, 'Top Sales Items Report', 10, 'TopItems.php?'), array('id', 'caption', 'parent', 'href'), array(19, 'Top Sales Items Report', 10, 'TopItems.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(20, 'Sales With Low Gross Profit Report', 10, 'PDFLowGP.php?'), array('id', 'caption', 'parent', 'href'), array(20, 'Sales With Low Gross Profit Report', 10, 'PDFLowGP.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(21, 'Maintenance', 1, '#'), array('id', 'caption', 'parent', 'href'), array(21, 'Maintenance', 1, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(22, 'Select Contract', 21, 'SelectContract.php?'), array('id', 'caption', 'parent', 'href'), array(22, 'Select Contract', 21, 'SelectContract.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(23, 'Create Contract', 21, 'Contracts.php?'), array('id', 'caption', 'parent', 'href'), array(23, 'Create Contract', 21, 'Contracts.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(24, 'Receivables', -1, '#'), array('id', 'caption', 'parent', 'href'), array(24, 'Receivables', -1, '#'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(25, 'Transactions', 24, '#'), array('id', 'caption', 'parent', 'href'), array(25, 'Transactions', 24, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(26, 'Select Order to Invoice', 25, 'SelectSalesOrder.php?'), array('id', 'caption', 'parent', 'href'), array(26, 'Select Order to Invoice', 25, 'SelectSalesOrder.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(27, 'Create A Credit Note', 25, 'SelectCreditItems.php?&amp;NewCredit=Yes'), array('id', 'caption', 'parent', 'href'), array(27, 'Create A Credit Note', 25, 'SelectCreditItems.php?&amp;NewCredit=Yes'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(28, 'Enter Receipts', 25, 'CustomerReceipt.php?&amp;NewReceipt=Yes&amp;Type=Customer'), array('id', 'caption', 'parent', 'href'), array(28, 'Enter Receipts', 25, 'CustomerReceipt.php?&amp;NewReceipt=Yes&amp;Type=Customer'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(29, 'Allocate Receipts or Credit Notes', 25, 'CustomerAllocations.php?'), array('id', 'caption', 'parent', 'href'), array(29, 'Allocate Receipts or Credit Notes', 25, 'CustomerAllocations.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(30, 'Inquiries and Reports', 24, '#'), array('id', 'caption', 'parent', 'href'), array(30, 'Inquiries and Reports', 24, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(31, 'Customer Transaction Inquiries', 30, 'SelectCustomer.php?'), array('id', 'caption', 'parent', 'href'), array(31, 'Customer Transaction Inquiries', 30, 'SelectCustomer.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(32, 'Where Allocated Inquiry', 30, 'CustWhereAlloc.php?'), array('id', 'caption', 'parent', 'href'), array(32, 'Where Allocated Inquiry', 30, 'CustWhereAlloc.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(33, 'Print Invoices or Credit Notes', 30, 'PrintCustTransPortrait.php?'), array('id', 'caption', 'parent', 'href'), array(33, 'Print Invoices or Credit Notes', 30, 'PrintCustTransPortrait.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(34, 'Print Statements', 30, 'PrintCustStatements.php?'), array('id', 'caption', 'parent', 'href'), array(34, 'Print Statements', 30, 'PrintCustStatements.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(35, 'Sales Analysis Reports', 30, 'SalesAnalRepts.php?'), array('id', 'caption', 'parent', 'href'), array(35, 'Sales Analysis Reports', 30, 'SalesAnalRepts.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(36, 'Aged Customer Balances/Overdues Report', 30, 'AgedDebtors.php?'), array('id', 'caption', 'parent', 'href'), array(36, 'Aged Customer Balances/Overdues Report', 30, 'AgedDebtors.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(37, 'Transaction Inquiries', 30, 'CustomerTransInquiry.php?'), array('id', 'caption', 'parent', 'href'), array(37, 'Transaction Inquiries', 30, 'CustomerTransInquiry.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(38, 'Re-Print A Deposit Listing', 30, 'PDFBankingSummary.php?'), array('id', 'caption', 'parent', 'href'), array(38, 'Re-Print A Deposit Listing', 30, 'PDFBankingSummary.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(39, 'Debtor Balances At A Prior Month End', 30, 'DebtorsAtPeriodEnd.php?'), array('id', 'caption', 'parent', 'href'), array(39, 'Debtor Balances At A Prior Month End', 30, 'DebtorsAtPeriodEnd.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(40, 'Customer Listing By Area/Salesperson', 30, 'PDFCustomerList.php?'), array('id', 'caption', 'parent', 'href'), array(40, 'Customer Listing By Area/Salesperson', 30, 'PDFCustomerList.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(41, 'Sales Graphs', 30, 'SalesGraph.php?'), array('id', 'caption', 'parent', 'href'), array(41, 'Sales Graphs', 30, 'SalesGraph.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(42, 'List Daily Transactions', 30, 'PDFCustTransListing.php?'), array('id', 'caption', 'parent', 'href'), array(42, 'List Daily Transactions', 30, 'PDFCustTransListing.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(43, 'Maintenance', 24, '#'), array('id', 'caption', 'parent', 'href'), array(43, 'Maintenance', 24, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(44, 'Add Customer', 43, 'Customers.php?'), array('id', 'caption', 'parent', 'href'), array(44, 'Add Customer', 43, 'Customers.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(45, 'Customers', 43, 'SelectCustomer.php?'), array('id', 'caption', 'parent', 'href'), array(45, 'Customers', 43, 'SelectCustomer.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(46, 'Payables', -1, '#'), array('id', 'caption', 'parent', 'href'), array(46, 'Payables', -1, '#'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(47, 'Transactions', 46, '#'), array('id', 'caption', 'parent', 'href'), array(47, 'Transactions', 46, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(48, 'Select Supplier', 47, 'SelectSupplier.php?'), array('id', 'caption', 'parent', 'href'), array(48, 'Select Supplier', 47, 'SelectSupplier.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(49, 'Supplier Allocations', 47, 'SupplierAllocations.php?'), array('id', 'caption', 'parent', 'href'), array(49, 'Supplier Allocations', 47, 'SupplierAllocations.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(50, 'Inquiries and Reports', 46, '#'), array('id', 'caption', 'parent', 'href'), array(50, 'Inquiries and Reports', 46, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(51, 'Aged Supplier Report', 50, 'AgedSuppliers.php?'), array('id', 'caption', 'parent', 'href'), array(51, 'Aged Supplier Report', 50, 'AgedSuppliers.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(52, 'Payment Run Report', 50, 'SuppPaymentRun.php?'), array('id', 'caption', 'parent', 'href'), array(52, 'Payment Run Report', 50, 'SuppPaymentRun.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(53, 'Remittance Advices', 50, 'PDFRemittanceAdvice.php?'), array('id', 'caption', 'parent', 'href'), array(53, 'Remittance Advices', 50, 'PDFRemittanceAdvice.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(54, 'Outstanding GRNs Report', 50, 'OutstandingGRNs.php?'), array('id', 'caption', 'parent', 'href'), array(54, 'Outstanding GRNs Report', 50, 'OutstandingGRNs.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(55, 'Supplier Balances At A Prior Month End', 50, 'SupplierBalsAtPeriodEnd.php?'), array('id', 'caption', 'parent', 'href'), array(55, 'Supplier Balances At A Prior Month End', 50, 'SupplierBalsAtPeriodEnd.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(56, 'List Daily Transactions', 50, 'PDFSuppTransListing.php?'), array('id', 'caption', 'parent', 'href'), array(56, 'List Daily Transactions', 50, 'PDFSuppTransListing.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(57, 'Supplier Transaction Inquiries', 50, 'SupplierTransInquiry.php?'), array('id', 'caption', 'parent', 'href'), array(57, 'Supplier Transaction Inquiries', 50, 'SupplierTransInquiry.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(58, 'Maintenance', 46, '#'), array('id', 'caption', 'parent', 'href'), array(58, 'Maintenance', 46, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(59, 'Add Supplier', 58, 'Suppliers.php?'), array('id', 'caption', 'parent', 'href'), array(59, 'Add Supplier', 58, 'Suppliers.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(60, 'Maintain Factor Companies', 58, 'Factors.php?'), array('id', 'caption', 'parent', 'href'), array(60, 'Maintain Factor Companies', 58, 'Factors.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(61, 'Purchases', -1, '#'), array('id', 'caption', 'parent', 'href'), array(61, 'Purchases', -1, '#'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(62, 'Transactions', 61, '#'), array('id', 'caption', 'parent', 'href'), array(62, 'Transactions', 61, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(63, 'Purchase Orders', 62, 'PO_SelectOSPurchOrder.php?'), array('id', 'caption', 'parent', 'href'), array(63, 'Purchase Orders', 62, 'PO_SelectOSPurchOrder.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(64, 'Add Purchase Order', 62, 'PO_Header.php?&amp;NewOrder=Yes'), array('id', 'caption', 'parent', 'href'), array(64, 'Add Purchase Order', 62, 'PO_Header.php?&amp;NewOrder=Yes'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(65, 'Process Tenders and Offers', 62, 'OffersReceived.php?'), array('id', 'caption', 'parent', 'href'), array(65, 'Process Tenders and Offers', 62, 'OffersReceived.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(66, 'Orders to Authorise', 62, 'PO_AuthoriseMyOrders.php?'), array('id', 'caption', 'parent', 'href'), array(66, 'Orders to Authorise', 62, 'PO_AuthoriseMyOrders.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(67, 'Shipment Entry', 62, 'SelectSupplier.php?'), array('id', 'caption', 'parent', 'href'), array(67, 'Shipment Entry', 62, 'SelectSupplier.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(68, 'Select A Shipment', 62, 'Shipt_Select.php?'), array('id', 'caption', 'parent', 'href'), array(68, 'Select A Shipment', 62, 'Shipt_Select.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(69, 'Inquiries and Reports', 61, '#'), array('id', 'caption', 'parent', 'href'), array(69, 'Inquiries and Reports', 61, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(70, 'Purchase Order Inquiry', 69, 'PO_SelectPurchOrder.php?'), array('id', 'caption', 'parent', 'href'), array(70, 'Purchase Order Inquiry', 69, 'PO_SelectPurchOrder.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(71, 'Purchase Order Detail Or Summary Inquiries', 69, 'POReport.php?'), array('id', 'caption', 'parent', 'href'), array(71, 'Purchase Order Detail Or Summary Inquiries', 69, 'POReport.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(72, 'Inventory', -1, '#'), array('id', 'caption', 'parent', 'href'), array(72, 'Inventory', -1, '#'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(73, 'Transactions', 72, '#'), array('id', 'caption', 'parent', 'href'), array(73, 'Transactions', 72, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(74, 'Receive Purchase Orders', 73, 'PO_SelectOSPurchOrder.php?'), array('id', 'caption', 'parent', 'href'), array(74, 'Receive Purchase Orders', 73, 'PO_SelectOSPurchOrder.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(75, 'Bulk Inventory Transfer - Dispatch', 73, 'StockLocTransfer.php'), array('id', 'caption', 'parent', 'href'), array(75, 'Bulk Inventory Transfer - Dispatch', 73, 'StockLocTransfer.php'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(76, 'Bulk Inventory Transfer - Receive', 73, 'StockLocTransferReceive.php?'), array('id', 'caption', 'parent', 'href'), array(76, 'Bulk Inventory Transfer - Receive', 73, 'StockLocTransferReceive.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(77, 'Inventory Location Transfers', 73, 'StockTransfers.php?'), array('id', 'caption', 'parent', 'href'), array(77, 'Inventory Location Transfers', 73, 'StockTransfers.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(78, 'Inventory Adjustments', 73, 'StockAdjustments.php?&amp;NewAdjustment=Yes'), array('id', 'caption', 'parent', 'href'), array(78, 'Inventory Adjustments', 73, 'StockAdjustments.php?&amp;NewAdjustment=Yes'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(79, 'Reverse Goods Received', 73, 'ReverseGRN.php?'), array('id', 'caption', 'parent', 'href'), array(79, 'Reverse Goods Received', 73, 'ReverseGRN.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(80, 'Enter Stock Counts', 73, 'StockCounts.php?'), array('id', 'caption', 'parent', 'href'), array(80, 'Enter Stock Counts', 73, 'StockCounts.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(81, 'Inquiries and Reports', 72, '#'), array('id', 'caption', 'parent', 'href'), array(81, 'Inquiries and Reports', 72, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(82, 'Serial Item Research Tool', 81, 'StockSerialItemResearch.php?'), array('id', 'caption', 'parent', 'href'), array(82, 'Serial Item Research Tool', 81, 'StockSerialItemResearch.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(83, 'Print Price Labels', 81, 'PDFPrintLabel.php?'), array('id', 'caption', 'parent', 'href'), array(83, 'Print Price Labels', 81, 'PDFPrintLabel.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(84, 'Inventory Item Movements', 81, 'StockMovements.php?'), array('id', 'caption', 'parent', 'href'), array(84, 'Inventory Item Movements', 81, 'StockMovements.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(85, 'Inventory Item Status', 81, 'StockStatus.php?'), array('id', 'caption', 'parent', 'href'), array(85, 'Inventory Item Status', 81, 'StockStatus.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(86, 'Inventory Item Usage', 81, 'StockUsage.php?'), array('id', 'caption', 'parent', 'href'), array(86, 'Inventory Item Usage', 81, 'StockUsage.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(87, 'Inventory Quantities', 81, 'InventoryQuantities.php?'), array('id', 'caption', 'parent', 'href'), array(87, 'Inventory Quantities', 81, 'InventoryQuantities.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(88, 'Reorder Level', 81, 'ReorderLevel.php?'), array('id', 'caption', 'parent', 'href'), array(88, 'Reorder Level', 81, 'ReorderLevel.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(89, 'Reorder Level By Category/Location', 81, 'ReorderLevelLocation.php?'), array('id', 'caption', 'parent', 'href'), array(89, 'Reorder Level By Category/Location', 81, 'ReorderLevelLocation.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(90, 'Stock Dispatch', 81, 'StockDispatch.php?'), array('id', 'caption', 'parent', 'href'), array(90, 'Stock Dispatch', 81, 'StockDispatch.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(91, 'Inventory Valuation Report', 81, 'InventoryValuation.php?'), array('id', 'caption', 'parent', 'href'), array(91, 'Inventory Valuation Report', 81, 'InventoryValuation.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(92, 'Inventory Planning Report', 81, 'InventoryPlanning.php?'), array('id', 'caption', 'parent', 'href'), array(92, 'Inventory Planning Report', 81, 'InventoryPlanning.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(93, 'Inventory Planning Based On Preferred Supplier Data', 81, 'InventoryPlanningPrefSupplier.php?'), array('id', 'caption', 'parent', 'href'), array(93, 'Inventory Planning Based On Preferred Supplier Data', 81, 'InventoryPlanningPrefSupplier.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(94, 'Inventory Stock Check Sheets', 81, 'StockCheck.php?'), array('id', 'caption', 'parent', 'href'), array(94, 'Inventory Stock Check Sheets', 81, 'StockCheck.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(95, 'Make Inventory Quantities CSV', 81, 'StockQties_csv.php?'), array('id', 'caption', 'parent', 'href'), array(95, 'Make Inventory Quantities CSV', 81, 'StockQties_csv.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(96, 'Compare Counts Vs Stock Check Data', 81, 'PDFStockCheckComparison.php?'), array('id', 'caption', 'parent', 'href'), array(96, 'Compare Counts Vs Stock Check Data', 81, 'PDFStockCheckComparison.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(97, 'All Inventory Movements By Location/Date', 81, 'StockLocMovements.php?'), array('id', 'caption', 'parent', 'href'), array(97, 'All Inventory Movements By Location/Date', 81, 'StockLocMovements.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(98, 'List Inventory Status By Location/Category', 81, 'StockLocStatus.php?'), array('id', 'caption', 'parent', 'href'), array(98, 'List Inventory Status By Location/Category', 81, 'StockLocStatus.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(99, 'Historical Stock Quantity By Location/Category', 81, 'StockQuantityByDate.php?'), array('id', 'caption', 'parent', 'href'), array(99, 'Historical Stock Quantity By Location/Category', 81, 'StockQuantityByDate.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(100, 'List Negative Stocks', 81, 'PDFStockNegatives.php?'), array('id', 'caption', 'parent', 'href'), array(100, 'List Negative Stocks', 81, 'PDFStockNegatives.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(101, 'Daily Stock Transaction Listing', 81, 'PDFStockTransListing.php?'), array('id', 'caption', 'parent', 'href'), array(101, 'Daily Stock Transaction Listing', 81, 'PDFStockTransListing.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(102, 'Maintenance', 72, '#'), array('id', 'caption', 'parent', 'href'), array(102, 'Maintenance', 72, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(103, 'Add A New Item', 102, 'Stocks.php?'), array('id', 'caption', 'parent', 'href'), array(103, 'Add A New Item', 102, 'Stocks.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(104, 'Select An Item', 102, 'SelectProduct.php?'), array('id', 'caption', 'parent', 'href'), array(104, 'Select An Item', 102, 'SelectProduct.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(105, 'Sales Category Maintenance', 102, 'SalesCategories.php?'), array('id', 'caption', 'parent', 'href'), array(105, 'Sales Category Maintenance', 102, 'SalesCategories.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(106, 'Add or Update Prices Based On Costs', 102, 'PricesBasedOnMarkUp.php?'), array('id', 'caption', 'parent', 'href'), array(106, 'Add or Update Prices Based On Costs', 102, 'PricesBasedOnMarkUp.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(107, 'View or Update Prices Based On Costs', 102, 'PricesByCost.php?'), array('id', 'caption', 'parent', 'href'), array(107, 'View or Update Prices Based On Costs', 102, 'PricesByCost.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(108, 'Manufacturing', -1, '#'), array('id', 'caption', 'parent', 'href'), array(108, 'Manufacturing', -1, '#'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(109, 'Transactions', 108, '#'), array('id', 'caption', 'parent', 'href'), array(109, 'Transactions', 108, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(110, 'Work Order Entry', 109, 'WorkOrderEntry.php?'), array('id', 'caption', 'parent', 'href'), array(110, 'Work Order Entry', 109, 'WorkOrderEntry.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(111, 'Select A Work Order', 109, 'SelectWorkOrder.php?'), array('id', 'caption', 'parent', 'href'), array(111, 'Select A Work Order', 109, 'SelectWorkOrder.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(112, 'Inquiries and Reports', 108, '#'), array('id', 'caption', 'parent', 'href'), array(112, 'Inquiries and Reports', 108, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(113, 'Select A Work Order', 112, 'SelectWorkOrder.php?'), array('id', 'caption', 'parent', 'href'), array(113, 'Select A Work Order', 112, 'SelectWorkOrder.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(114, 'Costed Bill Of Material Inquiry', 112, 'BOMInquiry.php?'), array('id', 'caption', 'parent', 'href'), array(114, 'Costed Bill Of Material Inquiry', 112, 'BOMInquiry.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(115, 'Where Used Inquiry', 112, 'WhereUsedInquiry.php?'), array('id', 'caption', 'parent', 'href'), array(115, 'Where Used Inquiry', 112, 'WhereUsedInquiry.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(116, 'Indented Bill Of Material Listing', 112, 'BOMIndented.php?'), array('id', 'caption', 'parent', 'href'), array(116, 'Indented Bill Of Material Listing', 112, 'BOMIndented.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(117, 'List Components Required', 112, 'BOMExtendedQty.php?'), array('id', 'caption', 'parent', 'href'), array(117, 'List Components Required', 112, 'BOMExtendedQty.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(118, 'Indented Where Used Listing', 112, 'BOMIndentedReverse.php?'), array('id', 'caption', 'parent', 'href'), array(118, 'Indented Where Used Listing', 112, 'BOMIndentedReverse.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(119, 'MRP', 112, 'MRPReport.php?'), array('id', 'caption', 'parent', 'href'), array(119, 'MRP', 112, 'MRPReport.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(120, 'MRP Shortages', 112, 'MRPShortages.php?'), array('id', 'caption', 'parent', 'href'), array(120, 'MRP Shortages', 112, 'MRPShortages.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(121, 'MRP Suggested Purchase Orders', 112, 'MRPPlannedPurchaseOrders.php?'), array('id', 'caption', 'parent', 'href'), array(121, 'MRP Suggested Purchase Orders', 112, 'MRPPlannedPurchaseOrders.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(122, 'MRP Suggested Work Orders', 112, 'MRPPlannedWorkOrders.php?'), array('id', 'caption', 'parent', 'href'), array(122, 'MRP Suggested Work Orders', 112, 'MRPPlannedWorkOrders.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(123, 'MRP Reschedules Required', 112, 'MRPReschedules.php?'), array('id', 'caption', 'parent', 'href'), array(123, 'MRP Reschedules Required', 112, 'MRPReschedules.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(124, 'Maintenance', 108, '#'), array('id', 'caption', 'parent', 'href'), array(124, 'Maintenance', 108, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(125, 'Work Centre', 124, 'WorkCentres.php?'), array('id', 'caption', 'parent', 'href'), array(125, 'Work Centre', 124, 'WorkCentres.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(126, 'Bills Of Material', 124, 'BOMs.php?'), array('id', 'caption', 'parent', 'href'), array(126, 'Bills Of Material', 124, 'BOMs.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(127, 'Master Schedule', 124, 'MRPDemands.php?'), array('id', 'caption', 'parent', 'href'), array(127, 'Master Schedule', 124, 'MRPDemands.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(128, 'Auto Create Master Schedule', 124, 'MRPCreateDemands.php?'), array('id', 'caption', 'parent', 'href'), array(128, 'Auto Create Master Schedule', 124, 'MRPCreateDemands.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(129, 'MRP Calculation', 124, 'MRP.php?'), array('id', 'caption', 'parent', 'href'), array(129, 'MRP Calculation', 124, 'MRP.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(130, 'General Ledger', -1, '#'), array('id', 'caption', 'parent', 'href'), array(130, 'General Ledger', -1, '#'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(131, 'Transactions', 130, '#'), array('id', 'caption', 'parent', 'href'), array(131, 'Transactions', 130, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(132, 'Bank Account Payments Entry', 131, 'Payments.php?&amp;NewPayment=Yes'), array('id', 'caption', 'parent', 'href'), array(132, 'Bank Account Payments Entry', 131, 'Payments.php?&amp;NewPayment=Yes'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(133, 'Bank Account Receipts Entry', 131, 'CustomerReceipt.php?&amp;NewReceipt=Yes&amp;Type=GL'), array('id', 'caption', 'parent', 'href'), array(133, 'Bank Account Receipts Entry', 131, 'CustomerReceipt.php?&amp;NewReceipt=Yes&amp;Type=GL'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(134, 'Journal Entry', 131, 'GLJournal.php?&amp;NewJournal=Yes'), array('id', 'caption', 'parent', 'href'), array(134, 'Journal Entry', 131, 'GLJournal.php?&amp;NewJournal=Yes'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(135, 'Bank Account Payments Matching', 131, 'BankMatching.php?&amp;Type=Payments'), array('id', 'caption', 'parent', 'href'), array(135, 'Bank Account Payments Matching', 131, 'BankMatching.php?&amp;Type=Payments'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(136, 'Bank Account Receipts Matching', 131, 'BankMatching.php?&amp;Type=Receipts'), array('id', 'caption', 'parent', 'href'), array(136, 'Bank Account Receipts Matching', 131, 'BankMatching.php?&amp;Type=Receipts'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(137, 'Inquiries and Reports', 130, '#'), array('id', 'caption', 'parent', 'href'), array(137, 'Inquiries and Reports', 130, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(138, 'Trial Balance', 137, 'GLTrialBalance.php?'), array('id', 'caption', 'parent', 'href'), array(138, 'Trial Balance', 137, 'GLTrialBalance.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(139, 'Account Inquiry', 137, 'SelectGLAccount.php?'), array('id', 'caption', 'parent', 'href'), array(139, 'Account Inquiry', 137, 'SelectGLAccount.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(140, 'Account Listing', 137, 'GLAccountReport.php?'), array('id', 'caption', 'parent', 'href'), array(140, 'Account Listing', 137, 'GLAccountReport.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(141, 'Account Listing to CSV File', 137, 'GLAccountCSV.php?'), array('id', 'caption', 'parent', 'href'), array(141, 'Account Listing to CSV File', 137, 'GLAccountCSV.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(142, 'Bank Account Reconciliation Statement', 137, 'BankReconciliation.php?'), array('id', 'caption', 'parent', 'href'), array(142, 'Bank Account Reconciliation Statement', 137, 'BankReconciliation.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(143, 'Cheque Payments Listing', 137, 'PDFChequeListing.php?'), array('id', 'caption', 'parent', 'href'), array(143, 'Cheque Payments Listing', 137, 'PDFChequeListing.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(144, 'Daily Bank Transactions', 137, 'DailyBankTransactions.php?'), array('id', 'caption', 'parent', 'href'), array(144, 'Daily Bank Transactions', 137, 'DailyBankTransactions.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(145, 'Profit and Loss Statement', 137, 'GLProfit_Loss.php?'), array('id', 'caption', 'parent', 'href'), array(145, 'Profit and Loss Statement', 137, 'GLProfit_Loss.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(146, 'Balance Sheet', 137, 'GLBalanceSheet.php?'), array('id', 'caption', 'parent', 'href'), array(146, 'Balance Sheet', 137, 'GLBalanceSheet.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(147, 'Tag Reports', 137, 'GLTagProfit_Loss.php?'), array('id', 'caption', 'parent', 'href'), array(147, 'Tag Reports', 137, 'GLTagProfit_Loss.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(148, 'Tax Reports', 137, 'Tax.php?'), array('id', 'caption', 'parent', 'href'), array(148, 'Tax Reports', 137, 'Tax.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(149, 'Maintenance', 130, '#'), array('id', 'caption', 'parent', 'href'), array(149, 'Maintenance', 130, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(150, 'GL Account', 149, 'GLAccounts.php?'), array('id', 'caption', 'parent', 'href'), array(150, 'GL Account', 149, 'GLAccounts.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(151, 'GL Budgets', 149, 'GLBudgets.php?'), array('id', 'caption', 'parent', 'href'), array(151, 'GL Budgets', 149, 'GLBudgets.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(152, 'Account Groups', 149, 'AccountGroups.php?'), array('id', 'caption', 'parent', 'href'), array(152, 'Account Groups', 149, 'AccountGroups.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(153, 'Account Sections', 149, 'AccountSections.php?'), array('id', 'caption', 'parent', 'href'), array(153, 'Account Sections', 149, 'AccountSections.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(154, 'GL Tags', 149, 'GLTags.php?'), array('id', 'caption', 'parent', 'href'), array(154, 'GL Tags', 149, 'GLTags.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(155, 'Asset Manager', -1, '#'), array('id', 'caption', 'parent', 'href'), array(155, 'Asset Manager', -1, '#'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(156, 'Transactions', 155, '#'), array('id', 'caption', 'parent', 'href'), array(156, 'Transactions', 155, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(157, 'Add a new Asset', 156, 'FixedAssetItems.php?'), array('id', 'caption', 'parent', 'href'), array(157, 'Add a new Asset', 156, 'FixedAssetItems.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(158, 'Select an Asset', 156, 'SelectAsset.php?'), array('id', 'caption', 'parent', 'href'), array(158, 'Select an Asset', 156, 'SelectAsset.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(159, 'Change Asset Location', 156, 'FixedAssetTransfer.php?'), array('id', 'caption', 'parent', 'href'), array(159, 'Change Asset Location', 156, 'FixedAssetTransfer.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(160, 'Depreciation Journal', 156, 'FixedAssetDepreciation.php'), array('id', 'caption', 'parent', 'href'), array(160, 'Depreciation Journal', 156, 'FixedAssetDepreciation.php'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(161, 'Inquiries and Reports', 155, '#'), array('id', 'caption', 'parent', 'href'), array(161, 'Inquiries and Reports', 155, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(162, 'Asset Register', 161, 'FixedAssetRegister.php?'), array('id', 'caption', 'parent', 'href'), array(162, 'Asset Register', 161, 'FixedAssetRegister.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(163, 'Maintenance', 155, '#'), array('id', 'caption', 'parent', 'href'), array(163, 'Maintenance', 155, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(164, 'Asset Categories Maintenance', 163, 'FixedAssetCategories.php?'), array('id', 'caption', 'parent', 'href'), array(164, 'Asset Categories Maintenance', 163, 'FixedAssetCategories.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(165, 'Add or Maintain Asset Locations', 163, 'FixedAssetLocations.php?'), array('id', 'caption', 'parent', 'href'), array(165, 'Add or Maintain Asset Locations', 163, 'FixedAssetLocations.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(166, 'Petty Cash', -1, '#'), array('id', 'caption', 'parent', 'href'), array(166, 'Petty Cash', -1, '#'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(167, 'Transactions', 166, '#'), array('id', 'caption', 'parent', 'href'), array(167, 'Transactions', 166, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(168, 'Assign Cash to PC Tab', 167, 'PcAssignCashToTab.php?'), array('id', 'caption', 'parent', 'href'), array(168, 'Assign Cash to PC Tab', 167, 'PcAssignCashToTab.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(169, 'Claim Expenses From PC Tab', 167, 'PcClaimExpensesFromTab.php'), array('id', 'caption', 'parent', 'href'), array(169, 'Claim Expenses From PC Tab', 167, 'PcClaimExpensesFromTab.php'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(170, 'Expenses Authorisation', 167, 'PcAuthorizeExpenses.php?'), array('id', 'caption', 'parent', 'href'), array(170, 'Expenses Authorisation', 167, 'PcAuthorizeExpenses.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(171, 'Inquiries and Reports', 166, '#'), array('id', 'caption', 'parent', 'href'), array(171, 'Inquiries and Reports', 166, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(172, 'PC Tab General Report', 171, 'PcReportTab.php?'), array('id', 'caption', 'parent', 'href'), array(172, 'PC Tab General Report', 171, 'PcReportTab.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(173, 'Maintenance', 166, '#'), array('id', 'caption', 'parent', 'href'), array(173, 'Maintenance', 166, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(174, 'Types of PC Tabs', 173, 'PcTypeTabs.php?'), array('id', 'caption', 'parent', 'href'), array(174, 'Types of PC Tabs', 173, 'PcTypeTabs.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(175, 'PC Tabs', 173, 'PcTabs.php?'), array('id', 'caption', 'parent', 'href'), array(175, 'PC Tabs', 173, 'PcTabs.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(176, 'PC Expenses', 173, 'PcExpenses.php?'), array('id', 'caption', 'parent', 'href'), array(176, 'PC Expenses', 173, 'PcExpenses.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(177, 'Expenses for Type of PC Tab', 173, 'PcExpensesTypeTab.php?'), array('id', 'caption', 'parent', 'href'), array(177, 'Expenses for Type of PC Tab', 173, 'PcExpensesTypeTab.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(178, 'Configuration', -1, '#'), array('id', 'caption', 'parent', 'href'), array(178, 'Configuration', -1, '#'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(179, 'General Settings', 178, '#'), array('id', 'caption', 'parent', 'href'), array(179, 'General Settings', 178, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(180, 'Company Preferences', 179, 'CompanyPreferences.php?'), array('id', 'caption', 'parent', 'href'), array(180, 'Company Preferences', 179, 'CompanyPreferences.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(181, 'Configuration Settings', 179, 'SystemParameters.php?'), array('id', 'caption', 'parent', 'href'), array(181, 'Configuration Settings', 179, 'SystemParameters.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(188, 'Page Security Settings', 179, 'PageSecurity.php?'), array('id', 'caption', 'parent', 'href'), array(188, 'Page Security Settings', 179, 'PageSecurity.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(182, 'Users', 178, '#'), array('id', 'caption', 'parent', 'href'), array(182, 'Users', 178, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(183, 'User Maintenance', 182, 'WWW_Users.php?'), array('id', 'caption', 'parent', 'href'), array(183, 'User Maintenance', 182, 'WWW_Users.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(184, 'Role Permissions', 182, 'WWW_Access.php?'), array('id', 'caption', 'parent', 'href'), array(184, 'Role Permissions', 182, 'WWW_Access.php?'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(185, 'Menu Access Rights', 182, 'MenuAccess.php?'), array('id', 'caption', 'parent', 'href'), array(185, 'Menu Access Rights', 182, 'MenuAccess.php?'), $db);

InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(186, 'General Ledger Setup', 178, '#'), array('id', 'caption', 'parent', 'href'), array(186, 'General Ledger Setup', 178, '#'), $db);
InsertRecord('menu', array('id', 'caption', 'parent', 'href'), array(187, 'Bank Accounts', 186, 'BankAccounts.php?'), array('id', 'caption', 'parent', 'href'), array(187, 'Bank Accounts', 186, 'BankAccounts.php?'), $db);

CreateTable('usermenurights', "CREATE TABLE `usermenurights` (
	`userid` varchar(20) NOT NULL DEFAULT '',
	`menuid` int(11) NOT NULL DEFAULT 0,
	`access` tinyint NOT NULL DEFAULT 1,
	PRIMARY KEY  (`userid`, `menuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

$sql="select * FROM usermenurights";
$result=DB_query($sql, $db);
if (DB_num_rows($result)==0) {
	$response=executeSQL("INSERT INTO `usermenurights` (userid,menuid,access) SELECT www_users.userid,  menu.`id`, 1 FROM menu, www_users", $db, False);
	if ($response==0) {
		OutputResult( _('The usermenurights table has been populated') , 'success');
	} else {
		OutputResult( _('The usermenurights table could not be populated') , 'error');
	}
} else {
	OutputResult( _('The usermenurights table is already populated') , 'info');
}

UpdateDBNo(46, $db);

?>