<?php

CreateTable('modules', 'CREATE TABLE `modules` (
  `secroleid` int NOT NULL DEFAULT "8",
  `modulelink` varchar(10) NOT NULL DEFAULT "",
  `reportlink` varchar(4) NOT NULL DEFAULT "",
  `modulename` varchar(25) NOT NULL DEFAULT "",
  `sequence` int NOT NULL DEFAULT "1",
  PRIMARY KEY (`secroleid`,`modulelink`)
)');

CreateTable('menuitems', 'CREATE TABLE `menuitems` (
  `secroleid` int NOT NULL DEFAULT "8",
  `modulelink` varchar(10) NOT NULL DEFAULT "",
  `menusection` varchar(15) NOT NULL DEFAULT "",
  `caption` varchar(60) NOT NULL DEFAULT "",
  `url` varchar(60) NOT NULL DEFAULT "",
  `sequence` int NOT NULL DEFAULT "1",
  PRIMARY KEY (`secroleid`,`modulelink`,`menusection`,`caption`)
)');


NewModule('Sales', 'ord', _('Sales'), 10);
NewModule('AR', 'ar', _('Receivables'), 20);
NewModule('PO', 'prch', _('Purchases'), 30);
NewModule('AP', 'ap', _('Payables'), 40);
NewModule('stock', 'inv', _('Inventory'), 50);
NewModule('manuf', 'man', _('Manufacturing'), 60);
NewModule('GL', 'gl', _('General Ledger'), 70);
NewModule('FA', 'fa', _('Asset Manager'), 80);
NewModule('PC', 'pc', _('Petty Cash'), 90);
NewModule('Personalia', 'pe', _('Personalia'), 100);
NewModule('system', 'sys', _('Setup'), 110);
NewModule('Utilities', 'utils', _('Utilities'), 120);

NewMenuItem('Sales', 'Transactions', _('New Sales Order or Quotation'), '/SelectOrderItems.php?NewOrder=Yes', 10);
NewMenuItem('Sales', 'Transactions', _('Enter Counter Sales'), '/CounterSales.php', 20);
NewMenuItem('Sales', 'Transactions', _('Enter Counter Returns'), '/CounterReturns.php', 30);
NewMenuItem('Sales', 'Transactions', _('Retail Point Of Sale'), '/KLRetailPOS.php', 40);
NewMenuItem('Sales', 'Transactions', _('Retail Customer Info Card'), '/KLRetailCustomerInfoCard.php', 50);
NewMenuItem('Sales', 'Transactions', _('Shop Tali Exchanges'), '/KLShopFreeExchanges.php', 60);
NewMenuItem('Sales', 'Transactions', _('Generate/Print Picking Lists'), '/PDFPickingList.php', 70);
NewMenuItem('Sales', 'Transactions', _('Outstanding Sales Orders/Quotations'), '/SelectSalesOrder.php', 80);
NewMenuItem('Sales', 'Transactions', _('Special Order'), '/SpecialOrder.php', 90);
NewMenuItem('Sales', 'Transactions', _('Recurring Order Template'), '/SelectRecurringSalesOrder.php', 100);
NewMenuItem('Sales', 'Transactions', _('Process Recurring Orders'), '/RecurringSalesOrdersProcess.php', 110);

NewMenuItem('Sales', 'Reports', _('Sales Order Inquiry'), '/SelectCompletedOrder.php', 10);
NewMenuItem('Sales', 'Reports', _('Print Price Lists'), '/PDFPriceList.php', 20);
NewMenuItem('Sales', 'Reports', _('Order Status Report'), '/PDFOrderStatus.php', 30);
NewMenuItem('Sales', 'Reports', _('Orders Invoiced Reports'), '/PDFOrdersInvoiced.php', 40);
NewMenuItem('Sales', 'Reports', _('Daily Sales Inquiry'), '/DailySalesInquiry.php', 50);
NewMenuItem('Sales', 'Reports', _('KL Daily Sales Inquiry'), '/KLDailySalesInquiry.php', 60);
NewMenuItem('Sales', 'Reports', _('Sales By Sales Type Inquiry'), '/SalesByTypePeriodInquiry.php', 70);
NewMenuItem('Sales', 'Reports', _('Sales By Category Inquiry'), '/SalesCategoryPeriodInquiry.php', 80);
NewMenuItem('Sales', 'Reports', _('Sales By Category By Item Inquiry'), '/StockCategorySalesInquiry.php', 90);
NewMenuItem('Sales', 'Reports', _('Sales Analysis Reports'), '/SalesAnalRepts.php', 100);
NewMenuItem('Sales', 'Reports', _('KPI Graphs'), '/KLGraph.php', 110);
NewMenuItem('Sales', 'Reports', _('Sales Graphs'), '/SalesGraph.php', 120);
NewMenuItem('Sales', 'Reports', _('Top Sellers Inquiry'), '/SalesTopItemsInquiry.php', 130);
NewMenuItem('Sales', 'Reports', _('Order Delivery Differences Report'), '/PDFDeliveryDifferences.php', 140);
NewMenuItem('Sales', 'Reports', _('Delivery In Full On Time (DIFOT) Report'), '/PDFDIFOT.php', 150);
NewMenuItem('Sales', 'Reports', _('Sales Order Detail Or Summary Inquiries'), '/SalesInquiry.php', 160);
NewMenuItem('Sales', 'Reports', _('KL Retail Sales Hourly Report'), '/KLSalesHourlyReport.php', 170);
NewMenuItem('Sales', 'Reports', _('KL Sales Order Detail Or Summary Inquiries'), '/KLSalesInquiry.php', 180);
NewMenuItem('Sales', 'Reports', _('Top Sales Items Report'), '/TopItems.php', 190);
NewMenuItem('Sales', 'Reports', _('Top Customers Inquiry'), '/SalesTopCustomersInquiry.php', 200);
NewMenuItem('Sales', 'Reports', _('Worst Sales Items Report'), '/NoSalesItems.php', 210);
NewMenuItem('Sales', 'Reports', _('Sales With Low Gross Profit Report'), '/PDFLowGP.php', 220);
NewMenuItem('Sales', 'Reports', _('KL Control Board'), '/KLControlBoard.php', 230);
NewMenuItem('Sales', 'Reports', _('KL Pricing Control Board'), '/KLControlBoardPrices.php', 240);
NewMenuItem('Sales', 'Reports', _('KL SPG Control Board'), '/KLControlBoardSPG.php', 250);
NewMenuItem('Sales', 'Reports', _('KL SPG End Of Shift Report'), '/KLRetailEndOfShift.php', 260);
NewMenuItem('Sales', 'Reports', _('KL Performance Board'), '/KLPerformanceBoard.php', 270);
NewMenuItem('Sales', 'Reports', _('KL SPG Performance Report'), '/KLSPGPerformance.php', 280);
NewMenuItem('Sales', 'Reports', _('KL Quality and Returns Report'), '/KLQualityReturnsPerformance.php', 290);
NewMenuItem('Sales', 'Reports', _('KL Retail Customer Analysis'), '/KLRetailCustomerBoard.php', 300);
NewMenuItem('Sales', 'Reports', _('KL Excel Sales Analysis'), '/KLExcelSalesAnalysis.php', 310);
NewMenuItem('Sales', 'Reports', _('Sell Through Support Claims Report'), '/PDFSellThroughSupportClaim.php', 320);
NewMenuItem('Sales', 'Reports', _('Sales to Customers'), '/SalesReport.php', 330);
NewMenuItem('Sales', 'Reports', _('Sales Commission Reports'), '/SalesCommissionReports.php', 340);

NewMenuItem('Sales', 'Maintenance', _('Create Contract'), '/Contracts.php', 10);
NewMenuItem('Sales', 'Maintenance', _('Select Contract'), '/SelectContract.php', 20);
NewMenuItem('Sales', 'Maintenance', _('Sell Through Support Deals'), '/SellThroughSupport.php', 30);
NewMenuItem('Sales', 'Maintenance', _('Maintain Picking Lists'), '/SelectPickingLists.php', 40);
NewMenuItem('Sales', 'Maintenance', _('KL Set Website Sales Categories'), '/KLSetWebsiteCategories.php', 50);
NewMenuItem('Sales', 'Maintenance', _('KL Set Related Items'), '/KLSetRelatedItems.php', 60);
NewMenuItem('Sales', 'Maintenance', _('Sendinblue: Export KL Retail Customers'), '/KLExcelExportCustomersSendinblue.php', 70);
NewMenuItem('Sales', 'Maintenance', _('Sendinblue: Export KL webERP Customers'), '/KLExcelExportOnlineSendinblue.php', 80);
NewMenuItem('Sales', 'Maintenance', _('Sendinblue: Export Newsletter Subscribers'), '/KLExcelExportNewsletterSendinblue.php', 90);
NewMenuItem('Sales', 'Maintenance', _('OpenCart: Sync webERP to OC Daily'), '/WeberpToOpenCartDaily.php', 100);
NewMenuItem('Sales', 'Maintenance', _('OpenCart: Sync webERP to OC Hourly'), '/WeberpToOpenCartHourly.php', 110);
NewMenuItem('Sales', 'Maintenance', _('OpenCart: Sync OC to webERP'), '/OpenCartToWeberp.php', 120);
NewMenuItem('Sales', 'Maintenance', _('AdminCerdas: Upload to marketplaces'), '/KLExcelAdminCerdas.php', 130);
NewMenuItem('Sales', 'Maintenance', _('FORSTOK: Upload to marketplaces'), '/KLExcelExportToFS.php', 140);
NewMenuItem('Sales', 'Maintenance', _('Tokopedia: Import ProductID-URL'), '/KLTokopediaURL.php', 150);
NewMenuItem('Sales', 'Maintenance', _('Shopee: Import ProductID-URL'), '/KLShopeeURL.php', 160);
NewMenuItem('Sales', 'Maintenance', _('Lazada: Import ProductID-URL'), '/KLLazadaURL.php', 170);
NewMenuItem('Sales', 'Maintenance', _('Lazada: Export Products to Lazada'), '/KLExcelLazada.php', 180);
NewMenuItem('Sales', 'Maintenance', _('Zalora: Export Products to Zalora'), '/KLExcelZalora.php', 190);

NewMenuItem('AR', 'Transactions', _('Select Order to Invoice'), '/SelectSalesOrder.php', 10);
NewMenuItem('AR', 'Transactions', _('Create A Credit Note'), '/SelectCreditItems.php?NewCredit=Yes', 20);
NewMenuItem('AR', 'Transactions', _('Enter Receipts'), '/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer', 30);
NewMenuItem('AR', 'Transactions', _('Allocate Receipts or Credit Notes'), '/CustomerAllocations.php', 40);

NewMenuItem('AR', 'Reports', _('Where Allocated Inquiry'), '/CustWhereAlloc.php', 10);
NewMenuItem('AR', 'Reports', _('Print Invoices or Credit Notes'), '/PrintCustTrans.php', 20);
NewMenuItem('AR', 'Reports', _('Print Statements'), '/PrintCustStatements.php', 30);
NewMenuItem('AR', 'Reports', _('Aged Customer Balances/Overdues Report'), '/AgedDebtors.php', 40);
NewMenuItem('AR', 'Reports', _('Re-Print A Deposit Listing'), '/PDFBankingSummary.php', 50);
NewMenuItem('AR', 'Reports', _('Debtor Balances At A Prior Month End'), '/DebtorsAtPeriodEnd.php', 60);
NewMenuItem('AR', 'Reports', _('Customer Listing By Area/Salesperson'), '/PDFCustomerList.php', 70);
NewMenuItem('AR', 'Reports', _('List Daily Transactions'), '/PDFCustTransListing.php', 80);
NewMenuItem('AR', 'Reports', _('Customer Transaction Inquiries'), '/CustomerTransInquiry.php', 90);
NewMenuItem('AR', 'Reports', _('Customer Activity and Balances'), '/CustomerBalancesMovement.php', 100);

NewMenuItem('AR', 'Maintenance', _('Add Customer'), '/Customers.php', 10);
NewMenuItem('AR', 'Maintenance', _('Select Customer'), '/SelectCustomer.php', 20);

NewMenuItem('AP', 'Transactions', _('Select Supplier'), '/SelectSupplier.php', 10);
NewMenuItem('AP', 'Transactions', _('Supplier Allocations'), '/SupplierAllocations.php', 20);

NewMenuItem('AP', 'Reports', _('Where Allocated Inquiry'), '/SuppWhereAlloc.php', 10);
NewMenuItem('AP', 'Reports', _('Aged Supplier Report'), '/AgedSuppliers.php', 20);
NewMenuItem('AP', 'Reports', _('Payment Run Report'), '/SuppPaymentRun.php', 30);
NewMenuItem('AP', 'Reports', _('Remittance Advices'), '/PDFRemittanceAdvice.php', 40);
NewMenuItem('AP', 'Reports', _('Outstanding GRNs Report'), '/OutstandingGRNs.php', 50);
NewMenuItem('AP', 'Reports', _('Supplier Balances At A Prior Month End'), '/SupplierBalsAtPeriodEnd.php', 60);
NewMenuItem('AP', 'Reports', _('List Daily Transactions'), '/PDFSuppTransListing.php', 70);
NewMenuItem('AP', 'Reports', _('Purchase Order Financial Planning'), '/POFinancialPlanning.php', 80);
NewMenuItem('AP', 'Reports', _('KL Goods Received Not Invoiced Yet'), '/KLGoodsReceivedNotInvoiced.php', 90);
NewMenuItem('AP', 'Reports', _('Supplier Transaction Inquiries'), '/SupplierTransInquiry.php', 100);

NewMenuItem('AP', 'Maintenance', _('Add Supplier'), '/Suppliers.php', 10);
NewMenuItem('AP', 'Maintenance', _('Select Supplier'), '/SelectSupplier.php', 20);
NewMenuItem('AP', 'Maintenance', _('Maintain Factor Companies'), '/Factors.php', 30);

NewMenuItem('PO', 'Transactions', _('New Purchase Order'), '/PO_Header.php?NewOrder=Yes', 10);
NewMenuItem('PO', 'Transactions', _('Purchase Orders'), '/PO_SelectOSPurchOrder.php', 20);
NewMenuItem('PO', 'Transactions', _('Purchase Order Grid Entry'), '/PurchaseByPrefSupplier.php', 30);
NewMenuItem('PO', 'Transactions', _('Create a New Tender'), '/SupplierTenderCreate.php?New=Yes', 40);
NewMenuItem('PO', 'Transactions', _('Edit Existing Tenders'), '/SupplierTenderCreate.php?Edit=Yes', 50);
NewMenuItem('PO', 'Transactions', _('Process Tenders and Offers'), '/OffersReceived.php', 60);
NewMenuItem('PO', 'Transactions', _('Orders to Authorise'), '/PO_AuthoriseMyOrders.php', 70);
NewMenuItem('PO', 'Transactions', _('Shipment Entry'), '/SelectSupplier.php', 80);
NewMenuItem('PO', 'Transactions', _('Select A Shipment'), '/Shipt_Select.php', 90);

NewMenuItem('PO', 'Reports', _('Purchase Order Inquiry'), '/PO_SelectPurchOrder.php', 10);
NewMenuItem('PO', 'Reports', _('Purchase Order Detail Or Summary Inquiries'), '/POReport.php', 20);
NewMenuItem('PO', 'Reports', _('Supplier Price List'), '/SuppPriceList.php', 30);
NewMenuItem('PO', 'Reports', _('Purchases from Suppliers'), '/PurchasesReport.php', 40);

NewMenuItem('PO', 'Maintenance', _('Maintain Supplier Price Lists'), '/SupplierPriceList.php', 10);

NewMenuItem('stock', 'Transactions', _('Receive Purchase Orders'), '/PO_SelectOSPurchOrder.php', 10);
NewMenuItem('stock', 'Transactions', _('Inventory Location Transfers'), '/StockTransfers.php?New=Yes', 20);
NewMenuItem('stock', 'Transactions', _('Bulk Inventory Transfer') . ' - ' . _('Dispatch'), '/StockLocTransfer.php', 30);
NewMenuItem('stock', 'Transactions', _('Bulk Inventory Transfer') . ' - ' . _('Receive'), '/StockLocTransferReceive.php', 40);
NewMenuItem('stock', 'Transactions', _('KL Shop Transfer - Send Return Transfer TO kantor'), '/KLPOSReturnToKantor.php', 50);
NewMenuItem('stock', 'Transactions', _('KL Shop Packaging Fill Up'), '/KLFillUpShopPackaging.php', 60);
NewMenuItem('stock', 'Transactions', _('KL Standard Cost Bulk Adjustments'), '/KLUpdateStandardCostCountry', 70);
NewMenuItem('stock', 'Transactions', _('Inventory Adjustments'), '/StockAdjustments.php?NewAdjustment=Yes', 80);
NewMenuItem('stock', 'Transactions', _('Reverse Goods Received'), '/ReverseGRN.php', 90);
NewMenuItem('stock', 'Transactions', _('Enter Stock Counts'), '/StockCounts.php', 100);
NewMenuItem('stock', 'Transactions', _('Create a New Internal Stock Request'), '/InternalStockRequest.php?New=Yes', 110);
NewMenuItem('stock', 'Transactions', _('Authorise Internal Stock Requests'), '/InternalStockRequestAuthorisation.php', 120);
NewMenuItem('stock', 'Transactions', _('Fulfil Internal Stock Requests'), '/InternalStockRequestFulfill.php', 130);
NewMenuItem('stock', 'Transactions', _('Returned Items Maintenance'), '/KLReturnedItems.php', 140);

NewMenuItem('stock', 'Reports', _('Serial Item Research Tool'), '/StockSerialItemResearch.php', 10);
NewMenuItem('stock', 'Reports', _('KL Print Price Labels'), '/KLPDFPrintLabelTCPDF.php', 20);
NewMenuItem('stock', 'Reports', _('Reprint GRN'), '/ReprintGRN.php', 30);
NewMenuItem('stock', 'Reports', _('Inventory Item Movements'), '/StockMovements.php', 40);
NewMenuItem('stock', 'Reports', _('Inventory Item Status'), '/StockStatus.php', 50);
NewMenuItem('stock', 'Reports', _('Shop Inventory for SPG'), '/KLShopInventorySPG.php', 60);
NewMenuItem('stock', 'Reports', _('Item Movements for SPG'), '/KLStockMovementsSPG.php', 70);
NewMenuItem('stock', 'Reports', _('Item Status at all KL Shops for SPG'), '/KLStockStatusSPG.php', 80);
NewMenuItem('stock', 'Reports', _('Item Status at all KL Shops for Manager'), '/KLStockStatusManagers.php', 90);
NewMenuItem('stock', 'Reports', _('Shop Transfers List for SPG'), '/KLShopTransfersSPG.php', 100);
NewMenuItem('stock', 'Reports', _('Item Sold List for SPG'), '/KLShopSalesSPG.php', 110);
NewMenuItem('stock', 'Reports', _('Inventory Item Usage'), '/StockUsage.php', 120);
NewMenuItem('stock', 'Reports', _('Inventory Quantities'), '/InventoryQuantities.php', 130);
NewMenuItem('stock', 'Reports', _('Reorder Level'), '/ReorderLevel.php', 140);
NewMenuItem('stock', 'Reports', _('Stock Dispatch'), '/StockDispatch.php', 150);
NewMenuItem('stock', 'Reports', _('Inventory Valuation Report'), '/InventoryValuation.php', 160);
NewMenuItem('stock', 'Reports', _('Mail Inventory Valuation Report'), '/MailInventoryValuation.php', 170);
NewMenuItem('stock', 'Reports', _('Inventory Planning Report'), '/InventoryPlanning.php', 180);
NewMenuItem('stock', 'Reports', _('Inventory Planning Based On Preferred Supplier Data'), '/InventoryPlanningPrefSupplier.php', 190);
NewMenuItem('stock', 'Reports', _('Inventory Stock Check Sheets'), '/StockCheck.php', 200);
NewMenuItem('stock', 'Reports', _('Make Inventory Quantities CSV'), '/StockQties_csv.php', 210);
NewMenuItem('stock', 'Reports', _('Compare Counts Vs Stock Check Data'), '/PDFStockCheckComparison.php', 220);
NewMenuItem('stock', 'Reports', _('All Inventory Movements By Location/Date'), '/StockLocMovements.php', 230);
NewMenuItem('stock', 'Reports', _('List Inventory Status By Location/Category'), '/StockLocStatus.php', 240);
NewMenuItem('stock', 'Reports', _('Historical Stock Quantity By Location/Category'), '/StockQuantityByDate.php', 250);
NewMenuItem('stock', 'Reports', _('List Negative Stocks'), '/PDFStockNegatives.php', 260);
NewMenuItem('stock', 'Reports', _('Period Stock Transaction Listing'), '/PDFPeriodStockTransListing.php', 270);
NewMenuItem('stock', 'Reports', _('KL Excel Inventory Taking At Location'), '/KLExcelInventoryTaking.php', 280);
NewMenuItem('stock', 'Reports', _('KL Stock Available Not in Shop'), '/KLAvailableItemsNotInShop.php', 290);
NewMenuItem('stock', 'Reports', _('KL Active Transfer Status'), '/KLTransferStatus.php', 30);
NewMenuItem('stock', 'Reports', _('KL Price Analysis'), '/KLExcelPriceAnalysis.php', 310);
NewMenuItem('stock', 'Reports', _('KL Inventory Distribution By Type'), '/KLInventoryDistribution.php', 320);
NewMenuItem('stock', 'Reports', _('Stock Transfer Note'), '/PDFStockTransfer.php', 330);
NewMenuItem('stock', 'Reports', _('Aged Controlled Stock Report'), '/AgedControlledInventory.php', 340);
NewMenuItem('stock', 'Reports', _('Internal stock request inquiry'), '/InternalStockRequestInquiry.php', 350);
NewMenuItem('stock', 'Reports', _('Pending Internal Stock Requests for SPG'), '/KLInternalStockRequestView.php', 360);
NewMenuItem('stock', 'Reports', _('KL Service Fee Calculator'), '/KLServiceFeeCalculator.php', 370);

NewMenuItem('stock', 'Maintenance', _('Add A New Item'), '/Stocks.php', 10);
NewMenuItem('stock', 'Maintenance', _('Select An Item'), '/SelectProduct.php', 20);
NewMenuItem('stock', 'Maintenance', _('Review Translated Descriptions'), '/RevisionTranslations.php', 30);
NewMenuItem('stock', 'Maintenance', _('Sales Category Maintenance'), '/SalesCategories.php', 40);
NewMenuItem('stock', 'Maintenance', _('Item Tags Maintenance'), '/KLItemTags.php', 50);
NewMenuItem('stock', 'Maintenance', _('Brands Maintenance'), '/Manufacturers.php', 60);
NewMenuItem('stock', 'Maintenance', _('Add or Update Prices Based On Costs'), '/PricesBasedOnMarkUp.php', 70);
NewMenuItem('stock', 'Maintenance', _('View or Update Prices Based On Costs'), '/PricesByCost.php', 80);
NewMenuItem('stock', 'Maintenance', _('KL Automatic Reorder Level Adjustments'), '/KLAdjustReorderLevel.php', 90);
NewMenuItem('stock', 'Maintenance', _('KL Price Change Process - Step 01'), '/KLRetailPriceChangeStep01.php', 100);
NewMenuItem('stock', 'Maintenance', _('KL Price Change Process - Step 02'), '/KLRetailPriceChangeStep02.php', 110);
NewMenuItem('stock', 'Maintenance', _('KL Move To 20% Discount Process - Step 01'), '/KLMoveToDiscount20Step01.php', 120);
NewMenuItem('stock', 'Maintenance', _('KL Move To 20% Discount Process - Step 02'), '/KLMoveToDiscount20Step02.php', 130);
NewMenuItem('stock', 'Maintenance', _('KL Move To 50% Discount Process - Step 01'), '/KLMoveToDiscount50Step01.php', 140);
NewMenuItem('stock', 'Maintenance', _('KL Move To 50% Discount Process - Step 02'), '/KLMoveToDiscount50Step02.php', 150);
NewMenuItem('stock', 'Maintenance', _('KL Move To 80% Discount Process - Step 01'), '/KLMoveToDiscount80Step01.php', 160);
NewMenuItem('stock', 'Maintenance', _('KL Move To 80% Discount Process - Step 02'), '/KLMoveToDiscount80Step02.php', 170);
NewMenuItem('stock', 'Maintenance', _('Reorder Level By Category/Location'), '/ReorderLevelLocation.php', 180);
NewMenuItem('stock', 'Maintenance', _('KL Set All Reorder Level Zero in Location'), '/KLSetAllRLZero.php', 190);
NewMenuItem('stock', 'Maintenance', _('KL Copy Reorder Level From one Location to another'), '/KLCopyRLBetweenShops.php', 200);
NewMenuItem('stock', 'Maintenance', _('Upload new prices from csv file'), '/UploadPriceList.php', 210);

NewMenuItem('manuf', 'Transactions', _('Work Order Entry'), '/WorkOrderEntry.php?New=True', 10);
NewMenuItem('manuf', 'Transactions', _('Select A Work Order'), '/SelectWorkOrder.php', 20);
NewMenuItem('manuf', 'Transactions', _('QA Samples and Test Results'), '/SelectQASamples.php', 30);
NewMenuItem('manuf', 'Transactions', _('Timesheet Entry'), '/Timesheets.php', 40);

NewMenuItem('manuf', 'Reports', _('Select A Work Order'), '/SelectWorkOrder.php', 10);
NewMenuItem('manuf', 'Reports', _('Costed Bill Of Material Inquiry'), '/BOMInquiry.php', 20);
NewMenuItem('manuf', 'Reports', _('Where Used Inquiry'), '/WhereUsedInquiry.php', 30);
NewMenuItem('manuf', 'Reports', _('Bill Of Material Listing'), '/BOMListing.php', 40);
NewMenuItem('manuf', 'Reports', _('Indented Bill Of Material Listing'), '/BOMIndented.php', 50);
NewMenuItem('manuf', 'Reports', _('List Components Required'), '/BOMExtendedQty.php', 60);
NewMenuItem('manuf', 'Reports', _('List Materials Not Used anywhere'), '/MaterialsNotUsed.php', 70);
NewMenuItem('manuf', 'Reports', _('Indented Where Used Listing'), '/BOMIndentedReverse.php', 80);
NewMenuItem('manuf', 'Reports', _('MRP'), '/MRPReport.php', 90);
NewMenuItem('manuf', 'Reports', _('MRP Shortages'), '/MRPShortages.php', 100);
NewMenuItem('manuf', 'Reports', _('MRP Suggested Purchase Orders'), '/MRPPlannedPurchaseOrders.php', 110);
NewMenuItem('manuf', 'Reports', _('MRP Suggested Work Orders'), '/MRPPlannedWorkOrders.php', 120);
NewMenuItem('manuf', 'Reports', _('MRP Reschedules Required'), '/MRPReschedules.php', 130);
NewMenuItem('manuf', 'Reports', _('Print Product Specification'), '/PDFProdSpec.php', 140);
NewMenuItem('manuf', 'Reports', _('Print Certificate of Analysis'), '/PDFCOA.php', 150);
NewMenuItem('manuf', 'Reports', _('Historical QA Test Results'), '/HistoricalTestResults.php', 160);
NewMenuItem('manuf', 'Reports', _('Multiple Work Orders Total Cost Inquiry'), '/CollectiveWorkOrderCost.php', 170);

NewMenuItem('manuf', 'Maintenance', _('Work Centre'), '/WorkCentres.php', 10);
NewMenuItem('manuf', 'Maintenance', _('Bills Of Material'), '/BOMs.php', 20);
NewMenuItem('manuf', 'Maintenance', _('Copy a Bill Of Materials Between Items'), '/CopyBOM.php', 30);
NewMenuItem('manuf', 'Maintenance', _('Master Schedule'), '/MRPDemands.php', 40);
NewMenuItem('manuf', 'Maintenance', _('Auto Create Master Schedule'), '/MRPCreateDemands.php', 50);
NewMenuItem('manuf', 'Maintenance', _('MRP Calculation'), '/MRP.php', 60);
NewMenuItem('manuf', 'Maintenance', _('Quality Tests Maintenance'), '/QATests.php', 70);
NewMenuItem('manuf', 'Maintenance', _('Product Specifications'), '/ProductSpecs.php', 80);
NewMenuItem('manuf', 'Maintenance', _('Employees'), '/Employees.php', 90);

NewMenuItem('GL', 'Transactions', _('Bank Account Payments Entry'), '/Payments.php?NewPayment=Yes', 10);
NewMenuItem('GL', 'Transactions', _('Bank Account Receipts Entry'), '/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL', 20);
NewMenuItem('GL', 'Transactions', _('Import Bank Transactions'), '/ImportBankTrans.php', 30);
NewMenuItem('GL', 'Transactions', _('Journal Entry'), '/GLJournal.php?NewJournal=Yes', 40);
NewMenuItem('GL', 'Transactions', _('Bank Account Payments Matching'), '/BankMatching.php?Type=Payments', 50);
NewMenuItem('GL', 'Transactions', _('Bank Account Receipts Matching'), '/BankMatching.php?Type=Receipts', 60);
NewMenuItem('GL', 'Transactions', _('KL Consignment Invoices'), '/KLConsignmentInvoice.php', 70);
NewMenuItem('GL', 'Transactions', _('KL Export CSV for Faktur Pajak'), '/KLConsignmentCSVFakturPajak.php', 80);
NewMenuItem('GL', 'Transactions', _('Process Regular Payments'), '/RegularPaymentsProcess.php', 90);

NewMenuItem('GL', 'Reports', _('Bank Account Balances'), '/BankAccountBalances.php', 1);
NewMenuItem('GL', 'Reports', _('Bank Account Reconciliation Statement'), '/BankReconciliation.php', 2);
NewMenuItem('GL', 'Reports', _('Cheque Payments Listing'), '/PDFChequeListing.php', 3);
NewMenuItem('GL', 'Reports', _('Daily Bank Transactions'), '/DailyBankTransactions.php', 4);
NewMenuItem('GL', 'Reports', _('Account Inquiry'), '/SelectGLAccount.php', 5);
NewMenuItem('GL', 'Reports', _('Graph of Account Transactions'), '/GLAccountGraph.php', 6);
NewMenuItem('GL', 'Reports', _('Account Listing'), '/GLAccountReport.php', 7);
NewMenuItem('GL', 'Reports', _('Account Listing to CSV File'), '/GLAccountCSV.php', 8);
NewMenuItem('GL', 'Reports', _('General Ledger Journal Inquiry'), '/GLJournalInquiry.php', 9);
NewMenuItem('GL', 'Reports', _('Trial Balance'), '/GLTrialBalance.php', 10);
NewMenuItem('GL', 'Reports', _('Balance Sheet'), '/GLBalanceSheet.php', 11);
NewMenuItem('GL', 'Reports', _('Profit and Loss Statement'), '/GLProfit_Loss.php', 12);
NewMenuItem('GL', 'Reports', _('KL Cash Variation'), '/KLGLCashVariation.php', 13);
NewMenuItem('GL', 'Reports', _('Statement of Cash Flows'), '/GLCashFlowsIndirect.php', 13);
NewMenuItem('GL', 'Reports', _('Export Excel GL Transactions for PT'), '/KLExcelGLTransactionsPajak.php', 13);
NewMenuItem('GL', 'Reports', _('Financial Statements'), '/GLStatements.php', 14);
NewMenuItem('GL', 'Reports', _('Horizontal Analysis of Statement of Financial Position'), '/AnalysisHorizontalPosition.php', 15);
NewMenuItem('GL', 'Reports', _('Horizontal Analysis of Statement of Comprehensive Income'), '/AnalysisHorizontalIncome.php', 16);
NewMenuItem('GL', 'Reports', _('KL Consignment Invoices Issued List'), '/KLConsignmentInvoiceIssuedList.php', 17);
NewMenuItem('GL', 'Reports', _('Tag Reports'), '/GLTagProfit_Loss.php', 17);
NewMenuItem('GL', 'Reports', _('Tax Reports'), '/Tax.php', 18);

NewMenuItem('GL', 'Maintenance', _('Account Sections'), '/AccountSections.php', 10);
NewMenuItem('GL', 'Maintenance', _('Account Groups'), '/AccountGroups.php', 20);
NewMenuItem('GL', 'Maintenance', _('GL Account'), '/GLAccounts.php', 30);
NewMenuItem('GL', 'Maintenance', _('GL Accounts for PT. Angin Dingin Utara'), '/KLGLAccountsADU.php', 40);
NewMenuItem('GL', 'Maintenance', _('GL Accounts for PT. Sungai Mutiara Hitam'), '/KLGLAccountsSMH.php', 50);
NewMenuItem('GL', 'Maintenance', _('GL Accounts for PT. Bumi Biru'), '/KLGLAccountsBB.php', 60);
NewMenuItem('GL', 'Maintenance', _('GL Accounts for Retail Partner POIK'), '/KLGLAccountsIK.php', 70);
NewMenuItem('GL', 'Maintenance', _('GL Accounts for Retail Partner POPI'), '/KLGLAccountsPI.php', 80);
NewMenuItem('GL', 'Maintenance', _('GL Budgets'), '/GLBudgets.php', 90);
NewMenuItem('GL', 'Maintenance', _('GL Tags'), '/GLTags.php', 100);
NewMenuItem('GL', 'Maintenance', _('GL Account Authorised Users'), '/GLAccountUsers.php', 110);
NewMenuItem('GL', 'Maintenance', _('User Authorised GL Accounts'), '/UserGLAccounts.php', 120);
NewMenuItem('GL', 'Maintenance', _('Copy Authority GL Accounts from one user to another'), '/GLAccountUsersCopyAuthority.php', 130);
NewMenuItem('GL', 'Maintenance', _('Bank Accounts'), '/BankAccounts.php', 140);
NewMenuItem('GL', 'Maintenance', _('Bank Account Authorised Users'), '/BankAccountUsers.php', 150);
NewMenuItem('GL', 'Maintenance', _('User Authorised Bank Accounts'), '/UserBankAccounts.php', 160);
NewMenuItem('GL', 'Maintenance', _('Copy Authority Bank Accounts from one user to another'), '/GLBankAccountUsersCopyAuthority.php', 170);
NewMenuItem('GL', 'Maintenance', _('Maintain Journal Templates'), '/GLJournalTemplates.php', 180);
NewMenuItem('GL', 'Maintenance', _('Setup Regular Payments'), '/RegularPaymentsSetup.php', 190);

NewMenuItem('FA', 'Transactions', _('Add a new Asset'), '/FixedAssetItems.php', 10);
NewMenuItem('FA', 'Transactions', _('Select an Asset'), '/SelectAsset.php', 20);
NewMenuItem('FA', 'Transactions', _('Change Asset Location'), '/FixedAssetTransfer.php', 30);
NewMenuItem('FA', 'Transactions', _('Depreciation Journal'), '/FixedAssetDepreciation.php', 40);
NewMenuItem('FA', 'Transactions', _('KL Maintenance Tasks'), '/KLMaintenanceTasks.php', 50);

NewMenuItem('FA', 'Reports', _('Asset Register'), '/FixedAssetRegister.php', 10);
NewMenuItem('FA', 'Reports', _('My Maintenance Schedule'), '/MaintenanceUserSchedule.php', 20);
NewMenuItem('FA', 'Reports', _('Maintenance Reminder Emails'), '/MaintenanceReminders.php', 30);
NewMenuItem('FA', 'Reports', _('KL Maintenance Tasks Control Board'), '/KLMaintenanceTasksBoard.php', 40);

NewMenuItem('FA', 'Maintenance', _('Fixed Asset Category Maintenance'), '/FixedAssetCategories.php', 10);
NewMenuItem('FA', 'Maintenance', _('Add or Maintain Asset Locations'), '/FixedAssetLocations.php', 20);
NewMenuItem('FA', 'Maintenance', _('Fixed Asset Maintenance Tasks'), '/MaintenanceTasks.php', 30);
NewMenuItem('FA', 'Maintenance', _('KL Maintenance Types Maintenace'), '/KLMaintenanceTypes.php', 40);

NewMenuItem('PC', 'Transactions', _('Assign Cash to PC Tab'), '/PcAssignCashToTab.php', 10);
NewMenuItem('PC', 'Transactions', _('Transfer Assigned Cash Between PC Tabs'), '/PcAssignCashTabToTab.php', 20);
NewMenuItem('PC', 'Transactions', _('Claim Expenses From PC Tab'), '/PcClaimExpensesFromTab.php', 30);
NewMenuItem('PC', 'Transactions', _('Authorise Assigned Cash'), '/PcAuthorizeCash.php', 40);
NewMenuItem('PC', 'Transactions', _('Authorise Expenses'), '/PcAuthorizeExpenses.php', 50);

NewMenuItem('PC', 'Reports', _('PC Tab General Report'), '/PcReportTab.php', 10);
NewMenuItem('PC', 'Reports', _('PC Expense General Report'), '/PcReportExpense.php', 20);
NewMenuItem('PC', 'Reports', _('PC Tab Expenses List'), '/PcTabExpensesList.php', 30);
NewMenuItem('PC', 'Reports', _('PC Expenses Analysis'), '/PcAnalysis.php', 40);

NewMenuItem('PC', 'Maintenance', _('Types of PC Tabs'), '/PcTypeTabs.php', 10);
NewMenuItem('PC', 'Maintenance', _('PC Tabs'), '/PcTabs.php', 20);
NewMenuItem('PC', 'Maintenance', _('PC Expenses'), '/PcExpenses.php', 30);
NewMenuItem('PC', 'Maintenance', _('Expenses for Type of PC Tab'), '/PcExpensesTypeTab.php', 40);

NewMenuItem('Personalia', 'Transactions', _('Import Monthly Salaries Info from Excel File'), '/KLPersonaliaImportSalaries.php', 10);
NewMenuItem('Personalia', 'Transactions', _('Export Info for PPH21 Deduction'), '/KLPersonaliaExportInfoPPH21.php', 20);
NewMenuItem('Personalia', 'Transactions', _('Update PPH21 Deduction'), '/KLPersonaliaDeductionPPH21.php', 30);
NewMenuItem('Personalia', 'Transactions', _('Export Monthly Salary Slips'), '/KLPersonaliaPDFMonthlySalarySlips.php', 40);
NewMenuItem('Personalia', 'Transactions', _('Send Monthly Salary Slips by e-mail'), '/KLPersonaliaEmailMonthlySalarySlips.php', 50);
NewMenuItem('Personalia', 'Transactions', _('Export CSV File for Danamon Transfer LLG'), '/KLPersonaliaCSVDanamonLLG.php', 60);
NewMenuItem('Personalia', 'Transactions', _('Export CSV File for Danamon Transfer Payroll'), '/KLPersonaliaCSVDanamonPayroll.php', 70);
NewMenuItem('Personalia', 'Transactions', _('Export CSV File for Danamon Transfer @ Cash Connect'), '/KLPersonaliaCSVDanamonCashConnect.php', 80);
NewMenuItem('Personalia', 'Transactions', _('Move Salaries info to Petty Cash'), '/KLPersonaliaMoveTxToPC.php', 90);

NewMenuItem('Personalia', 'Maintenance', _('SPG List'), '/SalesPeople.php', 10);
NewMenuItem('Personalia', 'Maintenance', _('User Maintenance'), '/WWW_Users.php', 20);
NewMenuItem('Personalia', 'Maintenance', _('KL SPG User Maintenance'), '/KLUsersSPG.php', 30);

NewMenuItem('system', 'Transactions', _('Company Preferences'), '/CompanyPreferences.php', 10);
NewMenuItem('system', 'Transactions', _('System Parameters'), '/SystemParameters.php', 20);
NewMenuItem('system', 'Transactions', _('KL Retail Partners'), '/KLRetailPartners.php', 30);
NewMenuItem('system', 'Transactions', _('KL Online Partners'), '/KLOnlinePartners.php', 40);
NewMenuItem('system', 'Transactions', _('Maintain Security Tokens'), '/SecurityTokens.php', 50);
NewMenuItem('system', 'Transactions', _('Access Permissions Maintenance'), '/WWW_Access.php', 60);
NewMenuItem('system', 'Transactions', _('Script Security Settings'), '/PageSecurity.php', 70);
NewMenuItem('system', 'Transactions', _('Currency Maintenance'), '/Currencies.php', 80);
NewMenuItem('system', 'Transactions', _('Tax Authorities and Rates Maintenance'), '/TaxAuthorities.php', 90);
NewMenuItem('system', 'Transactions', _('Tax Group Maintenance'), '/TaxGroups.php', 100);
NewMenuItem('system', 'Transactions', _('Dispatch Tax Province Maintenance'), '/TaxProvinces.php', 110);
NewMenuItem('system', 'Transactions', _('Tax Category Maintenance'), '/TaxCategories.php', 120);
NewMenuItem('system', 'Transactions', _('List Periods Defined'), '/PeriodsInquiry.php', 130);
NewMenuItem('system', 'Transactions', _('Report Builder Tool'), '/reportwriter/admin/ReportCreator.php', 140);
NewMenuItem('system', 'Transactions', _('View Audit Trail'), '/AuditTrail.php', 150);
NewMenuItem('system', 'Transactions', _('View Scripts Audit'), '/AuditScripts.php', 160);
NewMenuItem('system', 'Transactions', _('Geocode Maintenance'), '/GeocodeSetup.php', 170);
NewMenuItem('system', 'Transactions', _('Form Designer'), '/FormDesigner.php', 180);
NewMenuItem('system', 'Transactions', _('Web-Store Configuration'), '/ShopParameters.php', 190);
NewMenuItem('system', 'Transactions', _('SMTP Server Details'), '/SMTPServer.php', 200);
NewMenuItem('system', 'Transactions', _('Mailing Group Maintenance'), '/MailingGroupMaintenance.php', 210);
NewMenuItem('system', 'Transactions', _('Test Silent Printing'), '/KLRetailPOSTest.php', 220);

NewMenuItem('system', 'Reports', _('Sales Types'), '/SalesTypes.php', 10);
NewMenuItem('system', 'Reports', _('Returned Item Reasons'), '/KLReturnedItemsReasons.php', 20);
NewMenuItem('system', 'Reports', _('Customer Types'), '/CustomerTypes.php', 30);
NewMenuItem('system', 'Reports', _('Supplier Types'), '/SupplierTypes.php', 40);
NewMenuItem('system', 'Reports', _('Credit Status'), '/CreditStatus.php', 50);
NewMenuItem('system', 'Reports', _('Payment Terms'), '/PaymentTerms.php', 60);
NewMenuItem('system', 'Reports', _('Set Purchase Order Authorisation levels'), '/PO_AuthorisationLevels.php', 70);
NewMenuItem('system', 'Reports', _('Payment Methods'), '/PaymentMethods.php', 80);
NewMenuItem('system', 'Reports', _('Sales Areas'), '/Areas.php', 90);
NewMenuItem('system', 'Reports', _('Shippers'), '/Shippers.php', 100);
NewMenuItem('system', 'Reports', _('Sales GL Interface Postings'), '/SalesGLPostings.php', 110);
NewMenuItem('system', 'Reports', _('COGS GL Interface Postings'), '/COGSGLPostings.php', 120);
NewMenuItem('system', 'Reports', _('Freight Costs Maintenance'), '/FreightCosts.php', 130);
NewMenuItem('system', 'Reports', _('Discount Matrix'), '/DiscountMatrix.php', 140);
NewMenuItem('system', 'Reports', _('Sales Commission Types'), '/SalesCommissionTypes.php', 150);

NewMenuItem('system', 'Maintenance', _('Inventory Categories Maintenance'), '/StockCategories.php', 10);
NewMenuItem('system', 'Maintenance', _('Inventory Locations Zones Maintenance'), '/LocationZones.php', 20);
NewMenuItem('system', 'Maintenance', _('Inventory Locations Types Maintenance'), '/LocationTypes.php', 30);
NewMenuItem('system', 'Maintenance', _('Inventory Locations Maintenance'), '/Locations.php', 40);
NewMenuItem('system', 'Maintenance', _('Inventory Location Authorised Users'), '/LocationUsers.php', 50);
NewMenuItem('system', 'Maintenance', _('User Authorised Inventory Locations'), '/UserLocations.php', 60);
NewMenuItem('system', 'Maintenance', _('Copy Authority Locations from one user to another'), '/LocationUsersCopyAuthority.php', 70);
NewMenuItem('system', 'Maintenance', _('Discount Category Maintenance'), '/DiscountCategories.php', 80);
NewMenuItem('system', 'Maintenance', _('Units of Measure'), '/UnitsOfMeasure.php', 90);
NewMenuItem('system', 'Maintenance', _('MRP Available Production Days'), '/MRPCalendar.php', 100);
NewMenuItem('system', 'Maintenance', _('MRP Demand Types'), '/MRPDemandTypes.php', 110);
NewMenuItem('system', 'Maintenance', _('Maintain Internal Departments'), '/Departments.php', 120);
NewMenuItem('system', 'Maintenance', _('Maintain Internal Stock Categories to User Roles'),'/InternalStockCategoriesByRole.php', 130);
NewMenuItem('system', 'Maintenance', _('KL Label Templates Maintenance'), '/KLLabels.php', 140);
NewMenuItem('system', 'Maintenance', _('webERP Label Templates Maintenance'), '/Labels.php', 150);
NewMenuItem('system', 'Maintenance', _('Dashboard Configuration'), '/DashboardConfig.php', 160);

NewMenuItem('Utilities', 'Transactions', _('Change A Customer Code'), '/Z_ChangeCustomerCode.php', 10);
NewMenuItem('Utilities', 'Transactions', _('Change A Customer Branch Code'), '/Z_ChangeBranchCode.php', 20);
NewMenuItem('Utilities', 'Transactions', _('Change A GL Account Code'), '/Z_ChangeGLAccountCode.php', 30);
NewMenuItem('Utilities', 'Transactions', _('Change An Inventory Item Code'), '/Z_ChangeStockCode.php', 40);
NewMenuItem('Utilities', 'Transactions', _('Change A Location Code'), '/Z_ChangeLocationCode.php', 50);
NewMenuItem('Utilities', 'Transactions', _('Change A Salesman Code'), '/Z_ChangeSalesmanCode.php', 60);
NewMenuItem('Utilities', 'Transactions', _('Change A Stock Category Code'), '/Z_ChangeStockCategory.php', 70);
NewMenuItem('Utilities', 'Transactions', _('Change A Supplier Code'), '/Z_ChangeSupplierCode.php', 80);
NewMenuItem('Utilities', 'Transactions', _('Translate Item Descriptions'), '/AutomaticTranslationDescriptions.php', 90);
NewMenuItem('Utilities', 'Transactions', _('Update costs for all BOM items, from the bottom up'), '/Z_BottomUpCosts.php', 100);
NewMenuItem('Utilities', 'Transactions', _('Re-apply costs to Sales Analysis'), '/Z_ReApplyCostToSA.php', 110);
NewMenuItem('Utilities', 'Transactions', _('Delete sales transactions'), '/Z_DeleteSalesTransActions.php', 120);
NewMenuItem('Utilities', 'Transactions', _('Reverse all supplier payments on a specified date'), '/Z_ReverseSuppPaymentRun.php', 130);
NewMenuItem('Utilities', 'Transactions', _('Update sales analysis with latest customer data'), '/Z_UpdateSalesAnalysisWithLatestCustomerData.php', 140);

NewMenuItem('Utilities', 'Reports', _('Debtors Balances By Currency Totals'), '/Z_CurrencyDebtorsBalances.php', 10);
NewMenuItem('Utilities', 'Reports', _('Suppliers Balances By Currency Totals'), '/Z_CurrencySuppliersBalances.php', 20);
NewMenuItem('Utilities', 'Reports', _('Show General Transactions That Do Not Balance'), '/Z_CheckGLTransBalance.php', 30);
NewMenuItem('Utilities', 'Reports', _('List of items without picture'), '/Z_ItemsWithoutPicture.php', 40);

NewMenuItem('Utilities', 'Maintenance', _('Maintain Language Files'), '/Z_poAdmin.php', 10);
NewMenuItem('Utilities', 'Maintenance', _('Make New Company'), '/Z_MakeNewCompany.php', 20);
NewMenuItem('Utilities', 'Maintenance', _('Data Export Options'), '/Z_DataExport.php', 30);
NewMenuItem('Utilities', 'Maintenance', _('Import Customers from .csv file'), '/Z_ImportDebtors.php', 40);
NewMenuItem('Utilities', 'Maintenance', _('Import Stock Items from .csv'), '/Z_ImportStocks.php', 50);
NewMenuItem('Utilities', 'Maintenance', _('Import Price List from .csv file'), '/Z_ImportPriceList.php', 60);
NewMenuItem('Utilities', 'Maintenance', _('Import Fixed Assets from .csv file'), '/Z_ImportFixedAssets.php', 70);
NewMenuItem('Utilities', 'Maintenance', _('Import GL Payments Receipts Or Journals From .csv file'), '/Z_ImportGLTransactions.php', 80);
NewMenuItem('Utilities', 'Maintenance', _('Create new company template SQL file and submit to webERP'), '/Z_CreateCompanyTemplateFile.php', 90);
NewMenuItem('Utilities', 'Maintenance', _('Re-calculate brought forward amounts in GL'), '/Z_UpdateChartDetailsBFwd.php', 100);
NewMenuItem('Utilities', 'Maintenance', _('Re-Post all GL transactions from a specified period'), '/Z_RePostGLFromPeriod.php', 110);
NewMenuItem('Utilities', 'Maintenance', _('Purge all old prices'), '/Z_DeleteOldPrices.php', 120);
NewMenuItem('Utilities', 'Maintenance', _('Remove all purchase back orders'), '/Z_RemovePurchaseBackOrders.php', 130);
NewMenuItem('Utilities', 'Maintenance', _('KL Daily Database Maintenance'), '/KLMaintainDatabase.php', 140);
NewMenuItem('Utilities', 'Maintenance', _('KL Purge Old Data in Database'), '/KLPurgeOldData.php', 150);

UpdateDBNo(basename(__FILE__, '.php'), _('Move the menu and module strings to the database'));

?>