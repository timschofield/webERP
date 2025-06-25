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


NewModule('Sales', 'ord', _('Sales'), 1);
NewModule('AR', 'ar', _('Receivables'), 2);
NewModule('PO', 'prch', _('Purchases'), 3);
NewModule('AP', 'ap', _('Payables'), 4);
NewModule('stock', 'inv', _('Inventory'), 5);
NewModule('manuf', 'man', _('Manufacturing'), 6);
NewModule('GL', 'gl', _('General Ledger'), 7);
NewModule('FA', 'fa', _('Asset Manager'), 8);
NewModule('PC', 'pc', _('Petty Cash'), 9);
NewModule('system', 'sys', _('Setup'), 10);
NewModule('Utilities', 'util', _('Utilities'), 11);

NewMenuItem('Sales', 'Transactions', _('New Sales Order or Quotation'), '/SelectOrderItems.php?NewOrder=Yes', 1);
NewMenuItem('Sales', 'Transactions', _('Enter Counter Sales'), '/CounterSales.php', 2);
NewMenuItem('Sales', 'Transactions', _('Enter Counter Returns'), '/CounterReturns.php', 3);
NewMenuItem('Sales', 'Transactions', _('Generate/Print Picking Lists'), '/PDFPickingList.php', 4);
NewMenuItem('Sales', 'Transactions', _('Outstanding Sales Orders/Quotations'), '/SelectSalesOrder.php', 5);
NewMenuItem('Sales', 'Transactions', _('Special Order'), '/SpecialOrder.php', 6);
NewMenuItem('Sales', 'Transactions', _('Recurring Order Template'), '/SelectRecurringSalesOrder.php', 7);
NewMenuItem('Sales', 'Transactions', _('Process Recurring Orders'), '/RecurringSalesOrdersProcess.php', 8);
NewMenuItem('Sales', 'Transactions', _('Maintain Picking Lists'), '/SelectPickingLists.php', 9);

NewMenuItem('Sales', 'Reports', _('Sales Order Inquiry'), '/SelectCompletedOrder.php', 1);
NewMenuItem('Sales', 'Reports', _('Print Price Lists'), '/PDFPriceList.php', 2);
NewMenuItem('Sales', 'Reports', _('Order Status Report'), '/PDFOrderStatus.php', 3);
NewMenuItem('Sales', 'Reports', _('Orders Invoiced Reports'), '/PDFOrdersInvoiced.php', 4);
NewMenuItem('Sales', 'Reports', _('Daily Sales Inquiry'), '/DailySalesInquiry.php', 5);
NewMenuItem('Sales', 'Reports', _('Sales By Sales Type Inquiry'), '/SalesByTypePeriodInquiry.php', 6);
NewMenuItem('Sales', 'Reports', _('Sales By Category Inquiry'), '/SalesCategoryPeriodInquiry.php', 7);
NewMenuItem('Sales', 'Reports', _('Sales By Category By Item Inquiry'), '/StockCategorySalesInquiry.php', 8);
NewMenuItem('Sales', 'Reports', _('Sales Analysis Reports'), '/SalesAnalRepts.php', 9);
NewMenuItem('Sales', 'Reports', _('Sales Graphs'), '/SalesGraph.php', 10);
NewMenuItem('Sales', 'Reports', _('Top Sellers Inquiry'), '/SalesTopItemsInquiry.php', 11);
NewMenuItem('Sales', 'Reports', _('Order Delivery Differences Report'), '/PDFDeliveryDifferences.php', 12);
NewMenuItem('Sales', 'Reports', _('Delivery In Full On Time (DIFOT) Report'), '/PDFDIFOT.php', 13);
NewMenuItem('Sales', 'Reports', _('Sales Order Detail Or Summary Inquiries'), '/SalesInquiry.php', 14);
NewMenuItem('Sales', 'Reports', _('Top Sales Items Report'), '/TopItems.php', 15);
NewMenuItem('Sales', 'Reports', _('Top Customers Inquiry'), '/SalesTopCustomersInquiry.php', 16);
NewMenuItem('Sales', 'Reports', _('Worst Sales Items Report'), '/NoSalesItems.php', 17);
NewMenuItem('Sales', 'Reports', _('Sales With Low Gross Profit Report'), '/PDFLowGP.php', 18);
NewMenuItem('Sales', 'Reports', _('Sell Through Support Claims Report'), '/PDFSellThroughSupportClaim.php', 19);
NewMenuItem('Sales', 'Reports', _('Sales to Customers'), '/SalesReport.php', 20);
NewMenuItem('Sales', 'Reports', _('Sales Commission Reports'), '/SalesCommissionReports.php', 21);

NewMenuItem('Sales', 'Maintenance', _('Create Contract'), '/Contracts.php', 1);
NewMenuItem('Sales', 'Maintenance', _('Select Contract'), '/SelectContract.php', 2);
NewMenuItem('Sales', 'Maintenance', _('Sell Through Support Deals'), '/SellThroughSupport.php', 3);

NewMenuItem('AR', 'Transactions', _('Select Order to Invoice'), '/SelectSalesOrder.php', 1);
NewMenuItem('AR', 'Transactions', _('Create A Credit Note'), '/SelectCreditItems.php?NewCredit=Yes', 2);
NewMenuItem('AR', 'Transactions', _('Enter Receipts'), '/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer', 3);
NewMenuItem('AR', 'Transactions', _('Allocate Receipts or Credit Notes'), '/CustomerAllocations.php', 4);

NewMenuItem('AR', 'Reports', _('Where Allocated Inquiry'), '/CustWhereAlloc.php', 1);
NewMenuItem('AR', 'Reports', _('Print Invoices or Credit Notes'), '/PrintCustTrans.php', 2);
NewMenuItem('AR', 'Reports', _('Print Statements'), '/PrintCustStatements.php', 3);
NewMenuItem('AR', 'Reports', _('Aged Customer Balances/Overdues Report'), '/AgedDebtors.php', 4);
NewMenuItem('AR', 'Reports', _('Re-Print A Deposit Listing'), '/PDFBankingSummary.php', 5);
NewMenuItem('AR', 'Reports', _('Debtor Balances At A Prior Month End'), '/DebtorsAtPeriodEnd.php', 6);
NewMenuItem('AR', 'Reports', _('Customer Listing By Area/Salesperson'), '/PDFCustomerList.php', 7);
NewMenuItem('AR', 'Reports', _('List Daily Transactions'), '/PDFCustTransListing.php', 8);
NewMenuItem('AR', 'Reports', _('Customer Transaction Inquiries'), '/CustomerTransInquiry.php', 9);
NewMenuItem('AR', 'Reports', _('Customer Activity and Balances'), '/CustomerBalancesMovement.php', 10);

NewMenuItem('AR', 'Maintenance', _('Add Customer'), '/Customers.php', 1);
NewMenuItem('AR', 'Maintenance', _('Select Customer'), '/SelectCustomer.php', 2);

NewMenuItem('AP', 'Transactions', _('Select Supplier'), '/SelectSupplier.php', 1);
NewMenuItem('AP', 'Transactions', _('Supplier Allocations'), '/SupplierAllocations.php', 2);

NewMenuItem('AP', 'Reports', _('Where Allocated Inquiry'), '/SuppWhereAlloc.php', 1);
NewMenuItem('AP', 'Reports', _('Aged Supplier Report'), '/AgedSuppliers.php', 2);
NewMenuItem('AP', 'Reports', _('Payment Run Report'), '/SuppPaymentRun.php', 3);
NewMenuItem('AP', 'Reports', _('Remittance Advices'), '/PDFRemittanceAdvice.php', 4);
NewMenuItem('AP', 'Reports', _('Outstanding GRNs Report'), '/OutstandingGRNs.php', 5);
NewMenuItem('AP', 'Reports', _('Supplier Balances At A Prior Month End'), '/SupplierBalsAtPeriodEnd.php', 6);
NewMenuItem('AP', 'Reports', _('List Daily Transactions'), '/PDFSuppTransListing.php', 7);
NewMenuItem('AP', 'Reports', _('Supplier Transaction Inquiries'), '/SupplierTransInquiry.php', 8);

NewMenuItem('AP', 'Maintenance', _('Add Supplier'), '/Suppliers.php', 1);
NewMenuItem('AP', 'Maintenance', _('Select Supplier'), '/SelectSupplier.php', 2);
NewMenuItem('AP', 'Maintenance', _('Maintain Factor Companies'), '/Factors.php', 3);

NewMenuItem('PO', 'Transactions', _('New Purchase Order'), '/PO_Header.php?NewOrder=Yes', 1);
NewMenuItem('PO', 'Transactions', _('Purchase Orders'), '/PO_SelectOSPurchOrder.php', 2);
NewMenuItem('PO', 'Transactions', _('Purchase Order Grid Entry'), '/PurchaseByPrefSupplier.php', 3);
NewMenuItem('PO', 'Transactions', _('Create a New Tender'), '/SupplierTenderCreate.php?New=Yes', 4);
NewMenuItem('PO', 'Transactions', _('Edit Existing Tenders'), '/SupplierTenderCreate.php?Edit=Yes', 5);
NewMenuItem('PO', 'Transactions', _('Process Tenders and Offers'), '/OffersReceived.php', 6);
NewMenuItem('PO', 'Transactions', _('Orders to Authorise'), '/PO_AuthoriseMyOrders.php', 7);
NewMenuItem('PO', 'Transactions', _('Shipment Entry'), '/SelectSupplier.php', 8);
NewMenuItem('PO', 'Transactions', _('Select A Shipment'), '/Shipt_Select.php', 9);

NewMenuItem('PO', 'Reports', _('Purchase Order Inquiry'), '/PO_SelectPurchOrder.php', 1);
NewMenuItem('PO', 'Reports', _('Purchase Order Detail Or Summary Inquiries'), '/POReport.php', 2);
NewMenuItem('PO', 'Reports', _('Supplier Price List'), '/SuppPriceList.php', 3);
NewMenuItem('PO', 'Reports', _('Purchases from Suppliers'), '/PurchasesReport.php', 4);

NewMenuItem('PO', 'Maintenance', _('Maintain Supplier Price Lists'), '/SupplierPriceList.php', 1);

NewMenuItem('stock', 'Transactions', _('Receive Purchase Orders'), '/PO_SelectOSPurchOrder.php', 1);
NewMenuItem('stock', 'Transactions', _('Inventory Location Transfers'), '/StockTransfers.php?New=Yes', 2);
NewMenuItem('stock', 'Transactions', _('Bulk Inventory Transfer') . ' - ' . _('Dispatch'), '/StockLocTransfer.php', 3);
NewMenuItem('stock', 'Transactions', _('Bulk Inventory Transfer') . ' - ' . _('Receive'), '/StockLocTransferReceive.php', 4);
NewMenuItem('stock', 'Transactions', _('Inventory Adjustments'), '/StockAdjustments.php?NewAdjustment=Yes', 5);
NewMenuItem('stock', 'Transactions', _('Reverse Goods Received'), '/ReverseGRN.php', 6);
NewMenuItem('stock', 'Transactions', _('Enter Stock Counts'), '/StockCounts.php', 7);
NewMenuItem('stock', 'Transactions', _('Create a New Internal Stock Request'), '/InternalStockRequest.php?New=Yes', 8);
NewMenuItem('stock', 'Transactions', _('Authorise Internal Stock Requests'), '/InternalStockRequestAuthorisation.php', 9);
NewMenuItem('stock', 'Transactions', _('Fulfil Internal Stock Requests'), '/InternalStockRequestFulfill.php', 10);

NewMenuItem('stock', 'Reports', _('Serial Item Research Tool'), '/StockSerialItemResearch.php', 1);
NewMenuItem('stock', 'Reports', _('Print Price Labels'), '/PDFPrintLabel.php', 2);
NewMenuItem('stock', 'Reports', _('Reprint GRN'), '/ReprintGRN.php', 3);
NewMenuItem('stock', 'Reports', _('Inventory Item Movements'), '/StockMovements.php', 4);
NewMenuItem('stock', 'Reports', _('Inventory Item Status'), '/StockStatus.php', 5);
NewMenuItem('stock', 'Reports', _('Inventory Item Usage'), '/StockUsage.php', 6);
NewMenuItem('stock', 'Reports', _('Inventory Quantities'), '/InventoryQuantities.php', 7);
NewMenuItem('stock', 'Reports', _('Reorder Level'), '/ReorderLevel.php', 8);
NewMenuItem('stock', 'Reports', _('Stock Dispatch'), '/StockDispatch.php', 9);
NewMenuItem('stock', 'Reports', _('Inventory Valuation Report'), '/InventoryValuation.php', 10);
NewMenuItem('stock', 'Reports', _('Mail Inventory Valuation Report'), '/MailInventoryValuation.php', 11);
NewMenuItem('stock', 'Reports', _('Inventory Planning Report'), '/InventoryPlanning.php', 12);
NewMenuItem('stock', 'Reports', _('Inventory Planning Based On Preferred Supplier Data'), '/InventoryPlanningPrefSupplier.php', 13);
NewMenuItem('stock', 'Reports', _('Inventory Stock Check Sheets'), '/StockCheck.php', 14);
NewMenuItem('stock', 'Reports', _('Make Inventory Quantities CSV'), '/StockQties_csv.php', 15);
NewMenuItem('stock', 'Reports', _('Compare Counts Vs Stock Check Data'), '/PDFStockCheckComparison.php', 16);
NewMenuItem('stock', 'Reports', _('All Inventory Movements By Location/Date'), '/StockLocMovements.php', 17);
NewMenuItem('stock', 'Reports', _('List Inventory Status By Location/Category'), '/StockLocStatus.php', 18);
NewMenuItem('stock', 'Reports', _('Historical Stock Quantity By Location/Category'), '/StockQuantityByDate.php', 19);
NewMenuItem('stock', 'Reports', _('List Negative Stocks'), '/PDFStockNegatives.php', 20);
NewMenuItem('stock', 'Reports', _('Period Stock Transaction Listing'), '/PDFPeriodStockTransListing.php', 21);
NewMenuItem('stock', 'Reports', _('Stock Transfer Note'), '/PDFStockTransfer.php', 22);
NewMenuItem('stock', 'Reports', _('Aged Controlled Stock Report'), '/AgedControlledInventory.php', 23);
NewMenuItem('stock', 'Reports', _('Internal stock request inquiry'), '/InternalStockRequestInquiry.php', 24);

NewMenuItem('stock', 'Maintenance', _('Add A New Item'), '/Stocks.php', 1);
NewMenuItem('stock', 'Maintenance', _('Select An Item'), '/SelectProduct.php', 2);
NewMenuItem('stock', 'Maintenance', _('Review Translated Descriptions'), '/RevisionTranslations.php', 3);
NewMenuItem('stock', 'Maintenance', _('Sales Category Maintenance'), '/SalesCategories.php', 4);
NewMenuItem('stock', 'Maintenance', _('Brands Maintenance'), '/Manufacturers.php', 5);
NewMenuItem('stock', 'Maintenance', _('Add or Update Prices Based On Costs'), '/PricesBasedOnMarkUp.php', 6);
NewMenuItem('stock', 'Maintenance', _('View or Update Prices Based On Costs'), '/PricesByCost.php', 7);
NewMenuItem('stock', 'Maintenance', _('Upload new prices from csv file'), '/UploadPriceList.php', 8);
NewMenuItem('stock', 'Maintenance', _('Reorder Level By Category/Location'), '/ReorderLevelLocation.php', 9);

NewMenuItem('manuf', 'Transactions', _('Work Order Entry'), '/WorkOrderEntry.php?New=True', 1);
NewMenuItem('manuf', 'Transactions', _('Select A Work Order'), '/SelectWorkOrder.php', 2);
NewMenuItem('manuf', 'Transactions', _('QA Samples and Test Results'), '/SelectQASamples.php', 3);
NewMenuItem('manuf', 'Transactions', _('Timesheet Entry'), '/Timesheets.php', 4);

NewMenuItem('manuf', 'Reports', _('Select A Work Order'), '/SelectWorkOrder.php', 1);
NewMenuItem('manuf', 'Reports', _('Costed Bill Of Material Inquiry'), '/BOMInquiry.php', 2);
NewMenuItem('manuf', 'Reports', _('Where Used Inquiry'), '/WhereUsedInquiry.php', 3);
NewMenuItem('manuf', 'Reports', _('Bill Of Material Listing'), '/BOMListing.php', 4);
NewMenuItem('manuf', 'Reports', _('Indented Bill Of Material Listing'), '/BOMIndented.php', 5);
NewMenuItem('manuf', 'Reports', _('List Components Required'), '/BOMExtendedQty.php', 6);
NewMenuItem('manuf', 'Reports', _('List Materials Not Used anywhere'), '/MaterialsNotUsed.php', 7);
NewMenuItem('manuf', 'Reports', _('Indented Where Used Listing'), '/BOMIndentedReverse.php', 8);
NewMenuItem('manuf', 'Reports', _('MRP'), '/MRPReport.php', 9);
NewMenuItem('manuf', 'Reports', _('MRP Shortages'), '/MRPShortages.php', 10);
NewMenuItem('manuf', 'Reports', _('MRP Suggested Purchase Orders'), '/MRPPlannedPurchaseOrders.php', 11);
NewMenuItem('manuf', 'Reports', _('MRP Suggested Work Orders'), '/MRPPlannedWorkOrders.php', 12);
NewMenuItem('manuf', 'Reports', _('MRP Reschedules Required'), '/MRPReschedules.php', 13);
NewMenuItem('manuf', 'Reports', _('Print Product Specification'), '/PDFProdSpec.php', 14);
NewMenuItem('manuf', 'Reports', _('Print Certificate of Analysis'), '/PDFCOA.php', 15);
NewMenuItem('manuf', 'Reports', _('Historical QA Test Results'), '/HistoricalTestResults.php', 16);
NewMenuItem('manuf', 'Reports', _('Multiple Work Orders Total Cost Inquiry'), '/CollectiveWorkOrderCost.php', 17);

NewMenuItem('manuf', 'Maintenance', _('Work Centre'), '/WorkCentres.php', 1);
NewMenuItem('manuf', 'Maintenance', _('Bills Of Material'), '/BOMs.php', 2);
NewMenuItem('manuf', 'Maintenance', _('Copy a Bill Of Materials Between Items'), '/CopyBOM.php', 3);
NewMenuItem('manuf', 'Maintenance', _('Master Schedule'), '/MRPDemands.php', 4);
NewMenuItem('manuf', 'Maintenance', _('Auto Create Master Schedule'), '/MRPCreateDemands.php', 5);
NewMenuItem('manuf', 'Maintenance', _('MRP Calculation'), '/MRP.php', 6);
NewMenuItem('manuf', 'Maintenance', _('Quality Tests Maintenance'), '/QATests.php', 7);
NewMenuItem('manuf', 'Maintenance', _('Product Specifications'), '/ProductSpecs.php', 8);
NewMenuItem('manuf', 'Maintenance', _('Employees'), '/Employees.php', 9);

NewMenuItem('GL', 'Transactions', _('Bank Account Payments Entry'), '/Payments.php?NewPayment=Yes', 1);
NewMenuItem('GL', 'Transactions', _('Bank Account Receipts Entry'), '/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL', 2);
NewMenuItem('GL', 'Transactions', _('Import Bank Transactions'), '/ImportBankTrans.php', 3);
NewMenuItem('GL', 'Transactions', _('Bank Account Payments Matching'), '/BankMatching.php?Type=Payments', 4);
NewMenuItem('GL', 'Transactions', _('Bank Account Receipts Matching'), '/BankMatching.php?Type=Receipts', 5);
NewMenuItem('GL', 'Transactions', _('Journal Entry'), '/GLJournal.php?NewJournal=Yes', 6);
NewMenuItem('GL', 'Transactions', _('Process Regular Payments'), '/RegularPaymentsProcess.php', 7);

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
NewMenuItem('GL', 'Reports', _('Statement of Cash Flows'), '/GLCashFlowsIndirect.php', 13);
NewMenuItem('GL', 'Reports', _('Financial Statements'), '/GLStatements.php', 14);
NewMenuItem('GL', 'Reports', _('Horizontal Analysis of Statement of Financial Position'), '/AnalysisHorizontalPosition.php', 15);
NewMenuItem('GL', 'Reports', _('Horizontal Analysis of Statement of Comprehensive Income'), '/AnalysisHorizontalIncome.php', 16);
NewMenuItem('GL', 'Reports', _('Tag Reports'), '/GLTagProfit_Loss.php', 17);
NewMenuItem('GL', 'Reports', _('Tax Reports'), '/Tax.php', 18);

NewMenuItem('GL', 'Maintenance', _('Account Sections'), '/AccountSections.php', 1);
NewMenuItem('GL', 'Maintenance', _('Account Groups'), '/AccountGroups.php', 2);
NewMenuItem('GL', 'Maintenance', _('GL Account'), '/GLAccounts.php', 3);
NewMenuItem('GL', 'Maintenance', _('GL Account Authorised Users'), '/GLAccountUsers.php', 4);
NewMenuItem('GL', 'Maintenance', _('User Authorised GL Accounts'), '/UserGLAccounts.php', 5);
NewMenuItem('GL', 'Maintenance', _('GL Budgets'), '/GLBudgets.php', 6);
NewMenuItem('GL', 'Maintenance', _('GL Tags'), '/GLTags.php', 7);
NewMenuItem('GL', 'Maintenance', _('Bank Accounts'), '/BankAccounts.php', 8);
NewMenuItem('GL', 'Maintenance', _('Bank Account Authorised Users'), '/BankAccountUsers.php', 9);
NewMenuItem('GL', 'Maintenance', _('User Authorised Bank Accounts'), '/UserBankAccounts.php', 10);
NewMenuItem('GL', 'Maintenance', _('Maintain Journal Templates'), '/GLJournalTemplates.php', 11);
NewMenuItem('GL', 'Maintenance', _('Setup Regular Payments'), '/RegularPaymentsSetup.php', 12);

NewMenuItem('FA', 'Transactions', _('Add a new Asset'), '/FixedAssetItems.php', 1);
NewMenuItem('FA', 'Transactions', _('Select an Asset'), '/SelectAsset.php', 2);
NewMenuItem('FA', 'Transactions', _('Change Asset Location'), '/FixedAssetTransfer.php', 3);
NewMenuItem('FA', 'Transactions', _('Depreciation Journal'), '/FixedAssetDepreciation.php', 4);

NewMenuItem('FA', 'Reports', _('Asset Register'), '/FixedAssetRegister.php', 1);
NewMenuItem('FA', 'Reports', _('My Maintenance Schedule'), '/MaintenanceUserSchedule.php', 2);
NewMenuItem('FA', 'Reports', _('Maintenance Reminder Emails'), '/MaintenanceReminders.php', 3);

NewMenuItem('FA', 'Maintenance', _('Fixed Asset Category Maintenance'), '/FixedAssetCategories.php', 1);
NewMenuItem('FA', 'Maintenance', _('Add or Maintain Asset Locations'), '/FixedAssetLocations.php', 2);
NewMenuItem('FA', 'Maintenance', _('Fixed Asset Maintenance Tasks'), '/MaintenanceTasks.php', 3);

NewMenuItem('PC', 'Transactions', _('Assign Cash to PC Tab'), '/PcAssignCashToTab.php', 1);
NewMenuItem('PC', 'Transactions', _('Transfer Assigned Cash Between PC Tabs'), '/PcAssignCashTabToTab.php', 2);
NewMenuItem('PC', 'Transactions', _('Claim Expenses From PC Tab'), '/PcClaimExpensesFromTab.php', 3);
NewMenuItem('PC', 'Transactions', _('Authorise Expenses'), '/PcAuthorizeExpenses.php', 4);
NewMenuItem('PC', 'Transactions', _('Authorise Assigned Cash'), '/PcAuthorizeCash.php', 5);

NewMenuItem('PC', 'Reports', _('PC Tab General Report'), '/PcReportTab.php', 1);
NewMenuItem('PC', 'Reports', _('PC Expense General Report'), '/PcReportExpense.php', 2);
NewMenuItem('PC', 'Reports', _('PC Tab Expenses List'), '/PcTabExpensesList.php', 3);
NewMenuItem('PC', 'Reports', _('PC Expenses Analysis'), '/PcAnalysis.php', 4);

NewMenuItem('PC', 'Maintenance', _('Types of PC Tabs'), '/PcTypeTabs.php', 1);
NewMenuItem('PC', 'Maintenance', _('PC Tabs'), '/PcTabs.php', 2);
NewMenuItem('PC', 'Maintenance', _('PC Expenses'), '/PcExpenses.php', 3);
NewMenuItem('PC', 'Maintenance', _('Expenses for Type of PC Tab'), '/PcExpensesTypeTab.php', 4);

NewMenuItem('system', 'Transactions', _('Company Preferences'), '/CompanyPreferences.php', 1);
NewMenuItem('system', 'Transactions', _('System Parameters'), '/SystemParameters.php', 2);
NewMenuItem('system', 'Transactions', _('Users Maintenance'), '/WWW_Users.php', 3);
NewMenuItem('system', 'Transactions', _('Maintain Security Tokens'), '/SecurityTokens.php', 4);
NewMenuItem('system', 'Transactions', _('Access Permissions Maintenance'), '/WWW_Access.php', 5);
NewMenuItem('system', 'Transactions', _('Page Security Settings'), '/PageSecurity.php', 6);
NewMenuItem('system', 'Transactions', _('Currency Maintenance'), '/Currencies.php', 7);
NewMenuItem('system', 'Transactions', _('Tax Authorities and Rates Maintenance'), '/TaxAuthorities.php', 8);
NewMenuItem('system', 'Transactions', _('Tax Group Maintenance'), '/TaxGroups.php', 9);
NewMenuItem('system', 'Transactions', _('Dispatch Tax Province Maintenance'), '/TaxProvinces.php', 10);
NewMenuItem('system', 'Transactions', _('Tax Category Maintenance'), '/TaxCategories.php', 11);
NewMenuItem('system', 'Transactions', _('List Periods Defined'), '/PeriodsInquiry.php', 12);
NewMenuItem('system', 'Transactions', _('Report Builder Tool'), '/reportwriter/admin/ReportCreator.php', 13);
NewMenuItem('system', 'Transactions', _('View Audit Trail'), '/AuditTrail.php', 14);
NewMenuItem('system', 'Transactions', _('Geocode Maintenance'), '/GeocodeSetup.php', 15);
NewMenuItem('system', 'Transactions', _('Form Designer'), '/FormDesigner.php', 16);
NewMenuItem('system', 'Transactions', _('Web-Store Configuration'), '/ShopParameters.php', 17);
NewMenuItem('system', 'Transactions', _('SMTP Server Details'), '/SMTPServer.php', 18);
NewMenuItem('system', 'Transactions', _('Mailing Group Maintenance'), '/MailingGroupMaintenance.php', 19);

NewMenuItem('system', 'Reports', _('Sales Types'), '/SalesTypes.php', 1);
NewMenuItem('system', 'Reports', _('Customer Types'), '/CustomerTypes.php', 2);
NewMenuItem('system', 'Reports', _('Supplier Types'), '/SupplierTypes.php', 3);
NewMenuItem('system', 'Reports', _('Credit Status'), '/CreditStatus.php', 4);
NewMenuItem('system', 'Reports', _('Payment Terms'), '/PaymentTerms.php', 5);
NewMenuItem('system', 'Reports', _('Set Purchase Order Authorisation levels'), '/PO_AuthorisationLevels.php', 6);
NewMenuItem('system', 'Reports', _('Payment Methods'), '/PaymentMethods.php', 7);
NewMenuItem('system', 'Reports', _('Sales People'), '/SalesPeople.php', 8);
NewMenuItem('system', 'Reports', _('Sales Areas'), '/Areas.php', 9);
NewMenuItem('system', 'Reports', _('Shippers'), '/Shippers.php', 10);
NewMenuItem('system', 'Reports', _('Sales GL Interface Postings'), '/SalesGLPostings.php', 11);
NewMenuItem('system', 'Reports', _('COGS GL Interface Postings'), '/COGSGLPostings.php', 12);
NewMenuItem('system', 'Reports', _('Freight Costs Maintenance'), '/FreightCosts.php', 13);
NewMenuItem('system', 'Reports', _('Discount Matrix'), '/DiscountMatrix.php', 14);
NewMenuItem('system', 'Reports', _('Sales Commission Types'), '/SalesCommissionTypes.php', 15);

NewMenuItem('system', 'Maintenance', _('Inventory Categories Maintenance'), '/StockCategories.php', 1);
NewMenuItem('system', 'Maintenance', _('Inventory Locations Maintenance'), '/Locations.php', 2);
NewMenuItem('system', 'Maintenance', _('Inventory Location Authorised Users Maintenance'), '/LocationUsers.php', 3);
NewMenuItem('system', 'Maintenance', _('User Authorised Inventory Locations Maintenance'), '/UserLocations.php', 4);
NewMenuItem('system', 'Maintenance', _('Discount Category Maintenance'), '/DiscountCategories.php', 5);
NewMenuItem('system', 'Maintenance', _('Units of Measure'), '/UnitsOfMeasure.php', 6);
NewMenuItem('system', 'Maintenance', _('MRP Available Production Days'), '/MRPCalendar.php', 7);
NewMenuItem('system', 'Maintenance', _('MRP Demand Types'), '/MRPDemandTypes.php', 8);
NewMenuItem('system', 'Maintenance', _('Maintain Internal Departments'), '/Departments.php', 9);
NewMenuItem('system', 'Maintenance', _('Maintain Internal Stock Categories to User Roles'),'/InternalStockCategoriesByRole.php', 10);
NewMenuItem('system', 'Maintenance', _('Label Templates Maintenance'), '/Labels.php', 11);
NewMenuItem('system', 'Maintenance', _('Dashboard Configuration'), '/DashboardConfig.php', 12);

NewMenuItem('Utilities', 'Transactions', _('Change A Customer Code'), '/Z_ChangeCustomerCode.php', 1);
NewMenuItem('Utilities', 'Transactions', _('Change A Customer Branch Code'), '/Z_ChangeBranchCode.php', 2);
NewMenuItem('Utilities', 'Transactions', _('Change A GL Account Code'), '/Z_ChangeGLAccountCode.php', 3);
NewMenuItem('Utilities', 'Transactions', _('Change An Inventory Item Code'), '/Z_ChangeStockCode.php', 4);
NewMenuItem('Utilities', 'Transactions', _('Change A Location Code'), '/Z_ChangeLocationCode.php', 5);
NewMenuItem('Utilities', 'Transactions', _('Change A Salesman Code'), '/Z_ChangeSalesmanCode.php', 6);
NewMenuItem('Utilities', 'Transactions', _('Change A Stock Category Code'), '/Z_ChangeStockCategory.php', 7);
NewMenuItem('Utilities', 'Transactions', _('Change A Supplier Code'), '/Z_ChangeSupplierCode.php', 8);
NewMenuItem('Utilities', 'Transactions', _('Translate Item Descriptions'), '/AutomaticTranslationDescriptions.php', 9);
NewMenuItem('Utilities', 'Transactions', _('Update costs for all BOM items, from the bottom up'), '/Z_BottomUpCosts.php', 10);
NewMenuItem('Utilities', 'Transactions', _('Re-apply costs to Sales Analysis'), '/Z_ReApplyCostToSA.php', 11);
NewMenuItem('Utilities', 'Transactions', _('Delete sales transactions'), '/Z_DeleteSalesTransActions.php', 12);
NewMenuItem('Utilities', 'Transactions', _('Reverse all supplier payments on a specified date'), '/Z_ReverseSuppPaymentRun.php', 13);
NewMenuItem('Utilities', 'Transactions', _('Update sales analysis with latest customer data'), '/Z_UpdateSalesAnalysisWithLatestCustomerData.php', 14);
NewMenuItem('Utilities', 'Transactions', _('Copy Authority of GL Accounts from one user to another'), '/Z_GLAccountUsersCopyAuthority.php', 15);

NewMenuItem('Utilities', 'Reports', _('Debtors Balances By Currency Totals'), '/Z_CurrencyDebtorsBalances.php', 1);
NewMenuItem('Utilities', 'Reports', _('Suppliers Balances By Currency Totals'), '/Z_CurrencySuppliersBalances.php', 2);
NewMenuItem('Utilities', 'Reports', _('Show General Transactions That Do Not Balance'), '/Z_CheckGLTransBalance.php', 3);
NewMenuItem('Utilities', 'Reports', _('List of items without picture'), '/Z_ItemsWithoutPicture.php', 4);

NewMenuItem('Utilities', 'Maintenance', _('Maintain Language Files'), '/Z_poAdmin.php', 1);
NewMenuItem('Utilities', 'Maintenance', _('Make New Company'), '/Z_MakeNewCompany.php', 2);
NewMenuItem('Utilities', 'Maintenance', _('Data Export Options'), '/Z_DataExport.php', 3);
NewMenuItem('Utilities', 'Maintenance', _('Import Customers from .csv file'), '/Z_ImportDebtors.php', 4);
NewMenuItem('Utilities', 'Maintenance', _('Import Stock Items from .csv'), '/Z_ImportStocks.php', 5);
NewMenuItem('Utilities', 'Maintenance', _('Import Price List from .csv file'), '/Z_ImportPriceList.php', 6);
NewMenuItem('Utilities', 'Maintenance', _('Import Fixed Assets from .csv file'), '/Z_ImportFixedAssets.php', 7);
NewMenuItem('Utilities', 'Maintenance', _('Import GL Payments Receipts Or Journals From .csv file'), '/Z_ImportGLTransactions.php', 8);
NewMenuItem('Utilities', 'Maintenance', _('Create new company template SQL file and submit to webERP'), '/Z_CreateCompanyTemplateFile.php', 9);
NewMenuItem('Utilities', 'Maintenance', _('Re-calculate brought forward amounts in GL'), '/Z_UpdateChartDetailsBFwd.php', 10);
NewMenuItem('Utilities', 'Maintenance', _('Re-Post all GL transactions from a specified period'), '/Z_RePostGLFromPeriod.php', 11);
NewMenuItem('Utilities', 'Maintenance', _('Purge all old prices'), '/Z_DeleteOldPrices.php', 12);
NewMenuItem('Utilities', 'Maintenance', _('Remove all purchase back orders'), '/Z_RemovePurchaseBackOrders.php', 13);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), _('Move the menu and module strings to the database'));
}
