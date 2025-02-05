<?php

/*****************************************************************************************
KL RICARD MODIFICATIONS:
- Added entries for all KL scripts
*****************************************************************************************/

/* webERP menus with Captions and URLs. */

$ModuleLink = array('Sales', 'AR', 'PO', 'AP', 'stock', 'manuf',  'GL', 'FA', 'PC', 'Personalia', 'system', 'Utilities');
$ReportList = array('Sales'=>'ord',
					'AR'=>'ar',
					'PO'=>'prch',
					'AP'=>'ap',
					'stock'=>'inv',
					'manuf'=>'man',
					'GL'=>'gl',
					'FA'=>'fa',
					'PC'=>'pc',
					'Personalia'=>'pe',
					'system'=>'sys',
					'Utilities'=>'utils'
					);

/*The headings showing on the tabs accross the main index used also in WWW_Users for defining what should be visible to the user */
$ModuleList = array(_('Sales'),
					_('Receivables'),
					_('Purchases'),
					_('Payables'),
					_('Inventory'),
					_('Manufacturing'),
					_('General Ledger'),
					_('Asset Manager'),
					_('Petty Cash'),
					_('Personalia'),
					_('Setup'),
					_('Utilities'));

$MenuItems['Sales']['Transactions']['Caption'] = array(_('New Sales Order or Quotation'),
														_('Enter Counter Sales'),
														_('Enter Counter Returns'),
														_('Retail Point Of Sale'),
														_('Retail Customer Info Card'),
														_('Shop Tali Exchanges'),
														_('Print Picking Lists'),
														_('Outstanding Sales Orders/Quotations'),
														_('Special Order'),
														_('Recurring Order Template'),
														_('Process Recurring Orders'),
														_('Sales Commission Reports'));

$MenuItems['Sales']['Transactions']['URL'] = array('/SelectOrderItems.php?NewOrder=Yes',
													'/CounterSales.php',
													'/CounterReturns.php',
													'/KLRetailPOS.php',
													'/KLRetailCustomerInfoCard.php',
													'/KLShopFreeExchanges.php',
													'/PDFPickingList.php',
													'/SelectSalesOrder.php',
													'/SpecialOrder.php',
													'/SelectRecurringSalesOrder.php',
													'/RecurringSalesOrdersProcess.php',
													'/SalesCommissionReports.php');

if ($KL_BusinessDevelopmentManager){
	$TextControlBoard01 = _('KL Control Board Section 01');
	$LinkControlBoard01 = "/KLControlBoard.php?Section=01";
	$TextControlBoard02 = _('KL Control Board Section 02');
	$LinkControlBoard02 = "/KLControlBoard.php?Section=02";
} else {
	$TextControlBoard01 = _('KL Control Board');
	$LinkControlBoard01 = '/KLControlBoard.php';
	$TextControlBoard02 = '';
	$LinkControlBoard02 = '';
}	
													
if (false){
	$TextPricingControlBoard01 = _('KL Pricing Control Board Section 01');
	$LinkPricingControlBoard01 = "/KLControlBoardPrices.php?Section=01";
	$TextPricingControlBoard02 = _('KL Pricing Control Board Section 02');
	$LinkPricingControlBoard02 = "/KLControlBoardPrices.php?Section=02";
} else {
	$TextPricingControlBoard01 = _('KL Pricing Control Board');
	$LinkPricingControlBoard01 = '/KLControlBoardPrices.php';
	$TextPricingControlBoard02 = '';
	$LinkPricingControlBoard02 = '';
}	

if ($KL_SystemAdmin 
	OR $KL_BusinessDevelopmentManager
	OR $KL_OperationalManager
	OR $KL_SalesDirector){
	$TextPerformanceBoard01 = _('KL Performance Board Section 01');
	$LinkPerformanceBoard01 = "/KLPerformanceBoard.php?Section=01";
	$TextPerformanceBoard02 = _('KL Performance Board Section 02');
	$LinkPerformanceBoard02 = "/KLPerformanceBoard.php?Section=02";
	$TextPerformanceBoard03 = _('KL Performance Board Section 03');
	$LinkPerformanceBoard03 = "/KLPerformanceBoard.php?Section=03";
} else {
	$TextPerformanceBoard01 = _('KL Performance Board');
	$LinkPerformanceBoard01 = '/KLPerformanceBoard.php';
	$TextPerformanceBoard02 = '';
	$LinkPerformanceBoard02 = '';
	$TextPerformanceBoard03 = '';
	$LinkPerformanceBoard03 = '';
}	

$MenuItems['Sales']['Reports']['Caption'] = array( _('Sales Order Inquiry'),
													_('Print Price Lists'),
													_('Order Status Report'),
													_('Orders Invoiced Reports'),
													_('Daily Sales Inquiry'),
													_('Sales By Sales Type Inquiry'),
													_('Sales By Category Inquiry'),
													_('Sales By Category By Item Inquiry'),
													_('Sales Analysis Reports'),
													_('KPI Graphs'),
													_('Sales Graphs'),
													_('Top Sellers Inquiry'),
													_('Order Delivery Differences Report'),
													_('Delivery In Full On Time (DIFOT) Report'),
													_('Sales Order Detail Or Summary Inquiries'),
													_('KL Retail Sales Hourly Report'),
													_('KL Sales Order Detail Or Summary Inquiries'),
													_('Top Sales Items Report'),
													_('Top Customers Inquiry'),
													_('Worst Sales Items Report'),
													_('Sales With Low Gross Profit Report'),
													$TextControlBoard01,
													$TextControlBoard02,
													$TextPricingControlBoard01,
													$TextPricingControlBoard02,
													_('KL SPG Control Board'),
													_('KL SPG End Of Shift Report'),
													$TextPerformanceBoard01,
													$TextPerformanceBoard02,
													$TextPerformanceBoard03,
													_('KL SPG Performance Report'),
													_('KL Quality and Returns Report'),
													_('KL Retail Customer Analysis'),
													_('KL Excel Sales Analysis'),
													_('Sell Through Support Claims Report'));

$MenuItems['Sales']['Reports']['URL'] = array( '/SelectCompletedOrder.php',
												'/PDFPriceList.php',
												'/PDFOrderStatus.php',
												'/PDFOrdersInvoiced.php',
												'/KLDailySalesInquiry.php',
												'/SalesByTypePeriodInquiry.php',
												'/SalesCategoryPeriodInquiry.php',
												'/StockCategorySalesInquiry.php',
												'/SalesAnalRepts.php',
												'/KLGraphs.php',
												'/SalesGraph.php',
												'/SalesTopItemsInquiry.php',
												'/PDFDeliveryDifferences.php',
												'/PDFDIFOT.php',
												'/SalesInquiry.php',
												'/KLSalesHourlyReport.php',
												'/KLSalesInquiry.php',
												'/TopItems.php',
												'/SalesTopCustomersInquiry.php',
												'/NoSalesItems.php',
												'/PDFLowGP.php',
												$LinkControlBoard01,
												$LinkControlBoard02,
												$LinkPricingControlBoard01,
												$LinkPricingControlBoard02,
												'/KLControlBoardSPG.php',
												'/KLRetailEndOfShift.php',
												$LinkPerformanceBoard01,
												$LinkPerformanceBoard02,
												$LinkPerformanceBoard03,
												'/KLSPGPerformance.php',
												'/KLQualityReturnsPerformance.php',
												'/KLRetailCustomerBoard.php',
												'/KLExcelSalesAnalysis.php',
												'/PDFSellThroughSupportClaim.php');

$MenuItems['Sales']['Maintenance']['Caption'] = array( _('Create Contract'),
														_('Select Contract'),
														_('KL Set Website Sales Categories'),
														_('KL Set Related Items'),
														_('Sendinblue: Export KL Retail Customers'),
														_('Sendinblue: Export KL webERP Customers'),
														_('Sendinblue: Export Newsletter Subscribers'),
														_('OpenCart: Sync webERP to OC Daily'),
														_('OpenCart: Sync webERP to OC Hourly'),
														_('OpenCart: Sync OC to webERP'),
														_('AdminCerdas: Upload to marketplaces'),
														_('FORSTOK: Upload to marketplaces'),
														_('Tokopedia: Import ProductID-URL'),
														_('Shopee: Import ProductID-URL'),
														_('Lazada: Import ProductID-URL'),
														_('Lazada: Export Products to Lazada'),
														_('Zalora: Export Products to Zalora'),
														_('Sell Through Support Deals'));

$MenuItems['Sales']['Maintenance']['URL'] = array( '/Contracts.php',
													'/SelectContract.php',
													'/KLSetWebsiteCategories.php',
													'/KLSetRelatedItems.php',
													'/KLExcelExportCustomersSendinblue.php',
													'/KLExcelExportOnlineSendinblue.php',
													'/KLExcelExportNewsletterSendinblue.php',
													'/WeberpToOpenCartDaily.php',
													'/WeberpToOpenCartHourly.php',
													'/OpenCartToWeberp.php',
													'/KLExcelAdminCerdas.php',
													'/KLExcelExportToFS.php',
													'/KLTokopediaURL.php',
													'/KLShopeeURL.php',
													'/KLLazadaURL.php',
													'/KLExcelLazada.php',
													'/KLExcelZalora.php',
													'/SellThroughSupport.php');

$MenuItems['AR']['Transactions']['Caption'] = array(_('Select Order to Invoice'),
													_('Create A Credit Note'),
													_('Enter Receipts'),
													_('Allocate Receipts or Credit Notes'));
$MenuItems['AR']['Transactions']['URL'] = array('/SelectSalesOrder.php',
												'/SelectCreditItems.php?NewCredit=Yes',
												'/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer',
												'/CustomerAllocations.php');

$MenuItems['AR']['Reports']['Caption'] = array(	_('Where Allocated Inquiry'),
												_('Print Invoices or Credit Notes'),
												_('Print Statements'),
												_('Aged Customer Balances/Overdues Report'),
												_('Re-Print A Deposit Listing'),
												_('Debtor Balances At A Prior Month End'),
												_('Customer Listing By Area/SalesPerson'),
												_('List Daily Transactions'),
												_('Customer Transaction Inquiries'),
												_('Customer Activity and Balances'));

if ($_SESSION['InvoicePortraitFormat']==0){
	$PrintInvoicesOrCreditNotesScript = '/PrintCustTrans.php';
} else {
	$PrintInvoicesOrCreditNotesScript = '/PrintCustTransPortrait.php';
}

$MenuItems['AR']['Reports']['URL'] = array(	'/CustWhereAlloc.php',
											$PrintInvoicesOrCreditNotesScript,
											'/PrintCustStatements.php',
											'/AgedDebtors.php',
											'/PDFBankingSummary.php',
											'/DebtorsAtPeriodEnd.php',
											'/PDFCustomerList.php',
											'/PDFCustTransListing.php',
											'/CustomerTransInquiry.php',
											'/CustomerBalancesMovement.php' );

$MenuItems['AR']['Maintenance']['Caption'] = array(	_('Add Customer'),
													_('Select Customer'));
$MenuItems['AR']['Maintenance']['URL'] = array(	'/Customers.php',
												'/SelectCustomer.php');

$MenuItems['AP']['Transactions']['Caption'] = array(_('Select Supplier'),
													_('Supplier Allocations'));
$MenuItems['AP']['Transactions']['URL'] = array('/SelectSupplier.php',
												'/SupplierAllocations.php');

$MenuItems['AP']['Reports']['Caption'] = array(	_('Where Allocated Inquiry.php'),
												_('Aged Supplier Report'),
												_('Payment Run Report'),
												_('Remittance Advices'),
												_('Outstanding GRNs Report'),
												_('Supplier Balances At A Prior Month End'),
												_('List Daily Transactions'),
												_('Purchase Order Financial Planning'),
												_('KL Goods Received Not Invoiced Yet'),
												_('Supplier Transaction Inquiries'));

$MenuItems['AP']['Reports']['URL'] = array( '/SuppWhereAlloc.php',
											'/AgedSuppliers.php',
											'/SuppPaymentRun.php',
											'/PDFRemittanceAdvice.php',
											'/OutstandingGRNs.php',
											'/SupplierBalsAtPeriodEnd.php',
											'/PDFSuppTransListing.php',
											'/POFinancialPlanning.php',
											'/KLGoodsReceivedNotInvoiced.php',
											'/SupplierTransInquiry.php');

$MenuItems['AP']['Maintenance']['Caption'] = array(	_('Add Supplier'),
													_('Select Supplier'),
													_('Maintain Factor Companies'));
$MenuItems['AP']['Maintenance']['URL'] = array(	'/Suppliers.php',
												'/SelectSupplier.php',
												'/Factors.php');

$MenuItems['PO']['Transactions']['Caption'] = array(_('New Purchase Order'),
													_('Purchase Orders'),
													_('Purchase Order Grid Entry'),
													_('Create a New Tender'),
													_('Edit Existing Tenders'),
													_('Process Tenders and Offers'),
													_('Orders to Authorise'),
													_('Shipment Entry'),
													_('Select A Shipment'));
$MenuItems['PO']['Transactions']['URL'] = array(	'/PO_Header.php?NewOrder=Yes',
													'/PO_SelectOSPurchOrder.php',
													'/PurchaseByPrefSupplier.php',
													'/SupplierTenderCreate.php?New=Yes',
													'/SupplierTenderCreate.php?Edit=Yes',
													'/OffersReceived.php',
													'/PO_AuthoriseMyOrders.php',
													'/SelectSupplier.php',
													'/Shipt_Select.php');

$MenuItems['PO']['Reports']['Caption'] = array(	_('Purchase Order Inquiry'),
												_('Purchase Order Detail Or Summary Inquiries'),
												_('Supplier Price List'),
												_('Purchases from Suppliers'));

$MenuItems['PO']['Reports']['URL'] = array(	'/PO_SelectPurchOrder.php',
											'/POReport.php',
											'/SuppPriceList.php',
											'/PurchasesReport.php');

$MenuItems['PO']['Maintenance']['Caption'] = array(_('Maintain Supplier Price Lists'));

$MenuItems['PO']['Maintenance']['URL'] = array('/SupplierPriceList.php');

if ($KL_SPGSeniorOrSupport OR $KL_SPGJunior){
	$TextTransferReceive = _('KL Shop Transfer - Receive Transfer FROM kantor');
} else {
	$TextTransferReceive = _('Bulk Inventory Transfer') . ' - ' . _('Receive');
}

$MenuItems['stock']['Transactions']['Caption'] = array(	_('Receive Purchase Orders'),
														_('Inventory Location Transfers'),
														_('Bulk Inventory Transfer') . ' - ' . _('Dispatch'),
														$TextTransferReceive,
														_('KL Shop Transfer - Send Return Transfer TO kantor'),
														_('KL Shop Packaging Fill Up'),
														_('KL Standard Cost Bulk Adjustments'),
														_('Inventory Adjustments'),
														_('Reverse Goods Received'),
														_('Enter Stock Counts'),
														_('Create a New Internal Stock Request'),
														_('Authorise Internal Stock Requests'),
														_('Fulfill Internal Stock Requests'),
														_('Returned Items Maintenance'));

$MenuItems['stock']['Transactions']['URL'] = array(	'/PO_SelectOSPurchOrder.php',
													'/StockTransfers.php?New=Yes',
													'/StockLocTransfer.php',
													'/StockLocTransferReceive.php',
													'/KLPOSReturnToKantor.php',
													'/KLFillUpShopPackaging.php',
													'/KLUpdateStandardCostCountry.php',
													'/StockAdjustments.php?NewAdjustment=Yes',
													'/ReverseGRN.php',
													'/StockCounts.php',
													'/InternalStockRequest.php?New=Yes',
													'/InternalStockRequestAuthorisation.php',
													'/InternalStockRequestFulfill.php',
													'/KLReturnedItems.php');

$MenuItems['stock']['Reports']['Caption'] = array(	_('Serial Item Research Tool'),
													_('Print Price Labels'),
													_('Reprint GRN'),
													_('Inventory Item Movements'),
													_('Inventory Item Status'),
													_('Shop Inventory for SPG'),
													_('Item Movements for SPG'),
													_('Item Status at all KL Shops for SPG'),
													_('Item Status at all KL Shops for Manager'),
													_('Shop Transfers List for SPG'),
													_('Item Sold List for SPG'),
													_('Inventory Item Usage'),
													_('Inventory Quantities'),
													_('Reorder Level'),
													_('Stock Dispatch'),
													_('Inventory Valuation Report'),
													_('Mail Inventory Valuation Report'),
													_('Inventory Planning Report'),
													_('Inventory Planning Based On Preferred Supplier Data'),
													_('Inventory Stock Check Sheets'),
													_('Make Inventory Quantities CSV'),
													_('Compare Counts Vs Stock Check Data'),
													_('All Inventory Movements By Location/Date'),
													_('List Inventory Status By Location/Category'),
													_('Historical Stock Quantity By Location/Category'),
													_('List Negative Stocks'),
													_('Period Stock Transaction Listing'),
													_('KL Excel Inventory Taking At Location'),
													_('KL Stock Available Not in Shop'),
													_('KL Active Transfer Status'),
													_('KL Price Analysis'),
													_('KL Inventory Distribution By Type'),
													_('Stock Transfer Note'),
													_('Aged Controlled Stock Report'),
													_('Internal stock request inquiry'),
													_('Pending Internal Stock Requests for SPG'),
													_('KL Service Fee Calculator'));

$MenuItems['stock']['Reports']['URL'] = array(	'/StockSerialItemResearch.php',
												'/KLPDFPrintLabelTCPDF.php',
												'/ReprintGRN.php',
												'/StockMovements.php',
												'/StockStatus.php',
												'/KLShopInventorySPG.php',
												'/KLStockMovementsSPG.php',
												'/KLStockStatusSPG.php',
												'/KLStockStatusManagers.php',
												'/KLShopTransfersSPG.php',
												'/KLShopSalesSPG.php',
												'/StockUsage.php',
												'/InventoryQuantities.php',
												'/ReorderLevel.php',
												'/StockDispatch.php',
												'/InventoryValuation.php',
												'/MailInventoryValuation.php',
												'/InventoryPlanning.php',
												'/InventoryPlanningPrefSupplier.php',
												'/StockCheck.php',
												'/StockQties_csv.php',
												'/PDFStockCheckComparison.php',
												'/StockLocMovements.php',
												'/StockLocStatus.php',
												'/StockQuantityByDate.php',
												'/PDFStockNegatives.php',
												'/PDFPeriodStockTransListing.php',
												'/KLExcelInventoryTaking.php',
												'/KLAvailableItemsNotInShop.php',
												'/KLTransferStatus.php',
												'/KLExcelPriceAnalysis.php',
												'/KLInventoryDistribution.php',
												'/PDFStockTransfer.php',
												'/AgedControlledInventory.php',
												'/InternalStockRequestInquiry.php',
												'/KLInternalStockRequestView.php',
												'/KLServiceFeeCalculator.php');

$MenuItems['stock']['Maintenance']['Caption'] = array(	_('Add A New Item'),
														_('Select An Item'),
														_('Review Translated Descriptions'),
														_('Sales Category Maintenance'),
														_('Item Tags Maintenance'),
														_('Brands Maintenance'),
														_('Add or Update Prices Based On Costs'),
														_('View or Update Prices Based On Costs'),
														_('KL Automatic Reorder Level Adjustments'),
														_('KL Price Change Process - Step 01'),
														_('KL Price Change Process - Step 02'),
														_('KL Move To 20% Discount Process - Step 01'),
														_('KL Move To 20% Discount Process - Step 02'),
														_('KL Move To 50% Discount Process - Step 01'),
														_('KL Move To 50% Discount Process - Step 02'),
														_('KL Move To 80% Discount Process - Step 01'),
														_('KL Move To 80% Discount Process - Step 02'),
														_('Reorder Level By Category/Location'),
														_('KL Set All Reorder Level Zero in Location'),
														_('KL Copy Reorder Level From Location A to B'));

$MenuItems['stock']['Maintenance']['URL'] = array(	'/Stocks.php',
													'/SelectProduct.php',
													'/RevisionTranslations.php',
													'/SalesCategories.php',
													'/KLItemTags.php',
													'/Manufacturers.php',
													'/PricesBasedOnMarkUp.php',
													'/PricesByCost.php',
													'/KLAdjustReorderLevel.php',
													'/KLRetailPriceChangeStep01.php',
													'/KLRetailPriceChangeStep02.php',
													'/KLMoveToDiscount20Step01.php',
													'/KLMoveToDiscount20Step02.php',
													'/KLMoveToDiscount50Step01.php',
													'/KLMoveToDiscount50Step02.php',
													'/KLMoveToDiscount80Step01.php',
													'/KLMoveToDiscount80Step02.php',
													'/ReorderLevelLocation.php',
													'/KLSetAllRLZero.php',
													'/KLCopyRLBetweenShops.php');

$MenuItems['manuf']['Transactions']['Caption'] = array(	_('Work Order Entry'),
														_('Select A Work Order'),
														_('QA Samples and Test Results'));

$MenuItems['manuf']['Transactions']['URL'] = array(	'/WorkOrderEntry.php',
													'/SelectWorkOrder.php',
													'/SelectQASamples.php');
$MenuItems['manuf']['Reports']['Caption'] = array(	_('Select A Work Order'),
													_('Costed Bill Of Material Inquiry'),
													_('Where Used Inquiry'),
													_('Bill Of Material Listing'),
													_('Indented Bill Of Material Listing'),
													_('List Components Required'),
													_('List Materials Not Used Anywhere'),
													_('Indented Where Used Listing'),
													_('WO Items ready to produce'),
													_('MRP'),
													_('MRP Shortages'),
													_('MRP Suggested Purchase Orders'),
													_('MRP Suggested Work Orders'),
													_('MRP Reschedules Required'),
													_('Print Product Specification'),
													_('Print Certificate of Analysis'),
													_('Historical QA Test Results'),
													_('Multiple Work Orders Total Cost Inquiry'));

$MenuItems['manuf']['Reports']['URL'] = array(	'/SelectWorkOrder.php',
												'/BOMInquiry.php',
												'/WhereUsedInquiry.php',
												'/BOMListing.php',
												'/BOMIndented.php',
												'/BOMExtendedQty.php',
												'/MaterialsNotUsed.php',
												'/BOMIndentedReverse.php',
												'/WOCanBeProducedNow.php',
												'/MRPReport.php',
												'/MRPShortages.php',
												'/MRPPlannedPurchaseOrders.php',
												'/MRPPlannedWorkOrders.php',
												'/MRPReschedules.php',
												'/PDFProdSpec.php',
												'/PDFCOA.php',
												'/HistoricalTestResults.php',
												'/CollectiveWorkOrderCost.php');

$MenuItems['manuf']['Maintenance']['Caption'] = array(	_('Work Centre'),
														_('Bills Of Material'),
														_('Copy a Bill Of Materials Between Items'),
														_('Master Schedule'),
														_('Auto Create Master Schedule'),
														_('MRP Calculation'),
														_('Quality Tests Maintenance'),
														_('Product Specifications'));

$MenuItems['manuf']['Maintenance']['URL'] = array(	'/WorkCentres.php',
													'/BOMs.php',
													'/CopyBOM.php',
													'/MRPDemands.php',
													'/MRPCreateDemands.php',
													'/MRP.php',
													'/QATests.php',
													'/ProductSpecs.php');

$MenuItems['GL']['Transactions']['Caption'] = array(	_('Bank Account Payments Entry'),
														_('Bank Account Receipts Entry'),
														_('Import Bank Transactions'),
														_('Journal Entry'),
														_('Bank Account Payments Matching'),
														_('Bank Account Receipts Matching'),
														_('KL Consignment Invoices'),
														_('KL Export CSV for Faktur Pajak'),
														_('Process Regular Payments'));

$MenuItems['GL']['Transactions']['URL'] = array('/Payments.php?NewPayment=Yes',
												'/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL',
												'/ImportBankTrans.php',
												'/GLJournal.php?NewJournal=Yes',
												'/BankMatching.php?Type=Payments',
												'/BankMatching.php?Type=Receipts',
												'/KLConsignmentInvoice.php',
												'/KLConsignmentCSVFakturPajak.php',
												'/RegularPaymentsProcess.php');
										
$MenuItems['GL']['Reports']['Caption'] = array( _('Bank Account Reconciliation Statement'),
												_('Cheque Payments Listing'),
												_('Daily Bank Transactions'),
												_('Account Inquiry'),
												_('Account Listing'),
												_('Account Listing to CSV File'),
												_('General Ledger Journal Inquiry'),
												_('Trial Balance'),
												_('Balance Sheet'),
												_('Profit and Loss Statement'),
												_('KL Cash Variation'),
												_('Cash Flow Statement'),
												_('Export Excel GL Transactions for PT'),
												_('Horizontal Analysis of Statement of Financial Position'),
												_('Horizontal Analysis of Statement of Comprehensive Income'),
												_('KL Consignment Invoices Issued List'),
												_('Tag Reports'),
												_('Tax Reports'));

$MenuItems['GL']['Reports']['URL'] = array(	'/BankReconciliation.php',
											'/PDFChequeListing.php',
											'/DailyBankTransactions.php',
											'/SelectGLAccount.php',
											'/GLAccountReport.php',
											'/GLAccountCSV.php',
											'/GLJournalInquiry.php',
											'/GLTrialBalance.php',
											'/GLBalanceSheet.php',
											'/GLProfit_Loss.php',
											'/KLGLCashVariation.php',
											'/GLCashFlowsIndirect.php',
											'/KLExcelGLTransactionsPajak.php',
											'/AnalysisHorizontalPosition.php',
											'/AnalysisHorizontalIncome.php',
											'/KLConsignmentInvoiceIssuedList.php',
											'/GLTagProfit_Loss.php',
											'/Tax.php');																						

$MenuItems['GL']['Maintenance']['Caption'] = array(	_('Account Sections'),
													_('Account Groups'),
													_('GL Accounts'),
													_('GL Accounts for PT. Angin Dingin Utara'),
													_('GL Accounts for PT. Sungai Mutiara Hitam'),
													_('GL Accounts for PT. Bumi Biru'),
													_('GL Accounts for Retail Partner POIK'),
													_('GL Accounts for Retail Partner POPI'),
													_('GL Budgets'),
													_('GL Tags'),
													_('GL Accounts Authorised Users Maintenance'),
													_('User Authorised GL Accounts Maintenance'),
													_('Copy Authority GL Accounts from user A to B'),
													_('Bank Accounts'),
													_('Bank Account Authorized Users'),
													_('User Authorized Bank Accounts'),
													_('Copy Authority Bank Accounts from user A to B'), 
													_('Setup Regular Payments'));

$MenuItems['GL']['Maintenance']['URL'] = array(		'/AccountSections.php',
													'/AccountGroups.php',
													'/GLAccounts.php',
													'/KLGLAccountsADU.php',
													'/KLGLAccountsSMH.php',
													'/KLGLAccountsBB.php',
													'/KLGLAccountsIK.php',
													'/KLGLAccountsPI.php',
													'/GLBudgets.php',
													'/GLTags.php',
													'/GLAccountUsers.php',
													'/UserGLAccounts.php',
													'/GLAccountUsersCopyAuthority.php',
													'/BankAccounts.php',
													'/BankAccountUsers.php',
													'/UserBankAccounts.php',
													'/GLBankAccountUsersCopyAuthority.php',
													'/RegularPaymentsSetup.php');

$MenuItems['FA']['Transactions']['Caption'] = array(_('Add a new Asset'),
													_('Select an Asset'),
													_('Change Asset Location'),
													_('Depreciation Journal'),
													_('KL Maintenance Tasks'));

$MenuItems['FA']['Transactions']['URL'] = array('/FixedAssetItems.php',
												'/SelectAsset.php',
												'/FixedAssetTransfer.php',
												'/FixedAssetDepreciation.php',
												'/KLMaintenanceTasks.php');

$MenuItems['FA']['Reports']['Caption'] = array(	_('Asset Register'),
												_('My Maintenance Schedule'),
												_('Maintenance Reminder Emails'),
												_('KL Maintenance Tasks Control Board'));

$MenuItems['FA']['Reports']['URL'] = array(	'/FixedAssetRegister.php',
											'/MaintenanceUserSchedule.php',
											'/MaintenanceReminders.php',
											'/KLMaintenanceTasksBoard.php');

$MenuItems['FA']['Maintenance']['Caption'] = array(	_('Fixed Asset Category Maintenance'),
													_('Add or Maintain Asset Locations'),
													_('Fixed Asset Maintenance Tasks'),
													_('KL Maintenance Types Maintenace'));

$MenuItems['FA']['Maintenance']['URL'] = array(	'/FixedAssetCategories.php',
												'/FixedAssetLocations.php',
												'/MaintenanceTasks.php',
												'/KLMaintenanceTypes.php');

$MenuItems['PC']['Transactions']['Caption'] = array(_('Assign Cash to PC Tab'),
													_('Cash Transfer Between Tabs'),
													_('Claim Expenses From PC Tab'),
													_('Authorize Cash Assign To PC Tab'),
													_('Authorize Expenses From PC Tab'));

$MenuItems['PC']['Transactions']['URL'] = array('/PcAssignCashToTab.php',
												'/PcAssignCashTabToTab.php',
												'/PcClaimExpensesFromTab.php',
												'/PcAuthorizeCash.php',
												'/PcAuthorizeExpenses.php');

$MenuItems['PC']['Reports']['Caption'] = array(_('PC Tab General Report'),
											   _('PC Expense General Report'),
											   _('PC Tab Expenses List'),
											   _('PC Expenses Analysis'));

$MenuItems['PC']['Reports']['URL'] = array('/PcReportTab.php',
										   '/PcReportExpense.php',
										   '/PcTabExpensesList.php',
										   '/PcAnalysis.php');

$MenuItems['PC']['Maintenance']['Caption'] = array(	_('Types of PC Tabs'),
													_('PC Tabs'),
													_('PC Expenses'),
													_('Expenses for Type of PC Tab'));

$MenuItems['PC']['Maintenance']['URL'] = array(	'/PcTypeTabs.php',
												'/PcTabs.php',
												'/PcExpenses.php',
												'/PcExpensesTypeTab.php');

$MenuItems['Personalia']['Transactions']['Caption'] = array(_('Import Monthly Salaries Info from Excel File'),
															_('Export Info for PPH21 Deduction'),
															_('Update PPH21 Deduction'),
															_('Export Monthly Salary Slips'),
															_('Send Monthly Salary Slips by e-mail'),
															_('Export CSV File for Danamon Transfer LLG'),
															_('Export CSV File for Danamon Transfer Payroll'),
															_('Export CSV File for Danamon Transfer @ Cash Connect'),
															_('Move Salaries info to Petty Cash')
															);

$MenuItems['Personalia']['Transactions']['URL'] = array('/KLPersonaliaImportSalaries.php',
														'/KLPersonaliaExportInfoPPH21.php',
														'/KLPersonaliaDeductionPPH21.php',
														'/KLPersonaliaPDFMonthlySalarySlips.php',
														'/KLPersonaliaEmailMonthlySalarySlips.php',
														'/KLPersonaliaCSVDanamonLLG.php',
														'/KLPersonaliaCSVDanamonPayroll.php',
														'/KLPersonaliaCSVDanamonCashConnect.php',
														'/KLPersonaliaMoveTxToPC.php'
														);

$MenuItems['Personalia']['Reports']['Caption'] = array();

$MenuItems['Personalia']['Reports']['URL'] = array();

$MenuItems['Personalia']['Maintenance']['Caption'] = array(_('SPG List'),
															_('User Maintenance'),
															_('KL SPG User Maintenance'));

$MenuItems['Personalia']['Maintenance']['URL'] = array('/SalesPeople.php',
													'/WWW_Users.php',
													'/KLUsersSPG.php');

$MenuItems['system']['Transactions']['Caption'] = array(_('Company Preferences'),
														_('System Parameters'),
														_('KL Retail Partners'),
														_('KL Online Partners'),
														_('Maintain Security Tokens'),
														_('Access Permissions Maintenance'),
														_('Page Security Settings'),
														_('Currencies Maintenance'),
														_('Tax Authorities and Rates Maintenance'),
														_('Tax Group Maintenance'),
														_('Dispatch Tax Province Maintenance'),
														_('Tax Category Maintenance'),
														_('List Periods Defined'),
														_('View Audit Trail'),
														_('View Scripts Audit'),
														_('Geocode Maintenance'),
														_('Form Design'),
														_('Web-Store Configuration'),
														_('SMTP Server Details'),
														_('Mailing Group Maintenance'),
														_('Test Silent Printing')
														);

$MenuItems['system']['Transactions']['URL'] = array('/CompanyPreferences.php',
													'/SystemParameters.php',
													'/KLRetailPartners.php',
													'/KLOnlinePartners.php',
													'/SecurityTokens.php',
													'/WWW_Access.php',
													'/PageSecurity.php',
													'/Currencies.php',
													'/TaxAuthorities.php',
													'/TaxGroups.php',
													'/TaxProvinces.php',
													'/TaxCategories.php',
													'/PeriodsInquiry.php',
													'/AuditTrail.php',
													'/AuditScripts.php',
													'/GeocodeSetup.php',
													'/FormDesigner.php',
													'/ShopParameters.php',
													'/SMTPServer.php',
											       	'/MailingGroupMaintenance.php',
											       	'/KLRetailPOSTest.php'
													);

$MenuItems['system']['Reports']['Caption'] = array(	_('Sales Types'),
													_('Returned Item Reasons'),
													_('Customer Types'),
													_('Supplier Types'),
													_('Credit Status'),
													_('Payment Terms'),
													_('Set Purchase Order Authorisation levels'),
													_('Payment Methods'),
													_('Sales Areas'),
													_('Shippers'),
													_('Sales GL Interface Postings'),
													_('COGS GL Interface Postings'),
													_('Freight Costs Maintenance'),
													_('Discount Matrix'));
$MenuItems['system']['Reports']['URL'] = array(	'/SalesTypes.php',
												'/KLReturnedItemsReasons.php',
												'/CustomerTypes.php',
												'/SupplierTypes.php',
												'/CreditStatus.php',
												'/PaymentTerms.php',
												'/PO_AuthorisationLevels.php',
												'/PaymentMethods.php',
												'/Areas.php',
												'/Shippers.php',
												'/SalesGLPostings.php',
												'/COGSGLPostings.php',
												'/FreightCosts.php',
												'/DiscountMatrix.php');
$MenuItems['system']['Maintenance']['Caption'] = array(	_('Inventory Categories Maintenance'),
														_('Inventory Location Zones Maintenance'),
														_('Inventory Location Types Maintenance'),
														_('Inventory Locations Maintenance'),
														_('Inventory Location Authorised Users Maintenance'),
														_('User Authorised Inventory Locations Maintenance'),
														_('Copy Authority Locations from user A to B'),
														_('Discount Category Maintenance'),
														_('Units of Measure'),
														_('MRP Available Production Days'),
														_('MRP Demand Types'),
														_('Maintain Internal Departments'),
														_('Maintain Internal Stock Categories to User Roles'),
														_('KL Label Templates Maintenance'),
														_('webERP Label Templates Maintenance'),
														_('Dashboard Configuration'));

$MenuItems['system']['Maintenance']['URL'] = array(	'/StockCategories.php',
													'/LocationZones.php',
													'/LocationTypes.php',
													'/Locations.php',
													'/LocationUsers.php',
													'/UserLocations.php',
													'/LocationUsersCopyAuthority.php',
													'/DiscountCategories.php',
													'/UnitsOfMeasure.php',
													'/MRPCalendar.php',
													'/MRPDemandTypes.php',
													'/Departments.php',
													'/InternalStockCategoriesByRole.php',
													'/KLLabels.php',
													'/Labels.php',
													'/DashboardConfig.php');

$MenuItems['Utilities']['Transactions']['Caption'] = array(	_('Change A Customer Code'),
															_('Change A Customer Branch Code'),
															_('Change A Supplier Code'),
															_('Change A Stock Category Code'),
															_('Change An Inventory Item Code'),
															_('Change A GL Account Code'),
															_('Change A Location Code'),
															_('Translate Item Descriptions'),
															_('Update costs for all BOM items, from the bottom up'),
															_('Re-apply costs to Sales Analysis'),
															_('Delete sales transactions'),
															_('Reverse all supplier payments on a specified date'),
															_('Update sales analysis with latest customer data'));

$MenuItems['Utilities']['Transactions']['URL'] = array(	'/Z_ChangeCustomerCode.php',
														'/Z_ChangeBranchCode.php',
														'/Z_ChangeSupplierCode.php',
														'/Z_ChangeStockCategory.php',
														'/Z_ChangeStockCode.php',
														'/Z_ChangeGLAccountCode.php',
														'/Z_ChangeLocationCode.php',
														'/AutomaticTranslationDescriptions.php',
														'/Z_BottomUpCosts.php',
														'/Z_ReApplyCostToSA.php',
														'/Z_DeleteSalesTransActions.php',
														'/Z_ReverseSuppPaymentRun.php',
														'/Z_UpdateSalesAnalysisWithLatestCustomerData.php');

$MenuItems['Utilities']['Reports']['Caption'] = array(	_('Debtors Balances By Currency Totals'),
														_('Suppliers Balances By Currency Totals'),
														_('Show General Transactions That Do Not Balance'),
														_('List of items without picture'));

$MenuItems['Utilities']['Reports']['URL'] = array(	'/Z_CurrencyDebtorsBalances.php',
													'/Z_CurrencySuppliersBalances.php',
													'/Z_CheckGLTransBalance.php',
													'/Z_ItemsWithoutPicture.php');

$MenuItems['Utilities']['Maintenance']['Caption'] = array(	_('Maintain Language Files'),
															_('Make New Company'),
															_('Data Export Options'),
															_('Import Customers from .csv file'),
															_('Import Stock Items from .csv file'),
															_('Import Price List from .csv file'),
															_('Import Fixed Assets from .csv file'),
															_('Import GL Payments Receipts Or Journals From .csv file'),
															_('Create new company template SQL file and submit to webERP'),
															_('Re-calculate brought forward amounts in GL'),
															_('Re-Post all GL transactions from a specified period'),
															_('KL Daily Database Maintenance'),
															_('KL Purge Old Data in Database'),
															_('Purge all old prices'));

$MenuItems['Utilities']['Maintenance']['URL'] = array(	'/Z_poAdmin.php',
														'/Z_MakeNewCompany.php',
														'/Z_DataExport.php',
														'/Z_ImportDebtors.php',
														'/Z_ImportStocks.php',
														'/Z_ImportPriceList.php',
														'/Z_ImportFixedAssets.php',
														'/Z_ImportGLTransactions.php',
														'/Z_CreateCompanyTemplateFile.php',
														'/Z_UpdateChartDetailsBFwd.php',
														'/Z_RePostGLFromPeriod.php',
														'/KLMaintainDatabase.php',
														'/KLPurgeOldData.php',
														'/Z_DeleteOldPrices.php');
?>
