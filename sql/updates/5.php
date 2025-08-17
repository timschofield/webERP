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


NewModule('Sales', 'ord', __('Sales'), 1);
NewModule('AR', 'ar', __('Receivables'), 2);
NewModule('PO', 'prch', __('Purchases'), 3);
NewModule('AP', 'ap', __('Payables'), 4);
NewModule('stock', 'inv', __('Inventory'), 5);
NewModule('manuf', 'man', __('Manufacturing'), 6);
NewModule('GL', 'gl', __('General Ledger'), 7);
NewModule('FA', 'fa', __('Asset Manager'), 8);
NewModule('PC', 'pc', __('Petty Cash'), 9);
NewModule('system', 'sys', __('Setup'), 10);
NewModule('Utilities', 'util', __('Utilities'), 11);

NewMenuItem('Sales', 'Transactions', __('New Sales Order or Quotation'), '/SelectOrderItems.php?NewOrder=Yes', 1);
NewMenuItem('Sales', 'Transactions', __('Enter Counter Sales'), '/CounterSales.php', 2);
NewMenuItem('Sales', 'Transactions', __('Enter Counter Returns'), '/CounterReturns.php', 3);
NewMenuItem('Sales', 'Transactions', __('Generate/Print Picking Lists'), '/PDFPickingList.php', 4);
NewMenuItem('Sales', 'Transactions', __('Outstanding Sales Orders/Quotations'), '/SelectSalesOrder.php', 5);
NewMenuItem('Sales', 'Transactions', __('Special Order'), '/SpecialOrder.php', 6);
NewMenuItem('Sales', 'Transactions', __('Recurring Order Template'), '/SelectRecurringSalesOrder.php', 7);
NewMenuItem('Sales', 'Transactions', __('Process Recurring Orders'), '/RecurringSalesOrdersProcess.php', 8);
NewMenuItem('Sales', 'Transactions', __('Maintain Picking Lists'), '/SelectPickingLists.php', 9);

NewMenuItem('Sales', 'Reports', __('Sales Order Inquiry'), '/SelectCompletedOrder.php', 1);
NewMenuItem('Sales', 'Reports', __('Print Price Lists'), '/PDFPriceList.php', 2);
NewMenuItem('Sales', 'Reports', __('Order Status Report'), '/PDFOrderStatus.php', 3);
NewMenuItem('Sales', 'Reports', __('Orders Invoiced Reports'), '/PDFOrdersInvoiced.php', 4);
NewMenuItem('Sales', 'Reports', __('Daily Sales Inquiry'), '/DailySalesInquiry.php', 5);
NewMenuItem('Sales', 'Reports', __('Sales By Sales Type Inquiry'), '/SalesByTypePeriodInquiry.php', 6);
NewMenuItem('Sales', 'Reports', __('Sales By Category Inquiry'), '/SalesCategoryPeriodInquiry.php', 7);
NewMenuItem('Sales', 'Reports', __('Sales By Category By Item Inquiry'), '/StockCategorySalesInquiry.php', 8);
NewMenuItem('Sales', 'Reports', __('Sales Analysis Reports'), '/SalesAnalRepts.php', 9);
NewMenuItem('Sales', 'Reports', __('Sales Graphs'), '/SalesGraph.php', 10);
NewMenuItem('Sales', 'Reports', __('Top Sellers Inquiry'), '/SalesTopItemsInquiry.php', 11);
NewMenuItem('Sales', 'Reports', __('Order Delivery Differences Report'), '/PDFDeliveryDifferences.php', 12);
NewMenuItem('Sales', 'Reports', __('Delivery In Full On Time (DIFOT) Report'), '/PDFDIFOT.php', 13);
NewMenuItem('Sales', 'Reports', __('Sales Order Detail Or Summary Inquiries'), '/SalesInquiry.php', 14);
NewMenuItem('Sales', 'Reports', __('Top Sales Items Report'), '/TopItems.php', 15);
NewMenuItem('Sales', 'Reports', __('Top Customers Inquiry'), '/SalesTopCustomersInquiry.php', 16);
NewMenuItem('Sales', 'Reports', __('Worst Sales Items Report'), '/NoSalesItems.php', 17);
NewMenuItem('Sales', 'Reports', __('Sales With Low Gross Profit Report'), '/PDFLowGP.php', 18);
NewMenuItem('Sales', 'Reports', __('Sell Through Support Claims Report'), '/PDFSellThroughSupportClaim.php', 19);
NewMenuItem('Sales', 'Reports', __('Sales to Customers'), '/SalesReport.php', 20);
NewMenuItem('Sales', 'Reports', __('Sales Commission Reports'), '/SalesCommissionReports.php', 21);

NewMenuItem('Sales', 'Maintenance', __('Create Contract'), '/Contracts.php', 1);
NewMenuItem('Sales', 'Maintenance', __('Select Contract'), '/SelectContract.php', 2);
NewMenuItem('Sales', 'Maintenance', __('Sell Through Support Deals'), '/SellThroughSupport.php', 3);

NewMenuItem('AR', 'Transactions', __('Select Order to Invoice'), '/SelectSalesOrder.php', 1);
NewMenuItem('AR', 'Transactions', __('Create A Credit Note'), '/SelectCreditItems.php?NewCredit=Yes', 2);
NewMenuItem('AR', 'Transactions', __('Enter Receipts'), '/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer', 3);
NewMenuItem('AR', 'Transactions', __('Allocate Receipts or Credit Notes'), '/CustomerAllocations.php', 4);

NewMenuItem('AR', 'Reports', __('Where Allocated Inquiry'), '/CustWhereAlloc.php', 1);
NewMenuItem('AR', 'Reports', __('Print Invoices or Credit Notes'), '/PrintCustTrans.php', 2);
NewMenuItem('AR', 'Reports', __('Print Statements'), '/PrintCustStatements.php', 3);
NewMenuItem('AR', 'Reports', __('Aged Customer Balances/Overdues Report'), '/AgedDebtors.php', 4);
NewMenuItem('AR', 'Reports', __('Re-Print A Deposit Listing'), '/PDFBankingSummary.php', 5);
NewMenuItem('AR', 'Reports', __('Debtor Balances At A Prior Month End'), '/DebtorsAtPeriodEnd.php', 6);
NewMenuItem('AR', 'Reports', __('Customer Listing By Area/Salesperson'), '/PDFCustomerList.php', 7);
NewMenuItem('AR', 'Reports', __('List Daily Transactions'), '/PDFCustTransListing.php', 8);
NewMenuItem('AR', 'Reports', __('Customer Transaction Inquiries'), '/CustomerTransInquiry.php', 9);
NewMenuItem('AR', 'Reports', __('Customer Activity and Balances'), '/CustomerBalancesMovement.php', 10);

NewMenuItem('AR', 'Maintenance', __('Add Customer'), '/Customers.php', 1);
NewMenuItem('AR', 'Maintenance', __('Select Customer'), '/SelectCustomer.php', 2);

NewMenuItem('AP', 'Transactions', __('Select Supplier'), '/SelectSupplier.php', 1);
NewMenuItem('AP', 'Transactions', __('Supplier Allocations'), '/SupplierAllocations.php', 2);

NewMenuItem('AP', 'Reports', __('Where Allocated Inquiry'), '/SuppWhereAlloc.php', 1);
NewMenuItem('AP', 'Reports', __('Aged Supplier Report'), '/AgedSuppliers.php', 2);
NewMenuItem('AP', 'Reports', __('Payment Run Report'), '/SuppPaymentRun.php', 3);
NewMenuItem('AP', 'Reports', __('Remittance Advices'), '/PDFRemittanceAdvice.php', 4);
NewMenuItem('AP', 'Reports', __('Outstanding GRNs Report'), '/OutstandingGRNs.php', 5);
NewMenuItem('AP', 'Reports', __('Supplier Balances At A Prior Month End'), '/SupplierBalsAtPeriodEnd.php', 6);
NewMenuItem('AP', 'Reports', __('List Daily Transactions'), '/PDFSuppTransListing.php', 7);
NewMenuItem('AP', 'Reports', __('Supplier Transaction Inquiries'), '/SupplierTransInquiry.php', 8);

NewMenuItem('AP', 'Maintenance', __('Add Supplier'), '/Suppliers.php', 1);
NewMenuItem('AP', 'Maintenance', __('Select Supplier'), '/SelectSupplier.php', 2);
NewMenuItem('AP', 'Maintenance', __('Maintain Factor Companies'), '/Factors.php', 3);

NewMenuItem('PO', 'Transactions', __('New Purchase Order'), '/PO_Header.php?NewOrder=Yes', 1);
NewMenuItem('PO', 'Transactions', __('Purchase Orders'), '/PO_SelectOSPurchOrder.php', 2);
NewMenuItem('PO', 'Transactions', __('Purchase Order Grid Entry'), '/PurchaseByPrefSupplier.php', 3);
NewMenuItem('PO', 'Transactions', __('Create a New Tender'), '/SupplierTenderCreate.php?New=Yes', 4);
NewMenuItem('PO', 'Transactions', __('Edit Existing Tenders'), '/SupplierTenderCreate.php?Edit=Yes', 5);
NewMenuItem('PO', 'Transactions', __('Process Tenders and Offers'), '/OffersReceived.php', 6);
NewMenuItem('PO', 'Transactions', __('Orders to Authorise'), '/PO_AuthoriseMyOrders.php', 7);
NewMenuItem('PO', 'Transactions', __('Shipment Entry'), '/SelectSupplier.php', 8);
NewMenuItem('PO', 'Transactions', __('Select A Shipment'), '/Shipt_Select.php', 9);

NewMenuItem('PO', 'Reports', __('Purchase Order Inquiry'), '/PO_SelectPurchOrder.php', 1);
NewMenuItem('PO', 'Reports', __('Purchase Order Detail Or Summary Inquiries'), '/POReport.php', 2);
NewMenuItem('PO', 'Reports', __('Supplier Price List'), '/SuppPriceList.php', 3);
NewMenuItem('PO', 'Reports', __('Purchases from Suppliers'), '/PurchasesReport.php', 4);

NewMenuItem('PO', 'Maintenance', __('Maintain Supplier Price Lists'), '/SupplierPriceList.php', 1);

NewMenuItem('stock', 'Transactions', __('Receive Purchase Orders'), '/PO_SelectOSPurchOrder.php', 1);
NewMenuItem('stock', 'Transactions', __('Inventory Location Transfers'), '/StockTransfers.php?New=Yes', 2);
NewMenuItem('stock', 'Transactions', __('Bulk Inventory Transfer') . ' - ' . __('Dispatch'), '/StockLocTransfer.php', 3);
NewMenuItem('stock', 'Transactions', __('Bulk Inventory Transfer') . ' - ' . __('Receive'), '/StockLocTransferReceive.php', 4);
NewMenuItem('stock', 'Transactions', __('Inventory Adjustments'), '/StockAdjustments.php?NewAdjustment=Yes', 5);
NewMenuItem('stock', 'Transactions', __('Reverse Goods Received'), '/ReverseGRN.php', 6);
NewMenuItem('stock', 'Transactions', __('Enter Stock Counts'), '/StockCounts.php', 7);
NewMenuItem('stock', 'Transactions', __('Create a New Internal Stock Request'), '/InternalStockRequest.php?New=Yes', 8);
NewMenuItem('stock', 'Transactions', __('Authorise Internal Stock Requests'), '/InternalStockRequestAuthorisation.php', 9);
NewMenuItem('stock', 'Transactions', __('Fulfil Internal Stock Requests'), '/InternalStockRequestFulfill.php', 10);

NewMenuItem('stock', 'Reports', __('Serial Item Research Tool'), '/StockSerialItemResearch.php', 1);
NewMenuItem('stock', 'Reports', __('Print Price Labels'), '/PDFPrintLabel.php', 2);
NewMenuItem('stock', 'Reports', __('Reprint GRN'), '/ReprintGRN.php', 3);
NewMenuItem('stock', 'Reports', __('Inventory Item Movements'), '/StockMovements.php', 4);
NewMenuItem('stock', 'Reports', __('Inventory Item Status'), '/StockStatus.php', 5);
NewMenuItem('stock', 'Reports', __('Inventory Item Usage'), '/StockUsage.php', 6);
NewMenuItem('stock', 'Reports', __('Inventory Quantities'), '/InventoryQuantities.php', 7);
NewMenuItem('stock', 'Reports', __('Reorder Level'), '/ReorderLevel.php', 8);
NewMenuItem('stock', 'Reports', __('Stock Dispatch'), '/StockDispatch.php', 9);
NewMenuItem('stock', 'Reports', __('Inventory Valuation Report'), '/InventoryValuation.php', 10);
NewMenuItem('stock', 'Reports', __('Mail Inventory Valuation Report'), '/MailInventoryValuation.php', 11);
NewMenuItem('stock', 'Reports', __('Inventory Planning Report'), '/InventoryPlanning.php', 12);
NewMenuItem('stock', 'Reports', __('Inventory Planning Based On Preferred Supplier Data'), '/InventoryPlanningPrefSupplier.php', 13);
NewMenuItem('stock', 'Reports', __('Inventory Stock Check Sheets'), '/StockCheck.php', 14);
NewMenuItem('stock', 'Reports', __('Make Inventory Quantities CSV'), '/StockQties_csv.php', 15);
NewMenuItem('stock', 'Reports', __('Compare Counts Vs Stock Check Data'), '/PDFStockCheckComparison.php', 16);
NewMenuItem('stock', 'Reports', __('All Inventory Movements By Location/Date'), '/StockLocMovements.php', 17);
NewMenuItem('stock', 'Reports', __('List Inventory Status By Location/Category'), '/StockLocStatus.php', 18);
NewMenuItem('stock', 'Reports', __('Historical Stock Quantity By Location/Category'), '/StockQuantityByDate.php', 19);
NewMenuItem('stock', 'Reports', __('List Negative Stocks'), '/PDFStockNegatives.php', 20);
NewMenuItem('stock', 'Reports', __('Period Stock Transaction Listing'), '/PDFPeriodStockTransListing.php', 21);
NewMenuItem('stock', 'Reports', __('Stock Transfer Note'), '/PDFStockTransfer.php', 22);
NewMenuItem('stock', 'Reports', __('Aged Controlled Stock Report'), '/AgedControlledInventory.php', 23);
NewMenuItem('stock', 'Reports', __('Internal stock request inquiry'), '/InternalStockRequestInquiry.php', 24);

NewMenuItem('stock', 'Maintenance', __('Add A New Item'), '/Stocks.php', 1);
NewMenuItem('stock', 'Maintenance', __('Select An Item'), '/SelectProduct.php', 2);
NewMenuItem('stock', 'Maintenance', __('Review Translated Descriptions'), '/RevisionTranslations.php', 3);
NewMenuItem('stock', 'Maintenance', __('Sales Category Maintenance'), '/SalesCategories.php', 4);
NewMenuItem('stock', 'Maintenance', __('Brands Maintenance'), '/Manufacturers.php', 5);
NewMenuItem('stock', 'Maintenance', __('Add or Update Prices Based On Costs'), '/PricesBasedOnMarkUp.php', 6);
NewMenuItem('stock', 'Maintenance', __('View or Update Prices Based On Costs'), '/PricesByCost.php', 7);
NewMenuItem('stock', 'Maintenance', __('Upload new prices from csv file'), '/UploadPriceList.php', 8);
NewMenuItem('stock', 'Maintenance', __('Reorder Level By Category/Location'), '/ReorderLevelLocation.php', 9);

NewMenuItem('manuf', 'Transactions', __('Work Order Entry'), '/WorkOrderEntry.php?New=True', 1);
NewMenuItem('manuf', 'Transactions', __('Select A Work Order'), '/SelectWorkOrder.php', 2);
NewMenuItem('manuf', 'Transactions', __('QA Samples and Test Results'), '/SelectQASamples.php', 3);
NewMenuItem('manuf', 'Transactions', __('Timesheet Entry'), '/Timesheets.php', 4);

NewMenuItem('manuf', 'Reports', __('Select A Work Order'), '/SelectWorkOrder.php', 1);
NewMenuItem('manuf', 'Reports', __('Costed Bill Of Material Inquiry'), '/BOMInquiry.php', 2);
NewMenuItem('manuf', 'Reports', __('Where Used Inquiry'), '/WhereUsedInquiry.php', 3);
NewMenuItem('manuf', 'Reports', __('Bill Of Material Listing'), '/BOMListing.php', 4);
NewMenuItem('manuf', 'Reports', __('Indented Bill Of Material Listing'), '/BOMIndented.php', 5);
NewMenuItem('manuf', 'Reports', __('List Components Required'), '/BOMExtendedQty.php', 6);
NewMenuItem('manuf', 'Reports', __('List Materials Not Used anywhere'), '/MaterialsNotUsed.php', 7);
NewMenuItem('manuf', 'Reports', __('Indented Where Used Listing'), '/BOMIndentedReverse.php', 8);
NewMenuItem('manuf', 'Reports', __('MRP'), '/MRPReport.php', 9);
NewMenuItem('manuf', 'Reports', __('MRP Shortages'), '/MRPShortages.php', 10);
NewMenuItem('manuf', 'Reports', __('MRP Suggested Purchase Orders'), '/MRPPlannedPurchaseOrders.php', 11);
NewMenuItem('manuf', 'Reports', __('MRP Suggested Work Orders'), '/MRPPlannedWorkOrders.php', 12);
NewMenuItem('manuf', 'Reports', __('MRP Reschedules Required'), '/MRPReschedules.php', 13);
NewMenuItem('manuf', 'Reports', __('Print Product Specification'), '/PDFProdSpec.php', 14);
NewMenuItem('manuf', 'Reports', __('Print Certificate of Analysis'), '/PDFCOA.php', 15);
NewMenuItem('manuf', 'Reports', __('Historical QA Test Results'), '/HistoricalTestResults.php', 16);
NewMenuItem('manuf', 'Reports', __('Multiple Work Orders Total Cost Inquiry'), '/CollectiveWorkOrderCost.php', 17);

NewMenuItem('manuf', 'Maintenance', __('Work Centre'), '/WorkCentres.php', 1);
NewMenuItem('manuf', 'Maintenance', __('Bills Of Material'), '/BOMs.php', 2);
NewMenuItem('manuf', 'Maintenance', __('Copy a Bill Of Materials Between Items'), '/CopyBOM.php', 3);
NewMenuItem('manuf', 'Maintenance', __('Master Schedule'), '/MRPDemands.php', 4);
NewMenuItem('manuf', 'Maintenance', __('Auto Create Master Schedule'), '/MRPCreateDemands.php', 5);
NewMenuItem('manuf', 'Maintenance', __('MRP Calculation'), '/MRP.php', 6);
NewMenuItem('manuf', 'Maintenance', __('Quality Tests Maintenance'), '/QATests.php', 7);
NewMenuItem('manuf', 'Maintenance', __('Product Specifications'), '/ProductSpecs.php', 8);
NewMenuItem('manuf', 'Maintenance', __('Employees'), '/Employees.php', 9);

NewMenuItem('GL', 'Transactions', __('Bank Account Payments Entry'), '/Payments.php?NewPayment=Yes', 1);
NewMenuItem('GL', 'Transactions', __('Bank Account Receipts Entry'), '/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL', 2);
NewMenuItem('GL', 'Transactions', __('Import Bank Transactions'), '/ImportBankTrans.php', 3);
NewMenuItem('GL', 'Transactions', __('Bank Account Payments Matching'), '/BankMatching.php?Type=Payments', 4);
NewMenuItem('GL', 'Transactions', __('Bank Account Receipts Matching'), '/BankMatching.php?Type=Receipts', 5);
NewMenuItem('GL', 'Transactions', __('Journal Entry'), '/GLJournal.php?NewJournal=Yes', 6);
NewMenuItem('GL', 'Transactions', __('Process Regular Payments'), '/RegularPaymentsProcess.php', 7);

NewMenuItem('GL', 'Reports', __('Bank Account Balances'), '/BankAccountBalances.php', 1);
NewMenuItem('GL', 'Reports', __('Bank Account Reconciliation Statement'), '/BankReconciliation.php', 2);
NewMenuItem('GL', 'Reports', __('Cheque Payments Listing'), '/PDFChequeListing.php', 3);
NewMenuItem('GL', 'Reports', __('Daily Bank Transactions'), '/DailyBankTransactions.php', 4);
NewMenuItem('GL', 'Reports', __('Account Inquiry'), '/SelectGLAccount.php', 5);
NewMenuItem('GL', 'Reports', __('Graph of Account Transactions'), '/GLAccountGraph.php', 6);
NewMenuItem('GL', 'Reports', __('Account Listing'), '/GLAccountReport.php', 7);
NewMenuItem('GL', 'Reports', __('Account Listing to CSV File'), '/GLAccountCSV.php', 8);
NewMenuItem('GL', 'Reports', __('General Ledger Journal Inquiry'), '/GLJournalInquiry.php', 9);
NewMenuItem('GL', 'Reports', __('Trial Balance'), '/GLTrialBalance.php', 10);
NewMenuItem('GL', 'Reports', __('Balance Sheet'), '/GLBalanceSheet.php', 11);
NewMenuItem('GL', 'Reports', __('Profit and Loss Statement'), '/GLProfit_Loss.php', 12);
NewMenuItem('GL', 'Reports', __('Statement of Cash Flows'), '/GLCashFlowsIndirect.php', 13);
NewMenuItem('GL', 'Reports', __('Financial Statements'), '/GLStatements.php', 14);
NewMenuItem('GL', 'Reports', __('Horizontal Analysis of Statement of Financial Position'), '/AnalysisHorizontalPosition.php', 15);
NewMenuItem('GL', 'Reports', __('Horizontal Analysis of Statement of Comprehensive Income'), '/AnalysisHorizontalIncome.php', 16);
NewMenuItem('GL', 'Reports', __('Tag Reports'), '/GLTagProfit_Loss.php', 17);
NewMenuItem('GL', 'Reports', __('Tax Reports'), '/Tax.php', 18);

NewMenuItem('GL', 'Maintenance', __('Account Sections'), '/AccountSections.php', 1);
NewMenuItem('GL', 'Maintenance', __('Account Groups'), '/AccountGroups.php', 2);
NewMenuItem('GL', 'Maintenance', __('GL Account'), '/GLAccounts.php', 3);
NewMenuItem('GL', 'Maintenance', __('GL Account Authorised Users'), '/GLAccountUsers.php', 4);
NewMenuItem('GL', 'Maintenance', __('User Authorised GL Accounts'), '/UserGLAccounts.php', 5);
NewMenuItem('GL', 'Maintenance', __('GL Budgets'), '/GLBudgets.php', 6);
NewMenuItem('GL', 'Maintenance', __('GL Tags'), '/GLTags.php', 7);
NewMenuItem('GL', 'Maintenance', __('Bank Accounts'), '/BankAccounts.php', 8);
NewMenuItem('GL', 'Maintenance', __('Bank Account Authorised Users'), '/BankAccountUsers.php', 9);
NewMenuItem('GL', 'Maintenance', __('User Authorised Bank Accounts'), '/UserBankAccounts.php', 10);
NewMenuItem('GL', 'Maintenance', __('Maintain Journal Templates'), '/GLJournalTemplates.php', 11);
NewMenuItem('GL', 'Maintenance', __('Setup Regular Payments'), '/RegularPaymentsSetup.php', 12);

NewMenuItem('FA', 'Transactions', __('Add a new Asset'), '/FixedAssetItems.php', 1);
NewMenuItem('FA', 'Transactions', __('Select an Asset'), '/SelectAsset.php', 2);
NewMenuItem('FA', 'Transactions', __('Change Asset Location'), '/FixedAssetTransfer.php', 3);
NewMenuItem('FA', 'Transactions', __('Depreciation Journal'), '/FixedAssetDepreciation.php', 4);

NewMenuItem('FA', 'Reports', __('Asset Register'), '/FixedAssetRegister.php', 1);
NewMenuItem('FA', 'Reports', __('My Maintenance Schedule'), '/MaintenanceUserSchedule.php', 2);
NewMenuItem('FA', 'Reports', __('Maintenance Reminder Emails'), '/MaintenanceReminders.php', 3);

NewMenuItem('FA', 'Maintenance', __('Fixed Asset Category Maintenance'), '/FixedAssetCategories.php', 1);
NewMenuItem('FA', 'Maintenance', __('Add or Maintain Asset Locations'), '/FixedAssetLocations.php', 2);
NewMenuItem('FA', 'Maintenance', __('Fixed Asset Maintenance Tasks'), '/MaintenanceTasks.php', 3);

NewMenuItem('PC', 'Transactions', __('Assign Cash to PC Tab'), '/PcAssignCashToTab.php', 1);
NewMenuItem('PC', 'Transactions', __('Transfer Assigned Cash Between PC Tabs'), '/PcAssignCashTabToTab.php', 2);
NewMenuItem('PC', 'Transactions', __('Claim Expenses From PC Tab'), '/PcClaimExpensesFromTab.php', 3);
NewMenuItem('PC', 'Transactions', __('Authorise Expenses'), '/PcAuthorizeExpenses.php', 4);
NewMenuItem('PC', 'Transactions', __('Authorise Assigned Cash'), '/PcAuthorizeCash.php', 5);

NewMenuItem('PC', 'Reports', __('PC Tab General Report'), '/PcReportTab.php', 1);
NewMenuItem('PC', 'Reports', __('PC Expense General Report'), '/PcReportExpense.php', 2);
NewMenuItem('PC', 'Reports', __('PC Tab Expenses List'), '/PcTabExpensesList.php', 3);
NewMenuItem('PC', 'Reports', __('PC Expenses Analysis'), '/PcAnalysis.php', 4);

NewMenuItem('PC', 'Maintenance', __('Types of PC Tabs'), '/PcTypeTabs.php', 1);
NewMenuItem('PC', 'Maintenance', __('PC Tabs'), '/PcTabs.php', 2);
NewMenuItem('PC', 'Maintenance', __('PC Expenses'), '/PcExpenses.php', 3);
NewMenuItem('PC', 'Maintenance', __('Expenses for Type of PC Tab'), '/PcExpensesTypeTab.php', 4);

NewMenuItem('system', 'Transactions', __('Company Preferences'), '/CompanyPreferences.php', 1);
NewMenuItem('system', 'Transactions', __('System Parameters'), '/SystemParameters.php', 2);
NewMenuItem('system', 'Transactions', __('Users Maintenance'), '/WWW_Users.php', 3);
NewMenuItem('system', 'Transactions', __('Maintain Security Tokens'), '/SecurityTokens.php', 4);
NewMenuItem('system', 'Transactions', __('Access Permissions Maintenance'), '/WWW_Access.php', 5);
NewMenuItem('system', 'Transactions', __('Page Security Settings'), '/PageSecurity.php', 6);
NewMenuItem('system', 'Transactions', __('Currency Maintenance'), '/Currencies.php', 7);
NewMenuItem('system', 'Transactions', __('Tax Authorities and Rates Maintenance'), '/TaxAuthorities.php', 8);
NewMenuItem('system', 'Transactions', __('Tax Group Maintenance'), '/TaxGroups.php', 9);
NewMenuItem('system', 'Transactions', __('Dispatch Tax Province Maintenance'), '/TaxProvinces.php', 10);
NewMenuItem('system', 'Transactions', __('Tax Category Maintenance'), '/TaxCategories.php', 11);
NewMenuItem('system', 'Transactions', __('List Periods Defined'), '/PeriodsInquiry.php', 12);
NewMenuItem('system', 'Transactions', __('Report Builder Tool'), '/reportwriter/admin/ReportCreator.php', 13);
NewMenuItem('system', 'Transactions', __('View Audit Trail'), '/AuditTrail.php', 14);
NewMenuItem('system', 'Transactions', __('Geocode Maintenance'), '/GeocodeSetup.php', 15);
NewMenuItem('system', 'Transactions', __('Form Designer'), '/FormDesigner.php', 16);
NewMenuItem('system', 'Transactions', __('Web-Store Configuration'), '/ShopParameters.php', 17);
NewMenuItem('system', 'Transactions', __('SMTP Server Details'), '/SMTPServer.php', 18);
NewMenuItem('system', 'Transactions', __('Mailing Group Maintenance'), '/MailingGroupMaintenance.php', 19);

NewMenuItem('system', 'Reports', __('Sales Types'), '/SalesTypes.php', 1);
NewMenuItem('system', 'Reports', __('Customer Types'), '/CustomerTypes.php', 2);
NewMenuItem('system', 'Reports', __('Supplier Types'), '/SupplierTypes.php', 3);
NewMenuItem('system', 'Reports', __('Credit Status'), '/CreditStatus.php', 4);
NewMenuItem('system', 'Reports', __('Payment Terms'), '/PaymentTerms.php', 5);
NewMenuItem('system', 'Reports', __('Set Purchase Order Authorisation levels'), '/PO_AuthorisationLevels.php', 6);
NewMenuItem('system', 'Reports', __('Payment Methods'), '/PaymentMethods.php', 7);
NewMenuItem('system', 'Reports', __('Sales People'), '/SalesPeople.php', 8);
NewMenuItem('system', 'Reports', __('Sales Areas'), '/Areas.php', 9);
NewMenuItem('system', 'Reports', __('Shippers'), '/Shippers.php', 10);
NewMenuItem('system', 'Reports', __('Sales GL Interface Postings'), '/SalesGLPostings.php', 11);
NewMenuItem('system', 'Reports', __('COGS GL Interface Postings'), '/COGSGLPostings.php', 12);
NewMenuItem('system', 'Reports', __('Freight Costs Maintenance'), '/FreightCosts.php', 13);
NewMenuItem('system', 'Reports', __('Discount Matrix'), '/DiscountMatrix.php', 14);
NewMenuItem('system', 'Reports', __('Sales Commission Types'), '/SalesCommissionTypes.php', 15);

NewMenuItem('system', 'Maintenance', __('Inventory Categories Maintenance'), '/StockCategories.php', 1);
NewMenuItem('system', 'Maintenance', __('Inventory Locations Maintenance'), '/Locations.php', 2);
NewMenuItem('system', 'Maintenance', __('Inventory Location Authorised Users Maintenance'), '/LocationUsers.php', 3);
NewMenuItem('system', 'Maintenance', __('User Authorised Inventory Locations Maintenance'), '/UserLocations.php', 4);
NewMenuItem('system', 'Maintenance', __('Discount Category Maintenance'), '/DiscountCategories.php', 5);
NewMenuItem('system', 'Maintenance', __('Units of Measure'), '/UnitsOfMeasure.php', 6);
NewMenuItem('system', 'Maintenance', __('MRP Available Production Days'), '/MRPCalendar.php', 7);
NewMenuItem('system', 'Maintenance', __('MRP Demand Types'), '/MRPDemandTypes.php', 8);
NewMenuItem('system', 'Maintenance', __('Maintain Internal Departments'), '/Departments.php', 9);
NewMenuItem('system', 'Maintenance', __('Maintain Internal Stock Categories to User Roles'),'/InternalStockCategoriesByRole.php', 10);
NewMenuItem('system', 'Maintenance', __('Label Templates Maintenance'), '/Labels.php', 11);
NewMenuItem('system', 'Maintenance', __('Dashboard Configuration'), '/DashboardConfig.php', 12);

NewMenuItem('Utilities', 'Transactions', __('Change A Customer Code'), '/Z_ChangeCustomerCode.php', 1);
NewMenuItem('Utilities', 'Transactions', __('Change A Customer Branch Code'), '/Z_ChangeBranchCode.php', 2);
NewMenuItem('Utilities', 'Transactions', __('Change A GL Account Code'), '/Z_ChangeGLAccountCode.php', 3);
NewMenuItem('Utilities', 'Transactions', __('Change An Inventory Item Code'), '/Z_ChangeStockCode.php', 4);
NewMenuItem('Utilities', 'Transactions', __('Change A Location Code'), '/Z_ChangeLocationCode.php', 5);
NewMenuItem('Utilities', 'Transactions', __('Change A Salesman Code'), '/Z_ChangeSalesmanCode.php', 6);
NewMenuItem('Utilities', 'Transactions', __('Change A Stock Category Code'), '/Z_ChangeStockCategory.php', 7);
NewMenuItem('Utilities', 'Transactions', __('Change A Supplier Code'), '/Z_ChangeSupplierCode.php', 8);
NewMenuItem('Utilities', 'Transactions', __('Translate Item Descriptions'), '/AutomaticTranslationDescriptions.php', 9);
NewMenuItem('Utilities', 'Transactions', __('Update costs for all BOM items, from the bottom up'), '/Z_BottomUpCosts.php', 10);
NewMenuItem('Utilities', 'Transactions', __('Re-apply costs to Sales Analysis'), '/Z_ReApplyCostToSA.php', 11);
NewMenuItem('Utilities', 'Transactions', __('Delete sales transactions'), '/Z_DeleteSalesTransActions.php', 12);
NewMenuItem('Utilities', 'Transactions', __('Reverse all supplier payments on a specified date'), '/Z_ReverseSuppPaymentRun.php', 13);
NewMenuItem('Utilities', 'Transactions', __('Update sales analysis with latest customer data'), '/Z_UpdateSalesAnalysisWithLatestCustomerData.php', 14);
NewMenuItem('Utilities', 'Transactions', __('Copy Authority of GL Accounts from one user to another'), '/Z_GLAccountUsersCopyAuthority.php', 15);

NewMenuItem('Utilities', 'Reports', __('Debtors Balances By Currency Totals'), '/Z_CurrencyDebtorsBalances.php', 1);
NewMenuItem('Utilities', 'Reports', __('Suppliers Balances By Currency Totals'), '/Z_CurrencySuppliersBalances.php', 2);
NewMenuItem('Utilities', 'Reports', __('Show General Transactions That Do Not Balance'), '/Z_CheckGLTransBalance.php', 3);
NewMenuItem('Utilities', 'Reports', __('List of items without picture'), '/Z_ItemsWithoutPicture.php', 4);

NewMenuItem('Utilities', 'Maintenance', __('Maintain Language Files'), '/Z_poAdmin.php', 1);
NewMenuItem('Utilities', 'Maintenance', __('Make New Company'), '/Z_MakeNewCompany.php', 2);
NewMenuItem('Utilities', 'Maintenance', __('Data Export Options'), '/Z_DataExport.php', 3);
NewMenuItem('Utilities', 'Maintenance', __('Import Customers from .csv file'), '/Z_ImportDebtors.php', 4);
NewMenuItem('Utilities', 'Maintenance', __('Import Stock Items from .csv'), '/Z_ImportStocks.php', 5);
NewMenuItem('Utilities', 'Maintenance', __('Import Price List from .csv file'), '/Z_ImportPriceList.php', 6);
NewMenuItem('Utilities', 'Maintenance', __('Import Fixed Assets from .csv file'), '/Z_ImportFixedAssets.php', 7);
NewMenuItem('Utilities', 'Maintenance', __('Import GL Payments Receipts Or Journals From .csv file'), '/Z_ImportGLTransactions.php', 8);
NewMenuItem('Utilities', 'Maintenance', __('Create new company template SQL file and submit to webERP'), '/Z_CreateCompanyTemplateFile.php', 9);
NewMenuItem('Utilities', 'Maintenance', __('Re-calculate brought forward amounts in GL'), '/Z_UpdateChartDetailsBFwd.php', 10);
NewMenuItem('Utilities', 'Maintenance', __('Re-Post all GL transactions from a specified period'), '/Z_RePostGLFromPeriod.php', 11);
NewMenuItem('Utilities', 'Maintenance', __('Purge all old prices'), '/Z_DeleteOldPrices.php', 12);
NewMenuItem('Utilities', 'Maintenance', __('Remove all purchase back orders'), '/Z_RemovePurchaseBackOrders.php', 13);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Move the menu and module strings to the database'));
}
